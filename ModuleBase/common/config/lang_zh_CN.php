<?php 

function __header(){
	global $mbs_appenv;
	
	mbs_import('user', 'CUserSession');
	$us = new CUserSession();
	$info = $us->get();
	$content = $welcome = '';
	if(empty($info)){
		$content = '<a href="'.$mbs_appenv->toURL('login', 'user').'" class="pure-menu-link">登录</a>';
		$welcome = '请';
	}else{
		$welcome = $info[1]['name'];
		$content = '<a href="'.$mbs_appenv->toURL('logout', 'user').'" class="pure-menu-link">退出</a>';
	}
	
	return <<<EOT
<div class="home-menu pure-menu pure-menu-horizontal">
        <a class="pure-menu-heading" href="#">消息推送</a>
        <ul class="pure-menu-list">
        	<li class="pure-menu-item"><span style="color:white;">欢迎您，$welcome</span></li>
            <li class="pure-menu-item">$content</li>
        </ul>
    </div>
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
	'site_name'              => '',
	'db_exception'           => '系统繁忙，请稍后再试(dbe)',
	'header_html'            => __header(),
	'notice_page'            => $_notice_frame, //%s: meta tag or empty, %s: error/success, %s: msg content
	'click_if_not_redirect'  => '如果没有跳转，请点击链接',
		
	'add'                    => '添加',
	'edit'                   => '编辑',
	'delete'                 => '删除',
	'list'                   => '列表',
	'search'                 => '查询',
	'back'                   => '返回',
	'no_data'                => '暂无数据',
	'time'                   => '时间',
	'operation_success'      => '操作成功',
	'existed'                => '已存在',
	'confirmed'              => '确认吗？操作完成后数据将无法恢复！',
	'miss_args'              => '缺少参数',
	'name'                   => '名称',
);


?>