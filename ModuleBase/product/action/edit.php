<?php 
$page_title = 'add';
$error = array();
mbs_import('', 'CProductControl');

if(isset($_GET['dosubmit']) && empty($_POST)){
	$error['logo_path'] = $mbs_appenv->lang('upload_max_filesize');
}

$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
if(isset($_REQUEST['id'])){
	$page_title = 'edit';
	
	$pdt_ctr = CProductControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
	$info = $pdt_ctr->get();
	if(empty($info)){
		$mbs_appenv->echoex('Invalid param', 'PRODUCT_EDIT_INVALID_PARAM');
		exit(0);
	}
	if(isset($_REQUEST['_timeline'])){
		$info = array_intersect_key($_REQUEST, $info) + $info;
		$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'), array('logo_path'));
		if(!isset($error['en_name']) && !CStrTools::isWord($info['en_name'])){
			$error['en_name'] = $mbs_appenv->lang('invalid_EN_word');
		}
		if(empty($error)){
			if(isset($_FILES['logo_path']) && UPLOAD_ERR_OK == $_FILES['logo_path']['error']){
				$logo_path = CProductControl::moveLogo($_FILES['logo_path']['tmp_name'], $mbs_appenv);
				if($logo_path){
					CProductControl::unlinklogo($info['logo_path'], $mbs_appenv);
					$info['logo_path'] = $logo_path;
				}else{
					$error['logo_path'] = 'failed to thumbnail logo';
				}
			}
			if(empty($error)){
				$info['last_edit_time'] = time();
				$ret = $pdt_ctr->set($info);
				if(empty($ret)){
					$error[] = $pdt_ctr->error();
				}
			}
		}
	}
}
else if(isset($_REQUEST['_timeline'])){	
	$info_def = $info;
	$info = array_intersect_key($_REQUEST,$info) + $info;
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	if(!isset($error['en_name']) && !CStrTools::isWord($info['en_name'])){
		$error['en_name'] = $mbs_appenv->lang('invalid_EN_word');
	}
	if(empty($error)){
		$logo_path = CProductControl::moveLogo($_FILES['logo_path']['tmp_name'], $mbs_appenv);
		if($logo_path){
			$info['logo_path'] = $logo_path;
		}else{
			$error['logo_path'] = 'failed to thumbnail logo';
		}
		
		if(empty($error)){
			$pdt_ctr = CProductControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
			$info['last_edit_time'] = $info['create_time'] = time();
			$ret = $info_id = $pdt_ctr->add($info);
			if(empty($ret)){
				$error[] = $mbs_appenv->lang('error_on_field_exists').'('.$pdt_ctr->error().')';
			}else{
				$info = $info_def;
			}
		}
	}
}
if(!empty($info['logo_path'])){
	$info['logo_path'] = CProductControl::logourl($info['logo_path'], $mbs_appenv);
}
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet"> 
<style type="text/css">
aside {display:none;color:red;font-size:12px;}
.form-fld-img{width:30px;height:30px;}
input,textarea{width:300px;}
textarea{height:85px;}
.block{background-color:white;margin:10px 12px 0;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array($page_title, 'product'))?>
		<a class=back href="<?php echo $mbs_appenv->toURL('list')?>">&lt;<?php echo $mbs_appenv->lang(array('product', 'list'))?></a></div>
	<div class="">
	<form action="<?php echo $mbs_appenv->newURI(array('dosubmit'=>1)) ?>" class="pure-form pure-form-aligned" method="post" name="_form" enctype="multipart/form-data" >
		<input type="hidden" name="_timeline" value="<?php echo time()?>" />
		<fieldset>
			<?php if(isset($_REQUEST['_timeline'])){ if(isset($error[0])){ ?>
			<div class=error>&times;<?php echo $error[0]?></div>
			<?php }else if(empty($error)){?>
			<div class=success><?php echo $mbs_appenv->lang('operation_success')?></div> 
			<?php }} ?>
						
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['en_name'])?></label>
				<input type="text" name="en_name" value="<?php echo $info['en_name']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['en_name'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['name'])?></label>
				<input type="text" name="name" value="<?php echo $info['name']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['name'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['abstract'])?></label>
				<textarea name="abstract"><?php echo $info['abstract']?></textarea>
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['abstract'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['logo_path'])?></label>
				<input type="file" name="logo_path" /><aside class="pure-form-message-inline"><?php echo $mbs_appenv->lang('upload_max_filesize')?></aside>
				<?php if(!empty($info['logo_path'])){?><img class=form-fld-img src="<?php echo $info['logo_path']?>" /><?php }?>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['baike_link'])?></label>
				<input type="text" name="baike_link" value="<?php echo $info['baike_link']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['baike_link'], $mbs_appenv)?></aside>
			</div>
			<?php if(isset($_REQUEST['id'])){?>
			<div class="pure-control-group">
                <label><?php echo $mbs_appenv->lang(array('add', 'time'))?></label>
                <?php echo CStrTools::descTime($info['create_time'], $mbs_appenv)?>
            </div>
            <?php }?>
            <div class="pure-control-group">
                <label></label>
                <button type="submit" class="pure-button pure-button-primary" onclick="submitForm(this)"><?php echo $mbs_appenv->lang('submit')?></button>
            </div>
		</fieldset>
	</form>
	</div>
	<div class="footer"></div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('global.js')?>"></script>
<?php if(!empty($error)){?>
<script type="text/javascript">
formSubmitErr(document._form, <?php echo json_encode($error)?>);
</script>
<?php }?>
</body>
</html>