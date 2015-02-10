<?php

require_once dirname(__FILE__).'/CPrivGroupControl.php';

class CPrivFilter implements IModTag{
	private $error = '';
	
	function oper($params, $tag=''){
		global $mbs_appenv, $mbs_cur_moddef;
		
		$action_def = $mbs_cur_moddef->item(CModDef::PAGES, $mbs_appenv->item('cur_action'));
		if(!empty($action_def) && isset($action_def[CModDef::P_MGR])){
			mbs_import('user', 'CUserSession');
			$us = new CUserSession();
			$user_id = $us->checkLogin();
			if(empty($user_id)){
				$this->error = $us->getError();
				return false;
			}
			
			$up = CUserPrivControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance(), $user_id);
			if(!$up->privExists($mbs_appenv->item('cur_mod'), $mbs_appenv->item('cur_action'))){
				$this->error = 'access denied';
				return false;
			}
		}
		
		return true;
	}
	
	function getError(){
		return $this->error;
	}
}

?>