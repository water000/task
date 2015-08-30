<?php
/**
 * @desc 系统通用的错误类型及描述定义。当其它类发生错误时，可继承此类，
 * 然后再此类最大错误代码(1000)之后进行补充，如果有需要也可以覆盖此类的定义。
 * 此类的成员和方法都定义为static是因为在系统运行时，不能有多个实例存在，
 * 也就是意味着当错误不间断的发生后，后发生的会覆盖前面的
 * @author Administrator
 *
 */
require_once dirname(__FILE__).'/IError.php';
class CError implements IError
{
	CONST DEBUG_ON = true;
	CONST DEBUG_OFF = false;
	
	CONST RAISED_ERROR_OVERRIDE_ON = true;
	CONST RAISED_ERROR_OVERRIDE_OFF = false;
	
	CONST COMMON_MAX_CODE = 1000;
	
	//paramter error
	CONST COMMON_UNKNOWN = 0;
	
	CONST COMMON_PARAM_NUM = 1;
	CONST COMMON_PARAM_TYPE = 2;
	CONST COMMON_PARAM_EMPTY = 3;
	
	//file error
	CONST COMMON_FILE_TYPE = 100;
	CONST COMMON_FILE_EXISTS = 101;
	CONST COMMON_FILE_NOT_EXISTS = 102;
	CONST COMMON_FILE_UNABLE_WRITE = 103;
	CONST COMMON_FILE_UNABLE_READ = 104;
	CONST COMMON_FILE_UPLOAD = 105; 
	
	CONST COMMON_FORM_FIELD_EMPTY = 200;
	CONST COMMON_FORM_FIELD_TYPE = 201;
	CONST COMMON_FORM_FIELD_NUM = 202;
	
	CONST COMMON_CHARSET_NOT_EXISTS = 300;
	
	CONST COMMON_FUNC_RETURN_TYPE = 400;
	CONST COMMON_FUNC_RETURN_EMPTY = 401;
	
	CONST COMMON_DECLARATION_EXISTS = 500;
	CONST COMMON_DECLARATION_NOT_EXISTS = 501;
	CONST COMMON_INTERFACE_NOT_IMPLEMENT = 502;
	
	CONST COMMON_REPEAT_EXISTS = 600;
	CONST COMMON_OVERFLOW = 610;
	
	CONST COMMON_DB_EXCEPTION = 700;
	
	CONST COMMON_CACHE_EXCEPTION = 800;
	
	protected static $curErrorCode = 0;
	protected static $sCurErrorDetail = '暂无';
	protected static $sCurDebugTrace = '';
	
	/**
	 * @desc 当一个连续的调用错误发生时，有可能最底层的错误只有顶层
	 * 才会进行处理，这时候在这个过程中就可以使用raiseError方法进行
	 * 错误逐层传递而不处理
	 * @var object
	 */
	protected static $oRaisedError = null;
	
	protected static $arrErrorDesc = array(
		self::COMMON_UNKNOWN                 => '未知错误'
		
		,self::COMMON_PARAM_NUM              => '参数个数有误'
		,self::COMMON_PARAM_TYPE             => '参数类型有误'
		,self::COMMON_PARAM_EMPTY            => '参数不能为空'
		
		,self::COMMON_FILE_TYPE              => '文件类型有误'
		,self::COMMON_FILE_EXISTS            => '文件已经存在'
		,self::COMMON_FILE_NOT_EXISTS        => '文件不存在'
		,self::COMMON_FILE_UNABLE_WRITE      => '文件无法写入'
		,self::COMMON_FILE_UNABLE_READ       => '文件无法读取'
		,self::COMMON_FILE_UPLOAD            => '文件上传有误'
		
		,self::COMMON_FORM_FIELD_EMPTY       => '表单字段为空'
		,self::COMMON_FORM_FIELD_TYPE        => '表单字段类型'
		,self::COMMON_FORM_FIELD_NUM         => '表单字段数目'
		
		,self::COMMON_CHARSET_NOT_EXISTS     => '不存在此字符集或字符编码有误'
		
		,self::COMMON_FUNC_RETURN_TYPE       => '函数返回值类型有误'
		,self::COMMON_FUNC_RETURN_EMPTY      => '函数返回值为空'
		
		,self::COMMON_DECLARATION_EXISTS     => '声明(定义)已经存在'
		,self::COMMON_DECLARATION_NOT_EXISTS => '声明(定义)不存在'
		,self::COMMON_INTERFACE_NOT_IMPLEMENT => '接口未实现'
		
		,self::COMMON_REPEAT_EXISTS          => '存在重复的值 '
		,self::COMMON_OVERFLOW               => '超过了允许的最大值 '
		
		,self::COMMON_DB_EXCEPTION           => '数据库连接失败，请稍后再试 '
		,self::COMMON_CACHE_EXCEPTION        => 'CACHE连接失败，请稍后再试 '
		
	);
		
	private static $bDebug = self::DEBUG_OFF;
	
	static function setDebug($b = self::DEBUG_OFF)
	{
		self::$bDebug = $b;
	}
	
	/**
	 * @desc 将错误进行向上传递
	 * @param object $obj CError的一个子类对象
	 * @param bool $bOverride 是否覆盖前面一个错误对象, 如果不,则可以将第一个参数设置为null
	 */
	static function raiseError($obj=null, $bOverride=self::RAISED_ERROR_OVERRIDE_ON)
	{
		if($obj && $bOverride == self::RAISED_ERROR_OVERRIDE_ON)
			self::$oRaisedError = $obj;
	}
	
	static function clearRaisedError()
	{
		self::$oRaisedError = null;
	}
	
	/**
	 * @desc 设置一条错误
	 * @param int $code 错误代码
	 * @param string $detail 错误的补充说明
	 * @return no return`
	 */
	static function setError($code=self::COMMON_UNKNOWN, $detail='')
	{
		self::$curErrorCode = $code;
		self::$sCurErrorDetail = $detail;
		self::$sCurDebugTrace = '';
		if(self::$bDebug)
		{
			self::$sCurDebugTrace = IS_CLI ? "\n".var_export(debug_backtrace(), true) 
				: "<br/>".str_replace(array("\n"), array('<br/>'), var_export(debug_backtrace(), true));
		}
	}
	
	static function getErrorCode()
	{
		return self::$curErrorCode;
	}
	
	static function getErrorDesc()
	{
		return self::$arrErrorDesc[self::$curErrorCode] .' , '. self::$sCurErrorDetail.self::$sCurDebugTrace;
	}
}

?>