<?php

/**
 * 页面采集通用方法
 * @author  Zjmainstay
 * @website http://www.zjmainstay.cn/php-curl
 *
 * @param  string $url      采集的url
 * @param  array $postData  post提交的数据
 * @param  array $header    header头数组，curl命令里的-H参数组成的数组
 * @param  array $opts      option数组，供额外添加opt属性，key为curl_setopt第二参数，value为curl_setopt第三参数
 * @param  string $cookieSaveFile    存储cookie的文件
 * @param  string $cookieGetFile     读取cookie的文件（可以是上一个getPage存下来的cookie文件）
 * @param  string $timeout 超时时间
 * @return string
 */
function curlPage( $url, $postData = array(), $header = array(), $opts = array(), $cookieSaveFile = '', $cookieGetFile = '' , $timeout = 60 ) {
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
        curl_setopt($ch, CURLOPT_POST, 1);                  //发送POST类型数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    //POST数据，$post可以是数组（multipart/form-data），也可以是拼接参数串（application/x-www-form-urlencoded）
    }
    /*
    //header demo
    $header = array(
                  'Host: www.zjmainstay.cn',
                  'Referer: http://www.zjmainstay.cn/',
                  'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:50.0) Gecko/20100101 Firefox/50.0',
              );
    */
    //对cURL命令使用正则替换 -H 为 ,\n 即可得到数组项，一般移除其中的cookie，由上一个页面存储并使用，参考$cookieSaveFile/$cookieGetFile参数
    //如果是json格式上传，其中的Content-Length需要针对提交内容用strlen计算得到并替换
    //头信息
    if(!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);      //使用header头信息
    }

    //额外option
    if(!empty($opts)) {
        foreach($opts as $key => $value) {
            curl_setopt($ch, $key, $value);      //自定义OPT
        }
    }

    //存储cookie到文件
    if(!empty($cookieSaveFile)) {
        curl_setopt($ch,CURLOPT_COOKIEJAR,$cookieSaveFile); //存储提交后得到的cookie数据
    }
    //使用存储的cookie内容（上一次请求得到的cookie文件）
    if(!empty($cookieGetFile)) {
        curl_setopt($ch,CURLOPT_COOKIEFILE,$cookieGetFile); //使用提交后得到的cookie数据做参数
    }
    //超时时间
    if(!empty($timeout)) {
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
    }
    //执行
    $content = curl_exec($ch);
    if($error = curl_error($ch)) {
        //log error
        error_log($error);
    }
    curl_close($ch);

    return $content;
}

#测试http
$url = 'http://api.zjmainstay.cn';
echo "http url ok: ", curlPage($url) , "\n";

#测试https
$url = 'https://api.zjmainstay.cn';
echo "https url ok: ", curlPage($url) , "\n";

#测试缺少referere
$url = 'http://demo.zjmainstay.cn/php/curl/search_refer.php';
$post       = array(
    'wd'        => urlencode('php'),
);
echo "no referer fail: ", substr(curlPage($url, $post), 0, 100) , "\n";

#测试使用referer 和 post数据
$url = 'http://demo.zjmainstay.cn/php/curl/search_refer.php';
$post       = array(
    'wd'        => urlencode('php'),
);
$header = array(
                'Referer: http://demo.zjmainstay.cn/',
                );
echo "referer in header ok: ", preg_replace('#\s+#', '', substr(curlPage($url, $post, $header), 0, 100)) , "\n";

#测试超时
$url = 'http://demo.zjmainstay.cn/php/curl/sleep3seconds.php';
echo "timeout in 1's fail: ", substr(curlPage($url, $post, $header, array(), false, false, 1), 0, 100) , "\n";

#测试gzip正常
$url = 'http://news.sohu.com/';
echo "gzip is ok: ", substr(curlPage($url), 0, 100) , "\n";

#测试301正常（没有看到301 Move）
$url = 'http://zjmainstay.cn';
echo "301 is ok: ", substr(curlPage($url), 0, 100) , "\n";

#测试存储cookie成功
$url = 'http://demo.zjmainstay.cn/jquery/autoCheckCaptcha/createcode.php?t=' . rand(100000, 999999);
$saveCookieFile = __DIR__ . '/curlPage.cookie.txt';
if(file_exists($saveCookieFile)) { @unlink($saveCookieFile); }
$imgContent = curlPage($url, false, false, array(), $saveCookieFile);
$codeFile = __DIR__ . '/code.png';
file_put_contents($codeFile, $imgContent);
echo "save cookie is ok: ", file_get_contents($saveCookieFile) , "\n";

echo "请输入一个验证码（ {$codeFile} ）：";
$code = trim(fgets(STDIN));

#测试使用cookie
$url = 'http://demo.zjmainstay.cn/jquery/autoCheckCaptcha/checkcode.php';
$post = array(
              'code' => $code,    //手动填写验证码并提交运行
              );
echo "use cookie is ok when status=1: ", curlPage($url, $post, false, array(), false, $saveCookieFile) , "\n";

#测试添加额外opt
$url = 'http://zjmainstay.cn';
$content = curlPage($url, array(), false, array(
                                        CURLOPT_FOLLOWLOCATION => FALSE,
                                        CURLOPT_HEADER => TRUE,
                                        ));
echo "get url with no follow(302) and get header info: \n" , substr($content, 0, 200), "\n";
