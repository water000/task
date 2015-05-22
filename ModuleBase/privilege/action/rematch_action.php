<?php

mbs_import('privilege', 'CPrivGroupControl');
$priv_group = CPrivGroupControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$all = $priv_group->getDB()->listAll();

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
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
.content{background-color:#fff}
.content p.title{padding:2px 0;margin-top:5px;color:green;}
.mg-content{margin:0 10px;padding:20px 0;}
.content span{width:150px;display:inline-block;float:left;padding:2px 0;}
.content .mod{padding:0 5px;}
.even{background-color:#eee;}
p.table_title a{background:#3385ff;color:white;padding:4px;margin:0 2px;display:inline-block;float:right;font-size:12px;text-decoration:none;}
p.table_title a:hover{text-decoration:underline;color:white;}
.submit_btn{display:block;height:32px;padding:5px 30px;font-weight:bold;margin:0 auto;}
</style>
</head>
<body>
<div class="warpper">
	<div class=header></div>
	<div class=content>
		<div class=mg-content>
			<?php if($modified){ ?>
			<div class=success><?php echo $mbs_appenv->lang('oper_succ')?>
				<a href="<?php echo $mbs_appenv->toURL('group_list')?>"><?php echo $mbs_appenv->lang('group_list')?></a>
				<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
			</div>
			<?php }?>
			<p class=table_title style="margin-bottom: 16px;"><?php echo $mbs_appenv->lang('group_list')?></p>
			<form action="" method="post">
			<table cellspacing=0>
				<tr>
					<th>MODULE</th>
					<th>NOT FOUND</th>
					<th>CHANGE TO</th>
					<th>DELETE</th>
				</tr>
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
			<p style="text-align: center;color:#999;font-size:13px;"><?php if($diff_num > 0){?><input type=submit class=submit_btn /><?php }else{?> no action was changed <?php }?></p>
			</form>
		</div>
	</div>
	<div class=footer></div>
</div>
</body>
</html>