<?php 

class CForm{
	
	static function align($pargs, $default=array()){
		global $mbs_appenv;
		
		foreach($pargs as $key => $def){
			$range = array(0, 0);
			if(isset($def[CModDef::PA_RNG])){
				$arr = explode(',', $def[CModDef::PA_RNG]);
				$range[0] = intval(trim($arr[0]));
				$range[1] = intval(trim($arr[1]));
			}
?>
<div class="pure-control-group">
	<label><?php echo $def[CModDef::G_TL], isset($def[CModDef::PA_REQ]) && $def[CModDef::PA_REQ] 
		?'<span class=required>*</span>':''?></label>
	<?php if(isset($def[CModDef::PA_TYP]) && 'file' == strtolower($def[CModDef::PA_TYP])){ ?> 
	<input type='file' name='<?php echo $key?>' />
		<?php if(isset($default[$key])){?><img class=form-fld-img src="<?php echo $default[$key]?>" /><?php }?>
	<?php }else if($range[1] > 16){?>
	<textarea name="<?php echo $key?>"><?php echo $default[$key]?></textarea>
	<?php }else{ ?>
	<input type="text" name="<?php echo $key?>" value="<?php echo $default[$key]?>">
	<?php } ?>
	<aside class="pure-form-message-inline">
	<?php echo isset($def[CModDef::PA_RNG])?sprintf($mbs_appenv->lang('char_range', 'common'), $range[0], $range[1]):'', 
		isset($def[CModDef::G_DC])?$def[CModDef::G_DC]:''?></aside>
</div>
<?php			
		}
	}
	
	
}

?>