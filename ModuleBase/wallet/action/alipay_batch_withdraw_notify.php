<?php

require_once $mbs_appenv->getDir('wallet', CAppEnv::FT_CLASS).'/alipay/alipay_notify_class.php';

$alipayNotify = new AlipayNotify($mbs_appenv->config('alipay'));
$verify_succ = $alipayNotify->verifyNotify();

if($verify_succ){
    
    mbs_import('', 'CWallet', 'CWalletHistoryCtr', 'CWalletWithdrawApplyCtr', 
        'CWalletWithdrawHistoryCtr', 'CWalletWithdrawBatchCtr');
    
    $hty_ctr = CWalletHistoryCtr::getInstance($mbs_appenv, 
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    $wdr_ctr = CWalletWithdrawApplyCtr::getInstance($mbs_appenv, 
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    $wdr_hty_ctr = CWalletWithdrawHistoryCtr::getInstance($mbs_appenv,
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    $wdr_bat_ctr = CWalletWithdrawBatchCtr::getInstance($mbs_appenv, 
        CDbPool::getInstance(), CMemcachedPool::getInstance());
    
    CWalletHandle::ali_withdraw_batch_resp($_POST, $hty_ctr, $wdr_ctr, $wdr_hty_ctr, $wdr_bat_ctr);
    
    echo "success";
    
}else{
    echo "fail";
}

?>