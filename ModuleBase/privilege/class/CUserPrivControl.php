<?php

class CUserPrivControl extends CUniqRowControl {
	private static $instance = null;
	
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
			$memconn = $mempool->getConnection();
			self::$instance = new CUserPrivControl(
					new CUniqRowOfTable($dbpool->getDefaultConnection(),
							mbs_tbname('user_priv'), 'user_id', $primarykey),
					$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CUserPrivControl') : null,
					$primarykey
			);
		}
		return self::$instance;
	}
}

?>