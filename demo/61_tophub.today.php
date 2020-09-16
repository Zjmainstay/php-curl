<?php

/**
 * tophub.today内容抓取
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 * 访问登录页，提交登录表单，查看登录结果
 */

require_once __DIR__.'/../src/CurlAutoLogin.php';

$autologin = new PHPCurl\CurlAutoLogin();

//1. 初始化登录页
$curl = <<<CURL
curl 'https://tophub.today/n/NaEdZ2ndrO' \
  -H 'authority: tophub.today' \
  -H 'cache-control: max-age=0' \
  -H 'upgrade-insecure-requests: 1' \
  -H 'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36' \
  -H 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9' \
  -H 'sec-fetch-site: none' \
  -H 'sec-fetch-mode: navigate' \
  -H 'sec-fetch-user: ?1' \
  -H 'sec-fetch-dest: document' \
  -H 'accept-language: zh-CN,zh;q=0.9,en;q=0.8' \
  --compressed
CURL;
$content = $autologin->execCurl($curl);

preg_match_all('#<a href="([^"]+)"[^>]*itemid="(\d+)"#i', $content, $matches);

// 这里做测试，只抓取一个地址
foreach($matches[0] as $key => $value) {
    $url = $matches[1][$key];
    $itemid = $matches[2][$key];

    break;
}
// var_dump($url, $itemid);

$curl = <<<CURL
curl 'https://tophub.today{$url}' \
  -H 'authority: tophub.today' \
  -H 'upgrade-insecure-requests: 1' \
  -H 'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36' \
  -H 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9' \
  -H 'sec-fetch-site: same-origin' \
  -H 'sec-fetch-mode: navigate' \
  -H 'sec-fetch-user: ?1' \
  -H 'sec-fetch-dest: document' \
  -H 'referer: https://tophub.today/n/NaEdZ2ndrO' \
  -H 'accept-language: zh-CN,zh;q=0.9,en;q=0.8' \
  -H 'cookie: UM_distinctid=17495af4fca60f-0a34a4fc1d66b7-316b7002-1fa400-17495af4fcb784; CNZZDATA1276310587=1727048654-1600235131-%7C1600235131; Hm_lvt_3b1e939f6e789219d8629de8a519eab9=1600239129; Hm_lpvt_3b1e939f6e789219d8629de8a519eab9=1600239129' \
  --compressed
CURL;
$content = $autologin->execCurl($curl);
var_dump($content);


