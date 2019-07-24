<?php
/**
 * 定时任务
 * PHP version 7
 * swoole4.3
 *
 * @category Null
 * @package  Null
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
namespace App\Crontab;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use \EasySwoole\EasySwoole\Logger;

/**
 * 定时任务 -- 每分钟执行一次
 *
 * @category Null
 * @package  Null
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
class TaskOne extends AbstractCronTask
{

    /**
     * 定时任务规则-- 每小时执行一次
     *
     * @return : string
     */
    public static function getRule(): string
    {
        return '*/1 * * * *';
    }
    /**
     * 定时任务名称
     *
     * @return : string
     */
    public static function getTaskName(): string
    {
        // 定时任务名称
        return 'taskOne';
    }

    /**
     * 执行任务的内容
     *
     * @param mixed  $server       任务数据
     * @param int    $taskId       执行任务的task编号
     * @param int    $fromWorkerId 派发任务的worker进程号
     * @param string $flags        自定义参数
     *
     * @return array
     */
    static function run(\swoole_server $server, int $taskId, int $fromWorkerId,$flags=null)
    {
        // 定时任务处理逻辑
        Logger::getInstance()->log(date("Y-m-d H:i:s").'------Run per Min', 'crontab.log');//将记录日志
    }
}