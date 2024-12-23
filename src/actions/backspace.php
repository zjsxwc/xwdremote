<?php
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Request;

function backspace(Request $request) {
    exec("xdotool key BackSpace");

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