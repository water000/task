<?php 

//do not add any constant in the file.

$default = array(
	'session.save_handler' => 'memcache',
	'session.save_path'    => 'tcp://127.0.0.1:11211',
		
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
)

?>