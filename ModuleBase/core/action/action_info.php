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

function _parse_tb($txt, $mod){
    static $TPL = <<<END
    <a href="javascript:;" onclick="_pwin(this.innerHTML, 'TBDEF', this);event.cancelBubble=true;">TBNAME</a>
END;
    if(preg_match_all('/#([^#]+)#/', $txt, $matches) > 0){
        $moddef = mbs_moddef($mod);
        $tbdef = $moddef->item(CModDef::TBDEF);
        for($i=0; $i<count($matches[1]); ++$i){
            if(isset($tbdef[$matches[1][$i]])){
                $tb = explode("\n", htmlspecialchars($tbdef[$matches[1][$i]], ENT_QUOTES));
                array_walk($tb, function(&$item){$item=CStrTools::txt2html(trim($item));});
                $txt = str_replace($matches[0][$i], 
                    str_replace(array('TBNAME', 'TBDEF'), array($matches[1][$i], implode("<br/>", $tb)), $TPL), 
                    $txt);
            }
        }
    }
    return $txt;
}

function _parse_list($txt, $mod){
    global $mbs_appenv;
    
    static $TPL = <<<END
    <a href="javascript:;" onclick="if(!this._pw) this._pw=popwin('', 'BODY').note(this); this._pw.show();event.cancelBubble=true;">TITLE</a>
END;
    if(preg_match_all('/@([^@]+)@/', $txt, $matches) > 0){
        for($i=0; $i<count($matches[1]); ++$i){
            if(0 == strncmp($matches[1][$i], 'code:', '5')){
                $list = eval(substr($matches[1][$i], 5));
                if(false === $list){
                    trigger_error('invalid code: '.$matches[1][$i]);
                    continue;
                }
                $body = '<div>';
                $title = '';
                $k = 0;
                foreach($list as $v){
                    $n =  addcslashes($v, "'").'('.addcslashes($mbs_appenv->lang($v, $mod), "'").')';
                    $title = empty($title) ? $n : $title;
                    $body .= '<div>'.str_pad(++$k, 2, ' ', STR_PAD_LEFT).')&nbsp;'.$n.'</div>';
                }
                $body .= '</div>';
                $txt = str_replace($matches[0][$i], 
                    str_replace(array('BODY', 'TITLE'), 
                        array($body, $title), $TPL), 
                    $txt);
            }
            else if(0 == strncmp($matches[1][$i], 'json:', '5')){
            
            }
        }
    }
    
    return $txt;
}

function _parse_tags($txt, $mod){
    return _parse_list(_parse_tb($txt, $mod), $mod);
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
.left{width:290px;margin:10px 0 0;background-color:#fff;float:left;}
.left .action-item{font-size:12px;color:#333;position:relative;padding:3px 8px;border-bottom:1px solid #e0e0e0;cursor:pointer;}
.left .action-item .title{font-weight:bold;}
.left .action-item .date{float:right;}
.left .action-item .desc{color:#888;padding:1px;}
.right{position:fixed;width:690px;;min-height:500px;margin:10px 0 0 313px;background-color:#fff;box-shadow:0 2px 6px #313131}
.datediff{width:130px;margin: 0 auto;color:#555;}
.datediff span{width:26px;height:1px;background-color:#ccc;display:inline-block;margin:0 5px 3px;}
.action{width:670px;overflow:auto;background-color:#fff;margin:10px auto;cursor:default;}
.action table{margin-bottom:20px;overflow-x:scroll;}
td{word-wrap:break-word;word-break:break-all;}
.filter{text-align:right;}
.filter form{display:inline-block;}
.filter a{margin-right:10px;}
.basic_info th{width:90px;}
.even{background-color:#eee;}
h2{text-align:center;}
.bg-box{background-color:#fff;padding:5px;margin-top:10px;}
.bg-box p{padding:3px 5px; margin:0;}
</style>
</head>
<body style="background-color:#eee;">
<div class="warpper" >
	<div class=header></div>
	<div class=content>
		<div class=filter>
		    <a href="javascript:;" onclick="if(!this._pw) this._pw=popwin('', this.nextSibling.innerHTML).note(this);this._pw.show();event.cancelBubble=true;">APP-DEV</a><div style="display: none;">
    		  <dl style="margin-top:0;">
    		      <dt>INPUT</dt>
    		      <dd>_version: app version</dd>
    		      <dd>_ts: unix timestamp</dd>
    		      <dd>_sign: md5[APPKEY+_ts+...]</dd>
    		      <dd>_imei: device IMEI </dd>
    		      <dd>_json: add HTTP-HEADER "X-POST-JSON-FIELD: _json" </dd>
    		      <dd>HTPP-HEADER: "X-LOGIN-TOKEN:token-returned-by-login-api"</dd>
    		  </dl>
    		  <dl>
    		      <dt>OUTPUT</dt>
    		      <dd>{retcode:"SUCCESS/ERROR_CODE", data:[], [error:"DETAIL_IF_ERROR"]}</dd>
    		      <dd></dd>
    		  </dl>
    		  <dl>
    		      <dt>APPKEY</dt>
    		      <?php foreach($mbs_appenv->config('appkeys', 'common') as $v){?>
    		      <dd><?php echo $v?></dd>
    		      <?php } ?>
    		      <dd></dd>
    		  </dl>
    		</div>
		    <span>Filters: </span>
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
		</div>
		<div class="left vertical-menu" id=IDD_LEFT>
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
							<td><?php echo _parse_tags($args[CModDef::G_DC], $all_actions[$i]['_mod'])?></td>
							</tr>
							<?php }} ?>
					</table>
					<p class=table_title><?php echo $mbs_appenv->lang(CModDef::P_OUT)?></p>
					<table><tr><td style="font-size: 13px;background-color:#fff9ea;">
						<?php echo isset($def[CModDef::P_OUT]) ? _parse_tags($def[CModDef::P_OUT], $all_actions[$i]['_mod']) : 'NULL'?>
					</td></tr></table>
					
					<?php if(isset($def[CModDef::P_OUT])){ ?>
					<p class=table_title><a href="javascript:;" onclick="open('','', 'width=600,height=400,left=100000,top=100000').document.write(this.parentNode.nextSibling.innerHTML);">Test</a></p><div style="display:none">
					   <!doctype html>
                        <html>
                        <head>
                        <style type="text/css">
                        table{width:100%;}
                        input{width:450px;height:28px;margin-left: 20px;}
                        #IDD_FORM{overflow:auto;}
                        iframe{border:0;border-top:1px solid #ddd;width:99%;height:18%;overflow:auto;position:fixed;bottom:5px;background-color:#fff;}
                        ::-webkit-scrollbar{width:6px;height:6px}
                        ::-webkit-scrollbar-thumb{-webkit-border-radius: 3px;webkit-border-radius: 3px;background-color: #c3c3c3;}
					   </style>
                        </head>
                        <body>
                        <h2><?php echo $mbs_appenv->toURL($all_actions[$i]['_name'], $all_actions[$i]['_mod']), '(',CStrTools::txt2html($def[CModDef::P_TLE]),')'?></h2>
					   <form target=IFM_OUTPUT method=post action="<?php echo isset($_SERVER['HTTPS']) ?'HTTPS':'HTTP', '://',$_SERVER['HTTP_HOST'], $mbs_appenv->toURL($all_actions[$i]['_name'], $all_actions[$i]['_mod'])?>">
					   <div id=IDD_FORM>
					   <table style="width: 100%;">
					   <?php $include_file=false; if(isset($def[CModDef::P_ARGS])){foreach($def[CModDef::P_ARGS] as $key => $args){ $required=isset($args[CModDef::PA_REQ]) && $args[CModDef::PA_REQ];?>
					       <tr><td><?php echo $key?></td>
					       <td>
					       <?php if(isset($args[CModDef::PA_TYP]) && strncmp($args[CModDef::PA_TYP], 'file', 4) == 0){ $include_file = true;?>
					           <?php if('files' == strtolower($args[CModDef::PA_TYP])){ ?>
					           <input name="<?php echo $key?>[]" type="file" <?php echo $required?'required':'' ?> /><br/>
					           <input name="<?php echo $key?>[]" type="file" /><br/>
					           <input name="<?php echo $key?>[]" type="file" />
					           <?php }else{ ?>
					           <input name="<?php echo $key?>" type="file" <?php echo $required?'required':'' ?> />
					           <?php } ?>
					       <?php } else{ ?>
					           <input type="text" placeholder="<?php echo isset($args[CModDef::G_DC])?$args[CModDef::G_DC]:''?>" name="<?php echo $key, isset($args[CModDef::PA_TYP]) && 'array'==$args[CModDef::PA_TYP] ?'[]':''?>" value="" <?php echo $required?'required':'' ?> />
					       <?php }?>
					       </td></tr>
					   <?php }} ?>
					   </table>
					   <p><input value="submit" onclick="_scale_win();<?php if($include_file) echo 'this.form.enctype=\'multipart/form-data\';'; ?>" style="width:100%;margin:0;" type=submit /></p>
					   </div>
					   </form>
					   <iframe id=IDD_FRM name=IFM_OUTPUT></iframe>
					   <script type="text/javascript">
					   var _form=document.getElementById("IDD_FORM"),
					       _frm=document.getElementById("IDD_FRM");
					   function _scale_win(){
						   _frm.contentWindow.document.body.innerHTML = '';
						   _frm.onmouseover=function(event){
							   this.style.height = "50%";
							   _form.onmouseover=function(event){
								   _frm.style.height = "18%";
							   }
						   }
					   }
					   _frm.onload=function(event){
						   var arr=this.contentWindow.document.getElementsByTagName("div");
						   for(var i=0; i<arr.length; i++){
							   if('success'==arr[i].className || 'error'==arr[i].className){
								   arr[i].style.cssText = "width:auto;margin:0;";
							   }
						   }
					   }
					   </script>
					   </body></html>
					</div>
					<?php } ?>
					
				</div>
			</div>
<?php } ?>
		</div>
		<div class=right id=IDD_RIGHT><div class="action"></div></div>
		<div style="clear: both"></div>
	</div>
	<div class=footer></div>
</div>
<script type="text/javascript" src="/static/js/global.js"></script>
<script type="text/javascript">

var g_tbwin = [];
function _pwin(title, content, node){
	if(!g_tbwin[title]){
		g_tbwin[title] = popwin("", content).note(node);
	}
	g_tbwin[title].show().around(node);
}


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