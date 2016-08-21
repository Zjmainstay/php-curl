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

//方法一 利用execCurl前置回调添加cookie到header中
$autologin = new PHPCurl\CurlAutoLogin();
//复制浏览器登录成功后，访问https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxcheckurl?requrl=http%3A%2F%2Fwww.zjmainstay.cn的curl命令
$curl = "curl 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxcheckurl?requrl=http%3A%2F%2Fwww.zjmainstay.cn' -H 'Host: wx.qq.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:47.0) Gecko/20100101 Firefox/46.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Cookie: pgv_info=ssid=s7821947904; pgv_pvid=7479620263 ...' -H 'Connection: keep-alive' -H 'Cache-Control: max-age=0'";

$content = $autologin->execCurl($curl, function($parseCurlResult) {
    $parseCurlResult['header'][] = $parseCurlResult['cookie'];
    return $parseCurlResult;
});
$res = $autologin->assertContainStr($content, '请不要在该网页上填写QQ账号及密码');
if($res) {
    echo "Yes\n";
} else {
    echo "No\n";
}



//方法二 直接调用parseCurl对curl命令进行解析，并组装包含cookie的header头进行请求
$autologin = new PHPCurl\CurlAutoLogin();
//复制浏览器登录成功后，访问 https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxcheckurl?requrl=http%3A%2F%2Fwww.zjmainstay.cn 的curl命令
$curl = "curl 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxcheckurl?requrl=http%3A%2F%2Fwww.zjmainstay.cn' -H 'Host: wx.qq.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:47.0) Gecko/20100101 Firefox/46.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Cookie: pgv_info=ssid=s7821947904; pgv_pvid=7479620263 ...' -H 'Connection: keep-alive' -H 'Cache-Control: max-age=0'";
$url = 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxcheckurl?requrl=http%3A%2F%2Fwww.zjmainstay.cn';
//仅用来解析头
$curlInfo = $autologin->parseCurl($curl);
$header = array_merge((array)$curlInfo['header'], array($curlInfo['cookie']));
$content = $autologin->getUrl($url, $header);
$res = $autologin->assertContainStr($content, '请不要在该网页上填写QQ账号及密码');
if($res) {
    echo "Yes\n";
} else {
    echo "No\n";
}
