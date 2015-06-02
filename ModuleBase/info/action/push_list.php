<?php 

$list = array(
	array('id'=>1, 'title'=>'this is title', 'abstract'=>'this is abstract', 'format'=>'TXT', 'path'=>'', 'creator_id'=>1, 'dep_id'=>1),
	array('id'=>2, 'title'=>'这是标题', 'abstract'=>'这是概要', 'format'=>'VDO', 'path'=>'', 'creator_id'=>2, 'dep_id'=>2),
);

$mbs_appenv->echoex(array('list' => $list), '');

?>