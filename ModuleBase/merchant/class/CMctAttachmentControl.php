<?php

mbs_import('common', 'CMultiRowControl');

class CMctAttachmentControl extends CMultiRowControl{
	
	private static $instance   = null;
	
	private static $thumb = array(
		'small'  => array(65,  65,  's'), // width, height, desc
		'medium' => array(180, 100, 'm'),
		'big'    => array(400, 220, 'b'),
	);

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
	
	function moveAttachment($filename){
		$name = md5(uniqid('mct_', true));
		$hash = substr($name, 0, 2);
		$subdir = 'mct/'.$hash.'/';
		$dest_dir = $appenv->mkdirUpload($subdir);
		if(false === $dest_dir){
			trigger_error('mkdirUpload error: '.$subdir, E_USER_WARNING);
			return false;
		}
		
		mbs_import('common', 'CImage');
		$dest_path = $dest_dir.$name;
		try {
			CImage::thumbnail($_FILES[$filename], $dest);
		} catch (Exception $e) {
			trigger_error('thumbnail error: '.$e->getMessage());
			return false;
		}
		return $hash.'/'.$name.'.'.CImage::THUMB_FORMAT;
	}

}


?>