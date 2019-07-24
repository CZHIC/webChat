<?php
/**
 * 公共类管理--单例模式
 * PHP version 7
 * swoole4.3
 *
 * @category Null
 * @package  Null
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */


namespace App\model;
use EasySwoole\Component\Di;   // 单例


/**
 * 单例管理
 *
 * @category Null
 * @package  OO
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
class OO
{
    
    /**
     * 注册单例
     *
     * @return : array
     */
    public  function run()
    {
        Di::getInstance()->set('User', \App\model\ModelUser::class);
        Di::getInstance()->set('Fuc', \App\model\ModelFuc::class);
    }
}



