<?php

include __DIR__ . "/../vendor/autoload.php";

// 引入Workerman相关类
use Workerman\Worker;
use Workerman\Timer;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Request;
use Workerman\Connection\ConnectionInterface;


$windowTitle = "Wine Desktop";
$displayId = "0";//if swith logined user on linux, the display id of X server will change.
$absoluteUpperLeftX = null;
$absoluteUpperLeftY = null;
$decNumString = null;

refreshWindowData();

function refreshWindowData() {
    global $windowTitle;
    global $absoluteUpperLeftX;
    global $absoluteUpperLeftY;
    global $decNumString;

    $shellout = shell_exec('xwininfo -name "'.$windowTitle.'"');

    $origShellout = explode("\n",$shellout);
//获取图形界面程序的进程id
    $shellout = trim($origShellout[1]);
    $shellout = explode("Window id:", $shellout);
    $shellout = trim($shellout[1]);
    $shellout = explode('"'.$windowTitle.'"', $shellout);
    $shellout = $shellout[0];
    $hexString = $shellout;
    $hexNumString = explode("0x", $hexString);
    $hexNumString = $hexNumString[1];
    $decNumString = hexdec($hexNumString);

//获取图形界面程序的左上角的x y像素位置的绝对值
    $shellout = trim($origShellout[3]);
    $shellout = explode("Absolute upper-left X:", $shellout);
    $absoluteUpperLeftX = intval(trim($shellout[1]));
    $shellout = trim($origShellout[4]);
    $shellout = explode("Absolute upper-left Y:", $shellout);
    $absoluteUpperLeftY = intval(trim($shellout[1]));

}


//全局屏幕截图共享变量
$screenshotData = null;

// 创建一个HTTP协议的Worker实例，监听8080端口
$httpWorker = new Worker('http://0.0.0.0:58081');

//加载接口action文件
$actionFiles = glob(__DIR__."/actions/*.php");
$actionNameList = [];
foreach ($actionFiles as $actionFile) {
    include $actionFile;
    $basename = pathinfo($actionFile)['basename'];
    $extname = "." . pathinfo($actionFile)['extension'];
    $actionNameList[] = explode($extname, $basename)[0];
}

// 当接收到HTTP请求时触发的回调函数
$httpWorker->onMessage = function (ConnectionInterface $connection,Request $request) use($actionNameList) {
    $action = $request->get("action");
    if (!$action) {
        $action = "index";
    }
    if (!in_array($action, $actionNameList)) {
        $action = "index";
    }
    $connection->send($action($request));
};

$httpWorker->onWorkerStart = function ($worker) {
    Timer::add(0.2, function () {
        global $screenshotData;
        global $decNumString;
        global $displayId;
        $screenshotData = shell_exec("xwd -display :". $displayId  .".0  -id {$decNumString} | xwdtopnm | pnmtojpeg");
    });

    Timer::add(10, function () {
        refreshWindowData();
    });
};

// 运行所有的Worker
Worker::runAll();
