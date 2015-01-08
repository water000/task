<?php
/**
 * @depend ZipArchive , preg
 * @depend-constent: RTM_APP_ROOT, CFG_WEB_ROOT,CFG_SYS_ROOT
 * @author Administrator
 *
 */

class CFileType {
	
	CONST ENV_COMPILE = 1;
	CONST ENV_RUNTIME = 2;
	
	CONST FT_CLASS  = 'class';
	CONST FT_ACTION = 'action';
	CONST FT_JS     = 'js';
	CONST FT_CSS    = 'css';
	CONST FT_IMG    = 'img';
	CONST FT_OTHER  = 'other';
	CONST FT_MODDEF = 'moddef';
	CONST FT_DATA   = 'data';
	CONST FT_UPLOAD = 'upload';
	
	private static $commonType = array(
		self::FT_CLASS, self::FT_ACTION,
		self::FT_JS,    self::FT_CSS,
		self::FT_IMG,   self::FT_OTHER,
		self::FT_MODDEF,
	);
	
	CONST DEFAULT_LOG = 'default.log';
	
	CONST ACTION_FILE_SUFFIX = '.php';
	CONST ACTION_URL_SUFFIX = '.php';
	CONST CLASS_FILE_SUFFIX = '.php';
	
	private static $arrModDefObjBuf = array();
	
	static function getTypes(){
		return self::$commonType;
	}
	
	static function hasType($type){
		return (in_array($type, self::$commonType)
			|| $type == self::FT_DATA 
			|| $type == self::FT_UPLOAD);
	}
	
	static function rmdir($dir, $bFileOnly=false)
	{
		if(!file_exists($dir))
			return;
		if('/' != $dir[strlen($dir) -1])
			$dir .= '/';
		$dh = opendir($dir);
		if(!$dh)
			return;
		while(false !== ($file = readdir($dh)))
		{
			if('.' == $file || '..' == $file)
				continue;
			$path = $dir.$file;
			if(is_dir($path))
				self::rmdir($path.'/');
			else
				unlink($path);
		}
		closedir($dh);
		if(!$bFileOnly)
			rmdir($dir);
	}
	
	static function initRuntimeEnv(){
		/*foreach(self::$commonType as $type)
			mkdir(RTM_APP_ROOT.$type);
		mkdir(RTM_APP_ROOT.self::FT_UPLOAD);*/
	}
	
	private static function _createCompileTime($mod){
		$moddir = CFG_SYS_ROOT.$mod.'/';
		mkdir($moddir);
		foreach(self::$commonType as $type)
			mkdir($moddir.$type);
		mkdir($moddir.self::FT_DATA);
	}
	private static function _removeCompileTime($mod){
		$moddir = CFG_SYS_ROOT.$mod.'/';
		self::rmdir($moddir);
	}
	private static function _clearCompileTime($mod){
		$moddir = CFG_SYS_ROOT.$mod.'/';
		foreach(self::$commonType as $type)
			self::rmdir($moddir.$type, true);
	}
	
	private static function _createRunTime($mod){
		foreach(self::$commonType as $type)
			mkdir(RTM_APP_ROOT.$type.'/'.$mod);
		mkdir(RTM_APP_ROOT.self::FT_UPLOAD.'/'.$mod);
	}
	private static function _removeRunTime($mod){
		foreach(self::$commonType as $type)
			self::rmdir(RTM_APP_ROOT.$mod.'/'.$type);
		self::rmdir(RTM_APP_ROOT.self::FT_UPLOAD.'/'.$mod);
	}
	private static function _clearRunTime($mod){
		foreach(self::$commonType as $type)
			self::rmdir(RTM_APP_ROOT.$mod.'/'.$type, true);
	}

	static function createEnv($mod){
		self::_createCompileTime($mod);
		self::_createRunTime($mod);
	}
	
	static function removeEnv($mod){
		self::_removeCompileTime($mod);
		self::_removeRunTime($mod);
	}
	
	static function clearEnv($mod){
		self::_clearCompileTime($mod);
		self::_clearRunTime($mod);
	}
	
	static function hasMod($mod){
		return $mod != '' && $mod != '.' && false === strpos($mod, '..') &&
			file_exists(CFG_SYS_ROOT.$mod) && is_dir(CFG_SYS_ROOT.$mod);
	}
	
	static function getDir($env=self::ENV_COMPILE, $mod,$type){
		return $env == self::ENV_COMPILE ?
			CFG_SYS_ROOT.$mod.'/'.$type.'/' : 
			RTM_APP_ROOT.$mod.'/'.$type.'/' ;
	}
	
	private static function _path($dir, $name){
		static $tree = '';
		
		$path = $dir.$name;
		if(file_exists($path))
			return ($tree != '' ? $tree.'/':'').$name;
		$arr = scandir($dir);
		foreach($arr as $f){
			if($f != '.' && $f != '..' && is_dir($f)){
				$tree .= ($tree != '' ? '/':'').$f;
				if(($path = self::_path($dir.$f.'/', $name)) != '')
					return $path;
				$rpos = strrpos($tree, '/');
				$tree = $rpos === false ? '' : substr($tree, 0, $rpos);
			}
		}
		return '';
	}
	
	static function getPath($env=self::ENV_COMPILE, $mod, $file, $type){
		if(empty($file))
			return '';
		if(self::FT_CLASS == $type && false === strpos($file, '.'))
			$file .= self::CLASS_FILE_SUFFIX;
		else if(self::FT_ACTION == $type && false === strpos($file, '.'))
			$file .= self::ACTION_FILE_SUFFIX;
		return self::getDir($env, $mod, $type).$file;
	}
	
	static function search($env, $mod, $file, $type){
		if(self::FT_CLASS == $type && false === strpos($file, '.'))
			$file .= self::CLASS_FILE_SUFFIX;
		else if(self::FT_ACTION == $type && false === strpos($file, '.'))
			$file .= self::ACTION_FILE_SUFFIX;
		return self::_path(self::getDir($env, $mod, $type), $file);
	}
	
	static function getLogPath($mod, $file=self::DEFAULT_LOG){
		return self::getPath(self::ENV_COMPILE, $mod, $file, self::FT_DATA);
	}
	static function writeLog($str){
		$fp = fopen(self::getLogPath('core'), 'a');
		if(!$fp)
			return false;
		fwrite($fp, $str);
		fclose($fp);
	}
	
	static function getURL($mod, $file, $type, $ajax=0){
		if($type != self::FT_ACTION)
			return CFG_WEB_ROOT.$mod.'/'.$type.'/'.$file;
			
		$pos = strpos($file, '.');
		if($pos !== false)
			$file = substr($file, 0, $pos);
		$file = str_replace('/', '.', $file);
		return CFG_WEB_ROOT.'index'.self::ACTION_URL_SUFFIX
			.'?a='.$mod.'.'.$file.($ajax ? '&ajax=1':'');
	}
	
	static function checkRequest(){
		$ret = null;
		if(isset($_REQUEST['a'])){ //'a' defined in self::getURL
			$ret = explode('.', $_REQUEST['a'], 2);
			if(2 == count($ret) && self::hasMod($ret[0])){
				$ch = $ret[1][0];
				if('_' == $ch || ($ch >= 'a' && $ch <= 'z') || 
					($ch >= 'A' && $ch <= 'Z'))
				{
					$ret[1] = str_replace('.', '/', $ret[1]);
					$ret[] = self::getPath(self::ENV_RUNTIME, $ret[0], 
						$ret[1], self::FT_ACTION);
					$ret[] = isset($_REQUEST['ajax']);
				}
			}
		}
		return $ret;
	}
	
	/**
	 * @desc the dir has only one file which must be moddef
	 * @param $mod
	 */
	static function getModDef($mod){
		if(isset(self::$arrModDefObjBuf[$mod]))
			return self::$arrModDefObjBuf[$mod];
		$obj = null;
		$envs = array(self::ENV_RUNTIME, self::ENV_COMPILE);
		foreach($envs as $env){
			$dir = self::getDir($env, $mod, self::FT_MODDEF);
			if(is_dir($dir)){
				$arr = scandir($dir);
				foreach($arr as $f){
					if($f != '.' && $f != '..'){
						$class = substr($f, 0, strpos($f, '.'));
						if(!class_exists($class))
							require_once $dir.$f;
						if(class_exists($class)){
							$obj = new $class;
							if(! $obj instanceof IModDef)
								$obj = null;
						}
						break 2;
					}
				}
			}
		}
		self::$arrModDefObjBuf[$mod] = $obj;
		return $obj;
	}
	
	static function extractToCompileEnv($mod, $zippath){
		$zip = new ZipArchive;
		$res = $zip->open($zippath);
		if(true === $res){ // make sure that the dest dir is empty
			$zip->extractTo(CFG_SYS_ROOT);
			$zip->close();
			return CFG_SYS_ROOT.$mod.'/';
		}else return false;
	}
	
	/**
	 * extract the install package to the compiled env
	 * @param string $mod
	 * @param string $zippath an existance zip
	 * @return return an error or empty
	 */
	static function extract($mod, $zippath){
		$zip = new ZipArchive();
		
		$ret = $zip->open($zippath);
		if($ret != true){
			return 'faild to open zip: '.$zippath.', error code: '.$ret;
		}
		
		$err = '';
		$type_count = array();
		
		for($i=0; $i<$zip->numFiles; ++$i){
			$name = $zip->getNameIndex($i);
			if(0 == $i){
				if($name != $mod.'/'){
					$err = 'zip name is not equal to dir name : '.$mod.'!='.$name;
					break;
				}
			}
			if(strpos($name, $mod) !== 0){
				$err = 'invalid structure of the archive: '.$name;
				break;
			}
			$path = CFG_SYS_ROOT.$name;
			$arr = explode('/', $name);
			if('/' == $name[strlen($name)-1]){
				if($i != 0 && !self::hasType($arr[1])){
					$err = 'invalid file type: '.$arr[1];
					break;
				}
				if(!file_exists($path))
					mkdir($path);
			}
			else{
				file_put_contents($path, $zip->getFromIndex($i));
				if(!isset($type_count[$arr[1]]))
					$type_count[$arr[1]] = 1;
				else
					++$type_count[$arr[1]];
			}
		}
		$zip->close();
		
		if(empty($err)){
			if(!isset($type_count[self::FT_MODDEF]) || 0 == $type_count[self::FT_MODDEF]){
				$err = 'no moddef file found';
			}
			if($type_count[self::FT_MODDEF] > 1){
				$err = 'only one def file can exists';
			}
			if(0 == $type_count[self::FT_ACTION] && 0 == $type_count[self::FT_CLASS]){
				$err = 'both action and class are empty';
			}
		}
		
		if(!empty($err)){
			self::rmdir(CFG_SYS_ROOT.$mod);
		}
		
		return $err;
	}
	
	/**
	 * @desc 'class' format: FILE_NAME<class_name, class_name2,...>
	 * @param string $str
	 * @return array(0=>FILE_NAME, [1-n => class_name])
	 */
	static function parseClassDef($str){
		$match = array();
		$ret = array('', '');
		if(preg_match('/([^<]+)(?(?=<)(<((?:\s*\w+\s*[,]?)+)>))/', $str, $match)){
			if(empty($match[2])){
				$class = explode(self::CLASS_FILE_SUFFIX, $str, 2);
				if(2 == count($class)){
					$ret[0] = $match[1];
					$ret[1] = $class[0];
				}else{
					$ret[0] = $match[1].self::CLASS_FILE_SUFFIX;
					$ret[1] = $class[0];
				}
			}else{
				$ret[0] = $match[1];
				$ret = array_merge($ret, explode(',', $match[2]));
			}
		}
		return $ret;
	}
	
	/*
	 * @desc import('core', 'class1.php', 'class2.php',..)
	 * @mod: module name
	 * @files: $filename
	 */
	static function import($mod, $files){
		 $args = func_get_args();
		 $numargs = func_num_args();
		 for($i=1; $i<$numargs; ++$i){
		 	$path = self::getPath(CFileType::ENV_RUNTIME, 
				$mod, $args[$i],CFileType::FT_CLASS);
			if(file_exists($path))
				require_once $path;
			else
				require_once self::getPath(CFileType::ENV_COMPILE, 
					$mod, $args[$i],CFileType::FT_CLASS);
		 }
	}
	
	static function getModules(){
		$ret = array();
		$d = dir(CFG_SYS_ROOT);
		if($d){
			while (false !== ($entry = $d->read())) {
			  if($entry != '.' && $entry != '..' 
			  	&& is_dir(CFG_SYS_ROOT.$entry))
			  {
			  	$ret [] = $entry;
			  }
			}
			$d->close();
		}
		return $ret;
	}
	
	static function getFileType($filename){
		$rpos = strrpos($filename, '.');
		if($rpos === false)
			return self::FT_ACTION;
		$sfx = strtolower(substr($filename, $rpos+1));
		if($sfx == CFileType::FT_JS || $sfx == CFileType::FT_CSS)
			return $sfx;
		else if('.'.$sfx == self::ACTION_FILE_SUFFIX)
			return self::FT_ACTION;
		else
			return CFileType::FT_IMG;
	}
}

?>