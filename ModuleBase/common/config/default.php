<?php 

//do not add any constant in the file.

/* php.ini
session.use_only_cookies = 1
session.gc_maxlifetime = 86400*7
log_errors = On
error_log = path
post_max_size = 10M
upload_max_filesize = 10M
max_file_uploads = 20
*/


//ini_set('session.save_handler', 'memcache');
//ini_set('session.save_path',    'tcp://127.0.0.1:11211');
//ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 86400*7);

$default = array(
	'default_module'       => 'user',
	'default_action'       => 'login',
	'table_prefix'         => 'mbs_',
	'database'             => array(
		// format: host_port_dbname, the 'dbname' is a database name that should be created by yourself
		'localhost_3306_task' => array('username'=>'root', 'pwd'=>''),
		//... more
	),
	'PDO_ER_DUP_ENTRY'     => '23000', 
	
	'memcache'             => array(
		//array('localhost', '11211'),
		//... more
	),
	
	'action_filters'       => array(
		//array(function(){return true/false, execute the 'class' if true returned}, 'mod', 'class'), 
		//the class must extends the core.CModTag, and the params in function 'oper' are 'null'
		
		array(function($action_def){return !empty($action_def) && isset($action_def[CModDef::P_MGR]); }, 
			'privilege', 'privFtr'),
		
		array(function($action_def){global $mbs_appenv;return !empty($action_def) && isset($action_def[CModDef::P_OUT]) && $mbs_appenv->item('client_accept') != 'html'; }, 
		    'common', 'ApiSignFtr'),
		
		//.... more
	), 
	
	'appkeys'    => array(
		'1.0' => 'v1.0863c6bf0bc0d26257db4edcfdad309c1'
	),
	
	'events'     => array(
	    //'mod.action.event'=>array('mod.classEventListener'),
	    'task.submit_verify.on_submit_used' => array('wallet.CWalletHandle'),
	    'task.edit.on_task_create' => array('task.CTaskDepCtr'),
	    'task.edit.on_task_edit'   => array('task.CTaskDepCtr'),
	    
	    'user.reg.on_user_add' => array('user.CUserSyncIM'),
	    'user.myinfo.on_user_edit' => array('user.CUserSyncIM'),
	    'user.pwd_reset.on_user_edit'=> array('user.CUserSyncIM'),
	    'user.pwd_modify.on_user_edit'=> array('user.CUserSyncIM'),
	),
	
	'listener' => array(
	    'task.CTaskInfoCtr.add' => array('task.CTaskDepMapCtr'),
	    'task.CTaskInfoCtr.set' => array('task.CTaskDepMapCtr'),
	    'task.CTaskSubmitCtr.setNode' => array('wallet.CWalletHandle'),
	    
	    'user.CUserInfoCtr.add' => array('user.CUserSyncIM'),
	    'user.CUserInfoCtr.set' => array('user.CUserSyncIM'),
	),
);

?>