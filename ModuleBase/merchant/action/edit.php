<?php 
$page_title = 'add';
$error = array();

if(isset($_GET['dosubmit']) && empty($_POST)){
	$error[] = $mbs_appenv->lang('upload_max_filesize');
}

mbs_import('', 'CMctControl', 'CMctAttachmentControl');
mbs_import('user', 'CUserSession');

$max_upload_images = $mbs_appenv->config('mct_max_upload_images');
$allow_edit = true;
$usess = new CUserSession();
list($sess_uid,) = $usess->get();

$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
$images = array();
if(isset($_REQUEST['id'])){
	$page_title = 'edit';
	
	$mct_ctr = CMctControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
	$info = $mct_ctr->get();
	if(empty($info)){
		$mbs_appenv->echoex('Invalid param', 'MERCHANT_EDIT_INVALID_PARAM');
		exit(0);
	}
	
	if($info['owner_id'] != $sess_uid)
		$allow_edit = false;
	
	$mct_atch_ctr = CMctAttachmentControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
	$images = $mct_atch_ctr->get();
	$images = empty($images) ? array() : $images;
	
	if(isset($_REQUEST['_timeline']) && $allow_edit){
		if(isset($_REQUEST['delete']) && isset($_REQUEST['aid'])){
			foreach($images as $k => $img){
				if($img['id'] == $_REQUEST['aid']){
					$path = $mbs_appenv->uploadPath(CMctAttachmentControl::completePath($img['path']));
					if(file_exists($path)){
						unlink($path);
					}
					$mct_atch_ctr->setSecondKey($img['id']);
					$mct_atch_ctr->delNode();
					unset($images[$k]);
					break;
				}
			}
		}else{
			$info = array_intersect_key($_REQUEST, $info) + $info;
			$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'), array('image'));
			if(empty($error)){
				unset($info['image']);
				$info['edit_time'] = time();
				try {
					$ret = $mct_ctr->set($info);
					
					for($i=0; $i<count($_FILES['image']['error']); ++$i){
						if(UPLOAD_ERR_OK == $_FILES['image']['error'][$i]){
							$img = array($_FILES['image']['tmp_name'][$i], $_FILES['image']['name'][$i]);
							$id = $mct_atch_ctr->addEx($img);
							$images[] = $img;
						}else if($_FILES['image']['error'][$i] != UPLOAD_ERR_NO_FILE){
							$error[] = $mbs_appenv->lang($_FILES['image']['error'][$i]);
						}
					}
				} catch (Exception $e) {
					if($mbs_appenv->config('PDO_ER_DUP_ENTRY', 'common') == $e->getCode()){
						$error['name'] = sprintf('"%s" %s', $info['name'], $mbs_appenv->lang('existed'));
					}else{
						$error[] = $mbs_appenv->lang('db_exception');
						mbs_error_log($e.getMessage()."\n".$e->getTraceAsString(), __FILE__, __LINE__);
					}
				}
			}
		}
	}
}
else if(isset($_REQUEST['_timeline'])){	
	$info_def = $info;
	$info = array_intersect_key($_REQUEST,$info) + $info;
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	
	if(empty($error)){
		$mct_ctr = CMctControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
		unset($info['image']);
		$info['status'] = CMctControl::convStatus('verify');
		$info['owner_id'] = $sess_uid;
		$info['edit_time'] = $info['create_time'] = time();
		$merchant_id = 0;
		try {
			$merchant_id = $mct_ctr->add($info);
			
			$mct_atch_ctr = CMctAttachmentControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance(), $merchant_id);
			for($i=0; $i<count($_FILES['image']['error']); ++$i){
				if(UPLOAD_ERR_OK == $_FILES['image']['error'][$i]){
					$img = array($_FILES['image']['tmp_name'][$i], $_FILES['image']['name'][$i]);
					$id = $mct_atch_ctr->addEx($img);
				}else if($_FILES['image']['error'][$i] != UPLOAD_ERR_NO_FILE){
					$error['image'] = $mbs_appenv->lang($_FILES['image']['error'][$i]);
				}
			}
				
			$info = $info_def;
		} catch (Exception $e) {
			if($mbs_appenv->config('PDO_ER_DUP_ENTRY', 'common') == $e->getCode()){
				$error['name'] = sprintf('"%s" %s', $info['name'], $mbs_appenv->lang('existed'));
			}else{
				$error[] = $mbs_appenv->lang('db_exception');
				mbs_error_log($e->getMessage()."\n".$e->getTraceAsString(), __FILE__, __LINE__);
			}
		}
	}
}

if($mbs_appenv->item('client_accept') != 'html'){
	if(count($error) > 0)
		$mbs_appenv->echoex($error, 'MCT_EDIT_ERROR');
	else {
		$info['images'] = $images;
		$mbs_appenv->echoex($info);
	}
	exit(0);
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
#IDS_CONTAINER, input,textarea{width:380px;}
textarea{height:85px;}
.block{background-color:white;margin:10px 12px 0;}
.map-ctr{display:inline-block;width:380px; height:220px;}
.map-ctr-bigger{width:500px; height:300px;}

#img-lab-bg{width:67px ;height:67px ;position:relative;display:inline-block;overflow: hidden;margin:0 3px 0 0;}
#img-lab{position:absolute;top:0;left:0;line-height:55px;font-size:50px;text-align:center; width:65px;height:65px; border-radius:5px;}
.img-lab-add{color:#7DB8EC;border:1px dashed #ccc;background-color:#fff;overflow:hidden;}
.img-lab-del{color:red;border:1px dashed red;overflow:hidden;visibility:hidden;}
.img-name{position:absolute;bottom:0;left:0;margin:1px;font-size:12px;width: 63px;overflow: hidden;text-align:center;}
#img-lab-bg input{width:10px;margin:2px;float:right;border:0;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array($page_title))?>
		<a class=back href="<?php echo $mbs_appenv->toURL('list')?>">&lt;<?php echo $mbs_appenv->lang(array( 'list'))?></a></div>
	<div class="">
	<form name=_form action="<?php echo $mbs_appenv->newURI(array('dosubmit'=>1)) ?>" class="pure-form pure-form-aligned" method="post" name="_form" enctype="multipart/form-data" >
		<input type="hidden" name="_timeline" value="<?php echo time()?>" />
		<input type="hidden" name="lng_lat" value="" />
		<input type="hidden" name="address" value="" />
		<input type="hidden" name="area" value="" />
		<fieldset>
			<?php if(isset($_REQUEST['_timeline'])){ if(isset($error[0])){ ?>
			<div class=error><?php echo $error[0]?></div>
			<?php unset($error[0]);}else if(empty($error)){?>
			<div class=success id=IDD_SUCC_BOX><?php echo $mbs_appenv->lang('operation_success')?></div> 
			<script type="text/javascript">setTimeout(function(){var box=document.getElementById("IDD_SUCC_BOX");box.parentNode.removeChild(box);}, 3500);</script>
			<?php }} ?>
			
			<div class="pure-control-group">
				<label style="vertical-align: top;"><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['lng_lat'])?></label>
				<span class="map-ctr"><div id="IDD_MAP" style="width:100%;height: 100%;"></div></span>
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
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['telephone'])?></label>
				<input type="text" name="telephone" value="<?php echo $info['telephone']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['telephone'], $mbs_appenv)?></aside>
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
                <label><?php echo $mbs_appenv->lang(array('add', 'time'))?></label>
                <?php echo CStrTools::descTime($info['create_time'], $mbs_appenv)?>
            </div>
            <?php }?>
            <?php if($allow_edit){ ?>
            <div class="pure-control-group">
                <label></label>
                <button type="submit" class="pure-button pure-button-primary" onclick="submitForm(this)"><?php echo $mbs_appenv->lang('submit')?></button>
            </div>
            <?php } ?>
		</fieldset>
	</form>
	</div>
	<div class="footer"></div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('global.js')?>"></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=S8mKcAyeY2sq2aH7SmsGSHep"></script>
<script type="text/javascript">
<?php 
if(!empty($error)){ 
	$uerr = array();
	if(isset($error['image'])){
		$uerr['image'] = $error['image']; 
		unset($error['image']);
	}
	if(isset($error['address'])){
		$uerr['address'] = $error['address'];
		unset($error['address'], $error['area'], $error['lng_lat']);
	}
	if(!empty($uerr)){
?>
formSubmitErr({image:document.getElementById("IDS_CONTAINER"), address:document.getElementById("IDD_MAP")}, 
	<?php echo json_encode($uerr)?>);
<?php
	}
?>
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
function _on_submit(pt, area, address, map){
	//document.getElementById("IDD_MAP").className = "map-ctr";
	document._form.elements["address"].value = address;
	document._form.elements["area"].value = area;
	document._form.elements["lng_lat"].value = pt.lng + '-' + pt.lat;
}
(function(fn_submit, init_pt, area, address){
	var map = new BMap.Map("IDD_MAP");
	var point;
	if("" == init_pt){
		point = new BMap.Point();
		var myCity = new BMap.LocalCity();
		myCity.get(function(result){map.setCenter(result.name)});
	}else{
		var coor = init_pt.split('-');
		point = new BMap.Point(coor[0], coor[1]);
	}
	map.centerAndZoom(point,12);
	
	var _format_addr = function(addComp){
		return addComp.province + '/' + addComp.city + '/' + addComp.district+address;
	}
	var _draw = function(_pt, _area, _addr, _need_win){
		//marker
		var marker = new BMap.Marker(_pt);
		map.addOverlay(marker);
		if(_need_win){
			//window
			var _win = document.createElement("div");
			_win.innerHTML =
				"<div style='margin:0 0 5px 0;padding:0.2em 0;font-weight:bold;width:230px;overflow:hidden;'><?php echo $mbs_appenv->lang('complete_address')?>("+_area+")</div>" + 
				//"<p style='margin:0 0 5px 0;line-height:1.5;font-size:13px;'>"+_area+"</p>" +
				"<div style='margin:0 0 5px 0;'><input type=text style='width:170px;' value='"+_addr+"' />"+
				"<a class='pure-button' style='margin:0 0 0 5px;'><?php echo $mbs_appenv->lang('confirm')?></a></div>";
			var infoWindow = new BMap.InfoWindow(_win);
			map.openInfoWindow(infoWindow, _pt);
			_win.getElementsByTagName("a")[0].onclick = function(e){
				fn_submit(_pt, _area, this.previousSibling.value, map);
				infoWindow.close();
				var label = new BMap.Label(this.previousSibling.value, {offset:new BMap.Size(20,-10)});
				label.setStyle({width:"initial"});
				marker.setLabel(label);
			}
		}else{
			var label = new BMap.Label(_addr,{offset:new BMap.Size(20,-10)});
			label.setStyle({width:"initial"});
			marker.setLabel(label);
		}
	}
	var _clear = function(){
		map.clearOverlays();
		map.closeInfoWindow();
	}
	var geoc = new BMap.Geocoder();
	map.addEventListener("click", function(e){
		_clear();
		geoc.getLocation(e.point, function(rs){
			_draw(e.point, _format_addr(rs.addressComponents), 
					rs.addressComponents.street+rs.addressComponents.streetNumber, true);
		});
	});
	if(init_pt != ""){
		_draw(point, area, address, false);
		fn_submit(point, area, address, map);
	}
})(_on_submit<?php echo sprintf(', "%s", "%s", "%s"', $info['lng_lat'], $info['area'], $info['address'])?>);
//三王苗圃是一家经营雪松、广玉兰、桂花、香樟等等苗木的个人农场。
</script>
</body>
</html>