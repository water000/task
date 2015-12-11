<?php 

mbs_import('', 'CMctControl');
mbs_import('user', 'CUserControl');
	
$mct_ctr = CMctControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());

$user_ctr = CUserControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());


if(!isset($_REQUEST['action'])){
	$mbs_appenv->echoex('missing param: action', 'MCT_STATUS_NO_ACTION', $mbs_appenv->toURL('list'));
	exit(0);
}

$status_opt = array();
switch ($_REQUEST['action']){
	case 'verify':
		$status_opt = array(
			'passed'   => $mbs_appenv->lang('pass'), 
			'refused'  => $mbs_appenv->lang('refuse')
		);
		break;
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet"> 
<style type="text/css">
textarea{vertical-align:bottom;}

</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang($_REQUEST['action'])?>
		<a class=back href="<?php echo $mbs_appenv->toURL('list')?>">&lt;<?php echo $mbs_appenv->lang(array( 'list'))?></a></div>
	<form action="" method="post">
	<div style="margin:10px;">
		<table class="pure-table pure-table-horizontal">
			<thead><tr><td>#</td>
				<td><?php echo $mbs_appenv->lang('content')?></td>
				<td><?php echo $mbs_appenv->lang('status')?></td>
				<td><?php echo $mbs_appenv->lang(array('last', 'verify'))?></td>
				<td><?php echo $mbs_appenv->lang('edit')?></td>
			</tr></thead>
			<?php 
			$i=0; 
			foreach($_REQUEST['id'] as $id){ 
				$mct_ctr->setPrimaryKey(intval($id));
				$row=$mct_ctr->get();
				if(empty($row)){ 
			?>
			<tr><td colspan=5>ID: <?php echo $id, $mbs_appenv->lang('not_found')?></td></tr>
			<?php continue;}?>
			<tr><td><?php echo ++$i;?></td>
				<td><a href="<?php echo $mbs_appenv->toURL('home', '', array('id'=>$row['id']))?>">
					<?php echo CStrTools::txt2html($row['name'])?></a><br />
					<?php echo CStrTools::txt2html($row['area']),'/', CStrTools::txt2html($row['address'])?>
					<?php echo empty($row['telephone']) ? '':'<br/>Tel:'.CStrTools::txt2html($row['telephone'])?>
				</td>
				<td style="color: <?php echo $mbs_appenv->config(CMctControl::convStatus($row['status']).'.color')?>">
					<?php echo $mbs_appenv->lang(CMctControl::convStatus($row['status']))?></td>
				<td></td>
				<td><?php foreach($status_opt as $k=>$v){?>
					<a class="pure-button pure-button-check" name="status" _value="<?php echo $k?>"><?php echo $v?></a>
					<?php }?>
					<textarea name="desc"></textarea>
				</td>
			</tr>
			<?php } ?>
		</table>
	</div>
	<div style="margin:10px;">
		<button class="pure-button pure-button-primary">?&nbsp;<?php echo $mbs_appenv->lang($_REQUEST['action'])?></button>
	</div>
	</form>
	<div class="footer"></div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('global.js')?>"></script>
<script type="text/javascript">
switchRow(document.getElementsByTagName("table")[0], 1, null, "row-onmouseover");
btnlist(document.getElementsByTagName("a"));
</script>
</body>
</html>