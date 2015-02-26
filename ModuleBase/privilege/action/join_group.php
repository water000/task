<?php

mbs_import('privilege', 'CPrivGroupControl', 'CPrivUserControl');
mbs_import('user', 'CUserControl');

$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
if(empty($error)){
	try {
		$pg = CPrivGroupControl::getInstance($mbs_appenv, 
				CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['group_id']);
		$pg_info = $pg->get();
		$pg_list = $pg->decodePrivList($pg_info['priv_list']);
		
		$pu = CPrivUserControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['group_id']);
		
		if(isset($_REQUEST['user_id'])){
			$uc = CUserControl::getInstance($mbs_appenv, 
					CDbPool::getInstance(), CMemcachedPool::getInstance());
			$search_rs = $search_kv = array();
			foreach(array('user_id', 'phone_num', 'nick_name') as $k){
				if(isset($_REQUEST[$k]) && strlen($_REQUEST[$k]) != 0){
					$search_kv[$k] = $_REQUEST[$k];
				}
			}
			
			if(!empty($search_kv)){
				if(isset($search_kv['user_id'])){
					$search_kv['id'] = $search_kv['user_id'];
					unset($search_kv['user_id']);
				}
				
				$search_rs = $uc->search($search_kv);
			}
		}
		else if(isset($_REQUEST['del'])){
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
				$pu->addNode(array(
					'priv_group_id' => $_REQUEST['group_id'],
					'user_id'       => $uid,
					'creator_id'    => $user_id,
					'join_ts'       => time()
				));
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
<link href="<?=$mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
body, .warpper{background-color:#fff;}
.content{background-color:#fff;}
h1{color:#555;margin:60px 0;text-align:center;margin-top:30px;font-size:38px;}
.left{width:600px;float:left;}
.right{width:320px;float:right;padding:0 20px 20px;background-color:#eee;}
.left h2{text-align:center;color:#777;}

.right p.title{font-weight:bold;padding:2px 0;margin-top:20px;}
.right .text{width:100%; padding:3px;}
.right label{width:150px;display:inline-block;float:left;padding:2px 0;}
.right .allmod{padding:0 5px;}
.right .mod{padding:0 3px;}
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
		<h1><?=$mbs_cur_actiondef[CModDef::P_TLE]?></h1>
		<?php if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?=CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" ><?=$mbs_appenv->lang('close')?></a></div>
		<?php }else if(isset($_REQUEST['join']) || isset($_REQUEST['del'])){?>
		<div class=success><?=$mbs_appenv->lang('oper_succ')?>
			<a href="<?=$mbs_appenv->toURL('group_list')?>"><?=$mbs_appenv->lang('group_list')?></a>
			<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" ><?=$mbs_appenv->lang('close')?></a>
		</div>
		<?php }?>
		<div class=left>
			<fieldset>
				<legend><?php echo $mbs_appenv->lang('search_user')?></legend>
				<div>
					<form action="" method="post" >
					<span>ID <input type="text" name="user_id" value="<?php echo isset($_REQUEST['user_id']) ? CStrTools::txt2html($_REQUEST['user_id']) : ''?>" /></span>
					<span><?php echo $mbs_appenv->lang('phone_num')?> 
						<input type="text" name="phone_num" value="<?php echo isset($_REQUEST['phone_num']) ? CStrTools::txt2html($_REQUEST['phone_num']) : ''?>" /></span>
					<span><?php echo $mbs_appenv->lang('nick_name')?> 
						<input type="text" name="nick_name" value="<?php echo isset($_REQUEST['nick_name']) ? CStrTools::txt2html($_REQUEST['nick_name']) : ''?>" /></span>
					<input type="submit" class="submit_btn" value="search" />
					</form>
				</div>
<?php 
if(isset($_REQUEST['user_id']) && !empty($search_rs)){
?>
				<form action="" method="post">
				<table cellspacing=0>
					<tr><th>ID</th>
						<th><?php echo $mbs_appenv->lang('nick_name')?></th>
						<th><?php echo $mbs_appenv->lang('phone_num')?></th>
						<th><?php echo $mbs_appenv->lang('reg_time')?></th>
					</tr>
<?php $n = 1; foreach($search_rs as $row){?>
					<tr <?php echo 0 == $n++%2 ? 'class=even':''?>>
						<td><input style="width:30px;" type="checkbox" name="join[]" value="<?php echo $row['id']?>" /><?php echo $row['id']?></td>
						<td><?php echo CStrTools::txt2html($row['nick_name'])?></td>
						<td><?php echo $row['phone_num']?></td>
						<td><?php echo date('Y-m-d', $row['reg_ts'])?></td>
					</tr>
<?php }?>
				</table>
				<div style="margin-top:10px;"><input type="submit" style="width: 80px;" class="submit_btn" value="<?php echo $mbs_appenv->lang('join_group')?>" /></div>
				</form>
<?php
}
?>
			</fieldset>
			<fieldset style="border-color:rgb(9, 100, 18);margin-top:15px;">
				<legend><?=$mbs_appenv->lang('joined_user')?></legend>
				<form action="" method="post">
				<table cellspacing=0 style="margin-top:0;">
					<tr>
						<th>USER ID</th>
						<th><?php echo $mbs_appenv->lang('nick_name')?></th>
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
						<td><?php echo CStrTools::txt2html($usinfo['nick_name'])?></td>
						<td><?php echo date('Y-m-d', $row['join_ts'])?></td>
					</tr>
<?php }?>
				</table>
				<div style="margin-top:10px;"><input type="submit" style="width: 80px;" class="submit_btn" value="<?php echo $mbs_appenv->lang('exit_user')?>" /></div>
				</form>
<?php }?>
			</fieldset>
		</div>
		<div class=right>
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
		<div style="clear: both"></div>
	</div>
	<div class=footer></div>
</div>
</body>
</html>