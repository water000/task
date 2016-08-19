<?php 

if('send_sms' == $mbs_appenv->item('cur_action')){
    mbs_import('user', 'CUserInfoCtr');
    $user_ctr = CUserInfoCtr::getInstance($mbs_appenv,
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    $rs = $user_ctr->search(array('phone'=>$_REQUEST['phone']));
    if(empty($rs) || !($rs = $rs->fetchAll(PDO::FETCH_ASSOC))){
        return true;
    }
    $mbs_appenv->echoex($mbs_appenv->lang('phone_exists' , 'user'), 'USER_REG_PHONE_EXISTS');
    return false;
}

mbs_import('', 'CUserSession', 'CUserInfoCtr');
mbs_import('common', 'CSMSCaptcha');

define('REDIRECT_AFTER_LOGIN', isset($_REQUEST['redirect'])
    ? urldecode($_REQUEST['redirect']) : $mbs_appenv->toURL('myinfo'));

session_start();
$us = new CUserSession();
$user_info = $us->get();

if(!empty($user_info)){
    $mbs_appenv->echoex('access denied', 'USER_REG_INVALID', REDIRECT_AFTER_LOGIN);
    exit(0);
}

if(isset($_REQUEST['phone'])){
    $error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
    $uid = 0;
    if(empty($error)){
        $info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
        $info = array_intersect_key($_REQUEST,$info) + $info;
        
        $smscap = new CSMSCaptcha(CDbPool::getInstance());
        $ret = $smscap->verify($info['phone'], $info['captcha'], $info['cap_group']);
        if(!empty($ret)) $error[] = $mbs_appenv->lang($ret);
        else{
            unset($info['captcha'], $info['cap_group']);
            $info['password'] = CUserInfoCtr::passwordFormat($info['password']);
            $info['reg_ts'] = time();
            $info['reg_ip'] = $mbs_appenv->item('client_ip');
            
            $user_ctr = CUserInfoCtr::getInstance($mbs_appenv,
                CDbPool::getInstance(), CMemcachedPool::getInstance());
            try {
                $uid = $user_ctr->add($info);
            } catch (Exception $e) {
                if($mbs_appenv->config('PDO_ER_DUP_ENTRY', 'common') == $e->getCode()){
                    if(strpos($e->getMessage(), 'phone') !== false){
                        $error['phone'] = sprintf('"%s" %s', $info['phone'], $mbs_appenv->lang('existed'));
                    }else{
                        $error['email'] = sprintf('"%s" %s', $info['email'], $mbs_appenv->lang('existed'));
                    }
                }else{
                    throw $e; // throw the exception to the system handler
                }
            }
        }
    }
    if($mbs_appenv->item('client_accept') != 'html'){
        if(count($error) > 0)
            $mbs_appenv->echoex(implode(';', $error), 'USER_REG_ERR');
        else {
            $mbs_appenv->echoex(array('user_id'=>$uid));
        }
        exit(0);
    }
}

if($mbs_appenv->item('client_accept') != 'html'){
    $mbs_appenv->echoex('missing param', 'USER_REG_ERR');
    exit(0);
}

?>