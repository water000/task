<?php

class CUserSession implements IModTag {
	
	private $error = '';
	
	function __construct(){
		session_start();
	}
	
	function set($user_id, $userinfo=null){
		$userinfo = empty($userinfo) ? array() : $userinfo;
		$userinfo['id'] = $user_id;
		$_SESSION['user_login'] = $userinfo;
	}

	function get(){
		return isset($_SESSION['user_login']) ? $_SESSION['user_login'] : null;
	}
	
	/**
	 * 
	 * @param array $param('is_api'=>0/1)
	 * @return unknown
	 */
	function checkLogin($param=array()){
		global $mbs_appenv;
		
		if(isset($_SESSION['user']['id'])){
			return $_SESSION['user']['id'];
		}
		
		if(isset($param['is_api'])){
			echo json_encode(array('success'=>0, 'msg'=>'', 'force_login'=>1));
		}else{
			header('Location: '.$mbs_appenv->toURL('login', 'user'));
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

	function getError(){
		return $this->error;
	}
	
}

?>