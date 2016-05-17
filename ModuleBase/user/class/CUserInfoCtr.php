<?php
class CUserInfoCtr extends CUniqRowControl{

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
				self::$instance = new CUserInfoCtr(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('user_info'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CUserInfoCtr') : null,
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
	
	static function passwordFormat($pwd){
	    return password_hash($pwd, PASSWORD_BCRYPT);
	}
	
	static function passwordVerify($pwd, $hash){
	    return password_verify($pwd, $hash);
	}
}
?>