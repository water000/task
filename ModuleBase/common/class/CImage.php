<?php 

class CImage{
	
	const THUMB_FORMAT   = 'jpeg';
	
	private $subdir = '';
	private $thumb_opt = array( // order by size desc
		'big'    => array(400, 220, 'b'), // width, height, desc
		'medium' => array(180, 100, 'm'),
		'small'  => array(65,  65,  's'),
	);
	
	/**
	 *
	 * @param string $src, src path to read
	 * @param unknown $dest, [[$width, $height, $rename], ...], $rename NOT include suffix
	 * @throws Exception
	 */
	static function thumbnail($src, $dest){
		try{
			$im = new imagick();
			$im->setCompressionQuality(75);
			$im->readImage($src);
			foreach($dest as $arr){
				list($width, $height, $rename) = $arr;
				$im->thumbnailImage($width, $height);
				$im->writeImage($rename);
			}
			$im->clear();
		}catch (Exception $e){
			throw $e;
		}
	}
	
	function __construct($subdir='', $opt=null){
		$this->subdir = $subdir;
		if(!is_null($opt))
			$this->thumb_opt = $opt;
	}
	
	function thumbnailEx($mbs_appenv, $arr){
		list($src, $filename) = $arr;
		$subdir = $this->subdir;
		$name = md5(uniqid($subdir, true));
		$hash = substr($name, 0, 2);
		$subdir = $subdir.$hash.'/';
		$dest_dir = $mbs_appenv->mkdirUpload($subdir);
		if(false === $dest_dir){
			trigger_error('mkdirUpload error: '.$subdir, E_USER_WARNING);
			return false;
		}
		$path = $hash.'/'.$name;
		
		$dest = array_values($this->thumb_opt);
		foreach($dest as &$row){
			$row[2] = $dest_dir.$name.$row[2].'.'.self::THUMB_FORMAT;
		}
		try {
			self::thumbnail($src, $dest);
		} catch (Exception $e) {
			throw $e;
		}
		
		return $path;
	}
	
	function completePath($path, $type='small'){
		return $this->subdir.$path.$this->thumb_opt[$type][2].'.'.self::THUMB_FORMAT;
	}

}

?>