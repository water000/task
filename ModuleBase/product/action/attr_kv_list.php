<?php 
mbs_import('', 'CProductAttrKVControl');

$attr_kv_ctr = CProductAttrKVControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$attr_kv_ctr->setKID();
$keys = array();

$count = $attr_kv_ctr->getTotal();
$pid = isset($_REQUEST['page_id']) ? intval($_REQUEST['page_id']) : 1;
$num_per_page = $attr_kv_ctr->getDB()->getNumPerPage();
$page_num_list = array();
if($count > 0 && $count  > ($pid-1)*$num_per_page){
	$attr_kv_ctr->getDB()->setPageId($pid);
	$keys = $attr_kv_ctr->get();
	
	mbs_import('common', 'CTools');
	$page_num_list = CTools::genPagination($pid, ceil($count/$num_per_page), 8);
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet"> 
<style type="text/css">
i{font-size: 14px;color: #08c;font-weight: bold;display:inline-block;width:18px;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array('attr', 'kv', 'list'))?>
		<span class="tips"><?php echo sprintf($mbs_appenv->lang('total_count'), $count)?></span>
		<a class="pure-button button-success shortcut-a" style="float: right;" href="<?php echo $mbs_appenv->toURL('attr_kv_edit')?>">
			+<?php echo $mbs_appenv->lang(array('add', 'kv'))?></a></div>
	<div style="margin:10px;">
		<table class="pure-table pure-table-horizontal">
			<thead><tr><td>#</td><td><?php echo $mbs_appenv->lang('kv')?></td>
				<td><?php echo $mbs_appenv->lang('operation')?></td></tr></thead>
			<?php $i=0;$prev_char=''; foreach($keys as $row){ 
				$attr_kv_ctr->setPrimaryKey($row['id']); $vs = $attr_kv_ctr->get(); ?>
			<tr><td><input type=checkbox name="kid" value="<?php echo $row['id']?>" /><?php echo ++$i;?></td>
				<td><i><?php if($prev_char!=$row['first_char']){$prev_char=$row['first_char']; echo $prev_char; }else{ echo '&nbsp;'; }?></i>
				<?php echo CStrTools::txt2html($row['value'])?>
				(<?php foreach($vs as $v){ echo CStrTools::txt2html($v['value']), ';'; }?>)</td>
				<td><a href="<?php echo $mbs_appenv->toURL('attr_kv_edit', '', array('kid'=>$row['id']))?>">
					<?php echo $mbs_appenv->lang('edit')?></a></td></tr>
			<?php } ?>
		</table>
		
		<?php if(count($page_num_list) > 1){?>
		<div class="pure-menu pure-menu-horizontal page-break">
			<ul class="pure-menu-list">
			<?php if($pid > 1){ ?>
			<li class="pure-menu-item"><a href="<?php echo $mbs_appenv->toURL('attr_kv_list', '', array('page_id'=>$pid-1)) ?>" 
				class="pure-menu-link" ><?php echo $mbs_appenv->lang('prev_page')?></a></li>
			<?php } ?>
        	<?php foreach($page_num_list as $n => $v){ ?>
        	<li class="pure-menu-item<?php echo $n==$pid?' pure-menu-selected':''?>">
        		<?php if($n==$pid){ echo $v;}else{?>
        		<a href="<?php echo $mbs_appenv->toURL('attr_kv_list', '', array('page_id'=>$n)) ?>" 
        			class="pure-menu-link"><?php echo $v?></a>
        		<?php }?>
        		</li>
        	<?php }?>
        	<?php if($pid < count($page_num_list)){ ?>
	        <li class="pure-menu-item"><a href="<?php echo $mbs_appenv->toURL('attr_kv_list', '', array('page_id'=>$pid+1)) ?>" 
	        	class="pure-menu-link" ><?php echo $mbs_appenv->lang('next_page')?></a></li>
	        <?php }?>
	        </ul>
	    </div>
		<?php } ?>
	</div>
	<div class="footer"></div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('global.js')?>"></script>
<script type="text/javascript">
switchRow(document.getElementsByTagName("table")[0], 1, null, "row-onmouseover");
</script>
</body>
</html>