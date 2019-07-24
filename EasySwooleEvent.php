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
//连接池
use App\Utility\Pool\MysqlPool;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\Mysqli\Mysqli;


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
        //单例模式 -- 注册类
        Di::getInstance()->set(SysConst::LOGGER_HANDLER, \App\Utility\Logger::class);  // 重写Log类
        $instance = \EasySwoole\EasySwoole\Config::getInstance();
        
        //单例管理
        $oo = new OO();
        $oo->run();

          //注册mysql连接池 --------------------------------------------
        $mysqlConf =  PoolManager::getInstance()->register(MysqlPool::class, Config::getInstance()->getConf('MYSQL.POOL_MAX_NUM'));
        if ($mysqlConf === null) {
            //当返回null时,代表注册失败,无法进行再次的配置修改
            //注册失败不一定要抛出异常,因为内部实现了自动注册,不需要注册也能使用
            throw new \Exception('注册失败!');
        }
        //设置其他参数
        $mysqlConf->setMaxObjectNum(20)->setMinObjectNum(5);
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
