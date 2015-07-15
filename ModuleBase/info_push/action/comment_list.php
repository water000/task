<?php 
mbs_import('', 'CInfoCommentControl');
mbs_import('info', 'CInfoControl');
mbs_import('user', 'CUserControl', 'CUserSession');

$user_ctr = CUserControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$search_keys = array('date'=>'', 'title'=>'');
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

$sql = 'FROM %s i, %s c 
		WHERE i.id=c.info_id 
		AND i.creator_id = %d
		%s
		%s
		%s';
$sql = sprintf($sql, 
		mbs_tbname(CInfoControl::TBNAME), 
		mbs_tbname(CInfoCommentControl::TBNAME),
		$sess_uid,
		isset($req_search_keys['comment_time']) ? 
			sprintf(' AND comment_time>=%d AND comment_time<%d', 
					$req_search_keys['comment_time'][0], $req_search_keys['comment_time'][1]) : '',
		isset($req_search_keys['title']) ? ' AND i.title like ? ' : '',
		isset($_REQUEST['info_id']) ? ' AND c.info_id='.intval($_REQUEST['info_id']) : ''
);
$sql_list = 'SELECT i.*, c.*, c.id as cid '.$sql.' ORDER BY c.id ';
$sql_count = 'SELECT count(1)'.$sql;

$pdos = CDbPool::getInstance()->getDefaultConnection()->prepare($sql_list);
$pdos->execute(isset($req_search_keys['title']) ? $req_search_keys['title'] : null);
$cmt_list = $pdos->fetchAll(PDO::FETCH_ASSOC);

$pdos = CDbPool::getInstance()->getDefaultConnection()->prepare($sql_count);
$pdos->execute(isset($req_search_keys['title']) ? $req_search_keys['title'] : null);
$cmt_count = $pdos->fetchAll(PDO::FETCH_ASSOC);
$cmt_count = empty($cmt_count) ? 0 : $cmt_count[0];
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('piyue.css')?>" />
</head>
<body>
    <div class="allInfo">
    <?php if(isset($_REQUEST['info_id'])){ ?>
        <h2 class="tit">
            <?php echo sprintf($mbs_appenv->lang('info_title'), $cmt_list[0]['title'])?>
            <a href="javascript:;" class="btn-create"><span class="back-icon"></span><?php echo $mbs_appenv->lang('back')?></a>
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
                        <a href="javascript:;" class="content"><?php echo CStrTools::txt2html($row['comment_content'])?></a>
                    </p>
                    <p class="time-con"><?php echo date('Y-m-d H:i', $row['comment_time'])?></p>
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
            <label for="" class="label-word"><?php echo $mbs_appenv->lang(array('comment', 'info'))?>&nbsp;:&nbsp;</label>
            <input type="text" class="inp-keyWord" placeholder="<?php echo $mbs_appenv->lang('please_input')?>">
            <label for="" class="label-word"><?php echo $mbs_appenv->lang(array('comment', 'time'))?>&nbsp;:&nbsp;</label>
            <input type="text" class="inp-keyWord" placeholder="<?php echo $mbs_appenv->lang('please_input')?>">
            <a href="javascript:;" class="btn-search"><?php echo $mbs_appenv->lang('search')?></a>
        </div>

        <!-- 列表 -->
        <div class="box-tabel mb17">
            <div class="top">
                <p class="piyue-person"><?php echo $mbs_appenv->lang('comment_person')?></p>
                <p class="piyue-cont"><?php echo $mbs_appenv->lang(array('comment', 'content'))?></p>
                <p class="piyue-create"><?php echo $mbs_appenv->lang(array('comment', 'time'))?></p>
                <p class="piyue-create"><?php echo $mbs_appenv->lang(array('from', 'comment', 'info'))?></p>
            </div>
            <ul class="ul-list">
               <?php foreach($cmt_list as $row){ $user_ctr->setPrimaryKey($row['comment_uid']); ?>
                <li class="list">
                    <p class="person-name"><?php $u=$user_ctr->get(); echo empty($u)?'(delete)':$u['name']?></p>
                    <p class="con-info">
                        <a href="javascript:;" class="content"><?php echo CStrTools::txt2html($row['comment_content'])?></a>
                    </p>
                    <p class="time-con"><?php echo date('Y-m-d H:i', $row['comment_time'])?></p>
                    <p><a href="<?php echo $mbs_appenv->toURL('comment_list', '', array('info_id'=>$row['info_id']))?>">
                    	<?php echo CStrTools::txt2html($row['title'])?></a></p>
                </li>
            <?php } ?>
            </ul>
        </div>
        <!-- 列表end -->
        <div class="box-bottom">
            <p class="pageBox">
                <a href="javascript:;" class="btn-page">上一页</a>
                <a href="javascript:;" class="btn-page">1</a>
                <a href="javascript:;" class="btn-page">2</a>
                <a href="javascript:;" class="btn-page check">3</a>
                <a href="javascript:;" class="btn-page">4</a>
                <a href="javascript:;" class="btn-page">5</a>
                <a href="javascript:;" class="btn-page">下一页</a>
            </p>
        </div>
    <?php } ?>
    </div>
</body>
</html>