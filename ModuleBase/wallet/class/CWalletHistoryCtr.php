<?php
mbs_import('common', 'CMultiRowControl');

class CWalletHistoryCtr extends CMultiRowControl {
	private static $instance = null;
	
	private static $type_map = array(
	   'TASK_PAY',
	   'WITHDRAW',
	   'RECHARGE'
	);
	
	static function tpconv($tp){
	    if(is_numeric($tp)) return isset(self::$type_map[$tp]) ? self::$type_map[$tp] : false;
	    else if(is_string($tp)) return array_search($tp, self::$type_map);
	    else return self::$type_map;
	}
	
	protected function __construct($db, $cache, $primarykey = null, $secondKey = null){
		parent::__construct($db, $cache, $primarykey, $secondKey);
	}
	
	/**
	 *
	 * @param CAppEnv $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $primarykey = null){
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CWalletHistoryCtr(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('wallet_history'), 'a_uid', $primarykey, 'id'),
						$memconn ? new CMultiRowOfCache($memconn, $primarykey, 'CWalletHistoryCtr') : null
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