<?php
/**
 * argv:1)http, main.php?a=mod.action
 * 2)cli, php main.php arg1, arg2, ...
 */
date_default_timezone_set('Asia/Shanghai');

define('RTM_DEBUG', true);
error_reporting(RTM_DEBUG ? E_ALL : 0);
	
define('IN_INDEX', 1); //ref file: install.php, CModule.php

function mbs_error_handle(int $errno , string $errstr , string $errfile , int $errline , array $errcontext ){
	static $MSG_SEP = '';
	static $ERR_MSG = array(
		E_USER_ERROR   => 'ERROR',
		E_USER_WARNING => 'WARNNING',
		E_USER_NOTICE  => 'NOTICE'
	);
	
	$report_level = error_reporting();
	if(!($report_level & $errno)){
		// This error code is not included in error_reporting
		if(E_USER_ERROR == $errno){
			exit(1);
		}
		return;
	}
	
	if(false !== strpos(PHP_SAPI, 'cli')){
		$error_format = "[%s]\nerrstr: %s\nerfile: %s(%d)\nervars: %s\netrace: -------";
		$MSG_SEP = "\n\n";
		$errcontext = var_export($errcontext, true);
	}else{
		$error_format = '<p><b>%s</b></p><p>errstr: %s</p><p>erfile: %s(%d)</p><p>ervars: %s</p><p>etrace: -------</p>';
		$MSG_SEP = '<hr />';
		$errstr = htmlspecialchars($errstr);
		$errcontext = htmlspecialchars(var_export($errcontext, true));
	}
	
	$level = isset($ERR_MSG[$errno]) ? $ERR_MSG[$errno] : 'UNKNOWN('.$errno.')';
	echo sprintf($error_format, $level, $errstr, $errfile, $errline, $errcontext);
	debug_print_backtrace();
	echo $MSG_SEP;
	
	if(E_USER_ERROR == $errno){
		exit(1);
	}
	
	/* Don't execute PHP internal error handler */
	return true;
	
}
set_error_handler(mbs_error_handle);

//env and conf init;there are two kinds of const in the system.
//one start with 'RTM_' what means 'run-time' defined;the other start
//with 'CFG_' what means 'configuration(installing)' defined

require 'CAppEnvironment.php';
$mbs_appenv = CAppEnvironment::getInstance();

function mbs_import($mod, $class){
	global $mbs_appenv;
	$args = func_get_args();
	$numargs = func_num_args();
	for($i=2; $i<$numargs; ++$i){
		$c = $args[$i];
		$path = $mbs_appenv->getClassPath($mod, $c);
		require_once $path;
		if(!class_exists($c) && !interface_exists($c)){
			trigger_error('import class or interface "'.$c.'" not exists in: '.$path, E_USER_ERROR);
		}
	}
}

mbs_import('core', 'CModDef');

mbs_import('common', 'CDbPool.php', 'CMemcachedPool.php', 'CSession.php', 'CStrTools.php');
if(!class_exists('Memcached', false))
	mbs_import('common', 'Memcached.php');


function mbs_moddef($mod){
	global $mbs_appenv;
	static $modbuf = array();
		
	if(isset($modbuf[$mod])){
		return $modbuf[$mod];
	}
	
	$obj = null;
	list($class, $path) = $mbs_appenv->getModDefInfo($mod);
	if(file_exists($path)){
		require_once $path;
		$obj = new $class($this);
		if(! $obj instanceof CModDef){
			$obj = null;
			trigger_error($class.' not instance of CModDef', E_USER_WARNING);
		}
	}else{
		trigger_error($path.' not exists', E_USER_WARNING);
	}
	
	$modbuf[$mod] = $obj;
	
	return $obj;
}

if(false !== strpos(PHP_SAPI, 'cli')){
		if($args < 3){
			trigger_error('param is missing', E_USER_ERROR);
		}
		$mod = $args[1];
		$action = $args[2];
		$args = array_slice($args, 0, 3);
}else{
	if(false === stripos(ini_get('request_order'), 'GP'))
		$_REQUEST = array_merge($_GET, $_POST);
	
	if(ini_get('register_globals')){
		$GLOBALS = array_intersect_key($GLOBALS, array(
			'GLOBALS'=>'', '_GET'=>'', '_POST'=>'', '_COOKIE'=>''
			,'_REQUEST'=>'', '_SERVER'=>'', '_ENV'=>'', '_FILES'=>'')
		);
	}
	
	//check on installing first
	if((get_magic_quotes_gpc() || ini_get('magic_quotes_runtime')) && ini_get('magic_quotes_sybase'))
	{// the system use the method 'prepare' in class PDO to prevent the sql injection
		$func = create_function('&$v, $k', "\$v=str_replace(\"''\", \"'\", \$v);");
		array_walk_recursive($_GET, $func);
		array_walk_recursive($_POST, $func);
		array_walk_recursive($_COOKIE, $func);
		array_walk_recursive($_REQUEST, $func);
	}
	else if(get_magic_quotes_gpc())
	{
		$func = create_function('&$v, $k', "\$v=stripslashes(\$v);");
		array_walk_recursive($_GET, $func);
		array_walk_recursive($_POST, $func);
		array_walk_recursive($_COOKIE, $func);
		array_walk_recursive($_REQUEST, $func);
	}
 	
 	list($mod, $action, $args) = $mbs_appenv->fromURL();
}

if(empty($mod)){
	$mod = $mbs_appenv->item('default_module');
}else if(!CStrTools::isModifier($mod)){
	trigger_error('Invalid module', E_USER_ERROR);
}

if(empty($action)){
	$action = 'index.php';
}else if(!CStrTools::isModifier($action)){
	trigger_error('Invalid action', E_USER_ERROR);
}

define('RTM_APPENV', $mbs_appenv);
define('RTM_MOD',    $mod);
define('RTM_ACTION', $action);
define('RTM_ACTION_PATH', $mbs_appenv->getActionPath($mod, $action));

if(!file_exists(RTM_ACTION_PATH)){
	trigger_error('Invalid request: '.$mod.'.'.$action, E_USER_ERROR);
}

$moddef = mbs_moddef($mod);
if(empty($moddef)){
	trigger_error('no such module: '.$mod, E_USER_ERROR);
}

CDbPool::setConf($mbs_appenv->item('database'));
if(count($mbs_appenv->item('memcache')) > 0){
	CMemcachedPool::setConf($mbs_appenv->item('memcache'));
}

if(RTM_DEBUG){
	CDbPool::getInstance()->setClass(CDbPool::CLASS_PDODEBUG);
	CMemcachedPool::getInstance()->setClass(CMemcachedPool::CLASS_MEMCACHEDDEBUG);
}

//do filter checking
if(!$moddef->loadFilters()){
	exit(1);
}

header('Content-Type: text/html; charset='.$mbs_appenv->item('charset'));
require RTM_ACTION_PATH;

if(function_exists('fastcgi_finish_request'))
	fastcgi_finish_request();
	
CCore::runListeners();

if(RTM_DEBUG){
	CDbPool::getInstance()->html(); 
	CMemcachedPool::getInstance()->html();
	var_dump($_REQUEST);
}

exit(0);
?>
