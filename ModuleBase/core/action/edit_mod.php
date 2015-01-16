<?php 

$local_host = array('127.0.0.1', 'localhost');
if(!in_array($_SERVER['SERVER_NAME'], $local_host)){
	echo 'action was banned';
	exit(0);
}

?>
<!doctype html>
<html>
<head>
<style type="text/css">
body, p, td{margin:0;padding:0;border:0;}
body{min-height:100%;}
.header{height: 60px;background: #f1f1f1;border-bottom: 1px solid #eee;}
.footer{height: 60px;background: #fff;border-top: 1px solid #eee;}
.warpper{width:1000px;margin:0 auto;font-size:14px;}
h1{color:#555;margin-top:50px;text-align:center;}
table{width:100%;margin:50px 10px 20px;}
.rule{color:#737373;padding:0;vertical-align:top;}
.mod_info{background-color:#eee;width:360px;color:#222;font-weight:bold;padding:0 25px;}
.mod_info p{margin:5px 0;}
td.first, td.last{height:28px;}
input.s{width:100px;height:25px;padding:0 8px;}
input.m{width:300px;height:25px;padding:0 8px;margin-top:6px;}
textarea{width:100%;height:80px;margin-top:6px;}
.rule p{font-size:14px;}
.rule li{font-size:12px;}
.rule li span{margin-right:5px;color:rgb(27, 33, 50)}
.btn{display:inline-block;width:100%;height:35px;font-weight:bold;margin:20px auto 0;}
</style>
</head>
<body>
</body>
<div class=header></div>
<h1>添加或编辑一个新的模块</h1>
<div class="warpper">
	<table cellspacing=0 style="margin-bottom: 50px;">
		<tr><td class=rule></td><td class="mod_info first"></td></tr>
<?php 
$attrmap = $mbs_moddef->getAttrMap(); 
foreach($attrmap as $item => $val){
?>
		<tr>
			<td class=rule><p><?=$val['desc']?></p><ul><?php foreach ($val['items'] as $name=>$sub){?><li><span><?php echo $sub['title'], '</span>', $sub['desc']?></li><?php }?></ul></td>
			<td class=mod_info>
			<p><?=$val['desc']?></p>
			<?php foreach ($val['items'] as $name=>$sub){ ?>
			<?php if('b' == $sub['width']){ ?>
			<textarea name="<?=$name?>" placeholder="<?=$sub['title']?>"></textarea>
			<?php }else{ ?>
			<input type="text" name="<?=$name?>" class="<?=$sub['width']?>" placeholder="<?=$sub['title']?>" />
			<?php }} ?>
			</td>
		</tr>
<?php } ?>
		<tr><td class=rule></td><td class="mod_info"><input type=submit value="提交" class=btn /></td></tr>
		<tr><td class=rule></td><td class="mod_info last"></td></tr>
	</table>
</div>
<div class=footer></div>
</html>