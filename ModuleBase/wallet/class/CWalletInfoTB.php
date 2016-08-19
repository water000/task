<?php

class CWalletInfoTB extends CUniqRowOfTable{
    function incr($amount){
        $sql = sprintf('UPDATE %s SET amount = amount+%d %s WHERE uid=%d AND %s ',
            $this->tbname, 
            $amount,
            $amount > 0 ? sprintf(', history_amount = history_amount+%d', $amount) : '',
            $this->primaryKey, 
            $amount<0 ? sprintf('amount>%d', -$amount) : '1');
        return $this->oPdoConn->exec($sql);
    }
}

?>