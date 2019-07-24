<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/3/18 0018
 * Time: 9:40
 */

namespace App\HttpController;

use App\Utility\TrackerManager;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Trace\Bean\Tracker;

class Index extends Base
{
    function index()
    {
        $pid = posix_getpid();
        $this->writeJson(200, ['data'=>array('pid'=>$pid)], 'success wa jajajaj'); 
    }
}
