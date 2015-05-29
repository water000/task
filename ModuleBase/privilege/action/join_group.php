<?php

mbs_import('privilege', 'CPrivGroupControl', 'CPrivUserControl');
mbs_import('user', 'CUserControl');

$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
if(empty($error)){
	try {
		
		$pg = CPrivGroupControl::getInstance($mbs_appenv, 
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$pg_all = $pg->getDB()->listAll();
		if(empty($pg_all) || !($pg_all = $pg_all->fetchAll(PDO::FETCH_ASSOC))){
			echo $mbs_appenv->lang('create_before_join');
			exit;
		}
		
		if(isset($_REQUEST['group_id'])){
			foreach($pg_all as $row){
				if($row['id'] == $_REQUEST['group_id']){
					$pg_info = $row;
					break;
				}
			}
		}else{
			$_REQUEST['group_id'] = $pg_all[0]['id'];
			$pg_info = $pg_all[0];
		}		
		$pg_list = $pg->decodePrivList($pg_info['priv_list']);
		
		$pu = CPrivUserControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['group_id']);
		
		if(isset($_REQUEST['del'])){
			foreach($_REQUEST['del'] as $uid){
				$pu->setSecondKey($uid);
				$pu->delNode();
			}
		}
		else if(isset($_REQUEST['join'])){
			mbs_import('user', 'CUserSession');
			$us = new CUserSession();
			list($user_id, ) = $us->get();
			foreach($_REQUEST['join'] as $uid){
				$ret = $pu->addNode(array(
					'priv_group_id' => $_REQUEST['group_id'],
					'user_id'       => $uid,
					'creator_id'    => $user_id,
					'join_ts'       => time()
				));
				if(!$ret){
					$error[] = 'user(id:'.$uid.') already exists';
				}
			}
		}
		
		
		$pu_list = $pu->getDB()->getAll();
		
		$usctr = CUserControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance());
	} catch (Exception $e) {
		$error[] = $e->getMessage();
	}
	
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
body, .warpper{background-color:#fff;}
.content{background-color:#fff;}
h1{color:#555;margin:60px 0;text-align:center;margin-top:30px;font-size:38px;}
.left{width:600px;float:left;}
.right{width:360px;float:right;}
.left h2{text-align:center;color:#777;}

.right .group_info{padding:0 20px 20px;background-color:#eee;margin-top:5px;}
.right p.title{font-weight:bold;padding:2px 0;margin-top:20px;}
.right .text{width:100%; padding:3px;}
.right label{width:150px;display:inline-block;float:left;padding:2px 0;}
.right .allmod{padding:0 5px;}
.right .mod{padding:0 3px;}
.mod span{margin-right:10px;}
.right .allmod p{color:#000;margin-top:10px;}

table{margin:10px 0 0;}
input{width:120px;padding:3px 0;}
fieldset{border: 2px solid #85BBEF;border-left-width:5px; border-right-width:5px; border-radius:3px;padding:10px;}
fieldset span{margin-left:5px;}
.submit_btn{width:60px;font-weight:bold;margin-left:5px;}
.even{background-color:#eee}
</style>
</head>
<body>
<div class="warpper">
	<div class=header></div>
	<div class=content>
		<h1><?php echo $mbs_cur_actiondef[CModDef::P_TLE]?></h1>
		<?php if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" ><?php echo $mbs_appenv->lang('close')?></a></div>
		<?php }else if(isset($_REQUEST['join']) || isset($_REQUEST['del'])){?>
		<div class=success><?php echo $mbs_appenv->lang('oper_succ')?>
			<a href="<?php echo $mbs_appenv->toURL('group_list')?>"><?php echo $mbs_appenv->lang('group_list')?></a>
			<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" ><?php echo $mbs_appenv->lang('close')?></a>
		</div>
		<?php }?>
		<div class=left>
			<h3><?php echo $mbs_appenv->lang('joined_user')?></h3>
			<form action="" method="post">
			<table cellspacing=0 style="margin-bottom:10px;">
				<tr>
					<th>USER ID</th>
					<th><?php echo $mbs_appenv->lang('name')?></th>
					<th><?php echo $mbs_appenv->lang('join_ts')?></th>
				</tr>
<?php 
if(!empty($pu_list)){
	$n = 1; 
	foreach($pu_list as $row){ 
		$usctr->setPrimaryKey($row['user_id']); 
		$usinfo = $usctr->get(); 
?>
				<tr <?php echo 0 == $n++%2 ? 'class=even':''?>>
					<td><input style="width:30px;" type="checkbox" name="del[]" value="<?php echo $row['user_id']?>" /><?php echo $row['user_id']?></td>
					<td><?php echo CStrTools::txt2html($usinfo['name'])?></td>
					<td><?php echo date('Y-m-d', $row['join_ts'])?></td>
				</tr>
<?php }?>
				<tr><td colspan=3 style="border:0;padding-top:10px;">
					<input type="submit" style="width: 80px;" class="submit_btn" value="<?php echo $mbs_appenv->lang('exit_user')?>" /></td></tr>
<?php }else{?>
				<tr><td colspan=3><?php echo $mbs_appenv->lang('no_data', 'common')?></td></tr>
<?php } ?>
			</table>
			</form>
			
			<h3 style="margin-top:30px;"><?php echo $mbs_appenv->lang('join_user')?>
				&nbsp;&nbsp;<a href="javascript:window.open('<?=$mbs_appenv->toURL('list', 'user', array('popwin'=>1))?>', '_blank,_top', 'height=600,width=900,location=no', true);">
				<?php echo $mbs_appenv->lang('select_user')?></a>
			</h3>
			<form action="" method="post">
			<table cellspacing=0 style="margin-bottom:10px;" id="IDT_JOIN_LIST">
				<tr>
					<th>USER ID</th>
					<th><?php echo $mbs_appenv->lang('name')?></th>
					<th><?php echo $mbs_appenv->lang('delete', 'common')?></th>
				</tr>
				<tr><td colspan=2 style="border:0;padding-top:10px;"> <input type="submit" style="width: 80px;" class="submit_btn" value="<?php echo $mbs_appenv->lang('join_user')?>" /></td></tr>
			</table>
			</form>
<script type="text/javascript">
var g_join_list = document.getElementById("IDT_JOIN_LIST"), g_selected_user=[];
function _del(oa){
	delete g_selected_user[oa.previousSibling.value];
	oa.parentNode.parentNode.parentNode.removeChild(oa.parentNode.parentNode);
}
window.cb_class_selected = function(selected_class, popwin){
	if(selected_class.length > 0){
		for(var i=0, j=selected_class.length/2; i<j; i++){
			if("undefined" == typeof g_selected_user[selected_class[i*2]]){
				var tr = g_join_list.insertRow(g_join_list.rows.length-1);
				tr.insertCell().innerHTML = selected_class[i*2];
				tr.insertCell().innerHTML = selected_class[i*2+1];
				tr.insertCell().innerHTML = "<input type=hidden name='join[]' value='"+selected_class[i*2]
					+"' /><a href='javascript:;' onclick='_del(this)'><?php echo $mbs_appenv->lang('delete', 'common')?></a>";
				g_selected_user[selected_class[i*2]] = 1;
			}
		}
		popwin.close();
	}
}
</script>
		</div>
		<div class=right>
			<div><select style="float: right;" onchange="location.href='<?php echo $mbs_appenv->item('cur_action_url')?>?group_id='+this.options[this.selectedIndex].value">
				<?php foreach($pg_all as $row){?>
				<option value="<?php echo $row['id']?>" <?php echo $row['id']==$_REQUEST['group_id']?' selected':''?>>
					<?php echo CStrTools::txt2html($row['name'])?>
				</option>
				<?php }?>
				</select>
			</div>
			<div style="clear: both"></div>
			<div class=group_info>
			<p class=title><?php echo $mbs_appenv->lang('group_name')?></p>
			<p><?php echo $pg_info['name']?></p>
			<p class=title><?php echo $mbs_appenv->lang('group_type')?></p>
			<p><?php echo $pg_info['type']==CPrivilegeDef::TYPE_ALLOW ? $mbs_appenv->lang('type_allow'):$mbs_appenv->lang('type_deny')?></p>
			<p class=title><?php echo $mbs_appenv->lang('priv_list')?></p>
			<p>
<?php 
if(empty($error)){
	if(CPrivGroupControl::isTopmost($pg_list)){ 
		echo $mbs_appenv->lang('topmost_group');
	}else{
		$_moddef = null;
		foreach($pg_list as $mod => $actions){
			$_moddef = mbs_moddef($mod);
			if(empty($_moddef)){
				echo '<p class=title>', $mod, '<span style="color:red">(not found)</span></p>';
				continue;
			}
			echo '<p class=title>', $_moddef->item(CModDef::MOD, CModDef::G_TL), '</p>';
			echo '<div class=mod>';
			foreach($actions as $action){
				$ac = $_moddef->item(CModDef::PAGES, $action, CModDef::P_TLE);
				if(empty($ac)){
					echo '<span>', $action, '</span>';
				}else{
					echo '<span>', $ac, '</span>';
				}
			}
			echo '</div>';
		}
	}
}
?>
			</p>
			</div>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class=footer></div>
</div>
</body>
</html>