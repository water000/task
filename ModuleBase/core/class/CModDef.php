<?php
/**
 * @desc 
 * G_*:global object(module, action, tag, filter..)key
 * M_*:module key
 * A_*:action key
 * 'class' format: FILE_NAME<class_name, class_name2,...>.
 * the class_name must be defined in FILE_NAME
 * if FILE_NAME not present, '<>' must not be present and the class_name can be only one,
 * the class_name plus the CFileTypeDef::CLASS_FILE_SUFFIX will be the FILE_NAME.
 * if class_name not present, the FILE_NAME consiste of class_name and(or) CFileTypeDef::CLASS_FILE_SUFFIX,
 * if CFileTypeDef::CLASS_FILE_SUFFIX not present, the FILE_NAME was treated as class_name, 
 * then the CFileTypeDef::CLASS_FILE_SUFFIX was appended to class_name to get the FILE_NAME
 * @author Administrator
 * @dependent: CAppEnvironment.php, index.php:mbs_moddef()
 *
 */
abstract class CModDef {

	//an valid identifier([_a-zA-Z][_a-zA-Z0-9]*)
	CONST G_NM = 'name';
	CONST G_CS = 'class';
	CONST G_DC = 'desc';
	
	CONST MOD  = 'module';
	CONST M_CS = 'charset';
	CONST M_TR = 'introduce';
	
	CONST FTR = 'filter'; // define filters for using
	CONST TAG = 'tag'; // define tags for using
	
	CONST PAGES    = 'pages';  // the pages in the module.
	CONST PAGE_ARG = 'page_arg';
	CONST PARG_TYP = 'type';    // the arg's type what appears in gettype(). default is 'string'
	CONST PARG_REQ = 'required';// the arg MUST be required in the page. default is 1
	CONST PARG_DEP = 'depend'; // the arg which appears in the same page. default is null
	CONST PARG_EMP = 'empty';  // allow emmpty on the arg(default is 0). NOTICE: the empty validation only check the length of the trimed arg
	CONST PARG_TRI = 'trim';   // ignore triming on the arg if set to 0. default is 1(trim).
	CONST PARG_RNG = 'range'; // the range of the arg's length. split by comma, like [5,12)
	CONST PAGE_MGR = 'page_mgr'; // indicate wether the page is a managment page
	
	CONST TBDEF  = 'table_def';
	CONST LTN    = 'listener';
	CONST MGR    = 'mgrpage';
	CONST LD_FTR = 'load_filter'; // do filter checking on each script in the module
	CONST DEPEXT = 'depend_extension'; // checking wether the current environment include the extension. = array(ext1, ext2, ...)
	
	private static $appenv = null;
	private $desc = null;
	
	
	/**
	 * @return array(
	 *   self::MOD => array(self::M_CS=>'gbk', G_NM=>'mod'),
	 *   self::FTR => array(
	 *   	'name' => array(G_CS => '', G_DC => ''),
	 *   	... 
	 *   ),
	 *   self::TAG => array(
	 *  	 'name' => array(G_CS => '', G_DC => ''),
	 *   	... 
	 *   ),
	 *   self::PAGES => array(
	 *   	'a' => array( // CAN NOT append file suffix like 'a.php'
	 *      	self::PAGE_ARG => array(
     *   			'arg1' => array('type'=>'int', required=>true, 'range'=>'5,12'),
	 *   			'arg2' => array('type'=>'int', required=>true, depend=>'arg1')
	 *  		),
	 *			self::MGR => true,
	 *      )
	 *   ),
	 * 	 self::TBDEF => array(
	 * 		'name' => '',
	 * 		....
	 * 	 ),
	 *   self::LTN => array(
	 *   	'class' => 'mod.action1,mod.action2,...'
	 *   ),
	 *   self::LD_FTR=>array(
	 *   	array(mod, ftr_name, isExitOnFilterUndefined, arg1, arg2, ...),
	 *   	...
	 *   ),
	 * ) 
	 */
	abstract protected function desc();
	
	/**
	 *@$appenv, an instance of CAppEnvrironment
	 */
	function __construct($appenv){
		self::$appenv = $appenv;
		$this->desc = $this->desc();
	}
	
	function item($key, $subkey=''){
		if(empty($subkey)){
			return isset($this->desc[$key]) ? $this->desc[$key] : null;
		}else{
			return isset($this->desc[$key][$subkey]) ? $this->desc[$key][$subkey] : null;
		}
	}
	
	
	static function isIdentifier($str){
		$ret = false;
		
		if(($str[0]>='a' && $str[0]<='z') ||
			($str[0]>='A' && $str[0]<='Z') || '_' == $str[0])
		{
			for($i=1,$j=strlen($str); $i<$j; ++$i){
				if( ($str[$i]>='a' && $str[$i]<='z') ||
					($str[$i]>='A' && $str[$i]<='Z') ||
					($str[$i]>='0' && $str[$i]<='9') || 
					'_' == $str[$i] )
					;
				else break;
			}
			$ret = $i == $j;
		}
		
		return $ret;
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
				$class = explode(self::$appenv->item('class_file_suffix'), $str, 2);
				if(2 == count($class)){
					$ret[0] = $match[1];
					$ret[1] = $class[0];
				}else{
					$ret[0] = $match[1].self::$appenv->item('class_file_suffix');
					$ret[1] = $class[0];
				}
			}else{
				$ret[0] = $match[1];
				$ret = array_merge($ret, explode(',', $match[2]));
			}
		}
		return $ret;
	}
	
	private static function _class_exists($mod, $class){
		$path = self::$appenv->getClassPath($mod, $class);
		if(file_exists($path)){
			require_once $path;
			$c = strpos($class, '/');
			$c = $c === false ? $class : substr($class, $c+1);
			return class_exists($c);
		}
		return false;
	}
	
	private static function _mod($desc, &$modname, &$error, &$warning){
		if(isset($desc[self::MOD])){
			$mod = $desc[self::MOD];
			if(isset($mod[self::G_NM])){
				if(!self::isIdentifier($mod[self::G_NM]))
					$error[] = sprintf('invalid identifier "%s" on "%s" in "%s"', 
						$mod[self::G_NM], self::G_NM, self::MOD);
				else $modname = $mod[self::G_NM];
			}else $error[] = sprintf('"%s" not def in "%s"', self::G_NM, self::MOD);
			
			if(isset($mod[self::M_CS]) && false == iconv('ascii', $mod[self::M_CS], 'a'))
				$error[] = sprintf('unsupportted "%s" on "%s" in "%s"',
					$mod[self::M_CS], self::M_CS, self::MOD);
						
		}else $error[] = sprintf('"%s" not def in ROOT', self::MOD);
	}
	
	private static function _tag($tag, $mod, &$error, &$warning){
		if(!is_array($tag)){
			$error[] = sprintf('need ARRAY, "%s" was given in "%s" def', 
				gettype($tag), self::TAG);
			return ;
		}
		foreach($tag as $name => $arr){
			if(self::isIdentifier($name)){
				if(is_array($arr)){
					if(isset($arr[self::G_CS]) && $arr[self::G_CS] != ''){
						if(!self::_class_exists($mod, $arr[self::G_CS]))
							$error[] = sprintf('"%s" not exist on "%s" in "%s" def',
								$arr[self::G_CS], $name, self::TAG);
					}else $error[] = sprintf('lose "%s" on "%s" in "%s" def', 
						self::G_CS, $name, self::TAG);
						
					if(isset($arr[self::G_DC]) && $arr[self::G_DC] != '')
						;
					else $error[] = sprintf('lose "%s" on "%s" in "%s" def', 
						self::G_DC, $name, self::TAG);
					
				}else $error[] = sprintf('need ARRAY, "%s" was given on "%s" in "%s" def',
					gettype($arr), $name, self::TAG);
			}else $error[] = sprintf('invalid identifier "%s" in "%s" def', 
					$name, self::TAG);
		}
		
	}
	
	private static function _parg($parg, $mod, &$error, &$warning){
		if(!is_array($parg)){
			$error[] = sprintf('need ARRAY, "%s" was given in "%s" def', 
				gettype($parg), self::PAGE_ARG);
			return;
		}
		$var = '0';
		foreach($parg as $script => $arr){
			if(strpos($script, '..') !== false)
				$error[] = sprintf('invalid file name "%s" in "%s" def',
					$script, self::PAGE_ARG);
			else{
				$path = self::$appenv->getActionPath($mod, $script);
				if(!file_exists($path))
					$error[] = sprintf('"%s"(%s) not exist in "%s" def',
						$script, $path, self::PAGE_ARG);
			}
			if(!is_array($arr)){
				$error[] = sprintf('need ARRAY, "%s" was given on page "%s" in "%s" def', 
					gettype($arr), $script, self::PAGE_ARG);
				continue;
			}
			foreach($arr as $arg => $opt){
				if(!self::isIdentifier($arg))
					$error[] = sprintf('invalid identifier "%s" on page "%s" in "%s" def', 
						$arg, $script, self::PAGE_ARG);
				else{
					if(!is_array($opt))
						$error[] = sprintf('need ARRAY, "%s" was given at arg "%s" on page "%s" in "%s" def', 
							gettype($opt), $arg, $script, self::PAGE_ARG);
					else {
						if(isset($opt[self::PARG_TYP]) && !settype($var, $opt[self::PARG_TYP]))
							$error[] = sprintf('unsupported type "%s" at arg "%s" on page "%s" in "%s" def', 
								$opt[self::PARG_TYP], $arg, $script, self::PAGE_ARG);
						if(isset($opt[self::PARG_DEP]) && !isset($parg[$opt[self::PARG_DEP]]))
							$error[] = sprintf('not existed dep-arg "%s" at arg "%s" on page "%s" in "%s" def', 
								$opt[self::PARG_DEP], $arg, $script, self::PAGE_ARG);
					}
				}
			}
		}
	}
	
	private static function _tbdef($tb, $mod, &$error, &$warning){
		if(!is_array($tb)){
			$error[] = sprintf('need ARRAY, "%s" was given in "%s" def', 
				gettype($tb), self::TBDEF);
			return;
		}
		foreach($tb as $name => $def){
			if(!self::isIdentifier($name))
				$error[] = sprintf('invalid identifier "%s" in "%s" def', 
					$name, self::TBDEF);
		}
	}
	
	private static function _listener($lis, $mod, &$error, &$warning){
		if(!is_array($lis)){
			$error[] = sprintf('need ARRAY, "%s" was given in "%s" def', 
				gettype($lis), self::LTN);
			return;
		}
		foreach($lis as $class => $modac){
			if(!self::_class_exists($mod, $class))
				$error[] = sprintf('classdef "%s" not exist in "%s" def',
							$class, self::LTN);
			if(!is_string($modac)){
				$error[] = sprintf('need STRING, "%s" was given on classdef "%s" in "%s" def', 
				gettype($modac), $class, self::LTN);
			}
			foreach(explode(',', $modac) as $ma){
				list($mod, $action) = explode('.', $ma, 2);
				if(is_dir(self::$appenv->getDir($mod))){
					$path = self::$appenv->getActionPath($mod, $action);
					if(!file_exists($path))
						$error[] = sprintf('action "%s"(%s) not exist on classdef "%s" in "%s" def',
							$action, $ma, $class, self::LTN);
				}else $warning[] = sprintf('mod "%s"(%s) not exists on classdef "%s" in "%s" def',
					$mod, $ma, $class, self::LTN);
			}
		}
	}
	
	private static function _load_filter($ftr, $mod, &$error, &$warning){
		if(!is_array($ftr)){
			$error[] = sprintf('need ARRAY, "%s" was given in "%s" def', 
				gettype($ftr), self::LD_FTR);
			return;
		}
		foreach($ftr as $arr){
			if(!is_array($arr)){
				$error[] = sprintf('need ARRAY, "%s" was given on "%s" in "%s" def', 
					gettype($arr), var_export($arr, true), self::LD_FTR);
				continue;
			}
			if(count($arr) < 2){
				$error[] = sprintf('miss info on "%s" in "%s" def', 
					var_export($arr, true), self::LD_FTR);
				continue;
			}
			if(is_dir($this->appenv->getDir($arr[0]))){
				$moddef = mbs_moddef($mod);
				$info = $moddef->item(self::FTR);
				if(!is_null($info) && isset($info[$arr[1]]))
					;
				else
					$error[] = sprintf('filter "%s" not exists in mod "%s" on "%s" in "%s" def',
						$arr[1], $arr[0], var_export($arr, true), self::LTN);
			}else $warning[] = sprintf('mod "%s" not exists on "%s" in "%s" def',
				$arr[0], var_export($arr, true), self::LTN);
		}
	}
	
	private static function _depext($depext, $mod, &$error, &$warning){
		if(!is_array($depext)){
			$error[] = sprintf('need ARRAY, "%s" was given in "%s" def', 
				gettype($depext), self::DEPEXT);
			return;
		}
		
		foreach($depext as $dep){
			if(!extension_loaded($dep)){
				$error[] = sprintf('unloaded %s extension', $dep);
			}
		}
		
	}
	
	private static function _checkpages($pages, $mod, &$error, &$warning){
		if(!is_array($pages)){
			$error[] = sprintf('need ARRAY, "%s" was given in "%s" def', 
				gettype($pages), self::PAGES);
			return;
		}
		
		foreach($pages as $action => $p){
			if(!file_exists($this->appenv->getActionPath($mod, $action))){
				$error[] = sprintf('no such action %s', $action);
			}
		}
	}
	
	static function syntax(){
		$error = array();
		$warning = array();
		$modname = '';
		
		static $err_func = array(
			//self::MOD      => '_mod',
			self::TAG      => '_tag',
			self::PAGE_ARG => '_parg',
			self::TBDEF    => '_tbdef',
			self::LTN      => '_listener',
			self::LD_FTR   => '_load_filter',
			self::DEPEXT   => '_depext',
			self::PAGES    => '_checkpages',
			self::FTR      => '_tag',
		);

		self::_mod($this->desc, $modname, $error, $warning);
		foreach($this->desc as $key => $def){
			if(isset($err_func[$key])){
				call_user_func(array('CModDef', $err_func[$key]),
					$def, $modname, $error, $warning);
			}
		}
		
		return array($error, $warning);
	}
	
	
	function checkarg($action){
		static $error_desc = array(
			'no_such_depend_arg_appeared' => '参数 "%s" 需要 "%s", 但是未出现',
			'no_such_depend_arg_defined'  => '参数 "%s" 需要 "%s", 但是未定义',
			'no_such_arg_appeared'        => '参数 "%s" 未出现',
			'arg_cannot_be_empty'         => '参数 "%s" 不能为空',
			'arg_type_invalid'            => '参数 "%s" 类型(%s)错误, 需要(%s)',
			'arg_length_invalid'          => '参数 "%s" 长度(%d)无效, 需要(%s)',
		);
		$error = array();
		if(isset($this->desc[self::PAGES][$action][self::PAGE_ARG])){
			$defopts = array(
	 			CModDef::PARG_REQ => 1, 
	 			CModDef::PARG_DEP => null, 
	 			CModDef::PARG_EMP => 0, 
	 			CModDef::PARG_TRI => 1
	 		);
	 		$pargs = $this->desc[CModDef::PAGES][$action][self::PAGE_ARG];
	 		foreach($pargs as $name => $opts){
	 			if(empty($opts))
	 				$opts = $defopts;
	 			else
	 				$opts = array_merge($defopts, $opts);
	 				
	 			$appear = $success = true;
	 			if(isset($opts[CModDef::PARG_DEP]) && 
	 				!empty($opts[CModDef::PARG_DEP]))
 				{
 					$dep = $opts[CModDef::PARG_DEP];
 					if(isset($pargs[$dep])){
 						$appear = isset($_REQUEST[$dep]);
 						if(isset($_REQUEST[$name]) && !$appear)
 							$error[] = sprintf($error_desc['no_such_depend_arg_appeared'], $name, $dep);
 					}else $error[] = sprintf($error_desc['no_such_depend_arg_defined'], $name, $dep);
 				}
	 			
 				if($appear){
 					if(isset($opts[CModDef::PARG_REQ]) && 1 == $opts[CModDef::PARG_REQ]){
 						if(!isset($_REQUEST[$name])){
 							$error[] = sprintf($error_desc['no_such_arg_appeared'], $name);
 							$success = false;
 						}
 					}
 				}
 				
 				if($success){
 					if(isset($opts[CModDef::PARG_TRI]) && 0 == $opts[CModDef::PARG_TRI])
 						;
 					else{
 						if(isset($_REQUEST[$name]))
 							$_REQUEST[$name] = trim($_REQUEST[$name]);
 					}
 					
 					if(isset($opts[CModDef::PARG_EMP]) && 1 == $opts[CModDef::PARG_EMP])
 						;
 					else{
 						if(isset($_REQUEST[$name]) && 0 == strlen($_REQUEST[$name])){
 							$success = false;
 							$error[] = sprintf($error_desc['arg_cannot_be_empty'], $name);
 						}
 					}
 					
 					if($success){
 						if(isset($opts[CModDef::PARG_TYP]) && isset($_REQUEST[$name]) && 
 							 !settype($_REQUEST[$name], $opts[CModDef::PARG_TYP]))
 							$error[] = sprintf($error_desc['arg_type_invalid'], $name, 
								gettype($_REQUEST[$name]), $opts[CModDef::PARG_TYP]);
							
						if(isset($opts[CModDef::PARG_RNG]) && isset($_REQUEST[$name])){
							$len = strlen($_REQUEST[$name]);
							list($s, $e) = explode(',', $opts[CModDef::PARG_RNG]);
							if($len >= $s && $len < $e)
								;
							else
								$error[] = sprintf($error_desc['arg_length_invalid'], 
									$name, $len, $opts[CModDef::PARG_RNG] );
						}
 					}else break;
 				}else break;
	 		}
		}
		
		return $error;
	}
		
	//continue exucuting or abort
	function loadFilters(){
		if(isset($this->desc[self::LD_FTR])){
			foreach($this->desc[self::LD_FTR] as $ftr){
				list($mod, $ftrname, $isExitOnFilterUndefined) = $ftr;
				$moddef = mbs_moddef($mod);
				if(!empty($moddef)){
					$ftrsdef = $moddef->item(self::FTR);
					if(!empty($ftrsdef) && isset($ftrsdef[$ftrname]) 
						&& isset($ftrsdef[$ftrname][self::G_CS]) 
						&& self::_class_exists($mod, $ftrsdef[$ftrname][self::G_CS]))
					{
						$objftr = new $ftrsdef[$ftrname][self::G_CS]();
						if(!$objftr->oper(array_slice($objftr, 0, 3))){
							trigger_error($objftr->getError(), E_USER_ERROR);
							return false;
						}
					}else{
						if($isExitOnFilterUndefined){
							trigger_error(sprintf('load filter error on %s.%s', $mod, $ftrname), E_USER_ERROR);
							return false;
						}
					}
				}else{
					if($isExitOnFilterUndefined){
						trigger_error(sprintf('load filter error on %s.%s', $mod, $ftrname), E_USER_ERROR);
						return false;
					}
				}
			}
		}
		return true;
	}
	
	function install_tables($dbpool, $tabledef){
		$error = array();
		$pdoconn = $dbpool->getDefaultConnection();
		foreach($tabledef as $name => $def){
			$ret = $pdoconn->exec(sprintf('CREATE TABLE IF NOT EXISTS %s%s CHARACTER SET=%s', 
				self::$appenv->formatTableName($name), $def, str_replace('-', '', $this->appenv->item('charset'))));
			if(false === $ret){
				list($id, $code, $str) = $pdoconn->errorInfo();
				if($id != '00000'){
					$error []= $name.':'.$str;
				}
			}
		}
		return $error;
	}
	
	function install($dbpool, $mempool=null){
		$modinfo = $this->desc;
		
		list($err, $war) = self::syntax($modinfo);
		$err += $war;
		if(count($err) > 0)
			return $err;
			
		if(isset($modinfo[self::TBDEF]) && 
			count($modinfo[self::TBDEF]) > 0)
		{
			$err = $this->install_tables($dbpool, $modinfo[self::TBDEF]);
			if(!empty($err)){
				return $err;
			}
		}
		
		return array();
	}
	
	function uninstall(){
	}
	
	function update(){
	}
}


?>