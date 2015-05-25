<!doctype html>
<html>
<head>
<title><?php mbs_title($mbs_appenv->lang('list'))?></title>
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
    <div class="pure-u-2-3 align-center">
    	<form class="pure-form" method="post">
    		<fieldset>
        		<legend><?php echo $mbs_appenv->lang('search')?></legend>
        		<input type="text" name="name" placeholder="<?php echo $mbs_appenv->lang('name')?>" />
       			<input type="text" name="phone_num" placeholder="<?php echo $mbs_appenv->lang('phone_num')?>">
       			<input type="text" name="email" placeholder="<?php echo $mbs_appenv->lang('email')?>">
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('search')?></button>
       			<a href="<?php echo $mbs_appenv->toURL('add')?>" class="button-success pure-button"><?php echo $mbs_appenv->lang('add')?></a>
         	</fieldset>
		</form>
		
		<form class="pure-form" method="post">
			<table class="pure-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th>#</th>
			            <th><?php echo $mbs_appenv->lang('class_name')?></th>
			            <th><?php echo $mbs_appenv->lang('orgnization')?></th>
			            <th><?php echo $mbs_appenv->lang('phone_num')?></th>
			            <th><?php echo $mbs_appenv->lang('email')?></th>
			        </tr>
			    </thead>
			
			    <tbody>
			        <tr>
			            <td><input type="checkbox" name="" />1</td>
			            <td>Honda</td>
			            <td>Accord</td>
			            <td>Honda</td>
			            <td>Accord</td>
			        </tr>
			        <tr class="pure-table-odd">
			            <td>1</td>
			            <td>Honda</td>
			            <td>Accord</td>
			            <td>Honda</td>
			            <td>Accord</td>
			        </tr>
			      </tbody>
			</table>
		</form>
		
		<div style="margin-top:10px;">
			<button class="button-success pure-button"><?php echo $mbs_appenv->lang('select')?></button>
			<button class="button-error pure-button"><?php echo $mbs_appenv->lang('delete')?></button>
		</div>
    </div>
</div>
<div class=footer></div>
</body>
</html>