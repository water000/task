<?php 

mbs_import('', 'CMctControl');
mbs_import('user', 'CUserControl');
	
$mct_ctr = CMctControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
$list  = $mct_ctr->getDB()->search(array(), array('order'=>'edit_time DESC'));

$user_ctr = CUserControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
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
	<div class="ptitle"><?php echo $mbs_appenv->lang(array('merchant', 'list'))?>
		<a class="pure-button button-success shortcut-a" style="float: right;" href="<?php echo $mbs_appenv->toURL('edit')?>">
			+<?php echo $mbs_appenv->lang(array('add', 'product'))?></a></div>
	<div style="margin:10px;">
		<table class="pure-table pure-table-horizontal">
			<thead><tr><td>#</td><td><?php echo $mbs_appenv->lang('content')?></td>
				<td><?php echo $mbs_cur_moddef->item(CModDef::PAGES, 'edit', CModDef::P_ARGS, 'address', CModDef::G_DC)?></td>
				<td><?php echo $mbs_appenv->lang('owner')?></td>
				<td><?php echo $mbs_appenv->lang(array('edit', 'time'))?></td>
				<td><?php echo $mbs_appenv->lang('status')?></td></tr></thead>
			<?php $i=0; foreach($list as $row){ $user_ctr->setPrimaryKey($row['owner_id']);$uinfo=$user_ctr->get(); ?>
			<tr><td><?php echo ++$i;?></td>
				<td><a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$row['id']))?>">
					<?php echo CStrTools::txt2html($row['name'])?></a><br />
					<?php echo CStrTools::txt2html(CStrTools::cutstr($row['abstract'], 32, $mbs_appenv->item('charset')))?>
					<?php echo empty($row['telephone']) ? '':'<br/>Tel:'.CStrTools::txt2html($row['telephone'])?>
				</td>
				<td><?php echo CStrTools::txt2html($row['area']),'<br/>',CStrTools::txt2html($row['address'])?></td>
				<td><?php echo empty($uinfo) ? 'DELETED' : '<a href="">'.$uinfo['name'].'</a>'?></td>
				<td><?php echo CStrTools::descTime($row['edit_time'], $mbs_appenv)?></td>
				<td style="color: <?php echo $mbs_appenv->config(CMctControl::convStatus($row['status']).'.color')?>">
					<?php echo $mbs_appenv->lang(CMctControl::convStatus($row['status']))?></td></tr>
			<?php } ?>
		</table>
	</div>
	<div style="margin:10px;">
		<button class="pure-button pure-button-primary">?&nbsp;<?php echo $mbs_appenv->lang('verify')?></button>
		<button class="pure-button button-error" style="margin-left: 10px;">-&nbsp;<?php echo $mbs_appenv->lang('delete')?></button>
	</div>
	<div class="footer"></div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('global.js')?>"></script>
<script type="text/javascript">
switchRow(document.getElementsByTagName("table")[0], 1, null, "row-onmouseover");
</script>
</body>
</html>