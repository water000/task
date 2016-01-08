<?php

class CMctProductMapControl extends CMultiRowControl{
	private static $ins = array();

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
		if(empty(self::$ins)){
			try {
				$memconn = $mempool->getConnection();
				self::$ins = new CMctProductMapControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('merchant_product_map'), 'merchant_id', $primarykey, 'product_id'),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, __CLASS__) : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		return self::$ins;
	}
	
}


?>