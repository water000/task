<?php

class CMerchantDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'merchant',
				self::M_CS=>'utf-8',
				self::G_TL=>'商家系统',
				self::G_DC=>'',
			),
			self::LD_FTR => array(
				array('user', 'checkLogin', true)
			),
			self::TBDEF => array(
				'merchant_info' => '(
					id int unsigned not null auto_increment,
					owner_id int unsigned not null,
					lng_lat varchar(32) not null, -- split by -,
					area varchar(16) not null, -- province-city-area
					address varchar(32) not null, -- country-street-...
					post_code varchar(9) not null,
					name varchar(32) not null,
					abstract varchar(256) not null,
					telephone varchar(32) not null,
					status tinyint not null,
					create_time int unsigned not null,
					edit_time int unsigned not null,
					primary key(id),
					key(owner_id),
					unique key(name)
				)',
				'merchant_attachment' => '(
					id int unsigned not null auto_increment,
					merchant_id int unsigned not null,
					format tinyint not null, -- image, video, ...
					name varchar(16) not null,
					path varchar(64) not null, -- only path, not include domain
					abstract varchar(32) not null,
					create_time int unsigned not null,
					primary key(id),
					key(merchant_id)
				)',
				/* merchant_product_***: auto generated by CMctProductControl */
				// merchant_product_attachment_***: auto generated by CMctProductAttachmentControl
				
			),
			self::PAGES => array(
				'edit'     => array(
					self::P_TLE  => '注册',
					self::G_DC   => '注册商家需要填写相应信息，等待审核通过',
					//self::P_MGR  => false,
					self::P_ARGS => array(
						'lng_lat'    => array(self::PA_REQ=>1, self::G_TL=>'位置'),
						'name'       => array(self::PA_REQ=>1, self::G_TL=>'名称', self::PA_RNG=>'2, 16'),
						'abstract'   => array(self::PA_REQ=>1, self::G_TL=>'简介', self::PA_RNG=>'16, 256'),
						'telephone'  => array(self::PA_REQ=>0, self::G_TL=>'电话', self::PA_RNG=>'11,32', self::G_DC=>'电话号码,多个用封号;分隔'),
						'area'       => array(self::PA_REQ=>1, self::G_TL=>'省市区'),
						'address'    => array(self::PA_REQ=>1, self::G_DC=>'详细地址'),
						'image'      => array(self::PA_REQ=>1, self::PA_TYP=>'file', self::G_TL=>'图片'),
					)
				),
				'list'     => array(
					self::P_TLE  => '商家列表',
					self::G_DC   => '商家的列表，显示商家的信息',
					self::P_MGR  => true,
					self::P_ARGS => array(
					),
				),
				'del'     => array(
					self::P_TLE  => '删除商家',
					self::G_DC   => '删除指定商家，包括图片',
					self::P_MGR  => true,
					self::P_NCD  => true,
					self::P_ARGS => array(
					),
				),
			),
		);
	}
}

?>