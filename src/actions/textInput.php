<?php
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Request;

function textInput(Request $request) {
    $postData = $request->post();
    $x = intval($postData["x"]);
    $y = intval($postData["y"]);
    $text = $postData["text"];
    $tempFlieName = __DIR__ . "/../../cache/xsel_cache_".time().rand(100000,99999999).".txt";
    file_put_contents($tempFlieName, $text);

    global $absoluteUpperLeftX;
    global $absoluteUpperLeftY;

    $absX = $absoluteUpperLeftX + $x;
    $absY = $absoluteUpperLeftY + $y;

    exec("xsel -ib < " . $tempFlieName);
    @unlink($tempFlieName);
    exec("xdotool mousemove {$absX} {$absY}");
    exec("sleep 0.3");
    exec("xdotool click 1");
    exec("sleep 0.3");
    exec("xsel -ob | xdotool type --clearmodifiers --file -");


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