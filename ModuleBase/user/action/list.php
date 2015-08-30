<?php 

mbs_import('', 'CUserControl', 'CUserClassControl');

$user_ins = CUserControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());

$uclass_ctr = CUserClassControl::getInstance($mbs_appenv, 
	CDbPool::getInstance(), CMemcachedPool::getInstance());
$class_list = $uclass_ctr->getDB()->listAll();
$class_list = $class_list->fetchAll(PDO::FETCH_ASSOC);

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

define('ROWS_PER_PAGE', 14);
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

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">
<title><?php mbs_title()?></title>
<!--[if lt ie 9]>
	<script>
		document.createElement("article");
		document.createElement("section");
		document.createElement("aside");
		document.createElement("footer");
		document.createElement("header");
		document.createElement("nav");
</script>
<![endif]-->
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('ui.daterangepicker.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('jquery-ui-1.7.1.custom.css')?>" type="text/css" title="ui-theme" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('allInfo.css')?>">
<style type="text/css">
.col-chbox{width:48px;}
.col-name{width:188px;}
.col-org{width:285px;}
.col-phone{width:150px;}
.col-email{width:240px;}
.col-oper{width:118px;}
.name{font-size:14px; color:#111;}
</style>
</head>
<body>
<div class="allInfo">
	<h2 class="tit">
		<?php echo $mbs_appenv->lang(array('user', 'manage'))?>
		<span class="tips"><?php echo sprintf($mbs_appenv->lang('total_count'), $count)?></span>
		<a href="<?php echo $mbs_appenv->toURL('edit')?>" class="btn-create">
			+<?php echo $mbs_appenv->lang(array('add', 'user'))?></a>
	</h2>
	
	<form class="pure-form" method="post">
	<div class="searchBox">
		<label for="" class="label-word" style="width:auto;"><?php echo $mbs_appenv->lang('name')?>&nbsp;:&nbsp;</label>
		<input type="text" class="inp-keyWord" name="name" 
			placeholder="<?php echo $mbs_appenv->lang('please_input')?>" 
			value="<?php echo htmlspecialchars($search_keys['name'])?>" />
		<label for="" class="label-word"><?php echo $mbs_appenv->lang('phone')?>&nbsp;:&nbsp;</label>
		<input type="text" class="inp-keyWord" name="phone" 
			placeholder="<?php echo $mbs_appenv->lang('please_input')?>" 
			value="<?php echo htmlspecialchars($search_keys['phone'])?>" />
		<label for="" class="label-word"><?php echo $mbs_appenv->lang(array('user', 'class'))?>&nbsp;:&nbsp;</label>
		<select name="class_id" class="sel-format">
       		<option value=-1><?php echo $mbs_appenv->lang('all')?></option>
       		<?php foreach($class_list as $c){ ?>
       		<option value="<?php echo $c['id']?>" <?php echo $search_keys['class_id']==$c['id']?' selected':''?>><?php echo $c['name']?></option>
       		<?php } ?>
       	</select>
		<a href="javascript:;" class="btn-search" onclick="this.parentNode.parentNode.submit()"><?php echo $mbs_appenv->lang('search')?></a>
	</div>
	</form>
	<!-- 列表 -->
	<form name="form_list" action="<?php echo $mbs_appenv->toURL('edit', '', array('delete'=>''))?>" method="post">
	<div class="box-tabel mb17">
		<table class="info-table" style="margin-top:1em;">
		    <thead>
		        <tr>
		            <th class="first-col col-chbox"><input type="checkbox" onclick="_checkall(this, document.form_list)" /></th>
		            <th class=col-name><?php echo $mbs_appenv->lang('name')?></th>
		            <th class=col-org><?php echo $mbs_appenv->lang('organization')?></th>
		            <th class=col-phone><?php echo $mbs_appenv->lang('phone')?></th>
		            <th class=col-email><?php echo $mbs_appenv->lang('email')?></th>
		            <th class=col-oper><?php echo $mbs_appenv->lang('operation')?></th>
		        </tr>
		    </thead>
		    <tbody>
		    	<?php 
		    	$k=-1;
		    	foreach($list as $k => $row){ ?>
		        <tr>
		            <td class=first-col><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /></td>
		            <td class=name><?php echo CStrTools::txt2html($row['name'])?></td>
		            <td><?php echo CStrTools::txt2html($row['organization'])?></td>
		            <td><?php echo $row['phone']?></td>
		            <td><?php echo $row['email']?></td>
		            <td><a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$row['id']))?>"><?php echo $mbs_appenv->lang(array('edit', 'data'))?></a></td>
		        </tr>
		     	<?php } if(-1 == $k){ ?>
		     	<tr><td colspan=5 class=no-data><?php echo $mbs_appenv->lang('no_data', 'common')?></td></tr>
		     	<?php }?>
		      </tbody>
		</table>
	</div>
	<!-- 列表end -->
	<div class="box-bottom">
		<a id=IDA_BTN_DEL href="javascript:;" class="btn-del" onclick="if(confirm('<?php echo $mbs_appenv->lang('confirmed')?>')) document.form_list.submit();">
			<i class="ico"></i><?php echo $mbs_appenv->lang('delete')?></a>
		<?php if(count($page_num_list) > 1){ ?>
		<p class="pageBox">
			<?php if(PAGE_ID > 1){ ?>
			<a href="<?php echo $mbs_appenv->toURL('list', '', array_merge($search_keys, array('page_id'=>PAGE_ID-1))) ?>" 
				class="btn-page"><?php echo $mbs_appenv->lang('prev_page')?></a>
			<?php } ?>
        	<?php foreach($page_num_list as $n => $v){ ?>
        	<a href="<?php echo $mbs_appenv->toURL('list', '', array_merge($search_keys, array('page_id'=>$n))) ?>" 
        		class="btn-page <?php echo $n==PAGE_ID?' check':''?>" ><?php echo $v?></a>
        	<?php }?>
        	<?php if(PAGE_ID < count($page_num_list)){ ?>
	        <a href="<?php echo $mbs_appenv->toURL('list', '', array_merge($search_keys, array('page_id'=>PAGE_ID+1))) ?>" 
	        	class="btn-page"><?php echo $mbs_appenv->lang('next_page')?></a>
	        <?php }?>
	    </p>
		<?php } ?>
	</div>
	</form>
</div>
<script type="text/javascript">
if(window.parent.on_user_selected){
	var big_title = document.getElementsByTagName("h2")[0];
	big_title.parentNode.removeChild(big_title);

	var tb = document.getElementsByTagName("table")[0],i;
	tb.className += " pop-table";
	for(i=0; i<tb.rows.length; i++){
		tb.rows[i].deleteCell(tb.rows[i].cells.length-1);
		tb.rows[i].deleteCell(tb.rows[i].cells.length-1);
	}

	var del_btn = document.getElementById("IDA_BTN_DEL"),
		confirm_btn = document.createElement("a");
	confirm_btn.innerHTML = "<?php echo $mbs_appenv->lang(array('confirm', 'select'))?>";
	confirm_btn.className = "btn-primary";
	confirm_btn.style.fontSize = "12px";
	confirm_btn.onclick = function(e){
		var arr = [], chbox;
		for(i=0; i<tb.rows.length; i++){
			chbox = tb.rows[i].cells[0].getElementsByTagName("input")[0];
			if(chbox.checked){
				arr.push([chbox.value, tb.rows[i].cells[1].innerHTML, 
					tb.rows[i].cells[2].innerHTML,tb.rows[i].cells[3].innerHTML]);
			}
		}
		if(arr.length > 0){
			window.parent.on_user_selected(arr);
		}
	}
	del_btn.parentNode.insertBefore(confirm_btn, del_btn);
	del_btn.parentNode.removeChild(del_btn);
}
function _checkall(chkbox, form){
	var i, boxes=form.elements["id[]"];
	boxes = boxes.length ? boxes : [boxes];
	for(i=0; i<boxes.length; i++){
		boxes[i].checked = chkbox.checked;
	}
}
</script>
</body>
</html>