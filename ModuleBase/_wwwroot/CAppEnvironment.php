<?php

defined('IN_INDEX') or exit('access denied');

class CAppEnvironment{

	CONST FT_CLASS      = 'class';	
	CONST FT_ACTION     = 'action';
	CONST FT_MODDEF     = 'moddef';
	
	private static $instance = null;
	
	private $env = array(
		/**********config item **********/
		'charset'           => 'utf-8',
		'lang'              => 'zh_CN',
		'class_file_suffix' => '.php',
		'default_module'    => 'common',
		/********** config end **********/
		
		/********** runtime item **********/
		'app_root'          => '', // assigned by __construct()
		'web_root'          => '/', // assigned by __construct(). NOTICE: must be '/' if using url-rewrite conditions , else to empty
		'client_ip'         => '', // assigned by __construct()
		'client_accept'     => '', // assigned by __construct()
		'cur_mod'           => '', // assigned by fromURL()
		'cur_action'        => '', // assigned by fromURL()
		'cur_action_url'    => '', // assigned by fromURL()
		
	);
	
	private $mod_cfg = array();
	
	private $log_api = null; // an instance of core.CLogAPI
	
	private function __construct(){
		$this->env['app_root'] = realpath(dirname(__FILE__).'/..').'/';
		$this->env['web_root'] = empty($this->env['web_root']) ? 
			substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')+1) : $this->env['web_root'];
		$this->env['client_ip'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] 
			: (isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] 
			: (isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '0.0.0.0' ));
		
		if(isset($_SERVER['HTTP_ACCEPT'])){
			if(stripos($_SERVER['HTTP_ACCEPT'], 'json') !== false)
				$this->env['client_accept'] = 'json';
			else if(stripos($_SERVER['HTTP_ACCEPT'], 'html') !== false ||
				stripos($_SERVER['HTTP_ACCEPT'], 'xhtml') !== false || 
				stripos($_SERVER['HTTP_ACCEPT'], '*/*') !== false)
				$this->env['client_accept'] = 'html';
			else if(stripos($_SERVER['HTTP_ACCEPT'], 'xml') !== false)
				$this->env['client_accept'] = 'xml';
		}
		if($this->env['client_accept'] != '')
			header(sprintf('Content-Type: text/%s; charset=%s', 
				$this->env['client_accept'], $this->env['charset']));
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
	
	function setLogAPI($log){
		$this->log_api = $log;
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
  		//RewriteRule ^/static/(.+)   -                       [L,QSA]
		//RewriteRule ^/upload/(.+)   -                       [L,QSA]
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
	
	/**
	 * get the static resource URL
	 * @param string $filename the resource name like 'core.css, core.js, core/a.png'.
	 * @return string
	 */
	function sURL($filename){
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		switch (strtolower($ext)){
			case 'css':
				$filename = 'css/'.$filename;
				break;
			case 'js':
				$filename = 'js/'.$filename;
				break;
			default :
				$filename = 'img/'.$filename;
				break;
		}
		return $this->env['web_root'].'static/'.$filename;
	}
	
	function getModDefInfo($mod){
		$class = 'C'.ucfirst($mod).'Def';
		$path = $this->getDir($mod, self::FT_MODDEF).$class.$this->env['class_file_suffix'];
		return array($class, $path);
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
	
	function config($item, $mod='', $cfg='default'){
		$mod = empty($mod) ? $this->env['cur_mod'] : $mod;
		if(!isset($this->mod_cfg[$mod][$cfg])){
			$path = $this->getPath('config/'.$cfg.'.php', $mod);
			if(file_exists($path)){
				$mbs_appenv = $this;
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
		
		if(is_string($item)){
			return isset($this->mod_cfg[$mod][$cfg][$item]) ? $this->mod_cfg[$mod][$cfg][$item] : $item;
		}
		else if(is_array($item)){
			$ret = '';
			foreach($item as $e){
				$ret .= isset($this->mod_cfg[$mod][$cfg][$e]) ? $this->mod_cfg[$mod][$cfg][$e] : $e;
			}
			return $ret;
		}else{
			return $item;
		}
	}
	
	function lang($item, $mod=''){
		return $this->config($item, $mod, 'lang_'.$this->env['lang']);
	}
	
	static function _echo_as_xml($arr){
		foreach ($arr as $k => $val){
			$item = (is_numeric($k) ? 'item-':'').$k;
			echo '<', $item, '>';
			if(is_array($val)){
				self::_echo_as_xml($val);
			}else{
				echo '<![CDATA[',$val,']]>';
			}
			echo '</', $item, '>';
		}
	}
	
	function echoex($data, $errcode='', $redirect_url=''){
		if('json' == $this->env['client_accept'] 
			|| 'xml' == $this->env['client_accept'])
		{
			$out = array('retcode' => empty($errcode) ? 'SUCCESS': $errcode, 'data' => $data);
			if(empty($errcode)){
				$out = array('retcode'=>'SUCCESS', 'data'=>$data);
			}else{
				$out = array('retcode'=>$errcode, 'error'=>$data, 'data'=>null);
			}
			if('json' == $this->env['client_accept'])
				echo json_encode($out);
			else{
				echo '<?xml version="1.0" standalone="yes"?><response>';
				self::_echo_as_xml($out);
				echo '</response>';
			}
			if($this->log_api != null){
				if(function_exists('ob_start')){
					ob_start();
					CDbPool::getInstance()->cli();
					CMemcachedPool::getInstance()->cli();
					$other = ob_get_clean();
				}
				$this->log_api->write($out, $other);
			}
		}else{
			$style = $msg = '';
			if(empty($errcode)){
				$style = 'success'; 
				$msg =   is_string($data) ? $data : '';
			}else{
				$style = 'error';
				$msg   = $data.'('.$errcode.')';
			}
			$meta = '';
			if(empty($msg) && !empty($redirect_url)){
				header('Location: '.$redirect_url);
				return ;
			}
			else if(!empty($redirect_url)){
				$meta = '<meta http-equiv="Refresh" content="3;'.$redirect_url.'">';
				$msg .= sprintf('<p style="text-align:right;font-size: 12px;padding: 0 10px;">%s&nbsp;<a href="%s">%s</a></p>', 
						$this->lang('click_if_not_redirect', 'common'), $redirect_url, $redirect_url);
			}
			echo sprintf($this->lang('notice_page', 'common'), $meta, $style, $msg);
		}
	}
}

?>
