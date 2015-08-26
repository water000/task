<?php 

mbs_import('', 'CInfoPushStatControl');
mbs_import('info', 'CInfoControl');
mbs_import('user', 'CUserSession');

$user_sess = new CUserSession();
list($sess_uid, ) = $user_sess->get();

$output = array();

try {
	$info_push_stat = CInfoPushStatControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	$count = $info_push_stat->getDB()->countNewComment($sess_uid, mbs_tbname(CInfoControl::TBNAME));
	if($count > 0){
		$output [] = array(
			'id'                => 'info_push_new_comment_count',
			'redirect'          => $mbs_appenv->toURL('comment_list'),
			'title'             => $mbs_appenv->lang('info_push_new_comment'),
			'html'              => '<b>'.$count.'</b>'.$mbs_appenv->lang('new_comment'),
		);
	}
	$mbs_appenv->echoex($output);
	
} catch (Exception $e) {
	throw $e;
}


?>