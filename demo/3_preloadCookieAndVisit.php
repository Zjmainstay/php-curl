<?php

/**
 * 预加载cookie，然后访问其他页面
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 *
 * 解决页面cookie依赖问题
 * 没访问 http://m.5read.com/523 就直接访问 http://book.m.5read.com/search?sw=php&channel=search&Field=all&Sort=3&page=1&ecode=UTF-8 会提示错误
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$curl1 = "curl 'http://m.5read.com/523'";
$content = $autologin->execCurl($curl1);

$curl2 = "curl 'http://book.m.5read.com/search?sw=php&channel=search&Field=all&Sort=3&page=1&ecode=UTF-8'";
$content = $autologin->execCurl($curl2);

$pattern = '#(作者:.+)#';

preg_match_all($pattern, $content, $matches);

$itemPattern = '#([^:]+:.*?)(?:&nbsp;)+#';
foreach($matches[1] as $author) {
    $author = preg_replace('#(\d{4}\.\d{2}&nbsp;&nbsp;).+#i', '$1', $author);
    preg_match_all($itemPattern, $author, $itemMatches);
    var_dump($itemMatches[1]);
}


