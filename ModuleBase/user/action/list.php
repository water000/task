<?php 

mbs_import('', 'CUserControl');
$user_ins = CUserControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());

define('ROWS_PER_PAGE', 20);
define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);
$search_keys = array('name'=>'', 'phone'=>'', 'email'=>'');
$req_search_keys = array_intersect_key($_REQUEST, $search_keys);
if(count($req_search_keys) > 0){
	$list = $user_ins->getDB()->search($search_keys, ROWS_OFFSET, ROWS_PER_PAGE);
}else{
	$list = $user_ins->getDB()->listAll(ROWS_OFFSET, ROWS_PER_PAGE);
}

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
<?php if(isset($_REQUEST['phone'])){if(!empty($error)){ ?>
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
       			<input type="text" name="phone" placeholder="<?php echo $mbs_appenv->lang('phone')?>">
       			<input type="text" name="email" placeholder="<?php echo $mbs_appenv->lang('email')?>">
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('search')?></button>
       			<a href="<?php echo $mbs_appenv->toURL('edit')?>" class="pure-button-primary pure-button"><?php echo $mbs_appenv->lang('add')?></a>
         	</fieldset>
		</form>
		
		<form class="pure-form" method="post">
			<table class="pure-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th>ID</th>
			            <th><?php echo $mbs_appenv->lang('name')?></th>
			            <th><?php echo $mbs_appenv->lang('organization')?></th>
			            <th><?php echo $mbs_appenv->lang('phone')?></th>
			            <th><?php echo $mbs_appenv->lang('email')?></th>
			        </tr>
			    </thead>
			    <tbody>
			    	<?php foreach($list as $k => $row){ ?>
			        <tr <?php echo 1 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /><?php echo $row['id']?></td>
			            <td><?php echo CStrTools::txt2html($row['name'])?></td>
			            <td><?php echo CStrTools::txt2html($row['organization'])?></td>
			            <td><?php echo $row['phone']?></td>
			            <td><?php echo $row['email']?></td>
			        </tr>
			     	<?php } if(empty($list)){ ?>
			     	<tr><td colspan=5 class=no-data><?php echo $mbs_appenv->lang('no_data', 'common')?></td></tr>
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