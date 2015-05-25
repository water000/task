<?php 

mbs_import('', 'CUserClassControl');
$uc = CUserClassControl::getInstance($mbs_appenv, CDbPool::getInstance(), null);

if(isset($_REQUEST['class_name'])){
	$error = $mbs_cur_moddef->checkargs('class');
	if(empty($error)){
		$uc->add(array(
			'class_name' => $_REQUEST['class_name'],
			'class_code' => $_REQUEST['class_code']
		));
	}
}

$list = $uc->getDB()->listAll();

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<?php if(isset($_REQUEST['class_name']) && !empty($error)){ ?>
<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
</div>
<?php }?>
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-1-2 align-center" >
    	<form class="pure-form" method="post">
    		<fieldset>
        		<legend><?php echo $mbs_appenv->lang('add_class')?></legend>
        		<input type="text" name="name" placeholder="<?php echo $mbs_appenv->lang('class_name')?>" required />
       			<input type="text" name="code" placeholder="<?php echo $mbs_appenv->lang('class_code')?>" required />
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('add_class')?></button>
         	</fieldset>
		</form>
		
		<form class="pure-form" method="post">
			<table class="pure-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th>#</th>
			            <th><?php echo $mbs_appenv->lang('class_name')?></th>
			            <th><?php echo $mbs_appenv->lang('class_code')?></th>
			        </tr>
			    </thead>
			
			    <tbody>
			    <?php foreach($list as $k=>$row){?>
			        <tr <?php echo $k>0 && 0 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="checkbox" name="select_id[]" value="<?php echo $row['id']?>" /><?php echo $k+1?></td>
			            <td><?php echo $row['name']?></td>
			            <td><?php echo $row['code']?></td>
			        </tr>
			    <?php }?>
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