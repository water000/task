<?php 

mbs_import('', 'CWalletHistoryCtr', 'CWalletWithdrawApplyCtr');

mbs_import('user', 'CUserSession');
$usess = new CUserSession();
list($sess_uid,) = $usess->get();

$page_id = 1;
if(isset($_REQUEST['page_id'])){
    $page_id = intval($_REQUEST['page_id']);
    $page_id = $page_id > 0 ? $page_id : 1;
}

$wlthty_ctr = CWalletHistoryCtr::getInstance($mbs_appenv,
    CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);
$wlthty_ctr->setPageId($page_id);
$list = $wlthty_ctr->get();
foreach($list as &$row){
    $tp = CWalletHistoryCtr::tpconv($row['type']);
    $row['amount'] = CStrTools::currconv(intval($row['amount']));
    $row['title'] = $tp !== false ? sprintf($mbs_appenv->lang($tp), $row['msg']) : $row['type'];
    $row['msg'] = '';
}

if(1 == $page_id){
    $wltwda_ctr = CWalletWithdrawApplyCtr::getInstance($mbs_appenv, 
        CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);
    $wda = $wltwda_ctr->get();
    if(!empty($wda) && ($st=CWalletWithdrawApplyCtr::stconv($wda['status']))!='SUCCEEDED'){
        array_unshift($list, array(
            'amount'   => CStrTools::currconv(-intval($wda['amount'])),
            'title'    => sprintf($mbs_appenv->lang('WITHDRAW'), ''),
            'msg'      => $mbs_appenv->lang($st).('FAILED' == $st ? '('.$wda['fault_msg'].')':''),
            'create_ts'=> $wda['submit_ts'],
        ));
    }
}

$mbs_appenv->echoex(array('list'=>$list, 'has_more'=>count($list)==$wlthty_ctr->getDB()->getNumPerPage()));

?>