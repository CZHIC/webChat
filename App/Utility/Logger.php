<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/3/18 0018
 * Time: 14:56
 */

namespace App\Utility;

use EasySwoole\Trace\AbstractInterface\LoggerInterface;

class Logger implements LoggerInterface
{
    /**
     * 打印到控制台并记录日志
     *
     * @param string $str      日志内容
     * @param null   $category 呵呵
     * @param bool   $saveLog  保存
     *
     * @return string|null
     *
     * @author CZC
     * Time: 14:57
     */
    public function console(string $str, $category = null, $saveLog = true): ?string
    {
        echo $str;
        return $str;
    }

    /**
     * 自定义进行日志存储,比如存到数据库,存到文件,或者请求其他地方存储
     *
     * @param mix      $params    日志内容
     * @param null     $file      文件名
     * @param int|null $timestamp 时间
     * @param int      $fsize     文件大小基数
     *
     * @return string|null
     *
     * @author CZC
     * Time: 14:56
     */
    public function log($params, $file = null, int $timestamp = null, $fsize = 1): ?string
    {
        if ($file ==null) {
            $file = 'test.log';
        }

        if (!is_scalar($params)) {
            $params = var_export($params, true);
        }
        if (!$params) {
            return false;
        }
        $path = '/home/wwwroot/swoole/easyswoole/Log/'.$file;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $maxSize = max(1, $fsize) * 1024 * 1024;
        $size = file_exists($file) ? @filesize($file) : 0;
        if ($size > $maxSize) {
            file_put_contents($path, $params.PHP_EOL);
        } else {
            file_put_contents($path, $params.PHP_EOL, FILE_APPEND);
        }
        return getcwd().'/'.$file;//必须返回字符串回去
    }
}
