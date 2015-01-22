<?php 
error_reporting(0);
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
	if(empty($moddef))
		continue;
	$actions_def = $moddef->item(CModDef::PAGES);
	
	if(!empty($actions_def)){
		foreach($actions_def as $ac => $acdef){
			$fpath = $mbs_appenv->getActionPath($ac, $mod);
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

$pageargs = array(CModDef::PA_TYP=>'string', CModDef::PA_REQ=>'0', 
		CModDef::PA_EMP=>1, CModDef::PA_TRI=>1, CModDef::PA_RNG=>'');
?>
<!doctype html>
<html>
<head>
<style type="text/css">
body{font-size:12px;color:#333;font-family:"Lucida Grande", "Lucida Sans Unicode", "STHeiti", "Helvetica","Arial","Verdana","sans-serif"; }
body, p, td, ul{margin:0;padding:0;border:0;}
ul li{list-style-type:none;}
.header{height: 40px;background: #252525; color:white;border-bottom: 1px solid #eee;}
.footer{height: 60px;background: #fff;border-top: 1px solid #eee;clear:both;margin-top:50px;}
.warpper{width:100%;min-height:100%;background-color:#fff;font-size:12px;position:relative;}
.content{margin:30px auto 0;margin-top:30px;width:1000px;}

.vertical-manu{padding:2px;border:1px solid #bbb;border-top:3px solid #85BBEF;background-color:#fff;overflow:hidden}
.vertical-manu p.title{border-bottom:1px solid #bbb;background-color:#fff;font-weight:bold; text-align:center;padding:3px 0;}
.vertical-manu a{padding:2px 5px; display:block;text-decoration:none;border:1px solid #fff;}
.vertical-manu a:hover, .vertical-manu a.cur{border:1px solid #85BBEF; background-color:#C6E0FA;}

p.table_title{font-size:14px; font-weight:bold;color:#555;text-align:left;padding:3px 5px;}
table{width:100%;border:1px solid #aaa;margin-bottom:30px;}
tbody th, li.head{font-size:12px; font-weight:bold;text-align:center;padding:5px 0;border-bottom:1px solid #aaa;background-color: #ccccff}
tbody td, ul li{border-bottom:1px solid #aaa;padding:5px 3px;color:#333;}

body, .warpper{background-color:#ddd;}
h2{color:#555;margin:0;text-align:center;}

.left{width:290px;margin:30px 0;background-color:#fff;float:left;}
.left .action-item{font-size:12px;color:#333;position:relative;padding:3px 8px;border-bottom:1px solid #e0e0e0;cursor:pointer;}
.left .action-item .title{font-weight:bold;}
.left .action-item .date{float:right;}
.left .action-item .desc{color:#888;padding:1px;}
.right{width:630px;float:left;min-height:600px;margin:auto 0px;background-color:#fff;}
.datediff{width:100px;margin: 0 auto;color:#555;}
.datediff span{width:26px;height:1px;background-color:#ddd;display:inline-block;margin-top:10px;}
.action{position:absolute;width:700px;min-height:600px;top:-26px; left:290px;background-color:#fff;padding:10px 8px;cursor:default;display:none;}
.left .action table{margin-bottom:20px;}
.filter select, .filter span{float:right;margin-left:10px;}
.even{background-color:#eee;}
</style>
</head>
<body>
<div class="warpper">
	<div class=header></div>
	<div class=content>
		<h2 style="padding-top:15px;">Actions Info</h2>
		<div class=filter>
			<form action="<?=$mbs_appenv->item('cur_action_url')?>" method="get">
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
		<div class="left vertical-manu" id=IDD_LEFT>
			<p class=title>actions</p>
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
		echo '<div class=datediff><span></span>',
			(0 == $ago ? $mbs_appenv->lang('today') : ($ago < 7 ? $ago.$mbs_appenv->lang('days_ago') : $mbs_appenv->lang('long_aog'))),
			'<span></span></div>';
	}
?>
			<div class=action-item onclick="_action(this)">
				<div>
					<span class=title>[<?=$all_actions[$i]['_mod']?>.<?=$all_actions[$i]['_name']?>]
						<?=CStrTools::txt2html($def[CModDef::P_TLE])?></span>
				</div>
				<div class=desc><?=CStrTools::txt2html(CStrTools::cutstr($def[CModDef::G_DC], 45, $mbs_appenv->item('charset')))?></div>
				<div class="action" style="display: none;">
					<p class=table_title>Basic Info</p>
					<table cellspacing=0>
						<tr><th>URL</th><td><?=$mbs_appenv->toURL($all_actions[$i]['_mod'], $all_actions[$i]['_name'])?></td></tr>
						<tr><th>Last Modify</th><td><?=date('m-d H:i', $fmtime)?></td></tr>
						<tr><th>Desc</th><td><?=CStrTools::txt2html($def[CModDef::G_DC])?></td></tr>
						<?php if(isset($def[CModDef::P_MGR])){?><tr><th>admin</th><td>yes</td></tr><?php }?>
					</table>
					<p class=table_title><?=$mbs_appenv->lang(CModDef::P_ARGS)?></p>
					<table cellspacing=0>
						<tr><th><?=$mbs_appenv->lang(CModDef::G_NM)?></th>
							<?php foreach(array_keys($pageargs) as $pa){?><th><?=$mbs_appenv->lang($pa)?></th><?php }?>
							<th style="width: 40%;"><?=$mbs_appenv->lang(CModDef::G_DC)?></th>
						</tr>
							<?php $n=1; foreach($def[CModDef::P_ARGS] as $key => $args){?>
							<tr <?php echo 0 == $n++%2 ? 'class=even':''?>>
							<td><?=$key?></td>
							<?php next($pageargs); foreach($pageargs as $pa=>$defval){ ?>
							<td><?=isset($args[$pa])?$args[$pa]:$defval?></td>
							<?php } ?>
							<td><?=CStrTools::txt2html($args[CModDef::G_DC])?></td>
							</tr>
							<?php } ?>
					</table>
					<p class=table_title><?=$mbs_appenv->lang(CModDef::P_OUT)?></p>
					<table><tr><td style="border:0;">
						<?=isset($def[CModDef::P_OUT]) ? CStrTools::txt2html($def[CModDef::P_OUT]) : 'empty'?>
					</td></tr></table>
				</div>
			</div>
<?php } ?>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class=footer></div>
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