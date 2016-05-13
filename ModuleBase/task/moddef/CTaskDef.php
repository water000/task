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
                    acnt_uid  int unsigned not null, 
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
                'task_info' => '( -- M(pub_uid, id)
					id                   int unsigned auto_increment not null,
				    title                varchar(16) not null,
				    `desc`               varchar(64) not null,
                    contain_attachment   tinyint not null,  -- no=0, yes=1
                    cate_id              int unsigned not null,
                    price                int unsigned not null,
				    pub_uid              int unsigned not null,
                    pub_time             int unsigned not null,
                    edit_time            int unsigned not null,
                    status               int unsigned not null, -- ST_OPENING=0, ST_CLOSED=1
					primary key(id),
                    key(pub_uid)
				)',
                'task_attachment' => '( -- M(task_id, id)
                    id        int unsigned auto_increment not null,
                    task_id   int unsigned not null, 
                    name      varchar(64) not null, 
                    path      char(32) not null, 
                    size      int unsigned not null, 
                    mime_type varhcar(32) not null, 
                    file_type tinyint not null,
                    primary key(id),
                    key(task_id)
                )',
                'task_submit' => '( -- M(task_id|uid, id)
                    id                 int unsigned auto_increment not null, 
                    task_id            int unsigned not null, 
                    uid                int unsigned not null, 
                    submit_ts          int unsigned not null, 
                    content            varchar(64) not null, 
                    contain_attachment tinyint not null, -- no=0, yes=1
                    comment            varchar(32) not null,
                    -- ST_SUBMITED=0, ST_SAW=10, ST_USED=11, ST_UNUSED=12, ST_WAIT_TO_PAY=20, ST_PAIED=21 , ST_SUB_DEL=30, ST_SYS_DEL=31
                    status             tinyint not null, 
                    primary key(id),
                    key(task_id),
                    key(uid)
                )',
                'task_submit_attachment' => '( -- M(submit_id, id)
                    id        int unsigned auto_increment not null,
                    submit_id int unsigned not null,
                    name      varchar(64) not null,
                    path      char(32) not null,
                    size      int unsigned not null,
                    mime_type varhcar(32) not null,
                    file_type tinyint not null,
                    primary key(id),
                    key(submit_id)
                )',
                'task_submit_to_pay' => '(
                    submit_id int unsigned not null,
                    acnt_id   int unsigned not null, 
                    total_fee int unsigned not null, 
                    submit_ts int unsigned not null,
                    status    tinyint not null, -- 0: to pay, 1: success, 2: fail
                    fault_msg varchar(32) not null,
                    primary key(submit_id),
                    key(acnt_id)
                )',
                'task_submit_paid_history' => '(
                    submit_id   int unsigned not null,
                    acnt_id     int unsigned not null, 
                    total_fee   int unsigned not null,
                    submit_ts   int unsigned not null, 
                    success_ts  int unsigned not null,
                    primary key(submit_id),
                    key(acnt_id)
                )',
            ),
            self::PAGES => array(
                'category_list' => array(
                    self::P_TLE => '分类列表',
                    self::G_DC  => '',
                    self::P_ARGS => array(),
                    self::P_OUT => '{{id:1, name:"", icon:""}, ...}'
                ),
                'category_edit' => array(
                    self::P_TLE => '分类编辑',
                    self::G_DC  => '如果提供参数id，则是修改，反之则是添加',
                    self::P_MGR => true, 
                    self::P_ARGS => array(
                        'id'   => array(self::G_DC=>'任务id', self::PA_TYP=>'integer'),
                        'name' => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_TYP=>'string', self::PA_RNG=>'2, 16'),
                        'icon' => array(self::G_DC=>'图标', self::PA_TYP=>'file'),
                    ),
                ),
                'edit' => array(
                    self::P_TLE => '任务添加、编辑',
                    self::G_DC  => '如果提供参数id，则是修改任务，反之则是添加',
                    self::P_ARGS => array(
                        'id'         => array(self::G_DC=>'任务id', self::PA_TYP=>'integer'),
                        'title'      => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'标题', self::PA_TYP=>'string', self::PA_RNG=>'6, 16'),
                        'desc'       => array(self::G_DC=>'描述', self::PA_TYP=>'string', self::PA_RNG=>'6, 64'),
                        'attachment' => array(self::G_DC=>'附件', self::PA_TYP=>'file'),
                        'cate_id'    => array(self::G_DC=>'分类id', self::PA_TYP=>'integer'),
                        'price'      => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'价格', self::PA_TYP=>'integer'),
                    ),
                    self::P_OUT => '{id:1}, 如果是添加，除了正常的错误标志外还会返回任务id'
                ),
                'detail' => array(
                    self::P_TLE => '任务详情',
                    self::G_DC  => '',
                    self::P_ARGS => array(
                        'task_id' => array(self::PA_REQ=>1, self::G_DC=>'任务id', self::PA_TYP=>'integer'),
                    ),
                    self::P_OUT => '{id:1, 详见task_info表}'
                ),
                'opening_list' => array(
                    self::P_TLE => '正打开的任务列表',
                    self::G_DC  => '',
                    self::P_ARGS => array(
                        'category_id' => array(self::PA_REQ=>1, self::G_DC=>'分类id', self::PA_TYP=>'integer'),
                        'res_page_id' => array(self::PA_REQ=>0, self::G_DC=>'分页id', self::PA_TYP=>'integer'),
                    ),
                    self::P_OUT => '{has_more:0/1, list:[{id:1, 详见#task_info#表}, ...]}',
                ),
                'submit_edit' => array(
                    self::P_TLE => '提交编辑',
                    self::G_DC  => '',
                    self::P_ARGS => array(
                        'id'         => array(self::G_DC=>'提交id，用于编辑', self::PA_TYP=>'integer'),
                        'task_id'    => array(self::PA_REQ=>1, self::G_DC=>'任务id', self::PA_TYP=>'integer'),
                        'uid'        => array(self::PA_REQ=>1, self::G_DC=>'提交用户id', self::PA_TYP=>'integer'),
                        'content'    => array(self::PA_REQ=>1, self::G_DC=>'内容', self::PA_TYP=>'string', self::PA_RNG=>'6, 64'),
                        'attachment' => array(self::G_DC=>'附件', self::PA_TYP=>'file'),
                    ),
                    self::P_OUT => '{id:1}'
                ),
                'submit_stat' => array(
                    self::P_TLE => '提交汇总',
                    self::G_DC  => '',
                    self::P_ARGS => array(
                        'task_id' => array(self::PA_REQ=>1, self::G_DC=>'任务id', self::PA_TYP=>'integer'),
                    ),
                    self::P_OUT => '{total:10, used:3: unused:6: unread:1}'
                ),
                'submit_list' => array(
                    self::P_TLE => '提交列表',
                    self::G_DC  => '',
                    self::P_ARGS => array(
                        'task_id' => array(self::PA_REQ=>1, self::G_DC=>'任务id', self::PA_TYP=>'integer'),
                    ),
                    self::P_OUT => '{has_more:0/1, list:[{id:1, submit_uid, submit_uname, 详见#task_submit#表}, ...]}'
                ),
                /*'logout' => array(
                    self::P_TLE => '注销',
                    self::G_DC  => '注销当前已登录的用户',
                    self::P_OUT => '{retcode:"SUCCESS"}'
                ),*/
                
            ),
        );
    }

}

?>