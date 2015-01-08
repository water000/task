<?php

class CCoreDef extends CModDef{
	function desc(){
		return array(
		    self::MOD => array(self::G_NM=>'core', self::M_CS=>'gbk', ),
		    /*self::FTR => array(
		    	'name' => array(G_CS => '', G_DC => ''),
		    ),*/
		    self::TAG => array(
		   	 	'url' => array(self::G_CS => 'CFileURL', self::G_DC => '(,,mod,file,[type])'),
		    ),
		    self::PAGE_ARG => array(
		    	'detail.php'      => array(
		    		'mod'  => array(),
		    		'type' => array(self::PARG_DEP=>'file'),
		    		'file' => array(self::PARG_DEP=>'type'),
		    		'tb_edit_name' => array(self::PARG_DEP=>'tb_edit_text'),
		    		'tb_edit_text' => array(self::PARG_DEP=>'tb_edit_name')
		    	),
		    	'source_code.php' => array(
		    		'mod'  => array(), 
		    		'type' => array(),
		    		'file' => array(),
		    	),
		    	'download.php'    => array(
		    		'mod'  => array(), 
		    		'type' => array(),
		    		'file' => array(),
		    	)
		  	),
		 	self::TBDEF => array(
		  		'core_module_listenner' => "(
		  			class_path varchar(255) CHARACTER SET latin1 not null default '',
		  			class_module varchar(255) CHARACTER SET latin1  not null default '',
		  			class_name varchar(255) CHARACTER SET latin1  not null default '',
		  			action_module varchar(255) CHARACTER SET latin1  not null default '',
		  			action_name varchar(255) CHARACTER SET latin1  not null default '',
		  			primary key(class_name, action_module, action_name),
		  			key(class_module),
		  			key(action_module, action_name)
		  		)",
		  	),
		    /*self::LTN => array(
		    	'class' => 'mod.action1,mod.action2,...'
		    ),*/
	  );
	}
}

?>