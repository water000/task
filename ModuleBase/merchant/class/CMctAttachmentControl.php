<?php

mbs_import('common', 'CMultiRowControl', 'CImage');

class CMctAttachmentControl extends CMultiRowControl{
	
	private static $instance   = null;
	
	private static $mbs_appenv = null;
	
	private static $imgthumb = null;

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
		self::$mbs_appenv = $mbs_appenv;
		
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CMctAttachmentControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('merchant_attachment'), 'merchant_id', $primarykey, 'id'),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CMctAttachmentControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
			self::$imgthumb = new CImage('mct/');
		}
		self::$instance->setPrimaryKey($primarykey);
		return self::$instance;
	}
	
	function addNode(&$arr, $pos=null){
		try {
			$path = self::$imgthumb->thumbnailEx(self::$mbs_appenv, $arr);
			$arr = array(
				'merchant_id'  => $this->primaryKey,
				'path'         => $path,
				'name'         => $arr[1],
				'create_time'  => time(),
				'format'       => 1,
			);
			parent::addNode($arr);
		} catch (Exception $e) {
			throw $e;
		}
		return $arr['id'];
	}
	
	static function completePath($path, $type='small'){
		return self::$imgthumb->completePath($path, $type);
	}

}


?>