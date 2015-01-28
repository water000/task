<?php

class CAppEnvironment{

	CONST FT_CLASS      = 'class';	
	CONST FT_ACTION     = 'action';
	CONST FT_MODDEF     = 'moddef';
	
	private static $instance = null;
	
	private $env = array(
		/**********config item **********/
		'site_name'         => '',
		'charset'           => 'utf-8',
		'lang'              => 'zh_CN',
		'class_file_suffix' => '.php',
		'table_prefix'      => 'mbs_',
		'database'          => array(
			// format: host_port_dbname, the 'dbname' is a database name that should be created by yourself
			'localhost_3306_module_base' => array('username'=>'root', 'pwd'=>''),
			//... more 
		),
		'memcache'          => array(
			//array('localhost', '11211'),
			//... more
		),
		'default_module'    => 'index',
		/********** config end **********/
		
		
		/********** runtime item **********/
		'app_root'          => '', // assigned by __construct()
		'web_root'          => '/', // assigned by __construct(). NOTICE: must be '/' if using url-rewrite conditions , else to empty
		'client_ip'         => '', // assigned by __construct()
		'cur_mod'           => '', // assigned by fromURL()
		'cur_action'        => '', // assigned by fromURL()
		'cur_action_url'    => '', // assigned by fromURL()
			
	);
	
	private $mod_cfg = array();
	
	private function __construct(){
		$this->env['app_root'] = realpath(dirname(__FILE__).'/..').'/';
		$this->env['web_root'] = empty($this->env['web_root']) ? 
			substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')+1) : $this->env['web_root'];
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
	
	function getPath($filename, $mod=''){
		return $this->getDir(empty($mod) ? $this->env['cur_mod'] : $mod).$filename;
	}
	
	function getClassPath($classname, $mod=''){
		return $this->getPath(self::FT_CLASS.'/'.$classname.$this->env['class_file_suffix'], $mod);
	}
	
	function getActionPath($action, $mod=''){
		return $this->getPath(self::FT_ACTION.'/'.$action.'.php', $mod);
	}
	
	//@file: a relative path in action dir
	function file2action($file){
		return ($pos = strrpos($file, '.php')) !== false ? substr($file, 0, $pos) : $file;
	}
	
	function toURL($action='index', $mod='', $args=array()){
		/*
		$args['m'] = $mod;
		$args['a'] = $action;
		return $this->env['web_root'].'index.php?'.http_build_query($args);
		*/
		
		// detail at the 'fromURL()'
		return $this->env['web_root'].(empty($mod)?$this->env['cur_mod']:$mod).'/'.$action
			.(empty($args) ? '' : '?'.http_build_query($args));
	}
	
	function fromURL($url=''){
		/*
		// this version use the 'm' and 'a' to request directly without using any url-rewrite conditions
		parse_str(empty($url) ? $_SERVER['QUERY_STRING'] : $url, $arr);
		$arr2 = array();
		$arr2[0] = isset($arr['m']) ? $arr['m'] : $this->env['default_module'];
		$arr2[1] = isset($arr['a']) ? $arr['a'] : 'index';
		$arr2[] = $arr;
		*/
		
		// to enable the url-rewrite on server
		//RewriteEngine on
  		//RewriteRule ^/(.+)/(js|css|image)/(.*)     -                       [L,QSA]
  		//RewriteRule ^/favicon.ico   -                       [L,QSA]
  		//RewriteRule ^(.*)$          /index.php?__path__=$1  [B,L,QSA] 
		$arr = explode('/', trim($_GET['__path__'], '/'));
		$arr2[0] = isset($arr['0']) ? $arr['0'] : $this->env['default_module'];
		$arr2[1] = isset($arr['1']) ? $arr['1'] : 'index';
		unset($_GET['__path__']);
		$arr2[] = $_GET;

		$this->env['cur_mod']    = $arr2[0];
		$this->env['cur_action'] = $arr2[1];
		$this->env['cur_action_url'] = $this->toURL($arr2[1], $arr2[0]);

		return $arr2;
	}
	
	function getURL($filename, $mod=''){
		return $this->env['web_root'].(empty($mod) ? $this->env['cur_mod'] : $mod).'/'.$filename;
	}
	
	function getModDefInfo($mod){
		$class = 'C'.ucfirst($mod).'Def';
		$path = $this->getDir($mod, self::FT_MODDEF).$class.$this->env['class_file_suffix'];
		return array($class, $path);
	}
	
	function formatTableName($name){
		return empty($this->env['table_prefix']) ? $name : $this->env['table_prefix'].$name;
	}
	
	function getModList(){
		$list = array();
		
		if ($dh = opendir($this->env['app_root'])) {
			while (($file = readdir($dh)) !== false) {
				if($file[0] != '.' && is_dir($this->env['app_root'].$file) 
						&& is_dir($this->env['app_root'].$file.'/'.self::FT_MODDEF)){
					$list[] = $file;
				}
			}
			closedir($dh);
			
			sort($list);
		}
		
		return $list;
	}
	
	function config($item, $cfg='default', $mod=''){
		$mod = empty($mod) ? $this->env['cur_mod'] : $mod;
		if(!isset($this->mod_cfg[$mod][$cfg])){
			$path = $this->getPath('config/'.$cfg.'.php', $mod);
			if(file_exists($path)){
				require_once $path;
				if(isset($$cfg)){
					$this->mod_cfg[$mod][$cfg] = $$cfg;
				}else{
					$this->mod_cfg[$mod][$cfg] = null;
					trigger_error('no such config item defined: '.$cfg, E_USER_WARNING);
				}
			}else{
				trigger_error('no such config file found: '.$mod.'.'.$cfg, E_USER_ERROR);
			}
		}
		
		return isset($this->mod_cfg[$mod][$cfg][$item]) ? $this->mod_cfg[$mod][$cfg][$item] : $item;
	}
	
	function lang($item, $mod=''){
		return $this->config($item, 'lang_'.$this->env['lang'], $mod);
	}

}

?>
