<?php 

$_notice_frame = <<<EOT
<!doctype html>
<html>
<head>
<title>消息提醒</title>
<link href="{$mbs_appenv->sURL('core.css')}" rel="stylesheet">
%s
</head>
<body>
<div class=%s>%s<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a></div>
</body>
</html>
EOT;

$lang_zh_CN = array(
	'site_name'              => '国安守卫者',
	'db_exception'           => '系统繁忙，请稍后再试(dbe)',
	'header_html'            => '',
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
	'operation_success'      => '操作成功！',
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
	'not_found'              => '没有找到或被删除',
		
	'prev_page'              => '&lt;上一页',
	'next_page'              => '下一页&gt;',
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
		
	'num_of_char'           => '个字符',
	'above'                 => '以上',
	'last'                  => '最后',
	'yesterday'             => '昨天',
	'before_yesterday'      => '前天',
	'invalid_EN_word'       => '无效的英文单词.',
	'choose_please'         => '-请选择-',
	'required'              => '必须',
	'upload_max_filesize'   => '单个文件最大'.ini_get('upload_max_filesize'),
	'upload_max_filenum'    => '最多%d个',
	'complete_address'      => '完善地址',
	'log'                   => '日志',
	'operator'              => '操作员',
	'invalid_param'         => '无效的参数',
	'error'                 => '出错啦！',
	'result'                => '结果',
		
	UPLOAD_ERR_INI_SIZE      => '上传的文件大小超过了系统的限制',
	UPLOAD_ERR_FORM_SIZE     => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
	UPLOAD_ERR_PARTIAL       => '文件只有部分被上传',
	UPLOAD_ERR_NO_FILE       => '没有文件被上传',
	UPLOAD_ERR_NO_TMP_DIR    => '找不到临时文件夹',
	UPLOAD_ERR_CANT_WRITE    => '文件写入失败',
    
    'SMSC_INVAID_GROUP'           => '无效的组名',
    'SMSC_EXCEED_MAX_SEND_NUM'    => '已超过最大发送次数，请联系客服',
    'SMSC_INVALID_SEND_INTERVAL'  => '请在允许的时间间隔内操作', 
    'SMSC_NOT_FOUND'              => '未找到相应记录，请先获取验证码',
    'SMSC_EXCEED_MAX_VERIFY_NUM'  => '已超过最大的验证次数，请注意仔细输入',
    'SMSC_WRONG'                  => '验证码错误，请重试',
    'SMSC_EXPIRED'                => '验证码已过期，请重新获取',
    'SMSC_DB_EXCEPTION'           => '系统异常，请稍候再试',
    
    'captcha_title'        => '验证码',
    'captcha_body'         => '验证码：%d请尽快使用该验证码，切勿泄露给他人！【国安守卫者】',
    'incorrect_password'   => '密码有误',
    'src_pwd_equal_new'    => '原密码和新密码相同',
    'welcome'              => '欢迎您，',
    'logout'               => '退出',
    'foot'                 => 'Copyright © 2016  江苏省国家安全厅    All Rights Reserved  版权所有',
);


?>