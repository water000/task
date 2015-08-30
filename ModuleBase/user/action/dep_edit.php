<?php 

mbs_import('', 'CUserDepControl', 'CUserDepMemberControl', 'CUserControl');
$udep = CUserDepControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$udepmbr = CUserDepMemberControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$usr = CUserControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$list = array();
$error = array();
if(isset($_REQUEST['id'])){
	foreach($_REQUEST['id'] as $k => $id){
		$id = intval($id);
		$udep->setPrimaryKey($id);
		$udepmbr->setPrimaryKey($id);
		if(isset($_REQUEST['delete'])){
			$ret = $udep->destroy();
			$ret = $udepmbr->destroy();
		}
		else if(isset($_REQUEST['edit_submit'])){
			$edit_info = array(
				'edit_time' => time(),
				'password'  => $_REQUEST['password'][$k],
				'name'      => $_REQUEST['name'][$k]
			);
			try {
				$ret = $udep->set($edit_info);
				$edit_info['id'] = $id;
				$list[] = $edit_info;
			} catch (Exception $e) {
				$error[] = $e->getMessage();
			}
		}
		else{
			$list[] = $udep->get();
		}
	}
	
	if(isset($_REQUEST['delete'])){
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success', 'common'), '', $mbs_appenv->toURL('department'));
		exit(0);
	}
}else{
	$list = array(array('name'=>'', 'password'=>''));
	$dep = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
	if(isset($_REQUEST['name'])){
		$_REQUEST['name'] = $_REQUEST['name'][0];
		$_REQUEST['password'] = $_REQUEST['password'][0];
		$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
		$dep = array_intersect_key($_REQUEST, $dep);
		if(empty($error)){
			$dep['edit_time'] = time();
			$id = $udep->add($dep);
			if(empty($id)){
				$error[] = $mbs_appenv->lang('dep_exists');
			}else{
				$dep = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
			}
		}
	}
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
.even{background-color:#eee;}
</style>
</head>
<body>
<div class="userManager">
	<h2 class="tit"><?php echo $mbs_appenv->lang(array('department', 'edit'))?>
    	<a href="<?php echo $mbs_appenv->toURL('department')?>" class="btn-create"><span class="back-icon"></span><?php echo $mbs_appenv->lang('back')?></a>
    </h2>
    
    <?php if(!empty($error)){ ?>
	<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
	<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
	</div>
	<?php }else if(isset($_REQUEST['edit_submit'])){ ?>
	<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?></div>
	<?php }?>
	
	<form method="post" name=_form action="">
	<?php foreach($list as $k=>$row){ if(isset($row['id'])){ ?>
	<input type="hidden" name="id[]" value="<?php echo $row['id']?>" />
	<?php }?>
	<div <?php echo 1==$k%2 ? 'class="even"' : ''?>>
	<div class="inpBox mb17">
   		<label for="name" class="labelL"><?php echo $mbs_appenv->lang('name')?>&nbsp;:&nbsp;</label>
	    <input id="name" class="inpTit" name="name[]" type="text" value="<?php echo $row['name']?>" 
	    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
    </div>
    <div class="inpBox mb17">
    	<label for="code" class="labelL"><?php echo $mbs_appenv->lang('password')?>&nbsp;:&nbsp;</label>
    	<input id="password" class="inpTit" name="password[]" type="text" value="<?php echo $row['password']?>" 
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
    </div>
    </div>
    <?php } ?>
    <div class="btnBox">
    	<input type="hidden" name="edit_submit" value="" />
    	<label for="code" class="labelL">&nbsp;</label>
        <a href="javascript:document._form.submit();" class="btn-send"><?php echo $mbs_appenv->lang('submit')?></a>
        <a href="<?php echo $mbs_appenv->toURL('department')?>" class="btn-cancle"><?php echo $mbs_appenv->lang('cancel')?></a>
    </div>
    </form>
    
</div>
</body>
</html>