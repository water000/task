<?php
class CStrTools {
	const TO_JS_CHARS = "\f\r\n\t\v\"/";
	
	static function isModifier($name)
	{
		if(($name[0]>='a' && $name[0]<='z')
		|| ($name[0]>='A' && $name[0]<='Z') || '_' == $name[0])
			;
		else
			return false;
		$i = 1;
		$len = strlen($name);
		for(; $i<$len; ++$i){
			if(($name[$i] >= 'a' && $name[$i]<='z')
			|| ($name[$i]>='A' && $name[$i]<='Z')
			|| ($name[$i]>='0' && $name[$i]<='9')
			|| '_' == $name[$i]
			)
				;
			else
				return false;
		}
		return true;
	}
	
	static function isWord($s)
	{
		for($i=0, $c=strlen($s); $i<$c; ++$i)
		{
			$b = ord($s[$i]);
			if($b< 65 || $b>122 || ($b>90 && $b<97))
				return false;
		}
		return true;
	}
	
	static function isValidEmail($email)
	{
		///^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i
		return preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z]{2,4})+$/', $email);
	}
	
	static function isValidPhone($phone){
		$len = strlen($phone);
		
		if($len >= 11 && ('+' == $phone[0] || is_numeric($phone[0]))){
			for($i=1; $i<$len; ++$i){
				if(!is_numeric($phone[$i])){
					return false;
				}
			}
			return true;
		}
		
		return false;
	}
	
	static function isValidPassword($pwd){
		if(strlen($pwd) >= 6)
			return true;
		
		return false;
	}
	
	static function stripComment($s)
	{
		$s = preg_replace('/\/\*.+?\*\//s', '', $s);
		$s = preg_replace('/\/\/[^\n]+\r?\n?/s', '', $s);
		$s = preg_replace("/#[^\n]+\r?\n?/s", '', $s);
		return $s;
	}
	
	static function dbSearchKeyword(&$str)
	{
		$c = 0;
		$str = str_replace(array('%', '_'), array('\%', '\_'), $str, $c);
		return $c;
	}
	
	static function txt2html($str, $htmlspec=true)
	{
		return str_replace(
			array(' ', "\r\n", "\r", "\n", "\t"),
			array('&nbsp;', '<br />', '<br />', '<br />', '&nbsp;&nbsp;'),
			$htmlspec ? htmlspecialchars($str) : $str
		);
	}
	
	static function url2href($url)
	{
		return str_replace(
				array('"','\'', '<','>',),
				array(urlencode('"'), urlencode("'"), urlencode('<'), urlencode('>')),
				$url
		);
	}
	
	static function cutstr($str, $maxlen, $charset, $suffix='...'){
		return iconv_strlen($str, $charset) > $maxlen ? 
			iconv_substr($str, 0, $maxlen, $charset).$suffix : $str; 
	}
	
	static function descTime($timestamp, $mbs_appenv){
		$zero = mktime(0, 0, 0);
		if($timestamp >= $zero){
			return date('H:i', $timestamp);
		}
		$diff = $zero - $timestamp;
		if($diff <= 86400){
			return $mbs_appenv->lang('yesterday').date('H:i', $timestamp);
		}
		else if($diff <= 2*86400){
			return $mbs_appenv->lang('before_yesterday').date('H:i', $timestamp);
		}
		else{
			return date('Y/m/d H:i', $timestamp);
		}
	}
	
	static function fldTitle($def){
		echo $def[CModDef::G_TL],
			isset($def[CModDef::PA_REQ]) ? '<span class=required>*</span>':'';
	}
	
	static function fldDesc($def, $mbs_appenv){
		if(isset($def[CModDef::PA_RNG])){
			$s = $e = 0;
			$rnum = CModDef::pargRange($def, $s, $e);
			echo $s, 2==$rnum?'~'.$e:'', $mbs_appenv->lang('num_of_char', 'common');
		}
		echo isset($def[CModDef::G_DC])?$def[CModDef::G_DC]:'';
	}
}

?>