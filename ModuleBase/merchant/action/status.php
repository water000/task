<?php 

mbs_import('', 'CMctControl', 'CMctStatusLogControl');
mbs_import('user', 'CUserControl');

$mct_ctr = CMctControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
$msl_ctr = CMctControl::getInstance($mbs_appenv,
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

if(isset($_REQUEST['submit'])){
	mbs_import('user', 'CUserSession');
	$usess = new CUserSession();
	list($sess_uid,) = $usess->get();
	$error = array();
	for($i=0, $j=count($_REQUEST['id']); $i<$j; ++$i){
		if(isset($_REQUEST['status'.$i])){
			$st = CMctControl::convStatus($_REQUEST['status'.$i]);
			if(false === $st){
				$error[] = 'invalid status, ID: '.$_REQUEST['id'][$i];
			}else{
				$arr = array(
					'merchant_id' => $_REQUEST['id'][$i],
					'edit_uid'    => $sess_uid,
					'edit_time'   => time(),
					'type'        => $st, 
					'desc'        => $_REQUEST['desc'][$i],
				);
				$msl_ctr->add($arr);
				unset($_REQUEST['id'][$i]);
			}
		}
	}
	if(!empty($error)){
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '', $mbs_appenv->toURL('list'));
		exit(0);
	}
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
				<td><?php echo $mbs_appenv->lang('edit')?></td>
				<td><?php echo $mbs_appenv->lang(array('last', 'verify'))?></td>
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
			<tr><td><?php echo $i+1;?><input type="hidden" name="id[]" value="<?php echo $row['id']?>" /></td>
				<td><a href="<?php echo $mbs_appenv->toURL('home', '', array('id'=>$row['id']))?>">
					<?php echo CStrTools::txt2html($row['name'])?></a><br />
					<?php echo CStrTools::txt2html($row['area']),'/', CStrTools::txt2html($row['address'])?>
					<?php echo empty($row['telephone']) ? '':'<br/>Tel:'.CStrTools::txt2html($row['telephone'])?>
				</td>
				<td style="color: <?php echo $mbs_appenv->config(CMctControl::convStatus($row['status']).'.color')?>">
					<?php echo $mbs_appenv->lang(CMctControl::convStatus($row['status']))?></td>
				<td><?php foreach($status_opt as $k=>$v){?>
					<a class="pure-button pure-button-check" name="status<?php echo $i?>" _value="<?php echo $k?>"><?php echo $v?></a>
					<?php }?>
					<textarea name="desc[]"></textarea>
				</td>
				<td><?php $msl_ctr->setPrimaryKey($row['id']); $log=$msl_ctr->get();
				if(!empty($log)){echo '<ul>';
				foreach($log as $l){ 
					$user_ctr->setPrimaryKey($l['edit_uid']);
					$uinfo=$user_ctr->get();
				?>
					<li><span style="margin-right:5px;color: <?php echo $mbs_appenv->config(CMctControl::convStatus($row['status']).'.color')?>">
					<?php echo $mbs_appenv->lang(CMctControl::convStatus($l['type']))?></span>
					[<?php echo CStrTools::descTime($l['edit_time'], $mbs_appenv)?>@
					<?php if(empty($uinfo)){echo $l['edit_uid']; }else{ ?>
					<a href="<?php echo $mbs_appenv->toURL('home', 'user', array('id'=>$l['edit_uid']))?>">
						<?php echo $uinfo['name']?></a>]
					<br/>(<?php echo CStrTools::txt2html($l['desc'])?>)
					<?php }?>
					</li>
				<?php }echo '<ul>'; }?>
				</td>
			</tr>
			<?php ++$i;} ?>
		</table>
	</div>
	<div style="margin:10px;">
		<button class="pure-button pure-button-primary" name="submit" value="1">?&nbsp;<?php echo $mbs_appenv->lang($_REQUEST['action'])?></button>
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