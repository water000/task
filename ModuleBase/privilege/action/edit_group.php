<?php

mbs_import('privilege', 'CPrivGroupControl');
mbs_import('user', 'CUserSession');

$action_def = $mbs_cur_moddef->item(CModDef::PAGES, $mbs_appenv->item('cur_action'));
$args_def = $action_def[CModDef::P_ARGS];

$title = 'create';

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
	$title = 'edit';
}

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
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('userManager.css')?>">
<style type="text/css">
form{margin-top:50px;font-size:12px;}
dl, dl dd{display:inline-block;}
dl{width:750px;}
dl{background-color:rgb(248,248,248); border:1px solid rgb(228,228, 228);padding:0 25px 25px;}
dt{margin:30px 0 10px;}
dd{margin-right:20px;}


</style>
</head>
<body>
<div class="userManager">
    <h2 class="tit"><?php echo $mbs_appenv->lang(array($title, 'group'))?>
        <a href="<?php echo $mbs_appenv->toURL('group_list')?>" class="btn-create">
        	<span class="back-icon"></span><?php echo $mbs_appenv->lang('back')?></a>
    </h2>
        
    <?php if(!empty($error)){ ?>
	<div class=error><p><?php echo implode('<br/>', $error)?></p>
	<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
	</div>
	<?php }else if(isset($_REQUEST['name'])) {?>
	<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a></div>
	<?php }?>
	
	<form name="_form" method="post">
        <div class="inpBox mb17">
            <label for="name" class="labelL"><?php echo $mbs_appenv->lang('group_name')?>&nbsp;:&nbsp;</label>
		    <input id="name" class="inpTit" name="name" type="text" value="<?php echo isset($args_value['name'])?$args_value['name']:''?>" 
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
        </div>
        <div class="inpBox mb17">
            <label for="group_type" class="labelL"><?php echo $mbs_appenv->lang('group_type')?>&nbsp;:&nbsp;</label>
		    <label style="margin-right:20px;"><input type=radio name=type value=<?php echo CPrivilegeDef::TYPE_ALLOW?> 
		    	<?php echo isset($args_value['type']) && $args_value['type']==CPrivilegeDef::TYPE_ALLOW ? ' checked':''?> /><?php echo $mbs_appenv->lang('type_allow')?></label>
			<label><input type=radio name=type value=<?php echo CPrivilegeDef::TYPE_DENY?> 
				<?php echo isset($args_value['type']) && $args_value['type']==CPrivilegeDef::TYPE_DENY ? ' checked':''?> /><?php echo $mbs_appenv->lang('type_deny')?></label>
        </div>
        <div class="inpBox mb17">
            <label for="name" class="labelL" style="vertical-align: top; "><?php echo $mbs_appenv->lang(array('priv', 'select'))?>&nbsp;:&nbsp;</label>
		    <dl>
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
				<dt><?php echo $moddef->item(CModDef::MOD, CModDef::G_TL)?></dt>
				<?php foreach($mgr_list as $key => $def){?>
				<dd><label><input type="checkbox" name="priv_list[<?php echo $mod?>][]" value="<?php echo $key?>" 
					<?php echo (isset($_REQUEST['group_id'])&&$priv_group->privExists($mod, $key)) ? ' checked' : ''?> />
					<?php echo $def[CModDef::P_TLE]?></label><dd><?php }?>
			<?php
			}
			?>
		    </dl>
        </div>
        <div class="btnBox">
        	<label for="" class="labelL"></label>
            <a href="javascript:document._form.submit();" class="btn-send"><?php echo $mbs_appenv->lang('submit')?></a>
            <a href="<?php echo $mbs_appenv->toURL('group_list')?>" class="btn-cancle"><?php echo $mbs_appenv->lang('cancel')?></a>
        </div>
    </form>
</div>
</body>
</html>