<?php

mbs_import('privilege', 'CPrivGroupControl');
$priv_group = CPrivGroupControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$all = $priv_group->getDB()->listAll()->fetchAll();

$modified = false;
if(isset($_REQUEST['modify']) || isset($_REQUEST['del'])){
	foreach($all as $row){
		$priv_list = CPrivGroupControl::decodePrivList($row['priv_list']);
		if(!CPrivGroupControl::isTopmost($priv_list)){
			foreach($priv_list as $mod => &$actions){
				if(isset($_REQUEST['del'][$mod])){
					$actions = array_diff($actions, $_REQUEST['del'][$mod]);
				}
				if(isset($_REQUEST['modify'][$mod])){
					foreach($actions as &$ac){
						if(isset($_REQUEST['modify'][$mod][$ac]) && 
							!empty($_REQUEST['modify'][$mod][$ac])){
							$ac = $_REQUEST['modify'][$mod][$ac];
						}
					}
				}
			}
			$npriv_list = CPrivGroupControl::encodePrivList($priv_list);
			if($npriv_list != $row['priv_list']){
				$priv_group->setPrimaryKey($row['id']);
				$priv_group->set(array('priv_list'=>$npriv_list));
				$modified = true;
			}
		}
	}
}

$mod_items = array();
foreach($all as $row){
	$priv_list = CPrivGroupControl::decodePrivList($row['priv_list']);
	if(!CPrivGroupControl::isTopmost($priv_list)){
		foreach($priv_list as $mod => $actions){
			if(isset($mod_items[$mod])){
				$mod_items[$mod] = array_merge($mod_items[$mod], $actions);
			}else{
				$mod_items[$mod] = $actions;
			}
		}
	}
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<!--[if lt ie 9]>
<script>
	document.createElement("article");
	document.createElement("section");
	document.createElement("aside");
	document.createElement("footer");
	document.createElement("header");
	document.createElement("nav");
</script>
<![endif]-->
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('allInfo.css')?>">
<style type="text/css">
.col1{width:60px;}
.col2{width:185px;}
.col3{width:285px;}
.col4{width:520px;}
.name{font-size:14px; color:#111;}
.dep-desc{margin:22px 8px 0;;font-size:14px;}
.dep-desc span{color:rgb(0,67,144);margin-right:20px;}
.dep-desc i{margin-right:5px;position:inherit;left:0;right:0;display:inline-block;width:16px;vertical-align:middle;}
.ico-dep{background-position:-3px -236px}
.ico-mbr{background-position:-4px -216px}
</style>
</head>
<body>
<div class="allInfo">
<<<<<<< HEAD
	<h2 class="tit"><?php echo $mbs_appenv->lang('group_list')?></h2>
=======
	<h2 class="tit"><?php echo $mbs_cur_moddef->item(CModDef::PAGES, $mbs_appenv->item('cur_action'), CModDef::P_TLE)?></h2>
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d
	
	<?php if(!empty($error)){ ?>
	<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
	<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
	</div>
	<?php }else if($modified){ ?>
	<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
	</div>
	<?php }?>
	
	<form name=_form action="" method="post" >
			<table cellspacing=0 class=info-table style="margin-top: 26px">
				<thead><tr>
<<<<<<< HEAD
					<th>MODULE</th>
					<th>NOT FOUND</th>
					<th>CHANGE TO</th>
					<th>DELETE</th>
=======
					<th><?php echo $mbs_appenv->lang('module')?></th>
					<th><?php echo $mbs_appenv->lang('not_found')?></th>
					<th><?php echo $mbs_appenv->lang('change_to')?></th>
					<th><?php echo $mbs_appenv->lang('delete')?></th>
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d
				</tr></thead>
<?php 
$no =1; 
$diff_num = 0;
foreach($mod_items as $mod => &$actions){
	$actions = array_unique($actions);
	$moddef = mbs_moddef($mod);
	if(empty($moddef)){
		echo '<tr><td colspan=4>no such module found: ',$mod, '</td></tr>';
		continue;
	}
	$mgr_actions = array_keys($moddef->filterActions());
	$diff = array_diff($actions, $mgr_actions);
	if(empty($diff))
		continue;
	$diff_num += count($diff);
	$new = array_diff($mgr_actions, $actions);
?>
				<tr <?php echo 0==$no++%2?' class=even':''?>>
					<td><?php echo $mod?></td>
					<td><?php foreach($diff as $ac){ ?><p><?php echo $ac?></p><?php }?></td>
					<td>
					<?php foreach($diff as $ac){ ?>
						<p><select name="modify[<?php echo $mod?>][<?php echo $ac?>]"><option value=''>--changed--</option>
						<?php foreach($new as $n){?><option value=<?php echo $n?>><?php echo $n?></option><?php }?></select></p>
					<?php }?>
					</td>
					<td><?php foreach($diff as $ac){ ?>
						<p><input type="checkbox" name="del[<?php echo $mod?>][]" value="<?php echo $ac?>" /></p>
						<?php }?>
					</td>
				</tr>
<?php } ?>
			</table>
			<div style="margin-top:50px;text-align:center;color:#999;">
			<?php if($diff_num > 0){?>
			<a href="javascript:document._form.submit();" class="btn-primary" style="padding:6px 35px;"><?php echo $mbs_appenv->lang('submit')?></a>
<<<<<<< HEAD
			<?php }else{?> no action was changed <?php }?>
=======
			<?php }else{?> no action need to rematch <?php }?>
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d
			</div>
	</form>
			
</div>
</body>
</html>