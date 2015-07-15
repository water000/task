<?php 

mbs_import('', 'CInfoControl', 'CInfoPushControl');
$info_ctr = CInfoControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());

$search_keys = array('date'=>'', 'title'=>'', 'attachment_format'=>'');
$req_search_keys = array_intersect_key($_REQUEST, $search_keys);
foreach($req_search_keys as $k=> $v){
	$req_search_keys[$k] = trim($req_search_keys[$k]);
	if(0 == strlen($req_search_keys[$k])){
		unset($req_search_keys[$k]);
	}
}
if(isset($req_search_keys['attachment_format']) && 
	!CInfoControl::typeExists($req_search_keys['attachment_format'])){
	unset($req_search_keys['attachment_format']);
}
$search_keys = array_merge($search_keys, $req_search_keys);

if(isset($req_search_keys['date'])){
	$tstart = $tend = 0;
	$date = explode('-', $req_search_keys['date']);
	if(1 == count($date)){
		$tstart = strtotime($date[0]);
		$tend   = $tstart + 86400;
	}
	else{
		list($tstart, $tend) = $date;
		$tstart = empty($tstart) ? mktime(0, 0, 0) : strtotime($tstart);
		$tend   = empty($tend)   ? $tstart+86400 : strtotime($tend);
	}
	if($tstart >= $tend){
		$req_search_keys['date'] = '';
	}else{
		$req_search_keys['create_time'] = array($tstart, $tend);
	}
}
if(isset($req_search_keys['title'])){
	$req_search_keys['title'] = trim($req_search_keys['title']);
	if(empty($req_search_keys['title'])){
		unset($req_search_keys['title']);
	}else{
		$req_search_keys['title'] = '%'.$req_search_keys['title'].'%';
	}
}


mbs_import('user', 'CUserSession', 'CUserControl');
$user_ctr = CUserControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());
$usersess = new CUserSession();
list($req_search_keys['creator_id'],) = $usersess->get(); 
unset($req_search_keys['date']);

define('ROWS_PER_PAGE', 5);
define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);
$count = $info_ctr->getDB()->count($req_search_keys);
$list = array();
$page_num_list = array();
if($count > ROWS_OFFSET){
	$opts = array(
		'offset' => ROWS_OFFSET,
		'limit'  => ROWS_PER_PAGE,
		'order'  => ' id desc',
	);
	$list = $info_ctr->getDB()->search($req_search_keys, $opts);
	$list = $list->fetchAll(PDO::FETCH_ASSOC);
	
	mbs_import('common', 'CTools');
	$page_num_list = CTools::genPagination(PAGE_ID, ceil($count/ROWS_PER_PAGE), 8);
}

$info_push_ctr = CInfoPushControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());
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
</head>
<body>
<div class="allInfo">
	<h2 class="tit">
		<?php echo $mbs_appenv->lang('all_info')?>
		<span class="tips"><?php echo sprintf($mbs_appenv->lang('total_count'), $count)?></span>
		<a href="<?php echo $mbs_appenv->toURL('edit')?>" class="btn-create">
			+<?php echo $mbs_appenv->lang('add_info')?></a>
	</h2>
	
	<form class="pure-form" method="post">
	<div class="searchBox">
		<label for="" class="label-word"><?php echo $mbs_appenv->lang('title_keys')?>:&nbsp;</label>
		<input type="text" class="inp-keyWord" name="title" 
			placeholder="<?php echo $mbs_appenv->lang('please_input')?>" 
			value="<?php echo htmlspecialchars($search_keys['title'])?>" />
		<label for="" class="label-word"><?php echo $mbs_appenv->lang('create_time')?>:&nbsp;</label>
		<input id="IDI_DATE" type="text" class="inp-keyWord" name="date" style="width: 140px;"
			placeholder="<?php echo $mbs_appenv->lang('please_input')?>" 
			value="<?php echo htmlspecialchars($search_keys['date'])?>" />
		<label for="" class="label-word"><?php echo $mbs_appenv->lang('attachment_format')?>:&nbsp;</label>
		<select name="attachment_format" class="sel-format">
       		<option class="format" value=0><?php echo $mbs_appenv->lang('all')?></option>
       		<?php foreach(CInfoControl::getTypeMap() as $t=>$v){ ?>
       		<option class="format" value="<?php echo $t?>" <?php echo $search_keys['attachment_format']==$t?' selected':''?>><?php echo $mbs_appenv->lang($v)?></option>
       		<?php } ?>
       	</select>
		<a href="javascript:;" class="btn-search" onclick="this.parentNode.parentNode.submit()"><?php echo $mbs_appenv->lang('search')?></a>
	</div>
	</form>

	<!-- 列表 -->
	<form name="form_list" action="<?php echo $mbs_appenv->toURL('push', 'info_push')?>" method="post">
	<div class="box-tabel mb17">
		<div class="top">
			<input type="checkbox" class="checkAll" onclick="_checkall(this, this.form);">
			<p class="tit-info"><?php echo $mbs_appenv->lang('content')?></p>
			<p class="time-create"><?php echo $mbs_appenv->lang('create_time')?></p>
			<p class="format-file"><?php echo $mbs_appenv->lang('attachment_format')?></p>
		</div>
		<ul class="ul-list">
		<?php $k=-1; foreach($list as $k => $row){ ?>
			<li class="list">
				<input type="checkbox" class="check-part" name="id[]" value="<?php echo $row['id']?>" /><?php echo $k+1?>
				<p class="con-info">
					<a class="link-tit" href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$row['id']))?>">
					            		<?php echo CStrTools::txt2html($row['title'])?></a>
					<span class="subWord"><?php echo CStrTools::cutstr($row['abstract'], 55, $mbs_appenv->item('charset'))?></span>
				</p>
				<p class="time-con"><?php echo date('m-d H:i', $row['create_time'])?></p>
				<p class="format-con"><?php echo $mbs_appenv->lang(CInfoControl::type2txt($row['attachment_format']))?></p>
			</li>
		<?php } ?>
		</ul>
	</div>
	<!-- 列表end -->
	<div class="box-bottom">
		<a href="javascript:;" class="btn-send" onclick="document.form_list.submit();" >
			<i class="ico"></i><?php echo $mbs_appenv->lang('push')?></a>
		<a href="javascript:;" class="btn-del" onclick="document.form_list.action='<?php echo $mbs_appenv->toURL('edit', '', array('delete'=>''))?>';document.form_list.submit();">
			<i class="ico"></i><?php echo $mbs_appenv->lang('delete')?></a>
		<?php if(count($page_num_list) > 1){?>
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
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-1.3.1.min.js')?>"></script>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-ui-1.7.1.custom.min.js')?>"></script>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('daterangepicker_cn.jQuery.js')?>"></script>
<script type="text/javascript">	
$(function(){
	$('#IDI_DATE').daterangepicker({dateFormat:"yy/m/d"}); 
});
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