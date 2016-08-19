<?php

class CSMSCaptcha{
    const CAPTCHA_EXPIRE_SEC = 900;
    const MAX_VERIFY_NUM     = 3;
    const MAX_SEND_NUM       = 30;
    const MAX_RETRY_INTERVAL = 60;
    
    public static $group_map = array(
        'USER_PWD'
    );
    
    private $dbpool = null;
    private $db     = null;
    private $tbn    = '';
    
    function __construct($dbpool){
        $this->dbpool = $dbpool;
        $this->db     = $dbpool->getDefaultConnection();
        $this->tbn    = mbs_tbname('common_sms_captcha');
    }
    
    static function groupid($v){
        $key = array_search($v, self::$group_map);
        return $key === false ? 0 : $key+1;
    }
    static function groupval($id){
        return self::$group_map[$id-1];
    }
    
    /**
     *
     * @param unknown $phone
     * @param unknown $msg_title
     * @param int $group_id, 如果有新的功能出现，需要重新定义一个常量，以_GROUP_ID结尾，参照USER_REG_GROUP_ID
     * @return string
     */
    function create($phone, $captcha, $group){
        $group_id = self::groupid($group);
        if(0 == $group_id) return 'SMSC_INVAID_GROUP';
        
        $sql = sprintf('SELECT * FROM %s WHERE phone="%s"
				AND group_id=%d AND succeed=0',
            $this->tbn, $phone, $group_id);
        $res = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res)){
            $res = $res[0];
            if($res['send_num']== self::MAX_SEND_NUM){
                if(time() - $res['created_at'] < 86400){
                    return 'SMSC_EXCEED_MAX_SEND_NUM';
                }
            }
            //注意： 当二者的定义值相等时，可不必判断验证次数，因为验证次数总是<=发送次数
            /*if(self::MAX_VERIFY_NUM == $res['verify_num']+1){
             return '已超过最大的验证次数，请注意仔细输入';
             }*/
            	
            if(time() - $res['created_at'] < self::MAX_RETRY_INTERVAL ){
                return 'SMSC_INVALID_SEND_INTERVAL';
            }
        }
    
        if(empty($res)){
            $elem = array(
                'phone'      => $phone,
                'captcha'    => $captcha,
                'created_at' => time(),
                'send_num'   => 1,
                'verify_num' => 0,
                'succeed'    => 0,
                'group_id'   => $group_id,
            );
            $sql = sprintf('INSERT INTO %s(%s) VALUES(%s)',
                $this->tbn, implode(',', array_keys($elem)),
                str_repeat('?,', count($elem)-1).'?');
            $this->db->prepare($sql)->execute(array_values($elem));
            
        }else{
            $sql = sprintf('UPDATE %s SET captcha=%s, created_at=%d,
					send_num=%d WHERE id=%d',
                $this->tbn, $captcha, time(),
                ($res['send_num'] % self::MAX_SEND_NUM ) + 1 ,
                $res['id']);
            $this->db->exec($sql);
        }
    
        return '';
    }
    
    function verify($phone, $captcha, $group){
        $group_id = self::groupid($group);
        if(0 == $group_id) return 'SMSC_INVAID_GROUP';
        
        $sql = sprintf('SELECT * FROM %s WHERE phone="%s"
				AND GROUP_ID=%d AND succeed=0',$this->tbn, $phone, $group_id);
        $res = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if(empty($res)){
            return 'SMSC_NOT_FOUND';
        }
        $res = $res[0];
        if(self::MAX_VERIFY_NUM == $res['verify_num']){
            return 'SMSC_EXCEED_MAX_VERIFY_NUM';
        }
        if($captcha != $res['captcha']){
            if(self::MAX_VERIFY_NUM == $res['verify_num']+1){
                return 'SMSC_EXCEED_MAX_VERIFY_NUM';
            }
            $this->db->exec(sprintf('UPDATE %s SET verify_num=verify_num+1 WHERE id=%d',
                $this->tbn, $res['id']));
            return 'SMSC_WRONG';
        }
        if(time() > $res['created_at']+self::CAPTCHA_EXPIRE_SEC){
            return 'SMSC_EXPIRED';
        }
        if($res['succeed']) return '';
        $ret = $this->db->exec(sprintf('UPDATE %s set succeed=1 WHERE id=%d',
            $this->tbn, $res['id']));
        return $ret > 0 ? '' : 'SMSC_DB_EXCEPTION';
    }
    
}

?>