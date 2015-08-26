<?php 

mbs_import('', 'CUserClassControl');
$uc = CUserClassControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

if(isset($_REQUEST['name'])){
	$error = $mbs_cur_moddef->checkargs('class');
	if(empty($error)){
		$uc->add(array(
			'name' => $_REQUEST['name'],
			'code' => $_REQUEST['code'],
			'create_time' => time()
		));
	}
}

$list = $uc->getDB()->listAll()->fetchAll();

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<!--[if lt ie 9]>
	<script>
		document.createElement("article");
		document.createElement("section");
		document.createElement("aside");
		document.createElement("footer");
		document.createElement("header");
		document.createElement("nav");
</script>
<![endif]-->
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('allInfo.css')?>">
<style type="text/css">
.col1{width:60px;}
.col2{width:185px;}
.col3{width:285px;}
.col4{width:520px;}
.name{font-size:14px; color:#111;}
</style>
</head>
<body>
<div class="allInfo">
	<h2 class="tit">
		<?php echo $mbs_appenv->lang(array('class', 'manage'))?>
		<span class="tips"><?php echo sprintf($mbs_appenv->lang('total_count'), count($list))?></span>
		<a href="<?php echo $mbs_appenv->toURL('class_edit')?>" class="btn-create">
			+<?php echo $mbs_appenv->lang(array('add', 'class'))?></a>
	</h2>
	
    <div class="box-tabel mb17" style="margin-top:28px;">
		<form name="_form" method="post" action="<?php echo $mbs_appenv->toURL('class_edit')?>">
			<table class="info-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th class="first-col col1"><input type="checkbox" onclick="_checkall(this, document._form)" /></th>
			            <th class="col2"><?php echo $mbs_appenv->lang('class_name')?></th>
			            <th class="col3"><?php echo $mbs_appenv->lang('class_code')?></th>
			            <th class="col4"><?php echo $mbs_appenv->lang(array('add', 'time'), 'common')?></th>
			        </tr>
			    </thead>
			    <tbody>
			    <?php foreach($list as $k=>$row){?>
			        <tr>
			            <td class="first-col">
			            	<input type="checkbox" name="id[]" value="<?php echo $row['id']?>" <?php echo $row['id'] <= CUserDef::BANNED_DEL_MAX_CLASS_ID ? ' disabled':''?> />
			            </td>
			            <td class=name><?php echo $row['name']?></td>
			            <td><?php echo $row['code']?></td>
			            <td><?php echo date('Y-m-d H:i', $row['create_time'])?></td>
			        </tr>
			    <?php }?>
			    </tbody>
			</table>
			<div style="margin-top:10px;" class=box-bottom>
				<a href="javascript:if(confirm('<?php echo $mbs_appenv->lang('confirmed')?>')) {document._form.action='<?php echo $mbs_appenv->toURL('class_edit', '', array('delete'=>1))?>';document._form.submit();}" class="btn-del" >
					<i class="ico"></i><?php echo $mbs_appenv->lang('delete')?></a>
				<a href="javascript:document._form.action='<?php echo $mbs_appenv->toURL('class_edit', '', array('edit'=>1))?>';document._form.submit();" class="btn-send" >
					<i class="ico"></i><?php echo $mbs_appenv->lang('edit')?></a>
			</div>
		</form>
    </div>
</div>
<script type="text/javascript">
function _checkall(chkbox, form){
	var i, boxes=form.elements["id[]"];
	boxes = boxes.length ? boxes : [boxes];
	for(i=0; i<boxes.length; i++){
		boxes[i].checked = chkbox.checked;
	}
}
</script>
</body>
</html>