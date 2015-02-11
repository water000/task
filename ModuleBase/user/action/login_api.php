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
	
	$uc = new CUserControl(CDbPool::getInstance(), CMemcachedPool::getInstance());
	$rs = $uc->search(array('phone_num'=>$_REQUEST['phone_num']))
}
else if(isset($_REQUEST['third_platform_id'])){
	
}


exit(0);

?>