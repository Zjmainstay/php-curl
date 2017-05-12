<?php
require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();
$loginCookieFile = __DIR__.'/tmp_ant_login.txt';

if(empty($_POST)){
    $getDataUrl = "curl 'http://u.mumayi.com/oauth/?m=Oauth&a=authorize&client_id=100004&redirect_uri=http://pay.mumayi.com/?a=callback&response_type=code' -H 'Host: u.mumayi.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:53.0) Gecko/20100101 Firefox/53.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' --compressed -H 'Cookie: PHPSESSID=g705ctncodie3rd2df50tlg585' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -H 'Cache-Control: max-age=0'";
    $content = $autologin->execCurl($getDataUrl);

    $curl_code = "curl 'http://u.mumayi.com/oauth/?m=Oauth&a=getmycode&9414.216286713418' -H 'Host: u.mumayi.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:53.0) Gecko/20100101 Firefox/53.0' -H 'Accept: */*' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' --compressed -H 'Referer: http://u.mumayi.com/oauth/?m=Oauth&a=authorize&client_id=100004&redirect_uri=http://pay.mumayi.com/?a=callback&response_type=code&changeuser=y' -H 'Cookie: PHPSESSID=g705ctncodie3rd2df50tlg585' -H 'Connection: keep-alive' -H 'Cache-Control: max-age=0'";
    $content = $autologin->execCurl($curl_code);

    //保存cookie
    file_put_contents($loginCookieFile, $autologin->getLastCookieContent());
?>
<form method="POST">
    <input name="captcha" size="6" style="width:80px" type="text"/>
    <img src="data:image/png;base64,<?php echo base64_encode($content); ?>" />
    <input type="submit" value="提交">
</form>
<?php
}else{
    $autologin->setLastCookieFile($loginCookieFile);
    $username = 'qq';
    $password = "password";
    $captcha = isset($_REQUEST["captcha"]) ? $_REQUEST["captcha"] : "";
    $curl_login = "curl 'http://u.mumayi.com/oauth/?m=Oauth&a=authorize' -H 'Host: u.mumayi.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:53.0) Gecko/20100101 Firefox/53.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' --compressed -H 'Content-Type: application/x-www-form-urlencoded' -H 'Referer: http://u.mumayi.com/oauth/?m=Oauth&a=authorize&client_id=100004&redirect_uri=http://pay.mumayi.com/?a=callback&response_type=code&changeuser=y' -H 'Cookie: PHPSESSID=g705ctncodie3rd2df50tlg585' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' --data 'usernm={$username}&passwd={$password}&mycode={$captcha}&scopelist%5B%5D=basicinfo&scopelist%5B%5D=bbsinfo&client_id=100004&response_type=code&redirect_uri=http%3A%2F%2Fpay.mumayi.com%2F%3Fa%3Dcallback&state=&scope=&display=&accept=Yep'";

    $content = $autologin->execCurl($curl_login);
    unlink($loginCookieFile);    //删除临时文件
    echo $content;
    die;
}

