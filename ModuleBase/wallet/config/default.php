<?php
$default=array(
    'alipay' => array(
        'partner'       => '',
        'key'           => '',
        'pay_email'     => '',
        'pay_name'      => '',
        'cacert'        => $mbs_appenv->getDir('wallet', CAppEnv::FT_CLASS).'alipay/cacert.pem',
        'input_charset' => 'utf-8',
        'transport'     => 'https',
        'sign_type'     => 'MD5',       
        'notify_url'    => $mbs_appenv->toURL('alipay_batch_withdraw_notify', 'wallet'),
        
    ),
);
?>