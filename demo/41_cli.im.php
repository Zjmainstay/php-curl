<?php

/**
 * 草料图片上传二维码解析
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

//1. 初始化首页
// $curl = "curl 'https://upload.api.cli.im/upload.php?kid=cliim' -X OPTIONS -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:61.0) Gecko/20100101 Firefox/61.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2' --compressed -H 'Access-Control-Request-Method: POST' -H 'Origin: https://cli.im' -H 'Connection: keep-alive'";
// $content = $autologin->execCurl($curl);

// echo "1. " . $content . "\n";

$delimiter = '-----------------------------' . rand(1000000000, 9999999999) . rand(1000000000, 9999999999) . rand(10000000, 99999999);
$filePath = __DIR__ . '/../images/php-curl-qrcode.png';
$data = buildFileUploadData($delimiter, 'Filedata', $filePath);
$contentType = 'multipart/form-data; boundary=' . $delimiter;
$contentLength = strlen($data);

$apiUrl = 'https://upload.api.cli.im/upload.php?kid=cliim';

//2. 上传图片
$curl = "curl '{$apiUrl}' -H 'Accept: */*' --compressed -H 'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2' -H 'Connection: keep-alive' -H 'Cookie: koa.sid=6fuuKG4qGhJg2CFP0Y3Go1X92QKNjlI2; koa.sid.sig=FqGx8un0r_LBzf_60ROuaik-pPE' -H 'Origin: https://cli.im' -H 'Referer: https://cli.im/deqr' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:59.0) Gecko/20100101 Firefox/59.0' -H 'Content-Type: {$contentType}' -H 'Content-Length: {$contentLength}'";
// die(var_dump($curl));
$content = $autologin->execCurl($curl, function($parseCurlResult) use ($data) {
    $parseCurlResult['post'] = $data;
    return $parseCurlResult;
});

echo "2. " . $content . "\n";

$imgInfo = json_decode($content, true);
$imgUrl = urlencode($imgInfo['data']['path']);

//3. 解析二维码
$curl = "curl 'https://cli.im/apis/up/deqrimg' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:61.0) Gecko/20100101 Firefox/61.0' -H 'Accept: application/json, text/javascript, */*; q=0.01' -H 'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2' --compressed -H 'Referer: https://cli.im/deqr' -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' -H 'X-Requested-With: XMLHttpRequest' -H 'Cookie: _ga=GA1.2.612629640.1509329069; Hm_lvt_cb508e5fef81367bfa47f4ec313bf68c=1536997019; SERVERID=845671255dd12630e5f3f4b14406bdaa|1536997573|1536996809; PHPSESSID=57fn56uo3ca1e1pcb1aan1as71; Hm_lpvt_cb508e5fef81367bfa47f4ec313bf68c=1536997782; climanager_v2=%7B%22manager_id%22%3A%2211%22%2C%22type%22%3A%221%22%2C%22weight%22%3A%220%22%2C%22nickname%22%3A%22%u5C0F%u8D75%22%2C%22realname%22%3A%22%u8D75%u8FD0%22%2C%22star_text%22%3A%22%u4E13%u5C5E%u987E%u95EE%22%2C%22vcard_pic%22%3A%22https%3A//static.clewm.net/cli/images/contact/kefu-default@2x.png%22%2C%22logo_pic%22%3A%22https%3A//static.clewm.net/cli/images/contact/logo-zy@2x.png%22%2C%22telphone%22%3A%220574-55330927%22%2C%22qq%22%3A%222852371507%22%2C%22contact_link%22%3A%22https%3A//q.url.cn/s/mjzkTcm%22%2C%22group_link%22%3A%22//q.url.cn/s/wGb3v6m%22%2C%22complete%22%3A0%7D; text-content-words=33; qrs=157961239; _gat=1' -H 'Connection: keep-alive' --data 'img={$imgUrl}'";
$content = $autologin->execCurl($curl);

echo "3. " . $content . "\n";

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
