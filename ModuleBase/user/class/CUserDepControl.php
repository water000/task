<?php 

class CUserDepControl extends CUniqRowControl{

	private static $instance = null;
	
	private static $DEP_MAP = array(
		'',
		'CX', //业务系统查询结果 
		'YQ', //舆情报告
		'SX'  //声像信息（视频）
	);

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
				self::$instance = new CUserDepControl(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('user_department'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CUserDepControl') : null,
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
	
	static function txt2id($txt){
		return array_search($txt, self::$DEP_MAP);
	}
}

?>