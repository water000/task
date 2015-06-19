<?php 

mbs_import('', 'CInfoControl', 'CInfoPushControl');
mbs_import('user', 'CUserControl');

$info_ctr = CInfoControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());
$info_push_ctr = CInfoPushControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());
$user_ctr = CUserControl::getInstance($mbs_appenv,
	CDbPool::getInstance(), CMemcachedPool::getInstance());


$search_keys = array('tstart'=>'', 'tend'=>'');
$req_search_keys = array_intersect_key($_REQUEST, $search_keys);
foreach($req_search_keys as $k=> $v){
	$req_search_keys[$k] = trim($req_search_keys[$k]);
	if(0 == strlen($req_search_keys[$k])){
		unset($req_search_keys[$k]);
	}
}
if(isset($req_search_keys['tstart']) || isset($req_search_keys['tend'])){
	if(empty($req_search_keys['tstart'])){
		$tstart = time() - 24*3600;
		$req_search_keys['tstart'] = date('Y-m-d', $tstart);
	}else{
		$tstart = strtotime($req_search_keys['tstart']);
	}
	if(empty($req_search_keys['tend'])){
		$tend = mktime(24);
		$req_search_keys['tend'] = date('Y-m-d', $tend);
	}else{
		$tend = strtotime($req_search_keys['tend']);
	}
	if($tstart >= $tend){
		$req_search_keys['tstart'] = $req_search_keys['tend'] = '';
	}else{
		$req_search_keys['push_time'] = array($tstart, $tend);
	}
}
$search_keys = array_merge($search_keys, $req_search_keys);
mbs_import('user', 'CUserSession');
$usersess = new CUserSession();
list($req_search_keys['pusher_uid'],) = $usersess->get(); 
unset($req_search_keys['tstart'], $req_search_keys['tend']);

define('ROWS_PER_PAGE', 20);
define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);
$count = $info_push_ctr->getDB()->count($req_search_keys);
$list = array();
$page_num_list = array();
if($count > ROWS_OFFSET){
	$opts = array(
		'offset' => ROWS_OFFSET,
		'limit'  => ROWS_PER_PAGE,
		'order'  => ' id desc',
	);
	$list = $info_push_ctr->getDB()->search($req_search_keys, $opts);
	$list = $list->fetchAll(PDO::FETCH_ASSOC);

	mbs_import('common', 'CTools');
	$page_num_list = CTools::genPagination(PAGE_ID, ceil($count/ROWS_PER_PAGE));
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
td .title{font-weight:bold;text-align:center;}
td .abstract{width:95%; margin:10px auto;color:#555;font-size:80%;}
.popimg{position:fixed;top:10%;left:10%;height:85%;display:none;overflow:scroll;}
.popimg img{vertical-align:center;}
</style>
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g" style="margin-top: 20px;color:#777;">
    <div class="pure-u-1-6"><?php call_user_func($mbs_appenv->lang('menu'))?></div>
    <div class="pure-u-5-6">
    	<form class="pure-form" method="post">
    		<fieldset>
        		<legend><?php echo $mbs_appenv->lang(array('push', 'search'))?></legend>
        		<?php echo $mbs_appenv->lang('time')?>
        		<input type="text" style="width: 120px" name="tstart" value="<?php echo $search_keys['tstart']?>" />-<input type="text" name="tend" style="width: 120px" value="<?php echo $search_keys['tend']?>" />
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('search')?></button>
       			<a href="<?php echo $mbs_appenv->toURL('list')?>" class="pure-button-primary pure-button"><?php echo $mbs_appenv->lang('push')?></a>
         	</fieldset>
		</form>
		<form class="pure-form" method="post" name="_form" action="<?php echo $mbs_appenv->toURL('push')?>">
			<table class="pure-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th>ID</th>
			            <th><?php echo $mbs_appenv->lang('recipient')?></th>
			            <th><?php echo $mbs_appenv->lang('info')?></th>
			            <th><?php echo $mbs_appenv->lang('push_time')?></th>
			            <th><?php echo $mbs_appenv->lang('status')?></th>
			        </tr>
			    </thead>
			    <tbody>
			    	<?php $k=-1; foreach($list as $k => $row){ 
			    		$info_ctr->setPrimaryKey($row['info_id']);
			    		$info = $info_ctr->get();
			    		$user_ctr->setPrimaryKey($row['recv_uid']);
			    		$recv_user = $user_ctr->get();
			    		if(empty($info) || empty($recv_user))
			    			continue;
			    	?>
			        <tr <?php echo 1 == $k%2 ? 'class=pure-table-odd':'' ?>>
			            <td><input type="checkbox" name="id[]" value="<?php echo $row['id']?>" /><?php echo $row['id']?></td>
			            <td><?php echo $recv_user['name']?></td>
			            <td><div class=title><a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$row['info_id']))?>">
			            	<?php echo CStrTools::txt2html($info['title'])?></a></div>
			            </td>
			            <td><?php echo date('Y-m-d H:i:s', $row['push_time'])?></td>
			            <td><?php echo $mbs_appenv->lang(CInfoPushControl::statusText($row['status']))?></td>
			        </tr>
			     	<?php } if(-1 == $k){ ?>
			     	<tr><td colspan=5 class=no-data><?php echo $mbs_appenv->lang('no_data', 'common')?></td></tr>
			     	<?php }?>
			      </tbody>
			</table>
			<div class="oper-bar" >
				<button class="button-error pure-button" type="submit" name="delete" onclick="return confirm('<?php echo $mbs_appenv->lang('confirmed', 'common')?>');"><?php echo $mbs_appenv->lang('delete')?></button>
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
<script type="text/javascript">
/*(function _watch_operbar(){
	var es = document.getElementsByTagName("div"), i, dest=null;
	for(i=0; i<es.length; i++){
		if("oper-bar" == es[i].className){
			dest = es[i];
			break;
		}
	}
	if(dest){
		var tb = dest.parentNode.getElementsByTagName("table");
		if(tb.clientHeight > document.getElementsByTagName("html")[0].clientHeight*1.6){
			dest.className += " oper-bar-pop";
		}
	}
})();*/
</script>
</body>
</html>