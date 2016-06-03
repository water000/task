<?php 

mbs_import('', 'CUserSession', 'CUserInfoCtr');
mbs_import('common', 'CImage');

define('REDIRECT_AFTER_LOGIN', isset($_REQUEST['redirect'])
    ? urldecode($_REQUEST['redirect']) : $mbs_appenv->toURL('myinfo'));

session_start();

$us = new CUserSession();
$user_info = $us->get();

if(!empty($user_info)){
    $mbs_appenv->echoex('invalid visit', 'USER_REG_INVALID', REDIRECT_AFTER_LOGIN);
    exit(0);
}

$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
if(empty($error)){
    $info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
    $info = array_intersect_key($_REQUEST,$info) + $info;
    $info['password'] = CUserInfoCtr::passwordFormat($info['password']);
    $info['reg_ts'] = time();
    $info['reg_ip'] = $mbs_appenv->item('client_ip');
    if(isset($_FILES['avatar'])){
        $img_thumb = new CImage(CUserInfoCtr::USER_AVATAR_SUBDIR);
        if(UPLOAD_ERR_OK == $_FILES['avatar']['error']){
            $img = array($_FILES['avatar']['tmp_name'], $_FILES['avatar']['name']);
            $path = $img_thumb->thumbnailEx($mbs_appenv, $img);
            if(false === $path){
                $error['avatar'] = 'thubmnail avater error';
            }else{
                $info['avatar_path'] = $path;
            }
        }else if($_FILES['avatar']['error'] != UPLOAD_ERR_NO_FILE){
            $error['avatar'] = $mbs_appenv->lang($_FILES['avatar']['error']);
        }
    }
    
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
            throw $e; // throw the exception to the system center handler
        }
    }
}
if($mbs_appenv->item('client_accept') != 'html'){
    if(count($error) > 0)
        $mbs_appenv->echoex($error, 'MCT_EDIT_ERROR');
    else {
        $mbs_appenv->echoex(array('user_id'=>$uid));
    }
    exit(0);
}

?>