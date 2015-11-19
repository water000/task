<?php 

class CImage{
	
	const THUMB_FORMAT   = 'jpeg';
	
	/**
	 *
	 * @param string $src, src path to read
	 * @param unknown $dest, [[$width, $height, $rename], ...], $rename NOT include suffix
	 * @throws Exception
	 */
	static function thumbnail($src, $dest){
		try{
			$nim = $im = new imagick();
			$im->setCompressionQuality(100);
			$im->readImage($src);
			$nim->setImageFormat(self::THUMB_FORMAT);
			foreach($dest as $arr){
				list($width, $height, $rename) = $arr;
				$nim->thumbnailImage($width, $height);
				$nim->writeImage($rename);
			}
	
			if($nim != $im){
				$nim->clear();
			}
			$im->clear();
		}catch (Exception $e){
			throw $e;
		}
	}

}

?>