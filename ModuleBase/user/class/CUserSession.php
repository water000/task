<?php

/**
 * @include $mbs_appenv, mbs_api_echo(), session 
 * @author tiger
 *
 */

class CUserSession extends CModTag {

	function __construct(){
		if('' == session_id())
			session_start();
	}
	
	function set($user_id, $userinfo=null){
		$userinfo = empty($userinfo) ? array() : $userinfo;
		$_SESSION['user_login'] = array($user_id, $userinfo);
	}

	function get(){
		return isset($_SESSION['user_login']) ? $_SESSION['user_login'] : null;
	}

	function free(){
		unset($_SESSION['user_login']);
	}
	
	/**
	 * 
	 * @param array $param('is_api'=>0/1)
	 * @return user id
	 */
	function checkLogin($param=array()){
		global $mbs_appenv;
		
		if(isset($_SESSION['user_login'])){
			return $_SESSION['user_login'][0];
		}
		
		if(isset($param['is_api'])){
			$this->error = mbs_api_echo('login first', array('force_login'=>1), true);
		}else{
			header('Location: '.$mbs_appenv->toURL('login', 'user', array('redirect'=>$_SERVER['REQUEST_URI'])));
		}
		
		return false;
	}
	
	function oper($params, $tag=''){
		switch ($tag){
			case '':
			case 'checkLogin':
				return $this->checkLogin($params);
				break;
		}
		return false;
	}
	
}

?>