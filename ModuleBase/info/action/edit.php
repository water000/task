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
	if($_FILES['attachment']['size'] > 0){
		$atype = CInfoControl::getAttachType($_FILES['attachment']['name']);
		if(0 == $atype){
			$error[] = $mbs_appenv->lang('unsupport_attachment_type')
			.'('.$_FILES['attachment']['name'].')';
		}else{
			//try {
				$info['attachment_path']           = CInfoControl::moveAttachment(
						'attachment', $atype, $mbs_appenv);
				$info['attachment_format']         = $atype;
				$info['attachment_name']           = $_FILES['attachment']['name'];
			//} catch (Exception $e) {
			//	$notice = $mbs_appenv->lang('unsupport_attachment_type').';'.$e->getMessage();
		//	}
			if(false === $info['attachment_path']){
				$error[] = 'Move attachment error';
			}
		}
	}
	
	if(empty($error)){
		if(isset($_REQUEST['id'])){
			$ret = $infoctr->set($info);
			if($ret !== false && $_FILES['attachment']['size']>0
				&& !empty($req_info['attachment_name'])){
				unlink($mbs_appenv->uploadPath($req_info['attachment_path']));
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
<!doctype html>
<html>
<head>
<title><?php mbs_title($page_title)?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
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
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-1-6"><?php call_user_func($mbs_appenv->lang('menu'))?></div>
    <div class="pure-u-5-6">
	    <?php if(isset($_REQUEST['__timeline'])){ if(!empty($error)){ ?>
		<div class=error><p><?php echo implode('<br/>', $error)?></p>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }else {?>
		<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common'),  isset($notice) ? '('.$notice.')': '';?>
			<a href="<?php echo $mbs_appenv->toURL('push', '', array('id[]'=>$info_id));?>">
				<?php echo $mbs_appenv->lang('push')?></a>
			<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a></div>
		<?php }}?>
		
    	<form name="_form" class="pure-form pure-form-stacked" enctype="multipart/form-data" method="post">
    		<input type="hidden" name="__timeline" value="<?php echo time()?>" />
		    <fieldset>
		    	<legend style="font-size: 150%;"><?php echo $page_title?>
		    		<a class=back href="<?php echo $mbs_appenv->toURL('list')?>">&lt;<?php echo $mbs_appenv->lang('back', 'common')?></a></legend>
		    	
		            <label for="title"><?php echo $mbs_appenv->lang('title')?></label>
		            <input id="title" class="pure-input-1-2" name="title" type="text" 
		            	value="<?php echo htmlspecialchars($info['title'])?>" required />
		            <br/>
		            <label for="abstract"><?php echo $mbs_appenv->lang('abstract')?></label>
		            <textarea id="abstract" class="pure-input-1-2" style="height: 200px;"
		            	name="abstract"><?php echo htmlspecialchars($info['abstract'])?></textarea>
		            <br/>
		            <label for="attachment"><?php echo $mbs_appenv->lang('attachment')?></label>
		            <input id="attachment" name="attachment" type="file" />
		            
		       	 	<?php if(isset($info['attachment_path']) && !empty($info['attachment_path'])){ ?>
		            <div class="thumb_img">
						<img __to_url="<?php echo $mbs_appenv->uploadURL($info['attachment_path'])?>" 
			            	src="<?php echo $mbs_appenv->uploadURL($info['attachment_path']).CInfoControl::MIN_ATTACH_SFX?>" />
			             <?php if($info['attachment_format'] == CInfoControl::AT_VDO){ ?>
			             <div class=player __video_type="video/<?=pathinfo($info['attachment_name'], PATHINFO_EXTENSION )?>"></div>
			             <?php }?>
		            </div>
					<?php echo $info['attachment_name']; } ?>
					<br/> <br/>
		            <button type="submit" class="pure-button pure-button-primary"><?php echo $page_title?></button>
		    </fieldset>
		</form>
    </div>
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
})(window, document);
</script>
<div class=footer></div>
</body>
</html>