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
if(isset($_REQUEST['dep_id'])){
	if(isset($_REQUEST['join_member'])){
		foreach($_REQUEST['user_id'] as $uid){
			$dep_mbr = array(
					'dep_id'    => $_REQUEST['dep_id'],
					'user_id'   => $uid,
					'join_time' => time(),
			);
			$ret = $udepmbr->addNode($dep_mbr);
			if(!$ret){
				$error[] = $mbs_appenv->lang('member_exists').'(user-id:'.$uid.')';
			}
		}
	}
	else if(isset($_REQUEST['remove_member']) && isset($_REQUEST['user_id'])){
		$udepmbr->setPrimaryKey($_REQUEST['dep_id']);
		foreach($_REQUEST['user_id'] as $uid){
			$udepmbr->setSecondKey($uid);
			$ret = $udepmbr->delNode();
			if(!$ret){
				$error[] = 'user-id:'.$uid.'('.$udepmbr->error().')';
			}
		}
	}
	$udep->setPrimaryKey($_REQUEST['dep_id']);
	$list[] = $udep->get();
}
else if(isset($_REQUEST['id'])){
	foreach($_REQUEST['id'] as $k => $id){
		$id = intval($id);
		$udep->setPrimaryKey($id);
		$udepmbr->setPrimaryKey($id);
		if(isset($_REQUEST['delete'])){
			$ret = $udep->destroy();
			$ret = $udepmbr->destroy();
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
		else{
			$list[] = $udep->get();
		}
	}
	
	if(isset($_REQUEST['delete'])){
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success', 'common'), '', $mbs_appenv->toURL('department'));
		exit(0);
	}
}else{
	header('Location: '.$mbs_appenv->toURL('department'));
	exit(0);
}


?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
.selected_win{display: none; border-left:1px solid #e1e1e1;width:20%;margin-left:1.5%;padding-left:1.2%;}
.selected_win a{float:right;font-size:12px;}
#IDT_JOIN_LIST li{border-bottom:1px dashed #bbb;padding:2px 5px;}
</style>
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-1-6"><?php call_user_func($mbs_appenv->lang('menu'))?></div>
    <div class="pure-u-5-6">
    	<?php if(!empty($error)){ ?>
		<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }else if(isset($ret)){ ?>
		<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?>
			<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
		</div>
		<?php }?>
		
		
        <h3><?php echo $mbs_appenv->lang('edit')?>
        	<a class=back href="<?php echo $mbs_appenv->toURL('department', '')?>">&lt;<?php echo $mbs_appenv->lang('back', 'common')?></a>
        </h3>
        <form class="pure-form" method="post">
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
		        <tr <?php echo 1 == $k%2 ? 'class=pure-table-odd':'' ?>>
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
    	</form>
    		
    		<?php if(0 == $k){ 
    			$udepmbr->setPrimaryKey($list[0]['id']);
    			$mbr_list = $udepmbr->get(); 
    		?>
    		<div style="margin-top: 20px;">
    			<div class="pure-u-1">
		    		<h3><?php echo $mbs_appenv->lang('dep_member')?>
	    			<a href="#" style="float: right;" onclick="window.open('<?=$mbs_appenv->toURL('list', 'user')?>', window.attachEvent?null:'_blank,_top', 'height=600,width=900,location=no', true)"><?php echo $mbs_appenv->lang('add')?></a></h3>
	    		<form method="post" name="joined_member">
	    		<input type="hidden" name="dep_id" value="<?php echo $list[0]['id']?>" />
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
			        	$uinfo = $usr->get();
			        	if(empty($uinfo)) continue;
			        ?>
			        <tr <?php echo 1 == $j%2 ? 'class=pure-table-odd':'' ?>>
			        	<td><input type="checkbox" name="user_id[]" value="<?php echo $row['user_id']?>" /><?php echo $row['id']?></td>
			        	<td><?php echo CStrTools::txt2html($uinfo['name'])?></td>
			        	<td><?php echo date('Y-m-d H:i:s', $row['join_time'])?></td>
			        </tr>
			        <?php } ?>
	    		</table>
	    		<?php if(-1 == $j){ echo '<p class=no-data>', $mbs_appenv->lang('no_data', 'common'), '</p>'; 
	    		}else{ ?>
	    		<button class="pure-button button-error" name="remove_member" type="submit"
	    			onclick="return confirm('<?php echo $mbs_appenv->lang('confirmed', 'common')?>');" >
	    			<?php echo $mbs_appenv->lang('delete')?></button>
	    		<?php }?>
	    		</form>
    		</div>
    		<div class="pure-u-1-4 selected_win">
    			<form method="post">
    			<input type="hidden" name="dep_id" value="<?php echo $list[0]['id']?>" />
    			<ul id=IDT_JOIN_LIST></ul>
    			<button class="pure-button pure-button-primary" style="margin-top:15px;" name="join_member" type="submit">
    				<?php echo $mbs_appenv->lang('add')?></button>
	    		</form>
	    	</div>
    	</div>
<script type="text/javascript">
var g_join_list = document.getElementById("IDT_JOIN_LIST"), g_selected_user=[];
var ems = document.joined_member.elements, i=0;
for(; i<ems.length; i++){
	if("user_id[]" == ems[i].name){
		g_selected_user[ems[i].value] = 1;
	}
}
function _del(oa, id){
	delete g_selected_user[id];
	oa.parentNode.parentNode.removeChild(oa.parentNode);
	if(0 == g_join_list.childNodes.length){
		_switch_width(false);
	}
}
function _switch_width(trun_on){
	var left_list =  g_join_list.parentNode.parentNode.parentNode.getElementsByTagName("div")[0];
	if(trun_on){
		left_list.className = "pure-u-3-4";
		g_join_list.parentNode.parentNode.style.display = "inline-block";
	}else{
		left_list.className = "pure-u-1";
		g_join_list.parentNode.parentNode.style.display = "none";
	}
}
window.cb_class_selected = function(selected_user, popwin){
	if(selected_user.length > 0){
		for(var i=0, j=selected_user.length/2; i<j; i++){
			if("undefined" == typeof g_selected_user[selected_user[i*2]]){
				var li = document.createElement("li");
				li.innerHTML = "<input type=hidden name='user_id[]' value='"+selected_user[i*2]+"' />"
					+selected_user[i*2+1]+'('+selected_user[i*2]
					+')<a href="#" onclick="_del(this, '+selected_user[i*2]+')"><?php echo $mbs_appenv->lang('delete', 'common')?></a>';
				g_join_list.appendChild(li);
				g_selected_user[selected_user[i*2]] = 1;
			}
		}
		popwin.close();

		if(g_join_list.childNodes.length > 0){
			_switch_width(true);
		}else{
			alert("<?php echo $mbs_appenv->lang('member_exists')?>");
		}
	}
}
</script>
<?php } ?>
		
    </div>
</div>
<div class=footer></div>
</body>
</html>