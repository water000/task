2014/11/27
1. 将页面请求中的参数检查由英文转成中文, CModDef.php
2.需要将CObjectDB中的主键由%d转成'%s', 因为有些表的主键不一定是数字型。
   还有，CObjectDB名字中的DB需要再考虑下，因为DB代表的是一个数据库，
   而此处需要表达的是一张数据库中的表. CObjectDB.php
3.需要将环境中的'client_ip'的获取方式丰富化。CAppEnvironment.php

2014/12/26
将CObjectDB统一改成CUniqRowOfTable