<?php
/**
 * 模拟登录博客园
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn/php-curl
 * @date 2016/10/14
 */

require_once __DIR__.'/../../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

//登录缓存cookie文件
$loginCookieFile = '/tmp/cnblog_login_cookie.txt';

if(file_exists($loginCookieFile)) {
    $cookie = file_get_contents($loginCookieFile);
}

$username = 'Zjmainstay';
$password = 'password';

//登录信息缓存，频繁登录会出现验证码！！
if(empty($cookie)) {
    //登录页
    $curl1 = "curl 'https://passport.cnblogs.com/user/signin?ReturnUrl=http%3A%2F%2Fwww.cnblogs.com%2F' -H 'Host: passport.cnblogs.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:48.0) Gecko/20100101 Firefox/48.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Referer: http://www.cnblogs.com/' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1'";
    $content = $autologin->execCurl($curl1);

    //获取VerificationToken
    $pattern = "#'VerificationToken': '([^']+)'#is";
    if(!preg_match($pattern, $content, $match)) {
        exit('无法解析获得登录页VerificationToken');
        //throw new Exception('无法解析获得登录页VerificationToken');
    }

    $verificationToken = $match[1];

    //基于node.js获取登录加密结果
    $content = file_get_contents("http://localhost:8111?input1={$username}&input2={$password}");
    $encryptInfo = json_decode($content, true);
    $postData = sprintf('{"input1":"%s","input2":"%s","remember":false}', $encryptInfo['input1'], $encryptInfo['input2']);
    $contentLength = strlen($postData);

    //提交登录信息
    $curl2 = "curl 'https://passport.cnblogs.com/user/signin' -X POST -H 'Host: passport.cnblogs.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:48.0) Gecko/20100101 Firefox/48.0' -H 'Accept: application/json, text/javascript, */*; q=0.01' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Content-Type: application/json; charset=utf-8' -H 'VerificationToken: {$verificationToken}' -H 'X-Requested-With: XMLHttpRequest' -H 'Referer: https://passport.cnblogs.com/user/signin?ReturnUrl=http%3A%2F%2Fwww.cnblogs.com%2F&AspxAutoDetectCookieSupport=1' -H 'Content-Length: {$contentLength}' -H 'Cookie: AspxAutoDetectCookieSupport=1; SERVERID=d0849c852e6ab8cf0cebe3fa386ea513|1476439733|1476438874' -H 'Connection: keep-alive'";
    $content = $autologin->execCurl($curl2, function($parseCurlResult) use ($postData) {
        $parseCurlResult['post'] = $postData;
        return $parseCurlResult;
    });

    $autologin->lockLastCookieFile();

    $homeUrl = 'http://i.cnblogs.com/';
    $isLogin = $autologin->assertContainStr($autologin->getUrl($homeUrl), '修改密码');

    if($isLogin) {
        echo "已登录\n";
    } else {
        exit("未登录，请检查账号密码\n");
    }

    file_put_contents($loginCookieFile, $autologin->getLastCookieContent());
}

$autologin->setLastCookieFile($loginCookieFile);

//获取我的随笔
$url = 'https://i.cnblogs.com/';
$content = $autologin->getUrl($url);

$pattern = '#<td class="post-title">\s*<a href="([^"]+)"[^>]*>(.*?)</a>#is';

if( preg_match_all($pattern, $content, $matches) ) {
    foreach($matches[0] as $key => $val) {
        echo "{$matches[2][$key]}({$matches[1][$key]})\n";
    }
}
