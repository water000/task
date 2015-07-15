<?php 

mbs_import('user', 'CUserSession');
$usersess = new CUserSession();
list($sess_uid, ) = $usersess->get();

if(isset($_REQUEST['delete']) && isset($_REQUEST['id'])){

	mbs_import('', 'CInfoPushControl');
	$info_push_ctr = CInfoPushControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	$info_push_ctr->setPrimaryKey($sess_uid);

	foreach($_REQUEST['id'] as $id){
		$info_push_ctr->destroy(array('id'=>$id));
	}
	$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '', 
			isset($_REQUEST['redirect']) ? urldecode($_REQUEST['redirect']):$mbs_appenv->toURL('push_list'));
	exit(0);
}

if(isset($_REQUEST['user_id'])){
	mbs_import('', 'CInfoPushControl');
	$info_push_ctr = CInfoPushControl::getInstance($mbs_appenv, 
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	
	mbs_import('common', 'WebsocketClient');
	list($ws_host, $ws_port, $ws_path) = $mbs_appenv->config('web_socket');
	$wsock = new WebsocketClient();
	$wsock->connect($ws_host, $ws_port, $ws_path);
	$login = '{"MSG_TOKEN" : "'.session_id().'","MSG_TYPE" : "M_LOGIN"}';
	$ret = $wsock->sendData($login);
	
	$error = array();
	foreach($_REQUEST['user_id'] as $uid){
		$push_num = 0;
		foreach($_REQUEST['info_id'] as $info_id){
			$push_info = array(
				'pusher_uid' => $sess_uid,
				'recv_uid'   => $uid,
				'info_id'    => $info_id,
				'push_time'  => time(),
				'status'     => CInfoPushControl::ST_WAIT_PUSH,
			);
			$ret = $info_push_ctr->add($push_info);
			if(empty($ret)){
				$error[] = $info_push_ctr->error();
			}else{
				++$push_num;
			}
			if($push_num > 0){
				$_msg = sprintf('{"MSG_SENDER" : "%d","MSG_RECEIVER" : "%d","MSG_CREATE_TIME" : "%s","MSG_CONTENT" : "SYSTEM_NEWS_UPDATE","MSG_TYPE" : "M_SYSTEM_COMMAND"}',
						$sess_uid, $uid, time());
				$ret = $wsock->sendData($_msg);
			}
		}
	}
	$wsock->disconnect();
	
	if(empty($error))
		$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '', $mbs_appenv->toURL('push_list'));
	else
		$mbs_appenv->echoex($mbs_appenv->lang('info_had_push'), 'INFO_HAD_PUSH', $mbs_appenv->toURL('push_list'));
	exit(0);
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
.selected_win{display: none; border-left:1px solid #e1e1e1;width:22%;margin-left:1.5%;padding-left:1.2%;}
.selected_win a{float:right;font-size:12px;}
#IDT_JOIN_LIST {}
#IDT_JOIN_LIST li{border-bottom:1px dashed #bbb;padding:2px 5px;}
#IDT_JOIN_LIST li a{float:right;font-size:12px;}
h3{border-bottom:1px solid #eee;padding: 10px 0;margin:0 auto 10px;}
h3 a{font-size:80%;float:right;}
.odd{background-color:#f2f2f2}
.title{font-weight:bold;}
.title span{font-size:80%;font-weight:normal;margin-left:10px;}
.abstract{width:95%; margin:10px auto;color:#555;font-size:80%;}
.pure-u-1-3{width: 29%;margin-left:2%;padding:0 0.5%;border-left:1px solid #ddd;display:none;margin-top: 20px}
.pure-u-2-3 div{padding: 5px;}
</style>
</head>
<body>
<div class=header><?php echo $mbs_appenv->lang('header_html', 'common')?></div>
<div class="pure-g wrapper">
    <div class="pure-u-1">
		<form method="post">
		<div>
			 <div class="pure-u-2-3">
			 	<h3><?php echo $mbs_appenv->lang('push_list')?>
			 		<a href="#" onclick="window.open('<?=$mbs_appenv->toURL('list', 'user')?>', window.attachEvent?null:'_blank,_top', 'height=600,width=900,location=no', true)"><?php echo $mbs_appenv->lang('select_recv_user')?></a></h3>
<?php
$total_info_num = 0;
mbs_import('', 'CInfoControl');
$info_ctr = CInfoControl::getInstance($mbs_appenv, 
	CDbPool::getInstance(), CMemcachedPool::getInstance());
if(isset($_REQUEST['id'])){
	foreach($_REQUEST['id'] as $id){
		$info_ctr->setPrimaryKey($id);
		$info = $info_ctr->get();
		if(empty($info) || $info['creator_id'] != $sess_uid)
			continue;
		++$total_info_num;
?>
				<div <?php echo 0==$total_info_num%2 ? ' class=odd':''?>>
					<input type="hidden" name="info_id[]" value="<?php echo $id?>" />
					<div class=title><a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$info['id']))?>">
						<?php echo CStrTools::txt2html($info['title'])?></a>
						<span><?php echo date('Y-m-d', $info['create_time'])?></span></div>
					<div class=abstract><?php echo CStrTools::txt2html($info['abstract'])?></div>
				</div>
<?php
	}
}
if(0 == $total_info_num){ 
	echo '<div class=no-data>', $mbs_appenv->lang('no_data'), 
		'&nbsp;&nbsp;<a href="',$mbs_appenv->toURL('list'),'">',$mbs_appenv->lang('select_info'),'</a></div>';
}
?>
			 </div>
			 <div class="pure-u-1-3">
    			<ul id=IDT_JOIN_LIST></ul>
    			<button class="pure-button pure-button-primary" style="margin-top:15px;" name="join_member" type="submit">
    				<?php echo $mbs_appenv->lang('push')?></button>	    		
			 </div>
		</div>
		</form>
       
<script type="text/javascript">
var g_join_list = document.getElementById("IDT_JOIN_LIST"), g_selected_user=[];
function _del(oa, id){
	delete g_selected_user[id];
	oa.parentNode.parentNode.removeChild(oa.parentNode);
	if(0 == g_join_list.childNodes.length){
		_switch_width(false);
	}
}
function _switch_width(trun_on){
	if(trun_on){
		g_join_list.parentNode.style.display = "inline-block";
	}else{
		g_join_list.parentNode.style.display = "none";
	}
}
window.cb_class_selected = function(selected_user, popwin){
	if(selected_user.length > 0){
		for(var i=0, j=selected_user.length/2; i<j; i++){
			if("undefined" == typeof g_selected_user[selected_user[i*2]]){
				var li = document.createElement("li");
				li.innerHTML = "<input type=hidden name='user_id[]' value='"+selected_user[i*2]+"' />"
					+selected_user[i*2+1]+'('+selected_user[i*2]
					+')<a href="#" onclick="_del(this, '+selected_user[i*2]+')"><?php echo $mbs_appenv->lang('delete', 'common')?></a>';
				g_join_list.appendChild(li);
				g_selected_user[selected_user[i*2]] = 1;
			}
		}
		popwin.close();

		if(g_join_list.childNodes.length > 0){
			_switch_width(true);
		}else{
			alert("<?php echo $mbs_appenv->lang('exists')?>");
		}
	}
}
</script>
    </div>
</div>
<div class=footer></div>
</body>
</html>