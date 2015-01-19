<?php
class CStrTools {
	const TO_JS_CHARS = "\f\r\n\t\v\"/";
	
	static function isModifier($name)
	{
		if(($name[0]>='a' && $name[0]<='z')
		|| ($name[0]>='A' && $name[0]<='Z'))
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
}

?>