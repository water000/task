<?php
/**
 *对于错误的通用处理方法
 */
interface IError 
{
	static function setError($code=0, $desc='');
	
	static function getErrorCode();
	
	static function getErrorDesc();
}

?>