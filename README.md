### 环境要求
php7.2  swoole4.4  easyswoole3.3.4

### 启动方式
php easyswoole  start  
php easyswoole  stop
php easyswoole  reload

### 默认开启了守护进程模式 ， 可以在dev.php修改daemonize = 0， 转为控制台模式




### 文件说明
该项目是一个websocket服务器
App/WebSocket/index.php  控制器文件
dev.php  配置文件
EasySwooleEvent.php  事件注册文件

App/Public/index.html   客户端聊天页面  （记得修改里面的请求地址）






### 数据表
--
-- Database: `easyswoole_orm`
--

-- --------------------------------------------------------

--
-- 表的结构 `chatuser`
--

CREATE TABLE `chatuser` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `name` varchar(100) DEFAULT NULL COMMENT '昵称',
  `uname` varchar(100) DEFAULT NULL COMMENT '登录名',
  `upass` varchar(100) DEFAULT NULL COMMENT '登录密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='聊天室用户表';


### 在线demo地址
http://www.czc123.top/swoole/easyswooleChat/App/Public/index.html
测试账号 ：  admin   123456