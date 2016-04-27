<?php

date_default_timezone_set('Asia/Shanghai');


define('RTM_DEBUG', 1);
error_reporting(RTM_DEBUG ? E_ALL : 0);	
define('IN_INDEX', 1); //ref file: CAppEnv.php

//env and conf init;there are two kinds of const in the system.
//one start with 'RTM_' what means 'run-time' defined;the other start
//with 'CFG_' what means 'configuration(installing)' defined
require 'CAppEnv.php';
$mbs_appenv     = CAppEnv::getInstance();
$mbs_cur_moddef = $mbs_cur_actiondef = null;

//exception, database error, ...
//mbs_error_log('some errors', __FILE__, __LINE__);
function mbs_error_log($msg, $file, $lineno){
	global $mbs_appenv;
	//U: marking as user level
	$error = sprintf("U[%s]%s.%s(%s:%d)\n%s\n",
			date('Y/m/d H:i:s'),
			$mbs_appenv->item('cur_mod'),
			$mbs_appenv->item('cur_action'),
			$file,
			$lineno,
			$msg
	);
	if(RTM_DEBUG)
		$mbs_appenv->echoex($error, 'SYS_ERROR');
	error_log($error, 0);
}

set_exception_handler(function($e){// handle some exceptions that do not catch
	mbs_error_log($e->getMessage()."\n".$e->getTraceAsString(), 'UNCAUGHT', 0);
	if(!RTM_DEBUG)
		$mbs_appenv->echoex($mbs_appenv->lang('db_exception'), 'SYS_EXCEPTION');
});

/* php.ini (log_errors=ture, error_log=path)
 * if(!RTM_DEBUG){
	set_error_handler(function($eno, $err, $file, $line){
		mbs_error_log($err.'('.$eno.')', $file, $line);
	});
}*/

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

function _main($mbs_appenv){
	global $mbs_cur_moddef, $mbs_cur_actiondef;
	
	if(false !== strpos(PHP_SAPI, 'cli')){
		if($argc < 3){
			trigger_error('BIN/php index.php module action', E_USER_ERROR);
		}
		$mod = $argv[1];
		$action = $argv[2];
		$argv = array_slice($argv, 0, 3);
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
	
		list($mod, $action, $args) = $mbs_appenv->fromURL(
			$mbs_appenv->config('default_module', 'common'),
			$mbs_appenv->config('default_action', 'common')
		);
	}
	
	if(!CStrTools::isModifier($mod) || !CStrTools::isModifier($action)){
		header('HTTP/1.1 404');
		trigger_error('Invalid request', E_USER_ERROR);
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

	$mbs_appenv->config('', 'common');
	
	if(RTM_DEBUG && !isset($mbs_cur_actiondef[CModDef::P_DOF])){
		CDbPool::getInstance()->setClass(CDbPool::CLASS_PDODEBUG);
		CMemcachedPool::getInstance()->setClass(CMemcachedPool::CLASS_MEMCACHEDDEBUG);
	
		register_shutdown_function(function($mbs_appenv, $mbs_cur_actiondef){
			if(!isset($mbs_cur_actiondef[CModDef::P_DOF])){
				if(false !== strpos(PHP_SAPI, 'cli')){
					CDbPool::getInstance()->cli();
					CMemcachedPool::getInstance()->cli();
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
	
	if(CModDef::isReservedAction($action)){
		$err = '';
		try {
			$err = $mbs_cur_moddef->$action(CDbPool::getInstance(), CMemcachedPool::getInstance());
		} catch (Exception $e) {
			//echo $mbs_appenv->lang('db_exception', 'common');
			echo $e->getMessage(), "\n<br/>", $e->getTraceAsString();
		}
		echo $action, empty($err)? ' successed' : " error: \n". implode("\n<br/>", $err);
	}else{
		
		//do filter checking
		if(!$mbs_cur_moddef->loadFilters($action)){
			exit(1);
		}
		
		$filters = $mbs_appenv->config('action_filters', 'common');
		if(!empty($filters) && !empty($mbs_cur_actiondef)){
			foreach($filters as $ftr){
				if(count($ftr) >=3 && $ftr[0]($mbs_cur_actiondef)){
					$mdef = mbs_moddef($ftr[1]);
					if(!$mdef->filter($ftr[2], null, $err)){
						$mbs_appenv->echoex($err, 'AC_FTR_ERROR');
						exit(1);
					}
				}
			}
		}
		
		$path = $mbs_appenv->getActionPath($action, $mod);
		if(!file_exists($path)){
			header('HTTP/1.1 404');
			trigger_error('Invalid request: '.$mod.'.'.$action, E_USER_ERROR);
		}
		require $path;
	}

	if(!RTM_DEBUG && function_exists('fastcgi_finish_request'))
		call_user_func('fastcgi_finish_request');	
}

_main($mbs_appenv);

exit(0);
?>
