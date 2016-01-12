<?php 

mbs_import('', 'CMctControl', 'CMctProductControl', 'CMctProductMapControl', 'CMctProductAttachmentControl');
mbs_import('product', 'CProductControl', 'CProductAttrMapControl', 'CProductAttrKVControl', 'CProductAttrControl');
mbs_import('user', 'CUserSession');

$page_title = 'add';
$max_upload_images = $mbs_appenv->config('mct_max_upload_images');

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
	
	$mct_pdt_attch_ctr = CMctProductAttachmentControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), $pdt_info['en_name']);
}

if(isset($_REQUEST['item'])){ // product_id & item must be set at same time
	$_REQUEST['item'] = intval($_REQUEST['item']);
	$mct_pdt_ctr = CMctProductControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $pdt_info['en_name']);
	$mct_pdt_ctr->setPrimaryKey($_REQUEST['item']);
	$info = $mct_pdt_ctr->get();
	if(empty($info)){
		$mbs_appenv->echoex($mbs_appenv->lang('not_found'), 'MCT_PRODCT_EDIT_INVALID_ITEM_ID');
		exit(0);
	}
	$mct_pdt_attch_ctr->setPrimaryKey($_REQUEST['item']);
	$images = $mct_pdt_attch_ctr->get();
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet"> 
<style type="text/css">
.btnlist-box{display:inline-block;}
.btnlist-box, #IDS_CONTAINER, input,textarea,select{width:300px;}
textarea{height:85px;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array($page_title, 'product'))?>
		<a class=back href="<?php echo $mbs_appenv->toURL('attr_list')?>">&lt;<?php echo $mbs_appenv->lang(array('product', 'list'))?></a></div>
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
				<div class="btnlist-box">
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
					$is_product_in_list = false;
					foreach($pdt_list as $row){
						$pdt_ctr->setPrimaryKey($row['product_id']);
						$pdt_used = $pdt_ctr->get();
						if(!empty($pdt_used)){
							$is_product_in_list = $_REQUEST['product_id']==$row['id'];
				?>
					<a href="#" _checked="<?php echo $is_product_in_list ? '1':'0'?>" 
						class="pure-button pure-button-check" name="product_id" value="<?php echo $row['id']?>" ><?php echo $row['name']?></a>
				<?php
						}
					}
					if(!$is_product_in_list){
						echo $pdt_info['name'];
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
				<div  class="btnlist-box">
				<?php foreach($kv[1] as $v){?>
					<a href="#" _checked="<?php echo isset($info[$attr_info['en_name']]) && $info[$attr_info['en_name']]==$row['id'] ? '1':'0'?>"  
						class="pure-button pure-button-check" name="<?php echo $attr_info['en_name']?>" _value="<?php echo $v['id']?>"><?php echo $v['value']?></a>
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
			<div class="pure-control-group">
                <label style="vertical-align: top;"><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['image'])?></label>
                <span id=IDS_CONTAINER style="display:inline-block;">
                <?php if(isset($images)){foreach ($images as $img){ ?>
                <img src="<?php echo $mbs_appenv->uploadURL(CMctAttachmentControl::completePath($img['path']))?>" _data-id="<?php echo $img['id']?>" />
                <?php }}?>
                </span>
                <aside class="pure-form-message-inline"><?php echo $mbs_appenv->lang('upload_max_filesize'), 
					',', sprintf($mbs_appenv->lang('upload_max_filenum'), $max_upload_images)?></aside>
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
fileUpload({
	max_files:<?php echo $max_upload_images?>, 
	container:"IDS_CONTAINER", 
	file_name:"image[]", 
	onFileDel:function(file){
		var id = <?php echo isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;?>;
		if(0 == id) return;
		var f = document.createElement("form");
		f.method = "post";
		f.innerHTML = "<input type='hidden' name='id' value='"+id+"' />"+
			"<input type='hidden' name='_timeline' value='' />"+
			"<input type='hidden' name='delete' value='' />"+
			"<input type='hidden' name='aid' value='"+file.getAttribute("_data-id")+"' />";
		document.body.appendChild(f);
		f.submit();
	}
});
var btnlist_box = document.getElementsByTagName("DIV"), i;
for(i=0; i<btnlist_box.length; i++){
	if("btnlist-box" == btnlist_box[i].className){
		btnlist(btnlist_box[i].getElementsByTagName("a"));
	}
}
</script>

</body>
</html>