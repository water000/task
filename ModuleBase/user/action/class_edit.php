<?php 

mbs_import('', 'CUserClassControl');
$uc = CUserClassControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$list = array();
$error = array();
if(isset($_REQUEST['id'])){
	foreach($_REQUEST['id'] as $k => $id){
		$id = intval($id);
		$uc->setPrimaryKey($id);
		if(isset($_REQUEST['delete'])){
			$uc->destroy();
		}
		else if(isset($_REQUEST['edit'])){
			$list[] = $uc->get();
		}
		else if(isset($_REQUEST['edit_submit'])){
			try {
				$ret = $uc->set(array(
					'name' => $_REQUEST['name'][$k],
					'code' => $_REQUEST['code'][$k]
				));
				$list[] = array('id'=>$id, 'name'=>$_REQUEST['name'][$k], 'code'=>$_REQUEST['code'][$k]);
			} catch (Exception $e) {
				$error[] = $e->getMessage();
			}
		}
	}
	
	if(isset($_REQUEST['delete'])){
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success', 'common'), '', $mbs_appenv->toURL('class'));
		exit(0);
	}
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
<div class="pure-g wrapper">
    <div class="pure-u-1">
    	<?php if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }else if(isset($_REQUEST['edit_submit'])){ ?>
		<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?></div>
		<?php }?>
		
		<form class="pure-form" method="post">
        	<h3><?php echo $mbs_appenv->lang('edit')?>
        		<a class=back href="<?php echo $mbs_appenv->toURL('class', '')?>">&lt;<?php echo $mbs_appenv->lang('back', 'common')?></a>
        	</h3>
			<table class="pure-table" style="width: 100%;margin:1em 0;">
			    <thead>
			        <tr>
			            <th><?php echo $mbs_appenv->lang('class_name')?></th>
			            <th><?php echo $mbs_appenv->lang('class_code')?></th>
			        </tr>
			    </thead>
			
			    <tbody>
			    <?php $k = -1; foreach($list as $k=>$row){?>
			    	<input type="hidden" name="id[]" value="<?php echo $row['id']?>" />
			        <tr <?php echo 1 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="text" name="name[]" value="<?php echo $row['name']?>" /></td>
			            <td><input type="text" name="code[]" value="<?php echo $row['code']?>" /></td>
			        </tr>
			    <?php }?>
			    </tbody>
			</table>
			<?php if(-1 == $k){ echo '<p class=no-data>', $mbs_appenv->lang('no_data', 'common'), '</p>'; 
    		}else{ ?>
    		<button class="pure-button pure-button-primary" name="edit_submit" type="submit"><?php echo $mbs_appenv->lang('edit')?></button>
    		<?php }?>
		</form>
    </div>
</div>
<div class=footer></div>
</body>
</html>