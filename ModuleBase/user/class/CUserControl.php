<?php

class CUserControl extends CUniqRowControl {
	
	private static $instance   = null;
	private static $searchKeys = array('phone_num'=>'');
	
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
			$memconn = $mempool->getConnection();
			self::$instance = new CUserControl(
					new CUniqRowOfTable($dbpool->getDefaultConnection(),
							mbs_tbname('user_info'), 'id', $primarykey),
					$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CUserControl') : null,
					$primarykey
			);
		}
		return self::$instance;
	}
	
	static function getSearchKeys(){
		return $searchKeys;
	}
	
	static function search($keyval){
		$keyval = array_intersect_key($keyval, self::$searchKeys);
		$sql = sprintf('SELECT * FROM %s WHERE '.implode('=?,', array_keys($keyval)));
		try{
			$pdos = $this->oDB->getConnection()->prepare($sql);
			$pdos->execute(array_values($keyval));
			return $pdos->fetchAll();
		}catch (Exception $e){
			throw $e;
		}
		return null;
	}
	
}

?>