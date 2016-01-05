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
	
	$attr_kv_ctr->setPrimaryKey($_REQUEST['kid']);
	$info['value'] = $attr_kv_ctr->getDB()->getAll()->fetchAll(PDO::FETCH_ASSOC);
}

if(isset($_REQUEST['_timeline'])){	
	if(empty($info['key'])){
		$_REQUEST['kid'] = $attr_kv_ctr->add(array(
			'kid'   => CProductAttrKVControl::KEY_PID,
			'value' => $_REQUEST['key']
		));
		$attr_kv_ctr->setPrimaryKey($_REQUEST['kid']);
	}else if($info['key'] != $_REQUEST['key']){
		$attr_kv_ctr->setPrimaryKey(CProductAttrKVControl::KEY_PID);
		$attr_kv_ctr->setSecondKey($_REQUEST['kid']);
		$attr_kv_ctr->setNode(array('value'=>$_REQUEST['key']));
	}
	$info['key'] = $_REQUEST['key'];
	
	if(!empty($_REQUEST['value'])){
		foreach(explode("\r\n", $_REQUEST['value']) as $v){
			$arr = array(
				'kid'   => $_REQUEST['kid'],
				'value' => trim($v)
			);
			$arr['id'] = $attr_kv_ctr->add($arr);
			$info['value'][] = $arr;
		}
	}
	
	foreach($info['value'] as $k=>&$v){
		$attr_kv_ctr->setPrimaryKey($_REQUEST['kid']);
		if(isset($_REQUEST['value-'.$v['id']]) 
			&& $v['value'] != trim($_REQUEST['value-'.$v['id']])){
			$attr_kv_ctr->setSecondKey($v['id']);
			$v['value'] = trim($_REQUEST['value-'.$v['id']]);
			$attr_kv_ctr->setNode(array('value'=>$v['value']));
		}
		else if(isset($_REQUEST['del-'.$v['id']])){
			$attr_kv_ctr->setSecondKey($v['id']);
			$attr_kv_ctr->delNode();
			unset($info['value'][$k]);
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
input,textarea{width:150px;}
textarea{height:85px;height:110px;}
ul{margin:0;padding:0;}
.warpper ul li{border:0;padding:0;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array($page_title, 'attr', 'kv'))?>
		<a class=back href="<?php echo $mbs_appenv->toURL('attr_kv_list')?>">&lt;<?php echo $mbs_appenv->lang(array('attr', 'kv', 'list'))?></a></div>
	<div class="">
	<form action="" class="pure-form pure-form-aligned" method="post" name="_form">
		<input type="hidden" name="_timeline" value="<?php echo time()?>" />
		<?php if(isset($_REQUEST['kid'])){?><input type="hidden" name="kid" value="<?php echo $_REQUEST['kid']?>" /><?php }?>
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
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['value'])?></label>
				<div style="display: inline-block">
					<textarea name="value"></textarea>
					<?php if(!empty($info['value'])){ ?>
					<ul id=IDD_MULTI_OPTS><?php foreach($info['value'] as $v){?>
						<li><input type="text" name="value-<?php echo $v['id']?>" value="<?php echo CStrTools::txt2html($v['value'])?>" />
							<a class="pure-button pure-button-check" name="del-<?php echo $v['id']?>[]" _value="1"><?php echo $mbs_appenv->lang('delete')?></a></li>
					<?php }?></ul>
					<?php } ?>
				</div>
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['value'], $mbs_appenv)?></aside>
			</div>
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