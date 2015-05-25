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
			self::FTR => array(
				'checkLogin' =>array(self::G_CS=>'CUserSession', self::G_DC=>'检查用户是否登录')
			),
			self::TBDEF => array(
				'user_info' => '(
					id                   int unsigned auto_increment not null,
				    name                 varchar(8),
				    password             char(38),
				    orgnization          varchar(32),
				    phone                char(11),
				    email                varchar(255),
				    IMEI                 varchar(32),
				    IMSI                 varchar(32),
				    VPDN_name            varchar(32),
				    VPDN_pass            varchar(32),
				    class_id             shortint unsigned,
				    reg_time             int unsigned,
				    reg_ip               varchar(32),
					primary key(id),
					unique key(phone)
				)',
				/*'user_third_platform' => '(
					user_id int unsigned not null,
					plat_id varchar(33) not null, -- value=([1:weixin, 2:weibo, ...])+returned-id-by-third-platform
					unique key(plat_id),
					key(user_id)
				)',*/
				'user_mobile_device' => '(
					user_id int unsigned not null,
					os_type tinyint not null, -- 1:ios, 2:android, 3:...
					token varchar(32) not null,
					last_submit_ts int unsigned not null, -- last submit device token timestamp
					unique key(user_id)
				)',
				'user_class' => '(
					id                   int unsigned auto_increment not null,
					name                 varchar(16),
					code                 varchar(32),
					primary key (id),
					unique key(name),
					unique key(code)
				)'
			),
			self::PAGES => array(
				'class' => array(
					self::P_TLE => '分类',
					self::G_DC  => '获取、编辑用户分类',
					self::P_ARGS => array(
						'class_name'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'3, 16'),
						'class_code'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'编码', self::PA_RNG=>'2, 32'),
					),
				),
			),
		);
	}
}

?>
