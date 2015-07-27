<?php 

mbs_import('', 'CUserDepControl', 'CUserDepMemberControl');

$dep_ins = CUserDepControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$udepmbr_ctr = CUserDepMemberControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());


$list = $dep_ins->getDB()->listAll()->fetchAll();
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
		<?php echo $mbs_appenv->lang(array('department', 'manage'))?>
		<span class="tips"><?php echo sprintf($mbs_appenv->lang('total_count'), count($list))?></span>
		<a href="<?php echo $mbs_appenv->toURL('dep_edit')?>" class="btn-create">
			+<?php echo $mbs_appenv->lang(array('add', 'department'))?></a>
	</h2>
	
    <div class="box-tabel mb17" style="margin-top:28px;">
		<form name="_form" method="post" action="<?php echo $mbs_appenv->toURL('dep_edit')?>">
			<table class="info-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th class="first-col col1"><input type="checkbox" onclick="_checkall(this, document._form)" /></th>
			            <th><?php echo $mbs_appenv->lang('name', 'common')?></th>
			            <th><?php echo $mbs_appenv->lang('password')?></th>
			            <th><?php echo $mbs_appenv->lang(array('edit', 'time'), 'common')?></th>
			            <th><?php echo $mbs_appenv->lang(array('member', 'manage'))?></th>
			        </tr>
			    </thead>
			    <tbody>
			    <?php foreach($list as $k=>$row){ $udepmbr_ctr->setPrimaryKey($row['id']); ?>
			        <tr>
			            <td class="first-col">
			            	<input type="checkbox" name="id[]" value="<?php echo $row['id']?>" />
			            </td>
			            <td class=name><?php echo $row['name']?></td>
			            <td><?php echo $row['password']?></td>
			            <td><?php echo date('Y-m-d H:i', $row['edit_time'])?></td>
			            <td><a href="<?php echo $mbs_appenv->toURL('dep_member','', array('dep_id'=>$row['id']))?>">
			            	<?php echo sprintf($mbs_appenv->lang('total_member'), $udepmbr_ctr->getTotal())?></a></td>
			        </tr>
			    <?php }?>
			    </tbody>
			</table>
			<div style="margin-top:10px;" class=box-bottom>
				<a href="javascript:document._form.action='<?php echo $mbs_appenv->toURL('dep_edit', '', array('delete'=>1))?>';document._form.submit();" class="btn-del" >
					<i class="ico"></i><?php echo $mbs_appenv->lang('delete')?></a>
				<a href="javascript:document._form.submit();" class="btn-send" >
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