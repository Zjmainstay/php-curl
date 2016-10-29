<?php

/**
 * 中国知网下载
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();
$curl1 = "curl 'http://url.cn/2A2bDkA'";
$content = $autologin->execCurl($curl1, function($parseResult) {
    $parseResult['opt'][CURLOPT_HEADER] = true;
    return $parseResult;
});

preg_match('#Location: (http[^\r\n]+)#i', $content, $match);
$loginUrl = $match[1];

//http://epub.cnki.net/kns/download.aspx?filename=iF0arlHSv10crJEU1UHZklEVJJUR3gWMFdmYj1mQI10bxU1R1lFbvlzQmhVSEBVWZNXWkRXWVZlV5ZXZ1BXUyAXMwtCWJ5WSYRlVJpWc08yMYRVOVZXTMd1TuNkeUVHZSBzRZNleTZFaIZDaxg2UrBXZWF2ZwETT&tablename=CCNDPREP

$curl2 = "curl '{$loginUrl}' -H 'Host: epub.cnki.net' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:47.0) Gecko/20100101 Firefox/47.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate' -H 'Referer: http://epub.cnki.net/kns/download.aspx?filename=iF0arlHSv10crJEU1UHZklEVJJUR3gWMFdmYj1mQI10bxU1R1lFbvlzQmhVSEBVWZNXWkRXWVZlV5ZXZ1BXUyAXMwtCWJ5WSYRlVJpWc08yMYRVOVZXTMd1TuNkeUVHZSBzRZNleTZFaIZDaxg2UrBXZWF2ZwETT&tablename=CCNDPREP' -H 'Cookie: ASP.NET_SessionId=vabrf4am2gv3v445febxduqy; Ecp_IpLoginFail=16082561.51.129.138' -H 'Connection: keep-alive' -H 'Content-Type: application/x-www-form-urlencoded' --data 'username=123456%40qq.com&password=2222222'";

$content = $autologin->execCurl($curl2);

var_dump($content);
