<?php 

if(!isset($_REQUEST['id'])){
	$mbs_appenv->echoex('missing param', 'INFO_MISS');
	exit(0);
}

mbs_import('', 'CInfoControl');
mbs_import('info_push','CInfoPushControl');

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
if(empty($ret) || !($ret = $ret->fetchAll(PDO::FETCH_ASSOC))){
	$mbs_appenv->echoex('no such info', 'INFO_NOT_FOUND');
	exit(0);
}

$info_push_ctr->setPrimaryKey($ret[0]['pusher_uid']);
$info_push_ctr->setSecondKey($ret[0]['id']);
$info_push_ctr->setNode(array(
	'status'=>CInfoPushControl::ST_HAD_READ, 
	'request_time'=>time(),
));
$info = $info_ctr->get();


?>
<!doctype html>
<html>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">
<style type="text/css">
.win{padding:2% 1%; color:#333;font-size:1.5em;}
.title{text-align:center;font-size:130%;padding:0;margin:0;}
.date{text-align:center;color:#888;border-bottom:1px solid #ccc;padding: 10px 0;margin:10px 0 20px;}
.content{padding:1%;color:#555;}
video, img{width:100%;}
</style>
<link href="/static/css/video-js.css" rel="stylesheet" />
</head>
<body>
	<div class=win>
		<h1 class=title><?php echo CStrTools::txt2html($info['title'])?></h1>
		<div class=date><?php echo $mbs_appenv->lang('site_name'), '&nbsp;', date('Y-m-d H:i:s', $info['create_time'])?></div>
		<div>
		<?php if($info['attachment_format'] == CInfoControl::AT_VDO){?>
		<!-- script src="/static/js/video-min.js"></script>
		<video preload="auto" controls="controls" id="really-cool-video" class="video-js vjs-default-skin" data-setup='{}' -->
		<video preload="auto" controls="controls">
			<source src="<?php echo $mbs_appenv->uploadURL($info['attachment_path'], '', 'http://'.$mbs_appenv->config('download_file_host'))?>" type="video/<?=pathinfo($info['attachment_name'], PATHINFO_EXTENSION )?>"></source>
			unsupport video format
		</video>
		<script type="text/javascript">
// 		var player = videojs('really-cool-video', { /* Options */ }, function() {
// 			  console.log('Good to go!');
// 			  this.play(); 
// 			});
		</script>
		<?php }else if($info['attachment_format'] == CInfoControl::AT_IMG){ ?>
		<img src="<?php echo $mbs_appenv->uploadURL($info['attachment_path'], '', 'http://'.$mbs_appenv->config('download_file_host'))?>" />
		<?php } ?>
		</div>
		<div class=content><?php echo $info['abstract']?></div>
	</div>
</body>
</html>
