<?php
/**
 * 自定义控制器命令
 * PHP version 7
 * swoole4.3
 *
 * @category Null
 * @package  TestConsole
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */

namespace App\console;

use EasySwoole\Console\ModuleInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

/**
 * 自定义控制台命令
 *
 * @category Null
 * @package  TestConsole
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
class DefConsole implements ModuleInterface
{
    /**
     * 命令执行
     * exec
     *
     * @param Caller   $caller   参数1
     * @param Response $response 响应
     *
     * @return ''
     */
    public function exec(Caller $caller, Response $response)
    {
        $args = $caller->getArgs();
        $actionName = array_shift($args);
        $caller->setArgs($args);
        switch ($actionName) {
        case 'echo':
            $this->echo($caller, $response);
            break;
        case 'act':
            $this->act($caller, $response);
            break;    
        default:
                $this->help($caller, $response);
        }
        // TODO: Implement exec() method.
    }

    /**
     * 该命令的帮助
     * help
     *
     * @param Caller   $caller   参数1
     * @param Response $response 响应
     *
     * @return ''
     */
    public function help(Caller $caller, Response $response)
    {
        // TODO: Implement help() method.
        $help = <<<HELP
测试的自定义控制器

用法: 命令 [命令参数]

def echo [string]                        | 输出字符串,测试方法
def act [a:123 b:3434]                   | 打印输入参数
HELP;
        $response->setMessage($help);
        // TODO: Implement help() method.
    }

    /**
     * 返回控制器名称
     * moduleName
     *
     * @return string
     */
    public function moduleName(): string
    {
        return 'Def';
        // TODO: Implement moduleName() method.
    }

    /**
     * 输出方法
     * echo
     *
     * @param Caller   $caller   参数1
     * @param Response $response 响应
     *
     * @return : ''
     */
    public function echo(Caller $caller, Response $response)
    {

        $msg = array_shift($caller->getArgs());
        $response->setMessage($msg);
    }
    /**
     * 输出方法
     * echo
     *
     * @param Caller   $caller   参数1
     * @param Response $response 响应
     *
     * @return : ''
     */
    public function act(Caller $caller, Response $response)
    {

        $msg = $caller->getArgs();
        $msg = json_encode($msg);
        $response->setMessage($msg);
    }
}
