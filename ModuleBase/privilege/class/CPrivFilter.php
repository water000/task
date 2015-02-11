<?php

/**
 *@depend user.CUserSession, privilege.CUserPrivControl
 */

require_once dirname(__FILE__).'/CPrivGroupControl.php';

class CPrivFilter extends CModTag{
	
	function oper($params, $tag=''){
		global $mbs_appenv;
		
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
		
		return true;
	}

}

?>