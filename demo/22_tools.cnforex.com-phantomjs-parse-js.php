<?php

/**
 * 利用phantomjs处理js并访问js调整页面
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$lineBreak = $autologin->getLineBreak();

//初始化获得js内容
$getUrl = "curl 'http://tools.cnforex.com/hangqing/app/app.html'";
$content = $autologin->execCurl($getUrl);

$jsPattern = '#window.onload=.*}#is';
preg_match($jsPattern, $content, $match);
$js = $match[0];

//替换js内容，打印目标数据，加入phantom退出方法
$js = str_replace('eval("qo=eval;qo(po);");', 'console.log(po);phantom.exit();', $js);

//解析js
$file = '/tmp/tools.cnforex.com.js';
file_put_contents($file, $js);
$result = `/usr/bin/phantomjs $file`;
echo $result;
unlink($file);

//获取页面访问cookie
$pattern = '#_ydclearance=([^;]+)#is';
preg_match($pattern, $result, $match);
$_ydclearance = $match[1];

//携带cookie请求
$content = $autologin->execCurl($getUrl, function($parseCurlResult) use ($_ydclearance) {
    $parseCurlResult['header'][] = "Cookie: _ydclearance={$_ydclearance}";
    return $parseCurlResult;
});

echo $content;
