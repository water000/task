<?php

class CWalletHandle{
    
    static function response($ev, $args){
       global $mbs_appenv;
       
       switch ($ev){
           case 'task.CTaskSubmitCtr.setNode':
               mbs_import('wallet', 'CWalletInfoCtr', 'CWalletHistoryCtr');
               mbs_import('task', 'CTaskSubmitCtr', 'CTaskInfoCtr');
               if('USED' == CTaskSubmitCtr::stconv($args['status'])){
                   $wlt_ctr = CWalletInfoCtr::getInstance($mbs_appenv, 
                        CDbPool::getInstance(), CMemcachedPool::getInstance());
                   $wlt_hty_ctr = CWalletHistoryCtr::getInstance($mbs_appenv, 
                       CDbPool::getInstance(), CMemcachedPool::getInstance());
                   $task_ctr = CTaskInfoCtr::getInstance($mbs_appenv,
                       CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['task_id']);
                   $task = $task_ctr->get();
                   self::transfer($wlt_ctr, $wlt_hty_ctr, 
                       $mbs_appenv->config('sys_payer_uid', 'user'), 
                       $args['uid'], $task['price'], time());
               }
               break;
       }
    }
    
    //@$amount: the param should be convert to sys uint(fen) 
    static function transfer($wlt_ctr, $wlt_hty_ctr, $payer_uid, $payee_uid, $amount, $timestamp){
        $ret = '';

        $conn = $wlt_ctr->getDB()->getConnection();
        $conn->beginTransaction();
        
        try {
            $wlt_ctr->setPrimaryKey($payer_uid);
            $afrows = $wlt_ctr->incr(-$amount);
            if( 0 == $afrows ){
                $ret = 'WALLET_BALANCE_NOT_ENOUGH';
            }else{
                $wlt_ctr->setPrimaryKey($payee_uid);
                if(0 == $wlt_ctr->incr($amount)){
                    $ret = 'WALLET_USER_TRANSER_ERR';
                    $conn->rollBack();
                }else{
                    $arr = array(
                        'a_uid'     => $payer_uid,
                        'b_uid'     => $payee_uid,
                        'amount'    => -$amount,
                        'create_ts' => $timestamp,
                        'type'      => CWalletHistoryCtr::tpconv('TASK_PAY')
                    );
                    $wlt_hty_ctr->addNode($arr);
                    $arr = array(
                        'a_uid'     => $payee_uid,
                        'b_uid'     => $payer_uid,
                        'amount'    => $amount,
                        'create_ts' => $timestamp,
                        'type'      => CWalletHistoryCtr::tpconv('TASK_PAY')
                    );
                    $wlt_hty_ctr->addNode($arr);
                }
            }
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        
        $conn->commit();
        
        return $ret;
    }
    
    static function withdraw_apply($wlt_ctr, $wlt_wdr_ctr, $uid, $amount, $account, $acc_name){
        $ret = '';
        
        $conn = $wlt_ctr->getDB()->getConnection();
        $conn->beginTransaction();
        
        try {
            $wlt_ctr->setPrimaryKey($uid);
            $afrows = $wlt_ctr->incr(-$amount);
            if( 0 == $afrows ){
                $ret = 'WALLET_BALANCE_NOT_ENOUGH';
            }else{
                $arr = array(
                    'uid'          => $uid,
                    'amount'       => $amount,
                    'dest_account' => $account,
                    'account_name' => $acc_name,
                    'submit_ts'    => time(),
                );
                $wlt_wdr_ctr->add($arr);
            }
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        
        $conn->commit();
        
        return $ret;
    }
    
    static function withdraw_batch($uid_list, $wlt_wdr_ctr, $wlt_wdr_bat_ctr){
        $error = array();
        $list = array();
        $batch = array(
            'number'     => 0,
            'submit_ts'  => time(),
            'totoal_qty' => 0,
            'total_amt'  => 0,
        );
        $z = mktime(0, 0, 0);
        $batch['number'] = $z + ceil(($batch['submit_ts'] - $z)/9); // [1 ~ 86400]/9 = [1 ~ 9600]
        $now = date('YmdHis', $batch['submit_ts']);
        
        $conn = $wlt_wdr_ctr->getDB()->getConnection();
        $conn->beginTransaction();
        try {
            foreach($uid_list as $uid){
                $wlt_wdr_ctr->setPrimaryKey($uid);
                $wdr = $wlt_wdr_ctr->get();
                if(empty($wdr)) $error[] = $uid;
                else{
                    $batch['total_amt'] += $wdr['amount'];
                    ++$batch['totoal_qty'];
                    $list[] = array($wdr['dest_account'], $wdr['account_name'],
                        $wdr['amount'], $wdr['type'], $now.$uid);
                    $wlt_wdr_ctr->set(array(
                        'status'    => CWalletWithdrawApplyCtr::stconv('ACCEPTED'),
                        'update_ts' => $batch['submit_ts'],
                    ));
                }
            }
            
            list($code, $msg) = self::ali_withdraw_batch($batch, $list);
            
            $wlt_wdr_bat_ctr->add($batch);
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        
        $conn->commit();
    }
    
    
    static function recharge($wlt_ctr, $wlt_hty_ctr, $uid, $amount){
        $conn = $wlt_ctr->getDB()->getConnection();
        $conn->beginTransaction();
        try {
            $wlt_ctr->setPrimaryKey($uid);
            $wlt_ctr->incr($amount);
            $arr = array(
                'a_uid'     => $uid,
                'amount'    => $amount,
                'create_ts' => time(),
                'type'      => CWalletHistoryCtr::tpconv('RECHARGE')
            );
            $wlt_hty_ctr->addNode($arr);
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        
        $conn->commit();
    }
    
    static function ali_withdraw_batch_req($batch, $list){
        global $mbs_appenv;
        
        require_once __DIR__.'/alipay/alipay_submit.function.php';
        
        $detail_data = '';
        foreach($list as $elem){
            $detail_data += $list[4].'^'.$list[0].'^'.$list[1].'^'
                .CStrTools::currconv(intval($list[2])).'^|';
        }
        
        $alipay_config = $mbs_appenv->config('alipay', 'wallet');
        $parameter = array(
            "service"        => "batch_trans_notify",
            "partner"        => $alipay_config['partner'],
            "notify_url"	 => $alipay_config['notify_url'],
            "email"	         => $alipay_config['pay_email'],
            "account_name"	 => $alipay_config['pay_name'],
            "_input_charset" => $alipay_config['input_charset'],
            "pay_date"	     => $batch['submit_ts'],
            "batch_no"	     => $batch['number'],
            "batch_fee"	     => $batch['total_amt'],
            "batch_num"	     => $batch['totoal_qty'],
            "detail_data"	 => $detail_data,
        );
        
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $ret = $alipaySubmit->buildRequestHttp($parameter);
        
        return array($code, $msg);
    }
    
    static function ali_withdraw_batch_resp($data, $hty_ctr, $wdr_ctr, $wdr_hty_ctr, $wdr_bat_ctr){
        global $mbs_appenv;
        
        $succ_num = $err_num = 0;
        
        $conn = $hty_ctr->getDB()->getConnection();
        $conn->beginTransaction();
        try {
            foreach(explode('|', $data['success_details']) as $str){
                ++$succ_num;
                list($order_id, $acc, $acc_name, $amount, $flag, $reason, $query_id, $time) = explode('^', $str);
                $uid = substr($order_id, 14);
                $wdr_ctr->setPrimaryKey($uid);
                $wdr = $wdr_ctr->get();
                if(empty($wdr)) continue;
                $wdr_ctr->destroy();
            
                $arr = array(
                    'uid'          => $uid,
                    'amount'       => $wdr['amount'],
                    'dest_account' => $wdr['dest_account'],
                    'account_type' => $wdr['account_type'],
                    'submit_ts'    => $wdr['submit_ts'],
                    'notify_ts'    => strtotime($time),
                    'is_succ'      => 1,
                    'batch_no'     => $data['batch_no'],
                    'order_id'     => $order_id,
                    'query_id'     => $query_id,
                );
                $wdr_hty_ctr->addNode($arr);
            
                $arr = array(
                    'a_uid'     => $uid,
                    'amount'    => -$wdr['amount'],
                    'create_ts' => time(),
                    'type'      => CWalletHistoryCtr::tpconv('WITHDRAW')
                );
                $hty_ctr->addNode($arr);
            }
             
            $error_map = $mbs_appenv->lang('alierr', 'wallet');
            
            foreach(explode('|', $data['fail_details']) as $str){
                ++$err_num;
                list($order_id, $acc, $acc_name, $amount, $flag, $reason, $query_id, $time) = explode('^', $str);
                $uid = substr($order_id, 14);
                $wdr_ctr->setPrimaryKey($uid);
                $wdr = $wdr_ctr->get();
                if(empty($wdr)) continue;
                $wdr_ctr->destroy();
            
                $fault_msg = isset($error_map[$reason]) ? $error_map[$reason] : $reason;
                $arr = array(
                    'uid'          => $uid,
                    'amount'       => $wdr['amount'],
                    'dest_account' => $wdr['dest_account'],
                    'account_type' => $wdr['account_type'],
                    'submit_ts'    => $wdr['submit_ts'],
                    'notify_ts'    => strtotime($time),
                    'is_succ'      => 0,
                    'fault_msg'    => $fault_msg,
                    'batch_no'     => $data['batch_no'],
                    'order_id'     => $order_id,
                    'query_id'     => $query_id,
                );
                $wdr_hty_ctr->addNode($arr);
            
                $arr = array(
                    'a_uid'     => $uid,
                    'amount'    => -$wdr['amount'],
                    'create_ts' => time(),
                    'type'      => CWalletHistoryCtr::tpconv('WITHDRAW'),
                    'msg'       => $fault_msg,
                );
                $hty_ctr->addNode($arr);
            }
            
            $wdr_bat_ctr->setPrimaryKey($data['batch_no']);
            $wdr_bat_ctr->set(array(
                'success_qty' => $succ_num,
                'fault_qty'   => $err_num,
                'notify_ts'  => strtotime($data['notify_time']),
            ));
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        
        $conn->commit();
    }
}

?>