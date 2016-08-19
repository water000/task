<?php 

class CImage{
	
	const THUMB_FORMAT   = 'jpeg';
	
	private $subdir = '';
	private $thumb_opt = array( // order by size desc
		'big'    => array(800, 600, 'b'), // width, height, desc
		'medium' => array(300, 120, 'm'),
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
				$im->thumbnailImage($width, $height, true);
				$im->writeImage($rename);
			}
			$im->clear();
		}catch (Exception $e){
			throw $e;
		}
	}
	
	function __construct($subdir='', $opt=null){
	    $this->subdir = $subdir.(!empty($subdir) && $subdir[strlen($subdir)-1] != '/' ? '/':'');
	    
		if(!is_null($opt))
			$this->thumb_opt = $opt;
	}
	
	function thumbnailEx($src, $save_dir){
	    $save_dir .= !empty($save_dir) && $save_dir[strlen($save_dir)-1] != '/' ? '/':'';
	    $save_dir .= $this->subdir;
	    
		$name = md5(uniqid('', true));
		$hash = substr($name, 0, 2);
		
		$dest_dir = $save_dir.$hash.'/';
		if(!file_exists($dest_dir) && !mkdir($dest_dir, '0755', true)){
		    trigger_error(__FUNCTION__.':mkdir error', E_USER_WARNING);
		    return false;
		}
		
		$dest = array_values($this->thumb_opt);
		foreach($dest as &$row){
			$row[2] = $dest_dir.$name.$row[2].'.'.self::THUMB_FORMAT;
		}
		try {
			self::thumbnail($src, $dest);
		} catch (Exception $e) {
			throw $e;
		}
		
		return $name;
	}
	
	function completePath($path, $type='medium'){
		return $this->subdir.$path[0].$path[1].'/'.$path.$this->thumb_opt[$type][2].'.'.self::THUMB_FORMAT;
	}
	
	function remove($save_dir, $path){
	    foreach($this->thumb_opt as $k=>$v){
	        unlink($save_dir.$this->completePath($path, $k));
	    }
	}
	
	static function captcha($w, $h, $text){	    
	    /* Create Imagick object */
        $Imagick = new Imagick();
        
        /* Create the ImagickPixel object (used to set the background color on image) */
        $bg = new ImagickPixel();
        
        /* Set the pixel color to white */
        $bg->setColor( 'white' );
        
        /* Create a drawing object and set the font size */
        $ImagickDraw = new ImagickDraw();
        
        /* Set font and font size. You can also specify /path/to/font.ttf */
        //$ImagickDraw->setFont( 'Helvetica Regular' );
        $ImagickDraw->setFontSize( 20 );
        
        /* Create the text 
        $alphanum = 'ABXZRMHTL23456789';
        $string = substr( str_shuffle( $alphanum ), 2, 6 );*/
        
        /* Create new empty image */
        $Imagick->newImage( $w, $h, $bg ); 
        
        /* Write the text on the image */
        $Imagick->annotateImage( $ImagickDraw, 4, 20, 0, $text );
        
        /* Add some swirl */
        $Imagick->swirlImage( 20 );
        
        /* Create a few random lines */
        $ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );
        $ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );
        $ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );
        $ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );
        $ImagickDraw->line( rand( 0, 70 ), rand( 0, 30 ), rand( 0, 70 ), rand( 0, 30 ) );
        
        /* Draw the ImagickDraw object contents to the image. */
        $Imagick->drawImage( $ImagickDraw );
        
        /* Give the image a format */
        $Imagick->setImageFormat( 'png' );
        
        /* Send headers and output the image */
        header( "Content-Type: image/{$Imagick->getImageFormat()}" );
        echo $Imagick->getImageBlob( );
	}

}

?>