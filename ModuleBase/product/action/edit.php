<?php 
$page_title = 'add';
$error = array();
mbs_import('', 'CProductControl');

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
		$info = array_intersect_key($info, $_REQUEST);
		$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'), array('logo_path'));
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
				$pdt_ctr = CProductControl::getInstance($mbs_appenv,
						CDbPool::getInstance(), CMemcachedPool::getInstance());
				$ret = $pdt_ctr->set($info);
				if(empty($ret)){
					$error[] = $pdt_ctr->error();
				}
			}
		}
	}
	
}
else if(isset($_REQUEST['_timeline'])){
	$info = array_intersect_key($_REQUEST,$info);
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
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
		}
		if(empty($ret)){
			$error[] = $mbs_appenv->lang('error_on_field_exists').'('.$pdt_ctr->error().')';
		}
	}
	var_dump($error);
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
.logo-img{width:30px;height:30px;}
input,textarea{width:25%;}
.block{background-color:white;margin:10px 12px 0;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle block"><?php echo $mbs_appenv->lang(array($page_title, 'product'))?></div>
	<div class="block">
	<form class="pure-form pure-form-aligned" method="post" name="_form" enctype="multipart/form-data" >
		<input type="hidden" name="_timeline" value="<?php echo time()?>" />
		<fieldset>
			<?php if(!empty($error) && isset($error[0])){ ?><div class=pure-buton-error><?php echo $error[0]?></div><?php }?>
			<div class="pure-control-group">
                <label><?php echo $mbs_cur_actiondef[CModDef::P_ARGS]['en_name'][CModDef::G_TL]?></label>
                <input type="text" name="en_name" value="<?php echo $info['en_name']?>">
                <aside class="pure-form-message-inline"><?php echo $mbs_cur_actiondef[CModDef::P_ARGS]['en_name'][CModDef::G_DC]?></aside>
            </div>
            <div class="pure-control-group">
                <label><?php echo $mbs_cur_actiondef[CModDef::P_ARGS]['name'][CModDef::G_TL]?></label>
                <input type="text" name="name" value="<?php echo $info['name']?>">
                <aside class="pure-form-message-inline"><?php echo $mbs_cur_actiondef[CModDef::P_ARGS]['name'][CModDef::G_DC]?></aside>
            </div>
            <div class="pure-control-group">
                <label><?php echo $mbs_cur_actiondef[CModDef::P_ARGS]['abstract'][CModDef::G_TL]?></label>
                <textarea name="abstract"><?php echo $info['abstract']?></textarea>
                <aside class="pure-form-message-inline"><?php echo $mbs_cur_actiondef[CModDef::P_ARGS]['abstract'][CModDef::G_DC]?></aside>
            </div>
            <div class="pure-control-group">
                <label><?php echo $mbs_cur_actiondef[CModDef::P_ARGS]['logo_path'][CModDef::G_TL]?></label>
                <input type="file" name="logo_path" />
                <?php if(!empty($info['logo_path'])){ ?><img class=logo-img src="<?php echo $info['logo_path']?>" onclick="this.className=''" /><?php }?>
            </div>
            <div class="pure-control-group">
                <label><?php echo $mbs_cur_actiondef[CModDef::P_ARGS]['baike_link'][CModDef::G_TL]?></label>
                <input type="text" name="baike_link" value="<?php echo $info['baike_link']?>">
                <aside class="pure-form-message-inline"><?php echo $mbs_cur_actiondef[CModDef::P_ARGS]['baike_link'][CModDef::G_DC]?></aside>
            </div>
            <div class="pure-control-group">
                <label></label>
                <button type="submit" class="pure-button pure-button-primary" onclick="this.className +=' pure-input-disabled';this.innerHTML+='...';this.disabled=true;this.form.submit();"><?php echo $mbs_appenv->lang('submit')?></button>
            </div>
		</fieldset>
	</form>
	</div>
	<div class="footer block"></div>
</div>
<?php if(!empty($error)){?>
<script type="text/javascript">
function formSubmitErr(form, inputErr){
	var elems = form.elements, errctl, as, fnclk;
	fnclk = function(inp, _err, _as){
		var fnbind = inp['addEventListener']||inp['attachEvent'];
		fnbind.call(inp, 'click', function(e){
			this.style.border = "";
			_err.style.display = "none";
			if(_as)
				_as.style.display = "";
		});
	}
	for(var k in inputErr){
		if(typeof elems[k] != "undefined"){
			elems[k].style.border = "1px solid red";

			as = elems[k].parentNode.getElementsByTagName("aside")[0];
			if(as)
				as.style.display = "none";
			
			errctl = document.createElement("span");
			errctl.innerHTML = inputErr[k];
			errctl.style.cssText = "color:red;font-size:12px;";
			elems[k].parentNode.insertBefore(errctl, as);

			fnclk(elems[k], errctl, as);
		}
	}
}
formSubmitErr(document._form, <?php echo json_encode($error)?>);
</script>
<?php }?>

</body>
</html>