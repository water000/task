<?php 

mbs_import('', 'CUserDepControl', 'CUserDepMemberControl', 'CUserControl');
$udep = CUserDepControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$udepmbr = CUserDepMemberControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$usr = CUserControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$list = array();
$error = array();
if(isset($_REQUEST['id'])){
	foreach($_REQUEST['id'] as $k => $id){
		$id = intval($id);
		$udep->setPrimaryKey($id);
		if(isset($_REQUEST['delete'])){
			$uc->destroy();
		}
		else if(isset($_REQUEST['edit'])){
			$list[] = $udep->get();
		}
		else if(isset($_REQUEST['edit_submit'])){
			$edit_info = array(
				'edit_time' => time(),
				'password'  => $_REQUEST['password'][$k],
				'name'      => $_REQUEST['name'][$k]
			);
			try {
				$ret = $udep->set($edit_info);
				$edit_info['id'] = $id;
				$list[] = $edit_info;
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
<style type="text/css">
.selected_win{display: none; border-left:1px solid #eee;width:22%;margin-left:1.5%;padding-left:1%;}
.selected_win a{float:right;font-size:12px;}
</style>
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-1-2 align-center" >
    	<?php if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }else if(isset($_REQUEST['edit_submit'])){ ?>
		<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?></div>
		<?php }?>
		
		<form class="pure-form" method="post">
        	<h3><?php echo $mbs_appenv->lang('edit')?>
        		<a class=back href="<?php echo $mbs_appenv->toURL('department', '')?>">&lt;<?php echo $mbs_appenv->lang('back', 'common')?></a>
        	</h3>
			<table class="pure-table" style="width: 100%;margin:1em 0;">
			    <thead>
			        <tr>
			            <th><?php echo $mbs_appenv->lang('name', 'common')?></th>
			            <th><?php echo $mbs_appenv->lang('password')?></th>
			        </tr>
			    </thead>
			
			    <tbody>
			    <?php $k = -1; foreach($list as $k=>$row){?>
			    	<input type="hidden" name="id[]" value="<?php echo $row['id']?>" />
			        <tr <?php echo $k>0 && 0 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="text" name="name[]" value="<?php echo $row['name']?>" /></td>
			            <td><input type="text" name="password[]" value="*****" /></td>
			        </tr>
			    <?php }?>
			    </tbody>
			</table>
			<?php if(-1 == $k){ echo '<p class=no-data>', $mbs_appenv->lang('no_data', 'common'), '</p>'; 
    		}else{ ?>
    		<button class="pure-button pure-button-primary" name="edit_submit" type="submit"><?php echo $mbs_appenv->lang('edit')?></button>
    		<?php }?>
    		
    		<?php if(0 == $k){ $udepmbr->setPrimaryKey($list[0]['id']);$mbr_list = $udepmbr->get(); ?>
    		<div class="pure-g" style="margin-top: 20px;">
    			<div class="pure-u-1">
		    		<h3><?php echo $mbs_appenv->lang('dep_member')?>
		    			<a href="#" style="float: right;" onclick="window.open('<?=$mbs_appenv->toURL('list', 'user')?>', '_blank,_top', 'height=600,width=900,location=no', true)"><?php echo $mbs_appenv->lang('add')?></a></h3>
		    		<table class="pure-table" style="width: 100%;margin:1em 0;">
		    			<thead>
		    			<tr>
		    				<th>ID</th>
				            <th><?php echo $mbs_appenv->lang('name', 'common')?></th>
				            <th><?php echo $mbs_appenv->lang('join_time')?></th>
				        </tr>
				        </thead>
				        <?php $j=-1;foreach($mbr_list as $j=>$row){ 
				        	$usr->setPrimaryKey($row['user_id']); 
				        	$uinfo = $user->get();
				        	if(empty($uinfo)) continue;
				        ?>
				        <tr>
				        	<td><?php echo $row['id']?></td>
				        	<td><?php echo CStrTools::txt2html($uinfo['name'])?></td>
				        	<td><?php echo date('Y-m-d H:i:s', $row['create_time'])?></td>
				        </tr>
				        <?php } ?>
		    		</table>
		    		<?php if(-1 == $j){ echo '<p class=no-data>', $mbs_appenv->lang('no_data', 'common'), '</p>'; 
		    		}else{ ?>
		    		<button class="pure-button pure-button-primary" name="edit_submit" type="submit"><?php echo $mbs_appenv->lang('delete')?></button>
		    		<?php }?>
	    		</div>
	    		<div class="pure-u-1-4 selected_win">
	    			<h4><?php echo $mbs_appenv->lang('selected', 'common')?></h4>
	    			<ul id=IDT_JOIN_LIST></ul>
	    			<button class="pure-button pure-button-primary" style="margin-top:15px;" name="edit_submit" type="submit"><?php echo $mbs_appenv->lang('add')?></button>
	    		</div>
    		</div>
<script type="text/javascript">
var g_join_list = document.getElementById("IDT_JOIN_LIST"), g_selected_user=[];
function _del(oa, id){
	delete g_selected_user[id];
	oa.parentNode.parentNode.removeChild(oa.parentNode);
	if(0 == g_join_list.childNodes.length){
		_switch_width(false);
	}
}
function _switch_width(trun_on){
	var left_list =  g_join_list.parentNode.parentNode.getElementsByTagName("div")[0];
	if(trun_on){
		left_list.className = "pure-u-3-4";
		g_join_list.parentNode.style.display = "block";
	}else{
		left_list.className = "pure-u-1";
		g_join_list.parentNode.style.display = "none";
	}
}
window.cb_class_selected = function(selected_user, popwin){
	if(selected_user.length > 0){
		_switch_width(true);
		for(var i=0, j=selected_user.length/2; i<j; i++){
			if("undefined" == typeof g_selected_user[selected_user[i*2]]){
				var li = document.createElement("li");
				li.innerHTML = selected_user[i*2+1]+'('+selected_user[i*2]
					+')<a href="#" onclick="_del(this, '+selected_user[i*2]+')"><?php echo $mbs_appenv->lang('delete', 'common')?></a>';
				g_join_list.appendChild(li);
				g_selected_user[selected_user[i*2]] = 1;
			}
		}
		popwin.close();
	}
}
</script>
    		<?php } ?>
		</form>
    </div>
</div>
<div class=footer></div>
</body>
</html>