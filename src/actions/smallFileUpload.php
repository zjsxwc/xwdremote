<?php
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Request;

function smallFileUpload(Request $request) {
    $response = null;
    $msg = null;
    // 检查是否有文件上传
    if ($request->file('file')) {
        $file = $request->file('file');
        // 获取上传文件的临时文件名
        $tmpName = $file['tmp_name'];
        // 获取上传文件的原始文件名
        $name = $file['name'];
        // 获取上传文件的类型
        $type = $file['type'];
        // 获取上传文件的大小
        $size = $file['size'];

        $data = file_get_contents($tmpName);

        // 可以将文件移动到指定的目录
        $destination = __DIR__. '/../../smallFileUpload/'. $name;
//        if (move_uploaded_file($tmpName, $destination)) { //fixme不知道为什么我这边直接用move_uploaded_file失败来，只能用file_put_contents将就用一下
        if (file_put_contents($destination, $data)) {
            @unlink($tmpName);
            $msg = "File uploaded successfully.";
            $response = new Response(200);
        } else {
            $msg = "File upload failed.";
            $response = new Response(400);
        }
    } else {
        $msg = "No file uploaded.";
        $response = new Response(400);
    }

    // 设置Content - Type为application/json
    $response->withHeader('Content-Type', 'application/json');
    $data = [
        'code' => '-1',
        'msg' => $msg
    ];
    // 将数据转换为JSON格式并作为响应体发送
    $response->withBody(json_encode($data));
    return $response;
}