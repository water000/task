<?php 

mbs_import('', 'CMctControl', 'CMctProductControl', 'CMctProductMapControl', 'CMctProductAttachmentControl');
mbs_import('product', 'CProductControl', 'CProductAttrMapControl', 'CProductAttrKVControl');
mbs_import('user', 'CUserSession');

$us = new CUserSession();
$user_info = $us->get();

$merchant_id = $user_info['merchant_id'];

$pdt_ctr = CProductControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

if(isset($_REQUEST['item'])){
	$pdt_ctr->setPrimaryKey($_REQUEST['item']);
}

?>
<!doctype html>
<html>
<head>
<title><?php mbs_title()?></title>
<link href="<?php echo $mbs_appenv->sURL('pure-min.css')?>" rel="stylesheet">
<link href="<?php echo $mbs_appenv->sURL('core.css')?>" rel="stylesheet"> 
<style type="text/css">
input,textarea,select{width:300px;}
textarea{height:85px;}
</style>
</head>
<body>
<div class="warpper">
	<div class="ptitle"><?php echo $mbs_appenv->lang(array($page_title, 'attr'))?>
		<a class=back href="<?php echo $mbs_appenv->toURL('attr_list')?>">&lt;<?php echo $mbs_appenv->lang(array('attr', 'list'))?></a></div>
	<div class="">
	<form class="pure-form pure-form-aligned" method="post" name="_form" enctype="multipart/form-data" >
		<input type="hidden" name="_timeline" value="<?php echo time()?>" />
		<fieldset>
			<?php if(isset($_REQUEST['_timeline'])){ if(isset($error[0])){ ?>
			<div class=error><?php echo $error[0];unset($error[0])?></div>
			<?php }else if(empty($error)){?>
			<div class=success><?php echo $mbs_appenv->lang('operation_success')?></div> 
			<?php }} ?>
						
			<div class="pure-control-group">
				<label><?php echo $mbs_appenv->lang('product')?></label>
				<div style="display: inline-block;">
				<?php
				if(isset($_REQUEST['item'])){
					$pdt_info = $pdt_ctr->get();
				}else{
					$mct_pdt_map_ctr = CMctProductMapControl::getInstance($mbs_appenv, 
						CDbPool::getInstance(), CMemcachedPool::getInstance(), $merchant_id);
					$pdt_list = $mct_pdt_map_ctr->get();
				}

				?>
				</div>
			</div>
			
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['abstract'])?></label>
				<textarea name="abstract"><?php echo $info['abstract']?></textarea>
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['abstract'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['value_type'])?></label>
				<select name="value_type">
					<option value=0><?php echo $mbs_appenv->lang('choose_please')?></option>
					<?php foreach(CProductAttrControl::vtmap() as $key => $v){?>
					<option value="<?php echo $key?>" <?php echo $key==$info['value_type']?' SELECTED':''?>><?php echo $v?></option>
					<?php }?>
				</select>
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['value_type'], $mbs_appenv)?></aside>
			</div>
			<div class="pure-control-group">
				<label><?php CStrTools::fldTitle($mbs_cur_actiondef[CModDef::P_ARGS]['unit_or_size'])?></label>
				<input type="text" name="unit_or_size" value="<?php echo $info['unit_or_size']?>" />
				<aside class="pure-form-message-inline"><?php CStrTools::fldDesc($mbs_cur_actiondef[CModDef::P_ARGS]['unit_or_size'], $mbs_appenv)?></aside>
			</div>
			<?php if(isset($_REQUEST['id'])){?>
			<div class="pure-control-group">
                <label><?php $mbs_appenv->lang(array('add', 'time'))?></label>
                <?php echo CStrTools::descTime($info['create_time'], $mbs_appenv)?>
            </div>
            <?php }?>
            <div class="pure-control-group">
                <label></label>
                <button type="submit" class="pure-button pure-button-primary" onclick="submitForm(this)"><?php echo $mbs_appenv->lang('submit')?></button>
            </div>
		</fieldset>
	</form>
	</div>
	<div class="footer"></div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('global.js')?>"></script>
<script type="text/javascript">
<?php if(!empty($error)){?>
formSubmitErr(document._form, <?php echo json_encode($error)?>);
<?php }?>
</script>

</body>
</html>