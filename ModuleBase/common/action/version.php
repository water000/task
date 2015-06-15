<?php 

$output = array(
	'version_id'      => 2,
	'APP_URL'         => $mbs_appenv->uploadURL('info.apk'),
	'version_content' => '消息快报提供了编辑、推送的功能',
);

$mbs_appenv->echoex($output);

?>