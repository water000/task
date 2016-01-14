<?php

require_once dirname(__FILE__).'/CMctProductLIUTB.php';

// merchant_product_latest_in_user
class CMctProductLIUControl extends CUniqRowControl{
	private static $ins = array();
	const MAX_PRODUCT_NUM = 5;

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
				self::$ins = new CMctProductLIUControl(
						new CMctProductMapTB($dbpool->getDefaultConnection(),
								mbs_tbname('merchant_product_latest_in_use'), 'merchant_id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, __CLASS__) : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		return self::$ins;
	}
	
	function set($product_id){
		$ret = $this->get();
		if(empty($ret)){
			$arr = array(
				'merchant_id' => $this->primaryKey,
				'product_list' => $product_id,
			);
			parent::add($arr);
			$list = array($product_id);
		}else{
			$list = explode(',', $ret['product_list']);
			$key = array_search($product_id, $list);
			if($key !== false){
				unset($list[$key]);
			}
			else if(self::MAX_PRODUCT_NUM == count($list)){
				array_pop($list);
			}
			array_unshift($list, $product_id);
			parent::set(array('product_list'=>implode(',', $list)));
		}
		return $list;
	}
	
	function get(){
		try {
			$ret = parent::get();
			if(!empty($ret)){
				return explode(',', $ret['product_list']);
			}
		} catch (Exception $e) {
			throw $e;
		}
		return array();
	}
	
}


?>