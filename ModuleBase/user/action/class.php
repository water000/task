<?php 

mbs_import('', 'CUserClassControl');
$uc = CUserClassControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

if(isset($_REQUEST['name'])){
	$error = $mbs_cur_moddef->checkargs('class');
	if(empty($error)){
		$uc->add(array(
			'name' => $_REQUEST['name'],
			'code' => $_REQUEST['code'],
			'create_time' => time()
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
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-1-2 align-center" >
    	<?php if(isset($_REQUEST['name']) && !empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }?>
		
    	<form class="pure-form" method="post">
    		<fieldset>
        		<legend><?php echo $mbs_appenv->lang('add_class')?></legend>
        		<input type="text" name="name" placeholder="<?php echo $mbs_appenv->lang('class_name')?>" required />
       			<input type="text" name="code" placeholder="<?php echo $mbs_appenv->lang('class_code')?>" required />
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('add_class')?></button>
         	</fieldset>
		</form>
		
		<form name="_form" class="pure-form" method="post" action="<?php echo $mbs_appenv->toURL('class_edit')?>">
			<table class="pure-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th>ID</th>
			            <th><?php echo $mbs_appenv->lang('class_name')?></th>
			            <th><?php echo $mbs_appenv->lang('class_code')?></th>
			            <th><?php echo $mbs_appenv->lang(array('add', 'time'), 'common')?></th>
			        </tr>
			    </thead>
			
			    <tbody>
			    <?php foreach($list as $k=>$row){?>
			        <tr <?php echo 1 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /><?php echo $k+1?></td>
			            <td><?php echo $row['name']?></td>
			            <td><?php echo $row['code']?></td>
			            <td><?php echo date('Y-m-d H:i', $row['create_time'])?></td>
			        </tr>
			    <?php }?>
			    </tbody>
			</table>
			<div style="margin-top:10px;">
				<?php if(isset($_REQUEST['popwin'])){?>
				<input type="hidden" name="popwin" value=1 />
				<script type="text/javascript">
				function _selected(){
					var ems = document._form.elements, i, j, sel=[];
					for(i=0; i<ems.length; i++){
						if("id[]" == ems[i].name && ems[i].checked){
							sel.push(ems[i].value, ems[i].parentNode.parentNode.cells[1].innerHTML);
						}
					}
					if(window.opener.cb_class_selected){
						window.opener.cb_class_selected(sel, window);
					}
					document._form.onsubmit=function(e){return false;}
				}
				</script>
				<button class="button-success pure-button" onclick="return _selected()"><?php echo $mbs_appenv->lang('select')?></button>
				<?php }?>
				<button class="pure-button pure-button-primary" name="edit" type="submit"><?php echo $mbs_appenv->lang('edit')?></button>
				<button class="button-error pure-button" name="delete" type="submit"><?php echo $mbs_appenv->lang('delete')?></button>
			</div>
		</form>
		
		
    </div>
</div>
<div class=footer></div>
</body>
</html>