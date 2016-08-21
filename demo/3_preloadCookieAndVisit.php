<?php

/**
 * 模拟微信访问一个外部链接
 *
 * 由于微信需要扫码登录，目前没有对微信扫码登录做研究，
 * 而其登录成功后，一段时间内的cookie信息可重复使用，
 * 因此，先利用浏览器进行登录，然后复制登录成功后，
 * 访问相应链接的curl命令，引用其中的cookie进行采集
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$curl1 = "curl 'http://m.5read.com/523'";
$content = $autologin->execCurl($curl1);

$curl2 = "curl 'http://book.m.5read.com/search?sw=php&channel=search&Field=all&Sort=3&page=1&ecode=UTF-8'";
$content = $autologin->execCurl($curl2);


// file_put_contents('/tmp/a.html', $content);
$content = file_get_contents('/tmp/a.html');

$pattern = '#(作者:.+)#';

preg_match_all($pattern, $content, $matches);

$itemPattern = '#([^:]+:.*?)(?:&nbsp;)+#';
foreach($matches[1] as $author) {
    $author = preg_replace('#(\d{4}\.\d{2}&nbsp;&nbsp;).+#i', '$1', $author);
    preg_match_all($itemPattern, $author, $itemMatches);
    var_dump($itemMatches[1]);
}


