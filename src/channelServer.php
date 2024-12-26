<?php
include __DIR__ . "/../vendor/autoload.php";



// 引入Workerman相关类
use Workerman\Worker;

//Tcp 通讯方式
$channelServer = new Channel\Server('127.0.0.1', 42206);

//Unix Domain Socket 通讯方式
//$channel_server = new Channel\Server('unix:///tmp/workerman-channel.sock');

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}