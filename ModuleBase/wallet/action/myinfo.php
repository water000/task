<?php 

mbs_import('', 'CWalletInfoCtr', 'CWalletWithdrawApplyCtr');

mbs_import('user', 'CUserSession');
$usess = new CUserSession();
list($sess_uid,) = $usess->get();

$wlt_ctr = CWalletInfoCtr::getInstance($mbs_appenv, 
    CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);
$info = $wlt_ctr->get();
if(empty($info)){
    $info =array(
        'uid'             => $sess_uid,
        'amount'          => 0,
        'change_ts'       => time(),
    );
    $wlt_ctr->add($info);
}else{
    $info['amount'] = CStrTools::currconv(intval($info['amount']));
    
    $wltwda_ctr = CWalletWithdrawApplyCtr::getInstance($mbs_appenv,
        CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);
    $wda = $wltwda_ctr->get();
    if(!empty($wda)){
        $info['withdraw_amount'] = CStrTools::currconv(intval($wda['amount']));  
    }
}
$info['withdraw_amount'] = isset($info['withdraw_amount']) ? $info['withdraw_amount'] : 0;
$mbs_appenv->echoex($info);

?>