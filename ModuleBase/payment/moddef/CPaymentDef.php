<?php

//批量支付 https://doc.open.alipay.com/doc2/detail?treeId=64&articleId=103569&docType=1 
//curl "https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo=银行卡卡号&cardBinCheck=true"
//银联代付 https://open.unionpay.com/ajweb/product/detail?id=67
//银联批量代付文件标准 https://open.unionpay.com/ajweb/help?id=207#4.4.2
class CPaymentDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'payment',
				self::M_CS=>'utf-8',
				self::G_TL=>'支付接口',
				self::G_DC=>'提供支付功能，包括ali，银联等等'
			),
			self::TBDEF => array(
				'payment_order' => '(
					id                 int unsigned not null auto_increment,
					user_id            int unsigned not null,
					product_desc       varchar(16) not null,
					product_img_url    varchar(128),
					product_num        int unsigned,
					product_unit_price int unsigned,
					product_extra      varchar(32), -- product extra info 
					create_ts          int unsigned not null, -- register timestamp
					pay_type           tinyint unsigned, -- 1:points, 2: voucher, 4:alipay, 8:unionpay, 16:weixin, 4:..
					pay_extra          varchar(32) not null, -- extra info of which pay_type
					status             tinyint unsigned, -- 0:unpaid, 1:paid
					primary key(id),
					key(user_id)
				)',
			    'pm_unionpay_to_account' => '(
			        order_id, 
			        acc_no, 
			        acc_type, 
			        acc_name,
			    )',
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