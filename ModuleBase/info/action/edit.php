<?php 

$page_title = $mbs_appenv->lang(isset($_REQUEST['id']) ? 'edit_info' : 'add_info');
$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');

$req_info = null;
if(isset($_REQUEST['id'])){
	mbs_import('', 'CInfoControl');
	$infoctr = CInfoControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance(), $_REQUEST['id']);
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
			$error[] = $mbs_appenv->lang('unsupport_attach_type')
			.'('.$_FILES['attachment']['name'].')';
		}else{
			$info['attach_format']         = $atype;
			$info['attach_name']           = $_FILES['attachment']['name'];
			$info['attach_path']           = CInfoControl::moveAttachment(
					'attachment', $atype, $mbs_appenv);
			if(false === $info['attach_path']){
				$error[] = 'Move attachment error';
			}
		}
	}
	
	if(empty($error)){
		if(isset($_REQUEST['id'])){
			$ret = $infoctr->set($info);
			if($ret !== false && $_FILES['attachment']['size']>0
				&& !empty($req_info['attach_name'])){
				unlink($mbs_appenv->uploadPath($req_info['attach_path']));
			}
		}else{
			mbs_import('user', 'CUserDepSession', 'CUserSession');
			
			$udepsess = new CUserDepSession();
			$usess = new CUserSession();
			
			list($info['creator_id'], )    = $usess->get();
			list($info['dep_id'], )        = $udepsess->get();
			$info['create_time']           = time();
			
			$infoctr = CInfoControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
			$ret = $infoctr->add($info);
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
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-1-2 align-center">
	    <?php if(isset($_REQUEST['__timeline'])){ if(!empty($error)){ ?>
		<div class=error><p><?php echo implode('<br/>', $error)?></p>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }else {?>
		<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?>
			<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a></div>
		<?php }}?>
		
    	<form name="_form" class="pure-form pure-form-stacked" enctype="multipart/form-data" method="post">
    		<input type="hidden" name="__timeline" value="<?php echo time()?>" />
		    <fieldset>
		    	<legend style="font-size: 150%;"><?php echo $page_title?>
		    		<a class=back href="<?php echo $mbs_appenv->toURL('list')?>">&lt;<?php echo $mbs_appenv->lang('back', 'common')?></a></legend>
		    	
		            <label for="title"><?php echo $mbs_appenv->lang('title')?></label>
		            <input id="title" class="pure-input-1-2" name="title" type="text" value="<?php echo $info['title']?>" required />
		            <br/>
		            <label for="abstract"><?php echo $mbs_appenv->lang('abstract')?></label>
		            <textarea id="abstract" class="pure-input-1-2" style="height: 100px;"
		            	name="abstract"><?php echo CStrTools::txt2html($info['abstract'])?></textarea>
		            <br/>
		            <label for="attachment"><?php echo $mbs_appenv->lang('attachment')?></label>
		            <?php echo isset($info['attach_name']) ? $info['attach_name'] : ''?>
		            <input id="attachment" name="attachment" type="file" />
		       	 	<br/>
		            <button type="submit" class="pure-button pure-button-primary"><?php echo $page_title?></button>
		    </fieldset>
		</form>
    </div>
</div>
<div class=footer></div>
</body>
</html>