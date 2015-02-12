<?php 
/**
 * 
 * @author tiger
 * @depend gd
 *
 */
class CImgCaptcha
{	
	static function drawPNG($w, $h, $str)
	{
		header('Content-Type: image/png');
		$img = imagecreate($w, $h);
		imagecolorallocate($img, 255, 255, 255);
		$black = imagecolorallocate($img, 0, 0, mt_rand(0, 150));
		//$gray = imagecolorallocate($img, 0xe0, 0xe0, mt_rand(0xaa, 0xe0));
		$rx = mt_rand(1, 3);
		$ry = mt_rand(1, 2);
		for($y=0; $y<$h; $y += $ry)
		{
			$gray = imagecolorallocate($img, 0x90, mt_rand(0xb0, 0xe0), mt_rand(0x8a, 0xc0));
			for($o=mt_rand(1, 5), $x=$o; $x<$w; $x += $rx*$o)
				imagesetpixel($img, $x, $y, $gray);
		}
		$arcs =  mt_rand(0, 360);
		$arce = ($arcs + mt_rand(30, 90)) % 360;
		imagearc($img, mt_rand($w/4, $w/2), mt_rand($h/3, $h/2),mt_rand($w/3, $w), mt_rand($h/2, $h),$arcs, $arce, $black);
		imagestring($img, 5, mt_rand(2, 16), 10, $str, $black);
		imagepng($img);
		imagedestroy($img);
	}
}

?>