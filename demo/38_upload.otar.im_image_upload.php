<?php

/**
 * 图床图片上传
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 * 访问登录页，提交登录表单，查看登录结果
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

//1. 初始化首页
$curl = "curl 'http://upload.otar.im/' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' --compressed -H 'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2' -H 'Connection: keep-alive' -H 'Cookie: koa.sid=B9o5hBv6KJFiUFd1I-7aGfWMN8zgWAKT; koa.sid.sig=l3zXA2tsp_BIRgIBA5EGaajM3oc' -H 'Host: upload.otar.im' -H 'Upgrade-Insecure-Requests: 1' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:59.0) Gecko/20100101 Firefox/59.0'";
$content = $autologin->execCurl($curl);

if(!preg_match('#"x-csrf-token","([^"]+)"#i', $content, $match)) {
    exit("无法获取x-csrf-token，请检查数据源，url： http://upload.otar.im \n");
}
$csrfToken = $match[1];

$delimiter = '-----------------------------' . rand(1000000000, 9999999999) . rand(1000000000, 9999999999) . rand(10000000, 99999999);
$filePath = __DIR__ . '/../images/get-curl-text.png';
$data = buildFileUploadData($delimiter, 'img', $filePath);
$contentType = 'multipart/form-data; boundary=' . $delimiter;
$contentLength = strlen($data);

// $apiUrl = 'http://upload.otar.im/api/upload/sina';
// $apiUrl = 'http://upload.otar.im/api/upload/smms';
$apiUrl = 'http://upload.otar.im/api/upload/imgur';

$curl = "curl '{$apiUrl}' -H 'Accept: */*' --compressed -H 'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2' -H 'Connection: keep-alive' -H 'Cookie: koa.sid=6fuuKG4qGhJg2CFP0Y3Go1X92QKNjlI2; koa.sid.sig=FqGx8un0r_LBzf_60ROuaik-pPE' -H 'Host: upload.otar.im' -H 'Referer: http://upload.otar.im/' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:59.0) Gecko/20100101 Firefox/59.0' -H 'x-csrf-token: {$csrfToken}' -H 'Content-Type: {$contentType}' -H 'Content-Length: {$contentLength}'";
// die(var_dump($curl));
$content = $autologin->execCurl($curl, function($parseCurlResult) use ($data) {
    $parseCurlResult['post'] = $data;
    return $parseCurlResult;
});

echo $content;

/**
 * 构建文件上传数据
 * @param  [type] $delimiter [description]
 * @param  [type] $inputName [description]
 * @param  [type] $file      [description]
 * @return [type]            [description]
 */
function buildFileUploadData($delimiter, $inputName, $file) {
    $data = '';
    $data .= "--" . $delimiter . "\r\n";
    $data .= 'Content-Disposition: form-data; name="' . $inputName . '";' .
            ' filename="' . basename($file) . '"' . "\r\n";
    $data .= 'Content-Type: ' . mime_content_type($file) . "\r\n";
    $data .= "\r\n";
    $data .= file_get_contents($file) . "\r\n";
    $data .= "--" . $delimiter . "--\r\n";

    return $data;
}
