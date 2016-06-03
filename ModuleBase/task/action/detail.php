<?php 

mbs_import('', 'CTaskInfoCtr', 'CTaskAttachmentCtr');
mbs_import('common', 'CImage');

if(!isset($_REQUEST['id'])){
    $mbs_appenv->echoex('miss param', 'TASK_DETAIL_INVALID_PARAM');
    exit(0);
}

$task_id = intval($_REQUEST['id']);
$task_ctr = CTaskInfoCtr::getInstance($mbs_appenv,
    CDbPool::getInstance(), CMemcachedPool::getInstance(), $task_id);
$info = $task_ctr->get();
if(empty($info)){
    $mbs_appenv->echoex('Invalid param', 'TASK_DETAIL_INVALID_PARAM');
    exit(0);
}

$info['images'] = array();
if($info['contain_attachment']){
    $img_thumb = new CImage(CTaskAttachmentCtr::SUB_DIR);
    $task_atch_ctr = CTaskAttachmentCtr::getInstance($mbs_appenv,
        CDbPool::getInstance(), CMemcachedPool::getInstance(), $task_id);
    $list = $task_atch_ctr->get();
    foreach($list as $a){
        $info['images'][] = $mbs_appenv->uploadURL($img_thumb->completePath($a['path']));
    }
}

if($mbs_appenv->item('client_accept') != 'html'){
	if(count($error) > 0)
		$mbs_appenv->echoex($error, 'TASK_DETAIL_ERROR');
	else {
		$mbs_appenv->echoex($info);
	}
	exit(0);
}


?>