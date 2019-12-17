<?php

namespace App\Models;

use EasySwoole\ORM\AbstractModel;

/**
 * 用户映射表
 * Class UserShop
 */
class UserModel extends AbstractModel
{

     /**
      * @var 表名 
      */
     protected $tableName = 'chatuser';

     /**
      * 连接别称  ， 在mainserver里面创建连接时设置
      */
     protected $connectionName = 'masterdb';

    
}