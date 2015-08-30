<?php 

$lang_zh_CN = array(
	'info'                  => '消息',
	'edit_info'             => '编辑消息',
	'add_info'              => '创建信息',
	'title'                 => '标题',
	'abstract'              => '概要',
	'attachment'            => '附件',
	'attachment_format'     => '附件格式',
	'unsupport_attachment_type' => '不支持的附件类型',
	'TXT'                   => '文本',
	'IMG'                   => '图片',
	'VDO'                   => '视频',
	'push'                  => '推送',
	'push_list'             => '推送列表',
	'info_list'             => '消息列表',
	'select_recv_user'      => '选择接收用户',
	'select_info'           => '选择消息',
	'recipient'             => '接收人',
	'push_time'             => '推送时间',
	'wait_push'             => '未读',
	'had_read'              => '已读',
	'info_had_push'         => '消息已推送',
	'confirm_delete_info'   => '确认删除选中的消息以及推送过的相关消息记录？',
	'comment'               => '批阅',
	'comment_person'        => '批阅人',
	'latest_days'           => '近%d天',
		
	'all_info'              => '所有信息',
	'total_count'           => '共%d条',
	'title_keys'            => '标题关键字',
	'create_time'           => '创建时间',
	'please_input'          => '请输入...',
	'click_to_add'          => '点击添加',
		
	'menu'              => function(){
		global $mbs_appenv;
		$items = array(
			$mbs_appenv->toURL('list')       => '消息列表',
			$mbs_appenv->toURL('push_list')  => '推送记录',
		);
		$sub_items = array(
			$mbs_appenv->toURL('list')       => array($mbs_appenv->toURL('edit')),
			$mbs_appenv->toURL('push_list')  => array($mbs_appenv->toURL('push')),
		);
		echo '<div class="pure-menu custom-restricted-width"><ul class="pure-menu-list">';
		foreach($items as $link => $val){
			$selected = $mbs_appenv->item('cur_action_url')==$link
			|| in_array($mbs_appenv->item('cur_action_url'), $sub_items[$link]);
			echo '<li class="pure-menu-item',$selected?' pure-menu-selected':'',
			'"><a href="', $link, '" class="pure-menu-link">', $val, '</a></li>';
		}
		echo '</ul></div>';
	},
);

?>