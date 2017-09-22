<?php

/**
 * publish.haodanku.com模拟登录
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 * 访问登录页，提交登录表单，查看登录结果
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$lineBreak = $autologin->getLineBreak();

$phone = '手机号';   //手机号
$password = '密码';    //密码

$getDataUrl = "curl 'http://publish.haodanku.com/index/index.html'";
echo 'Before Login: ' . isLogin($autologin->execCurl($getDataUrl)) . $lineBreak;

//1. 首页
$curl = "curl 'http://publish.haodanku.com/login/index.html' -H 'Host: publish.haodanku.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:55.0) Gecko/20100101 Firefox/55.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' --compressed -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -H 'Cache-Control: max-age=0'";
$content = $autologin->execCurl($curl);

//2. 读取验证码
$curl = "curl 'http://publish.haodanku.com/authcode/index?t=?0.94427897345314' -H 'Host: publish.haodanku.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:55.0) Gecko/20100101 Firefox/55.0' -H 'Accept: */*' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' --compressed -H 'Referer: http://publish.haodanku.com/login/index.html' -H 'Cookie: PHPSESSID=gle0jjtlli5r55gk4mru9rsq61; __root_domain_v=.haodanku.com; _qddaz=QD.2oyyj7.cf30mz.j7vjw8i7; _qdda=3-1.91z3j; _qddab=3-x0k9tx.j7vjw8wd; _qddamta_2852060675=3-0; UM_distinctid=15ea870b69a479-02f04379124def8-49546e-13c680-15ea870b69b28a' -H 'Connection: keep-alive'";
$content = $autologin->execCurl($curl);
$codeFile = __DIR__ . '/code.png';
file_put_contents($codeFile, $content);
echo "请输入一个验证码（ {$codeFile} ）：";
$captcha = trim(fgets(STDIN));

//3. 提交
$curl = "curl 'http://publish.haodanku.com/login' -H 'Host: publish.haodanku.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:55.0) Gecko/20100101 Firefox/55.0' -H 'Accept: */*' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' --compressed -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' -H 'X-Requested-With: XMLHttpRequest' -H 'Referer: http://publish.haodanku.com/login/index.html' -H 'Cookie: PHPSESSID=gle0jjtlli5r55gk4mru9rsq61' -H 'Connection: keep-alive' --data 'phone={$phone}&password={$password}&authcode={$captcha}'";
$content = $autologin->execCurl($curl);

//4. 登录成功，锁定cookie的更新，直接访问已登录页面内容（类似采集内容），演示cookie锁定多次采集效果与cookie失效效果
$autologin->lockLastCookieFile();
echo 'After Login: ' . isLogin($autologin->execCurl($getDataUrl)) . $lineBreak;

function isLogin($content) {
    return (bool)stripos($content, '个人主页') ? 'Yes' : 'No';
}
