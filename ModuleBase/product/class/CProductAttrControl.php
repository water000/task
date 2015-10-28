<?php

class CProductAttrControl extends CUniqRowControl {
	
	private static $instance   = null;
	
	private static $VT_MAP = array(
		10 => 'char',
		11 => 'varchar',
		12 => 'text',
			
		20 => 'tinyint',
		21 => 'tinyint unsigned',
		22 => 'smallint',
		23 => 'smallint unsigned',
		24 => 'int',
		25 => 'int unsigned',
		26 => 'bigint',
		27 => 'bigint unsigned',
		
		30 => 'float',
		31 => 'double',
	);
	
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
				self::$instance = new CProductAttrControl(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('product_attr_def'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CProductAttrControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$instance->setPrimaryKey($primarykey);
		return self::$instance;
	}
	
	static function vtmap($key=null){
		return is_null($key) ? self::$VT_MAP : self::$VT_MAP[$key];
	}
}

?>