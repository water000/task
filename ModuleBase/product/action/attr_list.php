<?php 

mbs_import('', 'CProductControl', 'CProductAttrControl');
	
$pdtattr_ctr = CProductAttrControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
$list  = $pdtattr_ctr->getDB()->search(array(), array('order'=>'last_edit_time DESC'));
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet"> 
<style type="text/css">
.logo-img{height:20px;vertical-align: middle;}
.pure-table-horizontal{border-left:0;border-right:0;}
.row-onmouseover{background-color:#E3EEFB;}
.pure-button-checked{background-color: #F79F75;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array('attr', 'list'))?>
		<a class="pure-button button-success shortcut-a" style="float: right;" href="<?php echo $mbs_appenv->toURL('attr_edit')?>">
			+<?php echo $mbs_appenv->lang(array('add', 'attr'))?></a></div>
	<div style="margin:10px;">
		<table class="pure-table pure-table-horizontal">
			<thead><tr><td>#</td><td><?php echo $mbs_appenv->lang('content')?></td>
				<td><?php echo $mbs_cur_moddef->item(CModDef::PAGES, 'attr_edit', CModDef::P_ARGS, 'value_type', CModDef::G_TL)?></td>
				<td><?php echo $mbs_cur_moddef->item(CModDef::PAGES, 'attr_edit', CModDef::P_ARGS, 'unit_or_size', CModDef::G_TL)?></td>
				<td><?php echo $mbs_cur_moddef->item(CModDef::PAGES, 'attr_edit', CModDef::P_ARGS, 'value_opts', CModDef::G_TL)?></td>
				<td><?php echo $mbs_appenv->lang(array('last', 'edit', 'time'))?></td></tr></thead>
			<?php $i=0; foreach($list as $row){ ?>
			<tr><td><?php echo ++$i;?></td>
				<td><a href="<?php echo $mbs_appenv->toURL('attr_edit', '', array('id'=>$row['id']))?>">
					<?php echo $row['name'], '/', $row['en_name']?></a>
					(<?php echo CStrTools::cutstr($row['abstract'], 32, $mbs_appenv->item('charset'))?>)</td>
				<td><?php echo CProductAttrControl::vtmap($row['value_type'])?></td>
				<td><?php echo $row['unit_or_size']?></td>
				<td><?php echo $row['value_opts'], $row['allow_multi']?'<span class=pure-button-checked>'.$mbs_appenv->lang('allow_multi').'</span>':''?></td>
				<td><?php echo CStrTools::descTime($row['last_edit_time'], $mbs_appenv)?></td></tr>
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