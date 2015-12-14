<?php 

mbs_import('', 'CMctControl', 'CMctStatusLogControl');
mbs_import('user', 'CUserControl');

if(!isset($_REQUEST['id'])){
	$mbs_appenv->echoex('missing param: id', 'MCT_STATUS_NO_ID');
	exit(0);
}

$mct_ctr = CMctControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
$msl_ctr = CMctStatusLogControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$user_ctr = CUserControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());

if(isset($_REQUEST['action'])){
	$status_opt = array();
	switch ($_REQUEST['action']){
		case 'verify':
			$status_opt = array(
				'passed'   => $mbs_appenv->lang('pass'), 
				'refused'  => $mbs_appenv->lang('refuse')
			);
			break;
	}
	
	if(isset($_REQUEST['reason'])){
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
					$id = intval($_REQUEST['id'][$i]);
					$mct_ctr->setPrimaryKey($id);
					if($mct_ctr->set(array('status'=>$st)) !== false){
						$arr = array(
							'merchant_id' => $_REQUEST['id'][$i],
							'edit_uid'    => $sess_uid,
							'edit_time'   => time(),
							'type'        => $st, 
							'reason'      => $_REQUEST['reason'][$i],
						);
						$msl_ctr->add($arr);
						unset($_REQUEST['id'][$i]);
					}
				}
			}
		}
		if(empty($error)){
			$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '', $mbs_appenv->toURL('list'));
			exit(0);
		}
	}
}else{
	$mid = intval(is_array($_REQUEST['id']) ? $_REQUEST['id'][0] : $_REQUEST['id']);
	$mct_ctr->setPrimaryKey($mid);
	$minfo = $mct_ctr->get();
	if(empty($minfo)){
		$mbs_appenv->echoex('ID: "'.$mid.' "'.$mbs_appenv->lang('not_found'), 'MCT_STATUS_NO_MCT_RECORD');
		exit(0);
	}
	$msl_ctr->setPrimaryKey($mid);
	$count = $msl_ctr->getTotal();
	$slog = array();
	$pid = isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1;
	$num_per_page = $msl_ctr->getDB()->getNumPerPage();
	$page_num_list = array();
	if($count > 0 && $count  > ($pid-1)*$num_per_page){
		$msl_ctr->getDB()->setPageId($pid);
		$slog = $msl_ctr->get();
		
		mbs_import('common', 'CTools');
		$page_num_list = CTools::genPagination($pid, ceil($count/$num_per_page), 8);
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
.st-log ul{margin:0;padding:0;}
.st-log ul li{list-style-type:inherit;border:0;color:#666;padding: 0;}
</style>
</head>
<body>
<div class="warpper">
	<?php if(isset($_REQUEST['action'])){?>
	<div class="ptitle"><?php echo $mbs_appenv->lang($_REQUEST['action'])?>
		<a class=back href="<?php echo $mbs_appenv->toURL('list')?>">&lt;<?php echo $mbs_appenv->lang(array( 'list'))?></a></div>
	<?php if(!empty($error)){ ?>
	<div class=error><?php echo implode('<br/>', $error)?></div>
	<?php } ?>
	
	<form action="" method="post">
	<input type="hidden" name="action" value="<?php echo CStrTools::txt2html($_REQUEST['action'])?>" />
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
					<textarea name="reason[]"></textarea>
				</td>
				<td class=st-log><?php $msl_ctr->setPrimaryKey($row['id']); $log=$msl_ctr->get();
				if(!empty($log)){echo '<ul>';
				$j = 0;
				foreach($log as $l){ 
					$user_ctr->setPrimaryKey($l['edit_uid']);
					$uinfo=$user_ctr->get();
					if(4 == ++$j) break;
				?>
					<li><span style="margin-right:5px;color: <?php echo $mbs_appenv->config(CMctControl::convStatus($l['type']).'.color')?>">
					<?php echo $mbs_appenv->lang(CMctControl::convStatus($l['type']))?></span>
					[<?php echo CStrTools::descTime($l['edit_time'], $mbs_appenv)?>@
					<?php if(empty($uinfo)){echo $l['edit_uid']; }else{ ?>
					<a href="<?php echo $mbs_appenv->toURL('home', 'user', array('id'=>$l['edit_uid']))?>">
						<?php echo $uinfo['name']?></a>]
					<br/>(<?php echo CStrTools::txt2html($l['reason'])?>)
					<?php }?>
					</li>
				<?php }echo '<ul>'; }?>
				</td>
			</tr>
			<?php ++$i;} ?>
		</table>
	</div>
	<div style="margin:10px;">
		<button class="pure-button pure-button-primary">?&nbsp;<?php echo $mbs_appenv->lang($_REQUEST['action'])?></button>
	</div>
	</form>
	<?php }else{ // END isset($_REQUEST['action']) ?>
	<div class="ptitle">"<?php echo CStrTools::txt2html($minfo['name'])?>"<?php echo $mbs_appenv->lang(array('status', 'log'))?>
		<span><?php echo sprintf($mbs_appenv->lang('total_count'), $count)?></span></div>
	<div style="margin:10px;">
		<table class="pure-table pure-table-horizontal">
			<thead><tr><td>#</td>
				<td><?php echo $mbs_appenv->lang('operator')?></td>
				<td><?php echo $mbs_appenv->lang('status')?></td>
				<td><?php echo $mbs_appenv->lang('reason')?></td>
				<td><?php echo $mbs_appenv->lang(array('edit', 'time'))?></td>
			</tr></thead>
			<?php $i=0; foreach($slog as $log){ $user_ctr->setPrimaryKey($log['edit_uid']);$uinfo=$user_ctr->get();?>
			<tr><td><?php echo ++$i;?></td>
				<td><?php if(empty($uinfo)){echo $log['edit_uid']; }else{ ?>
					<a href="<?php echo $mbs_appenv->toURL('home', 'user', array('id'=>$log['edit_uid']))?>">
						<?php echo $uinfo['name']?></a><?php }?></td>
				<td style="color: <?php echo $mbs_appenv->config(CMctControl::convStatus($log['type']).'.color')?>">
					<?php echo $mbs_appenv->lang(CMctControl::convStatus($log['type']))?></td>
				<td><?php echo CStrTools::txt2html($log['reason'])?></td>
				<td><?php echo CStrTools::descTime($log['edit_time'], $mbs_appenv)?></td>
			</tr>
			<?php }?>
		</table>
		
		<?php if(count($page_num_list) > 1){?>
		<div class="pure-menu pure-menu-horizontal page-break">
			<ul class="pure-menu-list">
			<?php if($pid > 1){ ?>
			<li class="pure-menu-item"><a href="<?php echo $mbs_appenv->toURL('status', '', array('id'=>$mid, 'page_id'=>$pid-1)) ?>" 
				class="pure-menu-link" ><?php echo $mbs_appenv->lang('prev_page')?></a></li>
			<?php } ?>
        	<?php foreach($page_num_list as $n => $v){ ?>
        	<li class="pure-menu-item<?php echo $n==$pid?' pure-menu-selected':''?>">
        		<?php if($n==$pid){ echo $v;}else{?>
        		<a href="<?php echo $mbs_appenv->toURL('status', '', array('id'=>$mid, 'page_id'=>$n)) ?>" 
        			class="pure-menu-link"><?php echo $v?></a>
        		<?php }?>
        		</li>
        	<?php }?>
        	<?php if($pid < count($page_num_list)){ ?>
	        <li class="pure-menu-item"><a href="<?php echo $mbs_appenv->toURL('status', '', array('id'=>$mid, 'page_id'=>$pid+1)) ?>" 
	        	class="pure-menu-link" ><?php echo $mbs_appenv->lang('next_page')?></a></li>
	        <?php }?>
	        </ul>
	    </div>
		<?php } ?>
	</div>
	<?php } // END else ?>
	<div class="footer"></div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('global.js')?>"></script>
<script type="text/javascript">
switchRow(document.getElementsByTagName("table")[0], 1, null, "row-onmouseover");
btnlist(document.getElementsByTagName("a"));
</script>
</body>
</html>