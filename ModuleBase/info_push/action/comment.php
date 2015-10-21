<?php 


if(isset($_REQUEST['id'])){	
	$info_id = intval($_REQUEST['id']);
	
	mbs_import('', 'CInfoCommentControl', 'CInfoPushControl');
	$info_cmt_ctr = CInfoCommentControl::getInstance($mbs_appenv, 
			CDbPool::getInstance(), CMemcachedPool::getInstance(), $info_id);
	
	if(isset($_REQUEST['content'])){
		$comment = trim($_REQUEST['content']);
		if(empty($comment)){
			$mbs_appenv->echoex('empty comment', 'INFO_COMMENT_EMPTY');
			exit(0);
		}
		
		mbs_import('user', 'CUserSession');
		$usersess = new CUserSession();
		list($sess_uid,) = $usersess->get();
		
		mbs_import('', 'CInfoPushControl');
		$info_push_ctr = CInfoPushControl::getInstance($mbs_appenv, 
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$ret = $info_push_ctr->getDB()->search(array('recv_uid'=>$sess_uid, 'info_id'=>$info_id));
		if(empty($ret) || !($ret = $ret->fetchAll(PDO::FETCH_ASSOC))){
			$mbs_appenv->echoex('no such info', 'INFO_NOT_FOUND');
			exit(0);
		}
		
		$info_cmt_ctr->add(array(
			'comment_uid'    => $sess_uid, 
			'comment_content'=> $comment,
			'info_id'        => $info_id,
			'comment_time'   => time(),	 
		));
		
		mbs_import('', 'CInfoPushStatControl');
		$info_push_stat = CInfoPushStatControl::getInstance($mbs_appenv, 
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$info_push_stat->setPrimaryKey(0);
		$info_push_stat->getDB()->incrDup(array(
			'comment_count'     => '1',
<<<<<<< HEAD
			'new_commnet_count' => '1'
=======
			'new_comment_count' => '1'
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d
		));
		$info_push_stat->setPrimaryKey($info_id);
		$info_push_stat->getDB()->incrDup(array(
			'comment_count'     => '1',
<<<<<<< HEAD
			//'new_commnet_count' => 'new_comment_count+1'
=======
			'new_comment_count' => '1'
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d
		));
		
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '');
		
		exit(0);
	}
	
	mbs_import('user', 'CUserControl');
	$user_ctr = CUserControl::getInstance($mbs_appenv, 
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	
	$list = $info_cmt_ctr->get();
	foreach($list as &$row){
		$user_ctr->setPrimaryKey($row['comment_uid']);
		$user_info = $user_ctr->get();
		$row['user_name'] = empty($user_info) ? 'unknown/delete' : $user_info['name'];
	}
	
	$mbs_appenv->echoex($list, '');
}

?>