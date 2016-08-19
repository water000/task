<?php
class CCommonDef extends CModDef{
	protected function desc(){
		return array(
		    self::MOD => array(self::G_NM=>'common', self::G_CS=>'公共模块', self::M_CS=>'utf-8', ),
		    self::DEPEXT => array('curl'),
		    self::FTR => array(
		    	'ApiSignFtr' => array(self::G_CS => 'CApiSignFtr', self::G_DC => '对接口请求参数的加密进行检查'),
		    ),
		    /*self::LTN => array(
		    	'class' => 'mod.action1,mod.action2,...'
		    ),*/
		    self::TBDEF => array(
		        'common_sms_captcha' => '(
                    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `phone`      varchar(16) CHARACTER SET latin1 NOT NULL,
                    `captcha`    char(6) CHARACTER SET latin1 NOT NULL,
                    `created_at` int(10) unsigned NOT NULL,
                    `verify_num` tinyint(4) NOT NULL,
                    `send_num`   tinyint(4) NOT NULL,
                    `succeed`    tinyint(4) NOT NULL,
                    `group_id`   tinyint(4) NOT NULL,
                    PRIMARY KEY (`id`),
		            KEY `phone` (`phone`,`group_id`)
		        )', 
		        'common_session' => '(
		            id       char(32) CHARACTER SET latin1 NOT NULL , 
		            data     varchar(512) NOT NULL,
		            write_ts int unsigned NOT NULL,
		            primary key(id)
		        )',
		    ),
			self::PAGES => array(
				'send_sms' => array(
					self::P_TLE => '发送短信',
					self::G_DC  => '给指定手机发送短信, 每次发送的间隔1分钟.',
					self::P_ARGS => array(
						'phone' => array(self::PA_REQ=>1, self::G_DC=>'手机号码*S*'),
					    'type'  => array(self::PA_REQ=>1, self::G_DC=>'sms类型(值:captcha)'),
					    'cap_group' => array(self::PA_REQ=>1, self::G_DC=>'验证码分组(值:USER_PWD),请求和验证需一致'),
					    'for'  => array(self::PA_REQ=>0, self::G_DC=>'需要验证码的接口URL，将会检查phone的有效性'),
					),
				    //self::LD_FTR => array(array('common', 'ApiSignFtr', true),),
					self::P_OUT => 'data:{}'
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