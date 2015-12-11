<?php

class CProductControl extends CUniqRowControl {
	
	private static $instance   = null;
	
	const MIN_ATTACH_SFX = '_min';
	const THUMB_FORMAT   = 'jpeg';
	
	
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
				self::$instance = new CProductControl(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('product_info'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CProductControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$instance->setPrimaryKey($primarykey);
		return self::$instance;
	}
	
	static function _thumbnaim_logo($src, $dest){
		try{
			$nim = $im = new imagick();
			$im->setCompressionQuality(100);
			$im->readImage($src);
			$nim->setImageFormat(self::THUMB_FORMAT);
			$nim->thumbnailImage(100, 100);
			$nim->writeImage($dest.'.'.self::THUMB_FORMAT);
			
			if($nim != $im){
				$nim->clear();
			}
			$im->clear();
		}catch (Exception $e){
			throw $e;
		}
	}
	
	static function moveLogo($src, $appenv){
		$name = md5(uniqid('logo_', true)).'.'.self::THUMB_FORMAT;
		$hash = substr($name, 0, 2);
		$subdir = 'logo/'.$hash.'/';
		$dest_dir = $appenv->mkdirUpload($subdir);
		if(false === $dest_dir){
			trigger_error('mkdirUpload error: '.$subdir, E_USER_WARNING);
			return false;
		}
		$dest_path = $dest_dir.$name;
		try {
			self::_thumbnaim_logo($src, $dest_path);
		} catch (Exception $e) {
			trigger_error('thumbnail error: '.$e->getMessage());
			return false;
		}
		return $hash.'/'.$name;
	}
	
	static function logourl($path, $appenv){
		return $appenv->uploadURL('logo/'.$path);
	}
	
	static function unlinklogo($path, $appenv){
		return unlink($appenv->uploadPath('logo/'.$path));
	}
}

?>