<?php

class CUserControl extends CUniqRowControl {
	
	private static $instance   = null;
	private static $searchKeys = array('phone'=>'', 'id'=>'', 'name'=>'');
	
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
				self::$instance = new CUserControl(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('user_info'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CUserControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		return self::$instance;
	}
	
	static function getSearchKeys(){
		return $searchKeys;
	}
	
	/**
	 * 
	 * @param array $keyval
	 * @throws Exception
	 * @return return the result if $keyval exists, else return NULL
	 */
	function search($keyval){
		$keyval = array_intersect_key($keyval, self::$searchKeys);
		try{
			return $this->oDB->search($keyval);
		}catch (Exception $e){
			throw $e;
		}
		return null;
	}
	
	
	static function formatPassword($pwd){
		$salt = $pwd[0].mt_rand(1000, 9999).$pwd[strlen($pwd)-1];
		return $salt . md5(md5($pwd)+$salt);
	}
	static function checkPassword($pwd, $hash){
		$salt = substr($hash, 0, 6);
		return $hash == $salt.md5(md5($pwd)+$salt);
	}
}

?>