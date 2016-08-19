<?php 

mbs_import('', 'CWalletInfoCtr', 'CWalletHistoryCtr', 'CWalletWithdrawApplyCtr', 'CWalletHandle');

mbs_import('user', 'CUserSession');
$usess = new CUserSession();
list($sess_uid,) = $usess->get();

$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
if(empty($error)){
    $amount = CStrTools::currconv($_REQUEST['amount']);
    if($amount < 0){
        $error[] = 'invalid amount';
    }else{
        $wlt_ctr = CWalletInfoCtr::getInstance($mbs_appenv, 
             CDbPool::getInstance(), CMemcachedPool::getInstance());
        $wlt_wdr_ctr = CWalletWithdrawApplyCtr::getInstance($mbs_appenv,
            CDbPool::getInstance(), CMemcachedPool::getInstance());
        $ret = CWalletHandle::withdraw_apply($wlt_ctr, $wlt_wdr_ctr, $sess_uid, $amount, 
            $_REQUEST['account'], $_REQUEST['acc_name']);
        if( $ret != ''){
            $error[] = $mbs_appenv->lang($ret);
        }
    }
}

if(empty($error)) $mbs_appenv->echoex('success');
else $mbs_appenv->echoex(implode(';', $error), 'WALLET_WITHDRAW_ERR');

?>