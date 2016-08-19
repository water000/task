<?php 

mbs_import('', 'CUserInfoCtr', 'CUserSession');
mbs_import('common', 'CImage');

$usess = new CUserSession();
list($sess_uid, ) = $usess->get();

$def = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
$user_ctr = CUserInfoCtr::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);
$info = $user_ctr->get();

$img_thumb = new CImage(CUserInfoCtr::AVATAR_SUBDIR);

$output = array();
if(isset($_REQUEST['career_cid'])){
    $error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
    if(empty($error)){
        if(isset($_FILES['avatar'])){
            if(UPLOAD_ERR_OK == $_FILES['avatar']['error']){
                $path = $img_thumb->thumbnailEx($_FILES['avatar']['tmp_name'], $mbs_appenv->uploadPath(''));
                if(false === $path){
                    $error['avatar'] = 'sys error(thumbnail)';
                }else{
                    $def['avatar_path'] = '';
                    $_REQUEST['avatar_path'] = $path;
                }
            }else if($_FILES['avatar']['error'] != UPLOAD_ERR_NO_FILE){
                $error['avatar'] = $mbs_appenv->lang($_FILES['avatar']['error']);
            }
        }
        
        unset($def['avatar']);
        $diff = array_diff_assoc(array_intersect_key($_REQUEST, $def), $info);
        if(!empty($diff)){
            if(isset($diff['gender']) && !isset($mbs_appenv->config('gender_list')[$diff['gender']]))
                $error['gender'] = 'invalid gender';
            if(isset($diff['career_cid']) && !isset($mbs_appenv->config('career_list')[$diff['career_cid']]))
                $error['career_cid'] = 'invalid career_cid';
            if(empty($error)){
                try {
                    $info = $diff + $info;
                    $ret = $user_ctr->set($info);
                    if(!empty($info['avatar_path'])){
                        $img_thumb->remove($mbs_appenv->uploadPath(''), $info['avatar_path']);
                    }
                } catch (Exception $e) {
                    $img_thumb->remove($mbs_appenv->uploadPath(''), $info['avatar_path']);
                    if($mbs_appenv->config('PDO_ER_DUP_ENTRY', 'common') == $e->getCode()){
                        if(strpos($e->getMessage(), 'phone') !== false){
                            $error['phone'] = sprintf('"%s" %s', $info['phone'], $mbs_appenv->lang('existed'));
                        }else{
                            $error['email'] = sprintf('"%s" %s', $info['email'], $mbs_appenv->lang('existed'));
                        }
                    }else{
                        throw $e; // throw the exception to the system handler
                    }
                }
            }
        }
    }
    if(!empty($error)){
        $mbs_appenv->echoex(implode(';', $error), 'USER_MYINFO_ERROR');
        exit(1);
    }
}
else $output = array(
    'gender_list'  => $mbs_appenv->config('gender_list'), 
    'career_list'  => $mbs_appenv->config('career_list')
);
if(!empty($info['avatar_path'])) 
    $info['avatar_path'] = $mbs_appenv->uploadURL($img_thumb->completePath($info['avatar_path']));
    
$output['userinfo'] = $info;
$mbs_appenv->echoex($output);


?>