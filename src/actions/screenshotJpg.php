<?php
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Request;

function screenshotJpg(Request $request) {
    global $screenshotData;
    $response = new Response();
    $response->withHeader('Content-Type', 'image/jpeg');
    $response->withBody($screenshotData);
    return $response;
}