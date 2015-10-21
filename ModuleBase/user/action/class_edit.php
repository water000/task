<?php 

mbs_import('', 'CUserClassControl');
$uc = CUserClassControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$list = array();
$error = array();
if(isset($_REQUEST['id'])){
	foreach($_REQUEST['id'] as $k => $id){
		$id = intval($id);
		$uc->setPrimaryKey($id);
		if(isset($_REQUEST['delete'])){
			if($id > CUserDef::BANNED_DEL_MAX_CLASS_ID)
				$uc->destroy();
		}
		else if(isset($_REQUEST['edit'])){
			$list[] = $uc->get();
		}
		else if(isset($_REQUEST['edit_submit'])){
			if(empty($_REQUEST['name'][$k]) || empty($_REQUEST['code'][$k])){
				$error[] = $k.'#'.$mbs_appenv->lang('miss_args');
				continue;
			}
			try {
				$ret = $uc->set(array(
					'name' => $_REQUEST['name'][$k],
					'code' => $_REQUEST['code'][$k]
				));
				if(!$ret){
					$error[] = $mbs_appenv->lang(array('name', 'or', 'class_code', 'existed'))
						.':'.$_REQUEST['name'][$k].':'.$_REQUEST['code'][$k];
				}
				$list[] = array('id'=>$id, 'name'=>$_REQUEST['name'][$k], 'code'=>$_REQUEST['code'][$k]);
			} catch (Exception $e) {
				$error[] = $e->getMessage();
			}
			
		}
	}
	
	if(isset($_REQUEST['delete'])){
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success', 'common'), '', $mbs_appenv->toURL('class'));
		exit(0);
	}
}else{
	$list = array(array('name'=>'', 'code'=>''));
	if(isset($_REQUEST['edit_submit'])){
		if(empty($_REQUEST['name'][0]) || empty($_REQUEST['code'][0])){
			$error[] = $mbs_appenv->lang('miss_args');
		}
		if(empty($error)){
			try {
				$ret = $uc->add(array(
					'name' => $_REQUEST['name'][0],
					'code' => $_REQUEST['code'][0],
					'create_time' => time()
				));
			} catch (Exception $e) {
				$error[] = $e->getMessage();
			}
			if(!$ret){
				$error[] = $mbs_appenv->lang(array('name', 'or', 'class_code', 'existed'))
					.':'.$_REQUEST['name'][0].':'.$_REQUEST['code'][0];;
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
	<h2 class="tit"><?php echo $mbs_appenv->lang(array('class', 'edit'))?>
    	<a href="<?php echo $mbs_appenv->toURL('class')?>" class="btn-create"><span class="back-icon"></span><?php echo $mbs_appenv->lang('back')?></a>
    </h2>
    
    <?php if(!empty($error)){ ?>
	<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
	<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
	</div>
	<?php }else if(isset($_REQUEST['edit_submit'])){ ?>
	<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?></div>
	<?php }?>
	
	<form method="post" name=_form action="<?php echo $mbs_appenv->toURL('class_edit')?>">
	<?php foreach($list as $k=>$row){ if(isset($row['id'])){ ?>
	<input type="hidden" name="id[]" value="<?php echo $row['id']?>" />
	<?php }?>
	<div <?php echo 1==$k%2 ? 'class="even"' : ''?>>
	<div class="inpBox mb17">
<<<<<<< HEAD
   		<label for="name" class="labelL"><?php echo $mbs_appenv->lang('name')?>&nbsp;:&nbsp;</label>
=======
   		<label for="name" class="labelL"><?php echo $mbs_appenv->lang('name', 'common')?>&nbsp;:&nbsp;</label>
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d
	    <input id="name" class="inpTit" name="name[]" type="text" value="<?php echo $row['name']?>" 
	    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
    </div>
    <div class="inpBox mb17">
    	<label for="code" class="labelL"><?php echo $mbs_appenv->lang('class_code')?>&nbsp;:&nbsp;</label>
    	<input id="code" class="inpTit" name="code[]" type="text" value="<?php echo $row['code']?>" 
		    	placeholder="<?php echo $mbs_appenv->lang('please_input')?>" required />
    </div>
    </div>
    <?php } ?>
    <div class="btnBox">
    	<input type="hidden" name="edit_submit" value="" />
    	<label for="code" class="labelL">&nbsp;</label>
        <a href="javascript:document._form.submit();" class="btn-send"><?php echo $mbs_appenv->lang('submit')?></a>
        <a href="<?php echo $mbs_appenv->toURL('class')?>" class="btn-cancle"><?php echo $mbs_appenv->lang('cancel')?></a>
    </div>
    </form>
    
</div>
</body>
</html>