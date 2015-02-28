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
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet">
<style type="text/css">
.left{width:150px;float:left;margin-top:80px;}
.left a{font-size:14px;}

.right{float:left;width:720px;padding:8px 13px;margin:20px;background-color:#fff;box-shadow:0 2px 6px #313131}
h2{color:#555;margin:0;text-align:center;}
table , ul{background-color:#fff;}
ul{float:left;width:110px;word-wrap:break-word;}

li.head{}
.even{background-color:#eee}
</style>
</head>
<body>
<div class="warpper">
	<div class=header></div>
	<div class=content>
		<div class="left vertical-manu" >
			<p class=title><?php echo $mbs_appenv->lang('mod_list')?></p>
			<?php foreach($mod_list as $mod){?>
			<a href="<?php echo $mbs_appenv->toURL($mbs_appenv->item('cur_action'), '', array('mod'=>$mod))?>" <?php echo $mod==$selected_mod?' class=cur':''?>><?php echo $mod?></a>
			<?php }?>
		</div>
		<div class=right>
			<p class=table_title><?php echo $mbs_appenv->lang(CModDef::MOD)?></p>
			<table cellspacing=0>
			<?php foreach($moddef->item(CModDef::MOD) as $key => $val){ ?>
			<tr><th style="width:70px;"><?php echo $mbs_appenv->lang($key)?></th><td><?php echo $val?></td></tr>
			<?php } ?>
			</table>
			<p class=table_title><?php echo $mbs_appenv->lang(CModDef::TBDEF)?></p>
			<table cellspacing=0>
				<tr>
					<th><?php echo $mbs_appenv->lang(CModDef::G_NM)?></th>
					<th><?php echo $mbs_appenv->lang(CModDef::G_DC)?></th>
				</tr>
			<?php $n = 1; $tbdef=$moddef->item(CModDef::TBDEF); if(!empty($tbdef)){ foreach($tbdef as $key => $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>><td><?php echo $key?></td><td><?php echo CStrTools::txt2html(htmlspecialchars($val))?></td></tr>
			<?php }} ?>
			</table>
			<p class=table_title><?php echo $mbs_appenv->lang(CModDef::TAG)?></p>
			<table cellspacing=0>
				<tr>
					<th><?php echo $mbs_appenv->lang(CModDef::G_NM)?></th>
					<th><?php echo $mbs_appenv->lang(CModDef::G_CS)?></th>
					<th><?php echo $mbs_appenv->lang(CModDef::G_DC)?></th>
				</tr>
			<?php $n = 1; $tag = $moddef->item(CModDef::TAG); if(!empty($tag)){ foreach($tag as $key => $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>><td><?php echo $key?></td><td><?php echo $val[CModDef::G_CS]?></td>
				<td><?php echo CStrTools::txt2html(htmlspecialchars($val[CModDef::G_DC]))?></td></tr>
			<?php }} ?>
			</table>
			<p class=table_title><?php echo $mbs_appenv->lang(CModDef::FTR)?></p>
			<table cellspacing=0>
				<tr>
					<th><?php echo $mbs_appenv->lang(CModDef::G_NM)?></th>
					<th><?php echo $mbs_appenv->lang(CModDef::G_CS)?></th>
					<th><?php echo $mbs_appenv->lang(CModDef::G_DC)?></th>
				</tr>
			<?php $n = 1; $ftr=$moddef->item(CModDef::FTR); if(!empty($ftr)){foreach($ftr as $key => $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>><td><?php echo $key?></td><td><?php echo $val[CModDef::G_CS]?></td>
				<td><?php echo CStrTools::txt2html(htmlspecialchars($val[CModDef::G_DC]))?></td></tr>
			<?php }} ?>
			</table>
			<p class=table_title><?php echo $mbs_appenv->lang(CModDef::LD_FTR)?></p>
			<table cellspacing=0>
				<tr>
					<th><?php echo $mbs_appenv->lang(CModDef::MOD)?></th>
					<th><?php echo $mbs_appenv->lang(CModDef::G_NM)?></th>
					<th>isExitOnFilterUndefined</th>
					<th>args</th>
				</tr>
			<?php $n = 1; $ftr=$moddef->item(CModDef::LD_FTR); if(!empty($ftr)){foreach($ftr as $val){ ?>
			<tr <?php echo 0 == $n++%2 ? 'class=even':''?>></td><td><?php echo $val[0]?></td><td><?php echo $val[1]?></td>
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
				<li class=head><?php echo $t?></li>
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