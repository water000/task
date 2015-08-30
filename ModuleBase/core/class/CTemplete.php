<?php

//depend-php-lib: preg_match_all
//depend-class:CFileType.php

class CTemplete {
	
	private static $error = array();
	private static $tpl_buf = array();
	private static $content_buf = array();
	
	private static function _match_tpl_def($content, $mod, $filename, &$arr, &$error){
		if(preg_match_all('/<!--\s*TPL_DEF:(.+?)\s*-->\s*(.+?)\s*<!--\s*TPL_DEF_END\s*-->\s*/s', 
			$content, $match))
		{
			for($i=0, $num=count($match[0]); $i<$num; ++$i){
				$content = str_replace($match[0][$i], '', $content);
				if(preg_match('/([\w\.]+)\s*(\([^\)]+\))?/', $match[1][$i], $name)){
					$args_tag = array();
					$errnum = count($error);
					if(isset($name[2])){
						$args = substr($name[2], 1, -1); // cut '()'
						$args = explode(',', trim($args));
						foreach($args as $arg){
							$tag = sprintf('<!-- %s -->', trim($arg));
							if(false === strpos($match[2][$i], $tag))
								$error[] = sprintf('the tag "%s" of arg "%s" declared in "%s" not found in tpl body in file "%s"', 
									$tag, $arg, $name[0], $mod.'.'.$filename);
							else
								$args_tag[] = $tag;
						}
					}
					if($errnum == count($error))
						$arr[$name[1]] = array($args_tag, $match[2][$i]);
				}else $error[] = sprintf('invalid word def "%s" in file "%s"', $match[1][$i], $mod.'.'.$filename);
			}
		}

		return $content;
	}
	
	private static function _filename2key($filename){
		$pos = strpos($filename, '.');
		return $pos === false ? $filename : substr($filename, 0, $pos);
	}
	
	private static function _buf_match($content, $mod, $filename){
		$fkey = self::_filename2key($filename);
		
		if(!isset(self::$tpl_buf[$mod][$fkey])){
			$tags = array();
			$content = self::_match_tpl_def($content, $mod, $filename, $tags, self::$error);
			if(isset(self::$error[0])){
				self::$tpl_buf[$mod][$fkey] = false;
				self::$content_buf[$mod][$fkey] = false;
				return false;
			}else{
				self::$tpl_buf[$mod][$fkey] = $tags;
				self::$content_buf[$mod][$fkey] = $content;
			}
		}
		return self::$content_buf[$mod][$fkey];
	}
	
	private static function _tpl_content($name, $mod = '', $filename=''){
		$fkey = '';
		$arr = explode('.', $name, 3);
		if(3 == count($arr)){
			$mod = $arr[0];
			$fkey = $arr[1];
			$name = $arr[2];
		}else if(1 == count($arr))
			$fkey = self::_filename2key($filename);
		else{
			self::$error[] = sprintf('lose info in arg "%s", (mod.file.tplname)', $name);
			return false;
		}
		
		if(!isset(self::$tpl_buf[$mod][$fkey])){
			$path = CFileType::getPath(CFileType::ENV_COMPILE, $mod, $filename, CFileType::FT_ACTION);
			if(!file_exists($path)){
				self::$error[] = sprintf('No such file "%s" exists', $path);
				return false;
			}
			$content = file_get_contents($path);
			if(!self::_buf_match($content, $mod, $filename))
				return false;
		}
		if(!isset(self::$tpl_buf[$mod][$fkey][$name])){
			self::$error[] = sprintf('No tpl "%s.%s.%s" exists', $mod, $filename, $name);
			return false;
		}
		
		return self::$tpl_buf[$mod][$fkey][$name];
	}
	
	private static function _install($def, $mod='', $filename=''){
		$func_stack = array();
		$args_stack = array();
		for($m = $i=0,$j=strlen($def); $i<$j; ++$i){
			switch ($def[$i]){
			case '(':
				$func = trim(substr($def, $m, $i-$m));
				array_push($func_stack, $func);
				array_push($args_stack, $func);
				$m = $i+1;
				break;
			case ')' :
				$arg = trim(substr($def, $m, $i-$m));
				if($arg != ''){
					$ret = self::_tpl_content($arg, $mod, $filename);
					if(false === $ret)
						return false;
					array_push($args_stack, $ret[1]);
				}
				$func = array_pop($func_stack);
				if(!$func){
					self::$error = sprintf('Syntax error, no more tpl_name found in "%s(%d)"', $def, $i);
					return false;
				}
				$ret = self::_tpl_content($func, $mod, $filename);
				if(false === $ret)
					return false;
					
				$args = array();
				while(count($args_stack) > 0){
					$ar = array_pop($args_stack);
					if($ar == $func)
						break;
					$args[] = $ar;
				}
				if(count($ret[0]) != count($args)){
					self::$error = sprintf('Syntax error, Need "%d" arg, but "%d" given in "%s(%d)"', 
						count($ret[0]), count($args), $func, $i);
					return false;
				}
				$func_ret = str_replace($ret[0], $args, $ret[1]);
				array_push($args_stack, $func_ret);
				$m = $i+1;
				break;
			case ',' :
				$arg = trim(substr($def, $m, $i-$m));
				if($arg != ''){
					$ret = self::_tpl_content($arg, $mod, $filename);
					if(false === $ret)
						return false;
					array_push($args_stack, $ret[1]);
				}
				$m = $i+1;
				break;
			}
		}
		if(0 == $m){
			$ret = self::_tpl_content($def, $mod, $filename);
			if(false === $ret)
				return false;
			if(count($ret[0]) > 0){
				self::$error[] = sprintf('Syntax error, need "%d" arg in tpl_def "%s", but nothing given',
					count($ret[0]), $def);
				return false;
			}
			return $ret[1];
		}
		if(count($args_stack) != 1){
			self::$error[] = sprintf('Syntax error, too more(%d) arg given in "%s"', 
				count($args_stack)-1, $def);
			return false;
		}
		return $args_stack[0];
	}
	
	static function parseContent($content, $mod, $filename){
		self::$error = array();
		
		$content = self::_buf_match($content, $mod, $filename); // replace all the tpl_def tags
		if(false === $content)
			return false;
			
		$ret = preg_match_all('/<!--\s*TPL_INSTALL:([\w\.,\(\)\s]+?)\s*-->\s*/', $content, $match);
		if(!$ret){
			//self::$error[] = sprintf('No matching TPL_INSTALL tag found');
			//return false;
			return $content;
		}
		else if($ret != 1){
			self::$error [] = sprintf('Only one TPL_INSALL tag can appear, but "%d" now', $ret);
			return false;
		}
		
		$ret = self::_install(trim($match[1][0]), $mod, $filename);
		if(false === $ret)
			return false;
		
		return str_replace($match[0][0], $ret, $content);
	}
	
	static function parseFile($mod, $filename){
		$fkey = self::_filename2key($filename);
		if(!isset(self::$content_buf[$mod][$fkey])){
			$path = CFileType::getPath(CFileType::ENV_COMPILE, 
				$mod, $filename, CFileType::FT_ACTION);
			if(!file_exists($path)){
				self::$error[] = sprintf('No such file "%s" exists', $path);
				return false;
			}
			$content = file_get_contents($path);
			if(false === $content)
				return false;
		}else $content = self::$content_buf[$mod][$fkey];
		
		return self::parseContent($content, $mod, $filename);
	}
	
	static function getError(){
		return self::$error ;
	}
	
}
/*
$str1 = <<<EOD
<!-- TPL_DEF:global_frame(main_body, bottom) -->
<div class="wrap">
	<div class="main">
	<!-- main_body -->
	<!-- bottom -->
	</div>
</div>
<!-- TPL_DEF_END -->

<!-- TPL_DEF:main_body(left,right) -->
<div>
	<div><!-- left --></div>
	<div><!-- right --></div>
</div>
<!-- TPL_DEF_END -->

<!-- TPL_DEF:left -->
<div>this is left</div>
<!-- TPL_DEF_END -->

<!-- TPL_DEF:right -->
<div>this is right</div>
<!-- TPL_DEF_END -->

<!-- TPL_DEF:bottom -->
<div>this is bottom</div>
<!-- TPL_DEF_END -->

<!-- TPL_INSTALL:global_frame(
	main_body(left,right), bottom
) -->

EOD;

//var_dump(CTemplete::parseContent($str1, 'core', 'test.php'));
//var_dump(CTemplete::getError());
 */
?>