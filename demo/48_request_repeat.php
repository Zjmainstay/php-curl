<?php

/**
 * 简单的模拟登录示例
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 * 访问登录页，提交登录表单，查看登录结果
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$lineBreak = $autologin->getLineBreak();

//获取登录态（失败）
$curl = "curl 'http://demo.zjmainstay.cn/js/simpleAjax/loginResult.php'";
$content = $autologin->execCurl($curl);
echo 'Before Login: ' . $content . $lineBreak;

//登录
$username = 'Zjmainstay';
$curl = "curl 'http://demo.zjmainstay.cn/js/simpleAjax/doPost.php' -H 'Host: demo.zjmainstay.cn' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:47.0) Gecko/20100101 Firefox/47.0' -H 'Accept: application/json, text/javascript, */*; q=0.01' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate' -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' -H 'X-Requested-With: XMLHttpRequest' -H 'Referer: http://demo.zjmainstay.cn/js/simpleAjax/' -H 'Cookie: Hm_lvt_1526d5aecf5561ef9401f7c7b7842a97=1468327822,1468327904,1468341636,1468411918; Hm_lpvt_1526d5aecf5561ef9401f7c7b7842a97=1468421526' -H 'Connection: keep-alive' --data 'username=[username]'";
$curl = str_replace('[username]', $username, $curl);
$autologin->execCurl($curl, false, false, false);   //登录不需要记录最后请求参数

//重放获取登录态
$content = $autologin->repeatRequest();
echo 'After Login: ' . $content . $lineBreak;
