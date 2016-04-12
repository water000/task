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
                        'res_page_id' => array(self::PA_REQ=>0, self::G_DC=>'分页id', self::PA_TYP=>'integer'),
                    ),
                    self::P_OUT => '{has_more:0/1, list:[{id:1, 详见#task_info#表}, ...]}',
                ), 
                'category_list' => array(
                    self::P_TLE => '分类列表',
                    self::G_DC  => '',
                    self::P_ARGS => array(),
                    self::P_OUT => '{{id:1, name:"", icon:""}, ...}'
                ),
                'detail' => array(
                    self::P_TLE => '任务详情',
                    self::G_DC  => '',
                    self::P_ARGS => array(
                        'task_id' => array(self::PA_REQ=>1, self::G_DC=>'任务id', self::PA_TYP=>'integer'),
                    ),
                    self::P_OUT => '{id:1, 详见task_info表}'
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