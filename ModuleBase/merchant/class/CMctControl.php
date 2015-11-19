<?php

class CMctControl extends CUniqRowControl {
	
	private static $instance   = null;
	
	const ST_VERIRY = 0;
	const ST_REFUSE = 1;
	const ST_PASS   = 2;
	const ST_BAN    = 3;
	
	protected function __construct($db, $cache, $primarykey = null){
		parent::__construct($db, $cache, $primarykey);
	}
	
	/**
	 *
	 * @param CAppEnvironment $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $primarykey = null){
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CMctControl(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('merchant_info'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CMctControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$instance->setPrimaryKey($primarykey);
		return self::$instance;
	}
	
}

?>