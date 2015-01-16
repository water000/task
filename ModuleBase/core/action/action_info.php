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
tbody th, li.head{font-size:12px; font-weight:bold;text-align:center;padding:5px 0;width:180px;border-bottom:1px solid #aaa;background-color: #ccccff}
tbody td, ul li{border-bottom:1px solid #aaa;padding:5px 3px;color:#333333;}
ul{float:left;width:120px;overflow:hidden;}
ul li{list-style-type:none;}
li.head{width:120px;}
.even{background-color:#F1F1F1}
</style>
</head>
<body>
<div class=header></div>
<div class="warpper">
	<div class=content>
		<div class=left>
<?php
?>
		</div>
		<div class=right>
			<p>Page Info</p>
			<table cellspacing=0>
				<tr><th><?=CModDef::lang(CModDef::G_NM)?></th><td></td></tr>
				<tr><th><?=CModDef::lang(CModDef::MOD)?></th><td></td></tr>
				<tr><th><?=CModDef::lang(CModDef::G_TL)?></th><td></td></tr>
				<tr><th><?=CModDef::lang(CModDef::G_DC)?></th><td></td></tr>
				<tr><th>url</th><td></td></tr>
				<tr><th><?=CModDef::lang(CModDef::P_ARGS)?></th>
					<td><table cellspacing=0></table></td>
				</tr>
				<tr><th><?=CModDef::lang(CModDef::P_OUT)?></th><td></td></tr>
			</table>
		</div>
		<div style="clear: both"></div>
	</div>
</div>
</body>
</html>