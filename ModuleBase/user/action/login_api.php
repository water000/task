<?php 

mbs_import('', 'CUserControl', 'CUserSession');
mbs_import('common', 'CApiParamFilter');

$us = new CUserSession();
$user_info = $us->get();
if(!empty($user_info)){
	mbs_api_echo('user already exists');
	exit(1);
}

if(isset($_REQUEST['phone_num'])){
	if(!CStrTools::isValidPhone($_REQUEST['phone_num'])){
		mbs_api_echo($mbs_appenv->lang('invalid_phone_num'));
		exit(1);
	}
	if(!CStrTools::isValidPassword($_REQUEST['password'])){
		mbs_api_echo($mbs_appenv->lang('invalid_password'));
		exit(1);
	}
	
	$apf = new CApiParamFilter();
	if(!$apf->oper(array($_REQUEST['phone_num']))){
		echo $apf->getError();
		exit(1);
	}
	
	$uc = CUserControl::getInstance($mbs_appenv, CDbPool::getInstance(), CMemcachedPool::getInstance());
	$rs = null;
	try {
		$rs = $uc->search(array('phone_num'=>$_REQUEST['phone_num']));
	} catch (Exception $e) {
		mbs_api_echo('system exception');
		exit(1);
	}
	if(empty($rs)){
		mbs_api_echo($mbs_appenv->lang('invalid_phone_num'));
		exit(1);
	}
	if(!CUserControl::checkPassword($_REQUEST['password'], $rs[0]['password'])){
		mbs_api_echo($mbs_appenv->lang('invalid_password'));
		exit(1);
	}
	
	$us->set($rs[0]['id']);
	mbs_api_echo('', array('user_id'=>$rs[0]['id']));
	
}
else if(isset($_REQUEST['third_platform_id'])){
	
}


exit(0);

?>