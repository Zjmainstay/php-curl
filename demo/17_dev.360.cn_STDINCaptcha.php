<?php

/**
 * dev.360.cn模拟登录
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 * 访问登录页，提交登录表单，查看登录结果
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$lineBreak = $autologin->getLineBreak();

$username = urlencode('100000@qq.com');   //登录邮箱
$password = '密码加密串';                        //利用浏览器登录后，复制加密密码串填入

//0. 未登录
$getDataUrl = "curl 'http://dev.360.cn/dev/getuser?callback=onData&_=1489659410069' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Encoding: gzip, deflate' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Cache-Control: max-age=0' -H 'Connection: keep-alive' -H 'Cookie: test_cookie_enable=null' -H 'Host: dev.360.cn' -H 'Referer: http://dev.360.cn/mod/developer/?_=186613593&from=mobile' -H 'Upgrade-Insecure-Requests: 1' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0'";
echo 'Before Login: ' . $autologin->execCurl($getDataUrl) . $lineBreak;

//1. 首页
$curl1 = "curl 'http://dev.360.cn/' -H 'Host: dev.360.cn' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -H 'Cache-Control: max-age=0'";
$content = $autologin->execCurl($curl1);

//2. 获取验证码链接
$curl2 = "curl 'http://login.360.cn/?callback=jQuery19109780439789015878_1489658824142&src=pcw_open_app&from=pcw_open_app&charset=UTF-8&requestScema=http&o=sso&m=checkNeedCaptcha&account=hzgdys%40163.com&captchaApp=i360&_=1489658824143' -H 'Accept: */*' -H 'Accept-Encoding: gzip, deflate' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Connection: keep-alive' -H 'Cookie: __guid=69710341.1799766187254211600.1489658825084.8904' -H 'Host: login.360.cn' -H 'Referer: http://dev.360.cn/' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0'";
$content = $autologin->execCurl($curl2);
$json = substr(str_replace('jQuery19109780439789015878_1489658824142(', '', $content), 0, -1);
$jsonToArr = json_decode($json, true);

$captchaUrl = $jsonToArr['captchaUrl'];

//3. 读取验证码
$curl3 = "curl '{$captchaUrl}' -H 'Accept: */*' -H 'Accept-Encoding: gzip, deflate' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Connection: keep-alive' -H 'Cookie: __guid=69710341.1799766187254211600.1489658825084.8904' -H 'Host: passport.360.cn' -H 'Referer: http://dev.360.cn/' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0'";
$content = $autologin->execCurl($curl3);
$codeFile = __DIR__ . '/code.png';
file_put_contents($codeFile, $content);
echo "请输入一个验证码（ {$codeFile} ）：";
$captcha = trim(fgets(STDIN));

//4. 获取token
$curl4 = "curl 'https://login.360.cn/?func=jQuery19109780439789015878_1489658824142&src=pcw_open_app&from=pcw_open_app&charset=UTF-8&requestScema=https&o=sso&m=getToken&userName={$username}&_=1489658824144' -H 'Host: login.360.cn' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0' -H 'Accept: */*' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Referer: http://dev.360.cn/' -H 'Cookie: __guid=69710341.1799766187254211600.1489658825084.8904' -H 'Connection: keep-alive'";
$content = $autologin->execCurl($curl4);
$json = substr(str_replace('jQuery19109780439789015878_1489658824142(', '', $content), 0, -1);
$jsonToArr = json_decode($json, true);
$token = $jsonToArr['token'];

//5. 提交
$curl5 = "curl 'https://login.360.cn/' -H 'Host: login.360.cn' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Referer: http://dev.360.cn/' -H 'Cookie: __guid=69710341.1799766187254211600.1489658825084.8904' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -H 'Content-Type: application/x-www-form-urlencoded' --data 'src=pcw_open_app&from=pcw_open_app&charset=UTF-8&requestScema=https&o=sso&m=login&lm=0&captFlag=1&rtype=data&validatelm=0&isKeepAlive=1&captchaApp=i360&userName={$username}&type=normal&account={$username}&password={$password}&captcha={$captcha}&token={$token}&proxy=http%3A%2F%2Fdev.360.cn%2Fpsp_jump.html&callback=QiUserJsonp658825108&func=QiUserJsonp658825108'";
$content = $autologin->execCurl($curl5);
// var_dump($content);

//6. 回调1 login.360.cn
$curl6 = "curl 'https://login.360.cn/?func=jQuery19109780439789015878_1489658824152&src=pcw_open_app&from=pcw_open_app&charset=UTF-8&requestScema=https&o=sso&m=setcookie&s=c3k%3Ce%2CA%3Bgjhi3%3EtN-%7C+dHG*j*%22UkWCe%5Cw%7C_1rT%2BRGBZz%7Bqe%40m%60o%22s0!u)dr%3A8GOvKA_T%2B%23wA%3AgPKAJxN%2BH%7C%24aMR%5Cc_79ssF4k%3Ey+J(7!%24H1Jr%3EIbH%2F7%256s.RV%23%5Cdtc!pB%3A%3FBnh3gqq%5Dm!+T(ppp%2FS1SM%25_Jb4Rdx%3F8L%3DLyO8eyith_8F%5C-%7CCrcP*7%3BBFe%40z!hq%261%2B%3Fe%5D4am%7D)b%22A_%40%3F%3CbPjNx%26%7DFe(DPzLn%5C0z%2F&_=1489658824153' -H 'Host: login.360.cn' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0' -H 'Accept: */*' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Referer: http://dev.360.cn/' -H 'Cookie: __guid=69710341.1799766187254211600.1489658825084.8904' -H 'Connection: keep-alive'";
$content = $autologin->execCurl($curl6);

//7. 回调2 login.360.cn
$curl7 = "curl 'http://login.360.cn/?callback=jQuery19109780439789015878_1489658824154&src=pcw_open_app&from=pcw_open_app&charset=UTF-8&requestScema=http&o=sso&m=info&show_name_flag=1&head_type=b&_=1489658824160' -H 'Accept: */*' -H 'Accept-Encoding: gzip, deflate' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Connection: keep-alive' -H 'Cookie: __guid=69710341.1799766187254211600.1489658825084.8904' -H 'Host: login.360.cn' -H 'Referer: http://dev.360.cn/' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0'";
$content = $autologin->execCurl($curl7);
// var_dump($content);

//8. 登录成功，锁定cookie的更新，直接访问已登录页面内容（类似采集内容），演示cookie锁定多次采集效果与cookie失效效果
$autologin->lockLastCookieFile();
echo 'After Login 1(with_cookie): ' . $autologin->execCurl($getDataUrl) . $lineBreak;
$autologin->removeLastCookie();
echo 'After Login 2(without_cookie): ' . $autologin->execCurl($getDataUrl) . $lineBreak;
