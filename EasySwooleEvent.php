<?php
/**
 * 事件管理
 * PHP version 7
 * swoole4.3
 *
 * @category Null
 * @package  Null
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;   // 接收数据
use EasySwoole\Http\Response;  // 返回数据
use App\Process\HotReload;   // 热更新
use EasySwoole\Component\Di;   // 单例
use Swoole\Server;
use App\WebSocket\WebSocketEvent;
use App\WebSocket\WebSocketParser;
use App\model\OO;
use EasySwoole\Component\TableManager; // 共享内存
use swoole\table;
//orm
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config;



/**
 * 事件管理
 * PHP version 7
 * swoole4.3
 *
 * @category Null
 * @package  Null
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
class EasySwooleEvent implements Event
{

    /**
     * 初始化--最先启动，在主进程启动前调用
     *
     * @return : ‘’
     */
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
      
    }

    /**
     * 初始化--最先启动，在主进程启动前调用
     *
     * @param EventRegister $register 注册实例
     *
     * @return : ''
     */
    public static function mainServerCreate(EventRegister $register)
    {
        $swooleServer = ServerManager::getInstance()->getSwooleServer();

        //注册mysql链接
        $config = new Config();
        $config->setDatabase('easyswoole_orm');
        $config->setUser('root');
        $config->setPassword('123456');
        $config->setHost('127.0.0.1');
        //连接池配置
        $config->setGetObjectTimeout(3.0); //设置获取连接池对象超时时间
        $config->setIntervalCheckTime(30*1000); //设置检测连接存活执行回收和创建的周期
        $config->setMaxIdleTime(15); //连接池对象最大闲置时间(秒)
        $config->setMaxObjectNum(20); //设置最大连接池存在连接对象数量
        $config->setMinObjectNum(5); //设置最小连接池存在连接对象数量
        DbManager::getInstance()->addConnection(new Connection($config),'masterdb');

          //注册一张内存表
        TableManager::getInstance()->add(
            'UserOnline',
            [
                'fd'    =>['type'=>Table::TYPE_INT , 'size'=>11],
                'name'  =>['type'=>Table::TYPE_STRING , 'size'=>64],
                'uname' =>['type'=>Table::TYPE_STRING , 'size'=>64],
                'pic'   =>['type'=>Table::TYPE_STRING , 'size'=>200],
                'test_num' =>['type'=>Table::TYPE_FLOAT ],
            ],
            1024
        );   


        //注册 主进程启动事件
        $register->add(
            $register::onStart,
            function (\swoole_server $serv) {
                echo "MasterStart\n";
                swoole_set_process_name("chat_swoole_master");
            }
        );
         //注册 manager进程启动事件
        $register->add(
            $register::onManagerStart,
            function (\swoole_server $serv) {
                echo "ManagerStart\n";
                swoole_set_process_name("chat_swoole_manager");
            }
        );
        //注册 worker进程启动事件
        $register->add(
            $register::onWorkerStart,
            function (\swoole_server $serv, int $workid) {
                 //单例管理
                $oo = new OO();
                $oo->run();
                if ($workid >= $serv->setting['worker_num']) {
                        $msg= "Taskwoker ".$workid.'start'."\n";
                        swoole_set_process_name("chat_swoole_taskworker_{$workid}");
                } else {
                        $msg =  "wroker ".$workid.'start'."\n";
                        swoole_set_process_name("chat_swoole_worker_{$workid}");
                }
                echo $msg;
            }
        );
         // 注册一个热更新进程
         $swooleServer->addProcess((new HotReload('swoole_HotReload', ['disableInotify' => false]))->getProcess());


        // 创建一个 Dispatcher 配置
        $conf = new \EasySwoole\Socket\Config();
        // 设置 Dispatcher 为 WebSocket 模式
        $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
        // 设置解析器对象
        $conf->setParser(new WebSocketParser());
        // 创建 Dispatcher 对象 并注入 config 对象
        $dispatch = new \EasySwoole\Socket\Dispatcher($conf);
        // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
        $register->set(
            EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
                // {"class":"Index","action":"index","content":{"name":"\u4ed9\u58eb\u53ef"}}
                //具体解析流程   $conf->setParser(new WebSocketParser()); 
                var_dump($frame);
                $dispatch->dispatch($server, $frame->data, $frame);
            }
        );
         //自定义握手事件
        $websocketEvent = new WebSocketEvent();
        $register->set(
            EventRegister::onHandShake, function (\swoole_http_request $request, \swoole_http_response $response) use ($websocketEvent) {
                $websocketEvent->onHandShake($request, $response);
            }
        );
        //自定义关闭事件
        $register->set(
            EventRegister::onClose, function (\swoole_server $server, int $fd, int $reactorId) use ($websocketEvent) {
                $websocketEvent->onClose($server, $fd, $reactorId);
            }
        );



    }
 
    /**
     * 当EasySwoole收到任何的HTTP请求时，均会执行该事件。该事件可以对HTTP请求全局拦截。
     *
     * @param Request  $request  请求参数
     * @param Response $response 返回实例
     *
     * @return : bool
     */
    public static function onRequest(Request $request, Response $response): bool
    {
        //Implement onRequest() method.
        return true;
    }
  
    /**
     * 可在该事件中做trace 进行请求的追踪监视,以及获取此次的响应内容
     *
     * @param Request  $request  请求参数
     * @param Response $response 返回实例
     *
     * @return : void
     */
    public static function afterRequest(Request $request, Response $response): void
    {
        //Implement afterAction() method.
    }
    /**
     * 当EasySwoole收到任何的HTTP请求时，均会执行该事件。该事件可以对HTTP请求全局拦截。
     *
     * @param swoole_server $server     服务实例
     * @param int           $fd         连接描述符
     * @param int           $reactor_id 线程id
     * @param string        $data       数据
     *
     * @return : bool
     */
    public static function onReceive(\swoole_server $server, int $fd, int $reactor_id, string $data): void
    {
        echo "TCP onReceive.\n";
    }
}
