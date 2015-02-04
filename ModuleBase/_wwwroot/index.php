<?php
/**
 * argv:1)http, main.php?a=mod.action
 * 2)cli, php main.php arg1, arg2, ...
 */
date_default_timezone_set('Asia/Shanghai');

define('RTM_DEBUG', true);
error_reporting(RTM_DEBUG ? E_ALL : 0);
	
define('IN_INDEX', 1); //ref file: install.php, CModule.php

//env and conf init;there are two kinds of const in the system.
//one start with 'RTM_' what means 'run-time' defined;the other start
//with 'CFG_' what means 'configuration(installing)' defined

require 'CAppEnvironment.php';
$mbs_appenv = CAppEnvironment::getInstance();

// import class only
function mbs_import($mod, $class){
	global $mbs_appenv;
	$args = func_get_args();
	$numargs = func_num_args();
	for($i=1; $i<$numargs; ++$i){
		$c = $args[$i];
		$path = $mbs_appenv->getClassPath($c, $mod);
		require_once $path;
		if(!class_exists($c, false) && !interface_exists($c, false)){
			trigger_error('imported class or interface "'.$c.'" not exists in: '.$path, E_USER_ERROR);
		}
	}
}

mbs_import('core', 'CModDef');

mbs_import('common', 'CDbPool', 'CMemcachedPool', 'CUniqRowControl', 'CStrTools');
if(!class_exists('Memcached', false))
	mbs_import('common', 'Memcached');

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
		$obj = new $class($mbs_appenv);
		if(! $obj instanceof CModDef){
			$obj = null;
			trigger_error($class.' not instance of CModDef', E_USER_ERROR);
		}
	}else{
		//trigger_error($mod.' mod not exists', E_USER_WARNING);
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

if(!CStrTools::isModifier($mod)){
	trigger_error('Invalid module', E_USER_ERROR);
}
if(!CStrTools::isModifier($action)){
	trigger_error('Invalid action', E_USER_ERROR);
}


$mbs_cur_moddef = mbs_moddef($mod);
if(empty($mbs_cur_moddef)){
	trigger_error('no such module: '.$mod, E_USER_ERROR);
}

function mbs_tbname($name){
	return $GLOBALS['mbs_appenv']->config('table_prefix', 'common').$name;
}

function mbs_title($action='', $mod='', $system=''){
	global $mbs_cur_moddef, $mbs_appenv;

	$argc = func_num_args();
	if(0 == $argc){
		echo $mbs_cur_moddef->item(CModDef::PAGES, $mbs_appenv->item('cur_action'), CModDef::P_TLE), 
			'-', $mbs_cur_moddef->item(CModDef::MOD, CModDef::G_TL), 
			'-', $mbs_appenv->lang('site_name');
	}
	else if(1 == $argc){
		echo $action , 
			'-', $mbs_cur_moddef->item(CModDef::MOD, CModDef::G_TL),
			'-', $mbs_appenv->lang('site_name');
	}else if(2 == $argc){
		echo $action , '-', $mod, '-', $mbs_appenv->lang('site_name');
	}else{
		echo $action , '-', $mod, '-', $system;
	}
}

if(!empty($mbs_appenv->config('database', 'common'))){
	CDbPool::setConf($mbs_appenv->config('database', 'common'));
}
CDbPool::setCharset($mbs_appenv->item('charset'));
if(!empty($mbs_appenv->config('memcache', 'common'))){
	CMemcachedPool::setConf($mbs_appenv->config('memcache', 'common'));
}
if(RTM_DEBUG){
	CDbPool::getInstance()->setClass(CDbPool::CLASS_PDODEBUG);
	CMemcachedPool::getInstance()->setClass(CMemcachedPool::CLASS_MEMCACHEDDEBUG);
}

if(!empty($mbs_appenv->config('session.save_handler'))){
	ini_set("session.save_handler", $mbs_appenv->config('session.save_handler'));
	ini_set("session.save_path", $mbs_appenv->config('session.save_path'));
}


if('install' == $action){
	$err = $mbs_cur_moddef->install(CDbPool::getInstance(), CMemcachedPool::getInstance());
	echo empty($err)? 'install complete, successed' : 'error', "\n", implode("\n<br/>", $err);
	
}else{
	define('RTM_ACTION_PATH', $mbs_appenv->getActionPath($action, $mod));
	if(!file_exists(RTM_ACTION_PATH)){
		trigger_error('Invalid request: '.$mod.'.'.$action, E_USER_ERROR);
	}
	
	//do filter checking
	if(!$mbs_cur_moddef->loadFilters()){
		exit(1);
	}
	
	header('Content-Type: text/html; charset='.$mbs_appenv->item('charset'));
	require RTM_ACTION_PATH;
}

if(function_exists('fastcgi_finish_request'))
	fastcgi_finish_request();
	
if(RTM_DEBUG){
	CDbPool::getInstance()->html(); 
	CMemcachedPool::getInstance()->html();
}

exit(0);
?>
