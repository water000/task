<?php

class CCoreDef extends CModDef{
	protected function desc(){
		return array(
		    self::MOD => array(
		    	self::G_NM=>'core', 
		    	self::M_CS=>'gbk', 
		    	self::G_TL=>'内核模块', 
		    	self::G_DC=>'核心模块，定义了模块的组成部分，详细看class/CModDef.php'
		   	),
		    self::LD_FTR => array(
				//array('user', 'checkLogin', true),
			),
		    self::TAG => array(
		   	 	//'url' => array(self::G_CS => 'CFileURL', self::G_DC => '(,,mod,file,[type])'),
		    ),
			self::PAGES => array(
				'mod_info' => array(
					self::P_TLE => '模块信息',
					self::G_DC  => '模块的基本信息，主要是定义在ModDef中的，也包括模块下面所有的文件',
					self::P_MGR => true,
					self::P_ARGS => array(
						'mod'   => array(self::PA_TYP=>'string', self::PA_REQ=>0, self::G_DC=>'查看指定模块下的action信息'),
					)
				),
				'action_info' => array(
					self::P_TLE => '页面信息',
					self::G_DC  => '页面的基本信息，主要是定义在ModDef->PAGES中的，按照最后修改时间排序',
					self::P_MGR => true,
					self::P_ARGS => array(
						'mod' => array(self::PA_TYP=>'string', self::PA_REQ=>0, self::G_DC=>'可以查看指定模块'),
						'otype' => array(self::PA_TYP=>'string', self::PA_REQ=>0, self::G_DC=>'查看指定输出类型的action，分为html,not_html(json,xml,...)'),
					)
				),
				'dev_about' => array(
					self::P_TLE => '开发相关',
					self::G_DC  => '项目开发过程中的一些约定、规则和问题',
					self::P_MGR => true
				),
				'api_log'   => array(
					self::P_TLE => '接口日志',
					self::G_DC  => '接口访问的日志，记录了输入、输出、、时间、以及其他相关信息',
					self::P_MGR => true
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
		 		'core_api_log'  => '(
		 			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`input` text,
					`output` text,
					`other` text,
					`time` int(10) unsigned DEFAULT NULL,
					PRIMARY KEY (`id`)
		 		)',
		  	),
		    /*self::LTN => array(
		    	'class' => 'mod.action1,mod.action2,...'
		    ),*/
	  );
	}
}

?>