<?php

class CPrivGroupControl extends CUniqRowControl {
	const PRIV_TOPMOST = '*.*';
	
	private static $instance = null;
	
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
				self::$instance = new CPrivGroupControl(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('priv_group'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CPrivGroupControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$instance->setPrimaryKey($primarykey);
		
		return self::$instance;
	}
	
	static function encodePriv($mod, $action){
		return $mod.'.'.$action;
	}
	
	static function decodePriv($priv){
		return explode('.', $priv);
	}
	
	static function encodePrivList($list){
		if(self::isTopmost($list)){
			$list = array(self::PRIV_TOPMOST=>'');
		}else{
			/*ksort($list);//ascii-code(*:42, a:97, A:65, _:95)
			for(;$cur = key($list);){ // remove the element(s) like 'mod.name' when 'mod.*' appeared 
				list($mod, $action) = self::decodePriv($cur);
				if('*' == $action){
					next($list);
					while (list($k, $v) = each($list)) {
						list($nmod, $action) = self::decodePriv($k);
						if($mod == $nmod){
							unset($list[$k]);
						}else{
							break;
						}
					}
				}
			}*/
		}
		return json_encode($list);
	}
	
	static function decodePrivList($str){
		return json_decode($str, true); // return an array but an object
	}
	
	function privExists($mod, $action){
		$row = $this->get();
		if(!empty($row)){
			$list = self::decodePrivList($row['priv_list']);
			return (isset($list[$mod]) && in_array($action, $list[$mod])) || self::isTopmost($list); 
		}
		return false;
	}
	
	static function isTopmost($list){
		return isset($list['*.*']);
	}
}

?>