<?php 
mbs_import('', 'CInfoPushControl');
mbs_import('user', 'CUserSession', 'CUserControl');

$info_push_ctr = CInfoPushControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$user_sess = new CUserSession();
list($uid, ) = $user_sess->get();

define('ROWS_PER_PAGE', 10);
define('PAGE_ID',  isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1);
define('ROWS_OFFSET', (PAGE_ID-1)*ROWS_PER_PAGE);

$cond = array('pusher_uid' => $uid, 'info_id'=>$_REQUEST['info_id']);
$push_count = $info_push_ctr->getDB()->count(array('info_id'=>$_REQUEST['info_id']));
$read_count = $info_push_ctr->getDB()->count(array('info_id'=>$_REQUEST['info_id'],
		'status'=>CInfoPushControl::ST_HAD_READ));

$plist = $page_num_list = array();
if($push_count > ROWS_OFFSET){
	$opts = array(
		'offset' => ROWS_OFFSET,
		'limit'  => ROWS_PER_PAGE,
		'order'  => ' id desc',
	);
	$plist = $info_push_ctr->getDB()->search($cond, $opts)->fetchAll(PDO::FETCH_ASSOC);
	
	mbs_import('common', 'CTools');
	$page_num_list = CTools::genPagination(PAGE_ID, ceil($push_count/ROWS_PER_PAGE), 8);
}

$user_ctr = CUserControl::getInstance($mbs_appenv,
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
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('tuisong.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('allInfo.css')?>">
<style type="text/css">
.pop .list {
    position: relative;
    padding: 5px 0;
    font-size: 0;
    border-bottom: 1px solid #e3e3e3;
    overflow: hidden;
    height:25px; }
.pop .list:nth-child(2n) {
    background-color: #f8f8f8; }
.pop .list .check-part {
    position: absolute;
    left: 15px;
    top: 27px; }
.pop .list .con-info {
    padding-left: 44px; }
.pop .list .link-tit {
    font-size: 14px;
    color: #0a4f9c;
    display: block;
    line-height: 20px;
    width: 670px;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden; }
.pop .list .subWord {
    width: 570px;
    font-size: 12px;
    color: #666;
    display: block;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden; }
.pop .list .time-con {
    width: 100px;
    line-height: 56px;
    position: absolute;
    top: 0;
    right: 94px;
    padding-left: 9px;
    font-size: 12px;
    color: #333; }
.pop .list .format-con {
    width: 64px;
    line-height: 56px;
    position: absolute;
    top: 0;
    right: 0;
    padding-left: 9px;
    font-size: 12px;
    color: #333; }
  
.pop .list .check-part {
    position: absolute;
    left: 15px;
    top: 27px; }
    
.datail-tit .check-part{vertical-align:middle;margin-left:10px;}
    
.box-bottom{margin-top:15px;padding:15px 0 15px 13px;display:block;width:auto;}
</style>
</head>
<body>
<section class="pop">
    <div class="content">
        <div class="datail-tit"><?php echo sprintf($mbs_appenv->lang('push_and_read'), $push_count, $read_count)?>
           <div class="right-datail-tit"><?php echo $mbs_appenv->lang('status_filter')?>&nbsp;:
           	<input type="checkbox" class="check-part" name="check[]" /><?php echo $mbs_appenv->lang('had_read')?>
           	<input type="checkbox" class="check-part" name="check[]" /><?php echo $mbs_appenv->lang('unread')?></div>
        </div>
        <form name=form_list method=post action="<?php echo $mbs_appenv->toURL('push', '', array('delete'=>1, 'redirect'=>urlencode($mbs_appenv->toURL('push_detail', '', array('info_id'=>$_REQUEST['info_id'])))))?>">
        <ul class="ul-list">
        	<li class="list" style="padding: 0;background-color: #e8e8e8;line-height: 25px;">
                <input type="checkbox" class="check-part" style="top: 10px">
                <p class="receive-person"><?php echo $mbs_appenv->lang('recipient')?></p>
                <p class="receive-time"><?php echo $mbs_appenv->lang('push_time')?></p>
                <p class="read-state"><?php echo $mbs_appenv->lang('read_status')?></p>
            </li>
        <?php 
        $j = -1;
        foreach($plist as $j => $v){
			$user_ctr->setPrimaryKey($v['recv_uid']);
			$recv_user = $user_ctr->get();
			if(empty($recv_user))
				continue; 
		?>
            <li class="list" >
                <input type="checkbox" name="id[]" value="<?php echo $v['id']?>" class="check-part-t ">
                <p class="receive-name"><?php echo $recv_user['name']?></p>
                <p class="receive-Time"><?php echo date('Y-m-d H:i', $v['push_time'])?></p>
                <p class="read-State">
                	<?php if(CInfoPushControl::ST_HAD_READ == $v['status']){ ?>
                	<span class="state"><?php echo $mbs_appenv->lang('had_read')?></span>
                	<span class="State-Time"><?php echo date('Y-m-d H:i', $v['request_time'])?></span>
                	<?php }else{ ?>
                	<span ><?php echo $mbs_appenv->lang('unread');?></span>
                	<?php }?>
                </p>
            </li>
        <?php } ?>
        </ul>
        <div class="box-bottom">
        	<?php if(count($plist) > 0){ ?>
            <a href="javascript:;" class="btn-del" onclick="document.form_list.submit();">
				<i class="ico"></i><?php echo $mbs_appenv->lang('delete')?>
			</a>
			<?php } ?>
            <?php if(count($page_num_list) > 1){?>
			<p class="pageBox">
				<?php if(PAGE_ID > 1){ ?>
				<a href="<?php echo $mbs_appenv->toURL('push_detail', '', array('page_id'=>PAGE_ID-1)) ?>" 
					class="btn-page"><?php echo $mbs_appenv->lang('prev_page')?></a>
				<?php } ?>
	        	<?php foreach($page_num_list as $n => $v){ ?>
	        	<a href="<?php echo $mbs_appenv->toURL('push_detail', '', array('page_id'=>$n)) ?>" 
	        		class="btn-page <?php echo $n==PAGE_ID?' check':''?>" ><?php echo $v?></a>
	        	<?php }?>
	        	<?php if(PAGE_ID < count($page_num_list)){ ?>
		        <a href="<?php echo $mbs_appenv->toURL('push_detail', '', array('page_id'=>PAGE_ID+1)) ?>" 
		        	class="btn-page"><?php echo $mbs_appenv->lang('next_page')?></a>
		        <?php }?>
		    </p>
			<?php } ?>
        </div>
        </form>
    </div>
</section>
</body>
</html>