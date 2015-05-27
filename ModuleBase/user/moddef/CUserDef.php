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
				    organization         varchar(32),
				    phone                char(11),
				    email                varchar(255),
				    IMEI                 varchar(32),
				    IMSI                 varchar(32),
				    VPDN_name            varchar(32),
				    VPDN_pass            varchar(32),
				    class_id             int unsigned,
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
					create_time          int unsigned,
					primary key (id),
					unique key(name),
					unique key(code)
				)'
			),
			self::PAGES => array(
				'edit' => array(
					self::P_TLE => '编辑',
					self::G_DC  => '编辑用户信息',
					self::P_ARGS => array(
						'name'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'2, 17'),
						'password'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 17'),
						'organization' => array(self::PA_REQ=>0, self::G_DC=>'单位', self::PA_RNG=>'2, 32'),
						'phone'        => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机', self::PA_RNG=>'11, 12'),
						'email'        => array(self::PA_REQ=>0, self::G_DC=>'邮箱', self::PA_RNG=>'6, 255'),
						'IMEI'         => array(self::PA_REQ=>0, self::G_DC=>'邮箱', self::PA_RNG=>'6, 32'),
						'IMSI'         => array(self::PA_REQ=>0, self::G_DC=>'邮箱', self::PA_RNG=>'6, 32'),
						'VPDN_name'    => array(self::PA_REQ=>0, self::G_DC=>'VPDN名称', self::PA_RNG=>'6, 32'),
						'VPDN_pass'    => array(self::PA_REQ=>0, self::G_DC=>'VPDN密码', self::PA_RNG=>'6, 32'),
					),
				),
				'class' => array(
					self::P_TLE => '分类',
					self::G_DC  => '获取、编辑用户分类',
					self::P_ARGS => array(
						'name'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'2, 16'),
						'code'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'编码', self::PA_RNG=>'2, 32'),
					),
				),
			),
		);
	}
}

?>
