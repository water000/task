<?php 

if(isset($_COOKIE['is_cookie_avaiable'])){
	setcookie('is_cookie_avaiable', '', time()-1000);
	unset($_COOKIE['is_cookie_avaiable']);
}else{
	mbs_api_echo('cookie unavaiable');
	exit(1);
}

mbs_import('', 'CUserControl', 'CUserSession');

$us = new CUserSession();
$user_info = $us->get();
if(!empty($user_info)){
	exit(1);
}

if(isset($_REQUEST['phone_num'])){
	if(!CStrTools::isValidPhone($_REQUEST['phone_num'])){
		mbs_api_echo($mbs_appenv->lang('invalid_phone_num'));
		exit(1);
	}
}
else if(isset($_REQUEST['third_platform_id'])){
	
}

?>