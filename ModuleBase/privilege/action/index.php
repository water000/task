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

?>
<!doctype html>
<html>
<head>
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
body{overflow:hidden;}
iframe{width:100%;border:0;}
.actions{width:160px;padding:8px;position:fixed;right:10px;;bottom:35px;}
.actions div.groups{width:140px;font-size:12px;position:relative;}

.actions a{color:rgb(0,100,200);}
.actions a.mod{font-weight:bold;border-top:1px solid #e1e1e1;}
.actions a.mod span{margin-left:2px;color:#777;font-size:12px;float:right;}
.actions div.group{display:none;}
.actions div.group a{padding-left: 15px;}
.actions .blur_a{background-color:#fff;}

.change_win{width:15px;position:absolute;right:0;top:0;padding:0;}
.vertical-menu p.title{border-bottom:0;}
</style>
</head>
<body>
<iframe src=""></iframe>
<div class=actions>
	<div class="vertical-menu groups">
		<a class=change_win href="#" onclick="_change(this);">-</a>
		<p class=title><?php echo $mbs_appenv->lang('mgrlist')?></p>
			<div>
			<?php 
			if(isset($priv_group[CPrivGroupControl::PRIV_TOPMOST])){
				$list = $mbs_appenv->getModList();
				foreach($list as $mod){ 
					$moddef=mbs_moddef($mod);
					if(empty($moddef)) continue;
					$actions = $moddef->filterActions(CModDef::P_MGR);
					if(empty($actions)) continue;
			?>
			<a href="#" class=mod>
				<?php echo $moddef->item(CModDef::MOD, CModDef::G_TL)?><span>&gt;</span>
			</a><div class=group><?php foreach($actions as $ac => $title){?>
				<a href="#" data="<?php echo $mbs_appenv->toURL($ac, $mod)?>" onclick="_to(this)"><?php echo $title?></a><?php }?></div>
			<?php } }else{ foreach($priv_group as $mod => $actions){ $moddef=mbs_moddef($mod);if(empty($moddef)) continue; ?>
			<a href="#" class=mod>
				<?php echo $moddef->item(CModDef::MOD, CModDef::G_TL)?><span>&gt;</span>
			</a><div class=group><?php foreach($actions as $ac){?>
				<a href="#" data="<?php echo $mbs_appenv->toURL($ac, $mod)?>" onclick="_to(this)"><?php echo $moddef->item(CModDef::PAGES, $ac, CModDef::P_TLE)?></a><?php }?></div>
			<?php }} ?>
		</div>
	</div>
</div>
<script type="text/javascript">
var visit_mod_list = [], g_max_mod_num = 3;

function _push_mod(mod){
	var i;
	for(i=0; i<visit_mod_list.length; i++){
		if(visit_mod_list[i] == mod){
			return;
		}
	}
	if(g_max_mod_num == visit_mod_list.length){
		var m = visit_mod_list.shift();
		m.style.display = "none";
	}
	visit_mod_list.push(mod);
}
function _pull_mod(mod){
	var i;
	for(i=0; i<visit_mod_list.length; i++){
		if(visit_mod_list[i] == mod){
			visit_mod_list.splice(i, 1);
			return;
		}
	}
}
function _change(oa){
	if("-" == oa.innerHTML){
		oa.parentNode.getElementsByTagName("div")[0].style.display = "none";
		oa.innerHTML = "+";
	}else{
		oa.parentNode.getElementsByTagName("div")[0].style.display = "block";
		oa.innerHTML = "-";
	}
}

function _to(link, is_redirect){
	var url = link.getAttribute("data");

	is_redirect = 1 == arguments.length ? true : is_redirect;
	
	if(prev != null){
		prev.className = '';
	}
	link.className = "cur";
	prev = link;
	
	if(is_redirect)
		frame.src = url;
	
}

var frame = document.getElementsByTagName("iframe")[0], prev = null, visit_actions = [];
var links = document.getElementsByTagName("a"), i, j=0, firstlink=null;
for(var i=0, j=0; i<links.length; i++){
	if("mod" == links[i].className){
		links[i].onclick = function(e){
			if("none" == this.nextSibling.style.display){
				this.nextSibling.style.display = "block";
				_push_mod(this.nextSibling);
			}else{
				this.nextSibling.style.display = "none";
				_pull_mod(this.nextSibling);
			}
		}
		if(j++<g_max_mod_num){
			links[i].nextSibling.style.display = "block";
			_push_mod(links[i].nextSibling);
		}else{
			links[i].nextSibling.style.display = "none";
		}
			
	}else{
		if("group" == links[i].parentNode.className && null == firstlink){
			firstlink = links[i];
			firstlink.onclick.apply(firstlink);
		}
	}
}


frame.style.height=(document.getElementsByTagName("html")[0].clientHeight-5)+"px";
frame.onload = frame.onreadystatechange = function(e){ //onload: for chrom
	if (frame.contentWindow.document.readyState=="complete"){
		frame.contentWindow.document.body.onclick = function(e){
			if(prev)
				prev.className = "cur blur_a";
		}
		document.title = frame.contentWindow.document.title;
		
		if(prev.getAttribute("data") != frame.contentWindow.document.location.pathname){
			for(var i=0; i<links.length; i++){
				if(links[i].getAttribute("data") == frame.contentWindow.document.location.pathname){
					_to(links[i], false);
					break;
				}
			}
			if(i == links.length){
				document.location = frame.contentWindow.document.location.href;
			}
		}

		document.onkeydown = frame.contentWindow.document.onkeydown = function(e){
			e = e || this.parentWindow.event;
			if(116 == (e.keyCode || e.which)){ // forriden F5 key in parent window
				frame.contentWindow.location.reload();
				e.returnValue = false;
				e.cancelBubble = true;
				e.keyCode = 0;
				return false;
			}
			
		}
	}
}

</script>
</body>
</html>