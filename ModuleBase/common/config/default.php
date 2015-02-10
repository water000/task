<?php 

//do not add any constant in the file.


//ini_set('session.save_handler', 'memcache');
//ini_set('session.save_path',    'tcp://127.0.0.1:11211');

$default = array(
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
		//array('mod', 'class'), the class must implements the IModTag interface, and the params in function 'oper' are 'null'
		array('privilege', 'CPrivFilter'),
		//.... more
	),
)

?>