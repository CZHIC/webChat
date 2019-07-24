<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/26
 * Time: 7:23 PM
 */

namespace App\Utility\Pool;


use EasySwoole\Component\Pool\AbstractPool;
use EasySwoole\EasySwoole\Config;


class MemcachedPool extends AbstractPool
{
    protected function createObject()
    {
        // TODO: Implement createObject() method.
        $memcached = new MemcachedObject();
        $conf = Config::getInstance()->getConf('MEMCACHED');
        if( $memcached->connect($conf['host'],$conf['port'])){
            // if(!empty($conf['auth'])){
            //     $memcached->auth($conf['auth']);
            // }
            return $memcached;
        }else{
            return null;
        }
    }
}