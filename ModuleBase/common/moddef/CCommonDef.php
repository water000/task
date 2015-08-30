<?php
class CCommonDef extends CModDef{
	protected function desc(){
		return array(
		    self::MOD => array(self::G_NM=>'common', self::G_CS=>'公共模块', self::M_CS=>'gbk', ),
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
						'phone' => array(self::PA_REQ=>1, self::G_DC=>'手机号码'),
						'ts'        => array(self::PA_REQ=>1, self::G_DC=>'请求的时间戳'),
						'sign'      => array(self::PA_REQ=>1, self::G_DC=>'md5(phone+ts+APPKEY)')
					),
					self::P_OUT => '{success:0/1, msg:"如果失败，输出错误信息", captcha_num:""}'
				),
				'img_captcha' => array(
					self::P_TLE => '图形验证码',
					self::G_DC  => '输出图形验证码到客户端',
					self::P_ARGS => array(
					),
				),
				'version'    => array(
					self::P_TLE => '版本信息',
					self::G_DC  => '提供了版本的信息，以及修改，app的大概介绍',
					self::P_OUT => '{"version_id":"1.0", "version_content"=>"", "content"=>"", "APP_URL"=>""}'
				)
			),
	  );
	}
}

?>