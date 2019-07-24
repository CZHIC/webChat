<?php
/**
 * 公共方法
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

/**
 * 公共方法
 *
 * @category Null
 * @package  User
 * @author   Display NAme <chuzhichao@yiihua.com>
 * @license  www.yiihua.com chuzhchao
 * @link     www.yiihua.com
 */
class ModelFuc
{
    
    /**
     * 安全性检测.调用escape存入的,一定要调unescape取出
     *
     * @param string $string 字符串 
     *
     * @return : array
     */
    public static function escape($string)
    {
        return (PHP_VERSION > '5.3.0' ) ? addslashes(trim($string)) : mysql_escape_string(trim($string));
    }
}
