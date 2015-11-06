<?php 
$page_title = 'add';
$error = array();
mbs_import('', 'CProductAttrControl');

$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
if(isset($_REQUEST['id'])){
	$page_title = 'edit';
	
	$pdtattr_ctr = CProductAttrControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
	$info_def = $info = $pdtattr_ctr->get();
	if(empty($info)){
		$mbs_appenv->echoex('Invalid param', 'PRODUCT_EDIT_INVALID_PARAM');
		exit(0);
	}
	if(isset($_REQUEST['_timeline'])){
		$info = array_intersect_key($_REQUEST, $info) + $info;
		$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
		if(!isset($error['en_name']) && !CStrTools::isWord($info['en_name'])){
			$error['en_name'] = $mbs_appenv->lang('invalid_EN_word');
		}
		if(empty($error)){
			$info['last_edit_time'] = time();
			$ret = $pdtattr_ctr->set($info);
			if(empty($ret)){
				$error[] = $pdtattr_ctr->error();
			}else{
				$ev_args = array('new'=>$info, 'src_name'=>$info_def['en_name']);
				mbs_import('common', 'CEvent');
				CEvent::trigger('attr_changed', $ev_args, $mbs_appenv);
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
		$pdtattr_ctr = CProductAttrControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$info['last_edit_time'] = $info['create_time'] = time();
		$ret = $info_id = $pdtattr_ctr->add($info);
		if(empty($ret)){
			$error[] = $mbs_appenv->lang('error_on_field_exists').'('.$pdtattr_ctr->error().')';
		}else{
			$info = $info_def;
		}
		
	}
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
input,textarea,select{width:300px;}
textarea{height:85px;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array($page_title, 'attr'))?>
		<a class=back href="<?php echo $mbs_appenv->toURL('attr_list')?>">&lt;<?php echo $mbs_appenv->lang(array('attr', 'list'))?></a></div>
	<div class="">
	<form class="pure-form pure-form-aligned" method="post" name="_form" enctype="multipart/form-data" >
		<input type="hidden" name="_timeline" value="<?php echo time()?>" />
		<fieldset>
			<?php if(isset($_REQUEST['_timeline'])){ if(isset($error[0])){ ?>
			<div class=error><?php echo $error[0];unset($error[0])?></div>
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
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['value_type'])?></label>
				<select name="value_type">
					<option value=0><?php echo $mbs_appenv->lang('choose_please')?></option>
					<?php foreach(CProductAttrControl::vtmap() as $key => $v){?>
					<option value="<?php echo $key?>" <?php echo $key==$info['value_type']?' SELECTED':''?>><?php echo $v?></option>
					<?php }?>
				</select>
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['value_type'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['unit_or_size'])?></label>
				<input type="text" name="unit_or_size" value="<?php echo $info['unit_or_size']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['unit_or_size'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group" id="IDD_MULTI_OPTS">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['value_opts'])?></label>
				<input type="text" name="value_opts" value="<?php echo $info['value_opts']?>" />
				<a class="pure-button pure-button-check" name="allow_multi" _value="1" _checked="<?php echo '1'==$info['allow_multi']?'1':'0'?>">
					<?php echo $mbs_appenv->lang('allow_multi')?></a>
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['value_opts'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['default_value'])?></label>
				<input type="text" name="default_value" value="<?php echo $info['default_value']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['default_value'], $mbs_appenv)?></aside>
			</div>
			<?php if(isset($_REQUEST['id'])){?>
			<div class="pure-control-group">
                <label><?php $mbs_appenv->lang(array('add', 'time'))?></label>
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
<script type="text/javascript">
<?php if(!empty($error)){?>
formSubmitErr(document._form, <?php echo json_encode($error)?>);
<?php }?>
btnlist(document.getElementById("IDD_MULTI_OPTS").getElementsByTagName("a"));
</script>

</body>
</html>