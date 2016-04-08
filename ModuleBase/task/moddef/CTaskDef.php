<?php
class CTaskDef extends CModDef {
    protected function desc() {
        return array(
            self::MOD => array(
                self::G_NM=>'task',
                self::M_CS=>'utf-8',
                self::G_TL=>'任务系统',
                self::G_DC=>''
            ),
            self::TBDEF => array(
                'task_department' => '(
                    id          int unsigned auto_increment not null, 
                    name        varchar(16) not null, 
                    abstract    varchar(64) not null, 
                    create_uid  int unsigned not null, 
                    create_time int unsigned not null,
                    primary key(id)
                )',
                'task_dep_member' => '(
                    dep_id     int unsigned not null,
                    member_uid int unsigned not null,
                    join_time  int unsigned not null,
                    status     tinyint not null,
                    primary key(dep_id, member_uid)
                )',
                'task_dep_accountant' => '(
                    dep_id    int unsigned not null, 
                    acnt_id   int unsigned not null, 
                    amount    int unsigned not null, 
                    balance   int unsigned not null, 
                    join_time int unsigned not null, 
                    status    tinyint not null,
                    primary key(dep_id, acnt_id),
                    key(acnt_id)
                )',
                'task_category' => '(
                    id        int unsigned auto_increment not null, 
                    name      varchar(16) not null, 
                    parent_id int unsigned not null, 
                    icon_path varchar(32) not null,
                    primary key(id)
                )',
                'task_info' => '(
					id                   int unsigned auto_increment not null,
				    title                varchar(16) not null,
				    `desc`               varchar(64) not null,
                    contain_attachment   tinyint not null,
                    cate_id              int unsigned not null,
                    price                int unsigned not null,
				    pub_uid              int unsigned not null,
                    pub_time             int unsigned not null,
                    edit_time            int unsigned not null,
                    status               int unsigned not null,
					primary key(id)
				)',
                'task_attachment' => '(
                    id        int unsigned not null, 
                    name      varchar(64) not null, 
                    path      char(32) not null, 
                    size      int unsigned not null, 
                    mime_type varhcar(32) not null, 
                    file_type tinyint not null,
                    primary key(id)
                )',
                'task_submit' => '(
                    id                 int unsigned auto_increment not null, 
                    task_id            int unsigned not null, 
                    uid                int unsigned not null, 
                    time               int unsigned not null, 
                    content            varchar(64) not null, 
                    contain_attachment tinyint not null,
                    comment            varchar(32) not null,
                    status             tinyint not null,
                    primary key(id),
                    key(task_id),
                )',
                'task_submit_paid' => '(
                    acnt_id   int unsigned not null, 
                    submit_id int unsigned not null, 
                    time      int unsigned not null, 
                    amount    int unsigned not null,
                    primary key(acnt_id, submit_id)
                )',
            ),
            self::PAGES => array(
                'opening_list' => array(
                    self::P_TLE => '正打开的任务列表',
                    self::G_DC  => '',
                    self::P_ARGS => array(
                        'category_id' => array(self::PA_REQ=>1, self::G_DC=>'分类id', self::PA_TYP=>'integer'),
                    ),
                    self::P_OUT => '{has_more:0/1, list:[{id:1, 详见task_info表}]}',
                ),
                'logout' => array(
                    self::P_TLE => '注销',
                    self::G_DC  => '注销当前已登录的用户',
                    self::P_OUT => '{retcode:"SUCCESS"}'
                ),
                'edit' => array(
                    self::P_TLE => '编辑',
                    self::G_DC  => '编辑用户信息',
                    self::P_MGR => true,
                    self::P_NCD => true,
                    self::P_ARGS => array(
                        'name'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'2, 17'),
                        'password'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 17'),
                        'organization' => array(self::PA_REQ=>0, self::G_DC=>'单位', self::PA_RNG=>'2, 32'),
                        'phone'        => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机', self::PA_RNG=>'11, 12'),
                        'email'        => array(self::PA_REQ=>0, self::G_DC=>'邮箱', self::PA_RNG=>'6, 255'),
                        'IMEI'         => array(self::PA_REQ=>0, self::G_DC=>'IMEI', self::PA_RNG=>'6, 32'),
                        'IMSI'         => array(self::PA_REQ=>0, self::G_DC=>'IMSI', self::PA_RNG=>'6, 32'),
                        'class_id'     => array(self::PA_REQ=>0, self::G_DC=>'分类id'),
                        'VPDN_name'    => array(self::PA_REQ=>0, self::G_DC=>'VPDN名称', self::PA_RNG=>'6, 32'),
                        'VPDN_pass'    => array(self::PA_REQ=>0, self::G_DC=>'VPDN密码', self::PA_RNG=>'6, 32'),
                        'class_id'     => array(self::PA_REQ=>0, self::G_DC=>'分类id', self::PA_TYP=>'integer', self::PA_RNG=>'1, 4'),
                    ),
                ),
                'list' => array(
                    self::P_TLE => '用户管理',
                    self::G_DC  => '用户的列表，也可以搜索用户(phone, name, email)，都是精确查询，不支持模糊查询',
                    self::P_MGR => true
                ),
                'class' => array(
                    self::P_TLE => '分类管理',
                    self::G_DC  => '获取、删除用户分类',
                    self::P_MGR => true,
                    	
                ),
                'class_edit' => array(
                    self::P_TLE => '分类编辑',
                    self::G_DC => '对分类进行批量编辑',
                    self::P_MGR => true,
                    self::P_NCD => true,
                    self::P_ARGS => array(
                        'name'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'2, 16'),
                        'code'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'编码', self::PA_RNG=>'2, 32'),
                    ),
                ),
                'department' => array(
                    self::P_TLE => '部门管理',
                    self::G_DC => '添加、删除部门，及获取列表',
                    self::P_MGR => true,
                    	
                ),
                'dep_edit' => array(
                    self::P_TLE => '部门编辑',
                    self::G_DC => '对部门进行批量编辑, 以及加入指定用户到当前部门',
                    self::P_MGR => true,
                    self::P_NCD => true,
                    self::P_ARGS => array(
                        'name'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'2,16'),
                        'password' => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6,'),
                    )
                ),
                'myinfo'  => array(
                    self::P_TLE => '我的信息',
                    self::G_DC  => '显示或修改我的信息，如果提供参数列表中的参数时，则进行修改.没有则返回当前用户信息',
                    self::P_ARGS => array(
                        'pwd1'     => array(self::PA_REQ=>1, self::G_DC=>'新密码', self::PA_RNG=>'6, 32'),
                        'pwd2'     => array(self::PA_REQ=>1, self::G_DC=>'确认密码，须与1密码相同', self::PA_RNG=>'6, 32'),
                        'src_pwd'  => array(self::PA_REQ=>1, self::G_DC=>'原来密码', self::PA_RNG=>'6, 32'),
                    ),
                    self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{user-info}',
                    self::LD_FTR => array(
                        array('user', 'checkLogin', true)
                    ),
                )
            ),
        );
    }

    const BANNED_DEL_MAX_CLASS_ID = 3;

    function install($dbpool, $mempool=null){
        parent::install($dbpool, $mempool);

        mbs_import('', 'CUserControl', 'CUserClassControl', 'CUserDepControl');
        try {
            $ins = CUserControl::getInstance(self::$appenv, $dbpool, $mempool);
            $uid = $ins->add(array(
                'id'        => 1,
                'name'      => 'admin',
                'password'  => CUserControl::formatPassword('123321'),
                'phone'     => '13666666666',
                'reg_time'  => time(),
                'reg_ip'    => self::$appenv->item('client_ip')
            ));
            $uid = $ins->add(array(
                'id'        => 2,
                'name'      => 'developer',
                'password'  => CUserControl::formatPassword('123123'),
                'phone'     => '13888888888',
                'reg_time'  => time(),
                'reg_ip'    => self::$appenv->item('client_ip')
            ));
            	
            //由于系统的要求，分类和部门的id需要一一对应.且在CUserDepControl中的$DEP_MAP的内容也是遵循这里的顺序
            $uc = CUserClassControl::getInstance(self::$appenv, $dbpool, $mempool);
            $pre_class = array(
                array('id' => 1, 'name' => '干警', 'code' => 'POLICE', 'create_time' => time()),
                array('id' => 2, 'name' => '厅、处领导', 'code' => 'TC_LDR', 'create_time' => time()),
                array('id' => 3, 'name' => '省委领导', 'code' => 'PV_LDR', 'create_time' => time()),
            );
            foreach($pre_class as $c){
                $uc->add($c);
            }
            	
            $ud = CUserDepControl::getInstance(self::$appenv, $dbpool, $mempool);
            $pre_dep = array(
                array('id' => 1, 'name' => '业务系统查询结果', 'password'=>'123123', 'edit_time'=>time()),
                array('id' => 2, 'name' => '舆情报告', 'password'=>'123123', 'edit_time'=>time()),
                array('id' => 3, 'name' => '声像信息（视频）', 'password'=>'123123', 'edit_time'=>time()),
            );
            foreach($pre_dep as $d){
                $ud->add($d);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}

?>