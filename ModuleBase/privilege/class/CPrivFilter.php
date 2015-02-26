<?php

/**
 *@depend user.CUserSession, privilege.CPrivUserControl
 */

require_once dirname(__FILE__).'/CPrivGroupControl.php';
require_once dirname(__FILE__).'/CPrivUserControl.php';

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
		
		$priv_info = null;
		try {
			$pu = CPrivUserControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
			$priv_info = $pu->getDB()->search(array('user_id' => $user_id));
		} catch (Exception $e) {
			$this->error = $mbs_appenv->lang('db_exception', 'common');
			return false;
		}
		if(empty($priv_info)){
			$this->error = 'access denied';
			return false;
		}
		$priv_info = $priv_info[0];
		
		try {
			$pg = CPrivGroupControl::getInstance($mbs_appenv, CDbPool::getInstance(), 
					CMemcachedPool::getInstance(), $priv_info['priv_group_id']);
			if(!$pg->privExists($mbs_appenv->item('cur_mod'), $mbs_appenv->item('cur_action'))){
				$this->error = 'access denied';
				return false;
			}
		} catch (Exception $e) {
			$this->error = $mbs_appenv->lang('db_exception', 'common');
			return false;
		}

		return true;
	}

}

?>