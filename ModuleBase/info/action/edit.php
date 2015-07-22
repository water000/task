<?php 

$page_title = $mbs_appenv->lang(isset($_REQUEST['id']) ? 'edit_info' : 'add_info');
$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');

if(isset($_REQUEST['delete']) && isset($_REQUEST['id'])){
	mbs_import('user', 'CUserSession');
	$usess = new CUserSession();
	list($sess_uid) = $usess->get();
	
	mbs_import('', 'CInfoPushControl', 'CInfoControl');
	$infoctr = CInfoControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	$info_push_ctr = CInfoPushControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), $sess_uid);
	
	foreach($_REQUEST['id'] as $info_id){
		$infoctr->setPrimaryKey($info_id);
		$infoctr->destroy(array('creator_id'=>$sess_uid));
		
		$info_push_ctr->setSecondKey($info_id);// for db only
		$info_push_ctr->destroy(array('info_id'=>$info_id));
	}
	$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '', $mbs_appenv->toURL('list'));
	exit(0);
}

if(isset($_FILES['imgFile']) && $_FILES['imgFile']['size'] > 0){
	mbs_import('', 'CInfoControl');
	
	$path = CInfoControl::moveEditorImg('imgFile', $mbs_appenv);
	if(false === $path)
		echo json_encode(array('error'=>1, 'message'=>'error'));
	else 
		echo json_encode(array('error'=>0, 'url'=>$mbs_appenv->uploadURL($path)));
	
	exit(0);
}


$req_info = null;
if(isset($_REQUEST['id'])){
	mbs_import('', 'CInfoControl');
	$infoctr = CInfoControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	$infoctr->setPrimaryKey($_REQUEST['id']);
	$info = $req_info = $infoctr->get();
	if(empty($req_info)){
		$mbs_appenv->echoex('invalid info id: '.$_REQUEST['id'], 'NO_SUCH_ID');
		exit(0);
	}
}

if(isset($_REQUEST['__timeline'])){
	mbs_import('', 'CInfoControl');

	$info = array_intersect_key($_REQUEST, $info);
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	if(UPLOAD_ERR_OK == $_FILES['attachment']['error']){
		$atype = CInfoControl::getAttachType($_FILES['attachment']['name']);
		if(0 == $atype){
			$error[] = $mbs_appenv->lang('unsupport_attachment_type')
			.'('.$_FILES['attachment']['name'].')';
		}else{
			try {
				$info['attachment_path']           = CInfoControl::moveAttachment(
						'attachment', $atype, $mbs_appenv);
				$info['attachment_format']         = $atype;
				$info['attachment_name']           = $_FILES['attachment']['name'];
			} catch (Exception $e) {
				$error[] = $mbs_appenv->lang('unsupport_attachment_type').';'.$e->getMessage();
			}
			if(false === $info['attachment_path']){
				$error[] = 'Move attachment error';
			}
		}
	}else if($_FILES['attachment']['error'] != UPLOAD_ERR_NO_FILE){
		$error[] = $mbs_appenv->lang($_FILES['attachment']['error']);
	}
	
	if(empty($error)){
		if(isset($_REQUEST['id'])){
			$ret = $infoctr->set($info);
			if($ret !== false && $_FILES['attachment']['size']>0
				&& !empty($req_info['attachment_name'])){
				unlink($mbs_appenv->uploadPath($req_info['attachment_path']));
			}
			$imgtag_ptn = '/<img src="([^"]+?)"/i';
			if(preg_match_all($imgtag_ptn, $req_info['abstract'], $src_match) > 0){
				$simgs = $src_match[1];
				if(preg_match_all($imgtag_ptn, $info['abstract'], $cur_match) > 0){
					$simgs = array_diff($simgs, $cur_match[1]);
				}
				foreach($simgs as $img){
					$mbs_appenv->unlinkUploadFile($img);
				}
			}
			$info_id = $_REQUEST['id'];
		}else{
			mbs_import('user', 'CUserDepSession', 'CUserSession');
			
			$udepsess = new CUserDepSession();
			$usess = new CUserSession();
			
			list($info['creator_id'], )    = $usess->get();
			list($info['dep_id'], )        = $udepsess->get();
			$info['create_time']           = time();
			
			$infoctr = CInfoControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
			$info_id = $ret = $infoctr->add($info);
			$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
		}
		if(empty($ret)){
			$error[] = $mbs_appenv->lang('db_exception', 'common').'('.$infoctr->error().')';
		}
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">
<title><?php mbs_title($page_title)?></title>
<!--[if lt ie 9]>
	<script>
		document.createElement("article");
		document.createElement("section");
		document.createElement("aside");
		document.createElement("footer");
		document.createElement("header");
		document.createElement("nav");
	</script>
<![endif]-->
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('core.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('createInfo.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('keditor/default.css')?>" />
<script src="<?php echo $mbs_appenv->sURL('kindeditor-all-min.js')?>"></script>
<script src="<?php echo $mbs_appenv->sURL('zh-CN.js')?>"></script>
<style type="text/css">
.popimg{position:fixed;top:0;left:0;width:100%;height:100%;display:none;background:#333;}
.popimg div{height:89%;width:89%;margin:5%;overflow:auto;}
.popimg img, .popimg video{vertical-align:middle;display:block;margin:0 auto;}
div.thumb_img{position:relative;display:inline-block;}
.thumb_img .player{position:absolute;width:50%;height:50%;top:25%;left:25%;
	background: url(<?php echo $mbs_appenv->sURL('info/player.png')?>) no-repeat center center;}
</style>
</head>
<body>
<div class="createInfo">
	<h2 class="tit"><?php echo $mbs_appenv->lang('add_info')?></h2>
	<form action="" method="post" enctype="multipart/form-data">
		<?php if(isset($_REQUEST['__timeline'])){ if(!empty($error)){ ?>
		<div class=error><p><?php echo implode('<br/>', $error)?></p>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }else {?>
		<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common'),  isset($notice) ? '('.$notice.')': '';?>
			<a href="<?php echo $mbs_appenv->toURL('push', '', array('id[]'=>$info_id));?>">
				<?php echo $mbs_appenv->lang('push')?></a>
				<?php echo $mbs_appenv->lang(array('or', 'continue')), $page_title?>
			<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a></div>
		<?php }}?>
		
		<input type="hidden" name="__timeline" value="<?php echo time()?>" />
		<div class="inpBox mb17"> 
			<label for="" class="labelL"><?php echo $mbs_appenv->lang('title')?>&nbsp;:&nbsp;</label>
			<input type="text" class="inpTit" name="title" required
				value="<?php echo htmlspecialchars($info['title'])?>" placeholder="<?php echo $mbs_appenv->lang('please_input')?>...">
		</div>
		<div class="inpBox mb22">
			<label for="" class="labelL"><?php echo $mbs_appenv->lang('abstract')?>&nbsp;:&nbsp;</label>
			<textarea id=IDT_AREA type="text" rows="6" class="inpWord" name="abstract" style="height:420px;"
				placeholder="<?php echo $mbs_appenv->lang('please_input')?>..."><?php echo htmlspecialchars($info['abstract'])?></textarea>
		</div>
		<div class="inpBox" style="position:relative;">
			<label for="upFile" class="labelFile"><?php echo $mbs_appenv->lang('attachment')?>&nbsp;:&nbsp;
			<input type="file" id="upFile" name="attachment" class="inpFile" />
				<span class="btnFile"><i class="icoFile"></i><?php echo $mbs_appenv->lang('click_to_add')?></span>
			</label>
		</div>
		<?php if(isset($info['attachment_path']) && !empty($info['attachment_path'])){ ?>
	    <div class="thumb_img" style="margin-top:15px;">
	    	<label for="" class="labelL">&nbsp;</label>
			<img __to_url="<?php echo $mbs_appenv->uploadURL($info['attachment_path'])?>" 
				title="<?php echo $info['attachment_name'];?>"
		            src="<?php echo $mbs_appenv->uploadURL($info['attachment_path']).CInfoControl::MIN_ATTACH_SFX?>" />
		   <?php if($info['attachment_format'] == CInfoControl::AT_VDO){ ?>
		    <div class=player __video_type="video/<?=pathinfo($info['attachment_name'], PATHINFO_EXTENSION )?>"></div>
		    <?php }?>
	    </div>
		<?php } ?>
		<div class="btnBox" style="margin-top:40px;">
			<a href="javascript:;" class="btn-send" id=IDA_SUBMIT onclick="this.parentNode.parentNode.submit();"><?php echo $mbs_appenv->lang('submit')?></a>
			<a href="<?php echo $mbs_appenv->toURL('list')?>" class="btn-cancle"><?php echo $mbs_appenv->lang('cancel')?></a>
		</div>
	</form>
</div>
<div class="popimg" id="IDD_POPIMG"><div></div></div>
<script type="text/javascript">
(function(window, document){
	var g_popimg = document.getElementById("IDD_POPIMG");
	g_popimg.onclick = function(e){
		g_popimg.style.display = "none";
		g_popimg.firstChild.innerHTML = "";
	}
	var imgs = document.getElementsByTagName("img"), i;
	for(i=0; i<imgs.length; i++){
		if("thumb_img" == imgs[i].parentNode.className){
			imgs[i].parentNode.onclick = function(e){
				g_popimg.style.display = "block";
				var player = this.getElementsByTagName("div");
				if(player.length > 0){ 
					g_popimg.firstChild.innerHTML = '<video controls="controls" autoplay="autoplay"><source src="'
						+this.getElementsByTagName("img")[0].getAttribute("__to_url")
						+'" type="'+player[0].getAttribute("__video_type")
						+'" > </source>unsupport video format</video>';
				}else{
					g_popimg.firstChild.innerHTML = '<img alt="" src="'+this.getElementsByTagName("img")[0].getAttribute("__to_url")+'" />';
				}
			}
		}
	}

	KindEditor.ready(function (K) {
		var editor = K.create('#IDT_AREA', {
			basePath : '../',
			filterMode : false,
			wellFormatMode : false,
			uploadJson : '',
			afterCreate : function() {
				var self = this;
				document.getElementById("IDA_SUBMIT").onclick = function(e){
					self.sync();
					this.parentNode.parentNode.submit();
				}
			}
		});
	});
})(window, document);
</script>
</body>
</html>