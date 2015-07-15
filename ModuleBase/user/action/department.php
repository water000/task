<?php 

mbs_import('', 'CUserDepControl');

$dep_ins = CUserDepControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$dep = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
if(isset($_REQUEST['name'])){
	$error = $mbs_cur_moddef->checkargs($mbs_appenv->item('cur_action'));
	$dep = array_intersect_key($_REQUEST, $dep);
	if(empty($error)){
		$dep['edit_time'] = time();
		$id = $dep_ins->add($dep);
		if(empty($id)){
			$error[] = $mbs_appenv->lang('dep_exists');
		}else{
			$dep = array_fill_keys(array_keys($mbs_cur_actiondef[CModDef::P_ARGS]), '');
		}
	}
}


$list = $dep_ins->getDB()->listAll();
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
<div class="pure-g wrapper">
    <div class="pure-u-1">
    	<?php if(isset($_REQUEST['name'])){ if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }else{?>
		<div class=success><p><?php echo $mbs_appenv->lang('operation_success', 'common')?></p>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }}?>
		
    	<form class="pure-form" method="post">
    		<fieldset>
        		<legend><?php echo $mbs_appenv->lang('add_department')?></legend>
        		<input type="text" name="name" value="<?php echo $dep['name']?>" placeholder="<?php echo $mbs_appenv->lang('name', 'common')?>" required />
       			<input type="text" name="password" value="<?php echo $dep['password']?>" placeholder="<?php echo $mbs_appenv->lang('password')?>" required />
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('add_department')?></button>
         	</fieldset>
		</form>
		
		<form name="_form" class="pure-form" method="post" action="<?php echo $mbs_appenv->toURL('dep_edit')?>">
			<table class="pure-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th>ID</th>
			            <th><?php echo $mbs_appenv->lang('name', 'common')?></th>
			            <th><?php echo $mbs_appenv->lang('password')?></th>
			            <th><?php echo $mbs_appenv->lang(array('edit', 'time'), 'common')?></th>
			        </tr>
			    </thead>
			
			    <tbody>
			    <?php $k=-1; foreach($list as $k=>$row){?>
			        <tr <?php echo 1 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /><?php echo $row['id']?></td>
			            <td><a href="<?php echo $mbs_appenv->toURL('dep_edit', '', array('id[]'=>$row['id']))?>"><?php echo $row['name']?></a></td>
			            <td><?php echo $row['password']?></td>
			            <td><?php echo date('Y-m-d H:i', $row['edit_time'])?></td>
			        </tr>
			    <?php }?>
			    </tbody>
			</table>
			<?php if(-1 == $k){ echo '<p class=no-data>', $mbs_appenv->lang('no_data', 'common'), '</p>'; 
    		}else{ ?>
    		<div style="margin-top:10px;">
				<button class="pure-button pure-button-primary" type="submit"><?php echo $mbs_appenv->lang('edit')?></button>
				<button class="button-error pure-button" name="delete" type="submit" 
				 onclick="return confirm('<?php echo $mbs_appenv->lang('confirmed')?>');" ><?php echo $mbs_appenv->lang('delete')?></button>
			</div>
    		<?php }?>
			
		</form>
		
		
    </div>
</div>
<div class=footer></div>
</body>
</html>