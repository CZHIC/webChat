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


    /**
     * 数组 转 对象
     *
     * @param array $arr 数组
     * @return object
     */
    public static function  array_to_object($arr) {
        if (gettype($arr) != 'array') {
            return;
        }
        foreach ($arr as $k => $v) {
            if (gettype($v) == 'array' || getType($v) == 'object') {
                $arr[$k] = ModelFuc::array_to_object($v);
            }
        }
     
        return (object)$arr;
    }
     
    /**
     * 对象 转 数组
     *
     * @param object $obj 对象
     * @return array
     */
    public static function  object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = ModelFuc::object_to_array($v);
            }
        }
     
        return $obj;
    }
}
