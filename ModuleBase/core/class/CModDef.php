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
 * @dependent: CAppEnvironment.php, index.php:mbs_moddef(), index.php:mbs_tbname()
 *
 */
abstract class CModDef {

	//an valid identifier([_a-zA-Z][_a-zA-Z0-9]*)
	CONST G_NM = 'name';
	CONST G_CS = 'class';
	CONST G_TL = 'title';
	CONST G_DC = 'desc';
	
	CONST MOD  = 'module';
	CONST M_CS = 'charset';
	
	CONST FTR = 'filter'; // define filters for using
	CONST TAG = 'tag'; // define tags for using
	
	CONST PAGES  = 'pages';  // the pages in the module.
	CONST P_TLE  = 'p_title'; // the page's title where displayed in <title></title>
	CONST P_MGR  = 'p_mgr'; // indicate whether the page is a management page
	CONST P_OUT  = 'p_out'; // output something like json format to mobile app
	CONST P_ARGS = 'p_args'; // the page's arguments appeared in $_REQUEST, $args, $_FILES
	CONST P_DOF  = 'p_debug_output_off'; // close the debug_output if set
	CONST P_NCD  = 'p_no_click_direct_on_mgr';
	CONST P_MGNF = 'p_mgr_notification';// P_MGNF=>[true(system default interval)/integer(interval in second)], include(P_MGR,P_NCD)
	//CONST P_LGC  = 'p_logo_class'; // <a href="..."><i class=P_LGC></i>P_TLE</a>
	CONST PA_TYP = 'pa_type';    // the arg's type what appears in gettype(). default is 'string'
	CONST PA_REQ = 'pa_required';// the arg MUST be required in the page. default is 1
	CONST PA_DEP = 'pa_depend'; // the arg which appears in the same page. default is null
	CONST PA_EMP = 'pa_empty';  // allow empty on the arg(default is 0). NOTICE: the empty validation only check the length of the trimed arg
	CONST PA_TRI = 'pa_trim';   // ignore triming on the arg if set to 0. default is 1(trim).
	CONST PA_RNG = 'pa_range'; // the range of the arg's length. split by comma, like '5,12'.  arg>=5 && arg<12
	
	CONST TBDEF  = 'table_def';
	CONST LTN    = 'listener';
	CONST MGR    = 'mgrpage';
	CONST LD_FTR = 'load_filter'; // do filter checking on each script in the module
	CONST DEPEXT = 'dependent'; // checking whether the current environment included the extension or function. = array(ext1, ext2, ...)
	CONST MGR_NOTIFY_INTERVAL_SEC = 60;
	
	protected static $appenv = null;
	private $desc = null;
	private static $reserve_actions = array('install', 'upgrade', 'uninstall');
	
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
	 *   		self::P_TLE => '标题',
	 *   		self::G_DC  => '这是一个页面的描述',
	 *      	self::P_ARGS => array(
     *   			'arg1' => array('type'=>'int', required=>true, 'range'=>'5,12'),
	 *   			'arg2' => array('type'=>'int', required=>true, depend=>'arg1'),
	 *   			'sign' => array('type'=>'string', required=>true, depend=>'arg1', desc=>'md5(arg1+arg2)'),
	 *  		),
	 *			self::P_MGR => true,
	 *			self::P_MGNF=> true,
	 *			self::P_OUT => '{
	 *				'error': 'message if error happened or empty else',
	 *				'gift_id' : '100',
	 *			}'
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
	 *   	array(mod, ftr_name, isExitOnFilterUndefined, array('arg1'=>v1, 'arg2'=>v2, ...)),
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
		$num = func_num_args();
		$args = func_get_args();
		
		$ctx = $this->desc;
		for($i=0; $i<$num; ++$i){
			if(!isset($ctx[$args[$i]])){
				$ctx = null;
				break;
			}
			$ctx = $ctx[$args[$i]];
		}
		return $ctx;
	}
	
	function filterActions($condKey=CModDef::P_MGR){
		$ret = array();
		$num = func_num_args();
		$args = func_get_args();
		
		if(0 == $num){
			$num = 1;
			$args = array(CModDef::P_MGR);
		}
		
		foreach($this->desc[self::PAGES] as $ac => $def){
			for($i=0; $i<$num; ++$i){
				if(is_array($args[$i])){
					if(!isset($def[key($args[$i])]) 
						|| $def[key($args[$i])] == current($args[$i]))
						break;
				}else{
					if(!isset($def[$args[$i]]))
						break;
				}
			}
			if($i == $num)
				$ret[$ac] = $def;
		}
		
		return $ret;
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
	
	static function pargRange($def, &$s, &$e=0){
		$ret = 1;
		$arr = explode(',', $def[CModDef::PA_RNG]);
		$s = intval(trim($arr[0]));
		if(isset($arr[1])){
			$e = intval(trim($arr[1]));
			$ret = 2;
		}
		return $ret;
	}
	
	private static function _class_exists($mod, $class){
		$path = self::$appenv->getClassPath($class, $mod);
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
				else if(self::$appenv->item('cur_mod') != $mod[self::G_NM])
					$error[] = sprintf('submited "%s" missmatch "%s" in G_NM mod def', $mod[self::G_NM], self::$appenv->item('cur_mod'));
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
				gettype($parg), self::P_ARGS);
			return;
		}
		$var = '0';
		foreach($parg as $script => $arr){
			if(strpos($script, '..') !== false)
				$error[] = sprintf('invalid file name "%s" in "%s" def',
					$script, self::P_ARGS);
			else{
				$path = self::$appenv->getActionPath($script, $mod);
				if(!file_exists($path))
					$error[] = sprintf('"%s"(%s) not exist in "%s" def',
						$script, $path, self::P_ARGS);
			}
			if(!is_array($arr)){
				$error[] = sprintf('need ARRAY, "%s" was given on page "%s" in "%s" def', 
					gettype($arr), $script, self::P_ARGS);
				continue;
			}
			if(isset($arr[self::P_ARGS])){
				foreach($arr[self::P_ARGS] as $arg => $opt){
					if(!self::isIdentifier($arg))
						$error[] = sprintf('invalid identifier "%s" on page "%s" in "%s" def', 
							$arg, $script, self::P_ARGS);
					else{
						if(!is_array($opt))
							$error[] = sprintf('need ARRAY, "%s" was given at arg "%s" on page "%s" in "%s" def', 
								gettype($opt), $arg, $script, self::P_ARGS);
						else {
							if(isset($opt[self::PA_TYP]) && 'file' != strtolower($opt[self::PA_TYP]) 
								&& !settype($var, $opt[self::PA_TYP]))
								$error[] = sprintf('unsupported type "%s" on arg "%s" in page "%s" of "%s" def', 
									$opt[self::PA_TYP], $arg, $script, self::P_ARGS);
							if(isset($opt[self::PA_DEP]) && !isset($parg[$opt[self::PA_DEP]]))
								$error[] = sprintf('not existed dep-arg "%s" at arg "%s" on page "%s" in "%s" def', 
									$opt[self::PA_DEP], $arg, $script, self::P_ARGS);
						}
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
					$path = self::$appenv->getActionPath($action, $mod);
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
			if(is_dir(self::$appenv->getDir($arr[0]))){
				$moddef = mbs_moddef($arr[0]);
				$info = $moddef->item(self::FTR);
				if(!empty($info) && isset($info[$arr[1]]))
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
			if(!extension_loaded($dep) && !function_exists($dep)){
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
			if(!file_exists(self::$appenv->getActionPath($action, $mod))){
				$error[] = sprintf('no such action "%s" found in module "%s"', $action, $mod);
			}
		}
	}
	
	function syntax(){
		$error = array();
		$warning = array();
		$modname = '';
		
		static $err_func = array(
			//self::MOD      => '_mod',
			self::TAG      => '_tag',
			self::PAGES   => '_parg',
			self::TBDEF    => '_tbdef',
			self::LTN      => '_listener',
			self::LD_FTR   => '_load_filter',
			self::DEPEXT   => '_depext',
			self::FTR      => '_tag',
		);

		self::_mod($this->desc, $modname, $error, $warning);
		if(empty($error)){
			foreach($this->desc as $key => $def){
				if(isset($err_func[$key])){
					//call_user_func(array('CModDef', $err_func[$key]),
					//	$def, $modname, $error, $warning);
					self::$err_func[$key]($def, $modname, $error, $warning);
				}
			}
		}
		
		return array($error, $warning);
	}
	
	
	function checkargs($action, $exclude_args=array()){
		$error_desc = self::$appenv->lang('checkargs_error', 'core');
		$error = array();
		if(isset($this->desc[self::PAGES][$action][self::P_ARGS])){
			$defopts = array(
	 			self::PA_REQ => 0, 
	 			self::PA_DEP => null, 
	 			self::PA_EMP => 0, 
	 			self::PA_TRI => 1,
				self::PA_TYP => '',
				self::PA_DEP => ''
	 		);
	 		$pargs = $this->desc[CModDef::PAGES][$action][self::P_ARGS];
	 		foreach($pargs as $name => $opts){
	 			if(in_array($name, $exclude_args)){
	 				continue;
	 			}
	 			$opts = empty($opts) ? $defopts : array_merge($defopts, $opts);
	 				
	 			if(!empty($opts[CModDef::PA_DEP]))
 				{
 					$dep = $opts[CModDef::PA_DEP];
 					if(isset($pargs[$dep])){
 						if(isset($_REQUEST[$name]) && !isset($_REQUEST[$dep])){
 							$error[$name] = sprintf($error_desc['no_such_depend_arg_appeared'], $name, $dep);
 							continue;
 						}
 					}else {
 						$error[$name] = sprintf($error_desc['no_such_depend_arg_defined'], $name, $dep);
 						continue;
 					}
 				}

 				if($opts[CModDef::PA_TRI] && isset($_REQUEST[$name]) && is_string($_REQUEST[$name]))
 					$_REQUEST[$name] = trim($_REQUEST[$name]);
 				
 				if($opts[CModDef::PA_REQ]){
 					if('file' == strtolower($opts[self::PA_TYP])){
 						if(!isset($_FILES[$name]) ){
	 						$error[$name] = sprintf($error_desc['no_such_arg_appeared'], 
	 								(isset($opts[self::G_DC]) ? $opts[self::G_DC].'/-' : '').$name);
	 						continue;
 						}else if(isset($_FILES[$name]['error'][0])){
 							if($_FILES[$name]['error'][0] != UPLOAD_ERR_OK){
 								$error[$name] = self::$appenv->lang($_FILES[$name]['error'][0]);
 							}
 						}else {
 							if($_FILES[$name]['error'] != UPLOAD_ERR_OK){
 								$error[$name] = self::$appenv->lang($_FILES[$name]['error']);
 							}
 						}
 					}
 					else if(!isset($_REQUEST[$name])){
 						$error[$name] = sprintf($error_desc['no_such_arg_appeared'], 
 								(isset($opts[self::G_DC]) ? $opts[self::G_DC].'/' : '').$name);
 						continue;
 					}else{
 						if(!$opts[CModDef::PA_EMP] && 0==strlen($_REQUEST[$name])){
 							$error[$name] = sprintf($error_desc['arg_cannot_be_empty'], $name);
 							continue;
 						}
 					}
 				}
 				 				
				if(!empty($opts[CModDef::PA_TYP]) 
					&& strtolower($opts[CModDef::PA_TYP]) != 'file'
					&& isset($_REQUEST[$name])
					&& !settype($_REQUEST[$name], $opts[CModDef::PA_TYP]))
				{
					$error[$name] = sprintf($error_desc['arg_type_invalid'], $name,
							gettype($_REQUEST[$name]), $opts[CModDef::PA_TYP]);
					continue;
				}
				
				if(!empty($opts[CModDef::PA_RNG]) && isset($_REQUEST[$name]) && !empty($_REQUEST[$name])){
					if(is_string($_REQUEST[$name])){
						$num = iconv_strlen($_REQUEST[$name], self::$appenv->item('charset'));
					}
					else if(is_numeric($_REQUEST[$name])){
						$num = $_REQUEST[$name];
					}
					else if(is_array($_REQUEST[$name])){
						$num = count($_REQUEST[$name]);
					}
					else{
						trigger_error('type: '.gettype($_REQUEST[$name]).' can not be compared with integer', E_USER_ERROR);
					}
	
					$s = $e = 0;
					$rnum = self::pargRange($opts, $s, $e);
					
					if($num < $s || ($e !=0 && $num > $e)){
						$error[$name] = sprintf($error_desc['arg_length_invalid'],
								(isset($opts[self::G_DC]) ? $opts[self::G_DC].'/' : '').$name, 
								$num, $s.'-'.(0==$e?'':$e) );
						continue;
					}
				}
	 		}
		}
		
		return $error;
	}
		
	//continue exucuting or abort
	static function _loadFilters($ftrs, &$error){
		foreach($ftrs as $ftr){
			list($mod, $ftrname, $isExitOnFilterUndefined) = $ftr;
			$moddef = mbs_moddef($mod);
			if(!empty($moddef)){
				$ftrsdef = $moddef->item(self::FTR);
				if(!empty($ftrsdef) && isset($ftrsdef[$ftrname]) 
					&& isset($ftrsdef[$ftrname][self::G_CS]) 
					&& self::_class_exists($mod, $ftrsdef[$ftrname][self::G_CS]))
				{
					$objftr = new $ftrsdef[$ftrname][self::G_CS]();
					if(!$objftr->oper(isset($ftr[3])?$ftr[3]:null, $ftrname)){
						$error = $objftr->getError();
						return false;
					}
				}else{
					if($isExitOnFilterUndefined){
						$error = sprintf('LD_FTR_DEF_ERR on %s.%s', $mod, $ftrname);
						return false;
					}
				}
			}else{
				if($isExitOnFilterUndefined){
					$error = sprintf('LD_FTR_ERR on %s.%s', $mod, $ftrname);
					return false;
				}
			}
		}
		return true;
	}
	
	function loadFilters($action, &$error=''){
		if(isset($this->desc[self::LD_FTR])){
			if(!self::_loadFilters($this->desc[self::LD_FTR], $error))
				return false;
		}
		if(isset($this->desc[self::PAGES][$action][self::LD_FTR])){
			return self::_loadFilters($this->desc[self::PAGES][$action][self::LD_FTR], $error);
		}
		return true;
	}
	
	function filter($ftrname, $params=null, &$error=''){
		if(isset($this->desc[self::FTR][$ftrname][self::G_CS])){
			$class = $this->desc[self::FTR][$ftrname][self::G_CS];
			mbs_import($this->desc[self::MOD][self::G_NM], $class);
			$ftr = new $class();
			if($ftr->oper($params, $ftrname))
				return true;
			$error = $ftr->getError();
		}
		
		return false;
	}

	function installTables($dbpool, $tabledef){
		$error = array();
		try {
			$pdoconn = $dbpool->getDefaultConnection();
			foreach($tabledef as $name => $def){
				$ret = $pdoconn->exec(sprintf('CREATE TABLE IF NOT EXISTS %s%s CHARACTER SET=%s',
						mbs_tbname($name), $def,
						str_replace('-', '', self::$appenv->item('charset'))));
				if(false === $ret){
					list($id, $code, $str) = $pdoconn->errorInfo();
					if($id != '00000'){
						$error []= $name.':'.$str;
					}
				}
			}
		} catch (Exception $e) {
			throw $e;
		}
		
		return $error;
	}
	
	static function isReservedAction($action){
		return array_search($action, self::$reserve_actions) !== false;
	}
	
	function install($dbpool, $mempool=null){
		$modinfo = $this->desc;
		
		list($err, $war) = $this->syntax($modinfo);
		$err += $war;
		if(count($err) > 0)
			return $err;
			
		if(isset($modinfo[self::TBDEF]) && 
			count($modinfo[self::TBDEF]) > 0)
		{
			try {
				$err = $this->installTables($dbpool, $modinfo[self::TBDEF]);
			} catch (Exception $e) {
				throw $e;
			}
			
			if(!empty($err)){
				return $err;
			}
		}
		
		return array();
	}
	
	function uninstall($dbpool, $mempool=null){
	}
	
	function upgrade($dbpool, $mempool=null){
	}
}


?>