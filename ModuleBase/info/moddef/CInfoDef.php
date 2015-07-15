<?php 

class CInfoDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'info',
				self::M_CS=>'utf-8',
				self::G_TL=>'消息快报',
				self::G_DC=>'提供消息的编辑、推送、查询等'
			),
			self::LD_FTR => array(
				array('user', 'checkLogin', true)
			),
			self::DEPEXT => array('gd'),
			self::TBDEF => array(
				'info' => '(
				   id                   int unsigned auto_increment not null,
				   title                varchar(32) not null,
				   abstract             varchar(255),
				   attachment_format    tinyint,
				   attachment_path      varchar(255),
				   attachment_name      varchar(32),
				   create_time          int unsigned,
				   secure_level         tinyint,
				   creator_id           int unsigned,
				   dep_id               int unsigned,
				   primary key (id),
				   key(creator_id)
				)',
			),
			self::PAGES => array(
				'detail' => array(
					self::P_TLE => '消息详情',
					self::G_DC  => '返回消息的详细信息',
					self::P_ARGS => array(
						'id'     => array(self::PA_REQ=>1, self::PA_TYP=>'integer', self::PA_EMP=>0, self::G_DC=>'消息id'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{info:{详见数据表info中的字段}}',
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
						'abstract'  => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'概要', self::PA_RNG=>'6,255'),
						'attachment' => array(self::PA_TYP=>'file', self::G_DC=>'附件'),
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
				
			),
		);
	}
}

?>