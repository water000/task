<?php
class CCommonDef extends CModDef{
	function desc(){
		return array(
		    self::MOD => array(self::G_NM=>'common', self::M_CS=>'gbk', ),
		    /*self::FTR => array(
		    	self::G_NM => array(G_CS => '', G_DC => ''),
		    ),*/
		    /*self::LTN => array(
		    	'class' => 'mod.action1,mod.action2,...'
		    ),*/
			self::PAGES => array(),
	  );
	}
}

?>