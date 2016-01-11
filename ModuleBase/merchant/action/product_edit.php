<?php 

mbs_import('', 'CMctControl', 'CMctProductControl', 'CMctProductMapControl', 'CMctProductAttachmentControl');
mbs_import('product', 'CProductControl', 'CProductAttrMapControl', 'CProductAttrKVControl', 'CProductAttrControl');
mbs_import('user', 'CUserSession');

$page_title = 'add';

$us = new CUserSession();
$user_info = $us->get();

//$merchant_id = $user_info['merchant_id'];
$merchant_id = 1;

$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');

$pdt_ctr = CProductControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

if(isset($_REQUEST['product_id'])){
	$_REQUEST['product_id'] = intval($_REQUEST['product_id']);
	$pdt_ctr->setPrimaryKey($_REQUEST['product_id']);
	$pdt_info = $pdt_ctr->get();
	if(empty($pdt_info)){
		$mbs_appenv->echoex($mbs_appenv->lang('not_found'), 'MCT_PRODCT_EDIT_INVALID_PRODUCT_ID');
		exit(0);
	}
}

if(isset($_REQUEST['item'])){ // product_id & item must be set at same time
	$_REQUEST['item'] = intval($_REQUEST['item']);
	$mct_pdt_ctr = CMctProductControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $pdt_info['en_name']);
	$mct_pdt_ctr->setPrimaryKey($_REQUEST['item']);
	$item_info = $mct_pdt_ctr->get();
	if(empty($item_info)){
		$mbs_appenv->echoex($mbs_appenv->lang('not_found'), 'MCT_PRODCT_EDIT_INVALID_ITEM_ID');
		exit(0);
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
				<label><?php echo $mbs_appenv->lang('product')?></label>
				<div style="display: inline-block;">
				<?php
				if(isset($_REQUEST['item'])){
					echo $pdt_info['name'];
				}else{
					$mct_pdt_map_ctr = CMctProductMapControl::getInstance($mbs_appenv, 
						CDbPool::getInstance(), CMemcachedPool::getInstance(), $merchant_id);
					$pdt_list = $mct_pdt_map_ctr->get();
					if(!isset($_REQUEST['product_id']) && !empty($pdt_list)){
						$_REQUEST['product_id'] = $pdt_list[0]['product_id'];
					}
					foreach($pdt_list as $row){
						$pdt_ctr->setPrimaryKey($row['product_id']);
						$pdt_used = $pdt_ctr->get();
						if(!empty($pdt_used)){
				?>
					<a href="#" _checked="<?php echo $_REQUEST['product_id']==$row['id'] ? '1':'0'?>" 
						class="pure-button pure-button-check" name="product_id" value="<?php echo $row['id']?>" ><?php echo $row['name']?></a>
				<?php
						}
					}
				}
				?>
					<a href="#"><?php echo $mbs_appenv->lang('all')?></a>
				</div>
			</div>
			<?php 
			if(isset($_REQUEST['product_id'])){
				$pdt_attr_kv_ctr = CProductAttrKVControl::getInstance($mbs_appenv, 
					CDbPool::getInstance(), CMemcachedPool::getInstance());
				$pdt_attr_ctr = CProductAttrControl::getInstance($mbs_appenv, 
					CDbPool::getInstance(), CMemcachedPool::getInstance());
				$pdt_attr_map_ctr = CProductAttrMapControl::getInstance($mbs_appenv, 
					CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['product_id']);
				$attr_list = $pdt_attr_map_ctr->get();
				foreach($attr_list as $row){
					$kv = $pdt_attr_kv_ctr->kv($row['kid']);
					if(!empty($kv)){
						$pdt_attr_ctr->setPrimaryKey($row['aid']);
						$attr_info = $pdt_attr_ctr->get();
						if(!empty($attr_info)){
						
			?>
			<div class="pure-control-group">
				<label><?php echo $kv[0]['value'], $row['required']?'<span class=required>*</span>':''?></label>
				<div style="display: inline-block;">
				<?php foreach($kv[1] as $v){?>
					<a href="#" _checked="<?php echo $_REQUEST['product_id']==$row['id'] ? '1':'0'?>"  
						class="pure-button pure-button-check" value="<?php $v['id']?>"><?php echo $v['value']?></a>
				<?php }?>
				</div>
			</div>
			<?php
						}
					}
				}
			?>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['title'])?></label>
				<input type="text" name="title" value="<?php echo $info['title']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['title'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['inventory'])?></label>
				<input type="text" name="inventory" value="<?php echo $info['inventory']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['inventory'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['discount_price'])?></label>
				<input type="text" name="discount_price" value="<?php echo $info['discount_price']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['discount_price'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['src_price'])?></label>
				<input type="text" name="src_price" value="<?php echo $info['src_price']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['src_price'], $mbs_appenv)?></aside>
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
            <?php }?>
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
</script>

</body>
</html>