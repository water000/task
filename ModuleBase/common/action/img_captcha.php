<?php

mbs_import('common', 'CImgCaptcha');

$str = $str2 = '';
$num = 4;
for($i=0; $i<$num; ++$i){
	$c = chr(mt_rand(65, 90));
	$str .= $c.' ';
	$str2 .= $c;
}

session_start();
$_SESSION['common_img_captcha'] = $str2;

CImgCaptcha::drawPNG($num*20, 30, $str);

?>