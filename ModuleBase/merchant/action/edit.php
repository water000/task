<?php 
$page_title = 'add';
$error = array();

if(isset($_GET['dosubmit']) && empty($_POST)){
	$error['image'] = $mbs_appenv->lang('upload_max_filesize');
}

mbs_import('', 'CMctControl', 'CMctAttachmentControl');
$max_upload_images = $mbs_appenv->config('mct_max_upload_images');

$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
if(isset($_REQUEST['id'])){
	$page_title = 'edit';
	
	$mct_ctr = CMctControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
	$info = $mct_ctr->get();
	if(empty($info)){
		$mbs_appenv->echoex('Invalid param', 'MERCHANT_EDIT_INVALID_PARAM');
		exit(0);
	}
	
	$mct_atch_ctr = CMctAttachmentControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), intval($_REQUEST['id']));
	
	if(isset($_REQUEST['_timeline'])){
		$info = array_intersect_key($_REQUEST, $info) + $info;
		$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'), array('image'));
		if(empty($error)){
			unset($info['image']);
			$info['edit_time'] = time();
			$ret = $mct_ctr->set($info);
			if(empty($ret)){
				$error[] = $mct_ctr->error();
			}else{
				for($i=0; $i<count($_FILES['image']['error']); ++$i){
					if(UPLOAD_ERR_OK == $_FILES['image']['error'][$i]){
						try {
							$mct_atch_ctr->add(array($_FILES['image']['tmp_name'][$i], $_FILES['image']['name'][$i]));
						} catch (Exception $e) {
							$error[] = $e->getMessage();
						}
					}else{
						$error[] = $mbs_appenv->lang($_FILES['image']['error'][$i]);
					}
				}
			}
		}
	}
	
	$images = $mct_atch_ctr->get();
}
else if(isset($_REQUEST['_timeline'])){	
	$info_def = $info;
	$info = array_intersect_key($_REQUEST,$info) + $info;
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	
	if(empty($error)){
		$mct_ctr = CMctControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
		unset($info['image']);
		$info['status'] = CMctControl::ST_VERIRY;
		$merchant_id = $mct_ctr->add($info);
		if(empty($merchant_id)){
			$error[] = '('.$mct_ctr->error().')';
		}else{
			$info = $info_def;
			$mct_atch_ctr = CMctAttachmentControl::getInstance($mbs_appenv,
				CDbPool::getInstance(), CMemcachedPool::getInstance(), $merchant_id);
			for($i=0; $i<count($_FILES['image']['error']); ++$i){
				if(UPLOAD_ERR_OK == $_FILES['image']['error'][$i]){
					try {
						$mct_atch_ctr->add(array($_FILES['image']['tmp_name'][$i], $_FILES['image']['name'][$i]));
					} catch (Exception $e) {
						$error[] = $e->getMessage();
					}
				}else{
					$error[] = $mbs_appenv->lang($_FILES['image']['error'][$i]);
				}
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
aside {display:none;color:red;font-size:12px;}
.form-fld-img{width:30px;height:30px;}
input,textarea{width:300px;}
textarea{height:85px;}
.block{background-color:white;margin:10px 12px 0;}
.map-ctr{display:inline-block;width:400px; height:220px;}
.map-ctr-bigger{width:500px; height:300px;}

#img-lab-bg{width:66px ;height:66px ;position:relative;display:inline-block;overflow: hidden;margin:0 .5em 0 0;}
#img-lab{position:absolute;top:0;left:0;line-height:55px;font-size:45px;text-align:center; width:64px;height:64px; border-radius:5px;}
.img-lab-add{color:#7DB8EC;border:1px dashed #ccc;background-color:#fff;overflow:hidden;}
.img-lab-del{color:red;border:1px dashed red;overflow:hidden;visibility:hidden;}
.img-name{position:absolute;bottom:0;left:0;margin:2px;font-size:12px;width: 60px;overflow: hidden;text-align:center;}
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
			<div class=error>&times;<?php echo $error[0]?></div>
			<?php }else if(empty($error)){?>
			<div class=success><?php echo $mbs_appenv->lang('operation_success')?></div> 
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
				<input type="text" name="name" value="<?php echo $info['telephone']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['telephone'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
                <label style="vertical-align: top;"><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['image'])?></label>
                <span id=IDS_CONTAINER style="display:inline-block;">
                <?php if(isset($images)){foreach ($images as $img){ ?>
                <img src="<?php echo $mbs_appenv->uploadURL(CMctAttachmentControl::completePath($img['path']))?>" _data-id="<?php echo $img['id']?>" />
                <?php }}?>
                </span>
                <aside class="pure-form-message-inline"><?php echo $mbs_appenv->lang('upload_max_filesize')?></aside>
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
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=S8mKcAyeY2sq2aH7SmsGSHep"></script>
<script type="text/javascript">
fileUpload({
	max_files:<?php echo $max_upload_images?>, 
	container:"IDS_CONTAINER", 
	file_name:"image[]", 
	onFileDel:function(file){}
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
				"<div style='margin:0 0 5px 0;'><input type=text style='width:170px;' name=address value='"+_addr+"' />"+
				"<a class='pure-button' style='margin:0 0 0 5px;'><?php echo $mbs_appenv->lang('confirm')?></a></div>";
			var infoWindow = new BMap.InfoWindow(_win);
			map.openInfoWindow(infoWindow, _pt);
			_win.getElementsByTagName("a")[0].onclick = function(e){
				fn_submit(_pt, _area, this.previousSibling.value, map);
				infoWindow.close();
				var label = new BMap.Label(_addr, {offset:new BMap.Size(20,-10)});
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
		_draw(init_pt, area, address, false);
	}
})(_on_submit<?php echo sprintf(', "%s", "%s", "%s"', $info['lng_lat'], $info['area'], $info['address'])?>);

</script>
</body>
</html>