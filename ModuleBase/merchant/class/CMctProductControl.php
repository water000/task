<?php 

class CMctProductControl extends CMultiRowControl{
	private static $product_ins = array();
	
	protected function __construct($db, $cache, $primarykey = null){
		parent::__construct($db, $cache, $primarykey);
	}
	
	static function formatTable($product_name){
		return mbs_tbname('merchant_product_'.$product_name);
	}
	
	/**
	 *
	 * @param CAppEnvironment $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string product_name the name of the product
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $product_name, $primarykey = null){		
		if(!isset(self::$product_ins[$product_name])){
			try {
				$memconn = $mempool->getConnection();
				self::$product_ins[$product_name] = new CMctProductControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								self::formatTable($product_name), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CMctProductControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		return self::$product_ins[$product_name];
	}
	
	function createTable($field_def){
		$sql = 'CREATE TABLE IF NOT EXISTS %s(
			id int unsigned not null auto_increment,
			merchant_id int unsigned not null,
			last_edit_time int unsigned not null,
			size_start int unsigned not null,
			size_end int unsigned not null,
			inventory int unsigned not null,
			sale_num int unsigned not null,
			discount_price int unsigned not null, -- penny start
			src_price int unsigned not null, 
			%s
			primary key(id),
			key(merchant_id)
		)';
		$sql = sprintf($sql, $this->oDB->tbname(), 
				empty($field_def) ? '' : implode(',', $field_def).',');
		try {
			$this->oDB->getConnection()->exec($sql);
		} catch (Exception $e) {
			throw $e;
		}
		return true;
	}
	
	/**
	 * 
	 * @param array $del, array(field1, field2, ...)
	 * @param array $modify, array('field1'=>'sql def', ...),
	 * @param unknown $change, array('field1'=>'sql def', ...)
	 */
	function alterTable($add, $del, $modify, $change){
		try {
			foreach($add as $key => $def){
				$sql = sprintf('ALTER TABLE %s ADD %s %s', $this->oDB->tbname(), $key, $def);
				$this->oDB->exec($sql);
			}
				
			foreach($del as $key){
				$sql = sprintf('ALTER TABLE %s delete %s', $this->oDB->tbname(), $key);
				$this->oDB->exec($sql);
			}
				
			foreach($modify as $key => $def){
				$sql = sprintf('ALTER TABLE %s MODIFY %s %s', $this->oDB->tbname(), $key, $def);
				$this->oDB->exec($sql);
			}
				
			foreach($change as $key => $def){
				$sql = sprintf('ALTER TABLE %s CHANGE %s %s', $this->oDB->tbname(), $key, $def);
				$this->oDB->exec($sql);
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
}


?>