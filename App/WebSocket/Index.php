<?php
/**
 * 控制器websocket
 * PHP version 7
 * swoole4.3
 *
 * @category Null
 * @package  Null
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
namespace App\WebSocket;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\Task\TaskManager;
use EasySwoole\Socket\AbstractInterface\Controller;
use App\Utility\Pool\MysqlPool;
use EasySwoole\Component\Di;   // 单例
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\Component\TableManager; //共享内存

/**
 * Class Index
 *
 * 此类是默认的 websocket 消息解析后访问的 控制器
 *
 * @category Null
 * @package  App\WebSocket
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
class Index extends Controller
{
    /**
     * 测试
     *
     * @return array
     */
    function hello()
    {
        $this->response()->setMessage('call hello with arg:'. json_encode($this->caller()->getArgs()));
    }
    /**
     * 测试
     *
     * @return array
     */
    public function who()
    {
        $this->response()->setMessage('your fd is '. $this->caller()->getClient()->getFd());
    }
    /**
     * 测试
     *
     * @return array
     */
    function delay()
    {
        $this->response()->setMessage('this is delay action');
        $client = $this->caller()->getClient();

        // 异步推送, 这里直接 use fd也是可以的
        TaskManager::async(
            function () use ($client) {
                 $server = ServerManager::getInstance()->getSwooleServer();
                 $i = 0;
                while ($i < 5) {
                     sleep(1);
                    $server->push($client->getFd(), 'push in http at '. date('H:i:s'));
                     $i++;
                }
            }
        );
    }


    /**
     * 注册
     *
     * @return array
     */
    public function register()
    {
        $param = $this->caller()->getArgs();
        $uname =  Di::getInstance()->get('Fuc')->escape($param['uname']);
        $name  =  Di::getInstance()->get('Fuc')->escape($param['name']);
        $upass = $param['upass'];
        $db = PoolManager::getInstance()->getPool(MysqlPool::class)->getObj();
        $table_name =  'chutest.chatuser';
        $data = $db->where('uname', $uname, '=')->get($table_name, null, 'id,name');
        if ($data) {
            $this->response()->setMessage(json_encode(array('act'=>'register',  'code'=>1,  'err'=>'用户名已存在!')));
        } else {
            $insert_id = $db->insert($table_name, array('name'=>$name,'uname'=>$uname , 'upass'=>$upass));
            $msg = "注册成功，请登录";
            if (!$insert_id) {
                $msg  = '数据库错误，请重试';
            }
            $this->response()->setMessage(json_encode(array('act'=>'register','code'=>0,'err'=>$msg)));
        }
    }
    /**
     * 登录
     *
     * @return array
     */
    public function login()
    {
        $param = $this->caller()->getArgs();
        $uname =  Di::getInstance()->get('Fuc')->escape($param['uname']);
        $upass = $param['upass'];
        $fd    = $this->caller()->getClient()->getFd();
        echo 'fd :'.$fd."\n";
        $db = PoolManager::getInstance()->getPool(MysqlPool::class)->getObj();
        $table_name =  'chutest.chatuser';
        $data = $db->where('uname', $uname, '=')->where('upass', $upass, '=')->getOne($table_name, 'id,name,uname');
        if ($data) {
            $table = TableManager::getInstance()->get('UserOnline');

            $table->set($fd,  ['fd'=>$fd , 'name'=>$data['name'] , 'uname'=>$data['uname'] , 'pic'=> 'http://39.97.98.75/swoole/easyswooleChat/App/Public/user_img/00'.rand(1, 4).'.jpg' ]);
            $server = ServerManager::getInstance()->getSwooleServer();
            $start = 0;
            while (true) {
                $conn_list = $server->connection_list($start, 10);
                if (empty($conn_list)) {
                    break;
                }
                $start = end($conn_list);
                foreach ($conn_list as $fd) {
                    $info = $server->getClientInfo($fd);
                    // 判断此fd 是否是一个有效的 websocket 连接
                    if ($info && $info['websocket_status'] == WEBSOCKET_STATUS_FRAME) {
                        $allUser = $this->getAllUser(1, $fd);
                        $str = json_encode(array('act'=>'getAllUser','code'=>0,'err'=>'' , 'data'=> $allUser));
                        echo "FD:".$fd." -------------".$str."\n";
                        $server->push($fd, $str);
                    }
                }
            }

            $this->response()->setMessage(json_encode(array('act'=>'login',  'code'=>0,  'err'=>'登录成功' , 'name'=>$data['name'])));

        } else {
            $this->response()->setMessage(json_encode(array('act'=>'login','code'=>1,'err'=>'用户名或密码错误')));
        }
    }

    /**
     * 测试
     *
     * @return array
     */
    function say()
    {
        $param = $this->caller()->getArgs();
        $content = $param['content'];
        $fd      = $param['fd'];
        $server = ServerManager::getInstance()->getSwooleServer();
        $myfd    = $this->caller()->getClient()->getFd();
        $start = 0;
        $table = TableManager::getInstance()->get('UserOnline');
        $res  =  $table->get($myfd);
        $str = json_encode(array('act'=>'say','code'=>0,'err'=>'' , 'name'=>$res['name'] , 'msg'=> $content));
        $server->push($fd, $str);
        return true;
        //             $server->push($fd, $str);
        // // 此处直接遍历所有FD进行消息投递
        // // 生产环境请自行使用Redis记录当前在线的WebSocket客户端FD
        // while (true) {
        //     $conn_list = $server->connection_list($start, 10);
        //     if (empty($conn_list)) {
        //         break;
        //     }
        //     $start = end($conn_list);
        //     foreach ($conn_list as $fd) {
        //         if ($myfd == $fd) {
        //             continue;
        //         }
        //         $info = $server->getClientInfo($fd);
        //         $res  =  $table->get($fd);
        //         // 判断此fd 是否是一个有效的 websocket 连接
        //         if ($info && $info['websocket_status'] == WEBSOCKET_STATUS_FRAME) {
        //             $str = json_encode(array('act'=>'say','code'=>0,'err'=>'' , 'msg'=> $res['name'].' say :'.$content));
        //             $server->push($fd, $str);
        //         }
        //     }
        // }
    }

    /**
     * 返回所有在线用户
     *
     * @param int $type 返回值区分
     * @param int $fd   需要过滤的fd
     *
     * @return array
     */
    public function getAllUser($type = 0 , $fd=0)
    {
        $fd    = $fd==0 ? $this->caller()->getClient()->getFd() : $fd;
        $table = TableManager::getInstance()->get('UserOnline');
        $buff = [];
        foreach ($table as $key=>$item) {
            if ($key == $fd) {
                continue;
            }
            $buff[] = $item;
        } 
        if ($type == 1) {
            print_r($buff);
            return $buff;
        }
        $this->response()->setMessage(json_encode(array('act'=>'getAllUser',  'code'=>0,  'err'=>'' , 'data'=>$buff)));
    }







}
