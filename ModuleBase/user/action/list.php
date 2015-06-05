<?php 

mbs_import('', 'CUserControl');
$user_ins = CUserControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());

define('ROWS_PER_PAGE', 20);
define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);
$search_keys = array('name'=>'', 'phone'=>'', 'email'=>'');
$req_search_keys = array_intersect_key($_REQUEST, $search_keys);
foreach($req_search_keys as $k=> &$v){
	$v = trim($v);
	if(0 == strlen($v)){
		unset($req_search_keys[$k]);
	}
}
if(count($req_search_keys) > 0){
	$opts = array(
		'offset' => ROWS_OFFSET,
		'limit'  => ROWS_PER_PAGE,
		'order'  => ' id desc',
	);
	$list = $user_ins->getDB()->search($req_search_keys, $opts);
}else{
	$list = $user_ins->getDB()->listAll(ROWS_OFFSET, ROWS_PER_PAGE);
}

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
    <div class="pure-u-2-3 align-center">
    	<form class="pure-form" method="post">
    		<fieldset>
        		<legend><?php echo $mbs_appenv->lang('search')?></legend>
        		<input type="text" name="name" placeholder="<?php echo $mbs_appenv->lang('name')?>" value="<?php echo $search_keys['name']?>" />
       			<input type="text" name="phone" placeholder="<?php echo $mbs_appenv->lang('phone')?>" value="<?php echo $search_keys['phone']?>" />
       			<input type="text" name="email" placeholder="<?php echo $mbs_appenv->lang('email')?>" value="<?php echo $search_keys['email']?>"/>
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('search')?></button>
       			<button onclick="this.form.action='<?php echo $mbs_appenv->toURL('edit')?>'" class="pure-button-primary pure-button"><?php echo $mbs_appenv->lang('add')?></button>
         	</fieldset>
		</form>
		
		<form class="pure-form" method="post" name="_form" action="<?php echo $mbs_appenv->toURL('delete')?>">
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
			    	<?php $k=-1; foreach($list as $k => $row){ ?>
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
			</div>
		</form>
    </div>
</div>
<div class=footer></div>
</body>
</html>