<?php 
$page_title = $mbs_appenv->lang(isset($_REQUEST['id']) ? 'edit_info' : 'record_info');

mbs_import('', 'CUserControl', 'CUserClassControl');

$uclass_ctr = CUserClassControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$class_list = $uclass_ctr->getDB()->listAll();
$class_list = $class_list->fetchAll(PDO::FETCH_ASSOC);


$user = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');

if(isset($_REQUEST['id'])){
	$user_ins = CUserControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	
	if(isset($_REQUEST['delete'])){
		foreach($_REQUEST['id'] as $id){
			if($id > 3){
				$user_ins->setPrimaryKey(intval($id));
				$user_ins->destroy();
			}
		}
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '', $mbs_appenv->toURL('list'));
		exit(0);
	}
	
	$_REQUEST['id'] = intval($_REQUEST['id']);
	$user_ins->setPrimaryKey($_REQUEST['id']);
	
	if(isset($_REQUEST['name'])){
		$user = array_intersect_key($_REQUEST, $user);
		$exclude = array();
		if(!empty($user['password'])){
			$user['password']= CUserControl::formatPassword($user['password']);
		}else{
			unset($user['password']);
			$exclude[] = 'password';
		}
		$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'), $exclude);
		if(empty($error)){
			$ret = $user_ins->set($user);
			if(empty($ret)){
				$error[] = $mbs_appenv->lang('existed'. 'common').':'.$_REQUEST['phone'];
			}
		}
	}else{
		$user_spec = $user_ins->get();
		if(empty($user_spec)){
			$mbs_appenv->echoex('no such user', 'NO_USER');
			exit(0);
		}
		$user = array_intersect_key($user_spec, $user);
	}
	$user['password']='';
}
else if(isset($_REQUEST['__timeline'])){
	$new_user = array_intersect_key($_REQUEST, $user);
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	if(empty($error)){
		$user_ins = CUserControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$ret = $user_ins->add($new_user);
		if(empty($ret)){
			$error[] = $mbs_appenv->lang('existed'. 'common').':'.$_REQUEST['phone'];
			$user = $new_user;
		}
	}else{
		$user = $new_user;
	}
}else{
	$user = array_merge($user, array_intersect_key($_REQUEST, $user));
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
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('ui.daterangepicker.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('jquery-ui-1.7.1.custom.css')?>" type="text/css" title="ui-theme" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('userManager.css')?>">
</head>
<body>
	<div class="userManager">
        <h2 class="tit"><?php echo $mbs_appenv->lang(array('user', 'data', 'edit'))?>
            <a href="<?php echo $mbs_appenv->toURL('list')?>" class="btn-create"><span class="back-icon"></span><?php echo $mbs_appenv->lang('back')?></a>
        </h2>
        
        <?php if(isset($_REQUEST['__timeline'])){ if(!empty($error)){ ?>
		<div class=error><p><?php echo implode('<br/>', $error)?></p>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }else {?>
		<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?>
			<?php if(isset($new_user['class_id']) && empty($new_user['class_id']) ){ ?>
			<a href="<?php echo $mbs_appenv->toURL('department')?>" class=link ><?php echo $mbs_appenv->lang('join_department')?></a>
			<?php } ?>
			<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a></div>
		<?php }}?>
		
		<form name="_form" method="post" style="margin:0 80px;">
    		<input type="hidden" name="__timeline" value="<?php echo time()?>" />
        <div class="inpBox mb17">
            <label for="name" class="labelL"><?php echo $mbs_appenv->lang('name')?>&nbsp;:&nbsp;</label>
		    <input id="name" class="inpTit" name="name" type="text" value="<?php echo $user['name']?>" 
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
        </div>
        <div class="inpBox mb17">
        	<label for="name" class="labelL"><?php echo $mbs_appenv->lang('organization')?>&nbsp;:&nbsp;</label>
		    <input id="name" class="inpTit" name="organization" type="text" value="<?php echo $user['organization']?>" 
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
        </div>
        <div class="inpBox mb17">
            <label for="name" class="labelL"><?php echo $mbs_appenv->lang('phone')?>&nbsp;:&nbsp;</label>
		    <input id="name" class="inpTit" name="phone" type="text" value="<?php echo $user['phone']?>" 
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
        </div>
        <div class="inpBox mb17">
        	<label for="password" class="labelL"><?php echo $mbs_appenv->lang(array('login', 'password'))?>&nbsp;:&nbsp;</label>
		    <input id="name" class="inpTit" name="phone" type="text" value="<?php echo $user['password']?>" 
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
        </div>
        <div class="inpBox mb17">
        	<label for="email" class="labelL"><?php echo $mbs_appenv->lang('email')?>&nbsp;:&nbsp;</label>
		    <input id="email" class="inpTit" name="email" type="email" value="<?php echo $user['email']?>"
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" />
        </div>
        <div class="inpBox mb17">.
        	<label for="IMEI" class="labelL">IMEI&nbsp;:&nbsp;</label>
		    <input id="IMEI" class="inpTit" name="IMEI" value="<?php echo $user['IMEI']?>"
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" />
        </div>
        <div class="inpBox mb17">
        	<label for="IMSI" class="labelL">IMSI&nbsp;:&nbsp;</label>
		    <input id="IMSI" class="inpTit" name="IMSI" value="<?php echo $user['IMSI']?>"
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" />
        </div>
        <div class="inpBox mb17">
        	<label for="VPDN_name" class="labelL"><?php echo $mbs_appenv->lang('VPDN_name')?>&nbsp;:&nbsp;</label>
		    <input id="VPDN_name" class="inpTit" name="VPDN_name" value="<?php echo $user['VPDN_name']?>"
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" />
        </div>
        <div class="inpBox mb17">
        	<label for="VPDN_pass" class="labelL">VPDN<?php echo $mbs_appenv->lang('password')?>&nbsp;:&nbsp;</label>
		    <input id="VPDN_pass" class="inpTit" name="VPDN_pass" value="<?php echo $user['VPDN_pass']?>"
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" />
        </div>
        <div class="inpBox mb17">
            <label for="" class="labelL">类别：</label>
            <select name="class_id" id="" class="sel-format">
            <option class="format" value="0"></option>
            <?php foreach($class_list as $c){?>
            <option class="format" value="<?php echo $c['id']?>"><?php echo CStrTools::txt2html($c['name'])?></option>
            <?php }?>
            </select>
        </div>
        <div class="btnBox">
            <a href="javascript:document._form.submit();" class="btn-send"><?php echo $mbs_appenv->lang('submit')?></a>
            <a href="<?php echo $mbs_appenv->toURL('list')?>" class="btn-cancle"><?php echo $mbs_appenv->lang('cancel')?></a>
        </div>
        </form>
    </div>
</body>
</html>