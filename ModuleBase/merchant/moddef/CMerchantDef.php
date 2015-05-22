<?php

class CMerchantDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'merchant',
				self::M_CS=>'utf-8',
				self::G_TL=>'商家系统',
				self::G_DC=>''
			),
			self::TBDEF => array(
				'merchant_info' => '(
					id int unsigned not null auto_increment,
					owner_id int unsigned not null,
					abstract varchar(32),
					detail   varchar(128),
					
					primary key(id),
					key(owner_id)
				)',
				'merchant_product_model'=>'(
					id int unsigned not null auto_increment,
					en_name varchar(16) not null, -- used to name of table
					abstract varchar(32),
					detail   varchar(128),
				)',
				'merchant_pm_field_def'=>'(
					id int unsigned not null auto_increment,
					en_name varchar(16) not null,
					cn_name varchar(16) not null, -- used to shown on page
					abstract varchar(32), not null,
					value_type tinyint unsigned not null default 0, -- char, int , ...
					unit_or_size varchar(8) not null default "", -- unit for number , size for string
					value_opts varchar(128) not null default "",
					is_multi_opt tinyint not null default 0,
					default_value varchar(64) not null default "",
					primary key(id),
					unique key(en_name)
				)',
			),
			self::PAGES => array(
			),
		);
	}
}

?>