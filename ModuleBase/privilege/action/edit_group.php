<?php

mbs_import('privilege', 'CPrivGroupControl');
mbs_import('user', 'CUserSession');

$action_def = &$mbs_cur_moddef->item(CModDef::PAGES, $mbs_appenv->item('cur_action'));
$args_def = $action_def[CModDef::P_ARGS];

$error = array();
if(isset($_REQUEST['name'])){
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	if(empty($error)){
		$priv_group = CPrivGroupControl::getInstance($mbs_appenv, 
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$input_param = array_intersect_key($_REQUEST, $args_def);
		$input_param['priv_list'] = CPrivGroupControl::encodePrivList($input_param['priv_list']);
		try {
			if(isset($_REQUEST['group_id'])){
				$priv_group->append($_REQUEST['group_id']);
				unset($input_param['group_id']);
				$priv_group->set($input_param);
			}else{
				$us = new CUserSession();
				list($user_id, ) = $us->get();
				$input_param['creator_id'] = $user_id;
				$input_param['create_ts'] = time();
				$id = $priv_group->add($input_param);
			}
		} catch (Exception $e) {
			$error[] = $e->getMessage();
		}
	}
}

$args_value = array();
if(isset($_REQUEST['group_id'])){
	$priv_group = CPrivGroupControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['group_id']);
	$args_value = $priv_group->get();
	if(empty($args_value)){
		exit('access denied');
	}
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<style type="text/css">
body, .warpper{background-color:#fff;}
.content{background-color:#fff;}
h1{color:#555;margin:60px 0;text-align:center;margin-top:30px;font-size:38px;}
.left{width:600px;float:left;}
.right{width:320px;float:left;padding:0 20px 20px;background-color:#eee;}
.left h2, .left p{text-align:center;color:#777;}
.left p{padding:5px;}

.right p.title{font-weight:bold;padding:2px 0;margin-top:20px;}
.right .text{width:100%; padding:3px;}
.right label{width:150px;display:inline-block;float:left;padding:2px 0;}
.right .allmod{padding:0 5px;}
.right .mod{padding:0 3px;}
.right .allmod p{color:#000;margin-top:10px;}

.submit_btn{display:block;width:100%;height:32px;font-weight:bold;margin:0 auto;}
</style>
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="warpper">
	<div class=content>
		<h1><?php echo $action_def[CModDef::P_TLE]?></h1>
		<?php if(isset($_REQUEST['name'])){if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" ><?php echo $mbs_appenv->lang('close')?></a></div>
		<?php }else{?>
		<div class=success><?php echo $mbs_appenv->lang('oper_succ')?>
			<a href="<?php echo $mbs_appenv->toURL('group_list')?>"><?php echo $mbs_appenv->lang('group_list')?></a>
			<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" ><?php echo $mbs_appenv->lang('close')?></a>
		</div>
		<?php }}?>
		<div class=left><?php echo $mbs_appenv->lang('group_can')?></div>
		<div class=right>
			<form action="" method="post">
				<?php if(isset($_REQUEST['group_id'])){?>
				<input type=hidden name="group_id" value="<?php echo $_REQUEST['group_id']?>" />
				<?php }?>
				<p class=title><?php echo $mbs_appenv->lang('group_name')?></p>
				<p><input type="text" class=text name="name" value="<?php echo isset($args_value['name'])?$args_value['name']:''?>" /></p>
				<p class=title><?php echo $mbs_appenv->lang('group_type')?></p>
				<p><?php $args_value['type'] = isset($args_value['type']) ? $args_value['type'] : CPrivilegeDef::TYPE_ALLOW; ?>
					<input type=radio name=type value=<?php echo CPrivilegeDef::TYPE_ALLOW?> <?php echo $args_value['type']==CPrivilegeDef::TYPE_ALLOW ? ' checked':''?> /><?php echo $mbs_appenv->lang('type_allow')?>
					<input type=radio name=type value=<?php echo CPrivilegeDef::TYPE_DENY?> <?php echo $args_value['type']==CPrivilegeDef::TYPE_DENY ? ' checked':''?> /><?php echo $mbs_appenv->lang('type_deny')?>
				</p>
				<?php 
				$mod_list = $mbs_appenv->getModList();
				foreach($mod_list as $mod){
					$moddef = mbs_moddef($mod);
					if(empty($moddef))
						continue;
					$mgr_list = $moddef->filterActions();
					if(empty($mgr_list))
						continue;
				?>
				<div class=allmod>
					<p class=title><?php echo $moddef->item(CModDef::MOD, CModDef::G_TL)?></p>
					<div class=mod>
						<?php foreach($mgr_list as $key => $title){?><label>
							<input type="checkbox" name="priv_list[<?php echo $mod?>][]" value="<?php echo $key?>" <?php echo (isset($_REQUEST['group_id'])&&$priv_group->privExists($mod, $key)) ? ' checked' : ''?> /><?php echo $title?>
						</label><?php }?>
						<div style="clear: both"></div>
					</div>
				</div>
				<?php
				}
				?>
				<p class=title style="margin-top:30px;"><input class=submit_btn type=submit /></p>
			</form>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class=footer></div>
</div>
</body>
</html>