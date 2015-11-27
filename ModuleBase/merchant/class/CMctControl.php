<?php

class CMctControl extends CUniqRowControl {
	
	private static $instance   = null;
	
	private static final $status = array(
		'verify',
		'refused',
		'pass',
		'baned'
	);
	
	static function convStatus($param){
		if(is_numeric($param))
			return isset(self::$status[$param]) ? self::$status[$param] : false;
		else 
			return array_search($param, self::$status);
	}
	
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