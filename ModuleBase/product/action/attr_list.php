<?php 

mbs_import('', 'CProductControl', 'CProductAttrControl');
	
$pdtattr_ctr = CProductAttrControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
$attr_list  = $pdtattr_ctr->getDB()->search(array(), array('order'=>'edit_time DESC'));

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
	$pdtattrmap_list = $pdtattrmap_ctr->get();
	$pdtattrmap = array();
	foreach($pdtattrmap_list as $row){
		$pdtattrmap[$row['aid']] = $row;
	}
	
	if(isset($_REQUEST['submit_relate'])){
		$_REQUEST['aid'] = isset($_REQUEST['aid']) ? $_REQUEST['aid'] : array();
		
		$attr_map = array();
		foreach($attr_list as $lrow){
			$attr_map[$lrow['id']] = $lrow;
		}
		$attr_list = $attr_map;
		
		$_REQUEST['aid'] = array_intersect(array_keys($attr_map), $_REQUEST['aid']);
			
		function _compare($a, $b){ return $a == $b ? 0 : ( $a > $b ? 1 : -1); }
		$exists_aid = array_keys($pdtattrmap);
		$new = array_udiff($_REQUEST['aid'], $exists_aid, '_compare');
		$old = array_udiff($exists_aid, $_REQUEST['aid'], '_compare');
		$set = array_uintersect($exists_aid, $_REQUEST['aid'], '_compare');
		foreach($new as $naid){
			$req = !isset($_REQUEST['req_aid']) || array_search($naid, $_REQUEST['req_aid'])===false ? 0 : 1;
			$kid = isset($_REQUEST['key_attr_'.$naid]) ? intval($_REQUEST['key_attr_'.$naid]) : 0;
			//if(empty($kid)){
			//	$error[] = 'no key selected on attr: '.$naid;
			//	continue;
			//}
			$pdtattrmap[$naid] = array(
				'pid'         => $pid, 
				'aid'         => intval($naid), 
				'required'    => $req,
				'kid'         => $kid,
			);
			$pdtattrmap_ctr->addNode($pdtattrmap[$naid]);
		}
		foreach($old as $oaid){
			$pdtattrmap_ctr->setSecondKey(intval($oaid));
			$pdtattrmap_ctr->delNode();
			unset($pdtattrmap[$oaid]);
		}
		foreach($set as $said){
			$req = !isset($_REQUEST['req_aid']) || array_search($said, $_REQUEST['req_aid'])===false ? 0 : 1;
			$kid = isset($_REQUEST['key_attr_'.$said]) ? intval($_REQUEST['key_attr_'.$said]) : 0;
			if($req != $pdtattrmap[$said]['required'] || $kid != $pdtattrmap[$said]['kid']){
				$pdtattrmap_ctr->setSecondKey(intval($said));
				$arr = array('required'=>$req);
				if(!empty($kid)){
					$arr['kid'] = $kid;
				}
				$pdtattrmap_ctr->setNode($arr);
				$pdtattrmap[$said]['required'] = $req;
			}
		}
		
		mbs_import('common', 'CEvent');
		$ev_args = array(
			'product' => $pdt,
			'req_aid' => $_REQUEST['aid'],
			'attrmap' => $attr_map,
			'pdtattr' => $pdtattrmap,
			'new'     => $new,
			'old'     => $old, 
			'set'     => $set,
		);
		CEvent::trigger('map_changed', $ev_args, $mbs_appenv);
		
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
.product-block{width:50%;margin:10px auto;padding:10px;}
.product{height:50px; background-color:white;position:relative;padding: 5px 5px 5px 60px;border:1px solid #ddd;}
.product img{position:absolute;left:5px;top:5px;width:50px;height:50px;}
.product div.title{font-weight:bold;}
.product div.pcontent{color:#666;margin-top: 3px;}
.popwin iframe{width:100%;height:90%;border:0;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array('attr', 'list'))?>
		<a class="pure-button button-success shortcut-a" style="float: right;" href="<?php echo $mbs_appenv->toURL('attr_edit')?>">
			+<?php echo $mbs_appenv->lang(array('add', 'attr'))?></a></div>
	
	<?php if(HAS_PRODUCT){ ?>
	<div class=product-block>
		<b><?php echo $mbs_appenv->lang(array('selected', 'product'))?></b>
		<div class=product>
			<img src="<?php echo CProductControl::logourl($pdt['logo_path'], $mbs_appenv)?>" />
			<div class=title><a href="<?php echo $mbs_appenv->toURL('edit', '', array('id'=>$_REQUEST['product_id']))?>">
				<?php echo $pdt['name']?></a></div>
			<div class=pcontent><?php echo CStrTools::txt2html($pdt['abstract'])?></div>
		</div>
	</div>
	<form action="" method="post" name=form_relate>
	<input type="hidden" name="submit_relate" />
	<?php } ?>
	<div style="margin:15px 10px;">
		<table class="pure-table pure-table-horizontal">
			<thead><tr><td>#</td><td><?php echo $mbs_appenv->lang('content')?></td>
				<td><?php echo $mbs_cur_moddef->item(CModDef::PAGES, 'attr_edit', CModDef::P_ARGS, 'value_type', CModDef::G_TL)?></td>
				<td><?php echo $mbs_cur_moddef->item(CModDef::PAGES, 'attr_edit', CModDef::P_ARGS, 'unit_or_size', CModDef::G_TL)?></td>
				<td><?php echo $mbs_appenv->lang(array('edit', 'time'))?></td>
				<?php if(HAS_PRODUCT){?><td style="width: 45%;"><?php echo $mbs_appenv->lang('relate')?></td><?php } ?>
			</tr></thead>
			<?php $i=0; foreach($attr_list as $row){ ?>
			<tr><td><?php echo ++$i;?></td>
				<td><a href="<?php echo $mbs_appenv->toURL('attr_edit', '', array('id'=>$row['id']))?>">
					<?php echo $row['en_name']?></a>
					<?php echo HAS_PRODUCT ? '' : '('.CStrTools::cutstr($row['abstract'], 32, $mbs_appenv->item('charset')).')'?>
				</td>
				<td><?php echo CProductAttrControl::vtmap($row['value_type'])?></td>
				<td><?php echo $row['unit_or_size']?></td>
				<td><?php echo CStrTools::descTime($row['edit_time'], $mbs_appenv)?></td>
				<?php if(HAS_PRODUCT){?>
				<td><a class="pure-button pure-button-check" name="aid[]" _value="<?php echo $row['id']?>" 
						_checked="<?php echo isset($pdtattrmap[$row['id']])?'1':'0'?>" ><?php echo $mbs_appenv->lang('relate')?></a>
					<a class="pure-button pure-button-check" name="req_aid[]" 
						_checked="<?php echo isset($pdtattrmap[$row['id']]) && $pdtattrmap[$row['id']]['required'] ?'1':'0'?>" 
						_value="<?php echo $row['id']?>"><?php echo $mbs_appenv->lang('required')?></a>
					<a href="#" name="key_attr_<?php echo $row['id']?>" data-kid="<?php echo isset($pdtattrmap[$row['id']]) ? $pdtattrmap[$row['id']]['kid'] : 0?>"><?php echo $mbs_appenv->lang('kv')?>&gt;</a></td>
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
btnlist(document.form_relate.getElementsByTagName("a"));

var g_cur_btn = null;
window.cb_kv_selected = function(kv){
	if(g_cur_btn != null){
		_kv_show(g_cur_btn, kv);
		_kv_field(g_cur_btn, kv.kid);
		kv.box.checked = false;
		pop_win.hide();
	}
}

var frame = document.createElement("iframe");
frame.src = "<?php echo $mbs_appenv->toURL('attr_kv_list')?>";
var pop_win = popwin("<?php echo $mbs_appenv->lang(array('select', 'kv'))?>", frame);

function _kv_show(btn, kv){
	if(!btn.nextSibling){
		btn.parentNode.appendChild(document.createElement("span"));
	}
	btn.nextSibling.innerHTML = '"'+kv.desc+'"';
}
function _kv_field(btn, kid){
	if(btn.previousSibling.tagName != "INPUT"){
		btn.parentNode.insertBefore(document.createElement("input"), btn);
		btn.previousSibling.name = btn.name;
		btn.previousSibling.type = "hidden";
	}
	btn.previousSibling.value = kid;
}
frame.onload = function(e){
	var kv_btns = document.getElementsByTagName("a"), i, kid;
	for(i=0; i<kv_btns.length; i++){
		kid = kv_btns[i].getAttribute("data-kid");
		if( kid != null){
			kv_btns[i].onclick = function(e){
				pop_win.show();
				g_cur_btn = this;
			}
			if(kid != "0"){
				var kv = kv_query(kid);
				if(kv) _kv_show(kv_btns[i], kv);
			}
		}
	}
}
<?php }?>
</script>
</body>
</html>