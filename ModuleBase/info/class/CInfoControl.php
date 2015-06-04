<?php

class CInfoControl extends CUniqRowControl {
	
	private static $instance   = null;
	private static $appenv     = null;
	
	const AT_TXT = 1;
	const AT_VDO = 2;
	const AT_IMG = 3;
	private static $ATYPE_MAP = array(
		self::AT_TXT => 'TXT',
		self::AT_VDO => 'VDO',
		self::AT_IMG => 'IMG',
	);
	
	const MIN_ATTACH_SFX = '_min';
	
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
				self::$instance = new CInfoControl(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('info'), 'id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CInfoControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$appenv = $mbs_appenv;
		
		return self::$instance;
	}
	
	static function getTypeMap(){
		return self::$ATYPE_MAP;
	}

	static function type2txt($type){
		return isset(self::$ATYPE_MAP[$type]) ? self::$ATYPE_MAP[$type] : '';
	}
	
	static function typeExists($type){
		return isset(self::$ATYPE_MAP[$type]);
	}
	
	static function getAttachType($filename){
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$type = 0;
		switch ($ext){
			case 'doc':
			case 'docx':
				$type = self::AT_TXT;
				break;
			case 'gif':
			case 'jpg':
			case 'png':
			case 'swf':
			case 'swc':
			case 'psd':
			case 'tiff':
			case 'bmp':
			case 'iff':
			case 'jp2':
			case 'wbmp':
				$type = self::AT_IMG;
				break;
			case 'mp4':
			case 'avi':
			case 'mov':
			case 'asf':
			case 'wmv':
			case 'navi':
			case '3gp':
			case 'ram':
			case 'mkv':
			case 'flv':
				$type = self::AT_VDO;
				break;
		}
		
		return $type;
	}

	static function rename(){
		$n = mt_rand(0, 32);
		$subdir = ($n < 16 ? '0' : '') . dechex($n);
		
		return array($subdir, md5(uniqid('pfx_', true)));
	}
	
	static function moveAttachment($filename, $type, $appenv=null){
		$appenv = empty($appenv) ? self::$appenv : $appenv;
		if(empty($appenv)){
			trigger_error('empty appenv: '.$subdir, E_USER_WARNING);
			return false;
		}
		
		list($subdir, $name) = self::rename();
		$dest_dir = $appenv->mkdirUpload($subdir);
		if(false === $dest_dir){
			trigger_error('mkdirUpload error: '.$subdir, E_USER_WARNING);
			return false;
		}
	
		$new_width = $new_height = 50;
		list($width, $height, $type) = getimagesize($_FILES[$filename]['tmp_name']);
		
		$ext = substr(image_type_to_extension($type), 1);
		if($width > $new_width && !function_exists('imagecreatefrom'.$ext)){
			trigger_error('unsupported image type: '.$ext);
			return false;
		}

		$dest_path = $dest_dir.$name;
		if(!move_uploaded_file($_FILES[$filename]['tmp_name'], $dest_path)){
			trigger_error('move upload file error');
			return false;
		}
		
		if($width > $new_width){
			$image_p = imagecreatetruecolor($new_width, $new_height);
			$image = call_user_func('imagecreatefrom'.$ext, $dest_path);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			call_user_func('image'.$ext, $image_p, $dest_path.self::MIN_ATTACH_SFX, 100);
		}
		
		return $subdir.'/'.$name;
	}
}

?>