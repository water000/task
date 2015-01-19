<?php 

$dest_mod_list = $mod_list = $mbs_appenv->getModList();
if(isset($_REQUEST['mod']) && !empty($_REQUEST['mod'])){
	if(!in_array($_REQUEST['mod'], $mod_list)){
		trigger_error('Invalid module selected', E_USER_ERROR);
	}
	$dest_mod_list = array($_REQUEST['mod']);
}else{
	$_REQUEST['mod'] = '';
}

$output_type = array('html', 'not_html');
function _is_spec_type(&$actiondef){
	switch ($_REQUEST['otype']){
		case 'html':
			return isset($actiondef[CModDef::P_OUT]) ? false : true;
			break;
		case 'not_html':
			return isset($actiondef[CModDef::P_OUT]) ? true : false;
			break;
		default:
			return true;
			break;
	}
	return true;
}
if(isset($_REQUEST['otype']) && in_array($_REQUEST['otype'], $output_type))
	;
else
	$_REQUEST['otype'] = '';

$all_actions = array();
$curtime = time();
$nofiles = array();
foreach($dest_mod_list as $mod){
	$moddef = mbs_moddef($mod);
	$actions_def = $moddef->item(CModDef::PAGES);
	
	if(!empty($actions_def)){
		foreach($actions_def as $ac => $acdef){
			$fpath = $mbs_appenv->getActionPath($mod, $ac);
			if(file_exists($fpath)){
				$all_actions[] = array('_mod'=>$mod, '_name'=>$ac,
						 'actiondef'=>$acdef, 'fmtime'=>filemtime($fpath));
			}else{
				$nofiles[] = $mod.'.'.$ac;
			}
		}
	}	
}
usort($all_actions, create_function('$a, $b', 
	'if($a["fmtime"] == $b["fmtime"]) return 0; return $a["fmtime"] > $b["fmtime"] ? 1 : -1;'));

$pageargs = array(CModDef::PA_TYP, CModDef::PA_REQ, CModDef::PA_EMP, CModDef::PA_TRI, CModDef::PA_RNG);
?>
<!doctype html>
<html>
<head>
<style type="text/css">
body{font-family:"Lucida Grande", "Lucida Sans Unicode", "STHeiti", "Helvetica","Arial","Verdana","sans-serif"}
body, p, td, ul{margin:0;padding:0;border:0;}
.header{height: 60px;background: #252525; color:white;border-bottom: 1px solid #eee;}
.footer{height: 60px;background: #fff;border-top: 1px solid #eee;clear:both;margin-top:50px;}
.warpper{width:1000px;min-height:100%;margin:0 auto;font-size:12px;}
.content{margin-top:30px;}
.left{width:170px;float:left;margin-top:80px;border:1px solid #bbb;border-top:3px solid #85BBEF;}
.left p{font-size:12px; font-weight:bold; text-align:center;padding:6px 0; border-bottom:1px solid #ddd;}
.left a{display:block;font-size:14px;text-decoration:none;padding:3px 8px;border-bottom:1px solid #e0e0e0;}
.left a:hover{text-decoration:underline;}
.left a.current{background-color:#e0e0e0;font-weight:bold;}
.right{float:left;width:700px;padding:20px 30px;margin:0 30px;background-color: #F8F8F8}
h2{color:#555;margin:0;text-align:center;}
table{width:100%;border:1px solid #aaa;margin-bottom:30px;}
.right p{font-size:16px; font-weight:bold;padding:8px 3px;color:#555;}
tbody th, li.head{font-size:12px; font-weight:bold;text-align:center;padding:5px 0;width:80px;border-bottom:1px solid #aaa;background-color: #ccccff}
tbody td, ul li{border-bottom:1px solid #aaa;padding:5px 3px;color:#333333;}
ul{float:left;width:120px;overflow:hidden;}
ul li{list-style-type:none;}
li.head{width:120px;}
.even{background-color:#F1F1F1}

.left{width:400px;margin-top:30px;}
.left .action-item{font-size:12px;color:#333;position:relative;padding:3px 8px;border-bottom:1px solid #e0e0e0;cursor:pointer;}
.left .action-item .title{font-weight:bold;}
.left .action-item .date{float:right;}
.left .action-item .desc{color:#888;padding:1px;}
.right{width:0px;margin:auto 0px;background-color:#fff;}
.datediff{width:100px;margin: 0 auto;color:#555;}
.datediff span{width:26px;height:1px;background-color:#ddd;display:inline-block;margin-top:10px;}
.action{position:absolute;width:700px;top:-5px; left:401px;background-color:#E8E8A8;padding:10px 8px;}
.action td, .action .pout{background-color:#fff;}
.action .pout{padding: 3px 10px;}
.left .action p{font-size:16px; font-weight:bold;color:#555;text-align:left;}
.left .action table{margin-bottom:20px;}
.filter select, .filter span{float:right;margin-left:10px;}
</style>
</head>
<body>
<div class=header></div>
<div class="warpper">
	<h2 style="margin-top:30px;">Actions Info</h2>
	<div class=filter>
		<form action="<?=$mbs_appenv->toURL(RTM_MOD, RTM_ACTION)?>" method="get">
			<select name=mod onchange="this.form.submit();">
				<option value="">--all module--</option>
			<?php foreach($mod_list as $mod){?><option value=<?=$mod?> <?=$mod==$_REQUEST['mod']?' selected':''?>><?=$mod?></option><?php }?>
			</select>
			<select name=otype onchange="this.form.submit();">
				<option value="">--all output--</option>
				<?php foreach($output_type as $type){?><option value=<?=$type?> <?=$type==$_REQUEST['otype']?' selected':''?>><?=$type?></option><?php }?>
			</select>
			<?php foreach($_GET as $k=>$v){ if('mod' == $k || 'otype' == $k) continue;?>
			<input type="hidden" name="<?=$k?>" value="<?=$v?>" />
			<?php }?>
		</form>
		<span>Filters: </span>
	</div>
	<div class=content>
		<div class=left id=IDD_LEFT>
			<p>actions</p>
<?php
$dayago = -1;


//foreach ($all_actions as $fatime => $def){
for($i=count($all_actions)-1; $i>=0; --$i){
	$fmtime = $all_actions[$i]['fmtime'];
	$def    = $all_actions[$i]['actiondef'];
	
	if(!_is_spec_type($def))
		continue;
	
	$ago = intval(($curtime - $fmtime)/86400);
	if($dayago != $ago){ 
		$dayago = $ago;
		echo '<div class=datediff><span></span>',(0 == $ago ? '今天' : ($ago < 7 ? $ago.'天前' : '更久')),'<span></span></div>';
	}
?>
			<div class=action-item onclick="_action(this)">
				<div>
					<span class=title>[<?=$all_actions[$i]['_mod']?>.<?=$all_actions[$i]['_name']?>]
						<?=CStrTools::txt2html($def[CModDef::P_TLE])?></span>
					<span class=date><?=date('m-d H:i', $fmtime)?></span>
				</div>
				<div class=desc><?=$mbs_appenv->toURL($all_actions[$i]['_mod'], $all_actions[$i]['_name'])?></div>
				<div class=desc><?=CStrTools::txt2html($def[CModDef::G_DC])?></div>
				<div class="action" style="display: none;">
					<p><?=CModDef::lang(CModDef::P_ARGS)?></p>
					<table cellspacing=0>
						<tr><th><?=CModDef::lang(CModDef::G_NM)?></th>
							<?php foreach($pageargs as $pa){?><th><?=CModDef::lang($pa)?></th><?php }?>
							<th style="width: 20%;"><?=CModDef::lang(CModDef::G_DC)?></th>
						</tr>
							<?php foreach($def[CModDef::P_ARGS] as $key => $args){?>
							<tr>
							<td><?=$key?></td>
							<?php next($pageargs); foreach($pageargs as $pa){ ?>
							<td><?=isset($args[$pa])?CStrTools::txt2html($args[$pa]):''?></td>
							<?php } ?>
							<td><?=CStrTools::txt2html($args[CModDef::G_DC])?></td>
							</tr>
							<?php } ?>
					</table>
					<p><?=CModDef::lang(CModDef::P_OUT)?></p>
					<div class=pout><?=isset($def[CModDef::P_OUT]) ? CStrTools::txt2html($def[CModDef::P_OUT]) : 'empty'?></div>
				</div>
			</div>
<?php } ?>
		</div>
		<div class=right>
		</div>
		<div style="clear: both"></div>
	</div>
</div>
<script type="text/javascript">
var g_curAction = null;
var childs = document.getElementById("IDD_LEFT").childNodes, i;
for(i=0; i<childs.length; i++){
	if("action-item" == childs[i].className){
		g_curAction = childs[i];
		break;
	}
}
function _getPop(ac){
	var i;
	for(i=0; i<ac.childNodes.length; i++){
		if("action" == ac.childNodes[i].className){
			return ac.childNodes[i];
		}
	}
	return null;
}
function _action(ac){
	var pop;
	
	if(ac.className != "action-item"){
		return;
	}
	if(ac != g_curAction){
		pop = _getPop(g_curAction);
		if(pop != null){
			pop.style.display = "none";
			g_curAction.style.backgroundColor="#fff";
			g_curAction = ac;
		}
	}
	pop = _getPop(ac);
	if(pop != null){
		pop.style.display = "";
		ac.style.backgroundColor="#eee";
	}
}
if(g_curAction != null){
	_action(g_curAction);
}
</script>
</body>
</html>