<?php


class CPrivilegeDef extends CModDef {
	
	const TYPE_ALLOW = 1;
	const TYPE_DENY  = 2;
	
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'privilege',
				self::M_CS=>'utf-8',
				self::G_TL=>'权限管理',
				self::G_DC=>'权限管理模块，定义了权限的集合附加到用户上，以管理特定的访问权限。
								首先创建权限组，把允许或拒绝的action放到组中，待用户访问特定action时，
								检测当前用户的权限组是否包含当前action'
			),
			self::LD_FTR => array(
				array('user', 'checkLogin', true),
			),
			self::FTR => array(
				'privFtr' => array(self::G_CS => 'CPrivFilter', self::G_DC => '对当前用户的访问权限进行判断和过滤'),
			),
			self::TBDEF => array(
				'priv_group' => "(
		  			id int unsigned not null auto_increment,
					name varchar(16) not null default '',
					type tinyint not null default 0, -- 1:allow , 2:deny
					priv_list text not null default '',
					creator_id int unsigned not null default 0,
					create_ts int unsigned not null default 0,
					primary key(id),
					unique key(name)
		  		)",
				'priv_user' => "(
					user_id int unsigned not null default 0,
					creator_id int unsigned not null default 0, -- creator_id add priv_group_id to user_id
					priv_group_id int unsigned not null default 0,
					join_ts int unsigned not null default 0,
					last_edit_ts int unsigned not null default 0,
					unique key(user_id),
					key(priv_group_id)
				)"
			),
			self::PAGES => array(
				'index' => array(
					self::P_TLE => '页面导航',
					self::G_DC  => '查找当前用户所属的权限组下面所有的管理页面，并呈现出来',
					self::P_DOF => true,
					self::P_ARGS => array(
					)
				),
				'group_list' => array(
					self::P_TLE => '权限组管理',
					self::G_DC  => '列出权限组包含的信息',
					self::P_MGR => true,
					self::P_ARGS => array(
					),
				),
				'edit_group' => array(
					self::P_TLE => '创建权限组',
					self::G_DC  => '将系统中所有P_MGR 标记为true 的 action按模块分组列出，然后选择相应的action并保存到组中',
					self::P_MGR => true,
					self::P_ARGS => array(
						'name' => array(self::PA_TYP=>'string', self::PA_REQ=>1, 
								self::PA_RNG=>'3, 16', self::G_DC=>'权限组的名称，3-16个字符以内'),
						'type'       => array(self::PA_TYP=>'integer', self::PA_REQ=>1, 
								self::PA_RNG=>'1, 3', self::G_DC=>'权限的类型，分为允许(1), 拒绝(2), 默认为允许'),
						'priv_list'    => array(self::PA_TYP=>'array', self::PA_REQ=>1, 
								self::PA_EMP=>0, self::G_DC=>'所选权限的集合'),
						'group_id'   => array(self::PA_TYP=>'integer', self::PA_REQ=>0,self::G_DC=>'如果当前状态是编辑时，此参数代表当前组')
					),
				),
				
				'join_group' => array(
					self::P_TLE => '用户权限编辑',
					self::G_DC  => '选择一个或多个用户加入到指定的权限组中， 相应的组在group_list中选择',
					self::P_MGR => true,
					self::P_NCD => true,
					self::P_ARGS => array(
						'group_id' => array(self::PA_TYP=>'integer', self::PA_REQ=>1, 
								self::PA_EMP=>0, self::G_DC=>'选择指定的组id'),
						'del'  => array(self::PA_TYP=>'array', self::PA_REQ=>0, self::G_DC=>'选择需要删除的用户'),
						'join' => array(self::PA_TYP=>'array', self::PA_REQ=>0, self::G_DC=>'选择需要加入的用户'),
					),
				),
				'rematch_action' => array(
					self::P_TLE => '重新匹配权限',
					self::P_MGR => true,
					self::G_DC => '重新匹配被重命名或删除的action，然后修改，或删除这些变更',
					self::P_ARGS => array(
						'action_list' => array(self::PA_TYP=>'array', self::PA_REQ=>1, 
								self::PA_EMP=>0, self::G_DC=>'被重命名或删除的action列表')
					)
				)
			),
			/*self::LTN => array(
			 'class' => 'mod.action1,mod.action2,...'
			),*/
		);
	}
	
	function install($dbpool, $mempool=null){
		parent::install($dbpool, $mempool);
		
		mbs_import('', 'CPrivGroupControl', 'CPrivUserControl');
		try {
			$ins = CPrivGroupControl::getInstance(self::$appenv, $dbpool, $mempool);
			$pgid = $ins->add(array(
				'name'      => 'topmost',
				'type'      => self::TYPE_ALLOW,
				'priv_list' => CPrivGroupControl::encodePrivList(array(CPrivGroupControl::PRIV_TOPMOST=>'')),
				'create_ts' => time()
			));
			
			$ins = CPrivUserControl::getInstance(self::$appenv, $dbpool, $mempool, 1);
			$ins->setSecondKey(1);
			$ins->delNode();
			$ins->add(array(
				'user_id'       => 1,
				'priv_group_id' => 1,
				'join_ts'       => time(),
			));
		} catch (Exception $e) {
			throw $e;
		}
	}
}

?>