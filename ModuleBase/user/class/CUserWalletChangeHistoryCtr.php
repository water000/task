<?php
class CUserWalletChangeHistoryCtr extends CUniqRowControl{

	private static $instance = null;
	
	protected function __construct($db, $cache, $primarykey = null){
		parent::__construct($db, $cache, $primarykey);
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
				self::$instance = new CUserWalletChangeHistoryCtr(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('user_wallet_change_history'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CUserWalletChangeHistoryCtr') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}else {
			self::$instance->setPrimaryKey($primarykey);
		}
		return self::$instance;
	}
}
?>