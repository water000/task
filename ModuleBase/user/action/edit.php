<?php 
$page_title = $mbs_appenv->lang(isset($_REQUEST['id']) ? 'edit_info' : 'record_info');
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
<?php if(isset($_REQUEST['phone_num'])){if(!empty($error)){ ?>
<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
</div>
<?php }}?>
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-1-2 align-center">
    	<form class="pure-form pure-form-aligned" method="post">
		    <fieldset>
		    	<legend style="font-size: 1.5em;tex"><?php echo $page_title?></legend>
		    	
		        <div class="pure-control-group">
		            <label for="name"><?php echo $mbs_appenv->lang('name')?></label>
		            <input id="name" name="name" type="text" />
		        </div>
		        <div class="pure-control-group">
		            <label for="orgnization"><?php echo $mbs_appenv->lang('orgnization')?></label>
		            <input id="orgnization" name="orgnization" type="text" />
		        </div>
		        <div class="pure-control-group">
		            <label for="name"><?php echo $mbs_appenv->lang('phone_num')?></label>
		            <input id="phone_num" name="phone_num" type="text" />
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
		            <input id="class" name="class" type="text" readonly />
		            <a href="<?=$mbs_appenv->toURL('class', '', array('popwin'=>1))?>" style="vertical-align: bottom;margin-left:20px;">
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
</body>
</html>