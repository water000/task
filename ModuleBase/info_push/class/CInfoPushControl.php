<?php

mbs_import('common', 'CMultiRowControl');

class CInfoPushControl extends CMultiRowControl {
	private static $instance = null;
	
	const ST_WAIT_PUSH = 0;
	const ST_HAD_READ  = 1;
	
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
	static function getInstance($mbs_appenv, $dbpool, $mempool, $primarykey = null){
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CInfoPushControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('info_push_event'), 'pusher_uid', $primarykey, 'id'),
						$memconn ? new CMultiRowOfCache($memconn, $primarykey, 'CInfoPushControl') : null
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$instance->setPrimaryKey($primarykey);
		
		return self::$instance;
	}
	
	static function statusText($s){
		static $map = array(
			self::ST_HAD_READ  => 'had_read',
			self::ST_WAIT_PUSH => 'wait_push',
		);
		
		return isset($map[$s])? $map[$s] : '';
	}
} 

?>