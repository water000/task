<?php 

mbs_import('common', 'CMultiRowControl');

class CInfoCommentControl extends CMultiRowControl {
	private static $instance = null;

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
				self::$instance = new CInfoCommentControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('info_comment'), 'info_id', $primarykey, 'id'),
						$memconn ? new CMultiRowOfCache($memconn, $primarykey, 'CInfoCommentControl') : null
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