<?php

interface IError 
{
	static function setError($code=0, $desc='');
	
	static function getErrorCode();
	
	static function getErrorDesc();
}

?>