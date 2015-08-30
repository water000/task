<?php 
$root = $mbs_appenv->getDir('');
if(file_exists($root.'readme.html')){
	include $root.'readme.html';
}else{
	echo 'no readme.html found';
}
?>