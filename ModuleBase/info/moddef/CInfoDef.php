<?php 

class CInfoDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'user',
				self::M_CS=>'utf-8',
				self::G_TL=>'消息快报',
				self::G_DC=>'提供消息的编辑、下发、查询等'
			),
			self::LD_FTR => array(
				array('user', 'checkLogin', true)
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
			
			),
			self::PAGES => array(
				'push_list' => array(
					self::P_TLE => '消息下发列表',
					self::G_DC  => '返回当前用户未读的消息列表',
					self::P_ARGS => array(
						'type'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'消息类型, IMG/VDO/TXT中的一个'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{list:[info-1{详见数据表info中的字段}, 2, 3, ...]}}',
				),
				'detail' => array(
					self::P_TLE => '消息详情',
					self::G_DC  => '返回消息的详细信息',
					self::P_ARGS => array(
						'info_id'     => array(self::PA_REQ=>1, self::PA_TYP=>'integer', self::PA_EMP=>0, self::G_DC=>'消息id'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{info:{详见数据表info中的字段}}',
				),
				'comment' => array(
					self::P_TLE => '消息批阅',
					self::G_DC  => '对消息的具体批阅',
					self::P_ARGS => array(
						'info_id'     => array(self::PA_REQ=>1, self::PA_TYP=>'integer', self::PA_EMP=>0, self::G_DC=>'消息id'),
						'content'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'批阅的内容'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG"}',
				),
				'edit' => array(
					self::P_TLE => '编辑消息',
					self::G_DC => '添加、编辑、删除消息',
					self::P_MGR => true,
				),
				'list' => array(
					self::P_TLE => '消息列表',
					self::G_DC => '仅限当前用户编辑过的消息列表',
					self::P_MGR => true,
				),
				'push' => array(
					self::P_TLE => '下发消息',
					self::G_DC => '下发选中的消息，选择相应的接收用户进行下发',
					self::P_MGR => true,
				)
			),
		);
	}
}

?>