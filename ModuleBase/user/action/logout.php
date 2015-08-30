<?php 

session_start();
if(session_id() != ''){
	session_destroy();
	
	$p = session_get_cookie_params();
	setcookie(session_name(), '', time() - 1800, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
	$mbs_appenv->echoex($mbs_appenv->lang('logout_succeed'),
			'', $mbs_appenv->toURL('login', 'user'));
}


?>