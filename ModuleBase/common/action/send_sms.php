<?php 

$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
if(!empty($error)){
    $mbs_appenv->echoex(implode(';', $error), 'INVALID_PARAM');
    exit(1);
}

if(isset($_REQUEST['for'])){
    $for_path = $mbs_appenv->url2path($_REQUEST['for']);
    if(empty($for_path) || !file_exists($for_path)){
        $mbs_appenv->echoex('invalid param: for', 'SEND_SMS_INVALID_PARAM');
        exit(1);
    }
    if(! (require $for_path)){
        exit(1);
    }
}

switch ($_REQUEST['type']){
    case 'captcha':
        mbs_import('common', 'CSMSCaptcha');
        $captcha = mt_rand(100000, 999999);
        $smscap = new CSMSCaptcha(CDbPool::getInstance());
        $ret = $smscap->create($_REQUEST['phone'], $captcha, $_REQUEST['cap_group']);
        if(empty($ret)){
            $err = mbs_sendmessage($_REQUEST['phone'], $mbs_appenv->lang('captcha_title'), 
                sprintf($mbs_appenv->lang('captcha_body'),$captcha));
            if(empty($err))
                $mbs_appenv->echoex(null);
            else{
                trigger_error('send_sms_error: '.$err, E_USER_WARNING);
                $mbs_appenv->echoex('sys error', 'COMMON_SEND_SMS_ERR');
            }
        }else{
            $mbs_appenv->echoex($mbs_appenv->lang($ret), $ret);
        }
        break;
}

function mbs_sendmessage($to_set, $subject_set, $body_set) {
    $data_str = 'to_set='.$to_set.'&subject_set='.$subject_set.'&body_set='.$body_set.'&type=1';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,'http://153.3.217.251:11024/');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $server_output = curl_exec ($ch);
    $ret = $server_output === false ? curl_error($ch) : '';
    curl_close ($ch);
    return $ret;
}



?>