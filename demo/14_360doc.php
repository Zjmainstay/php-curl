<?php

/**
 * 模拟登录360doc
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 * 访问登录页，提交登录表单，查看登录结果
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

//1. 请求首页
$curl1 = "curl 'http://www.360doc.com/' -H 'Host: www.360doc.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:50.0) Gecko/20100101 Firefox/50.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -H 'If-Modified-Since: Fri, 02 Dec 2016 02:16:59 GMT' -H 'Cache-Control: max-age=0'";
$autologin->execCurl($curl1);

//2. 依赖cookie获取
$curl2 = "curl 'http://www.360doc.com/clippertool/getnoteclipperASHX.ashx?type=10&jsoncallback=jsonp1480652930568' -H 'Host: www.360doc.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:50.0) Gecko/20100101 Firefox/50.0' -H 'Accept: */*' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate' -H 'Referer: http://www.360doc.com/' -H 'Connection: keep-alive' -H 'Pragma: no-cache' -H 'Cache-Control: no-cache'";
$autologin->execCurl($curl2);

//3. 提交登录表单
$curl3 = "curl 'http://www.360doc.com/ajax/login/login.ashx?email=100000000@qq.com&pws=2b6b12e433588471822eac2b6e8004b2&isr=0&login=1' -X POST -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Encoding: gzip, deflate' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Connection: keep-alive' -H 'Content-Length: 0' -H 'Content-Type: text/xml' -H 'Cookie: 360docsn=Q8PM6PS1DGHCOHWL; Hm_lvt_d86954201130d615136257dde062a503=1479284689,1480563198,1480563274; Hm_lpvt_d86954201130d615136257dde062a503=1480644777' -H 'Host: www.360doc.com' -H 'Referer: http://www.360doc.com/' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:50.0) Gecko/20100101 Firefox/50.0'";
$content = $autologin->execCurl($curl3);

$autologin->lockLastCookieFile();
echo $autologin->getUrl('http://www.360doc.com/mymsg.aspx');
