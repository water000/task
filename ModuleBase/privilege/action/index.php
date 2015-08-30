<?php 

mbs_import('user', 'CUserSession');
$us = new CUserSession();
$user_id = $us->checkLogin();
if(empty($user_id)){
	echo $us->getError();
	exit(0);
}

mbs_import('privilege', 'CPrivUserControl', 'CPrivGroupControl');

$priv_info = null;
try {
	$pu = CPrivUserControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	$priv_info = $pu->getDB()->search(array('user_id' => $user_id));
} catch (Exception $e) {
	echo $mbs_appenv->lang('db_exception', 'common');
	exit();
}
if(empty($priv_info) || !($priv_info = $priv_info->fetchAll(PDO::FETCH_ASSOC))){
	echo 'access denied(1)';
	exit(0);
}
$priv_info = $priv_info[0];

$pg = CPrivGroupControl::getInstance($mbs_appenv, CDbPool::getInstance(), 
	CMemcachedPool::getInstance(), $priv_info['priv_group_id']);
$priv_list = $pg->get();
if(empty($priv_list)){
	echo 'access denied(2)';
	exit(0);
}

$priv_group = CPrivGroupControl::decodePrivList($priv_list['priv_list']);


function _fn_icon($mod, $ac){
	static $icon_map = array(
		'info.edit' => 'ico1',
		'info.list' => 'ico2',

		'info_push.push'         => 'ico3',
		'info_push.push_list'    => 'ico4',
		'info_push.comment_list' => 'ico5',
		
		'user.list'              => 'ico6',
		'user.class'             => 'ico7',
		'user.department'        => 'ico8',
			
		'privilege.group_list'   => 'ico11',
		'privilege.edit_group'   => 'ico12',
		'privilege.rematch_action' => 'ico13',
	);
	echo isset($icon_map[$mod.'.'.$ac]) ?  '<i class="ico '. $icon_map[$mod.'.'.$ac]. '"></i>' : '';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
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
	<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>">
	<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('zebra-dialog/zebra_dialog.css')?>" />
	<style type="text/css">
	iframe{width:100%;border:0;}
	.myclass td.col1{font-size:12px;font-weight:bold;}
	.myclass td.col2{padding-left:15px;cursor:pointer;color:rgb(20, 90,170);}
	.myclass td.col2 b{color:red;padding-right:3px;}
	.myclass td.col2 a{text-decoration:none;}
	.myclass td.col2 a:hover{text-decoration:underline;}
	</style>
</head>
<body>
<div class=top-win>
	<!-- 头部 -->
	<header class="header"><?php echo $mbs_appenv->lang('header_html')?></header>
	<!-- 头部end -->
	<!-- 左边栏 -->
	<nav id="navBar">
		<dl class="navBar">
<?php 
$mgr_notify_list = array();

$mod_list = isset($priv_group[CPrivGroupControl::PRIV_TOPMOST]) ? 
	$mbs_appenv->getModList() : array_keys($priv_group);
$actions = array();
foreach($mod_list as $mod){
	if('core' == $mod) continue;
	$moddef=mbs_moddef($mod);
	if(empty($moddef)) continue;
	$actions = $moddef->filterActions(CModDef::P_MGR);
	if(empty($actions)) continue;
	if(isset($priv_group[$mod])){
		foreach($priv_group[$mod] as $ac){
			if(!isset($actions[$ac])){
				unset($actions[$ac]);
			}
		}
	}
?>
		<dt class="group-type"><?php echo $moddef->item(CModDef::MOD, CModDef::G_TL)?></dt>
<?php 
foreach($actions as $ac => $def){ 
	if(isset($def[CModDef::P_MGNF])){
		$mgr_notify_list[$mod.'.'.$ac] = $def[CModDef::P_MGNF];
		continue;
	} 
	if(isset($def[CModDef::P_NCD])) continue; 
?>
		<dd class="type"><a href="#" class="link-type" data="<?php echo $mbs_appenv->toURL($ac, $mod)?>" onclick="_to(this)">
			<?php _fn_icon($mod, $ac)?><?php echo $def[CModDef::P_TLE]?></a></dd>
<?php
}}
?>
		</dl>
	</nav>
	<!-- 左边栏end -->
	<!-- 内容主体 -->
	<section class="wrap" id="wrap">
		<iframe src=""></iframe>
	</section>
	<!-- 内容主体end -->

	<!-- 加载jquery.js -->
	<!--[if ie 6]>
	<script src="<?php echo $mbs_appenv->sURL('jquery-1.10.2.js')?>"></script>
	<script src="<?php echo $mbs_appenv->sURL('fixIE6.js')?>"></script>
	<![endif]-->
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-1.10.2.js')?>"></script>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('zebra_dialog.js')?>"></script>

<script type="text/javascript">
var frame = document.getElementsByTagName("iframe")[0], prev = null, visit_actions = [];
var links = document.getElementsByTagName("a"), i, j=0, firstlink=null;

for(i=0; i<links.length; i++){
	if("DD" == links[i].parentNode.tagName){
		if(document.location.search.indexOf("to=") != -1){
			if(document.location.href.indexOf(encodeURIComponent(links[i].getAttribute("data"))) != -1){
				_to.call(links[i], decodeURIComponent(document.location.search.substr(4)));
				break;
			}
		}
		else{
			_to(links[i]);
			break;
		}
	}
}

function _to(link, is_redirect){
	var url;
	
	if("object" == typeof link){
		url = link.getAttribute("data");
	}else{
		url = link;
		link = this;
	}

	is_redirect = 1 == arguments.length ? true : is_redirect;
	
	if(prev != null){
		prev.className = prev.className.replace("check", "");
	}
	link.className += " check";
	prev = link;
	
	if(is_redirect){
		frame.src = url;
	}
}

frame.onload = frame.onreadystatechange = function(e){ //onload: for chrom
	if (frame.contentWindow.document.readyState=="complete"){
		frame.style.height=(document.getElementsByTagName("html")[0].clientHeight-65)+"px";
		document.title = frame.contentWindow.document.title;
		history.pushState(null, null, "<?php echo $mbs_appenv->item('cur_action_url') ?>?to="
				+encodeURIComponent( frame.contentWindow.location.href));
		//frame.contentWindow.document.body.onclick = function(e){
		//	if(prev)
		//		prev.className = "blur_a";
		//}
		
		if( -1 == frame.contentWindow.document.location.href.indexOf(prev.getAttribute("data")) ){
			for(var i=0; i<links.length; i++){
				if(frame.contentWindow.document.location.href.indexOf(links[i].getAttribute("data")) != -1){
					_to(links[i], false);
					break;
				}
			}
			/*if(i == links.length){
				document.location = frame.contentWindow.document.location.href;
			}*/
		}

		document.onkeydown = frame.contentWindow.document.onkeydown = function(e){
			e = e || this.parentWindow.event;
			if(116 == (e.keyCode || e.which)){ // forriden F5 key in parent window
				frame.contentWindow.document.location.reload();
				e.returnValue = false;
				e.cancelBubble = true;
				e.keyCode = 0;
				return false;
			}
		}
	}
}

var _zbdlg, _body, item_map={};
function _init_dialog(){
	_zbdlg = new $.Zebra_Dialog("<table id=IDT_ZDDLG></table>", {
		'title': '<?php echo $mbs_appenv->lang('mgr_msg_notify')?>',
		 'custom_class':  'myclass',
	    'buttons':  false,
	    'modal': false,
	    'type':     'question',
	    'position': ['right - 20', 'bottom - 20'],
	});
	_body = document.getElementById("IDT_ZDDLG");
}
var _click = function(url, obj, id){
	frame.src = url;
	obj.parentNode.parentNode.parentNode.removeChild(obj.parentNode.parentNode);
	item_map[id]  = null;
}
function _handle(data){
	if('SUCCESS' == data.retcode && data.data.length > 0){
		if(!_zbdlg)
			_init_dialog();
		var tr;
		for(var i=0; i<data.data.length; i++){
			if(item_map[data.data[i].id]){
				tr = item_map[data.data[i].id];
				tr.innerHTML = "";
			}else{
				tr = _body.insertRow();
				item_map[data.data[i].id] = tr;
			}
			tr.insertCell().innerHTML = data.data[i].title;
			tr.insertCell().innerHTML = "<a href='#' onclick='_click(\""+data.data[i].redirect
				+"\", this, \""+data.data[i].id+"\")'>"+data.data[i].html+"</a>";
			tr.cells[0].className = "col1";
			tr.cells[1].className = "col2";
		}
	}
}
<?php foreach($mgr_notify_list as $ac => $interval){ list($mod, $ac) = explode('.', $ac, 2); ?>
setInterval(function(){
	$.ajax({url:"<?php echo $mbs_appenv->toURL($ac, $mod)?>", headers:{Accept:"application/json"}, dataType:"json", success:_handle});
}, <?php echo (is_integer($interval) ? $interval : CModDef::MGR_NOTIFY_INTERVAL_SEC)*1000?>);
<?php } ?>
</script>
</body>
</html>