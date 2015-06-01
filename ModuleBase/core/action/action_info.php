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
	if(empty($moddef))
		continue;
	$actions_def = $moddef->item(CModDef::PAGES);
	
	if(!empty($actions_def)){
		foreach($actions_def as $ac => $acdef){
			$fpath = $mbs_appenv->getActionPath($ac, $mod);
			$all_actions[] = array('_mod'=>$mod, '_name'=>$ac,
				'actiondef'=>$acdef, 'fmtime'=>file_exists($fpath) ? filemtime($fpath) : 0);
		}
	}
}
usort($all_actions, create_function('$a, $b', 
	'if($a["fmtime"] == $b["fmtime"]) return 0; return $a["fmtime"] > $b["fmtime"] ? 1 : -1;'));

$pageargs = array(CModDef::PA_TYP=>'string', CModDef::PA_REQ=>'0', 
		CModDef::PA_EMP=>1, CModDef::PA_RNG=>'');
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
.left{width:290px;margin:30px 0 0;background-color:#fff;float:left;}
.left .action-item{font-size:12px;color:#333;position:relative;padding:3px 8px;border-bottom:1px solid #e0e0e0;cursor:pointer;}
.left .action-item .title{font-weight:bold;}
.left .action-item .date{float:right;}
.left .action-item .desc{color:#888;padding:1px;}
.right{position:fixed;width:690px;;min-height:500px;margin:30px 0 0 313px;background-color:#fff;box-shadow:0 2px 6px #313131}
.datediff{width:130px;margin: 0 auto;color:#555;}
.datediff span{width:26px;height:1px;background-color:#ccc;display:inline-block;margin:0 5px 3px;}
.action{width:670px;background-color:#fff;margin:10px auto;cursor:default;}
.action table{margin-bottom:20px;overflow-x:scroll;}
td{word-wrap:break-word;word-break:break-all;}
.filter select, .filter span{float:right;margin-left:10px;}
.basic_info th{width:90px;}
.even{background-color:#eee;}
</style>
</head>
<body>
<div class="warpper">
	<div class=header></div>
	<div class=content>
		<h2>Actions Info</h2>
		<div class=filter>
			<form action="<?php echo $mbs_appenv->item('cur_action_url')?>" method="get">
				<select name=mod onchange="this.form.submit();">
					<option value="">--all module--</option>
				<?php foreach($mod_list as $mod){?><option value=<?php echo $mod?> <?php echo $mod==$_REQUEST['mod']?' selected':''?>><?php echo $mod?></option><?php }?>
				</select>
				<select name=otype onchange="this.form.submit();">
					<option value="">--all output--</option>
					<?php foreach($output_type as $type){?><option value=<?php echo $type?> <?php echo $type==$_REQUEST['otype']?' selected':''?>><?php echo $type?></option><?php }?>
				</select>
				<?php foreach($_GET as $k=>$v){ if('mod' == $k || 'otype' == $k) continue;?>
				<input type="hidden" name="<?php echo $k?>" value="<?php echo $v?>" />
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
	$ago = $ago > 7 ? 7 : $ago;
	if($dayago != $ago){ 
		$dayago = $ago;
		echo '<div class=datediff><span></span>',
			(0 == $ago ? $mbs_appenv->lang('today') : ($ago < 7 ? $ago.$mbs_appenv->lang('days_ago') : $mbs_appenv->lang('long_ago'))),
			'<span></span></div>';
	}
?>
			<div class=action-item onclick="_action(this)">
				<div>
					<span class=title>[<?php echo $all_actions[$i]['_mod']?>.<?php echo $all_actions[$i]['_name']?>]
						<?php echo CStrTools::txt2html($def[CModDef::P_TLE])?></span>
				</div>
				<div class=desc><?php echo CStrTools::txt2html(CStrTools::cutstr($def[CModDef::G_DC], 45, $mbs_appenv->item('charset')))?></div>
				<div class="action" style="display: none;">
					<p class=table_title>Basic Info</p>
					<table cellspacing=0 class=basic_info>
						<tr><th>URL</th><td><?php echo $mbs_appenv->toURL($all_actions[$i]['_name'], $all_actions[$i]['_mod'])?></td></tr>
						<tr><th>Last Modify</th><td><?php echo 0==$fmtime ? '' : date('m-d H:i', $fmtime)?></td></tr>
						<tr><th>Desc</th><td><?php echo isset($def[CModDef::G_DC]) ? CStrTools::txt2html($def[CModDef::G_DC]):''?></td></tr>
						<?php if(isset($def[CModDef::P_MGR])){?><tr><th>admin</th><td>yes</td></tr><?php }?>
					</table>
					<p class=table_title><?php echo $mbs_appenv->lang(CModDef::P_ARGS)?></p>
					<table cellspacing=0>
						<tr><th><?php echo $mbs_appenv->lang(CModDef::G_NM)?></th>
							<?php foreach(array_keys($pageargs) as $pa){?><th><?php echo $mbs_appenv->lang($pa)?></th><?php }?>
							<th width=40%><?php echo $mbs_appenv->lang(CModDef::G_DC)?></th>
						</tr>
							<?php $n=1; if(isset($def[CModDef::P_ARGS])){foreach($def[CModDef::P_ARGS] as $key => $args){?>
							<tr <?php echo 0 == $n++%2 ? 'class=even':''?>>
							<td><?php echo $key?></td>
							<?php next($pageargs); foreach($pageargs as $pa=>$defval){ ?>
							<td><?php echo isset($args[$pa])?$args[$pa]:$defval?></td>
							<?php } ?>
							<td><?php echo CStrTools::txt2html($args[CModDef::G_DC])?></td>
							</tr>
							<?php }} ?>
					</table>
					<p class=table_title><?php echo $mbs_appenv->lang(CModDef::P_OUT)?></p>
					<table><tr><td style="font-size: 13px;background-color:#fff9ea;">
						<?php echo isset($def[CModDef::P_OUT]) ? CStrTools::txt2html($def[CModDef::P_OUT]) : 'NULL'?>
					</td></tr></table>
				</div>
			</div>
<?php } ?>
		</div>
		<div class=right id=IDD_RIGHT><div class="action"></div></div>
		<div style="clear: both"></div>
	</div>
	<div class=footer></div>
</div>
<script type="text/javascript">
var g_curAction = null, defClass = null, 
	g_rightAction=document.getElementById("IDD_RIGHT").childNodes[0];
var childs = document.getElementById("IDD_LEFT").childNodes, i;
for(i=0; i<childs.length; i++){
	if("action-item" == childs[i].className){
		g_curAction = childs[i];
		defClass = g_curAction.className;
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
		g_curAction.className=defClass;
		g_curAction = ac;
	}
	pop = _getPop(ac);
	if(pop != null){
		ac.className = defClass+" cur";
		g_rightAction.innerHTML = pop.innerHTML;
	}
}
if(g_curAction != null){
	_action(g_curAction);
}
</script>
</body>
</html>