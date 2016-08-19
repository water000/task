<?php
mbs_import('common', 'CMultiRowControl');

class CWalletWithdrawHistoryCtr extends CMultiRowControl {
	private static $instance = null;
	
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
				self::$instance = new CWalletWithdrawHistoryCtr(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('wallet_withdraw_history'), 'uid', $primarykey, 'id'),
						$memconn ? new CMultiRowOfCache($memconn, $primarykey, 'CWalletWithdrawHistoryCtr') : null
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