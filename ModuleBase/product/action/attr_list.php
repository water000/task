<?php 

mbs_import('', 'CProductControl', 'CProductAttrControl');
	
$pdtattr_ctr = CProductAttrControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
$list  = $pdtattr_ctr->getDB()->search(array(), array('order'=>'last_edit_time DESC'));

if(isset($_REQUEST['product_id'])){
	$pid = intval($_REQUEST['product_id']);
	
	$pdt_ctr = CProductControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $pid);
	$pdt = $pdt_ctr->get();
	if(empty($pdt)){
		$mbs_appenv->echoex('product not found', 'INVALID_PRODCUT');
		exit(0);
	}
	define('HAS_PRODUCT', true);
	
	mbs_import('', 'CProductAttrMapControl');
	$pdtattrmap_ctr = CProductAttrMapControl::getInstance($mbs_appenv,
		CDbPool::getInstance(), CMemcachedPool::getInstance(), $pid);
	$attrmap_list = $pdtattrmap_ctr->get();
	$attrmap = array();
	foreach($attrmap_list as $row){
		$attrmap[$row['aid']] = $row;
	}
	
	if(isset($_REQUEST['aid'])){
		function _compare($a, $b){
			return $a['aid'] == $b ? 0 : 1;
		}
		$new = array_udiff($_REQUEST['aid'], $attrmap_list, '_compare');
		$old = array_udiff($attrmap_list, $_REQUEST['aid'], '_compare');
		foreach($new as $naid){
			$pdtattrmap_ctr->add(array(
				'pid'         => $pid, 
				'aid'         => intval($naid), 
				'required'    => isset($_REQUEST['req_aid']) && array_search($_REQUEST['req_aid'], $naid)===false ? 0 : 1,
				'relate_time' => time(),
			));
		}
		foreach($old as $oaid){
			$pdtattrmap_ctr->setSecondKey(intval($oaid));
			$pdtattrmap_ctr->destroy();
		}
	}
}
else{
	define('HAS_PRODUCT', false);
}
?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet"> 
<style type="text/css">
.pure-table-horizontal{border-left:0;border-right:0;}
.row-onmouseover{background-color:#E3EEFB;}
.product-block{width:50%;margin:10px auto;padding:10px; background-color:#e0e0e0;}
.product{height:50px; background-color:white;position:relative;padding: 5px 5px 5px 60px;}
.product img{position:absolute;left:5px;top:5px;width:50px;height:50px;}
.product div.title{font-weight:bold;}
.product div.pcontent{color:#666;margin-top: 3px;}
.pure-button-check{padding:.3em;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array('attr', 'list'))?>
		<a class="pure-button button-success shortcut-a" style="float: right;" href="<?php echo $mbs_appenv->toURL('attr_edit')?>">
			+<?php echo $mbs_appenv->lang(array('add', 'attr'))?></a></div>
	
	<?php if(HAS_PRODUCT){ ?>
	<div class=product-block>
		<div class=product>
			<img src="<?php echo CProductControl::logourl($pdt['logo_path'], $mbs_appenv)?>" />
			<div class=title><a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$_REQUEST['product_id']))?>">
				<?php echo $pdt['name']?></a></div>
			<div class=pcontent><?php echo CStrTools::txt2html($pdt['abstract'])?></div>
		</div>
	</div>
	<form action="" method="post" name=form_relate>
	<?php } ?>
	<div style="margin:15px 10px;">
		<table class="pure-table pure-table-horizontal">
			<thead><tr><td>#</td><td><?php echo $mbs_appenv->lang('content')?></td>
				<td><?php echo $mbs_cur_moddef->item(CModDef::PAGES, 'attr_edit', CModDef::P_ARGS, 'value_type', CModDef::G_TL)?></td>
				<td><?php echo $mbs_cur_moddef->item(CModDef::PAGES, 'attr_edit', CModDef::P_ARGS, 'unit_or_size', CModDef::G_TL)?></td>
				<td><?php echo $mbs_cur_moddef->item(CModDef::PAGES, 'attr_edit', CModDef::P_ARGS, 'value_opts', CModDef::G_TL)?></td>
				<td><?php echo $mbs_appenv->lang(array('edit', 'time'))?></td>
				<?php if(HAS_PRODUCT){?><td><?php echo $mbs_appenv->lang('relate')?></td><?php } ?>
			</tr></thead>
			<?php $i=0; foreach($list as $row){ ?>
			<tr><td><?php echo ++$i;?></td>
				<td><a href="<?php echo $mbs_appenv->toURL('attr_edit', '', array('id'=>$row['id']))?>">
					<?php echo $row['name'], '/', $row['en_name']?></a>
					<?php echo HAS_PRODUCT ? '' : '('.CStrTools::cutstr($row['abstract'], 32, $mbs_appenv->item('charset')).')'?>
				</td>
				<td><?php echo CProductAttrControl::vtmap($row['value_type'])?></td>
				<td><?php echo $row['unit_or_size']?></td>
				<td><?php echo $row['value_opts'], $row['allow_multi']?'<span class=pure-button-checked>'.$mbs_appenv->lang('allow_multi').'</span>':''?></td>
				<td><?php echo CStrTools::descTime($row['last_edit_time'], $mbs_appenv)?></td>
				<?php if(HAS_PRODUCT){?>
				<td><a class="pure-button pure-button-check" name="aid[]" _value="<?php echo $row['id']?>" 
						_checked="<?php echo isset($attrmap[$row['id']])?'1':'0'?>" ><?php echo $mbs_appenv->lang('relate')?></a>
					<a class="pure-button pure-button-check" name="req_aid[]" 
						_checked="<?php echo isset($attrmap[$row['id']]) && $attrmap[$row['id']]['required'] ?'1':'0'?>" 
						_value="<?php echo $row['id']?>"><?php echo $mbs_appenv->lang('required')?></a></td>
				<?php } ?>
			</tr>
			<?php } ?>
		</table>
		<?php if(HAS_PRODUCT){?>
		<div style="text-align: right;"><button class="pure-button pure-button-primary" 
			onclick="return confirm('<?php echo $mbs_appenv->lang('confirm')?>?')&&submitForm(this);">
			<?php echo $mbs_appenv->lang(array('confirm', 'submit'))?></button></div>
		<div style="text-align: right; color:red;"><?php echo $mbs_appenv->lang('cancle_relate_warning')?></div>
		</form>
		<?php }?>
	</div>
	<div class="footer"></div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('global.js')?>"></script>
<script type="text/javascript">
switchRow(document.getElementsByTagName("table")[0], 1, null, "row-onmouseover");
<?php if(HAS_PRODUCT){?>
btnlist(document.form_relate.getElementsByTagName("a"));;
<?php }?>
</script>
</body>
</html>