<?php 

mbs_import('', 'CProductControl');
	
$pdt_ctr = CProductControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
$list  = $pdt_ctr->getDB()->search(array(), array('order'=>'edit_time DESC'));
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
	<div class="ptitle"><?php echo $mbs_appenv->lang(array('product', 'list'))?>
		<a class="pure-button button-success shortcut-a" style="float: right;" href="<?php echo $mbs_appenv->toURL('edit')?>">
			+<?php echo $mbs_appenv->lang(array('add', 'product'))?></a></div>
	<div style="margin:10px;">
		<table class="pure-table pure-table-horizontal">
			<thead><tr><td>#</td><td><?php echo $mbs_appenv->lang('content')?></td>
				<td><?php echo $mbs_appenv->lang(array('attr', 'relate'))?></td>
				<td><?php echo $mbs_appenv->lang(array('edit', 'time'))?></td></tr></thead>
			<?php $i=0; foreach($list as $row){ ?>
			<tr><td><?php echo ++$i;?></td>
				<td><img class=logo-img onclick="this.className=''" src="<?php echo CProductControl::logourl($row['logo_path'], $mbs_appenv)?>" />
					<a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$row['id']))?>">
					<?php echo $row['name'], '/', $row['en_name']?></a>
					(<?php echo CStrTools::cutstr($row['abstract'], 32, $mbs_appenv->item('charset'))?>)</td>
				<td><a href="<?php echo $mbs_appenv->toURL('attr_list', '', array('product_id'=>$row['id']))?>">
					<?php echo $mbs_appenv->lang('relate')?></a></td>
				<td><?php echo CStrTools::descTime($row['edit_time'], $mbs_appenv)?></td></tr>
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