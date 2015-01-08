<?php
/**
 * @depend-lib: core.CFileType.php, core.IModDef.php, core.CCore, iconv, 
 * 	core.IModInstall.php, core.CModDefChk.php, PDO, core.CTemplete.php
 *  common.CObjectDB
 * @depend-constants: CFG_CHARSET
 * @author wanghu8242837@163.com
 *
 */
require_once dirname(__FILE__).'/CModDefChk.php';
require_once dirname(__FILE__).'/CTemplete.php';

class CModule {
	private $error = array();
	private $name = '';
	private $path = '';
	private $moddef = null;
	private $oFileParser = null;
	private $oPdoConn = null;
	
	function getErrorMsg(){
		return $this->error;
	}
	
	private function setErrorMsg($err){
		$this->error[] = $err;
	}
	
	function setModule($name){
		$this->name = $name;
	}
	
	function __construct($oPdoConn, $modname=''){
		$this->oPdoConn = $oPdoConn;
		$this->name = $modname;
	}
	
	function clear(){
		$this->name = '';
		$this->path = '';
		$this->moddef = null;
	}
	
	function _init($sZipPath, $sZipName=''){
		$this->clear();
		
		$this->path = $sZipPath;
		if(empty($sZipName)){
			$rpos = strrpos($sZipPath, '/');
			if(false === $rpos){
				$rpos = strrpos($sZipPath, '\\');
			}
			$sZipName = false === $rpos ? $sZipPath : substr($sZipPath, $rpos+1);
		}
		$sZipName = strtolower($sZipName);
		list($name, ) = explode('.', $sZipName, 2);
		if('' == $this->name)
			$this->name = $name;
		else
			return $this->name == $name;
		return true;
	}
	
	private function _file($mod, $name, $type, $charset='', $path=''){
		if('' == $path){
			$treename = CFileType::search(CFileType::ENV_COMPILE, 
				$mod, $name, $type);
			if('' == $treename){
				$this->setErrorMsg(sprintf('file "%s" not found', $name));
				return false;
			}
			$path = CFileType::getDir(CFileType::ENV_COMPILE, $mod, $type).$treename;
			$name = $treename;
		}
		$content = file_get_contents($path);
		if(empty($content))
			return true;
		
		$name = basename($name);
		if($type != CFileType::FT_IMG && $type != CFileType::FT_OTHER){
			if($type == CFileType::FT_ACTION){
				$content = CTemplete::parseContent($content, $mod, $name);
				if(false === $content){
					$this->error += CTemplete::getError();
					return false;
				}
				$pdos = $this->oPdoConn->query(sprintf(
					'SELECT class_path, class_module, class_name FROM %s WHERE action_module="%s" AND action_name="%s"',
					CObjectDB::formatTable('core_module_listenner'), $mod, self::_lisacname($name)
				));
				foreach($pdos as $row)
					CCore::appendListener($row['class_path'], $row['class_module'], $row['class_name']);
				$ret = CCore::formatAndClearListener();
				if($ret != '')
					$content .= sprintf('<php CCore::setListeners(%s); ?>', $ret);
				$content = '<?php defined(\'IN_INDEX\') or exit(\'access denied\') ?>' . $content;//defined in index.php
			}
			$content = $this->oFileParser->getResult($mod, $content);
			$warn = $this->oFileParser->getWarnings();
			if(count($warn) > 0)
				$this->error += $warn;
			if($charset != '' && strcasecmp($charset, CFG_CHARSET) != 0)
				$content = iconv($charset, CFG_CHARSET, $content);
		}
		return file_put_contents(CFileType::getPath(CFileType::ENV_RUNTIME, 
			$mod, $name, $type), $content);
	}
	
	private function _recdir($dir, $filetype, $charset){
		static $tree = '';

		$arr = scandir($dir);
		foreach($arr as $f){
			if($f != '.' && $f != '..'){
				if(is_dir($dir.$f)){
					$tree .= ($tree != '' ? '/':'').$f;
					$ndir = CFileType::getDir(CFileType::ENV_RUNTIME, $this->name, $filetype).$tree;
					if(!file_exists($ndir))
						mkdir($ndir);
					$this->_recdir($dir.$f.'/', $filetype, $charset);
					$rpos = strrpos($tree, '/');
					$tree = $rpos === false ? '' : substr($tree, 0, $rpos);
				}else{
					$this->_file($this->name, $tree.'/'.$f, $filetype, $charset, $dir.$f);
				}
			}
		}
	}
	
	private function _files($filetype, $charset){
		$dir = CFileType::getDir(CFileType::ENV_COMPILE, $this->name, $filetype);
		if(file_exists($dir)){
			$ndir = CFileType::getDir(CFileType::ENV_RUNTIME, $this->name, $filetype);
			if(!file_exists($ndir))
				mkdir($ndir);
			$this->_recdir($dir, $filetype, $charset);
		}
	}
	
	private function _operfiles($charset){
		$dir = CFileType::getDir(CFileType::ENV_RUNTIME, $this->name, '');
		if(!file_exists($dir))
			mkdir($dir);
		foreach(CFileType::getTypes() as $type){
			$this->_files($type, $charset);
		}
	}
	
	private function _tables($pdoconn, $tabledef){
		foreach($tabledef as $name => $def){
			$ret = $pdoconn->exec(sprintf('CREATE TABLE IF NOT EXISTS %s%s CHARACTER SET=%s', 
				CObjectDB::formatTable($name), $def, str_replace('-', '', CFG_CHARSET)));
			if(false === $ret){
				list($id, $code, $str) = $pdoconn->errorInfo();
				if($id != '00000'){
					$this->setErrorMsg($name.':'.$str);
					return false;
				}
			}
		}
		return true;
	}
	
	private static function _lisacname($action){
		$pos = strpos($action, '.');
		return $pos === false ? $action.CFileType::ACTION_FILE_SUFFIX : $action;
	}
	
	private function _listeners($pdoconn, $def){
		$lis = array();
		foreach($def as $class => $actions){
			$classinfo = CFileType::parseClassDef($class);
			$classpath = CFileType::getPath(CFileType::ENV_COMPILE, 
				$this->name, $classinfo[0], CFileType::FT_CLASS);
			
			$arr = explode(',', $actions);
			foreach($arr as $modac){
				//the action for listening may not be existed before the current mod installing.
				//so, we save it to database and install it until existing in sometime
				list($mod, $action) = explode('.', $modac, 2);
				$actionpath = CFileType::getPath(CFileType::ENV_COMPILE, 
					$mod, $action, CFileType::FT_ACTION);
				$action = self::_lisacname($action);				
				$ret = $pdoconn->exec(sprintf(
					'INSERT INTO %s VALUES("%s", "%s", "%s", "%s", "%s")', 
					CObjectDB::formatTable('core_module_listenner'),
					$classinfo[0], $this->name, $classinfo[1], $mod, $action)
				);
				$_err = false;
				if(0 == $ret){
					list($id, $code, $str) = $pdoconn->errorInfo();
					if($id != null){
						$this->setErrorMsg($str);
						$_err = true;
					}
				}else{
					$lis[] = array($classinfo[0], $classinfo[1], $mod, $action);
				}
				if(!$_err && file_exists($actionpath)){
					$modinfo = CFileType::getModDef($mod);
					$charset = isset($modinfo[IModDef::M_CS]) ? $modinfo[IModDef::M_CS] : '';
					$this->_file($mod, $action, CFileType::FT_ACTION, $charset, $actionpath);
				}
				
			}
		}
		return $lis;
	}
	
	private function _mod(){
		$error = CFileType::extract($this->name, $this->path);
		if($error != ''){
			$this->setErrorMsg($error);
			return false;
		}
		$this->moddef = CFileType::getModDef($this->name);
		if(!$this->moddef){
			$this->setErrorMsg(sprintf('"%s" moddef was not found',  $this->name));
			return false;
		}
		if(! $this->moddef instanceof IModDef ){
			$this->setErrorMsg(sprintf('"%s" moddef was not instance of IModDef interface', $this->name));
			return false;
		}
		
		$modinfo = $this->moddef->desc();
		list($error, $warning) = CModDefChk::syntax($modinfo);
		$this->error += $error;
		$this->error += $warning;
		if(count($error) > 0)
			return false;
			
		if($this->name != $modinfo[IModDef::MOD][IModDef::G_NM]){
			$this->setErrorMsg(sprintf('dir "%s" not equal to name def "%s"', 
				$this->name, $modinfo[IModDef::MOD][IModDef::G_NM]));
			return false;
		}
		
		if(isset($modinfo[IModDef::TBDEF]) && 
			count($modinfo[IModDef::TBDEF]) > 0)
		{
			if(!$this->_tables($this->oPdoConn, $modinfo[IModDef::TBDEF]))
				return false;
		}
		
		$charset = $modinfo[IModDef::MOD][IModDef::M_CS];
		$this->_operfiles(strcasecmp($charset, CFG_CHARSET) != 0 ? $charset : '');
		
		if(isset($modinfo[IModDef::LTN])){
			$this->oPdoConn->exec(sprintf(
				'DELETE FROM %s WHERE class_module="%s"', 
				CObejctDB::formatTable('core_module_listenner'), $this->name)
			);
			$this->_listeners($this->oPdoConn, $modinfo[IModDef::LTN]);
		}
		
		return $modinfo;
	}
	
	function install($sZipPath, $sZipName, $oFileParser){
		$this->_init($sZipPath, $sZipName);
		$this->oFileParser = $oFileParser;
		
		if(CFileType::hasMod($this->name)){
			CFileType::clearEnv($this->name);
		}
		
		$modinfo = $this->_mod();
		if(!$modinfo)
			return false;
		
		if(isset($modinfo[IModDef::STL]) 
			&& $modinfo[IModDef::STL] != ''){
			list($fname,$cname) = CFileType::parseClassDef($modinfo[IModDef::STL]);
			CFileType::import($this->name, $fname);
			$st = new $cname;
			if($st instanceof IModInstall){
				$st->install($this->name);
			}else $this->setErrorMsg(sprintf('%s is not instanceof "IModInstall"', $modinfo[IModDef::STL]));
		}
		return $modinfo;
	}
	
	function update($sZipPath, $sZipName, $oFileParser){
		$this->oFileParser = $oFileParser;
		if(!$this->_init($sZipPath, $sZipName)){
			$this->setErrorMsg(sprintf('%s is not equal to %s', $this->name, $sZipPath));
			return false;
		}
		
		CFileType::clearEnv($this->name);
		
		$pdos = $this->oPdoConn->query(sprintf(
			'SELECT * FROM %s  WHERE class_module="%s"',  
			CObjectDB::formatTable('core_module_listenner'), $this->name)
		);
		$oldlis = array();
		foreach($pdos as $row)
			$oldlis[$row['action_module'].'.'.$row['action_name']] = $row;
		
		$modinfo = $this->_mod();
		if(!$modinfo)
			return false;
		
		$pdos = $this->oPdoConn->query(sprintf(
			'SELECT * FROM %s  WHERE class_module="%s"',  
			CObjectDB::formatTable('core_module_listenner'), $this->name)
		);
		foreach($pdos as $row){
			if(isset($oldlis[$row['action_module'].'.'.$row['action_name']]))
				unset($oldlis[$row['action_module'].'.'.$row['action_name']]);
		}
		$srcmod = $this->name;
		foreach($oldlis as $row){
			$moddef = CFileType::getModDef($row['action_module']);
			if($moddef != null){
				$info = $moddef->desc();
				$this->_file($row['action_module'], $row['action_name'], CFileType::FT_ACTION, 
					$info[IModDef::MOD][IModDef::M_CS]);
			}
		}
		$this->name = $srcmod;
		
		if(isset($modinfo[IModDef::STL]) 
			&& $modinfo[IModDef::STL] != ''){
			list($fname,$cname) = CFileType::parseClassDef($modinfo[IModDef::STL]);
			CFileType::import($this->name, $fname);
			$st = new $cname;
			if($st instanceof IModInstall){
				$st->update($this->name);
			}else $this->setErrorMsg(sprintf('%s is not instanceof "IModInstall"', $modinfo[IModDef::STL]));
		}
		return $modinfo;
	}
	
	function delete(){
		CFileType::removeEnv($this->name);
		
		$modinfo = CFileType::getModDef($this->name);
		if(isset($modinfo[IModDef::STL]) 
			&& $modinfo[IModDef::STL] != ''){
			list($fname,$cname) = CFileType::parseClassDef($modinfo[IModDef::STL]);
			CFileType::import($this->name, $fname);
			$st = new $cname;
			if($st instanceof IModInstall){
				$st->uninstall($this->name);
			}else $this->setErrorMsg(sprintf('%s is not instanceof "IModInstall"', $modinfo[IModDef::STL]));
		}
		
		$srcmod = $this->name;
		$pdos = $this->oPdoConn->query(sprintf(
			'SELECT * FROM %s  WHERE class_module="%s"',  
			CObjectDB::formatTable('core_module_listenner'), $this->name)
		);
		$ret = $pdos->fetchAll();
		$this->oPdoConn->exec(sprintf(
			'DELETE FROM %s WHERE class_module="%s"', 
			CObjectDB::formatTable('core_module_listenner'), $this->name)
		);
		foreach($ret as $row){
			$moddef = CFileType::getModDef($row['action_module']);
			if($moddef != null){
				$info = $moddef->desc();
				$this->name = $row['action_module'];
				$this->_file($row['action_name'], CFileType::FT_ACTION, 
					$info[IModDef::MOD][IModDef::M_CS]);
			}else $this->error[] = sprintf('No such mod "%s" exists', $row['action_module']);
		}
		$this->name = $srcmod;
	}
	
	function updateFile($name, $type, $path, $oFileParser){
		$ch = $name[0];
		if('_' == $ch || ($ch >= 'a' && $ch <= 'z') || 
			($ch >= 'A' && $ch <= 'Z'))
			;
		else{
			$this->setErrorMsg(sprintf('invalid file name "%s"', $name));
			return false;
		}
		$this->oFileParser = $oFileParser;
		$moddef = CFileType::getModDef($this->name);
		$info = $moddef->desc();
		return $this->_file($this->name, $name, $type, $info[IModDef::MOD][IModDef::M_CS], $path);
	}
}

?>