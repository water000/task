<?php

date_default_timezone_set('Asia/Shanghai');

define('RTM_DEBUG', 1);
define('RTM_INDEX', 1); //ref file: CAppEnv.php
if(RTM_DEBUG){
    error_reporting(E_ALL);
    ini_set('display_startup_errors', '1');
}else{// do not display errors on genaration env. HIGH recommend that the errors put in logs
    ini_set('display_errors', '0');
}

//env and conf init;there are two kinds of const in the system.
//one start with 'RTM_' what means 'run-time' defined;the other start
//with 'CFG_' what means 'configuration(installing)' defined
require 'CAppEnv.php';
$mbs_appenv     = CAppEnv::getInstance();
$mbs_cur_moddef = $mbs_cur_actiondef = null;

//DO not call the function directly, instead of using the trigger_error function.
//mbs_error_log('[int]error type/no', 'some errors', __FILE__, __LINE__);
function mbs_error_log($errno, $msg, $file, $lineno){
	global $mbs_appenv;
	static $map = array(E_WARNING=>'PHP WARN', E_NOTICE=>'PHP NOTICE', 
	    E_USER_ERROR=>'USER ERROR', E_USER_WARNING=>'USER WARN', E_USER_NOTICE=>'USER NOTICE');
	
	http_response_code(500);
	
	$error = sprintf("%s: %s.%s: %s(%s:%d)\n",
	        isset($map[$errno]) ? $map[$errno] : 'UNDEF('.$errno.')',
			$mbs_appenv->item('cur_mod'),
			$mbs_appenv->item('cur_action'),
			$msg,
	        $file,
	        $lineno
	);
	$mbs_appenv->echoex(RTM_DEBUG ? '['.date('Y/m/d H:i:s e').']'.$error 
	    : $mbs_appenv->lang('db_exception'), 'SYS_ERROR');
	error_log($error, 0);
	if(E_USER_ERROR == $errno)
	    exit(1);
}
set_error_handler('mbs_error_log');// php.ini (log_errors=ture, error_log=path)
set_exception_handler(function($e){// handle some uncaught exceptions
	mbs_error_log(E_USER_ERROR, 
	    $e->getMessage()."\n".$e->getTraceAsString(), 
	    $e->getFile(), 
	    $e->getLine());
});
    
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

mbs_import('core', 'CModDef', 'CModTag');
mbs_import('common', 'CDbPool', 'CMemcachedPool', 'CUniqRowControl', 'CStrTools', 'CSessionDBCache');
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
			trigger_error($class.' not instance of CModDef', E_USER_WARNING);
		}
	}else{
		trigger_error($mod.' mod not exists', E_USER_WARNING);
	}
	
	$modbuf[$mod] = $obj;
	
	return $obj;
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

function mbs_runtime_close_debug(){ // call the function before the echoex invoked if json request coming
	global $mbs_cur_moddef, $mbs_appenv;
	
	$mbs_cur_actiondef[CModDef::P_DOF] = ''; // set the key to close the output when app terminated
	$mbs_appenv->setLogAPI(null);  // do NOT record log
}

function _main(){
	global $mbs_appenv, $mbs_cur_moddef, $mbs_cur_actiondef, $argc;
	
	if(false !== strpos(PHP_SAPI, 'cli')){
		if($argc < 3){
			trigger_error('BIN/php index.php module action', E_USER_ERROR);
		}
		list($mod, $action, $args) = $mbs_appenv->fromCLI();
	}else{
		if(false === stripos(ini_get('request_order'), 'GP'))
			$_REQUEST = array_merge($_GET, $_POST);
	
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
	
		list($mod, $action, $args) = $mbs_appenv->fromURL(
			$mbs_appenv->config('default_module', 'common'),
			$mbs_appenv->config('default_action', 'common')
		);
		

		if(isset($_SERVER['HTTP_X_LOGIN_TOKEN']) && !empty($_SERVER['HTTP_X_LOGIN_TOKEN'])){ // only for app request
		    $_COOKIE[session_name()] = $_SERVER['HTTP_X_LOGIN_TOKEN'];
		}
		else if(isset($_REQUEST['X-LOGIN-TOKEN'])){
		    $_COOKIE[session_name()] = $_REQUEST['X-LOGIN-TOKEN'];
		}
		
		if(isset($_SERVER['HTTP_X_POST_JSON_FIELD']) 
		    && isset($_REQUEST[$_SERVER['HTTP_X_POST_JSON_FIELD']])){
		    $_REQUEST = array_merge($_REQUEST, json_decode($_REQUEST[$_SERVER['HTTP_X_POST_JSON_FIELD']], true));
		}
	}
	
	if(!CStrTools::isModifier($mod) || !CStrTools::isModifier($action)){
		http_response_code(404);
		exit(404);
	}
	
	$mbs_cur_moddef = mbs_moddef($mod);
	if(empty($mbs_cur_moddef)){
		trigger_error('no such module: '.$mod, E_USER_ERROR);
	}
	$mbs_cur_actiondef = $mbs_cur_moddef->item(CModDef::PAGES, $mbs_appenv->item('cur_action'));
	
	$db = $mbs_appenv->config('database', 'common');
	if(!empty($db)){
		CDbPool::setConf($db);
	}
	CDbPool::setCharset($mbs_appenv->item('charset'));
	
	$mem = $mbs_appenv->config('memcache', 'common');
	if(!empty($mem)){
		CMemcachedPool::setConf($mem);
	}
	
	session_set_save_handler(new CSessionDBCache(
	    CDbPool::getInstance(), CMemcachedPool::getInstance()), true);
	
	if(RTM_DEBUG && !isset($mbs_cur_actiondef[CModDef::P_DOF])){
		CDbPool::getInstance()->setClass(CDbPool::CLASS_PDODEBUG);
		CMemcachedPool::getInstance()->setClass(CMemcachedPool::CLASS_MEMCACHEDDEBUG);
	
		register_shutdown_function(function($mbs_appenv, $mbs_cur_actiondef){
			if(!isset($mbs_cur_actiondef[CModDef::P_DOF])){
				if(false !== strpos(PHP_SAPI, 'cli')){
					CDbPool::getInstance()->cli();
					CMemcachedPool::getInstance()->cli();
					echo "\n";
				}else if('html' == $mbs_appenv->item('client_accept')){
					echo '<div><a href="javascript:;" style="font-size:12px;color:#888;display:block;text-align:right;" onclick="open(null, null, \'width=800,height=600\').document.write(this.parentNode.nextSibling.innerHTML)">debug-info</a></div><div style="display:none">';
					CDbPool::getInstance()->html();
					CMemcachedPool::getInstance()->html();
					echo '</div>';
				}
			}
		}, $mbs_appenv, $mbs_cur_actiondef);
	
		mbs_import('core', 'CLogAPI');
		$mbs_appenv->setLogAPI(new CDBLogAPI(CDbPool::getInstance()->getDefaultConnection()));
	}

	if(false !== strpos(PHP_SAPI, 'cli') && CModDef::isReservedAction($action)){
		$err = '';
		try {
			$err = $mbs_cur_moddef->$action(CDbPool::getInstance(), CMemcachedPool::getInstance());
		} catch (Exception $e) {
			echo $e->getMessage(), "\n<br/>", $e->getTraceAsString();
		}
		echo $action, empty($err)? ' successed!' : " error: \n". implode("\n<br/>", $err);
	}else{

	    $path = $mbs_appenv->getActionPath($action, $mod);
	    if(!file_exists($path)){
	        http_response_code(404);
	        exit(404);
	    }
		//do filter checking
		if(!$mbs_cur_moddef->loadFilters($action)){
			exit(1);
		}
		
		$filters = $mbs_appenv->config('action_filters', 'common');
		if(!empty($filters) && !empty($mbs_cur_actiondef)){
			foreach($filters as $ftr){
				if(count($ftr) >=3 && $ftr[0]($mbs_cur_actiondef)){
					$mdef = mbs_moddef($ftr[1]);
					if(!$mdef->filter($ftr[2], isset($ftr[3])?$ftr[3]:null, $err)){
						$mbs_appenv->echoex($err, 'AC_FTR_ERROR');
						exit(1);
					}
				}
			}
		}
		
		$listeners = $mbs_appenv->config('listener', 'common');
		if(!empty($listeners)){
		    foreach($listeners as $k => $v){
		        list($mod, $class, $func) = explode('.', $k);
		        mbs_import($mod, $class);
		        $ins = $class::getInstance($mbs_appenv, 
		            CDbPool::getInstance(), CMemcachedPool::getInstance());
		        $ins->produce($func, $v, $k);
		    }
		}
		
		require $path;
	}

	if(!RTM_DEBUG && function_exists('fastcgi_finish_request'))
		call_user_func('fastcgi_finish_request');	
}

_main();
exit(0);
?>
