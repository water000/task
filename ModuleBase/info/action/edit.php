<?php 
$page_title = $mbs_appenv->lang(isset($_REQUEST['id']) ? 'edit_info' : 'add_info');

$info = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');

if(isset($_REQUEST['__timeline'])){
	$info = array_intersect_key($_REQUEST, $info);
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	$atype = CInfoControl::getAttchType($_FILES['attachment']['name']);
	if(0 == $atype){
		$error[] = $mbs_appenv->lang('unsupport_attach_type')
		.'('.$_FILES['attachment']['name'].')';
	}
	
	if(empty($error)){
		mbs_import('', 'CInfoControl');
		mbs_import('user', 'CUserDepSession', 'CUserSession');
		
		$udepsess = new CUserDepSession();
		$usess = new CUserSession();
		
		$info['attach_format'] = $atype;
		$info['attach_name']   = $_FILES['attachment']['name'];
		$info['attach_path']   = CInfoControl::moveAttachment($_FILES['attachment']['tmp_name']);
		$info['creator_id']    = $usess->get()[0];
		$info['dep_id']        = $udepsess->get()[0];
		$info['create_time']   = time();
		
		$infoctr = CInfoControl::getInstance($mbs_appenv, 
				CDbPool::getInstance(), CMemcachedPool::getInstance());
		$infoctr->add($info);
		
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
		
    	<form name="_form" class="pure-form pure-form-stacked" method="post">
    		<input type="hidden" name="__timeline" value="<?php echo time()?>" />
		    <fieldset>
		    	<legend style="font-size: 150%;"><?php echo $page_title?>
		    		<a class=back href="<?php echo $mbs_appenv->toURL('list')?>">&lt;<?php echo $mbs_appenv->lang('back', 'common')?></a></legend>
		    	
		            <label for="title"><?php echo $mbs_appenv->lang('title')?></label>
		            <input id="title" class="pure-input-1-2" name="title" type="text" value="<?php echo $info['title']?>" required />
		            <br/>
		            <label for="abstract"><?php echo $mbs_appenv->lang('abstract')?></label>
		            <textarea id="abstract" class="pure-input-1-2" name="abstract" type="text" value="<?php echo $info['abstract']?>" >
		            	<?php echo CStrTools::txt2html($info['abstract'])?>
		            </textarea>
		            <br/>
		            <label for="attachment"><?php echo $mbs_appenv->lang('attachment')?></label>
		            <?php echo isset($info['attach_name']) ? $info['attach_name'] : ''?>
		            <input id="attachment" name="attachment" type="file"  required />
		       	 	<br/>
		            <button type="submit" class="pure-button pure-button-primary"><?php echo $page_title?></button>
		    </fieldset>
		</form>
    </div>
</div>
<div class=footer></div>
</body>
</html>