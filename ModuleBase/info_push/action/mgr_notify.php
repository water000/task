<?php 

mbs_import('', 'CInfoPushStatControl');

$output = array();

try {
	$info_push_stat = CInfoPushStatControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	$info_push_stat->setPrimaryKey(0);
	$stat = $info_push_stat->get();
	if(!empty($stat)/* && $stat['new_comment_count'] > 0*/){
		$output [] = array(
			'id'                => 'info_push_new_comment_count',
			'redirect'          => $mbs_appenv->toURL('comment_list'),
			'title'             => $mbs_appenv->lang('info_push_new_comment'),
			'html'              => '<b>'.$stat['new_comment_count'].'</b>'.$mbs_appenv->lang('new_comment'),
		);
		$mbs_appenv->echoex($output);
	}
	
} catch (Exception $e) {
	throw $e;
}


?>