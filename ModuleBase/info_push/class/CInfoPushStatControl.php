<?php

require_once dirname(__FILE__).'/CInfoPushStatDB.php';

class CInfoPushStatControl extends CUniqRowControl {
	
	private static $instance   = null;
	
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
				self::$instance = new CInfoPushStatControl(
						new CInfoPushStatDB($dbpool->getDefaultConnection(),
								mbs_tbname('info_push_stat'), 'info_id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CInfoPushStatControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		
		return self::$instance;
	}
	
}

?>