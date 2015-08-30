<?php 

class CInfoPushDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'info_push',
				self::M_CS=>'utf-8',
				self::G_TL=>'消息推送',
				self::G_DC=>'对消息进行推送管理, 包括对消息的批阅'
			),
			self::LD_FTR => array(
				array('user', 'checkLogin', true)
			),
			self::TBDEF => array(
				'info_push_event' => '(
					id                   int unsigned auto_increment not null,
				    pusher_uid           int unsigned,
				    recv_uid             int unsigned,
				    push_time            int unsigned,
				    info_id              int unsigned,
				    status               tinyint,
				    request_time         int unsigned,
				    primary key (id),
					key(pusher_uid),
					key(recv_uid),
					unique key(pusher_uid, recv_uid, info_id)
				)',
				'info_push_comment' => '(
					id                   int unsigned auto_increment not null,
				    info_id              int unsigned,
				    comment_uid          int unsigned,
				    comment_time         int unsigned,
				    comment_content      text,
				    primary key (id),
					key(info_id)
				)',
				'info_push_stat'   => '(
					info_id int unsigned not null,
					new_comment_count int unsigned not null,
					comment_count int unsigned not null,
					push_count int unsigned not null,
					read_count int unsigned not null,
					primary key(info_id)
				)'
			),
			self::PAGES => array(
				'recv_list' => array(
					self::P_TLE => '接收列表',
					self::G_DC  => '返回当前用户未读的消息列表',
					self::P_ARGS => array(
						'class_type'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'消息类型, CX/SX/YQ中的一个'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{list:[info-1{详见数据表info中的字段}, 2, 3, ...]}}',
				),
				'comment' => array(
					self::P_TLE => '消息批阅',
					self::G_DC  => '对消息的具体批阅',
					self::P_ARGS => array(
						'id'     => array(self::PA_REQ=>1, self::PA_TYP=>'integer', self::PA_EMP=>0, self::G_DC=>'消息id'),
						'content'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'批阅的内容'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG"}',
				),
				'push' => array(
					self::P_TLE => '推送新消息',
					self::G_DC => '推送选中的消息，选择相应的接收用户进行推送',
					self::P_MGR => true,
					self::LD_FTR => array(
						array('user', 'checkDepLogin', true)
					),
				),
				'push_list' => array(
					self::P_TLE => '已推送',
					self::G_DC => '当前用户推送的消息列表',
					self::P_MGR => true,
					self::LD_FTR => array(
						array('user', 'checkDepLogin', true)
					),
				),
				'comment_list' => array(
					self::P_TLE => '批阅管理',
					self::G_DC  => '显示最新（7天）的评论',
					self::P_ARGS => array(
						'info_id'     => array(self::PA_TYP=>'integer', self::PA_EMP=>0, self::G_DC=>'消息id'),
					),
					self::P_MGR => true,
				),
				'mgr_notify' => array(
					self::P_TLE => '管理通知',
					self::G_DC  => '用于在管理页面中，定期提醒新消息',
					self::P_MGR => true,
					self::P_MGNF => true,
				)
			),
		);
	}
	
	function install($dbpool, $mempool=null){
		mbs_import('', 'CInfoPushStatControl');
		
		try {
			parent::install($dbpool, $mempool);
			
			$info_push_stat = CInfoPushStatControl::getInstance(self::$appenv, $dbpool, $mempool);
			$info_push_stat->add(array(
				'info_id'           => 0, // which means the all info exists
				'new_comment_count' => 0
			));
		} catch (Exception $e) {
			throw $e;
		}
	}
}

?>