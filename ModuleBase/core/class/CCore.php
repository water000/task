<?php

class CCore {
	private static $insbuf = array();
	private static $mod = ''; // the module name of current action
	
	//array(array(mod, path, class),...)
	private static $listeners = array();
	private static $status = 0;
	
	CONST O_HTML = 0;
	CONST O_AJAX = 1;
	CONST O_CLI  = 2;
	private static $otype = self::O_HTML;
	
	static function setOutput($type){
		self::$otype = $type;
	}
	static function getOutput(){
		return self::$otype;
	}
	
	private static function _htmlAbort($param){
		echo '<!doctype html><html><head><title>ERROR</title></head><body><h3><center>ERROR DETAIL</center></h3><ul>';
		if(!is_array($param))
			$param = array($param);
		foreach($param as $str)
			echo '<li>', htmlspecialchars($str), '</li>';
		echo '</ul></body></html>';
	}
	private static function _cliAbort($param){
		echo 'ERROR DETAIL', "\n";
		$count = 0;
		foreach($param as $str)
			echo ++$count, ')', $str, "\n";
	}
	private static function _xmlAbort($param){
		echo '<success>0</success>';
		foreach($param as $str)
			echo '<error><![CDATA[', $str, ']]></error>';
	}
	//some destructed functions need to konw the exited status of the current script, normal or abnormal
	static function hasAborted(){
		return 1 == self::$status;
	}
	static function abort($param='System aborted'){
		if(!is_array($param))
			$param = array($param);
		self::$status = 1;
		
		if(self::$otype == self::O_CLI)
			self::_cliAbort($param);
		else if(self::$otype == self::O_HTML)
			self::_htmlAbort($param);
		else 
			self::_xmlAbort($param);
		exit;
	}
	
	static function setCurrentModule($mod){
		self::$mod = $mod;
	}
	static function getCurrentModule(){
		return self::$mod;
	}
	
	/**
	 * @desc register a instance for others calling, normally using in 'common' module
	 * @param string $class class name
	 * @param object $ins class instance
	 */
	static function regInstance($class, $ins){
		self::$insbuf[$class] = $ins;
	}
	static function getInstance($class){
		return isset(self::$insbuf[$class]) ? self::$insbuf[$class] : null;
	}
	
	static function appendListener($path, $mod, $class){
		self::$listeners[] = array($path, $mod, $class);
	}
	static function formatAndClearListener(){
		$ret = count(self::$listeners) > 0 
			? var_export(self::$listeners, true) : '';
		self::$listeners = array();
		return $ret;
	}
	static function setListeners($arr){
		self::$listeners = $arr;
	}
	static function getListeners(){
		return self::$listeners;
	}
	static function runListeners(){
		if(count(self::$listeners) > 0){
			require_once dirname(__FILE__).'/IListenner.php';
			foreach(self::$listeners as $arr){
				list($path, $mod, $class) = $arr;
				CFileType::import($mod, $path);
				if(class_exists($class)){
					$obj = new $class;
					if($obj instanceof IListenner)
						$obj->run();
				}
			}
		}
	}
}

?>