<?php
ini_set('memory_limit', '2048M');

include __DIR__ . "/../vendor/autoload.php";

// 引入Workerman相关类
use Workerman\Worker;
use Workerman\Timer;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Request;
use Workerman\Connection\ConnectionInterface;
use Workerman\Connection\TcpConnection;
use Channel\Client;

include_once __DIR__."/parameters.php";

global $windowTitle;
global $startWindowCmd;
global $httpPort;
global $wsPort;

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
$screenshotDataPixels = null;

// 创建一个HTTP协议的Worker实例，监听8080端口
$httpWorker = new Worker('http://0.0.0.0:'.$httpPort);

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

        $imagick = new \Imagick();
        $imagick->readImageBlob($screenshotData);
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        $pixels = [];
        // 遍历图像的每一行
        for ($y = 0; $y < $height; $y++) {
            $pixels[$y] = [];
            // 遍历每行中的每个像素
            for ($x = 0; $x < $width; $x++) {
                // 获取指定位置的像素颜色信息
                $pixelColor = $imagick->getImagePixelColor($x, $y);
                $color = $pixelColor->getColor();
                $pixels[$y][$x] = [
                    $color['r'],
                    $color['g'],
                    $color['b']
                ];
            }
        }
        // 此时 $pixels 就是像素矩阵，可做后续处理
        $imagick->clear();
        $imagick->destroy();

        global $screenshotDataPixels;
        if ($screenshotDataPixels) {
            $diffData = [];
            $height1 = count($screenshotDataPixels);
            $width1 = count($screenshotDataPixels[0]);
            $height2 = count($pixels);
            $width2 = count($pixels[0]);

            for ($y = 0; $y < min($height1, $height2); $y++) {
                for ($x = 0; $x < min($width1, $width2); $x++) {
                    if ($screenshotDataPixels[$y][$x] !== $pixels[$y][$x]) {
                        $diffData[] = [
                            'x' => $x,
                            'y' => $y,
                            'newColor' => $pixels[$y][$x]
                        ];
                    }
                }
            }
            if ($diffData) {
                // 初始化 Channel 客户端
                Client::connect('127.0.0.1', 42206);
                Client::publish('diffDataChannel', json_encode($diffData));
            }
        }
        $screenshotDataPixels = $pixels;
    });

    Timer::add(10, function () {
        refreshWindowData();
    });
};

$wsWorker = new Worker("websocket://0.0.0.0:".$wsPort);
$wsWorker->connections = [];
$wsWorker->onWorkerStart = function (){
    // 初始化 Channel 客户端
    Client::connect('127.0.0.1', 42206);
    // 订阅数据通道
    Client::on('diffDataChannel', function ($jsonDifferData) {
        global $wsWorker;
        foreach ($wsWorker->connections as $wsConnection) {
            $wsConnection->send($jsonDifferData);
        }
    });
};
// WebSocket 服务的请求处理
$wsWorker->onMessage = function (TcpConnection $connection, $data) {
    // 接收 WebSocket 消息
    $connection->send(json_encode(["msg" => "Message to WebSocket server"]));

};
// 当有新的 WebSocket 连接时
$wsWorker->onConnect = function (TcpConnection  $connection) {
    global $wsWorker;
    $wsWorker->connections[] = $connection;
    $connection->send(json_encode(["msg" => "Welcome to WebSocket server"]));
};
// 当 WebSocket 连接关闭时
$wsWorker->onClose = function (TcpConnection $connection) {
    global $wsWorker;
    $index = array_search($connection, $wsWorker->connections);
    if ($index!== false) {
        unset($wsWorker->connections[$index]);
    }
};
// 运行所有的Worker
Worker::runAll();
