<?php

class CAppEnvironment{

	CONST FT_CLASS      = 'class';	
	CONST FT_ACTION     = 'action';
	CONST FT_MODDEF     = 'moddef';
	
	private static $instance = null;
	
	private $env = array(
		/* config item */
		'site_name'         => '',
		'charset'           => 'utf-8',
		'class_file_suffix' => '.php',
		'table_prefix'      => 'mbs_',
		'database'          => array(
			// format: host_port_dbname, the 'dbname' is a database name that should be created by yourself
			'localhost_3307_module_base' => array('username'=>'root', 'pwd'=>''),
			//... more 
		),
		'memcache'          => array(
			array('localhost', '11211'),
			//... more
		),
		'default_module'    => 'index',
		/* config item end */
		
	);
	
	private function __construct(){
		$this->env['app_root'] = dirname(__FILE__).'/';
		$this->env['web_root'] = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')+1);
		$this->env['client_ip'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] 
			: (isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] 
			: (isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '0.0.0.0' ));
	}
	
	static function getInstance(){
		if(empty(self::$instance)){
			self::$instance = new CAppEnvironment();
		}
		return self::$instance;
	}
	
	function item($key){
		return isset($this->env[$key]) ? $this->env[$key] : null;
	}
	
	function getDir($mod, $file_type=''){
		return $this->env['app_root'].$mod.'/'.(empty($file_type) ? '' : $file_type.'/');
	}
	
	function getPath($mod, $filename){
		return $this->getDir($mod).$filename;
	}
	
	function getClassPath($mod, $classname){
		return $this->getPath($mod, self::FT_CLASS.'/'.$classname.$this->env['class_file_suffix']);
	}
	
	function getActionPath($mod, $action){
		return $this->getPath($mod, self::FT_ACTION.'/'.$action.'.php');
	}
	
	function toURL($mod, $action='index', $args=array()){
		$args['m'] = $mod;
		$args['a'] = $action;
		return $this->env['web_root'].'index.php?'.http_build_query($args);
	}
	
	function fromURL($url=''){
		parse_str(empty($url) ? $_SERVER['QUERY_STRING'] : $url, $arr);
		if(isset($arr['m']) && isset($arr['a'])){
			$arr2 = array($arr['m'], $arr['a']);
			unset($arr['m'], $arr['a']);
		}else{
			$arr2 = array('', '');
		}
		$arr2[] = $arr;
		return $arr2;
	}
	
	function getURL($mod, $filename){
		return $this->env['web_root'].$mod.'/'.$filename;
	}
	
	function getModDefInfo($mod){
		$class = 'C'.ucfirst($mod).'Def';
		$path = $this->getDir($mod, self::FT_MODDEF).$class.$this->env['class_file_suffix'];
		return array($class, $path);
	}
	
	function formatTableName($name){
		return empty($this->env['table_prefix']) ? $name : $this->env['table_prefix'].'_'.$name;
	}
	
	function getModList(){
		$list = array();
		
		if ($dh = opendir($this->env['app_root'])) {
			while (($file = readdir($dh)) !== false) {
				if($file[0] != '.' && is_dir($this->env['app_root'].$file)){
					$list[] = $file;
				}
			}
			closedir($dh);
			
			sort($list);
		}
		
		return $list;
	}

}

?>
