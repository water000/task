<?php
class CCommonDef extends CModDef{
	protected function desc(){
		return array(
		    self::MOD => array(self::G_NM=>'common', self::M_CS=>'gbk', ),
		    /*self::FTR => array(
		    	self::G_NM => array(G_CS => '', G_DC => ''),
		    ),*/
		    /*self::LTN => array(
		    	'class' => 'mod.action1,mod.action2,...'
		    ),*/
			self::PAGES => array(
				'sms_captcha_api' => array(
					self::P_TLE => '短信验证码',
					self::G_DC  => '给指定手机发送验证码，并返回当前验证码。每次发送的间隔1分钟',
					self::P_ARGS => array(
						'phone_num' => array(self::PA_REQ=>1, self::G_DC=>'手机号码'),
						'ts'        => array(self::PA_REQ=>1, self::G_DC=>'请求的时间戳'),
						'sign'      => array(self::PA_REQ=>1, self::G_DC=>'身份签名，用于检验是否是有效客户端。md5(phone_num+ts+APPKEY)')
					),
					self::P_OUT => '{success:0/1: msg:"如果失败，输出错误信息", captcha_num:""}'
				),
				'img_captcha' => array(
					self::P_TLE => '图形验证码',
					self::G_DC  => '输出图形验证码到客户端',
					self::P_ARGS => array(
					),
				),
			),
	  );
	}
}

?>