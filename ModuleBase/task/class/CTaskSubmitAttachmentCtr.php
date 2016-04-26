<?php
mbs_import('common', 'CMultiRowControl');

class CTaskSubmitAttachmentCtr extends CMultiRowControl {
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
				self::$instance = new CUserDepMemberControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('task_submit_attachment'), 'submit_id', $primarykey, 'id'),
						$memconn ? new CMultiRowOfCache($memconn, $primarykey, 'CTaskSubmitAttachmentCtr') : null
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