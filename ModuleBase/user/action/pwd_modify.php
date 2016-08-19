<?php 

mbs_import('', 'CUserInfoCtr', 'CUserSession');

$usess = new CUserSession();
list($sess_uid, ) = $usess->get();

$user_ctr = CUserInfoCtr::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);
$info = $user_ctr->get();

if(isset($_REQUEST['pwd1'])){
	$err = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	if(empty($err)){
		if($_REQUEST['pwd1'] != $_REQUEST['pwd2']){
			$mbs_appenv->echoex($mbs_appenv->lang('pwd_diff_or_error'), 'USER_MYINFO_PWD_DIFF');
			exit(0);
		}
		if(!CUserInfoCtr::passwordVerify($_REQUEST['src_pwd'], $info['password'])){
		    $mbs_appenv->echoex($mbs_appenv->lang('incorrect_password'), 'USER_MYINFO_PWD_INCORRECT');
		    exit(0);
		}
		if(CUserInfoCtr::passwordVerify($_REQUEST['pwd1'], $info['password'])){
		    $mbs_appenv->echoex($mbs_appenv->lang('src_pwd_equal_new'), 'USER_MYINFO_PWD_EQUAL');
		    exit(0);
		}
		$info['password'] = CUserInfoCtr::passwordFormat($_REQUEST['pwd1']);
		$info['pwd_modify_count'] = 1;
		$user_ctr->set($info);
		$mbs_appenv->echoex(null);
		
	}else{
		$mbs_appenv->echoex(implode(';', $err), 'USER_MYINFO_REQ_INVALID');
	}
}
else{
    $mbs_appenv->echoex('missing param', 'MISSING_PARAM');
}
?>