<?php 

function __user_mod_menu(){
	
}


$lang_zh_CN = array(
	'oper_succ'     => '操作成功&nbsp;!&nbsp;&nbsp;您可以继续编辑或者浏览',
	'close'         => '关闭',
	'login'         => '登录',
	'phone'         => '手机号码',
	'password'      => '密码',
	'captcha'       => '验证码',
	'reload_on_unclear' => '看不清，换一个',
	'invalid_phone' => '手机号码无效',
	'invalid_password'  => '密码无效',
	'invalid_captcha'   => '验证码无效',
	'remember_me'       => '记住我',
	'had_login'         => '您已登录！',
	'login_succeed'     => '登录成功！',
	'logout_succeed'    => '退出成功！',
	'login_first'       => '请先登录',
		
	'record_info'       => '录入信息',
	'edit_info'         => '编辑信息',
	'name'              => '姓名',
	'organization'       => '单位',
	'email'             => '邮箱',
	'VPDN_name'         => 'VPDN 名称',
	'class'             => '分类',
	'select_class'      => '获取分类',
	'add_class'         => '添加分类',
	'class_name'        => '名称',
	'class_code'        => '编码',
	'class_list'        => '列表',
	'all_class'         => '全部分类',
	'select'            => '选择',
	'delete'            => '删除',
	
	'list'              => '列表',
	'search'            => '查询',
	'add'               => '添加',
	'edit'              => '编辑',
	'user'              => '用户',
		
	'add_department'    => '添加部门',
	'join_department'   => '加入相应业务部门',
	'dep_exists'        => '部门已存在',
	'member_exists'     => '成员已存在，或已加入其它部门',
	'dep_member'        => '部门成员',
	'join_time'         => '加入时间',
	'confirmed'         => '确认删除此部门和部门下所有的成员吗？删除后，操作无法撤销',
	'dep_login'         => '部门登录',
		
	'menu'              => function(){
		global $mbs_appenv;
		$items = array(
			$mbs_appenv->toURL('list')       => '列表',
			$mbs_appenv->toURL('class')      => '分类',
			$mbs_appenv->toURL('department') => '部门',
		);
		$sub_items = array(
			$mbs_appenv->toURL('list')       => array($mbs_appenv->toURL('edit')),
			$mbs_appenv->toURL('class')      => array($mbs_appenv->toURL('class_edit')),
			$mbs_appenv->toURL('department') => array($mbs_appenv->toURL('dep_edit')),
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