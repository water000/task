<?php 
$page_title = 'add';
$error = array();
if(isset($_GET['dosubmit']) && empty($_POST)){
	$error[] = $mbs_appenv->lang('upload_max_filesize');
}
mbs_import('', 'CMctControl', 'CMctAttachmentControl');
mbs_import('user', 'CUserSession');
$max_upload_images = $mbs_appenv->config('mct_max_upload_images');
$allow_edit = true;
$usess = new CUserSession();
list($sess_uid,) = $usess->get();
$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
$images = array();
if(isset($_REQUEST['id'])){
	$page_title = 'edit';
	
	$mct_ctr = CMctControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
	$info = $mct_ctr->get();
	if(empty($info)){
		$mbs_appenv->echoex('Invalid param', 'MERCHANT_EDIT_INVALID_PARAM');
		exit(0);
	}
	
	if($info['owner_id'] != $sess_uid)
		$allow_edit = false;
	
	$mct_atch_ctr = CMctAttachmentControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
	$images = $mct_atch_ctr->get();
	$images = empty($images) ? array() : $images;
	
	if(isset($_REQUEST['_timeline']) && $allow_edit){
		if(isset($_REQUEST['delete']) && isset($_REQUEST['aid'])){
			foreach($images as $k => $img){
				if($img['id'] == $_REQUEST['aid']){
					$path = $mbs_appenv->uploadPath(CMctAttachmentControl::completePath($img['path']));
					if(file_exists($path)){
						unlink($path);
					}
					$mct_atch_ctr->setSecondKey($img['id']);
					$mct_atch_ctr->delNode();
					unset($images[$k]);
					break;
				}
			}
		}else{
			//$req = array_intersect_key($_REQUEST, $info);
			$diff = array_diff_assoc(array_intersect_key($_REQUEST, $info), $info);
			//$info = $diff + $info;
			$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'), array('image'));
			if(empty($error)){
				//unset($info['image']);
				//$info['edit_time'] = time();
				try {
					if(!empty($diff)){
						$diff['edit_time'] = time();
						$info = $diff + $info;
						$ret = $mct_ctr->set($diff);
					}
					for($i=0; $i<count($_FILES['image']['error']); ++$i){
						if(UPLOAD_ERR_OK == $_FILES['image']['error'][$i]){
							$img = array($_FILES['image']['tmp_name'][$i], $_FILES['image']['name'][$i]);
							$id = $mct_atch_ctr->addNode($img);
							$images[] = $img;
						}else if($_FILES['image']['error'][$i] != UPLOAD_ERR_NO_FILE){
							$error[] = $mbs_appenv->lang($_FILES['image']['error'][$i]);
						}
					}
				} catch (Exception $e) {
					if($mbs_appenv->config('PDO_ER_DUP_ENTRY', 'common') == $e->getCode()){
						$error['name'] = sprintf('"%s" %s', $info['name'], $mbs_appenv->lang('existed'));
					}else{
						$error[] = $mbs_appenv->lang('db_exception');
						mbs_error_log($e.getMessage()."\n".$e->getTraceAsString(), __FILE__, __LINE__);
					}
				}
			}
		}
	}
}
else if(isset($_REQUEST['_timeline'])){	
	$info_def = $info;
	$info = array_intersect_key($_REQUEST,$info) + $info;
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	
	if(empty($error)){
		$mct_ctr = CMctControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
		unset($info['image']);
		$info['status'] = CMctControl::convStatus('verify');
		$info['owner_id'] = $sess_uid;
		$info['edit_time'] = $info['create_time'] = time();
		$merchant_id = 0;
		try {
			$merchant_id = $mct_ctr->add($info);
			
			$mct_atch_ctr = CMctAttachmentControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance(), $merchant_id);
			for($i=0; $i<count($_FILES['image']['error']); ++$i){
				if(UPLOAD_ERR_OK == $_FILES['image']['error'][$i]){
					$img = array($_FILES['image']['tmp_name'][$i], $_FILES['image']['name'][$i]);
					$id = $mct_atch_ctr->addNode($img);
				}else if($_FILES['image']['error'][$i] != UPLOAD_ERR_NO_FILE){
					$error['image'] = $mbs_appenv->lang($_FILES['image']['error'][$i]);
				}
			}
			$info = $info_def;
		} catch (Exception $e) {
			if($mbs_appenv->config('PDO_ER_DUP_ENTRY', 'common') == $e->getCode()){
				$error['name'] = sprintf('"%s" %s', $info['name'], $mbs_appenv->lang('existed'));
			}else{
				$error[] = $mbs_appenv->lang('db_exception');
				mbs_error_log($e->getMessage()."\n".$e->getTraceAsString(), __FILE__, __LINE__);
			}
		}
	}
}
if($mbs_appenv->item('client_accept') != 'html'){
	if(count($error) > 0)
		$mbs_appenv->echoex($error, 'MCT_EDIT_ERROR');
	else {
		$info['images'] = $images;
		$mbs_appenv->echoex($info);
	}
	exit(0);
}
?>