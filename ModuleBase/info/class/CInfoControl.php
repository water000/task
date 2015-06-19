<?php

class CInfoControl extends CUniqRowControl {
	
	private static $instance   = null;
	private static $appenv     = null;
	
	const AT_TXT = 1;
	const AT_VDO = 2;
	const AT_IMG = 3;
	const AT_PDF = 4;
	private static $ATYPE_MAP = array(
		self::AT_TXT => 'TXT',
		self::AT_VDO => 'VDO',
		self::AT_IMG => 'IMG',
	);
	
	const MIN_ATTACH_SFX = '_min';
	const THUMB_HEIGHT   = 80;
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
	
	static function txt2type($txt){
		return array_search($txt, self::$ATYPE_MAP);
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
			case 'jpeg':
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
			case 'pdf':
				$type = self::AT_PDF;
				break;
		}
		
		return $type;
	}

	static function rename(){
		$n = mt_rand(0, 32);
		$subdir = ($n < 16 ? '0' : '') . dechex($n);
		
		return array($subdir, md5(uniqid('pfx_', true)));
	}
	
	static function _thumbnail($src, $dest, $is_video=false){
		try{
			$nim = $im = new imagick();
			$im->setCompressionQuality(100);
			$im->readImage($src);
			if($is_video){
				foreach($im as $k => $sub){
					$nim = $sub;
					break;
				}
			}
			$nim->setImageFormat(self::THUMB_FORMAT);
			$nim->thumbnailImage(0, self::THUMB_HEIGHT);
			$nim->writeImage($dest);
			if($nim != $im){
				$nim->clear();
			}
			$im->clear();
		}catch (Exception $e){
			throw $e;
		}
	}
	
	static function moveAttachment($filename, &$format, $appenv=null){
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
		$dest_path = $dest_dir.$name;
	
		switch ($format){
		case self::AT_IMG:
		case self::AT_VDO:
			if(!move_uploaded_file($_FILES[$filename]['tmp_name'], $dest_path)){
				trigger_error('Failed to move upload file');
				return false;
			}
			if($format == self::AT_IMG){
				self::_thumbnail($dest_path, $dest_path.self::MIN_ATTACH_SFX, $format == self::AT_VDO);
			}
			/*$src = $_FILES[$filename]['tmp_name'].urlencode($_FILES[$filename]['name']);
			rename($_FILES[$filename]['tmp_name'], $src);
			
			//try{
				self::_thumbnail($src, $dest_path.self::MIN_ATTACH_SFX, $format == self::AT_VDO);
			//}catch (Exception $e){
			//	trigger_error('unsupport file type!'.$e->getMessage());
			//}
			
			rename($src, $dest_path);
			*/
			
			break;
		case self::AT_TXT:
			$format = self::AT_IMG;
			
			$src = $_FILES[$filename]['tmp_name'].urlencode($_FILES[$filename]['name']);
			rename($_FILES[$filename]['tmp_name'], $src);
			try{
				self::word2png_jod($src, $dest_path);
			}catch (Exception $e){
				throw $e;
			}
			unset($src);
			
			break;
		
		case self::AT_PDF:
			$format = self::AT_IMG;
			try{
				self::pdf2img($_FILES[$filename]['tmp_name'], $dest_path);
			}catch (Exception $e){
				throw $e;
			}
			break;
		}
		
		return $subdir.'/'.$name;
	}
	
	static function word2png_jod($src, $dest){
		$cmd = 'java -jar /opt/jodconverter-2.2.2/lib/jodconverter-cli-2.2.2.jar %s %s';
		$pdf = $src.'.pdf';
		exec (sprintf($cmd, $src, $pdf), $ret);
		try {
			self::pdf2img($pdf, $dest);
		} catch (Exception $e) {
			throw $e;
		}
		
		unlink($pdf);
	}
	
	static function pdf2img($src, $dest){
		try {
			$im = new imagick();
			$im->setCompressionQuality(100);
			$im->readImage($src);
		
			$canvas = new imagick();
			foreach($im as $k => $sub){
				$sub->setImageFormat(self::THUMB_FORMAT);
				$sub->stripImage();
				//$sub->trimImage(0);
				
				$canvas->newImage($sub->getImageWidth()+10, 
					$sub->getImageHeight()+10+($k+1 == $im->getNumberImages() ? 10 : 0), 
					'gray');
				$canvas->compositeImage($sub, Imagick::COMPOSITE_COPY, 5, 5);
				
				if(0 == $k){
					$sub->thumbnailImage(0, self::THUMB_HEIGHT);
					$sub->writeImage($dest.self::MIN_ATTACH_SFX);
				}
			}
			
			$canvas->resetIterator();
			$nimg = $canvas->appendImages(true);
			$nimg->setImageFormat(self::THUMB_FORMAT);
			$nimg->writeImage($dest);
			
			
			$nimg->clear();
			$im->clear();
			$canvas->clear();
			
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	static function word2png_com($src, $dest){
		if(!class_exists('COM')){
			trigger_error('class "COM" not exists', E_USER_WARNING);
			return false;
		}
		
		//set_time_limit(0);
		$word = null;
		
		try {
			$word = new COM("word.application");
			$word->DisplayAlerts = 0;
			$ret = $word->Documents->Open($src);
			var_dump($src, $ret);
			if($ret){
				//var_dump($ret);
				$word->ActiveDocument->ExportAsFixedFormat($dest.'.pdf', 
						17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
			}
		} catch (Exception $e) {
			echo iconv('gbk', 'utf-8', $e->getMessage().':'.$e->getLine());
		}
		if($word){
			$word->Quit();
			unset($word);
		}
	}
	
}

?>