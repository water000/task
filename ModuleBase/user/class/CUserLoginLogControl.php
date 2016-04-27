<?php 

require_once dirname(__FILE__).'/CUserLoginLogTB.php';

class CUserLoginLogControl extends CUniqRowControl{
	private static $instance   = null;
	
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
				self::$instance = new CUserLoginLogControl(
						new CUserLoginLogTB($dbpool->getDefaultConnection(),
								mbs_tbname('user_login_log'), 'user_id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CUserLoginLogControl') : null,
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