<?php


class CUniqRowOfTable
{
	protected        $primaryKey = null;
	protected        $oPdoConn   = null;
	protected        $keyname    = null;
	protected        $tbname     = null;
	protected        $pdos       = null;
	protected        $error      = '';
	protected        $fetch_type = PDO::FETCH_ASSOC;

	/*protected*/ function __construct($oPdoConn, $tbname, $keyname, $primaryKey)
	{
		$this->oPdoConn   = $oPdoConn;
		$this->primaryKey = $primaryKey;
		$this->keyname    = $keyname;
		$this->tbname     = $tbname;
	}
	
	function setKeyname($name){
	    $this->keyname = $name;
	}
	function keyname(){
	    return $this->keyname;
	}
	function setPrimaryKey($key)
	{
		$this->primaryKey = $key;
	}
	function getPrimaryKey()
	{
		return $this->primaryKey;
	}
	function setConnection($conn)
	{
		$this->oPdoConn = $conn;
	}
	function getConnection()
	{
		return $this->oPdoConn;
	}
	
	protected function _seterror($src){
		$error = $src->errorInfo();
		$this->error = sprintf('[%s:%s]%s', $error[0], $error[1], $error[2]);
	}
	
	function error(){
		return $this->error;
	}

	function setTetchType($type){
		$this->fetch_type = $type;
	}
	
	/**
	 * the method must return a primary key
	 * @param array $param the key-pair array, if some keys(like primary key) are not set,
	 * you MUST set them. Because the array will be cached like calling 'get()'
	 */
	function add(&$param){
		$keys = array_keys($param);
		$sql = sprintf('INSERT INTO %s(%s) VALUES(%s)', 
			$this->tbname, 
			implode(',', $keys), 
			str_repeat('?,', count($keys)-1).'?'
		);
		try{
			$pre = $this->oPdoConn->prepare($sql);
			$ret = $pre->execute(array_values($param));
			if($ret === false){
				$this->_seterror($pre);
				return 0;
			}
			if(!isset($param[$this->keyname])){
			    $ret = $this->primaryKey= $param[$this->keyname] = $this->oPdoConn->lastInsertId();
			}else{
			    $ret = $this->primaryKey= $param[$this->keyname];
			}
		}catch(Exception $e){
			throw $e;
		}
		return $ret;
	}
	
	function get(){
		$sql = sprintf('SELECT * FROM %s WHERE %s=%d', 
			$this->tbname, $this->keyname, $this->primaryKey);
		try {
			$pdos = $this->oPdoConn->query($sql);
			$ret = $pdos->fetchAll(PDO::FETCH_ASSOC);
			$ret = empty($ret) ? array() : $ret[0];
		} catch (Exception $e) {
			throw $e;
		}
		return $ret;
	}
	
	function union($keys){
		$sql = '';
		$ret = array();
		
		foreach($keys as $k){
			$sql .= sprintf('SELECT * FROM %s WHERE %s=%d UNION', 
				$this->tbname, $this->keyname, $k);
		}
		if(!empty($sql)){
			$sql = substr($sql, 0, -5);
			try {
				$pdos = $this->oPdoConn->query($sql);
				while(($arr = $pdos->fetch(PDO::FETCH_ASSOC)) !== FALSE){
					$ret[$arr[$this->keyname]] = $arr;
				}
			} catch (Exception $e) {
				throw $e;
			}
		}
		
		return $ret;
	}
	
	function set($param){
		$keys = array_keys($param);
		$sql = sprintf('UPDATE %s SET %s WHERE %s=%d', 
			$this->tbname, 
			implode('=?,', $keys).'=?',
			$this->keyname, $this->primaryKey);
		$ret = false;
		try {
			$pdos = $this->oPdoConn->prepare($sql);
			$ret = $pdos->execute(array_values($param));
			if($ret === false){
				$this->_seterror($pdos);
			}else{
				$ret = $pdos->rowCount();
			}
		} catch (Exception $e) {
			throw $e;
		}
		
		return $ret;
	}
	
 	function del($condtions=array()){
 		$ret = null;
 		$sql = sprintf('DELETE FROM %s WHERE %s=%d', 
 			$this->tbname, $this->keyname, $this->primaryKey);
 		
 		if(!empty($condtions)){
 			$sql .= ' AND '.implode('=? AND ', array_keys($condtions)).'=?';
 		}
 			
 		try {
 			if(empty($condtions)){
				$ret = $this->oPdoConn->exec($sql);
 			}else{
 				$pdos = $this->oPdoConn->prepare($sql);
 				$ret = $pdos->execute(array_values($condtions));
 				if($ret !== false){
 					$ret = $pdos->rowCount();
 				}
 			}
			if($ret === false){
				$this->_seterror($this->oPdoConn);
			}
		} catch (Exception $e) {
			throw $e;
		}
		
		return $ret;
 	}
 	
 	function listAll($offset=0, $limit=0){
 		$ret = null;
 		$sql = 'SELECT * FROM '.$this->tbname;
 		if($limit > 0){
 			$sql .= ' LIMIT '.$offset.','.$limit;
 		}
 		
 		try {
 			$pdos = $this->oPdoConn->query($sql);
 		} catch (Exception $e) {
 			throw $e;
 		}
 		
 		return $pdos;
 	}
 	
 	function tbname(){
 		return $this->tbname;
 	}
 	
 	/**
 	 * 
 	 * @param array $keyval
 	 * @param array $opts array('offset'=>0, 'limit'=>0, 'order'=>'id desc')
 	 * @throws Exception
 	 * @return Ambigous <boolean, unknown>|multitype:
 	 */
 	function search($keyval, $opts = array()){
 		$sql = sprintf('SELECT * FROM %s WHERE 1', $this->tbname);
 		$values = array();
 		foreach ($keyval as $key => $val){
 			if(is_array($val)){
 				if(1 == count($val)){
 				    $sql .= sprintf(' AND %s>=?', $key);
 				    array_push($values, $val[0]);
 				}else{
 				    $sql .= sprintf(' AND %s>=? AND %s<?', $key, $key);
 				    array_push($values, $val[0], $val[1]);
 				}
 			}
 			else{
 				if(is_string($val) && 
 					('%' == $val[0] || '%' == $val[strlen($val)-1])){
 					$sql .= sprintf(' AND %s LIKE ?', $key);
 				}else{
 					$sql .= sprintf(' AND %s = ?', $key);
 				}
 				$values[] = $val;
 			}
 		}

 		if(isset($opts['order'])){
 			$sql .= ' ORDER BY '.$opts['order'];
 		}
 		if(isset($opts['offset']) && isset($opts['limit'])){
 			$sql .= ' LIMIT '.$opts['offset'].','.$opts['limit'];
 		}
 		
 		try{
 			$pdos = $this->oPdoConn->prepare($sql);
 			$ret = $pdos->execute($values);
 			return false === $ret ? false : $pdos;
 		}catch (Exception $e){
 			throw $e;
 		}
 		return array();
 	}
 	
 	function count($keyval){
 		$sql = sprintf('SELECT count(1) FROM %s WHERE 1', $this->tbname);
 		$values = array();
 		foreach ($keyval as $key => $val){
 			if(is_array($val)){
 			    if(1 == count($val)){
 			        $sql .= sprintf(' AND %s>=?', $key);
 			        array_push($values, $val[0]);
 			    }else{
 				   $sql .= sprintf(' AND %s>=? AND %s<?', $key, $key);
 				   array_push($values, $val[0], $val[1]);
 			    }
 			}
 			else{
 				if(is_string($val) &&
 				('%' == $val[0] || '%' == $val[strlen($val)-1])){
 					$sql .= sprintf(' AND %s LIKE ?', $key);
 				}else{
 					$sql .= sprintf(' AND %s = ?', $key);
 				}
 				$values[] = $val;
 			}
 		}
 		
 		try{
 			$pdos = $this->oPdoConn->prepare($sql);
 			$ret = $pdos->execute($values);
 			if(empty($ret) || !($ret = $pdos->fetchAll())){
 				return 0;
 			}
 		
 			return $ret[0][0];
 		}catch (Exception $e){
 			throw $e;
 		}
 		
 		return 0;
 	}
}

?>