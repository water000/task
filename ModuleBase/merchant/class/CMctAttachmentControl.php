<?php

mbs_import('common', 'CMultiRowControl');

class CMctAttachmentControl extends CMultiRowControl{
	
	private static $instance   = null;

	protected function __construct($db, $cache, $primarykey = null){
		parent::__construct($db, $cache, $primarykey);
	}

	/**
	 *
	 * @param CAppEnvironment $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string product_name the name of the product
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $primarykey = null){
		
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CMctAttachmentControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								'merchant_attachment', 'merchant_id', $primarykey, 'id'),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CMctAttachmentControl') : null,
						$primarykey
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