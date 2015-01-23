<?php 

$mod_list = $mbs_appenv->getModList();
$selected_mod = $mod_list[0];
if(isset($_REQUEST['mod'])){
	if(!in_array($_REQUEST['mod'], $mod_list)){
		trigger_error('Invalid module selected', E_USER_ERROR);
	}
	$selected_mod = $_REQUEST['mod'];
}
$moddef = mbs_moddef($selected_mod);
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

.vertical-manu{padding:2px;border:1px solid #bbb;border-top:3px solid #85BBEF;background-color:#fff;}
.vertical-manu p.title{border-bottom:1px solid #bbb;background-color:#fff;font-weight:bold; text-align:center;padding:3px 0;}
.vertical-manu a{padding:2px 5px; display:block;text-decoration:none;border:1px solid #fff;}
.vertical-manu a:hover, .vertical-manu a.cur{border:1px solid #85BBEF; background-color:#C6E0FA;}

p.table_title{font-size:14px; font-weight:bold;color:#555;text-align:left;padding:3px 5px;}
table{width:100%;border:1px solid #aaa;margin-bottom:30px;}
tbody th, li.head{font-size:12px; font-weight:bold;text-align:center;padding:5px 0;border-bottom:1px solid #aaa;background-color: #ccccff;min-width:80px;}
tbody td, ul li{border-bottom:1px solid #aaa;padding:5px 3px;color:#333;}

.warpper{background-color:#ddd;}

.left{width:170px;float:left;margin-top:80px;}
.left a{font-size:14px;}

.right{float:left;width:700px;padding:8px 13px;margin:20px;background-color:#fff;}
h2{color:#555;margin:0;text-align:center;}
table , ul{background-color:#fff;}
ul{float:left;width:120px;word-wrap:break-word;}

li.head{width:120px;}
.even{background-color:#eee}
</style>
</head>
<body>
<div class="warpper">
	<div class=header></div>
	<div class=content>
		<div class="left vertical-manu" >
			<p class=title><?=$mbs_appenv->lang('mod_list')?></p>
			<?php foreach($mod_list as $mod){?>
			<a href="<?=$mbs_appenv->toURL($mbs_appenv->item('cur_mod'), $mbs_appenv->item('cur_action'), array('mod'=>$mod))?>" <?=$mod==$selected_mod?' class=cur':''?>><?=$mod?></a>
			<?php }?>
		</div>
		<div class=right>
			<p class=table_title><?=$mbs_appenv->lang(CModDef::MOD)?></p>
			<table cellspacing=0>
			<?php foreach($moddef->item(CModDef::MOD) as $key => $val){ ?>
			<tr><th><?=$mbs_appenv->lang($key)?></th><td><?=$val?></td></tr>
			<?php } ?>
			</table>
			<p class=table_title><?=$mbs_appenv->lang(CModDef::TBDEF)?></p>
			<table cellspacing=0>
				<tr>
					<th><?=$mbs_appenv->lang(CModDef::G_NM)?></th>
					<th><?=$mbs_appenv->lang(CModDef::G_DC)?></th>
				</tr>
			<?php $n = 1; $tbdef=$moddef->item(CModDef::TBDEF); if(!empty($tbdef)){ foreach($tbdef as $key => $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>><td><?=$key?></td><td><?=CStrTools::txt2html(htmlspecialchars($val))?></td></tr>
			<?php }} ?>
			</table>
			<p class=table_title><?=$mbs_appenv->lang(CModDef::TAG)?></p>
			<table cellspacing=0>
				<tr>
					<th><?=$mbs_appenv->lang(CModDef::G_NM)?></th>
					<th><?=$mbs_appenv->lang(CModDef::G_CS)?></th>
					<th><?=$mbs_appenv->lang(CModDef::G_DC)?></th>
				</tr>
			<?php $n = 1; $tag = $moddef->item(CModDef::TAG); if(!empty($tag)){ foreach($tag as $key => $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>><td><?=$key?></td><td><?=$val[CModDef::G_CS]?></td>
				<td><?=CStrTools::txt2html(htmlspecialchars($val[CModDef::G_DC]))?></td></tr>
			<?php }} ?>
			</table>
			<p class=table_title><?=$mbs_appenv->lang(CModDef::FTR)?></p>
			<table cellspacing=0>
				<tr>
					<th><?=$mbs_appenv->lang(CModDef::G_NM)?></th>
					<th><?=$mbs_appenv->lang(CModDef::G_CS)?></th>
					<th><?=$mbs_appenv->lang(CModDef::G_DC)?></th>
				</tr>
			<?php $n = 1; $ftr=$moddef->item(CModDef::FTR); if(!empty($ftr)){foreach($ftr as $key => $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>><td><?=$key?></td><td><?=$val[CModDef::G_CS]?></td>
				<td><?=CStrTools::txt2html(htmlspecialchars($val[CModDef::G_DC]))?></td></tr>
			<?php }} ?>
			</table>
			<p class=table_title><?=$mbs_appenv->lang(CModDef::LD_FTR)?></p>
			<table cellspacing=0>
				<tr>
					<th><?=$mbs_appenv->lang(CModDef::MOD)?></th>
					<th><?=$mbs_appenv->lang(CModDef::G_NM)?></th>
					<th>isExitOnFilterUndefined</th>
					<th>args</th>
				</tr>
			<?php $n = 1; $ftr=$moddef->item(CModDef::LD_FTR); if(!empty($ftr)){foreach($ftr as $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>></td><td><?=$val[0]?></td><td><?=$val[1]?></td>
				<td><?php echo isset($val[2])?$val[2]:''?></td><td><?php echo isset($val[3])?$val[3]:''?></td></tr>
			<?php }} ?>
			</table>
			<p class=table_title>files</p>
			<?php 
			$dir = $mbs_appenv->getDir($selected_mod);
			$types = scandir($dir);
			$n = 1;
			foreach($types as $t){
				if('.' == $t[0])
					continue;
				$sub = array($t);
			?>
			<ul <?php echo 0 == $n++%2 ? 'class=even':''?>>
				<li class=head><?=$t?></li>
				<?php 
				//foreach($sub as $st){
				for($i=0; $i<count($sub); ++$i){
					$st = $sub[$i];
					$files = scandir($dir.$st);
					$pre = ($pos=strpos($st, '/')) !== false ? substr($st, $pos+1).'/':'';
					foreach($files as $f){
						if('.' == $f[0]);
						else if(is_dir($dir.$st.'/'.$f))
							$sub[] = $st.'/'.$f;
						else
							echo '<li>', $pre, $f, '</li>';
					}
				}
				?>
			</ul>
			<?php 
			}
			?>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class=footer></div>
</div>
</body>
</html>