<?php 

mbs_import('', 'CInfoPushStatControl');
<<<<<<< HEAD
=======
mbs_import('info', 'CInfoControl');
mbs_import('user', 'CUserSession');

$user_sess = new CUserSession();
list($sess_uid, ) = $user_sess->get();
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d

$output = array();

try {
	$info_push_stat = CInfoPushStatControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
<<<<<<< HEAD
	$info_push_stat->setPrimaryKey(0);
	$stat = $info_push_stat->get();
	if(!empty($stat)/* && $stat['new_comment_count'] > 0*/){
=======
	$count = $info_push_stat->getDB()->countNewComment($sess_uid, mbs_tbname(CInfoControl::TBNAME));
	if($count > 0){
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d
		$output [] = array(
			'id'                => 'info_push_new_comment_count',
			'redirect'          => $mbs_appenv->toURL('comment_list'),
			'title'             => $mbs_appenv->lang('info_push_new_comment'),
<<<<<<< HEAD
			'html'              => '<b>'.$stat['new_comment_count'].'</b>'.$mbs_appenv->lang('new_comment'),
		);
		$mbs_appenv->echoex($output);
	}
=======
			'html'              => '<b>'.$count.'</b>'.$mbs_appenv->lang('new_comment'),
		);
	}
	$mbs_appenv->echoex($output);
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d
	
} catch (Exception $e) {
	throw $e;
}


?>