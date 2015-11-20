<?php

mbs_import('common', 'CMultiRowControl');

class CMctAttachmentControl extends CMultiRowControl{
	
	private static $instance   = null;
	
	private static $thumb = array(
		'small'  => array(65,  65,  's'), // width, height, desc
		'medium' => array(180, 100, 'm'),
		'big'    => array(400, 220, 'b'),
	);
	
	private static $mbs_appenv = null;

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
	
	function add($arr){
		list($src, $name) = $arr;
		$name = md5(uniqid('mct_', true));
		$hash = substr($name, 0, 2);
		$subdir = 'mct/'.$hash.'/';
		$dest_dir = self::$mbs_appenv->mkdirUpload($subdir);
		if(false === $dest_dir){
			trigger_error('mkdirUpload error: '.$subdir, E_USER_WARNING);
			return false;
		}
		$path = $hash.'/'.$name;
		
		mbs_import('common', 'CImage');
		$dest = array_values(self::$thumb);
		$dest[0][2] = $dest_dir.$path.$dest[0][2].'.'.CImage::THUMB_FORMAT;
		$dest[1][2] = $dest_dir.$path.$dest[1][2].'.'.CImage::THUMB_FORMAT;
		$dest[2][2] = $dest_dir.$path.$dest[2][2].'.'.CImage::THUMB_FORMAT;
		try {
			CImage::thumbnail($src, $dest);
			$id = parent::add(array(
				'merchant_id'  => $this->primaryKey,
				'path'         => $path,
				'name'         => $name,
				'create_time'  => time(),
				'format'       => 1,
			));
		} catch (Exception $e) {
			trigger_error('thumbnail error: '.$e->getMessage());
			return false;
		}
		return $id;
	}
	
	static function completePath($path, $type='small'){
		return $path.self::$thumb[$type][2].'.'.CImage::THUMB_FORMAT;
	}

}


?>