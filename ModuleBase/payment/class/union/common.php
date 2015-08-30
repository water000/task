<?php

include_once dirname(__FILE__) . '/SDKConfig.php';

$unpay_logs = array();
function unpay_log($msg){
	$GLOBALS['unpay_logs'][] = $msg;
}
if(defined('DEBUG')){
	register_shutdown_function(function(){echo implode("<br/>\n", $GLOBALS['unpay_logs']);});
}else{
	register_shutdown_function(function(){
		error_log(implode("\n", $GLOBALS['unpay_logs']), 3, SDK_LOG_FILE_PATH.date('Y-md').'log');
	});
}

/**
 * 后台交易 HttpClient通信
 * @param unknown_type $params
 * @param unknown_type $url
 * @return mixed
 */
function sendHttpRequest($params, $url) {
	$opts = http_build_query( $params );

	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//不验证证书
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);//不验证HOST
	curl_setopt ( $ch, CURLOPT_SSLVERSION, 3);
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
		'Content-type:application/x-www-form-urlencoded;charset=UTF-8'));
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $opts );

	/**
	 * 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
	*/
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

	// 运行cURL，请求网页
	$html = curl_exec ( $ch );
	// close cURL resource, and free up system resources
	curl_close ( $ch );
	return $html;
}


/**
 * 处理返回报文 解码客户信息 , 如果编码为utf-8 则转为utf-8
 *
 * @param unknown_type $params        	
 */
function deal_params(&$params) {
	/**
	 * 解码 customerInfo
	 */
	if (! empty ( $params ['customerInfo'] )) {
		$params ['customerInfo'] = base64_decode ( $params ['customerInfo'] );
	}
	
	if (! empty ( $params ['encoding'] ) && strtoupper ( $params ['encoding'] ) == 'utf-8') {
		foreach ( $params as $key => $val ) {
			$params [$key] = iconv ( 'utf-8', 'UTF-8', $val );
		}
	}
}

/**
 * 压缩文件 对应java deflate
 *
 * @param unknown_type $params        	
 */
function deflate_file(&$params) {
	foreach ( $_FILES as $file ) {
		unpay_log ( "---------处理文件---------" );
		if (file_exists ( $file ['tmp_name'] )) {
			$params ['fileName'] = $file ['name'];
			
			$file_content = file_get_contents ( $file ['tmp_name'] );
			$file_content_deflate = gzcompress ( $file_content );
			
			$params ['fileContent'] = base64_encode ( $file_content_deflate );
			unpay_log ( "压缩后文件内容为>" . base64_encode ( $file_content_deflate ) );
		} else {
			unpay_log ( ">>>>文件上传失败<<<<<" );
		}
	}
}

/**
 * 处理报文中的文件
 *
 * @param unknown_type $params        	
 */
function deal_file($params) {
	if (isset ( $params ['fileContent'] )) {
		unpay_log ( "---------处理后台报文返回的文件---------" );
		$fileContent = $params ['fileContent'];
		
		if (empty ( $fileContent )) {
			unpay_log ( '文件内容为空' );
		} else {
			// 文件内容 解压缩
			$content = gzuncompress ( base64_decode ( $fileContent ) );
			$root = SDK_FILE_DOWN_PATH;
			$filePath = null;
			if (empty ( $params ['fileName'] )) {
				unpay_log ( "文件名为空" );
				$filePath = $root . $params ['merId'] . '_' . $params ['batchNo'] . '_' . $params ['txnTime'] . 'txt';
			} else {
				$filePath = $root . $params ['fileName'];
			}
			if (! is_writable ( $filePath )) {
				unpay_log ( "文件:" . $filePath . "不可写，请检查！" );
			} else {
				file_put_contents ( $filePath, $content );
				unpay_log ( "文件位置 >:" . $filePath );
			}
		}
	}
}

/**
 * 构造自动提交表单
 *
 * @param unknown_type $params        	
 * @param unknown_type $action        	
 * @return string
 */
function create_html($params, $action) {
	$encodeType = isset ( $params ['encoding'] ) ? $params ['encoding'] : 'UTF-8';
	$html = <<<eot
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={$encodeType}" />
</head>
<body  onload="javascript:document.pay_form.submit();">
    <form id="pay_form" name="pay_form" action="{$action}" method="post">
	
eot;
	foreach ( $params as $key => $value ) {
		$html .= "    <input type=\"hidden\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />\n";
	}
	$html .= <<<eot
    <input type="submit" type="hidden">
    </form>
</body>
</html>
eot;
	return $html;
}

?>