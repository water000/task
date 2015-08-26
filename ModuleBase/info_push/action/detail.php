<?php 

if(!isset($_REQUEST['id'])){
	$mbs_appenv->echoex('missing param', 'INFO_MISS');
	exit(0);
}

mbs_import('', 'CInfoControl', 'CInfoPushControl');

$info_ctr = CInfoControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['id']);
$info_push_ctr = CInfoPushControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());

mbs_import('user', 'CUserSession');
$usersess = new CUserSession();
list($sess_uid,) = $usersess->get();

$search_keys = array(
	'recv_uid' => $sess_uid,
	'status'   => CInfoPushControl::ST_WAIT_PUSH,
	'info_id'  => intval($_REQUEST['id'])
);
$ret = $info_push_ctr->getDB()->search($search_keys);
// if(empty($ret) || !($ret = $ret->fetchAll(PDO::FETCH_ASSOC))){
// 	$mbs_appenv->echoex('no such info', 'INFO_NOT_FOUND');
// 	exit(0);
// }

$info_push_ctr->setPrimaryKey($ret[0]['pusher_uid']);
$info_push_ctr->setSecondKey($ret[0]['id']);
$info_push_ctr->setNode(array(
	'status'=>CInfoPushControl::ST_HAD_READ, 
	'request_time'=>time(),
));
$info = $info_ctr->get();

mbs_import('', 'CInfoPushStatControl');
$info_push_stat = CInfoPushStatControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$info_push_stat->setPrimaryKey(0);
$info_push_stat->getDB()->incrDup(array(
	'read_count'     => '1',
));
$info_push_stat->setPrimaryKey($info['id']);
$info_push_stat->getDB()->incrDup(array(
	'read_count'     => '1',
));
?>
<!doctype html>
<html>
<head>
<style type="text/css">
.win{padding:2% 1%; color:#333;font-size:1.5em;}
h1{text-align:center;font-size:130%;padding:0;margin:0;}
.date{text-align:center;color:#888;border-bottom:1px solid #ccc;padding: 10px 0;margin:10px 0 20px;}
p{padding:1%;color:#555;}
video, img{width:100%;}
</style>
</head>
<body>
	<div class=win>
		<h1><?php echo CStrTools::txt2html($info['title'])?></h1>
		<div class=date><?php echo $mbs_appenv->lang('site_name'), '&nbsp;', date('Y-m-d H:i:s', $info['create_time'])?></div>
		<div>
		<?php if($info['attachment_format'] == CInfoControl::AT_VDO){?>
		<video preload="preload" controls="controls">
			<source src="<?php echo $mbs_appenv->uploadURL($info['attachment_path'])?>" type="video/<?=pathinfo($info['attachment_name'], PATHINFO_EXTENSION )?>"></source>
			unsupport video format
		</video>
		<?php }else if($info['attachment_format'] == CInfoControl::AT_IMG){ ?>
		<img src="<?php echo $mbs_appenv->uploadURL($info['attachment_path'], '', $_SERVER['HTTP_HOST'])?>" />
		<?php } ?>
		</div>
		<p><?php echo CStrTools::txt2html($info['abstract'])?></p>
	</div>
</body>
</html>
