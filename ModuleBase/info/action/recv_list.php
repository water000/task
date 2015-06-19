<?php 

define('ROWS_PER_PAGE', isset($_REQUEST['per_page']) ? intval($_REQUEST['per_page']) : 20);
define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);

mbs_import('user', 'CUserSession');
$usersess = new CUserSession();
list($sess_uid,) = $usersess->get();

mbs_import('', 'CInfoControl', 'CInfoPushControl');
$type = isset($_REQUEST['type']) ? CInfoControl::txt2type($_REQUEST['type']) : false;
$sql = sprintf('SELECT i.* FROM %s i, %s p 
		WHERE i.id=p.info_id AND p.recv_uid=%d AND status=%d ORDER BY i.id desc %s LIMIT %d,%d',
		mbs_tbname('info'), 
		mbs_tbname('info_push_event'),
		$sess_uid, 
		CInfoPushControl::ST_WAIT_PUSH,
		false === $type ? '' : 'AND attachment_format='.$type,
		ROWS_OFFSET, 
		ROWS_PER_PAGE
);
$pdoconn = CDbPool::getInstance()->getDefaultConnection();
$pdos = $pdoconn->query($sql);
$pdos->setFetchMode(PDO::FETCH_ASSOC);
$ret = array();
$k = -1;
foreach($pdos as $k=> $row){
	$row['attachment_format'] = CInfoControl::type2txt($row['attachment_format']);
	$ret[] = $row;
}

$page_id = $k != -1 && $k+1 == ROWS_PER_PAGE ? PAGE_ID + 1 : 0;

$mbs_appenv->echoex(array('list' => $ret, 'page_id'=>$page_id), '');

?>