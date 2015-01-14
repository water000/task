<?php 

$local_host = array('127.0.0.1', 'localhost');
if(!in_array($_SERVER['SERVER_ADDR'], $local_host)){
	echo 'action was banned';
	exit(0);
}

?>
<!doctype html>
<html>
<head>
<style type="text/css">
body{margin:0;padding:0;border:0;}
.header{height: 60px;background: #f1f1f1;border-bottom: 1px solid #eee;}
.warpper{width:1000px;margin:0 auto;}
.warpper div{font-size:12px;float:left;}
h1{color:#555;margin-top:50px;text-align:center;}
table{width:100%;margin:50px 10px 20px;}
.rule{color: #737373;width:43%;}
.mod_info{background-color:#eee;width:50%;color:#222;font-weight:bold;padding:26px;}
</style>
</head>
<body>
</body>
<div class=header></div>
<h1>添加或编辑一个新的模块</h1>
<div class="warpper">
	<h3>编辑模块属性</h3>
	<table>
<?php 
$attrmap = $mbs_moddef->getAttrMap(); 
foreach($attrmap as $item => $val){
?>
		<tr>
			<td class=rule><p><?=$item?></p><ul><?php foreach ($val as $name=>$sub){?><li><?php echo $sub['title'], ')', $sub['desc']?></li><?php }?></ul></td>
			<td class=mod_info>
			<?php 
			foreach ($val as $name=>$sub){
				if('b' == $sub['width']){
			?>
			<textarea name="<?=$name?>" placeholder="<?=$sub['title']?>"></textarea>
			<?php }else{ ?>
			<input type="text" name="<?=$name?>" placeholder="<?=$sub['title']?>" />
			<?php }} ?>
			</td>
		</tr>
<?php } ?>
	</table>
</div>
</html>