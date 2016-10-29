<?php
/**
 * 模拟登录知乎
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn/php-curl
 * @date 2016/10/14
 */

require_once __DIR__.'/../vendor/autoload.php';

//知乎模拟登录

$autologin = new PHPCurl\CurlAutoLogin();

//登录缓存cookie文件
$loginCookieFile = '/tmp/zhihu_login_cookie.txt';

if(file_exists($loginCookieFile)) {
    $cookie = file_get_contents($loginCookieFile);
}

$email = 'user_email@email.com';
$password = 'password';

//登录信息缓存，知乎频繁登录会出现验证码！！
if(empty($cookie)) {
    $curl1 = "curl 'https://www.zhihu.com/' -H 'Host: www.zhihu.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:47.0) Gecko/20100101 Firefox/47.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Connection: keep-alive'";
    $content = $autologin->execCurl($curl1);

    //偶然发现csrf没有用，所以直接赋值了useless
    $email = urlencode($email);
    $curl2 = "curl 'https://www.zhihu.com/login/email' -H 'Accept: */*' -H 'Accept-Encoding: gzip, deflate, br' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Connection: keep-alive' -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' -H 'Cookie: q_c1=0daf021c52de4ef3b6a529ff4f1a6811|1470651590000|1470651590000; _xsrf=4e599680f57fee89a4dd1aebe0e38128; l_cap_id=\"NzFlNjRmZGE3ODJhNDJkMGI1NDc2YzQxYjliZDkwZTE=|1470651590|9d2ff3f78f14a0e32c7938f1d86023736b25a5bd\"; cap_id=\"OWFjNDkzYWYxNmZhNDBjN2JlZWJjNDhiNGUxYTJjNWI=|1470651590|652577038670a9b544e91d6cbcb5a355dfc29eee\"; n_c=1; d_c0=\"ABCAsUV-WgqPTnNCtsTsgzk-RdfrxZVFb8g=|1470651591\"; __utma=51854390.248018333.1470651592.1470651592.1470651592.1; __utmb=51854390.2.10.1470651592; __utmc=51854390; __utmz=51854390.1470651592.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __utmv=51854390.000--|3=entry_date=20160808=1; __utmt=1; _za=ee1a9bde-d629-4f05-b670-7adb1ee17a5f; _zap=64a770d4-5a33-4744-9602-4608f0f6f551' -H 'Host: www.zhihu.com' -H 'Referer: https://www.zhihu.com/' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:47.0) Gecko/20100101 Firefox/47.0' -H 'X-Requested-With: XMLHttpRequest' -H 'X-Xsrftoken: 4e599680f57fee89a4dd1aebe0e38128' --data '_xsrf=useless&password={$password}&captcha_type=cn&remember_me=true&email={$email}'";
    $content = $autologin->execCurl($curl2);

    $autologin->lockLastCookieFile();

    $homeUrl = 'https://www.zhihu.com/';
    $isLogin = $autologin->assertContainStr($autologin->getUrl($homeUrl), '退出');

    if($isLogin) {
        echo "已登录";
    } else {
        exit('未登录，请检查账号密码');
    }

    file_put_contents($loginCookieFile, $autologin->getLastCookieContent());
}

$autologin->setLastCookieFile($loginCookieFile);

//获取我的收藏（未做登录缓存过期判断处理）
$url = 'https://www.zhihu.com/collections';
$content = $autologin->getUrl($url);

$pattern = '#<h2[^>]*?zm-item-title[^>]*?>\s*<a href="([^"]+)" >(.*?)</a>#is';

if( preg_match_all($pattern, $content, $matches) ) {
    foreach($matches[0] as $key => $val) {
        echo "{$matches[2][$key]}({$matches[1][$key]})\n";
    }
}
