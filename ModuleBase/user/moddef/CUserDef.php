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
				'checkLogin' =>array(self::G_CS=>'CUserSession', self::G_DC=>'检查用户是否登录'),
			),
		    self::DEPEXT => array('password_hash'),
			self::TBDEF => array(
			    /*'user_career_category' => '(
			        id   int unsigned auto_increment not null,
				    name varchar(64) not null,
			        primary key(id),
			        unique key(name)
			    )',*/
			    'user_city_dict' => '(
			        id   int unsigned auto_increment not null,
				    name varchar(64) not null,
			        primary key(id) 
			    )',
				'user_info' => '(
					id                   int unsigned auto_increment not null,
				    name                 varchar(16) not null,
			        avatar_path          char(32) not null default "",
				    password             varchar(128) not null,
				    phone                char(11) not null,
				    email                varchar(64) not null default "",
			        career_cid           int unsigned not null,
			        city_no              int unsigned not null,
				    reg_ts               int unsigned not null,
				    reg_ip               varchar(32) not null,
					pwd_modify_count     int unsigned not null,
			        status               tinyint not null,
			        gender               tinyint not null, -- 0: sceret, 1: male, 2: female
					primary key(id),
					unique key(phone)
				)',
			    'user_login_device' => '(
			        uid   int unsigned not null,
			        sid   char(32) CHARACTER SET latin1 NOT NULL,
			        type  tinyint NOT NULL,   -- PC, PAD, PHONE, ...
			        os    tinyint NOT NULL,     -- windows, ios, android, ..
			        id    varchar(32) CHARACTER SET latin1 NOT NULL,  -- device id(for pushing)
			        primary key(uid)
			    )',
			),
			self::PAGES => array(
			    'reg' => array(
			        self::P_TLE => '注册',
			        self::G_DC  => '用户注册系统的入口.',
			        self::P_ARGS => array(
			            'phone'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机*S*', self::PA_RNG=>'11, 16'),
			            'password'      => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 32'),
			            'captcha'       => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机验证码', self::PA_RNG=>'6,7'),
			            'cap_group'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'验证码组，见/common/send_sms'),
			        ),
			        self::P_OUT => 'data:{user_id:1}',
			    ),
				'login' => array(
					self::P_TLE => '登录',
					self::G_DC  => '用户登录系统的入口.',
					self::P_ARGS => array(
						'phone'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机*S*', self::PA_RNG=>'11, 16'),
						'password'      => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 32'),
						'captcha'       => array(self::PA_REQ=>0, self::G_DC=>'第一次登录没有验证码，当登录失败后会出现，客户端需要做相应处理', self::PA_RNG=>'4,5'),
						'remember_me'   => array(self::PA_REQ=>0, self::G_DC=>'记住我！延长用户登录的有效期'),
					    'device_id'     => array(self::PA_REQ=>0, self::PA_EMP=>0, self::G_DC=>'设备号', self::PA_RNG=>'16, 33'),
					),
					self::P_OUT => 'data:{user:{详见#user_info#表中字段列表}, token:32位的字符串}',
				),
				'logout' => array(
					self::P_TLE => '注销',
					self::G_DC  => '注销当前已登录的用户.(APP)',
					//self::P_OUT => '{retcode:"SUCCESS"}'
				),
				'edit' => array(
					self::P_TLE => '编辑',
					self::G_DC  => '编辑用户信息',
					self::P_MGR => true,
					self::P_NCD => true,
					self::P_ARGS => array(
						'name'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'2, 17'),
						'password'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 17'),
						'phone'        => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机', self::PA_RNG=>'11, 12'),
						'email'        => array(self::PA_REQ=>0, self::G_DC=>'邮箱', self::PA_RNG=>'6, 255'),
					),
				),
				'list' => array(
					self::P_TLE => '用户管理',
					self::G_DC  => '用户的列表，也可以搜索用户(phone, name, email)，都是精确查询，不支持模糊查询',
					self::P_MGR => true
				),
				'myinfo'  => array(
					self::P_TLE => '我的信息',
					self::G_DC  => '显示或修改我的信息，如果提供参数列表中的参数时，则进行修改,没有则返回当前用户信息.',
					self::P_ARGS => array(
					    'name'          => array(self::PA_REQ=>0, self::PA_EMP=>0, self::G_DC=>'用户名', self::PA_RNG=>'2, 16'),
					    'email'         => array(self::PA_REQ=>0, self::G_DC=>'邮箱', self::PA_RNG=>'6, 64'),
					    'career_cid'    => array(self::PA_REQ=>0, self::PA_TYP=>'integer', self::G_DC=>'职业id'),
					    'city_no'       => array(self::PA_REQ=>0, self::G_DC=>'city编号', self::PA_TYP=>'integer'),
					    'avatar'        => array(self::PA_REQ=>0, self::G_DC=>'头像', self::PA_TYP=>'file'),
					    'gender'        => array(self::PA_REQ=>0, self::PA_TYP=>'integer', self::G_DC=>'性别id'),
					),
					self::P_OUT => 'data:{userinfo:{user-info}[, gender_list:{0:**,1:**}, career_list:{0:**, 1:**}]}',
					self::LD_FTR => array(
						array('user', 'checkLogin', true)
					),
				),
			    'pwd_modify'  => array(
			        self::P_TLE => '修改密码',
			        self::G_DC  => '修改密码',
			        self::P_ARGS => array(
			            'src_pwd'  => array(self::PA_REQ=>1, self::G_DC=>'原来密码*S*', self::PA_RNG=>'6, 32'),
			            'pwd1'     => array(self::PA_REQ=>1, self::G_DC=>'新密码,不能与原密码相同', self::PA_RNG=>'6, 32'),
			            'pwd2'     => array(self::PA_REQ=>1, self::G_DC=>'确认密码，须与1密码相同', self::PA_RNG=>'6, 32'),
			        ),
			        self::P_OUT => 'data:{}',
			        self::LD_FTR => array(
			            array('user', 'checkLogin', true)
			        ),
			    ),
			    'pwd_reset'  => array(
			        self::P_TLE => '密码找回',
			        self::G_DC  => '使用手机号码找回密码，需要用短信进行验证',
			        self::P_ARGS => array(
			            'phone'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机*S*', self::PA_RNG=>'11, 16'),
			            'password'      => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 32'),
			            'captcha'       => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机验证码', self::PA_RNG=>'6,7'),
			            'cap_group'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'验证码组，见/common/send_sms'),
			        ),
			        self::P_OUT => 'data:{}',
			    )
			),
		);
	}
		
	function install($dbpool, $mempool=null){
		parent::install($dbpool, $mempool);
		
		mbs_import('', 'CUserInfoCtr');
		try {
			$ins = CUserInfoCtr::getInstance(self::$appenv, $dbpool, $mempool);
			

			$uid = $ins->add(array(
			    'id'        => 88,
			    'name'      => 'sys_payer',
			    'password'  => CUserInfoCtr::passwordFormat('123123'),
			    'phone'     => '13555555555',
			    'reg_ts'    => time(),
			    'reg_ip'    => self::$appenv->item('client_ip')
			));
			$uid = $ins->add(array(
				'id'        => 1,
				'name'      => 'admin',
				'password'  => CUserInfoCtr::passwordFormat('123321'),
				'phone'     => '13666666666',
				'reg_ts'    => time(),
				'reg_ip'    => self::$appenv->item('client_ip')
			));
			$uid = $ins->add(array(
				'id'        => 10,
				'name'      => 'developer',
				'password'  => CUserInfoCtr::passwordFormat('123123'),
				'phone'     => '13888888888',
				'reg_ts'    => time(),
				'reg_ip'    => self::$appenv->item('client_ip')
			));
			$uid = $ins->add(array(
			    'id'        => 99,
			    'name'      => 'tester',
			    'password'  => CUserInfoCtr::passwordFormat('123123'),
			    'phone'     => '13999999999',
			    'reg_ts'    => time(),
			    'reg_ip'    => self::$appenv->item('client_ip')
			));
			
		
		} catch (Exception $e) {
			throw $e;
		}
	}
}

?>