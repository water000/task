<?php 

session_start();
if(session_id() != ''){
	session_destroy();
	setcookie(ini_get('session.name'), '', time()-1800);

	$mbs_appenv->echoex($mbs_appenv->lang('logout_succeed'),
			'', $mbs_appenv->toURL('login', 'user'));
}


?>