<?php

mbs_import('privilege', 'CPrivGroupControl');
$priv_group = CPrivGroupControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$all = $priv_group->getDB()->listAll();

$not_found = array();
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
</style>
</head>
<body>
<div class="warpper">
	<div class=header></div>
	<div class=content>
		<div class=mg-content>
			<p class=table_title style="margin-bottom: 16px;"><?php echo $mbs_appenv->lang('group_list')?></p>
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
	$mgr_actions = $moddef->filterActions();
	$diff = array_diff($actions, array_keys($mgr_actions)); 
	$diff_num += count($diff);
	$new = array_intersect($actions, $mgr_actions);
?>
				<tr <?php echo 0==$no++%2?' class=even':''?>>
					<td><?php echo $mod?></td>
					<td><?php foreach($diff as $ac){ ?><p><?php echo $ac?></p><?php }?></td>
					<td>
					<?php foreach($diff as $ac){ ?>
						<p><select name="modify[<?php echo $mod?>][<?php echo $ac?>]">
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
			<?php if($diff_num > 0){?><p><input type=submit class=submit_btn /></p><?php }?>
		</div>
	</div>
	<div class=footer></div>
</div>
</body>
</html>