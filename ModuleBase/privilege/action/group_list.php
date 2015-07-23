<?php

mbs_import('privilege', 'CPrivGroupControl', 'CPrivUserControl');
mbs_import('user', 'CUserControl');

$priv_group = CPrivGroupControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$all = $priv_group->getDB()->listAll()->fetchAll();

$user_ctr = CUserControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$priv_user_ctr = CPrivUserControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$page_num_list = array();
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">
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
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('allInfo.css')?>">
<style type="text/css">
.col-chbox{width:48px;}
.col-name{width:188px;}
.col-org{width:285px;}
.col-phone{width:150px;}
.col-email{width:240px;}
.col-oper{width:118px;}
.name{font-size:14px; color:#111;}
.info-table table tr{border:0;}
.info-table table tr td{padding:0;}
.allInfo .btn-create{width:100px;}
.total-person{background-color:rgb(230, 240, 250);color:rgb(0, 67, 144);padding:3px 5px;border-radius:3px;}
</style>
</head>
<body>
<div class="allInfo">
	<h2 class="tit">
		<?php echo $mbs_appenv->lang(array('group', 'manage'))?>
		<span class="tips"><?php echo sprintf($mbs_appenv->lang('total_count'), count($all))?></span>
		<a href="<?php echo $mbs_appenv->toURL('edit_group')?>" class="btn-create">
			+<?php echo $mbs_appenv->lang('create_group')?></a>
	</h2>
	
	<!-- 列表 -->
	<form name="form_list" action="<?php echo $mbs_appenv->toURL('push', 'info_push')?>" method="post">
	<div class="box-tabel mb17">
		<table class="info-table" style="margin-top:23px;">
		    <thead>
		        <tr>
		            <th class="first-col col-chbox"><input type="checkbox" /></th>
		            <th><?php echo $mbs_appenv->lang('group_name')?></th>
					<th><?php echo $mbs_appenv->lang('creator')?></th>
					<th><?php echo $mbs_appenv->lang('create_time')?></th>
					<th><?php echo $mbs_appenv->lang('group_type')?></th>
					<th style="width:30%"><?php echo $mbs_appenv->lang('priv_list')?></th>
		            <th><?php echo $mbs_appenv->lang('member')?></th>
		            <th><?php echo $mbs_appenv->lang('operation')?></th>
		        </tr>
		    </thead>
		    <tbody>
		    	<?php 
		    	$k=-1;
		    	foreach($all as $k => $row){ $priv_user_ctr->setPrimaryKey($row['id']);?>
		        <tr >
		            <td><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /></td>
		            <td class=name><?php echo CStrTools::txt2html($row['name'])?></td>
				<td><?php if($row['creator_id'] != 0){$user_ctr->setPrimaryKey($row['creator_id']); $uinfo=$user_ctr->get(); echo empty($uinfo)?'(delete)':$uinfo['name'];}?></td>
				<td><?php echo date('Y-m-d', $row['create_ts'])?></td>
				<td><?php echo $mbs_appenv->lang($row['type'] == CPrivilegeDef::TYPE_ALLOW ? 'type_allow' : 'type_deny')?></td>
				<td>
<?php 
$modified_priv = array();
$priv_list = CPrivGroupControl::decodePrivList($row['priv_list']);
if(CPrivGroupControl::isTopmost($priv_list)){
	echo '<b>',$mbs_appenv->lang('topmost_group'), '</b>';
}else{
	$_moddef = null;
	echo '<table>';
	foreach($priv_list as $mod => $actions){
		$_moddef = mbs_moddef($mod);
		if(empty($_moddef)){
			$modified_priv[$row['id']][] = $mod;
			continue;
		}
		echo '<tr><td>', $_moddef->item(CModDef::MOD, CModDef::G_TL), '</td>';
		echo '<td>&nbsp;(&nbsp;';
		foreach($actions as $k => $action){
			if($k > 0){
				echo $mbs_appenv->lang('slash');
			}
			$ac = $_moddef->item(CModDef::PAGES, $action, CModDef::P_TLE);
			if(empty($ac)){
				$modified_priv[$row['id']][] = $mod.'.'.$action;
			}else{
				echo '<span>', $ac, '</span>';
			}
		}
		echo '&nbsp;)</td></tr>';
	}
	echo '</table>';
}
?>
				</td>
				<td><a class=total-person href="<?php echo $mbs_appenv->toURL('join_group', '', array('group_id'=>$row['id']))?>">
					<?php echo sprintf($mbs_appenv->lang('total_person'), $priv_user_ctr->getTotal())?></a></td>
				<td><a href="<?php echo $mbs_appenv->toURL('edit_group', '', array('group_id'=>$row['id']))?>">
					<?php echo $mbs_appenv->lang(array('edit', 'group'))?></a></td>
		        </tr>
		     	<?php } if(-1 == $k){ ?>
		     	<tr><td colspan=5 class=no-data><?php echo $mbs_appenv->lang('no_data', 'common')?></td></tr>
		     	<?php }?>
		      </tbody>
		</table>
	</div>
	<!-- 列表end -->
	<div class="box-bottom">
		<a id=IDA_BTN_DEL href="javascript:;" class="btn-del" onclick="document.form_list.action='<?php echo $mbs_appenv->toURL('edit', '', array('delete'=>''))?>';document.form_list.submit();">
			<i class="ico"></i><?php echo $mbs_appenv->lang('delete')?></a>
		<?php if(count($page_num_list) > 1){ ?>
		<p class="pageBox">
			<?php if(PAGE_ID > 1){ ?>
			<a href="<?php echo $mbs_appenv->toURL('list', '', array_merge($search_keys, array('page_id'=>PAGE_ID-1))) ?>" 
				class="btn-page"><?php echo $mbs_appenv->lang('prev_page')?></a>
			<?php } ?>
        	<?php foreach($page_num_list as $n => $v){ ?>
        	<a href="<?php echo $mbs_appenv->toURL('list', '', array_merge($search_keys, array('page_id'=>$n))) ?>" 
        		class="btn-page <?php echo $n==PAGE_ID?' check':''?>" ><?php echo $v?></a>
        	<?php }?>
        	<?php if(PAGE_ID < count($page_num_list)){ ?>
	        <a href="<?php echo $mbs_appenv->toURL('list', '', array_merge($search_keys, array('page_id'=>PAGE_ID+1))) ?>" 
	        	class="btn-page"><?php echo $mbs_appenv->lang('next_page')?></a>
	        <?php }?>
	    </p>
		<?php } ?>
	</div>
	</form>
</div>
</body>
</html>