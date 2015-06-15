<?php 
$page_title = $mbs_appenv->lang(isset($_REQUEST['id']) ? 'edit_info' : 'record_info');

mbs_import('', 'CUserControl');

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
<title><?php mbs_title($page_title)?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-1-6"><?php call_user_func($mbs_appenv->lang('menu'))?></div>
    <div class="pure-u-5-6">
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
		
    	<form name="_form" class="pure-form pure-form-aligned" method="post">
    		<input type="hidden" name="__timeline" value="<?php echo time()?>" />
		    <fieldset>
		    	<legend style="font-size: 150%;"><?php echo $page_title?>
		    		<a class=back href="<?php echo $mbs_appenv->toURL('list')?>">&lt;<?php echo $mbs_appenv->lang('back', 'common')?></a></legend>
		    	
		        <div class="pure-control-group">
		            <label for="name"><?php echo $mbs_appenv->lang('name')?></label>
		            <input id="name" name="name" type="text" value="<?php echo $user['name']?>" required />
		        </div>
		        <div class="pure-control-group">
		            <label for="organization"><?php echo $mbs_appenv->lang('organization')?></label>
		            <input id="organization" name="organization" type="text" value="<?php echo $user['organization']?>" />
		        </div>
		        <div class="pure-control-group">
		            <label for="phone"><?php echo $mbs_appenv->lang('phone')?></label>
		            <input id="phone" name="phone" type="text" value="<?php echo $user['phone']?>" required />
		        </div>
		        <div class="pure-control-group">
		            <label for="password"><?php echo $mbs_appenv->lang(array('login', 'password'))?></label>
		            <input id="password" name="password" type="text" value="<?php echo $user['password']?>" />
		        </div>
		        <div class="pure-control-group">
		            <label for="email"><?php echo $mbs_appenv->lang('email')?></label>
		            <input id="email" name="email" type="email" value="<?php echo $user['email']?>" />
		        </div>
		        <div class="pure-control-group">
		            <label for="IMEI">IMEI</label>
		            <input id="IMEI" name="IMEI" type="text" value="<?php echo $user['IMEI']?>" />
		        </div>
		        <div class="pure-control-group">
		            <label for="IMSI">IMSI</label>
		            <input id="IMSI" name="IMSI" type="text" value="<?php echo $user['IMSI']?>" />
		        </div>
		        <div class="pure-control-group">
		            <label for="VPDN_name"><?php echo $mbs_appenv->lang('VPDN_name')?></label>
		            <input id="VPDN_name" name="VPDN_name" type="text" value="<?php echo $user['VPDN_name']?>" />
		        </div>
		        <div class="pure-control-group">
		            <label for="VPDN_pass">VPDN <?php echo $mbs_appenv->lang('password')?></label>
		            <input id="VPDN_pass" name="VPDN_pass" type="text" value="<?php echo $user['VPDN_pass']?>" />
		        </div>
		        <div class="pure-control-group">
		            <label for="class"><?php echo $mbs_appenv->lang('class')?></label>
		            <?php 
		            $class_value=''; 
		            if($user['class_id'] > 0){
						mbs_import('user', 'CUserClassControl');
						$class_ins = CUserClassControl::getInstance($mbs_appenv, 
							CDbPool::getInstance(), CMemcachedPool::getInstance(), $user['class_id']);
						$class_info = $class_ins->get();
						if(empty($class_info)){
							$user['class_id'] = $class_value = '';
						}else{
							$class_value = $class_info['name'];
						}
					}
		            ?>
		            <input id="class" name="class" type="text" style="color: #aaa;" 
		            	value="<?php echo $class_value?>" disabled />
		            <input type="hidden" name="class_id" value="<?php echo $user['class_id']?>"  />
		            <a href="#" style="vertical-align: bottom; color:red; display: <?php echo empty($class_value) ? 'none':'';?>" 
		            	onclick="var c=this.parentNode.getElementsByTagName('input');c[0].value=c[1].value='';this.style.display='none';">&times;</a>
		            <a href="javascript:;" onclick="window.open('<?=$mbs_appenv->toURL('class', '', array('popwin'=>1))?>', '_blank,_top', 'height=400,width=600,location=no', true)"
		            	style="vertical-align: bottom;margin-left:20px;">
		            	<?php echo $mbs_appenv->lang('select_class')?>
		            </a>
		        </div>
		        <br />
		        <div class="pure-control-group">
		            <label for="submit"></label>
		            <button type="submit" class="pure-button pure-button-primary"><?php echo $page_title?></button>
		        </div>
		    </fieldset>
		</form>
    </div>
</div>
<div class=footer></div>
<script type="text/javascript">
window.cb_class_selected = function(selected_class, popwin){
	if(selected_class.length > 0){
		document._form.elements["class_id"].value = selected_class[0];
		document._form.elements["class"].value = selected_class[1];
		popwin.close();
	}
}
</script>
</body>
</html>