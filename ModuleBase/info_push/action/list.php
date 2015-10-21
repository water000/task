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

define('ROWS_PER_PAGE', 10);
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

<<<<<<< HEAD
$info_push_ctr = CInfoPushControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());
=======
>>>>>>> 34fb3f7efb340cde68392838046ce78e5cca682d
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>

<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">


<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('ui.daterangepicker.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('jquery-ui-1.7.1.custom.css')?>" type="text/css" title="ui-theme" />

<style type="text/css">
.title{font-weight:bold;}
.abstract{color:#555;font-size:80%;margin:6px 0;}
.popimg{position:fixed;top:0;left:0;width:100%;height:100%;display:none;background:#333;}
.popimg div{height:89%;width:89%;margin:5%;overflow:auto;}
.popimg img, .popimg video{vertical-align:middle;display:block;margin:0 auto;}
div.thumb_img{position:relative;margin-right:10px;}
.thumb_img .player{position:absolute;width:50%;height:50%;top:25%;left:25%;
	background: url(<?php echo $mbs_appenv->sURL('info/player.png')?>) no-repeat center center;}
	
.ui-widget{font-size:90%;}

.info-had-read{color:#777;}
.push-log{font-size:80%;display:none;position:absolute;background-color:white;z-index:99;width:500px;padding:5px 3px;border:1px solid #bbb;}
.push-log table, .push-log .oper-bar{width:490px;margin:5px 6px;background-color:white;}
.oper-bar-fixed{position:fixed;bottom:10px;background-color:white;}
.push-log table td{background-color:white;}
.push-log a.close{display:block;text-align:right;text-decoration:none;color:#777;}
.push-log a.close:hover{color:#333;}

.pure-table td{word-wrap:break-word;word-break:break-all;border-bottom:1px solid #e8e8e8;border-left:0;}

</style>
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g wrapper">
    <div class="pure-u-1">
    	<form class="pure-form" method="post">
    		<fieldset>
        		<legend><?php echo $mbs_appenv->lang(array('info', 'search'))?></legend>
        		<?php echo $mbs_appenv->lang('time')?>
        		<input id="IDI_DATE" type="text" name="date" style="width: 210px;" 
        			value="<?php echo htmlspecialchars($search_keys['date'])?>" />&nbsp;
        		
       			<?php echo $mbs_appenv->lang('title')?>
        		<input type="text" name="title" value="<?php echo htmlspecialchars($search_keys['title'])?>" />
       			&nbsp;<?php echo $mbs_appenv->lang('attachment_format')?>
       			<select name="attachment_format">
       				<option value=0><?php echo $mbs_appenv->lang('all')?></option>
       				<?php foreach(CInfoControl::getTypeMap() as $t=>$v){ ?>
       				<option value="<?php echo $t?>" <?php echo $search_keys['attachment_format']==$t?' selected':''?>><?php echo $mbs_appenv->lang($v)?></option>
       				<?php } ?>
       			</select>
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('search')?></button>
         	</fieldset>
		</form>
		<form class="pure-form" method="post" name="_form" action="<?php echo $mbs_appenv->toURL('edit')?>">
			<table class="pure-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th>ID</th>
			            <th style="width: 70%"><?php echo $mbs_appenv->lang('content')?></th>
			            <th><?php echo $mbs_appenv->lang('time')?></th>
			            <th><?php echo $mbs_appenv->lang('had_read'), '/', $mbs_appenv->lang('push')?></th>
			        </tr>
			    </thead>
			    <tbody>
			    	<?php $k=-1; foreach($list as $k => $row){ ?>
			        <tr <?php echo 1 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /><?php echo $k+1?></td>
			            <td>
			            	<div class=pure-g>
			            		<?php if($row['attachment_path'] != ''){ ?>
					            <div class="thumb_img pure-u">
									<img __to_url="<?php echo $mbs_appenv->uploadURL($row['attachment_path'])?>" 
						            	src="<?php echo $row['attachment_format'] == CInfoControl::AT_VDO ? 
										$mbs_appenv->sURL('info/white-bg-50-50.png') :  $mbs_appenv->uploadURL($row['attachment_path']).CInfoControl::MIN_ATTACH_SFX?>" 
						            	alt="<?php echo $row['attachment_name']; ?>"
						            	title="<?php echo $row['attachment_name']; ?>" />
						             <?php if($row['attachment_format'] == CInfoControl::AT_VDO){ ?>
						             <div class=player __video_type="video/<?=pathinfo($row['attachment_name'], PATHINFO_EXTENSION )?>"></div>
						             <?php }?>
					            </div>
					            <?php } ?>
					            <div class=pure-u-4-5>
					            	<div class=title>
					            		<a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$row['id']))?>">
					            		<?php echo CStrTools::txt2html($row['title'])?></a>
					            	</div>
					            	<div class=abstract><?php echo CStrTools::txt2html($row['abstract'])?></div>
					            </div>
			            	</div>
			            </td>
			            <td title="<?php echo date('Y-m-d H:i:s', $row['create_time'])?>"><?php echo date('m-d', $row['create_time'])?></td>
			            <td style="position: relative;">
			            	<div class=push-log>
			            		<a class=close href="#" onclick="this.parentNode.style.display='none';">&times;</a>
				            	<table class="pure-table">
				            		 <thead><tr>
				            		 	<th>ID</th>
				            		 	<th><?php echo $mbs_appenv->lang('recipient')?></th>
				            		 	<th><?php echo $mbs_appenv->lang('push_time')?></th>
				            		 	<th><?php echo $mbs_appenv->lang('status')?></th>
				            		 </tr></thead>
					            <?php
					            $cond = array('pusher_uid' => $req_search_keys['creator_id'], 'info_id'=>$row['id']);
					            $plist = $info_push_ctr->getDB()->search($cond)->fetchAll(PDO::FETCH_ASSOC);
					            $read_num = 0;
					            $j = -1;
					            foreach($plist as $j => $v){
									$read_num += CInfoPushControl::ST_HAD_READ==$v['status'] ? 1 : 0;
									$user_ctr->setPrimaryKey($v['recv_uid']);
									$recv_user = $user_ctr->get();
									if(empty($recv_user))
										continue;
								?>
									<tr <?php echo CInfoPushControl::ST_HAD_READ==$v['status']?'class=info-had-read':''?>>
										<td><input type="checkbox" value="<?php echo $v['id']?>" /><?php echo $j+1?></td>
										<td><?php echo empty($recv_user) ? 'deleted-user':$recv_user['name']?></td>
										<td><?php echo date('Y-m-d H:i:s', $v['push_time'])?></td>
										<td><?php echo $mbs_appenv->lang(CInfoPushControl::statusText($v['status'])), 
											CInfoPushControl::ST_HAD_READ==$v['status']?'('.date('Y-m-d H:i:s', $v['request_time']).')':''?></td>
									</tr>
								<?php
								}
					            ?>
				            	</table>
				            	<div class="oper-bar">
				            		<a class="button-error pure-button" name="delete" onclick="_del_push_log(this);"><?php echo $mbs_appenv->lang('delete')?></a>
				            		<a style="float: right;" class="pure-button-primary pure-button" onclick="this.parentNode.parentNode.style.display='none';"><?php echo $mbs_appenv->lang('close')?></a>
				            	</div>
			            	</div>
			            	<a href="#" <?php echo -1==$j ? '':'onclick="_show_push_log(this);"'?>><?php echo $read_num, '/', $j+1?></a>
			            </td>
			        </tr>
			     	<?php } if(-1 == $k){ ?>
			     	<tr><td colspan=5 class=no-data><?php echo $mbs_appenv->lang('no_data', 'common')?></td></tr>
			     	<?php }?>
			      </tbody>
			</table>
			<div class="oper-bar">
				<button class="pure-button-primary pure-button" type="submit" onclick="this.form.action='<?php echo $mbs_appenv->toURL('push')?>'"><?php echo $mbs_appenv->lang('push')?></button>
				<button class="button-error pure-button" type="submit" name="delete" onclick="return confirm('<?php echo $mbs_appenv->lang('confirm_delete_info')?>');"><?php echo $mbs_appenv->lang('delete')?></button>
				<?php if(!empty($page_num_list)){ ?>
				<div class="pure-menu pure-menu-horizontal page-num-menu">
					<span class=pure-menu-heading><?php echo sprintf($mbs_appenv->lang('page_num_count_format'), $count)?></span>
			        <?php if(count($page_num_list) > 1){?>
			        <ul class="pure-menu-list">
			        	<?php foreach($page_num_list as $n => $v){ ?>
			        	<li class="pure-menu-item<?php echo $n==PAGE_ID?' pure-menu-selected':''?>"><a href="<?php echo $mbs_appenv->toURL('list', '', 
			        			array_merge($search_keys, array('page_id'=>$n))) ?>" class="pure-menu-link"><?php echo $v?></a></li>
			        	<?php }?>
			        </ul>
			        <?php }?>
			    </div>
				<?php } ?>
			</div>
		</form>
    </div>
</div>
<div class="popimg" id="IDD_POPIMG"><div></div></div>
<script type="text/javascript">
(function(window, document){
	var g_popimg = document.getElementById("IDD_POPIMG");
	g_popimg.onclick = function(e){
		g_popimg.style.display = "none";
		g_popimg.firstChild.innerHTML = "";
	}
	var imgs = document.getElementsByTagName("img"), i;
	for(i=0; i<imgs.length; i++){
		if(imgs[i].parentNode.className.indexOf("thumb_img") != -1){
			imgs[i].parentNode.onclick = function(e){
				g_popimg.style.display = "block";
				var player = this.getElementsByTagName("div");
				if(player.length > 0){ 
					g_popimg.firstChild.innerHTML = '<video controls="controls" autoplay="autoplay"><source src="'
						+this.getElementsByTagName("img")[0].getAttribute("__to_url")
						+'" type="'+player[0].getAttribute("__video_type")
						+'" > </source>unsupport video format</video>';
				}else{
					g_popimg.firstChild.innerHTML = '<img alt="" src="'+this.getElementsByTagName("img")[0].getAttribute("__to_url")+'" />';
				}
			}
		}
	}
})(window, document);
function _show_push_log(oa){
	var win = oa.parentNode.getElementsByTagName("div")[0];
	win.style.display = 'inline-block';
	win.style.top = (oa.offsetTop + oa.offsetHeight+2)+"px";
	win.style.right = (oa.offsetLeft + oa.offsetWidth + 2)+"px";
}
function _del_push_log(btn){
	var tb = btn.parentNode.parentNode.getElementsByTagName("table")[0], i, sel=[], del_num=0, chbox, del_list=[];
	for(i=0; i<tb.rows.length; i++){
		chbox = tb.rows[i].getElementsByTagName("input")[0];
		if(chbox && chbox.checked){
			sel.push(i);
			del_list.push(chbox.value);
		}
	}
	$.ajax({
		headers: {"Accept":"application/json"},
		url:"<?php echo $mbs_appenv->toURL('push')?>",
		data:"delete=1&id%5B%5D=" + del_list.join("&id%5B%5D="),
		dataType:"json",
		success:function(data){
			var data=eval(data);
			if("SUCCESS" == data.retcode){
				for(i=0; i<sel.length; i++){
					tb.deleteRow(sel[i]-del_num);
					del_num++;
				}
			}
		},
		error:function(){
			alert("ajax request error");
		}
	});
	
}
</script>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-1.3.1.min.js')?>"></script>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-ui-1.7.1.custom.min.js')?>"></script>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('daterangepicker_cn.jQuery.js')?>"></script>

<script type="text/javascript">	
$(function(){
	$('#IDI_DATE').daterangepicker({dateFormat:"yy/m/d"}); 
});
</script>
<div class=footer></div>
</body>
</html>