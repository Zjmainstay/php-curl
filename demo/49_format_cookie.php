<?php

/**
 * cookie格式化与持久化示例
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin('', true);
$curl = <<<CURL
curl 'http://demo.zjmainstay.cn/js/simpleAjax/loginResult.php' \
  -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' \
  -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8' \
  -H 'Cache-Control: no-cache' \
  -H 'Cookie: userIdentify=C96BEE3F333F8A676424211E19F15E06; Hm_lvt_2778aed5b668aa63a50ae9c5acd178f8=1699413729,1701801627; Hm_lpvt_2778aed5b668aa63a50ae9c5acd178f8=1701943275; Hm_lvt_1526d5aecf5561ef9401f7c7b7842a97=1702518307; Hm_lpvt_1526d5aecf5561ef9401f7c7b7842a97=1702518307; PHPSESSID=erpvlhe02137vdtvmff9ltgfu4' \
  -H 'Pragma: no-cache' \
  -H 'Proxy-Connection: keep-alive' \
  -H 'Upgrade-Insecure-Requests: 1' \
  -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36' \
  --compressed \
  --insecure
CURL;
$parseResult = $autologin->parseCurl($curl);
$cookieForFile = $autologin->formatHeaderCookieToFileContent($parseResult['cookie'], '.zjmainstay.cn');
$autologin->appendCookieContent($cookieForFile, true);
$content = $autologin->execCurl($curl);
var_dump($content, $autologin->getLastCookieContent());
