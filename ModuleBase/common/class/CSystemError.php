<?php

require_once dirname(__FILE__).'/CError.php';
class CSystemError extends CError 
{
	CONST ACTION_BANNED = 1001;
	CONST INVALID_REQ_MOD = 1002;
	
	protected static $arrErrorDesc = array(
		self::ACTION_BANNED                  => '您所访问的页面已经被禁用',
		self::INVALID_REQ_MOD                => '访问模式有误',
	);
	static function getErrorDesc()
	{
		$c = __CLASS__;
		if(parent::$oRaisedError && !(parent::$oRaisedError instanceof $c))
			return parent::$oRaisedError->getErrorDesc();
		return  (parent::$curErrorCode < parent::COMMON_MAX_CODE ?
					parent::$arrErrorDesc[parent::$curErrorCode] :
					self::$arrErrorDesc[parent::$curErrorCode]
				).' , '.parent::$sCurErrorDetail.parent::$sCurDebugTrace;
	}
}
?>