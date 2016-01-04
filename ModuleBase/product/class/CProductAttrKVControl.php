<?php

mbs_import('common', 'CMultiRowControl');

class CProductAttrKVControl extends CMultiRowControl {
	private static $instance = null;
	
	const KEY_PID = 0;
	
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
				self::$instance = new CProductAttrKVControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('product_attr_kv'), 'kid', $primarykey, 'id'),
						$memconn ? new CMultiRowOfCache($memconn, $primarykey, __CLASS__) : null
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$instance->setPrimaryKey($primarykey);
		
		return self::$instance;
	}
	
	function key($id){
		$this->setPrimaryKey(self::KEY_PID);
		$this->setSecondKey($id);
		
		try {
			return $this->getNode();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	function keys(){
		$this->setPrimaryKey(self::KEY_PID);
		
		try {
			return $this->get();
		} catch (Exception $e) {
			throw $e;
		}
	}
}

?>