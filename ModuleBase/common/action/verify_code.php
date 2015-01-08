<?php
_import('CTools.php');

$oSession = CSession::getInstance(CMemcachedPool::getInstance());
$str = $str2 = '';
$num = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : 4;
if($num < 1)
	$num = 1;
if($num > 8)
	$num = 8;
for($i=0; $i<$num; ++$i)
{
	$c = chr(mt_rand(65, 90));
	$str .= $c.' ';
	$str2 .= $c;
}
$oSession->set('vcode', $str2);
CTools::drawPNG($num*20, 30, $str);
?>