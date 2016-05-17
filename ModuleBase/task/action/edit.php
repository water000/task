<?php 

mbs_import('', 'CTaskInfoCtr', 'CTaskAttachmentCtr');
mbs_import('common', 'CImage');

$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
$task_id = 0;

mbs_import('user', 'CUserSession');
$max_upload_images = $mbs_appenv->config('mct_max_upload_images');
$usess = new CUserSession();
list($sess_uid,) = $usess->get();

if(isset($_REQUEST['id'])){
    $task_id = intval($_REQUEST['id']);
    $task_ctr = CTaskInfoCtr::getInstance($mbs_appenv,
        CDbPool::getInstance(), CMemcachedPool::getInstance(), $task_id);
    $info = $task_ctr->get();
    if(empty($info)){
        $mbs_appenv->echoex('Invalid param', 'TASK_EDIT_INVALID_PARAM');
        exit(0);
    }
    $error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'), array('image'));
    if(empty($error)){
        $diff = array_diff_assoc(array_intersect_key($_REQUEST, $info), $info);
        if(!empty($diff)){
            $diff['pub_time'] = time();
            $info = $diff + $info;
            $ret = $task_ctr->set($diff);
        }
    }
}else if(isset($_REQUEST['title'])){
    $info_def = $info;
    $info = array_intersect_key($_REQUEST,$info) + $info;
    $error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
    if(empty($error)){
        $task = array(
            'title'              => $info['title'],
            'desc'               => $info['desc'],
            'contain_attachment' => isset($_FILES['image']['error']) && count($_FILES['image']['error']) > 0,
            'cate_id'            => $info['cate_id'],
            'price'              => CStrTools::currconv($info['price']),
            'pub_uid'            => $sess_uid,
            'pub_time'           => time(),
        );
        $task_ctr = CTaskInfoCtr::getInstance($mbs_appenv,
            CDbPool::getInstance(), CMemcachedPool::getInstance());
        $task_id = $task_ctr->addNode($task);
    }
}

if(isset($_FILES['image']['error']) && $task_id > 0){
    $img_count = count($_FILES['image']['error']);
    if($img_count > 0){
        $task_atch = CTaskAttachmentCtr::getInstance($mbs_appenv,
            CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
        $img_thumb = new CImage(CTaskAttachmentCtr::SUB_DIR);
        for($i=0; $i<$img_count; ++$i){
            if(UPLOAD_ERR_OK == $_FILES['image']['error'][$i]){
                $img = array($_FILES['image']['tmp_name'][$i], $_FILES['image']['name'][$i]);
                $path = $img_thumb->thumbnailEx($mbs_appenv, $img);
                if($path !== false){
                    $node = array(
                        'task_id'   => $task_id,
                        'name'      => $_FILES['image']['name'][$i],
                        'path'      => $path,
                        'size'      => $_FILES['image']['size'][$i],
                        'mime_type' => $_FILES['image']['type'][$i],
                    );
                    $ret = $task_atch->addNode($node);
                }
            }else if($_FILES['image']['error'][$i] != UPLOAD_ERR_NO_FILE){
                $error[] = $mbs_appenv->lang($_FILES['image']['error'][$i]);
            }
        }
    }
}

?>