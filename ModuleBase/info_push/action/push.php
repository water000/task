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
	$mbs_appenv->echoex($mbs_appenv->lang('operation_success'), '', $mbs_appenv->toURL('push_list'));
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
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('sendNew.css')?>" />
</head>
<body>
<div class="sendNew">
	<h2 class="tit"><?php echo $mbs_appenv->lang(array('push', 'new', 'info'))?></h2>
	<div class="content">
		<form action="" name=_form method="post">
		<div class="partLeft">
			<h3 class="subTit"><?php echo $mbs_appenv->lang(array('to_be', 'push', 'info'))?>
				(<?php echo isset($_REQUEST['id']) ? count($_REQUEST['id']) : 0, $mbs_appenv->lang('info_unit')?>)&nbsp;:&nbsp;</h3>
			<ul id=IDU_LIST class="ul-news">
			<?php
			$total_info_num = 0;
			mbs_import('info', 'CInfoControl');
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
				<li class="list-news">
					<input type="hidden" name="info_id[]" value="<?php echo $id?>" />
					<h4 class="tit-news"><a href="<?php echo $mbs_appenv->toURL('edit', 'info', array('id'=>$_REQUEST['id']))?>">
						<?php echo CStrTools::txt2html($info['title'])?></a></h4>
					<p class="word-news"><?php echo $info['abstract']?></p>
					<a href="javascript:;" onclick="_remove_info(this);" 
						class="btn-dele"><?php echo $mbs_appenv->lang('remove')?></a>
				</li>
			<?php }} ?>
				<li><a href="javascript:;" class="add-news">+<?php echo $mbs_appenv->lang(array('continue', 'add', 'info'))?>...</a></li>
			</ul>
		</div>
		<div class="partRight">
			<?php
				mbs_import('user', 'CUserControl', 'CUserClassControl'); 
				$user_ctr = CUserControl::getInstance($mbs_appenv,
					CDbPool::getInstance(), CMemcachedPool::getInstance());
				$uclass_ctr = CUserClassControl::getInstance($mbs_appenv, 
					CDbPool::getInstance(), CMemcachedPool::getInstance());
				$ulist = $user_ctr->getDB()->search(array('class_id'=>array(1, 100)), array('order' => 'class_id desc'));
				$ulist = $ulist->fetchAll();
			?>
			<h3 class="subTit"><?php echo $mbs_appenv->lang('recipient')?>(<?php echo count($ulist) , $mbs_appenv->lang('person')?>)&nbsp;:&nbsp;</h3>
			<div class="container">
				<i class="ico-sear"></i>
				<input type="text" class="inp-sear" placeholder="<?php echo $mbs_appenv->lang('input_name_to_search')?>..." />
				<?php
				$cid = 0;
				foreach($ulist as $u){
					if($u['class_id'] != $cid){
						if($cid != 0) echo '</ul>';
						$uclass_ctr->setPrimaryKey($u['class_id']);
						$ucname = $uclass_ctr->get();
						$cid = $u['class_id'];
				?>
				<p class="nav-tab"><i class="ico-arrow"></i><?php echo empty($ucname) ? 'delete':$ucname['name'] ?>
					<a href="javascript:;" onclick="_checkall(this)" class="btn-checkAll"><?php echo $mbs_appenv->lang('check_all')?></a></p>
				<ul class="ul-people">
					<?php } ?>
					<li class="list-name"><label class="labelH"><?php echo $u['name']?>
						<input type="checkbox" class="check-part" name="user_id[]" value="<?php echo $u['id']?>"></label>
					</li>
				<?php } ?>
			</div>
		</div>
		<div class="btnBox">
			<a href="javascript:_submit(document._form);" class="btn-send"><?php echo $mbs_appenv->lang(array('confirm', 'push'))?></a>
		</div>
		</form>
	</div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-1.3.1.min.js')?>"></script>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery.avgrund.js')?>"></script>
<script type="text/javascript">
var title_count = document.getElementsByTagName("h3")[0];

function _checkall(chkbox){
	var list;
	for(list=chkbox.parentNode.nextSibling; list; list=list.nextSibling){
		if("UL" == list.tagName){
			break;
		}
	}
	if(list){
		var cblist = list.getElementsByTagName("input"), i, checked = cblist.length>0?!cblist[0].checked : false;
		for(i=0; i<cblist.length; i++){
			cblist[i].checked = checked;
		}
	}
}

var link = window.top.document.createElement("link");
window.top.document.body.appendChild(link);
link.href = "<?php echo $mbs_appenv->sURL('avgrund.css')?>"; 
link.rel="stylesheet";

$('.avgrund-popin', window.top.document).remove();

var avgrund = $('.add-news').avgrund({
	height: 515,
	width: 900,
	holderClass: 'avgrund-custom',
	showClose: true,
	showCloseText: '<?php echo $mbs_appenv->lang('close')?>',
	title: '<?php echo $mbs_appenv->lang('select_info_to_push')?>',
	onBlurContainer: '.container',
	body: window.top.document.getElementsByTagName("div")[0],
	template: function(obj){
		return '<iframe style="width:100%;height:100%;" src="<?php echo $mbs_appenv->toURL('list', 'info', array('popup'=>'1'))?>'
			+_req_info()+'"></iframe>';
	}
});
window.top.cb_info_selected = function(sel_list){
	var list = document.getElementById("IDU_LIST"), 
		ch = list.getElementsByTagName("li"),
		last = ch[ch.length-1];
	for(var i=0, _new; i<sel_list.length; i++){
		_new = document.createElement("li");
		_new.className = "list-news";
		_new.innerHTML = '<input type="hidden" name="info_id[]" value="'+sel_list[i].id+'" />'+
			'<h4 class="tit-news"><a href="javascript:;">'+sel_list[i].title+'</a></h4>'+
			'<p class="word-news">'+sel_list[i].abstract+'</p>'+
			'<a href="javascript:;" onclick="_remove_info(this)"'+
			'class="btn-dele"><?php echo $mbs_appenv->lang('remove')?></a>';
		list.insertBefore(_new, last);
	}
	avgrund.deactivate();
	title_count.innerHTML = title_count.innerHTML.replace(/[\d]+/, function(n){return parseInt(n)+i});
}
function _req_info(){
	var selected_info = document._form.elements["info_id"], ret="";
	if(selected_info){
		selected_info = selected_info.length ? selected_info : [selected_info];
		var i;
		for(i=0; i<selected_info.length; i++){
			ret += "&info_id%5B%5D="+selected_info[i].value;
		}
	}
	return ret;
}
function _remove_info(btn){
	btn.parentNode.parentNode.removeChild(btn.parentNode);
	title_count.innerHTML = title_count.innerHTML.replace(/[\d]+/, function(n){return parseInt(n)-1});
}
function _submit(f){
	if(f.elements["info_id[]"]){
		var uid_list = f.elements["user_id[]"], uid_list = uid_list.length ? uid_list : [uid_list];
		for(var i=0; i<uid_list.length; i++){
			if(uid_list[i].checked){
				f.submit();
				return true;
			}
		}
	}
	return false;
}

</script>
</body>
</html>