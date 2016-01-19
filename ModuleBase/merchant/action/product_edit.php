<?php 

mbs_import('', 'CMctControl', 'CMctProductControl', 'CMctProductLIUControl', 'CMctProductAttachmentControl');
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

$mct_pdt_liu_ctr = CMctProductLIUControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $merchant_id);
$pdt_inuse_list = $mct_pdt_liu_ctr->get();
if(!empty($pdt_inuse_list) && !isset($_REQUEST['product_id'])){
	$_REQUEST['product_id'] = $pdt_inuse_list[0];
}

if(isset($_REQUEST['product_id'])){
	$_REQUEST['product_id'] = intval($_REQUEST['product_id']);
	$pdt_ctr->setPrimaryKey($_REQUEST['product_id']);
	$pdt_info = $pdt_ctr->get();
	if(empty($pdt_info)){
		$mbs_appenv->echoex($mbs_appenv->lang('not_found'), 'MCT_PRODCT_EDIT_INVALID_PRODUCT_ID');
		exit(0);
	}
	
	$mct_pdt_ctr = CMctProductControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), $pdt_info['en_name'], $merchant_id);
	$mct_pdt_attch_ctr = CMctProductAttachmentControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), $pdt_info['en_name']);
	
	$pdt_attr_kv_ctr = CProductAttrKVControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	$pdt_attr_ctr = CProductAttrControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	$pdt_attr_map_ctr = CProductAttrMapControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['product_id']);
	$attr_list = $pdt_attr_map_ctr->get();
}

if(isset($_REQUEST['item'])){ // product_id & item must be set at same time
	$_REQUEST['item'] = intval($_REQUEST['item']);
	$mct_pdt_ctr->setSecondKey($_REQUEST['item']);
	$info = $mct_pdt_ctr->getNode();
	if(empty($info)){
		$mbs_appenv->echoex($mbs_appenv->lang('not_found'), 'MCT_PRODCT_EDIT_INVALID_ITEM_ID');
		exit(0);
	}
	$mct_pdt_attch_ctr->setPrimaryKey($_REQUEST['item']);
	$images = $mct_pdt_attch_ctr->get();
	if(isset($_REQUEST['del_atch'])){
		$img_count = count($images);
		if(0 == $img_count){
			$mbs_appenv->echoex('invalid operation', 'MCT_PRODUCT_EDIT_INVALID_OPER');
			exit(0);
		}
		else if(1 == $img_count){
			$mbs_appenv->echoex($mbs_appenv->lang('save_at_least_one_img'), 'MCT_PRODUCT_EDIT_INVALID_IMG_COUNT');
			exit(0);
		}
		$mct_pdt_attch_ctr->setSecondKey($_REQUEST['del_atch']);
		$img = $mct_pdt_attch_ctr->getNode();
		if(!empty($img)){
			$path = $mbs_appenv->uploadPath(CMctProductAttachmentControl::completePath($img['path']));
			if(file_exists($path)){
				unlink($path);
			}
			$ret = $mct_pdt_attch_ctr->delNode();
		}
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success'));
		exit(0);
	}
}

if(isset($_REQUEST['_timeline'])){
	if(!empty($attr_list)){
		foreach($attr_list as $row){
			$pdt_attr_ctr->setPrimaryKey($row['aid']);
			$attr_info = $pdt_attr_ctr->get();
			if(empty($attr_info)){
				trigger_error('attr not found: '.$row['aid']);
				continue;
			}
			if(isset($_REQUEST[$attr_info['en_name']])){
				if(empty($row['kid'])){
					if($row['required'] && 0 == strlen($_REQUEST[$attr_info['en_name']])){
						$error[$attr_info['en_name']] = $mbs_appenv->lang('invalid_param');
					}
				}else{
					$kv = $pdt_attr_kv_ctr->kv($row['kid']);
					if(!empty($kv[1])){
						$found = false;
						foreach($kv[1] as $v){
							if($v['id'] == $_REQUEST[$attr_info['en_name']]){
								$found = true;
								break;
							}
						}
						if(!$found){
							$error[$attr_info['en_name']] = $mbs_appenv->lang('invalid_param');
						}
					}else trigger_error('kv not found: '.$row['kid']);
				}
				if(!isset($info[$attr_info['en_name']])) $info[$attr_info['en_name']] = '';
			}else if($row['required']){
				$error[$attr_info['en_name']] = $mbs_appenv->lang('invalid_param');
			}
		}
	}
	if(isset($_REQUEST['item'])){
		$diff = array_diff_assoc(array_intersect_key($_REQUEST, $info), $info);
		if(!empty($diff)){
			$error += $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'), 
					array_merge(array('image'), array_keys(array_diff_key($info, $diff))));
			if(empty($error)){
				$diff['edit_time']   = time();
				unset($diff['image']);
				$info = $diff + $info;
				$mct_pdt_ctr->set($diff);
			}
		}
	}else{
		$error += $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
		if(empty($error)){
			$info_def = $info;
			$info = array_intersect_key($_REQUEST,$info) + $info;
			$info['merchant_id'] = $merchant_id;
			$info['edit_time']   = time();
			unset($info['image']);
			$mct_pdt_ctr->addNode($info);
			$mct_pdt_attch_ctr->setPrimaryKey($info['id']);
			$info = $info_def;
		}
	}
	$pdt_inuse_list = $mct_pdt_liu_ctr->set($_REQUEST['product_id']);
	
	if(empty($error)){
		for($i=0; $i<count($_FILES['image']['error']); ++$i){
			if(UPLOAD_ERR_OK == $_FILES['image']['error'][$i]){
				$img = array($_FILES['image']['tmp_name'][$i], $_FILES['image']['name'][$i]);
				$id = $mct_pdt_attch_ctr->addNode($img);
				if(isset($images)) $images[] = $img;
			}else if($_FILES['image']['error'][$i] != UPLOAD_ERR_NO_FILE){
				$error[] = $mbs_appenv->lang($_FILES['image']['error'][$i]);
			}
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
.btnlist-box{display:inline-block;}
.btnlist-box, #IDS_CONTAINER, input,textarea,select{width:300px;}
.popwin iframe{width:100%;height:90%;border:0;}
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
					$is_product_in_list = false;
					foreach($pdt_inuse_list as $pid){
						$pdt_ctr->setPrimaryKey($pid);
						$pdt_used = $pdt_ctr->get();
						if(!empty($pdt_used)){
							$is_product_in_list = $_REQUEST['product_id']==$pid;
				?>
					<a href="#" _checked="<?php echo $is_product_in_list ? '1':'0'?>" class="pure-button pure-button-check" 
						name="product_id" _value="<?php echo $pid?>" ><?php echo $pdt_used['name']?></a>
				<?php
						}
					}
					if(isset($pdt_info) && !$is_product_in_list){
						echo $pdt_info['name'];
					}
				?>
				<a href="#"><?php echo $mbs_appenv->lang('all')?></a>
				<?php } ?>
				</div>
			</div>
			<?php 
			if(isset($_REQUEST['product_id'])){
				foreach($attr_list as $row){
					$kv = $pdt_attr_kv_ctr->kv($row['kid']);
					if(!empty($kv)){
						$pdt_attr_ctr->setPrimaryKey($row['aid']);
						$attr_info = $pdt_attr_ctr->get();
						if(!empty($attr_info)){
						
			?>
			<div class="pure-control-group">
				<label><?php echo $kv[0]['value'], $row['required']?'<span class=required>*</span>':''?></label>
				<div  class="btnlist-box" name="<?php echo $attr_info['en_name']?>">
				<?php foreach($kv[1] as $v){?>
					<a href="#" _checked="<?php echo isset($info[$attr_info['en_name']]) && $info[$attr_info['en_name']]==$v['id'] ? '1':'0'?>"  
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
				<input type="text" name="title" value="<?php echo $info['title']?>" onkeyup="txtcounter(this)" <?php $rnum = CModDef::pargRange($mbs_cur_actiondef[CModDef::P_ARGS]['title'], $s, $e); echo $e>0?'_data-maximum='.$e:''?> />
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
                <span name="image" id=IDS_CONTAINER style="display:inline-block;">
                <?php if(isset($images)){foreach ($images as $img){ ?>
                <img src="<?php echo $mbs_appenv->uploadURL(CMctProductAttachmentControl::completePath($img['path']))?>" _data-id="<?php echo $img['id']?>" />
                <?php }}?>
                </span>
                <aside class="pure-form-message-inline"><?php echo $mbs_appenv->lang('upload_max_filesize'), 
					',', sprintf($mbs_appenv->lang('upload_max_filenum'), $max_upload_images)?></aside>
            </div>
			<?php if(isset($_REQUEST['id'])){?>
			<div class="pure-control-group">
                <label><?php $mbs_appenv->lang(array('edit', 'time'))?></label>
                <?php echo CStrTools::descTime($info['edit_time'], $mbs_appenv)?>
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
<?php if(!empty($error)){?>formSubmitErr(document._form, <?php echo json_encode($error)?>);<?php }?>
<?php if(isset($_REQUEST['product_id'])){ ?>
fileUpload({
	max_files:<?php echo $max_upload_images?>, 
	container:"IDS_CONTAINER", 
	file_name:"image[]", 
	onFileDel:function(file, onsuccess){
		<?php if(isset($_REQUEST['item'])){ ?>
		var aid = file.getAttribute("_data-id");
		if(aid != null){
			var pw = popwin("<?php echo $mbs_appenv->lang(array('delete', 'result'))?>", "<h2 style='text-align:center;color:green;'>loading...</h2>");
			pw.className += " popwin-s";
			pw.show();
			ajax({dataType:"json", url:"<?php echo $mbs_appenv->toURL('product_edit', '', array('product_id'=>$_REQUEST['product_id'], 'item'=>$_REQUEST['item']))?>&del_atch="+aid, 
				success:function(data){
					if("SUCCESS" == data.retcode){onsuccess();pw.body("<div class=success>"+data.data+"</div>");setTimeout(function(){pw.hide();}, 3000);}
					else{pw.body("<div class=error><?php echo $mbs_appenv->lang('error')?>"+data.error+"</div>");}
				}
			});
		}
		<?php } ?>
	}
});
<?php } ?>
var btnlist_box = document.getElementsByTagName("DIV"), i;
for(i=0; i<btnlist_box.length; i++){
	if("btnlist-box" == btnlist_box[i].className){
		btnlist(btnlist_box[i].getElementsByTagName("a"));
	}
}
</script>
</body>
</html>