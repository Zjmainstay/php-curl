<?php

/**
 * 简单的模拟登录示例
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 *
 * 带验证码模拟手动输入验证码示例
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$lineBreak = $autologin->getLineBreak();

//登录信息
$username = urlencode('email@qq.com');
$password = "password";

//0. 未登录
$getDataUrl = 'http://www.zuoche.com/welcome.jspx';
echo 'Before Login: ' .  ($autologin->assertContainStr($autologin->getUrl($getDataUrl), '退出') ? '已登录' : '未登录' ) . $lineBreak;

if(empty($_POST)) {
    //1. 初始化登录页
    $curl1 = "curl 'https://zuoche.com/login.jspx' -H 'Host: zuoche.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:49.0) Gecko/20100101 Firefox/49.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Cookie: z_tttt=01580fcb13570031; Hm_lvt_d91c090e9c1140131a8ac18e9cb36e23=1477733727; Hm_lpvt_d91c090e9c1140131a8ac18e9cb36e23=1477733727; login-check=ok' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -H 'Cache-Control: max-age=0'";
    $content = $autologin->execCurl($curl1);

    preg_match('#getverifyimage\.jspx\?verifykey=([^"]+)#is', $content, $match);

    $verifykey = $match[1];
    $codeImgSrc = $match[0];
?>
<form method="POST">
<input name="code" size="6" style="width:80px" type="text"/>
<input name="verifykey" type="hidden" value="<?php echo $verifykey ?>"/>
<img src="https://zuoche.com/<?php echo $codeImgSrc ?>" />
<input type="submit" value="提交">
</form>
<?php
} else {
    $code = $_POST['code'];
    $verifykey = $_POST['verifykey'];

    //2. 提交登录表单
    $curl2 = "curl 'https://zuoche.com/login.jspx?op=login' -H 'Host: zuoche.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:49.0) Gecko/20100101 Firefox/49.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Referer: https://zuoche.com/login.jspx' -H 'Cookie: z_tttt=01580fcb13570031; Hm_lvt_d91c090e9c1140131a8ac18e9cb36e23=1477733727; Hm_lpvt_d91c090e9c1140131a8ac18e9cb36e23=1477733727; login-check=ok' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -H 'Content-Type: application/x-www-form-urlencoded' --data 'ref=&verifykey={$verifykey}&username={$username}&password={$password}&verifycode={$code}'";
    $content = $autologin->execCurl($curl2);

    //3. 登录成功，锁定cookie的更新，直接访问已登录页面内容（类似采集内容），演示cookie锁定多次采集效果与cookie失效效果
    $autologin->lockLastCookieFile();
    echo 'After Login: ' .  ($autologin->assertContainStr($autologin->getUrl($getDataUrl), '退出') ? '已登录' : '未登录' ) . $lineBreak;
    echo $autologin->getUrl($getDataUrl) . $lineBreak;
}
