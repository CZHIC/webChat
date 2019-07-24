<?php
/**
 * 异步task任务
 * PHP version 7
 * swoole4.3
 *
 * @category Null
 * @package  Null
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
namespace App\Utility;
use \EasySwoole\EasySwoole\Logger;
/**
 * 异步taskr任务
 *
 * @category Null
 * @package  Null
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
class AsyncTask extends \EasySwoole\EasySwoole\Swoole\Task\AbstractAsyncTask
{

    /**
     * 执行任务的内容
     *
     * @param mixed  $taskData     任务数据
     * @param int    $taskId       执行任务的task编号
     * @param int    $fromWorkerId 派发任务的worker进程号
     * @param string $flags        自定义参数
     *
     * @return array
     */
    function run($taskData, $taskId, $fromWorkerId, $flags = null)
    {
        // 需要注意的是task编号并不是绝对唯一
        // 每个worker进程的编号都是从0开始
        // 所以 $fromWorkerId + $taskId 才是绝对唯一的编号
        // !!! 任务完成需要 return 结果
        if ($taskData['act'] == 'test') {
             Logger::getInstance()->log('测试内容--1', 'chu1233.log');//将记录日志
             sleep(5);
             $pid = posix_getpid(); 
             $str = 'pid= '.$pid.'----workerid = '.$fromWorkerId .'----taskid=' . $taskId;
             Logger::getInstance()->log($str, 'chu1233.log');//将记录日志
             echo $str."\n";
             return $str;
        } else {
            return true;
        }
    }

    /**
     * 任务执行完的回调
     *
     * @param mixed $result  任务执行完成返回的结果
     * @param int   $task_id 执行任务的task编号
     *
     * @return : array
     */
    function finish($result, $task_id)
    {
        // 任务执行完的处理
    }
}
