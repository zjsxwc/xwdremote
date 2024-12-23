<?php
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Request;

function mousemoveLeftDoubleClick(Request $request) {
    $postData = $request->post();
    $x = intval($postData["x"]);
    $y = intval($postData["y"]);

    global $absoluteUpperLeftX;
    global $absoluteUpperLeftY;

    $absX = $absoluteUpperLeftX + $x;
    $absY = $absoluteUpperLeftY + $y;

    exec("xdotool mousemove {$absX} {$absY}");
    exec("sleep 0.3");
    exec("xdotool click --repeat 2 1");

    $response = new Response();
    // 设置Content - Type为application/json
    $response->withHeader('Content-Type', 'application/json');
    $data = [
        'code' => '-1',
        'msg' => 'ok'
    ];
    // 将数据转换为JSON格式并作为响应体发送
    $response->withBody(json_encode($data));
    return $response;
}