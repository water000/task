<?php

if(!CFileType::hasMod($_REQUEST['mod']))
	CCore::abort('not found :'.htmlspecialchars($_REQUEST['mod']));
$commonTypes = CFileType::getTypes();
if(!in_array($_REQUEST['type'], $commonTypes))
	CCore::abort('type banned: '.htmlspecialchars($_REQUEST['type']));
if(strpos($_REQUEST['file'], '..') !== false)
	CCore::abort('invalid file: '.htmlspecialchars($_REQUEST['file']));
	
$path = CFileType::getPath(CFileType::ENV_COMPILE, $_REQUEST['mod'], 
	$_REQUEST['file'], $_REQUEST['type']);
if(!file_exists($path))
	CCore::abort('not found: '.htmlspecialchars($_REQUEST['file']));
	
$pos = strrpos($_REQUEST['file'], '/');
$f = $pos === false ? $_REQUEST['file'] : substr($_REQUEST['file'], $pos+1);
	
header('Content-Disposition: attachment; filename="'.$f.'"');
readfile($path);
?>