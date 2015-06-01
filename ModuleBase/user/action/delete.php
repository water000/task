<?php 

if(isset($_REQUEST['id'])){
	mbs_import('', 'CUserControl');
	$user_ins = CUserControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	foreach($_REQUEST['id'] as $id){
		if($id > 3){
			$user_ins->setPrimaryKey(intval($id));
			$user_ins->destroy();
		}
	}
	
	$mbs_appenv->echoex($mbs_appenv->lang('operation_success', 'common'), '', $mbs_appenv->toURL('list'));
}else{
	$mbs_appenv->echoex($mbs_appenv->lang('miss_args', 'common'), '', $mbs_appenv->toURL('list'));
}

?>