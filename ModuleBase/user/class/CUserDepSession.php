<?php

/**
 * @include $mbs_appenv, session 
 * @author tiger
 *
 */

class CUserDepSession extends CModTag {

	function __construct(){
		if('' == session_id())
			session_start();
	}
	
	function set($dep_id, $dep_info=null){
		$dep_info = empty($dep_info) ? array() : $dep_info;
		$_SESSION['user_dep_login'] = array($dep_id, $dep_info);
	}

	function get(){
		return isset($_SESSION['user_dep_login']) ? $_SESSION['user_dep_login'] : null;
	}

	function free(){
		unset($_SESSION['user_dep_login']);
	}
	
	function checkLogin(){
		global $mbs_appenv;
		
		if(isset($_SESSION['user_dep_login'])){
			return $_SESSION['user_dep_login'][0];
		}
		
		$mbs_appenv->echoex($mbs_appenv->lang('login_first', 'user'), 'USER_NOT_LOGIN_DEP', 
				$mbs_appenv->toURL('dep_login', 'user', array('redirect'=>$_SERVER['REQUEST_URI'])));
		
		return false;
	}
	
	function oper($params, $tag=''){
		switch ($tag){
			case '':
			case 'checkDepLogin':
				return $this->checkLogin($params);
				break;
		}
		return false;
	}
	
}

?>