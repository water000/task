<?php 
mbs_import('', 'CInfoCommentControl', 'CInfoControl');
mbs_import('user', 'CUserControl');

$user_ctr = CUserControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$search_keys = array( 'title'=>'');
$req_search_keys = array_intersect_key($_REQUEST, $search_keys);
foreach($req_search_keys as $k=> $v){
	$req_search_keys[$k] = trim($req_search_keys[$k]);
	if(0 == strlen($req_search_keys[$k])){
		unset($req_search_keys[$k]);
	}
}
$search_keys = array_merge($search_keys, $req_search_keys);

define('LATEST_DAYS', 7);
$sql = 'SELECT i.*, c.*, c.id as cid FROM %s i, %s c 
		WHERE i.id=c.info_id 
		AND c.comment_time>=%d AND c.comment_time<%d
		%s
		ORDER BY c.id ';
$today = mktime(0, 0, 0);
$sql = sprintf($sql, 
		mbs_tbname(CInfoControl::TBNAME), 
		mbs_tbname(CInfoCommentControl::TBNAME),
		$today - 6*86400,
		$today + 86400,
		isset($req_search_keys['title']) ? ' AND i.title like ? ' : ''
);
$pdos = CDbPool::getInstance()->getDefaultConnection()->prepare($sql);
$pdos->execute(isset($req_search_keys['title']) ? $req_search_keys['title'] : null);
$cmt_list = $pdos->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
.blocked{display:block;}
.search-bar{text-align:right;}
.info-list{overflow-x:hidden;width:500px;display:inline-block;}
.info-comment-list{position:fixed;width:520px;overflow-y:auto;display:inline-block;margin-top:15px;}
.list{width:98%;margin:5px auto;}
.list p{font-weight:bold;margin:0;padding:5px 0;border-bottom:1px solid #eee;}
.list p span{font-size:80%;color:blue;margin-left:16px;}
.info-selected{background:#eee;}
.info-item{margin:5px 0;}

.title{font-weight:bold;}
.abstract{color:#555;font-size:80%;margin:6px 0;}
.popimg{position:fixed;top:0;left:0;width:100%;height:100%;display:none;background:#333;}
.popimg div{height:89%;width:89%;margin:5%;overflow:auto;}
.popimg img, .popimg video{vertical-align:middle;display:block;margin:0 auto;}
div.thumb_img{position:relative;margin-right:10px;}
.thumb_img .player{position:absolute;width:50%;height:50%;top:25%;left:25%;
	background: url(<?php echo $mbs_appenv->sURL('info/player.png')?>) no-repeat center center;}
</style>
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g wrapper">
	<div class="search-bar pure-u-1">
		<form class="pure-form" method="post">
    		<fieldset>
       			<?php echo $mbs_appenv->lang(array('info', 'title'))?>
        		<input type="text" name="title" value="<?php echo htmlspecialchars($search_keys['title'])?>" />
       			<button type="submit" class="pure-button pure-button-primary"><?php echo $mbs_appenv->lang('search')?></button>
         	</fieldset>
		</form>
	</div>
	<div class="pure-u-1">
		<div class="info-list">
			<div class=list>
				<p>
					<?php echo $mbs_appenv->lang(array('info', 'list'))?>
					<span><?php echo sprintf($mbs_appenv->lang('latest_days'), LATEST_DAYS)?></span>
				</p>
				<?php 
				while (count($cmt_list) > 0){
					$first_row = $cmt_list[0];
					$info_id = $cmt_list[0]['info_id'];
					$cur_cmt_list = array();
					foreach($cmt_list as $k => $row){
						if($row['info_id'] == $first_row['info_id']){
							$user_ctr->setPrimaryKey($row['comment_uid']);
							$comment_user = $user_ctr->get();
							$cur_cmt_list[] = array(
								'id'              => $row['cid'], 
								'comment_uid'     => $row['comment_uid'], 
								'comment_uname'   => empty($comment_user) ? 'unknown' : $comment_user['name'],
								'comment_content' => $row['comment_content'],
								'comment_time'    => date('m-d H:i', $row['comment_time'])
							);
							unset($cmt_list[$k]);
						}
					}
				?>
				<div class="pure-g info-item" comment-data='<?php echo json_encode($cur_cmt_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)?>'>
					<?php if($first_row['attachment_path'] != ''){ ?>
		            <div class="thumb_img pure-u">
						<img __to_url="<?php echo $mbs_appenv->uploadURL($first_row['attachment_path'])?>" 
			            	src="<?php echo $first_row['attachment_format'] == CInfoControl::AT_VDO ? 
							$mbs_appenv->sURL('info/white-bg-50-50.png') :  $mbs_appenv->uploadURL($first_row['attachment_path']).CInfoControl::MIN_ATTACH_SFX?>" 
			            	alt="<?php echo $first_row['attachment_name']; ?>"
			            	title="<?php echo $first_row['attachment_name']; ?>" />
			             <?php if($first_row['attachment_format'] == CInfoControl::AT_VDO){ ?>
			             <div class=player __video_type="video/<?=pathinfo($first_row['attachment_name'], PATHINFO_EXTENSION )?>"></div>
			             <?php }?>
		            </div>
		            <?php } ?>
					<div class="pure-u-3-5">
						<div class=title>
		            		<a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$first_row['id']))?>">
		            		<?php echo CStrTools::txt2html($first_row['title'])?></a>
		            	</div>
		            	<div class=abstract><?php echo CStrTools::txt2html($first_row['abstract'])?></div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="info-comment-list">
			<div class=list>
				<table class=pure-table style="width: 100%">
					<thead><tr>
						<th>#</th>
						<th><?php echo $mbs_appenv->lang('comment_person')?></th>
						<th width=60%><?php echo $mbs_appenv->lang('content')?></th>
						<th><?php echo $mbs_appenv->lang('time')?></th>
					</tr></thead>
					<tr><td>1</td><td>tester</td><td>批阅测试，南京多处被淹</td><td>6-28 10:20</td></tr>
				</table>
			</div>
		</div>
	</div>
</div>
<div class=footer></div>
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
</script>
</body>
</html>