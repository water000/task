<?php

// Base on mysql-PDO AND Memcached
class CSessionDBCache extends SessionHandler {
    
    private $dbpool = null;
    private $dbconn = null;
    private $tbname = null;
    private $mempool = null;
    private $memconn = null;
    private $initmemconn = false;
    
    function __construct($dbpool, $mempool=null){
        $this->tbname = mbs_tbname('common_session');
        $this->dbpool = $dbpool;
        $this->mempool = $mempool;
    }
    
    private function _init_dbconn(){
        $this->dbconn = $this->dbpool->getDefaultConnection();
    }
    private function _init_memconn(){
        $this->memconn = $this->mempool->getConnection();
        $this->initmemconn = true;
    }
    
    public  function close (){
        
    }
    
    public function create_sid (){
        return md5(uniqid('abcdef', true));
    }
    
    static function check_sid($sid){
        $len = strlen($sid);
        if($len != 32) return false;
        for($i=0; $i<$len; ++$i){
            $c = ord($sid[$i]);
            if(($c>47 && $c<58 ) || ($c > 96 && $c<103)) ; // 0-9 , a-f
            else break;
        }
        return $i==$len;
    }
    
    public function destroy ( $session_id ){
        if(!self::check_sid($session_id)){
            trigger_error('Invalid sid', E_USER_ERROR);
            return;
        }
        
        if(empty($this->dbconn)) $this->_init_dbconn();
        $this->dbconn->exec(sprintf('DELETE FROM %s WHERE id="%s"',
            $this->tbname, $session_id));
        
        if(!$this->initmemconn) $this->_init_memconn();
        if(!empty($this->memconn)){
            $ret = $this->memconn->deleteByKey(__CLASS__, 'session/'.$session_id);
            if(!empty($ret)) return $ret;
        }
    }
    
    public function gc ( $maxlifetime ){
        
    }
    
    public function open ( $save_path , $session_name ){
        
    }
    
    public function read ( $session_id ){
        $ret = null;
        
        if(!self::check_sid($session_id)){
            trigger_error('Invalid sid', E_USER_ERROR);
            return null;
        }
        
        if(!$this->initmemconn) $this->_init_memconn();
        if(!empty($this->memconn)){
            $ret = $this->memconn->getByKey(__CLASS__, 'session/'.$session_id);
            if(!empty($ret)) return $ret;
        }
        
        if(empty($this->dbconn)) $this->_init_dbconn();
        $pdos = $this->dbconn->query(sprintf('SELECT data, write_ts FROM %s WHERE id="%s"', 
            $this->tbname, $session_id) );
        $res = $pdos->fetchAll(PDO::FETCH_ASSOC);
        if(empty($res)) return null;
        $res = $res[0];
        $now = time();
        if($now - $res['write_ts'] > ini_get('session.gc_maxlifetime')) return null;
        $this->dbconn->exec(sprintf('UPDATE %s SET write_ts=%d WHERE id="%s"', 
            $this->tbname, $now, $session_id));
        
        if(!empty($this->memconn)){
            $this->memconn->setByKey(__CLASS__, 'session/'.$session_id, 
                $res['data'], ini_get('session.cache_expire')*60);
        }
        
        return $res['data'];
    }
    
    public function write ( $session_id , $session_data ){
        if(empty($session_data)) return false;
        if(empty($this->dbconn)) $this->_init_dbconn();
        $pre = $this->dbconn->prepare(sprintf('REPLACE INTO %s SET id=?, write_ts=?, data=?', $this->tbname));
        $ret = $pre->execute(array($session_id, time(), $session_data));
        
        if(!$this->initmemconn) $this->_init_memconn();
        if(!empty($this->memconn)){
            return $this->memconn->setByKey(__CLASS__, 'session/'.$session_id, 
                $session_data, ini_get('session.cache_expire')*60);
        }
        return $ret;
    }
    
    function readByUid($uid){
        if(empty($this->dbconn)) $this->_init_dbconn();
        $pdos = $this->dbconn->query(sprintf('SELECT id, data, write_ts FROM %s WHERE uid="%s"'),
            $this->tbname, $uid);
        $res = $pdos->fetchAll(PDO::FETCH_ASSOC);
        return isset($res[0]) ? $res[0] : null;
    }
}

?>