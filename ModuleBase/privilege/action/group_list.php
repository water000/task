<?php

mbs_import('privilege', 'CPrivGroupControl');
$priv_group = CPrivGroupControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$all = $priv_group->getDB()->listAll();

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?=$mbs_appenv->sURL('core.css')?>" rel="stylesheet">
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
			<p class=table_title style="margin-bottom: 16px;"><?=$mbs_appenv->lang('group_list')?>
				<a href="<?=$mbs_appenv->toURL('edit_group')?>"><?=$mbs_appenv->lang('create_group')?></a></p>
			<table cellspacing=0>
				<tr>
					<th  style="width:80px;">NO.</th>
					<th><?=$mbs_appenv->lang('group_name')?></th>
					<th><?=$mbs_appenv->lang('creator')?></th>
					<th><?=$mbs_appenv->lang('create_time')?></th>
					<th><?=$mbs_appenv->lang('group_type')?></th>
					<th style="width: 320px;"><?=$mbs_appenv->lang('priv_list')?></th>
					<th></th>
				</tr>
			<?php $no =1; foreach($all as $row){ ?>
			<tr <?=0==$no%2?' class=even':''?>>
				<td><?=$no++?></td><td><a href="<?=$mbs_appenv->toURL('edit_group', '', array('group_id'=>$row['id']))?>"><?=CStrTools::txt2html($row['name'])?></a></td>
				<td><?=$row['creator_id']?></td><td><?=date('Y-m-d', $row['create_ts'])?></td>
				<td><?=$mbs_appenv->lang($row['type'] == CPrivilegeDef::TYPE_ALLOW ? 'type_allow' : 'type_deny')?></td>
				<td>
	<?php 
	$modified_priv = array();
	$priv_list = CPrivGroupControl::decodePrivList($row['priv_list']);
	if(CPrivGroupControl::isTopmost($priv_list)){
		echo '<b>',$mbs_appenv->lang('topmost_group'), '</b>';
	}else{
		$prev = '';
		$_moddef = null;
		foreach($priv_list as $mod => $actions){
			$_moddef = mbs_moddef($mod);
			if(empty($_moddef)){
				$modified_priv[$row['id']][] = $mod;
				continue;
			}
			echo '<p class=title>', $_moddef->item(CModDef::MOD, CModDef::G_TL), '</p>';
			echo '<div class=mod>';
			foreach($actions as $action){
				$ac = $_moddef->item(CModDef::PAGES, $action, CModDef::P_TLE);
				if(empty($ac)){
					$modified_priv[$row['id']][] = $mod.'.'.$action;
				}else{
					echo '<span>', $ac, '</span>';
				}
			}
			echo '</div>';
		}
	}
	?>
				</td>
				<td><a href="<?=$mbs_appenv->toURL('join_group')?>"><?=$mbs_appenv->lang('join_group')?></a></td>
			</tr>
			<?php } ?>
			</table>
		</div>
	</div>
	<div class=footer></div>
</div>
</body>
</html>