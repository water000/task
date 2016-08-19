<?php 

if('send_sms' == $mbs_appenv->item('cur_action')){
    mbs_import('user', 'CUserInfoCtr');
    $user_ctr = CUserInfoCtr::getInstance($mbs_appenv,
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    $rs = $user_ctr->search(array('phone'=>$_REQUEST['phone']));
    if(empty($rs) || !($rs = $rs->fetchAll(PDO::FETCH_ASSOC))){
        $mbs_appenv->echoex( $mbs_appenv->lang('phone_not_exists', 'user') , 'USER_PWD_RESET_PHONE_NOT_FOUND');
        return false;
    }
    return true;
}

mbs_import('', 'CUserSession', 'CUserInfoCtr');
mbs_import('common', 'CSMSCaptcha');

session_start();
$us = new CUserSession();
$user_info = $us->get();
if(!empty($user_info)){
    $mbs_appenv->echoex('access denied, already login', 'USER_PWD_RESET_INVALID');
    exit(0);
}

if(isset($_REQUEST['phone'])){
    $error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
    if(empty($error)){
        $user_ctr = CUserInfoCtr::getInstance($mbs_appenv,
            CDbPool::getInstance(), CMemcachedPool::getInstance());
        $rs = $user_ctr->search(array('phone'=>$_REQUEST['phone']));
        if(empty($rs) || !($rs = $rs->fetchAll(PDO::FETCH_ASSOC))){
            $mbs_appenv->echoex( $mbs_appenv->lang('invalid_phone') , 'USER_PWD_RESET_PHONE_NOT_FOUND');
            exit(1);
        }
        
        $smscap = new CSMSCaptcha(CDbPool::getInstance());
        $ret = $smscap->verify($_REQUEST['phone'], $_REQUEST['captcha'], $_REQUEST['cap_group']);
        if(!empty($ret)) $error[] = $mbs_appenv->lang($ret);
        else{
            if(CUserInfoCtr::passwordVerify($_REQUEST['password'], $rs[0]['password'])){
                $error[] = $mbs_appenv->lang('src_pwd_equal_new');
            }else{
                $user_ctr->setPrimaryKey($rs[0]['id']);
                $rs[0]['password'] = CUserInfoCtr::passwordFormat($_REQUEST['password']);
                $user_ctr->set($rs[0]);
            }
        }
    }
    
    if($mbs_appenv->item('client_accept') != 'html'){
        if(count($error) > 0)
            $mbs_appenv->echoex(implode(';', $error), 'USER_PWD_RESET_ERR');
        else {
            $mbs_appenv->echoex(null);
        }
        exit(0);
    }
}

if($mbs_appenv->item('client_accept') != 'html'){
    $mbs_appenv->echoex('missing param', 'USER_REG_ERR');
    exit(0);
}

?>