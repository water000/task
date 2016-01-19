<?php 

mbs_import('', 'CProductAttrKVControl');

$attr_kv_ctr = CProductAttrKVControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$page_title = isset($_REQUEST['kid']) ? 'edit' : 'add';
$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
$info['first_char'] = '';

if(isset($_REQUEST['kid'])){
	$key = $attr_kv_ctr->key($_REQUEST['kid']);
	if(empty($key)){
		$mbs_appenv->echoex('invalid kid: '.$_REQUEST['kid'], 'PRODUCT_ATTR_KV_EDIT_INVALID_KID');
		exit(0);
	}
	$info['key'] = $key['value'];
	$info['first_char'] = $key['first_char'];
	
	$attr_kv_ctr->setPrimaryKey($_REQUEST['kid']);
	$info['value'] = $attr_kv_ctr->getDB()->getAll()->fetchAll(PDO::FETCH_ASSOC);
}

if(isset($_REQUEST['_timeline'])){
	list($key, $first_char) = explode(' ', $_REQUEST['key']);
	$first_char = strtoupper($first_char);
	if(empty($info['key'])){
		$_REQUEST['kid'] = $attr_kv_ctr->add(array(
			'kid'        => CProductAttrKVControl::KEY_PID,
			'value'      => $key,
			'first_char' => $first_char,
		));
		$attr_kv_ctr->setPrimaryKey($_REQUEST['kid']);
	}else if($info['key'].' '.$info['first_char'] != $_REQUEST['key']){
		$attr_kv_ctr->setPrimaryKey(CProductAttrKVControl::KEY_PID);
		$attr_kv_ctr->setSecondKey($_REQUEST['kid']);
		$attr_kv_ctr->setNode(array('value'=>$key, 'first_char'=>$first_char));
	}
	$info['key'] = $key;
	$info['first_char'] = $first_char;
	
	foreach($info['value'] as $k=>&$v){
		$attr_kv_ctr->setPrimaryKey($_REQUEST['kid']);
		if(isset($_REQUEST['value-'.$v['id']]) 
			&& $v['value'].' '.$v['first_char'] != trim($_REQUEST['value-'.$v['id']])){
			$attr_kv_ctr->setSecondKey($v['id']);
			list($key, $first_char) = explode(' ', trim($_REQUEST['value-'.$v['id']]));
			$attr_kv_ctr->setNode(array('value'=>$key, 'first_char'=>strtoupper($first_char)));
			$v['value'] = $key;
			$v['first_char'] = strtoupper($first_char);
		}
		else if(isset($_REQUEST['del-'.$v['id']])){
			$attr_kv_ctr->setSecondKey($v['id']);
			$attr_kv_ctr->delNode();
			unset($info['value'][$k]);
		}
	}
	
	if(!empty($_REQUEST['value'])){
		foreach(explode("\r\n", $_REQUEST['value']) as $val){
			list($key, $first_char) = explode(' ', trim($val));
			$arr = array(
					'kid'        => $_REQUEST['kid'],
					'value'      => $key,
					'first_char' => strtoupper($first_char),
			);
			$arr['id'] = $attr_kv_ctr->add($arr);
			$info['value'][] = $arr;
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
input,textarea{width:180px;}
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
				<input type="text" name="key" value="<?php echo empty($info['key']) ? '' : CStrTools::txt2html($info['key']).' '.$info['first_char']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['key'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['value'])?></label>
				<div style="display: inline-block">
					<?php if(!empty($info['value'])){ ?>
					<ul id=IDD_MULTI_OPTS><?php foreach($info['value'] as $val){?>
						<li><input type="text" name="value-<?php echo $val['id']?>" value="<?php echo CStrTools::txt2html($val['value']), ' ', $val['first_char']?>" />
							<a class="pure-button pure-button-check" name="del-<?php echo $val['id']?>[]" _value="1"><?php echo $mbs_appenv->lang('delete')?></a></li>
					<?php }?></ul>
					<?php } ?>
					<textarea name="value"></textarea>
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
<?php if(!empty($info['value'])){ ?>
btnlist(document.getElementById("IDD_MULTI_OPTS").getElementsByTagName("a"));
<?php } ?>
</script>
</body>
</html>