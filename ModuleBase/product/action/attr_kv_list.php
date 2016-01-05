<?php 
mbs_import('', 'CProductAttrKVControl');

$attr_kv_ctr = CProductAttrKVControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$keys = $attr_kv_ctr->keys();

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet"> 
<style type="text/css">
.logo-img{height:20px;vertical-align: middle;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array('attr', 'kv', 'list'))?>
		<a class="pure-button button-success shortcut-a" style="float: right;" href="<?php echo $mbs_appenv->toURL('attr_kv_edit')?>">
			+<?php echo $mbs_appenv->lang(array('add', 'kv'))?></a></div>
	<div style="margin:10px;">
		<table class="pure-table pure-table-horizontal">
			<thead><tr><td>#</td><td><?php echo $mbs_appenv->lang('kv')?></td>
				<td><?php echo $mbs_appenv->lang('operate')?></td></tr></thead>
			<?php $i=0; foreach($keys as $row){ $attr_kv_ctr->setPrimaryKey($row['id']); $vs = $attr_kv_ctr->get(); ?>
			<tr><td><?php echo ++$i;?></td>
				<td><?php echo CStrTools::txt2html($row['value'])?>
				(<?php foreach($vs as $v){ echo CStrTools::txt2html($v['value']), ';'; }?>)</td>
				<td><a href="<?php echo $mbs_appenv->toURL('attr_kv_edit', '', array('kid'=>$row['id']))?>">
					<?php echo $mbs_appenv->lang('operate')?></a></td></tr>
			<?php } ?>
		</table>
	</div>
	<div class="footer"></div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('global.js')?>"></script>
<script type="text/javascript">
switchRow(document.getElementsByTagName("table")[0], 1, null, "row-onmouseover");
</script>
</body>
</html>