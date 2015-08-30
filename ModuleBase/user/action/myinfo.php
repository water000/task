<?php 

mbs_import('', 'CUserControl', 'CUserSession');

$usess = new CUserSession();
list($sess_uid, ) = $usess->get();

$user_ins = CUserControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);

if(isset($_REQUEST['pwd1'])){
	$err = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	if(empty($err)){
		$uinfo = $user_ins->get();
		if(!CUserControl::checkPassword($_REQUEST['src_pwd'], $uinfo['password'])){
			$mbs_appenv->echoex($mbs_appenv->lang('invalid_password'), 'USER_MYINFO_PWD_INCORRECT');
			exit(0);
		}
		if($_REQUEST['pwd1'] != $_REQUEST['pwd2']){
			$mbs_appenv->echoex($mbs_appenv->lang('pwd_diff_or_error'), 'USER_MYINFO_PWD_DIFF');
			exit(0);
		}
		$req_args['password'] = CUserControl::formatPassword($_REQUEST['pwd1']);
		$req_args['pwd_modify_count'] = 1;
		$user_ins->set($req_args);
		$mbs_appenv->echoex(null);
	}else{
		$mbs_appenv->echoex(implode(';', $err), 'USER_MYINFO_REQ_INVALID');
	}
	exit(0);
}

echo $mbs_appenv->echoex($user_ins->get());
?>