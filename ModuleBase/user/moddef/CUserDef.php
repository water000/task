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
			    'user_career_category' => '(
			        id   int unsigned auto_increment not null,
				    name varchar(64) not null,
			        primary key(id)
			    )',
			    'user_city_dict' => '(
			   
			    )',
				'user_info' => '(
					id                   int unsigned auto_increment not null,
				    name                 varchar(16) not null,
			        avatar_path          varchar(40) not null,
				    password             varchar(128) not null,
				    phone                char(11) not null,
				    email                varchar(64) not null,
			        career_cid           int unsigned not null,
			        city_no              int unsigned not null,
				    reg_time             int unsigned not null,
				    reg_ip               varchar(32) not null,
					pwd_modify_count     int unsigned not null,
			        status               tinyint not null,
					primary key(id),
					unique key(phone)
				)',
			    'user_wallet' => '(
			        uid            int unsigned not null, 
			        amount         int unsigned not null, 
			        history_amount int unsigned not null,
			        last_change_ts int unsigned not null,
			        status         tinyint not null, -- 0: normal, 1: banned(banned by sys if the user operate with some exceptions)
			        primary key(uid)
			    )',
			    'user_wallet_change_history' => '(
			        id         int unsigned auto_increment not null,
			        pay_uid    int unsigned not null,
			        payee_uid  int unsigned not null,
			        event_type tinyint unsigned not null, -- 0: get by submiting task, 1: withdraw
			        fee        int unsigned not null,
			        change_ts  int unsigned not null,
			        primary key(id),
			        key(uid),
			        key(other_uid)
			    )',
			    // insert the record to 'user_wallet_withdraw_history' if successful and delete it after user visit
			    'user_wallet_withdraw_apply' => '(
			        uid          int unsigned not null,
			        fee          int unsigned not null,
			        dest_account varchar(64) not null,
			        account_name varchar(8) not null,
			        account_type tinyint not null,
			        submit_date  int unsigned not null,
			        status       tinyint not null, -- 0: user submit, 1: sys accepted, 2: successful, 3: failure
			        fault_msg    varchar(16) not null, -- payment result, successful or failure
			        attempt_num  tinyint not null,
			        primary key(uid)
			    )',
			    'user_wallet_withdraw_history' => '(
			        id           int unsigned auto_increment not null, 
			        uid          int unsigned not null, 
			        fee          int unsigned not null, 
			        dest_account varchar(64) not null,
			        account_name varchar(8) not null,
			        account_type tinyint not null,
			        submit_ts    int unsigned not null, 
			        success_ts   int unsigned not null,
			        primary key(id),
			        key(uid)
			    )',
			    
			),
			self::PAGES => array(
			    'reg' => array(
			        self::P_TLE => '注册',
			        self::G_DC  => '用户注册系统的入口',
			        self::P_ARGS => array(
			            'name'          => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机', self::PA_RNG=>'2, 16'),
			            'phone'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机', self::PA_RNG=>'11, 16'),
			            'password'      => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 32'),
			            'captcha'       => array(self::PA_REQ=>0, self::G_DC=>'第一次登录没有验证码，当登录失败后会出现，客户端需要做相应处理', self::PA_RNG=>'4,5'),
			            'email'         => array(self::PA_REQ=>0, self::G_DC=>'邮箱', self::PA_RNG=>'6, 64'),
			            'career_cid'    => array(self::PA_REQ=>0, self::PA_TYP=>'integer', self::G_DC=>'职业id'),
			            'city_no'       => array(self::PA_REQ=>0, self::G_DC=>'city编号', self::PA_TYP=>'integer'),
			            'avatar'        => array(self::PA_REQ=>0, self::G_DC=>'头像', self::PA_TYP=>'file'),
			        ),
			        self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{user_id:1, avatar:"url"}',
			    ),
				'login' => array(
					self::P_TLE => '登录',
					self::G_DC  => '用户登录系统的入口',
					self::P_ARGS => array(
						'phone'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机', self::PA_RNG=>'11, 16'),
						'password'      => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 32'),
						'captcha'       => array(self::PA_REQ=>0, self::G_DC=>'第一次登录没有验证码，当登录失败后会出现，客户端需要做相应处理', self::PA_RNG=>'4,5'),
						'remember_me'   => array(self::PA_REQ=>0, self::G_DC=>'记住我！延长用户登录的有效期'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{user:{详见#user_info#表中字段列表}, token:32位的字符串}',
				),
				'logout' => array(
					self::P_TLE => '注销',
					self::G_DC  => '注销当前已登录的用户',
					self::P_OUT => '{retcode:"SUCCESS"}'
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
					self::G_DC  => '显示或修改我的信息，如果提供参数列表中的参数时，则进行修改.没有则返回当前用户信息',
					self::P_ARGS => array(
						'pwd1'     => array(self::PA_REQ=>1, self::G_DC=>'新密码', self::PA_RNG=>'6, 32'),
						'pwd2'     => array(self::PA_REQ=>1, self::G_DC=>'确认密码，须与1密码相同', self::PA_RNG=>'6, 32'),
						'src_pwd'  => array(self::PA_REQ=>1, self::G_DC=>'原来密码', self::PA_RNG=>'6, 32'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{user-info}',
					self::LD_FTR => array(
						array('user', 'checkLogin', true)
					),
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
				'id'        => 1,
				'name'      => 'admin',
				'password'  => CUserInfoCtr::passwordFormat('123321'),
				'phone'     => '13666666666',
				'reg_time'  => time(),
				'reg_ip'    => self::$appenv->item('client_ip')
			));
			$uid = $ins->add(array(
				'id'        => 10,
				'name'      => 'developer',
				'password'  => CUserInfoCtr::passwordFormat('123123'),
				'phone'     => '13888888888',
				'reg_time'  => time(),
				'reg_ip'    => self::$appenv->item('client_ip')
			));
			$uid = $ins->add(array(
			    'id'        => 99,
			    'name'      => 'tester',
			    'password'  => CUserInfoCtr::passwordFormat('123123'),
			    'phone'     => '13999999999',
			    'reg_time'  => time(),
			    'reg_ip'    => self::$appenv->item('client_ip')
			));
		} catch (Exception $e) {
			throw $e;
		}
	}
}

?>