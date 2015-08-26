<?php 
mbs_import('', 'CInfoCommentControl');
mbs_import('info', 'CInfoControl');
mbs_import('user', 'CUserControl', 'CUserSession');

$user_ctr = CUserControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$search_keys = array('date'=>'', 'title'=>'', 'comment_user'=>'');
$req_search_keys = array_intersect_key($_REQUEST, $search_keys);
foreach($req_search_keys as $k=> $v){
	$req_search_keys[$k] = trim($req_search_keys[$k]);
	if(0 == strlen($req_search_keys[$k])){
		unset($req_search_keys[$k]);
	}
}

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
		$req_search_keys['comment_time'] = array($tstart, $tend);
	}
}

$search_keys = array_merge($search_keys, $req_search_keys);

$user_sess = new CUserSession();
list($sess_uid, ) = $user_sess->get();

$sql = 'FROM %s i, %s c, %s u
		WHERE i.id=c.info_id  AND u.id = c.comment_uid
		AND i.creator_id = %d
		%s
		%s
		%s
		%s';
$sql = sprintf($sql, 
		mbs_tbname(CInfoControl::TBNAME), 
		mbs_tbname(CInfoCommentControl::TBNAME),
		$user_ctr->getDB()->tbname(),
		$sess_uid,
		isset($req_search_keys['comment_time']) ? 
			sprintf(' AND comment_time>=%d AND comment_time<%d', 
					$req_search_keys['comment_time'][0], $req_search_keys['comment_time'][1]) : '',
		isset($req_search_keys['title']) ? ' AND comment_content like ? ' : '',
		isset($_REQUEST['info_id']) ? ' AND c.info_id='.intval($_REQUEST['info_id']) : '',
		isset($req_search_keys['comment_user']) ? ' AND u.name like ? ' : ''
);

$exec_values = array();
if(isset($req_search_keys['title']))
	$exec_values[] = '%'.$req_search_keys['title'].'%';
if(isset($req_search_keys['comment_user']))
	$exec_values[] = '%'.$req_search_keys['comment_user'].'%';

$sql_count = 'SELECT count(1) as count '.$sql;

try {
	$pdos = CDbPool::getInstance()->getDefaultConnection()->prepare($sql_count);
	$pdos->execute($exec_values);
	$cmt_count = $pdos->fetchAll(PDO::FETCH_ASSOC);
	$cmt_count = empty($cmt_count) ? 0 : $cmt_count[0]['count'];
	
	define('ROWS_PER_PAGE', 8);
	define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
	define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);
	$cmt_list = $page_num_list = array();
	
	if($cmt_count > ROWS_OFFSET){
		$sql_list = 'SELECT i.*, c.*, c.id as cid '.$sql.' ORDER BY c.id DESC LIMIT '.ROWS_OFFSET.','.ROWS_PER_PAGE;
		$pdos = CDbPool::getInstance()->getDefaultConnection()->prepare($sql_list);
		$pdos->execute($exec_values);
		$cmt_list = $pdos->fetchAll(PDO::FETCH_ASSOC);
	
		mbs_import('common', 'CTools');
		$page_num_list = CTools::genPagination(PAGE_ID, ceil($cmt_count/ROWS_PER_PAGE), 8);
	}
} catch (Exception $e) {
	echo $e->getMessage();
}

mbs_import('', 'CInfoPushStatControl');
$info_push_stat = CInfoPushStatControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$info_push_stat->setPrimaryKey(0);
$info_push_stat->set(array(
	'new_comment_count' => 0
));
$info_push_stat->getDB()->resetNewCommentCount($sess_uid, mbs_tbname(CInfoControl::TBNAME));

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('piyue.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('ui.daterangepicker.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('jquery-ui-1.7.1.custom.css')?>" type="text/css" title="ui-theme" />
</head>
<body>
    <div class="allInfo">
    <?php if(isset($_REQUEST['info_id'])){ ?>
        <h2 class="tit">
            <?php echo sprintf($mbs_appenv->lang('info_title'), $cmt_list[0]['title'])?>
            <a href="<?php echo $mbs_appenv->toURL('comment_list')?>" class="btn-create"><span class="back-icon"></span><?php echo $mbs_appenv->lang('back')?></a>
        </h2>
        <!-- 列表 -->
        <div class="box-tabel mb17">
            <div class="top">
                <p class="piyue-person"><?php echo $mbs_appenv->lang('comment_person')?></p>
                <p class="piyue-cont"><?php echo $mbs_appenv->lang(array('comment', 'content'))?></p>
                <p class="piyue-create"><?php echo $mbs_appenv->lang(array('comment', 'time'))?></p>
            </div>
            <ul class="ul-list">
            <?php foreach($cmt_list as $row){ $user_ctr->setPrimaryKey($row['comment_uid']); ?>
                <li class="list">
                    <p class="person-name"><?php $u=$user_ctr->get(); echo empty($u)?'(delete)':$u['name']?></p>
                    <p class="con-info">
                        <a class="content" style="white-space: normal;text-decoration:none;width:800px;"><?php echo CStrTools::txt2html($row['comment_content'])?></a>
                    </p>
                    <p class="piyue-create time-con"><?php echo date('Y-m-d H:i', $row['comment_time'])?></p>
                </li>
            <?php } ?>
            </ul>
        </div>
        <!-- 列表end -->
	<?php }else{ ?>
		<h2 class="tit">
			<?php echo $mbs_appenv->lang(array('comment', 'manage'))?>
			<span class="tips"><?php echo sprintf($mbs_appenv->lang('total_count'), $cmt_count)?></span>
        </h2>
        <div class="searchBox">
        <form action="" method="get" name="_search_form">
            <label for="" class="label-word"><?php echo $mbs_appenv->lang(array('comment', 'info'))?>&nbsp;:&nbsp;</label>
            <input type="text" class="inp-keyWord" name="title" 
            	value="<?php echo $search_keys['title']?>" placeholder="<?php echo $mbs_appenv->lang('please_input')?>">
            <label for="" class="label-word"><?php echo $mbs_appenv->lang(array('comment', 'time'))?>&nbsp;:&nbsp;</label>
            <input type="text" class="inp-keyWord" name="date" id=IDI_DATE style="width: 140px;"
            	value="<?php echo $search_keys['date']?>" placeholder="<?php echo $mbs_appenv->lang('please_input')?>">
            <label for="" class="label-word"><?php echo $mbs_appenv->lang(array('comment_person'))?>&nbsp;:&nbsp;</label>
            <input type="text" class="inp-keyWord" name="comment_user" 
            	value="<?php echo $search_keys['comment_user']?>" placeholder="<?php echo $mbs_appenv->lang('please_input')?>">
            <a href="javascript:document._search_form.submit();" class="btn-search"><?php echo $mbs_appenv->lang('search')?></a>
        </form>
        </div>
        <script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-1.3.1.min.js')?>"></script>
		<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-ui-1.7.1.custom.min.js')?>"></script>
		<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('daterangepicker_cn.jQuery.js')?>"></script>
		<script type="text/javascript">	
		$(function(){
			$('#IDI_DATE').daterangepicker({dateFormat:"yy/m/d"}); 
		});
		</script>

        <!-- 列表 -->
        <div class="box-tabel mb17">
            <div class="top">
                <p class="piyue-person"><?php echo $mbs_appenv->lang('comment_person')?></p>
                <p class="piyue-cont"><?php echo $mbs_appenv->lang(array('comment', 'content'))?></p>
                <p class="piyue-create piyue-create2"><?php echo $mbs_appenv->lang(array('comment', 'time'))?></p>
                <p class="piyue-info"><?php echo $mbs_appenv->lang(array('from', 'comment', 'info'))?></p>
            </div>
            <ul class="ul-list">
               <?php foreach($cmt_list as $row){ $user_ctr->setPrimaryKey($row['comment_uid']); ?>
                <li class="list">
                    <p class="person-name"><?php $u=$user_ctr->get(); echo empty($u)?'(delete)':$u['name']?></p>
                    <p class="con-info">
                        <a class="content" style="width: 480px;text-decoration:none;"><?php echo CStrTools::txt2html($row['comment_content'])?></a>
                    </p>
                    <p class="piyue-create piyue-create2 time-con"><?php echo date('Y-m-d H:i', $row['comment_time'])?></p>
                    <p class="piyue-info from-info"><a href="<?php echo $mbs_appenv->toURL('comment_list', '', array('info_id'=>$row['info_id']))?>">
                    	<?php echo CStrTools::txt2html($row['title'])?></a></p>
                </li>
            <?php } ?>
            </ul>
        </div>
        <!-- 列表end -->
        <div class="box-bottom">
		<?php if(count($page_num_list) > 1){?>
		<p class="pageBox">
			<?php if(PAGE_ID > 1){ ?>
			<a href="<?php echo $mbs_appenv->toURL('comment_list', '', array_merge($search_keys, array('page_id'=>PAGE_ID-1))) ?>" 
				class="btn-page"><?php echo $mbs_appenv->lang('prev_page')?></a>
			<?php } ?>
        	<?php foreach($page_num_list as $n => $v){ ?>
        	<a href="<?php echo $mbs_appenv->toURL('comment_list', '', array_merge($search_keys, array('page_id'=>$n))) ?>" 
        		class="btn-page <?php echo $n==PAGE_ID?' check':''?>" ><?php echo $v?></a>
        	<?php }?>
        	<?php if(PAGE_ID < count($page_num_list)){ ?>
	        <a href="<?php echo $mbs_appenv->toURL('comment_list', '', array_merge($search_keys, array('page_id'=>PAGE_ID+1))) ?>" 
	        	class="btn-page"><?php echo $mbs_appenv->lang('next_page')?></a>
	        <?php }?>
	    </p>
		<?php } ?>
		</div>
    <?php } ?>
    </div>
</body>
</html>