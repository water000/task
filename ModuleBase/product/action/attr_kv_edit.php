<?php 

mbs_import('', 'CProductAttrKVControl');

$attr_kv_ctr = CProductAttrKVControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$page_title = isset($_REQUEST['kid']) ? 'edit' : 'add';
$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');

if(isset($_REQUEST['kid'])){
	$key = $attr_kv_ctr->key($_REQUEST['kid']);
	if(empty($key)){
		$mbs_appenv->echoex('invalid kid: '.$_REQUEST['kid'], 'PRODUCT_ATTR_KV_EDIT_INVALID_KID');
		exit(0);
	}
	$info['key'] = $key['value'];
	
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
	<div class="ptitle"><?php echo $mbs_appenv->lang(array($page_title, 'attr', 'kv'))?>
		<a class=back href="<?php echo $mbs_appenv->toURL('attr_kv_list')?>">&lt;<?php echo $mbs_appenv->lang(array('attr', 'kv', 'list'))?></a></div>
	<div class="">
	<form action="" class="pure-form pure-form-aligned" method="post" name="_form">
		<input type="hidden" name="_timeline" value="<?php echo time()?>" />
		<fieldset>
			<?php if(isset($_REQUEST['_timeline'])){ if(isset($error[0])){ ?>
			<div class=error>&times;<?php echo $error[0]?></div>
			<?php }else if(empty($error)){?>
			<div class=success><?php echo $mbs_appenv->lang('operation_success')?></div> 
			<?php }} ?>
						
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['key'])?></label>
				<input type="text" name="key" value="<?php echo CStrTools::txt2html($info['key'])?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['key'], $mbs_appenv)?></aside>
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