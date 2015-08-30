<?php

mbs_import('common', 'CMultiRowControl');

class CPrivUserControl extends CMultiRowControl {
	private static $instance = null;
	
	protected function __construct($db, $cache, $primarykey = null, $secondKey = null){
		parent::__construct($db, $cache, $primarykey, $secondKey);
	}
	
	/**
	 *
	 * @param CAppEnvironment $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $priv_group_id = null){
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CPrivUserControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('priv_user'), 'priv_group_id', $priv_group_id, 'user_id'),
						$memconn ? new CMultiRowOfCache($memconn, $priv_group_id, 'CPrivUserControl') : null
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$instance->setPrimaryKey($priv_group_id);
		
		return self::$instance;
	}
}

?>