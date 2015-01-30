<?php

class CUserDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'user',
				self::M_CS=>'utf-8',
				self::G_TL=>'用户信息',
				self::G_DC=>'提供用户的基本信息，包括注册，登录，认证，修改信息等'
			),
			self::TBDEF => array(
				'user_info' => '(
					id int unsigned not null auto_increment,
					nick_name varchar(16) not null,
					phone_num char(14) not null, -- 3(country-code:086)+11(basic num)
					password char(32) not null, -- md5(submit-password)
					reg_ts int unsigned not null, -- register timestamp
					reg_ip varchar(32) not null, -- not only include ipv4 but also include ipv6
					third_platform_id char(33) not null, -- value=([1:weixin, 2:weibo, ...])+md5(returned-id-by-third-platform)
					primary key(id),
					unique key(phone_num),
					unique key(third_platform_id)
				)',
				'user_mobile_device' => '(
					user_id int unsigned not null,
					os_type tinyint not null, -- 1:ios, 2:android, 3:...
					token varchar(32) not null,
					last_submit_ts int unsigned not null, -- last submit device token timestamp
					unique key(user_id)
				)'
			),
			self::PAGES => array(
				'reg_api' => array(
					self::P_TLE => '用户注册',
					self::G_DC  => '用户信息注册，必须提供手机号码,包括通过第三方认证后首次注册的。这样方便以后我们能对应上当前用户',
					self::P_ARGS => array(
						'phone_num'          => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'如果能够获取到国别代码，可以加上，例如08613813888888.非强制要求，如果没有带上，系统默认为086'),
						'captcha'            => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'短信验证码'),
						'password'           => array(self::G_DC=>'用户提供的密码；如果此参数不存在，则third_platform_id必须存在'),
						'third_platform_id'  => array(self::G_DC=>'第三方认证平台返回的唯一编码;如果此参数不存在，则password必须存在'),
						'third_platform_src' => array(self::G_DC=>'第三方平台的来源，1:weixin, 2:weibo, 3...;当third_platform_id出现后，此参数必须存在'),
						'ts'                 => array(self::PA_REQ=>1, self::G_DC=>'请求的时间戳'),
						'sign'               => array(self::PA_REQ=>1, self::G_DC=>'身份签名，用于检验是否是有效客户端。md5(phone_num+password/third_platform_id+ts+APPKEY)'),
					),
					self::P_OUT => '{success:0/1, msg:"如果失败， 返回错误提示.成功后返回user_id", user_id:111}',
				),
				'login_api' => array(
					self::P_TLE => '用户登录',
					self::G_DC  => '用户登录入口,也可以提供第三方平台认证后的登录。登录成功后，设置cookie["token"]="32位的字符串"',
					self::P_ARGS => array(
						'phone_num'          => array(self::G_DC=>'登录的手机号码'),
						'password'           => array(self::G_DC=>'登录的密码, 当third_platform_id不存在时，此参数和phone_num必须存在'),
						'third_platform_id'  => array(self::G_DC=>'第三方认证平台返回的唯一编码;如果phone_num不存在时，此参数和third_platform_src必须存在'),
						'third_platform_src' => array(self::G_DC=>'详细看reg_api'),
						'ts'                 => array(self::G_DC=>'请求的时间戳'),
						'sign'               => array(self::G_DC=>'md5(phone_num/third_platform_id + ts + APPKEY)')
					),
					self::P_OUT => '{success:0/1, msg:"如果失败，输出错误信息", token:"登录成功后的唯一标识"}'
				)
			),
		);
	}
}

?>