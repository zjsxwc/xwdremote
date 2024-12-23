<?php
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Request;

function index(Request $request) {
    // 创建一个响应对象
    $response = new Response();
    // 设置响应头，这里设置内容类型为text/html
    $response->withHeader('Content-Type', 'text/html');
    // 设置响应体内容，这里返回一个简单的HTML页面示例
    $response->withBody(file_get_contents(__DIR__."/index.html"));
    return $response;
}