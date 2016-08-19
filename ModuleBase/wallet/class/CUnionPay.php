<?php

define('DEBUG', true);

include_once dirname(__FILE__) . '/union/secureUtil.php';
include_once dirname(__FILE__) . '/union/common.php';

if(defined('DEBUG')){
    include_once dirname(__FILE__) . '/union/SDKConfig.test.php';
}else{
    include_once dirname(__FILE__) . '/union/SDKConfig.php';
}

class CUnionPay extends CMultiRowControl {
	private static $instance = null;
	
	protected function __construct($db, $cache, $primarykey = null, $secondKey = null){
		parent::__construct($db, $cache, $primarykey, $secondKey);
	}
	
	/**
	 *
	 * @param CAppEnv $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $user_id = null){
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CPrivUserControl(
						new CMultiRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('payment_order'), 'user_id', $user_id, 'id'),
						$memconn ? new CMultiRowOfCache($memconn, $primarykey, 'CUnionPay') : null
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		self::$instance->setPrimaryKey($user_id);
	
		return self::$instance;
	}
	
	function requestId(){
		$params = array(
			'version' => '5.0.0',				//版本号
			'encoding' => 'utf-8',				//编码方式
			'certId' => getSignCertId (),			//证书ID
			'txnType' => '01',				//交易类型
			'txnSubType' => '01',				//交易子类
			'bizType' => '000201',				//业务类型
			'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  		//前台通知地址，控件接入的时候不会起作用
			'backUrl' => SDK_BACK_NOTIFY_URL,		//后台通知地址
			'signMethod' => '01',		//签名方法
			'channelType' => '08',		//渠道类型，07-PC，08-手机
			'accessType' => '0',		//接入类型
			'merId' => '888888888888888',	//商户代码，请改自己的测试商户号
			'orderId' => date('YmdHis'),	//商户订单号，8-40位数字字母
			'txnTime' => date('YmdHis'),	//订单发送时间
			'txnAmt' => '100',		//交易金额，单位分
			'currencyCode' => '156',	//交易币种
			'orderDesc' => '订单描述',  //订单描述，可不上送，上送时控件中会显示该信息
			'reqReserved' =>' 透传信息', //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
		);
		
		
	}
}

?>