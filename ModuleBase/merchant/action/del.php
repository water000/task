<?php 

if(isset($_REQUEST['id'])){
	mbs_import('', 'CMctControl', 'CMctAttachmentControl');
	
	$mct_ctr = CMctControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	
	$mct_atch_ctr = CMctAttachmentControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
	
	foreach($_REQUEST['id'] as $id){
		$mct_ctr->setPrimaryKey($id);
		$mct_ctr->destroy();
		
		$mct_atch_ctr->setPrimaryKey($id);
		$images = $mct_atch_ctr->get();
		if(!empty($images)){
			foreach($images as $k => $img){
				$path = $mbs_appenv->uploadPath(CMctAttachmentControl::completePath($img['path']));
				if(file_exists($path)){
					unlink($path);
				}
			}
			$mct_atch_ctr->destroy();
		}
	}
	
	$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '', $mbs_appenv->toURL('list'));
}


?>