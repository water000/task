<?php

class CUserSyncIM{ 
    
    static function _upload_avater($host, $req_path, $ava_path){
        $ava_cnt = file_get_contents($ava_path);
        $errno = 0;
        $errstr = '';
        $sock = fsockopen($host, 80, $errno, $errstr);
        if($sock){
            $header = "POST %s HTTP/1.1\r\nHOST: %s\r\nContent-Type: image/jpeg\r\nContent-Length: %d\r\nConnection: Close\r\n\r\n";
            fwrite($sock, sprintf($header, $req_path, $host, strlen($ava_cnt)));
            fwrite($sock, $ava_cnt);
            $ret = fread($sock, 4096);
            if($ret !== false){
                $pos = strpos($ret, "\r\n\r\n");
                if($pos !== false){
                    $ret = substr($ret, $pos+3);
                    $p0 = strpos($ret, '{');
                    $p1 = strrpos($ret, '}');
                    $ret = substr($ret, $p0, $p1-$p0+1);
                    return $ret;
                }
            }
            fclose($sock);
        }
     }
    
    
    static function add($user, $ch, $opts){
        global $mbs_appenv;
        
        $params = array(
            'id'       => $user['id'],
            'phone'    => $_REQUEST['phone'],
            'password' => $_REQUEST['password'],
            '_ts'      => time(),
        );
        $params['_key'] = md5(md5($params['_ts']).$params['id']);
        
        $opts[CURLOPT_POSTFIELDS] = $params;
        $opts[CURLOPT_URL] .= '/sync_user/syncadd';
        curl_setopt_array($ch, $opts);
        $ret = curl_exec($ch);
        if($ret != 'success'){
            throw new Exception(sprintf('user.syncadd:%s, error: %s', json_encode($params), $ret), '000');
        }
    }
    
    static function edit($diff, $ch, $opts){
        $im_user = array(
            'id'       => $diff['id'],
            'sex'      => isset($diff['gender']) ? $diff['gender'] : '',
            'nick'     => isset($diff['name']) ? $diff['name'] : '',
            'email'    => isset($diff['email']) ? $diff['email'] : '',
            '_ts'      => time(),
        );
        $im_user['_key'] = md5(md5($im_user['_ts']).$im_user['id']);
        if(isset($diff['password'])){
            $im_user['password'] = isset($_REQUEST['pwd1']) ? $_REQUEST['pwd1'] : $_REQUEST['password'];
        }
        
        if(isset($_FILES['avatar']) && UPLOAD_ERR_OK == $_FILES['avatar']['error']){
            $ret = self::_upload_avater(str_replace(array('http://', '/'), array('', ''), $opts[CURLOPT_URL]), 
                '/sync_user/upload?filename='.urlencode($_FILES['avatar']['name'])
                .'&id='.$im_user['id'].'&_ts='.$im_user['_ts'].'&_key='.$im_user['_key'], 
                $_FILES['avatar']['tmp_name']);
            $jret = json_decode($ret, 1);
            if('success' == $jret['status']){
                $im_user['avatar'] = $jret['real_path'];
            }else{
                mbs_error_log(E_USER_WARNING, 'im_user_upload_err:'.$ret, __FILE__, __LINE__);
            }
        }
        
        $opts[CURLOPT_POSTFIELDS] = $im_user;
        $opts[CURLOPT_URL] .= '/sync_user/syncedit';
        curl_setopt_array($ch, $opts);
        $ret = curl_exec($ch);
        if($ret != 'success'){
            throw new Exception(sprintf('user.syncedit:%s, error: %s', json_encode($im_user), $ret), '000');
        }
    }
    
    static function response($ev, $args){
        global $mbs_appenv;
        
        $defaults = array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_URL => $mbs_appenv->config('im_user_sync_host', 'user'),
        );
        $ch = curl_init();
        $host = $mbs_appenv->config('im_user_sync_host', 'user');
        
        switch ($ev){
            case 'user.CUserInfoCtr.add':
                self::add($args, $ch, $defaults);
                break;
            case 'user.CUserInfoCtr.set':
                self::edit($args, $ch, $defaults);
                break;
        }
    }
}

?>