<?php 

function __header(){
	global $mbs_appenv;
	
	mbs_import('user', 'CUserSession');
	$us = new CUserSession();
	$info = $us->get();
	$content = $welcome = '';
	if(empty($info)){
		$content = '<a href="'.$mbs_appenv->toURL('login', 'user').'" class="btn-quit">登录</a>';
		$welcome = '请';
	}else{
		$welcome = $info[1]['name'];
		$content = '<a href="'.$mbs_appenv->toURL('logout', 'user').'" class="btn-quit">退出</a>';
	}
	
	return <<<EOT
<h1 class="logo">快讯服务平台</h1>
<p class="loginBar">欢迎您，$welcome
	$content
</p>
EOT;
}


$_notice_frame = <<<EOT
<!doctype html>
<html>
<head>
<title>消息提醒</title>
<link href="{$mbs_appenv->sURL('core.css')}" rel="stylesheet">
%s
</head>
<body>
<h2 class=%s>%s<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a></h2>
</body>
</html>
EOT;

$lang_zh_CN = array(
	'site_name'              => '快讯服务平台',
	'db_exception'           => '系统繁忙，请稍后再试(dbe)',
	'header_html'            => __header(),
	'notice_page'            => $_notice_frame, //%s: meta tag or empty, %s: error/success, %s: msg content
	'click_if_not_redirect'  => '如果没有跳转，请点击链接',
		
	'add'                    => '添加',
	'edit'                   => '编辑',
	'delete'                 => '删除',
	'list'                   => '列表',
	'manage'                 => '管理',
	'search'                 => '查询',
	'back'                   => '返回',
	'close'                  => '关闭',
	'no_data'                => '暂无数据',
	'operation_success'      => '^_^操作成功！',
	'existed'                => '已存在',
	'selected'               => '已选择',
	'confirmed'              => '确认吗？操作完成后数据将无法恢复！',
	'miss_args'              => '缺少参数',
	'name'                   => '名称',
	'time'                   => '时间',
	'content'                => '内容',
	'all'                    => '所有',
	'status'                 => '状态',
	'page_num_count_format'  => '共%d条记录',
	'confirm_submit'         => '确认提交?',
	'continue'               => '继续',
	'or'                     => '或',
	'remove'                 => '移除',
	'error_on_field_exists'  => '提交的数据可能已经存在',
		
	'prev_page'              => '上一页',
	'next_page'              => '下一页',
	'submit'                 => '提交',
	'to_be'                  => '待',
	'new'                    => '新',
	'confirm'                => '确认',
	'check_all'              => '全选',
	'select'                 => '选择',
	'operation'              => '操作',
	'total_count'           => '共%d条',
	'please_input'          => '请输入',
	'cancel'                => '取消',
	'slash'                 => '、',
	'mgr_msg_notify'        => '管理消息提醒',
		
	'num_of_char'           => '个字符.',
	'last'                  => '最后',
	'yesterday'             => '昨天',
	'before_yesterday'      => '前天',
	'invalid_EN_word'       => '无效的英文单词.',
	'choose_please'         => '-请选择-',
		
	UPLOAD_ERR_INI_SIZE      => '上传的文件大小超过了系统的限制',
	UPLOAD_ERR_FORM_SIZE     => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
	UPLOAD_ERR_PARTIAL       => '文件只有部分被上传',
	UPLOAD_ERR_NO_FILE       => '没有文件被上传',
	UPLOAD_ERR_NO_TMP_DIR    => '找不到临时文件夹',
	UPLOAD_ERR_CANT_WRITE    => '文件写入失败',
);


?>