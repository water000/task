<?php 

//do not add any constant in the file.


//ini_set('session.save_handler', 'memcache');
//ini_set('session.save_path',    'tcp://127.0.0.1:11211');
ini_set('session.use_only_cookies', 1);

if(isset($_SERVER['HTTP_X_LOGIN_TOKEN']) && !empty($_SERVER['HTTP_X_LOGIN_TOKEN'])){ // only for app request
	$_COOKIE[session_name()] = $_SERVER['HTTP_X_LOGIN_TOKEN'];
}
else if(isset($_REQUEST['X-LOGIN-TOKEN'])){
	$_COOKIE[session_name()] = $_REQUEST['X-LOGIN-TOKEN'];
}

$default = array(
	'default_module'       => 'user',
	'default_action'       => 'login',
	'table_prefix'         => 'mbs_',
	'database'             => array(
		// format: host_port_dbname, the 'dbname' is a database name that should be created by yourself
		'localhost_3306_module_base' => array('username'=>'root', 'pwd'=>''),
		//... more
	),
	
	'memcache'             => array(
		//array('localhost', '11211'),
		//... more
	),
	
	'action_filters'       => array(
		//array(function(){return true/false, execute the 'class' if true returned}, 'mod', 'class'), 
		//the class must extends the core.CModTag, and the params in function 'oper' are 'null'
		
		array(function($action_def){return !empty($action_def) && isset($action_def[CModDef::P_MGR]); }, 
			'privilege', 'privFtr'),
		
		//array(function($action_def){return !empty($action_def) && isset($action_def[CModDef::P_OUT]); }, '', ''),
		
		//.... more
	), 
	
	'appkeys'    => array(
		'1.0' => 'v1.0863c6bf0bc0d26257db4edcfdad309c1'
	),
	
	'event'      => array(
		//'mod.action.event' => array(mod.CModEvent, ...), ...
		
		'product.attr_list.map_changed' => array('merchant.CMctEvent'),
	),
);

?>