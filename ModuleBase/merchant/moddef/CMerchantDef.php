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
					abstract varchar(32),
					detail   varchar(128),
				)'
			),
			self::PAGES => array(
				'unionpay_notify' => array(
					self::P_TLE => '银联支付后回调接口',
					self::G_DC  => '银联处理完成回调此接口，用于通知支付是否成功(具体流程看银联文档)。即更新表中status字段',
					self::P_ARGS => array(
					),
					self::P_OUT => '{success:0/1, msg:"如果失败， 返回错误提示.成功后返回user_id", user_id:111}',
				),
			),
		);
	}
}

?>