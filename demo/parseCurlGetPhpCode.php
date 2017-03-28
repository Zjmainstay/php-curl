<?php
/**
 * 基于curl解析得到PHP代码
 * @param  [type]  $curlContent [description]
 * @param  boolean $withCookie  [description]
 * @param  integer $timeout     [description]
 * @return [type]               [description]
 */
function parseCurlToCode($curlContent, $withCookie = false, $timeout = 10) {
    if(!preg_match("#curl '([^']*?)'#is", $curlContent, $matchUrl)) {
        return false;
    }
    //get cookie
    if(!preg_match("#-H '(Cookie:[^']*)'#is", $curlContent, $cookieMatch)) {
        $cookieData = '';
    } else {
        $cookieData = $cookieMatch[1];
    }
    //remove cookie data in header
    $curlContent = preg_replace("#-H 'Cookie:[^']*'#is", '', $curlContent);
    //get header
    if(!preg_match_all("#-H '([^']*?)'#is", $curlContent, $headerMatches)) {
        $httpHeader = array();
    } else {
        $httpHeader = $headerMatches[1];
    }
    //get data
    if(!preg_match("#--data '([^']*?)'#is", $curlContent, $postDataMatch)) {
        $curlPostData = '';
    } else {
        $curlPostData = $postDataMatch[1];
    }

    $url = $matchUrl[1];
    $header = $httpHeader;
    $postData = $curlPostData;
    if($withCookie) {
        $cookie = $cookieData;
    } else {
        $cookie = '';
    }

    $tpl = <<<'CONTENT'
<?php
    $url = '%s';
    $header = %s;
    $postData = %s;
    $cookie = %s;
    // $cookieSaveFile = '';
    // $cookieGetFile = '';
    $timeout = %s;

    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);       //返回数据不直接输出
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");        //指定gzip压缩
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);    //302/301
    //SSL
    if(substr($url, 0, 8) === 'https://') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //error:14077458:SSL routines:SSL23_GET_SERVER_HELLO:reason(1112)解决
        //值有0-6，请参考手册，值1不行试试其他值
        //curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    }
    //post数据
    if(!empty($postData)) {
        curl_setopt($ch, CURLOPT_POST, 1);               //发送POST类型数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); //POST数据，$post可以是数组（multipart/form-data），也可以是拼接参数串（application/x-www-form-urlencoded）
    }
    if(!empty($cookie)) {
        $header[] = $cookie;
    }
    if(!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);     //使用header头信息
    }
    // curl_setopt($ch,CURLOPT_COOKIEJAR,$cookieSaveFile); //存储提交后得到的cookie数据
    // curl_setopt($ch,CURLOPT_COOKIEFILE,$cookieGetFile); //使用提交后得到的cookie数据做参数
    //超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
    //执行
    $content = curl_exec($ch);
    if($error = curl_error($ch)) {
        //log error
        error_log($error);
    }
    curl_close($ch);

    // $content 是请求结果
    echo $content;
CONTENT;

    return sprintf($tpl, $url, var_export($header, true), var_export($postData, true), var_export($cookie, true), $timeout);

}
if(!empty($_POST['curl'])) {
    if(!isset($_POST['withCookie'])) {
        $_POST['withCookie'] = false;
    }
    if(!isset($_POST['timeout']) || !abs((int)$_POST['timeout'])) {
        $_POST['timeout'] = 10;
    }
    highlight_string(parseCurlToCode($_POST['curl'], (bool)$_POST['withCookie'], $_POST['timeout']));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>基于cURL命令解析获得PHP代码</title>
</head>
<body>
    <form method="POST" action="parseCurlGetPhpCode.php" target="_blank">
        <div>cURL命令：<textarea name="curl" rows="10" cols="100"></textarea></div>
        <div>携带Cookie:<input type="checkbox" name="withCookie"></div>
        <div>超时时间:<input type="text" name="timeout" value="10" placeholder="cURL超时时间"></div>
        <div><input type="submit" value="提交"></div>
    </form>
</body>
</html>
