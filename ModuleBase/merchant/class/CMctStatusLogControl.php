<?php

mbs_import('common', 'CMultiRowControl');

class CMctStatusLogControl extends CMultiRowControl{
	
	private static $instance   = null;
	private static $mbs_appenv = null;

	protected function __construct($db, $cache, $primarykey = null){
		parent::__construct($db, $cache, $primarykey);
	}

	/**
	 *
	 * @param CAppEnvironment $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string product_name the name of the product
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $primarykey = null){
		self::$mbs_appenv = $mbs_appenv;
		
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CMctStatusLogControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('merchant_status_log'), 'merchant_id', $primarykey, 'id'),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CMctStatusLogControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$instance->setPrimaryKey($primarykey);
		self::$instance->getDB()->setNumPerPage(10);
		return self::$instance;
	}
	
}


?>