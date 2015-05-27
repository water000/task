<?php 
$page_title = $mbs_appenv->lang(isset($_REQUEST['id']) ? 'edit_info' : 'record_info');

mbs_import('', 'CUserControl');

$user = array(
	'name'=>'', 'organization'=>'',
);
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
	    <?php if(isset($_REQUEST['phone'])){if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }}?>
		
    	<form name="_form" class="pure-form pure-form-aligned" method="post">
		    <fieldset>
		    	<legend style="font-size: 150%;"><?php echo $page_title?>
		    		<a class=back href="<?php echo $mbs_appenv->toURL('list')?>">&lt;<?php echo $mbs_appenv->lang('back', 'common')?></a></legend>
		    	
		        <div class="pure-control-group">
		            <label for="name"><?php echo $mbs_appenv->lang('name')?></label>
		            <input id="name" name="name" type="text" required />
		        </div>
		        <div class="pure-control-group">
		            <label for="organization"><?php echo $mbs_appenv->lang('organization')?></label>
		            <input id="organization" name="organization" type="text" />
		        </div>
		        <div class="pure-control-group">
		            <label for="phone"><?php echo $mbs_appenv->lang('phone')?></label>
		            <input id="phone" name="phone" type="text" required />
		        </div>
		        <div class="pure-control-group">
		            <label for="name"><?php echo $mbs_appenv->lang('email')?></label>
		            <input id="email" name="email" type="email" />
		        </div>
		        <div class="pure-control-group">
		            <label for="IMEI">IMEI</label>
		            <input id="IMEI" name="IMEI" type="text" />
		        </div>
		        <div class="pure-control-group">
		            <label for="IMSI">IMSI</label>
		            <input id="IMSI" name="IMSI" type="text" />
		        </div>
		        <div class="pure-control-group">
		            <label for="VPDN_name"><?php echo $mbs_appenv->lang('VPDN_name')?></label>
		            <input id="VPDN_name" name="VPDN_name" type="text" />
		        </div>
		        <div class="pure-control-group">
		            <label for="VPDN_pass">VPDN <?php echo $mbs_appenv->lang('password')?></label>
		            <input id="VPDN_pass" name="VPDN_pass" type="text" />
		        </div>
		        <div class="pure-control-group">
		            <label for="class"><?php echo $mbs_appenv->lang('class')?></label>
		            <input id="class" name="class" type="text" style="color: #aaa;" disabled />
		            <input type="hidden" name="class_id" value="" />
		            <a href="javascript:window.open('<?=$mbs_appenv->toURL('class', '', array('popwin'=>1))?>', '_blank,_top', 'height=400,width=600,location=no', true);" 
		            	style="vertical-align: bottom;margin-left:20px;">
		            	<?php echo $mbs_appenv->lang('select_class')?>
		            </a>
		        </div>
		        <br />
		        <div class="pure-control-group">
		            <label for="submit"></label>
		            <button type="submit" class="pure-button pure-button-primary"><?php echo $page_title?></button>
		        </div>
		    </fieldset>
		</form>
    </div>
</div>
<div class=footer></div>
<script type="text/javascript">
window.cb_class_selected = function(selected_class, popwin){
	if(selected_class.length > 0){
		document._form.elements["class_id"].value = selected_class[0];
		document._form.elements["class"].value = selected_class[1];
		popwin.close();
	}
}
</script>
</body>
</html>