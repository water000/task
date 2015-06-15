<?php 

mbs_import('', 'CUserControl');
$user_ins = CUserControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());

$search_keys = array('name'=>'', 'phone'=>'', 'class_id'=>'');
$req_search_keys = array_intersect_key($_REQUEST, $search_keys);
foreach($req_search_keys as $k=> &$v){
	$v = trim($v);
	if(0 == strlen($v)){
		unset($req_search_keys[$k]);
	}
}
if(isset($req_search_keys['class_id']) && -1 == $req_search_keys['class_id']){
	unset($req_search_keys['class_id']);
}

/*mbs_import('', 'CUserDepMemberControl', 'CUserSession');
$udepmbr = CUserDepMemberControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$usersess = new CUserSession();
list($sess_uid, ) = $usersess->get();

$udep_info = $udepmbr->getDB()->search(array('user_id'=>$sess_uid));
if(!empty($udep_info) && ($udep_info = $udep_info->fetchAll(PDO::FETCH_ASSOC))){
	$req_search_keys['class_id'] = $udep_info[0]['dep_id'];
}*/

$search_keys = array_merge($search_keys, $req_search_keys);

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
	<div class="pure-u-1-6"><?php call_user_func($mbs_appenv->lang('menu'))?></div>
    <div class="pure-u-5-6">
    	<form class="pure-form" method="post">
    		<fieldset>
        		<legend><?php echo $mbs_appenv->lang(array('user', 'search'))?></legend>
        		<input type="text" name="name" placeholder="<?php echo $mbs_appenv->lang('name')?>" value="<?php echo $search_keys['name']?>" />
       			<input type="text" name="phone" placeholder="<?php echo $mbs_appenv->lang('phone')?>" value="<?php echo $search_keys['phone']?>" />
       			<select name="class_id">
       				<option value=-1><?php echo $mbs_appenv->lang('all_class')?></option>
       				<?php 
       				mbs_import('', 'CUserClassControl');
       				$uclass_ctr = CUserClassControl::getInstance($mbs_appenv, 
						CDbPool::getInstance(), CMemcachedPool::getInstance());
					$class_list = $uclass_ctr->getDB()->listAll();
					$class_list = $class_list->fetchAll(PDO::FETCH_ASSOC);
					foreach($class_list as $row){
       				?>
       				<option value="<?php echo $row['id']?>" <?php echo $row['id'] == $search_keys['class_id'] ? ' selected':''?>>
       					<?php echo $row['name']?></option>
       				<?php }?>
       			</select>
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('search')?></button>
       			<button onclick="this.form.action='<?php echo $mbs_appenv->toURL('edit')?>'" class="pure-button-primary pure-button"><?php echo $mbs_appenv->lang('add')?></button>
         	</fieldset>
		</form>
		
		<form class="pure-form" method="post" name="_form" action="<?php echo $mbs_appenv->toURL('edit', '', array('delete'=>1))?>">
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
			    	<?php $k=-1;
			    	
			    	define('ROWS_PER_PAGE', 20);
			    	define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
			    	define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);
			    	$count = $user_ins->getDB()->count($req_search_keys);
			    	$list = array();
			    	$page_num_list = array();
			    	if($count > ROWS_OFFSET){
			    		$opts = array(
			    			'offset' => ROWS_OFFSET,
			    			'limit'  => ROWS_PER_PAGE,
			    			'order'  => ' id desc',
			    		);
			    		$list = $user_ins->getDB()->search($req_search_keys, $opts);
			    	
			    		mbs_import('common', 'CTools');
			    		$page_num_list = CTools::genPagination(PAGE_ID, ceil($count/ROWS_PER_PAGE));
			    	}
			    	foreach($list as $k => $row){ ?>
			        <tr <?php echo 1 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /><?php echo $row['id']?></td>
			            <td><a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$row['id']))?>">
			            	<?php echo CStrTools::txt2html($row['name'])?></a></td>
			            <td><?php echo CStrTools::txt2html($row['organization'])?></td>
			            <td><?php echo $row['phone']?></td>
			            <td><?php echo $row['email']?></td>
			        </tr>
			     	<?php } if(-1 == $k){ ?>
			     	<tr><td colspan=5 class=no-data><?php echo $mbs_appenv->lang('no_data', 'common')?></td></tr>
			     	<?php }?>
			      </tbody>
			</table>
			<div style="margin-top:10px;">
				<script type="text/javascript">
				if(window.opener){
					var str = '<button class="button-success pure-button" type="submit" onclick="return _selected();"><?php echo $mbs_appenv->lang('select')?></button>';
					document.write(str);
					function _selected(){
						var ems = document._form.elements, i, j, sel=[];
						for(i=0; i<ems.length; i++){
							if("id[]" == ems[i].name && ems[i].checked){
								sel.push(ems[i].value, ems[i].parentNode.parentNode.cells[1].childNodes[0].innerHTML);
							}
						}
						if(window.opener.cb_class_selected){
							window.opener.cb_class_selected(sel, window);
						}
						return false;
					}
				}
				</script>
				<button class="button-error pure-button" type="submit" onclick="return confirm('<?php echo $mbs_appenv->lang('confirmed', 'common')?>');"><?php echo $mbs_appenv->lang('delete')?></button>
				<?php if(!empty($page_num_list)){ $keys = array_intersect_key($_REQUEST, $search_keys); ?>
				<div class="pure-menu pure-menu-horizontal page-num-menu">
					<span class=pure-menu-heading><?php echo sprintf($mbs_appenv->lang('page_num_count_format'), $count)?></span>
			        <?php if(count($page_num_list) > 1){?>
			        <ul class="pure-menu-list">
			        	<?php foreach($page_num_list as $n => $v){ ?>
			        	<li class="pure-menu-item<?php echo $n==PAGE_ID?' pure-menu-selected':''?>"><a href="<?php echo $mbs_appenv->toURL('list', '', 
			        			array_merge($keys, array('page_id'=>$n))) ?>" class="pure-menu-link"><?php echo $v?></a></li>
			        	<?php }?>
			        </ul>
			        <?php }?>
			    </div>
				<?php } ?>
			</div>
		</form>
    </div>
</div>
<div class=footer></div>
</body>
</html>