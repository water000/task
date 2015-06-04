<?php 

class CInfoDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'info',
				self::M_CS=>'utf-8',
				self::G_TL=>'消息快报',
				self::G_DC=>'提供消息的编辑、下发、查询等'
			),
			self::LD_FTR => array(
				array('user', 'checkLogin', true)
			),
			self::TBDEF => array(
				'info' => '(
				   id                   int unsigned auto_increment not null,
				   title                varchar(32) not null,
				   abstract             varchar(255),
				   attach_format        tinyint,
				   attach_path          varchar(255),
				   attach_name          varchar(32),
				   create_time          int unsigned,
				   secure_level         tinyint,
				   creator_id           int unsigned,
				   dep_id               int unsigned,
				   primary key (id),
				   key(creator_id)
				)',
				'info_push_event' => '(
					id                   int unsigned auto_increment not null,
				    pusher_uid           int unsigned,
				    recv_uid             int unsigned,
				    push_time            int unsigned,
				    info_id              int unsigned,
				    status               tinyint,
				    request_time         int unsigned,
				    primary key (id)
				)',
				'info_comment' => '(
					id                   int unsigned auto_increment not null,
				    info_id              int unsigned,
				    comment_uid          int unsigned,
				    comment_time         int unsigned,
				    comment_content      text,
				    primary key (id)
				)'
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
					self::LD_FTR => array(
						array('user', 'checkDepLogin', true)
					),
					self::P_ARGS => array(
						'title'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'标题', self::PA_RNG=>'6,33'),
						'abstract'  => array(self::PA_EMP=>0, self::G_DC=>'概要', self::PA_RNG=>'6,255'),
						'attachment' => array(self::PA_EMP=>0, self::PA_TYP=>'file', self::G_DC=>'附件'),
					)
				),
				'list' => array(
					self::P_TLE => '消息列表',
					self::G_DC => '仅限当前用户编辑过的消息列表',
					self::P_MGR => true,
					self::LD_FTR => array(
						array('user', 'checkDepLogin', true)
					),
				),
				'push' => array(
					self::P_TLE => '下发消息',
					self::G_DC => '下发选中的消息，选择相应的接收用户进行下发',
					self::P_MGR => true,
					self::LD_FTR => array(
						array('user', 'checkDepLogin', true)
					),
				)
			),
		);
	}
}

?>