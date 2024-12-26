<?php
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Request;

function index(Request $request) {
    global $validCredentials;

    // 获取请求头中的 Authorization 信息
    $auth_header = $request->header('Authorization');
    if (!$auth_header) {
        // 发送 401 Unauthorized 响应，并要求客户端进行身份验证
        $response = new Response(401);
        $response->withHeader('WWW-Authenticate', 'Basic realm="Restricted Area"');
        return $response;
    }

    // 解析 Authorization 信息
    list($auth_type, $auth_data) = explode(' ', $auth_header);
    if ($auth_type === 'Basic') {
        $decoded = base64_decode($auth_data);
        list($username, $password) = explode(':', $decoded);
        // 验证用户名和密码
        if (isset($validCredentials[$username]) && $validCredentials[$username] === $password) {
            // 身份验证成功，继续处理请求
            goto INDEX;
        } else {
            // 身份验证失败，发送 403 Forbidden 响应
            return new Response(403);
        }
    } else {
        // 不支持的身份验证类型，发送 400 Bad Request 响应
        return new Response(400);
    }

    INDEX:
    // 创建一个响应对象
    $response = new Response();
    // 设置响应头，这里设置内容类型为text/html
    $response->withHeader('Content-Type', 'text/html');
    // 设置响应体内容，这里返回一个简单的HTML页面示例
    global $wsPort;
    $html = file_get_contents(__DIR__."/index.html");
    $html = str_replace("WS_PORT", $wsPort, $html);
    $response->withBody($html);
    return $response;
}