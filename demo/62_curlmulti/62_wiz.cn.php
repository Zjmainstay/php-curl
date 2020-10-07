<?php
/**
 * 为知笔记登录并批量下载全部笔记文档
 * Created by PhpStorm.
 * User: zjmainstay
 * Date: 2020/10/7
 * Time: 09:33
 */

//注意：本示例执行前，请先执行 composer install 初始化项目依赖
//注意：本示例执行前，请先执行 composer install 初始化项目依赖
//注意：本示例执行前，请先执行 composer install 初始化项目依赖

require __DIR__ . '/vendor/autoload.php';
header("Content-type: text/html; charset=utf-8");

use PHPCurl\CurlAutoLogin;

//请填写为知笔记帐号密码
$username = 'email';
$password = 'password';
$usernameMd5 = md5($username);

//登录态缓存文件
$loginCookieFile = __DIR__ . "/cache/cookie_{$usernameMd5}.txt";
$loginResultFile      = __DIR__ . "/cache/loginRes_{$usernameMd5}.txt";
$autoLogin = new CurlAutoLogin();

//已有登录态，则直接读取登录态，避免多次重复登录
if(!file_exists($loginCookieFile)) {
    $resArr = doLogin($autoLogin, $username, $password, $loginResultFile, $loginCookieFile);
} else {
    echo "有登录态，自动登录...\n";
    $autoLogin->setLastCookieFile($loginCookieFile);
    $resArr = json_decode(file_get_contents($loginResultFile), true);
}

if(empty($resArr['result']['kbGuid'])) {
    exit("未登录成功，请检查帐号和密码是否正确\n");
}

$kbGuid = $resArr['result']['kbGuid'];
$wizToken = $resArr['result']['token'];

//加载全部笔记数据
$size = 100;
$i = 0;
$docGuidArr = [];
while(true){
    $start = $i * $size;
    $curl = <<<CURL
curl 'https://kshttps0.wiz.cn/ks/note/list/category/{$kbGuid}?category=&start={$start}&count={$size}&orderBy=modified&ascending=desc&withAbstract=true&withFavor=false&withShare=true&clientType=web&clientVersion=4.0&lang=zh-cn' \
  -H 'Connection: keep-alive' \
  -H 'X-Wiz-Token: {$wizToken}' \
  -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36' \
  -H 'X-Wiz-Referer: https://www.wiz.cn' \
  -H 'Accept: */*' \
  -H 'Origin: https://www.wiz.cn' \
  -H 'Sec-Fetch-Site: same-site' \
  -H 'Sec-Fetch-Mode: cors' \
  -H 'Sec-Fetch-Dest: empty' \
  -H 'Referer: https://www.wiz.cn/' \
  -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8' \
  --compressed
CURL;

    $content = $autoLogin->execCurl($curl);
    $resArr = json_decode($content, true);
    //登录态丢失自动登录，然后返回重新请求
    if(isset($resArr['returnMessage']) && $resArr['returnMessage'] == 'Invalid token') {
        $resArr = doLogin($autoLogin, $username, $password, $loginResultFile, $loginCookieFile);
        if(empty($resArr['result']['kbGuid'])) {
            exit("未登录成功，请检查帐号和密码是否正确\n");
        }
        $kbGuid = $resArr['result']['kbGuid'];
        $wizToken = $resArr['result']['token'];
        continue;
    }
    if(empty($resArr['result'])) {
        break;
    }
    $docGuidArr = array_merge($docGuidArr, array_column($resArr['result'], 'title', 'docGuid'));
//    file_put_contents(__DIR__ . '/result.log', $content . "\n\n", 8);
    $i++;
}

//单个下载示例
//测试下载460个文档，耗时370秒
//$start = microtime(true);
//echo "逐个请求，开始:" . date('Y-m-d H:i:s') . "\n";
//foreach($docGuidArr as $docGuid => $title) {
//    $curl = <<<CURL
//curl 'https://kshttps0.wiz.cn/ks/note/download/{$kbGuid}/{$docGuid}?downloadInfo=1&downloadData=1&withFavor=false&withShare=true&clientType=web&clientVersion=4.0&lang=zh-cn' \
//  -H 'Connection: keep-alive' \
//  -H 'X-Wiz-Token: {$wizToken}' \
//  -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36' \
//  -H 'X-Wiz-Referer: https://www.wiz.cn' \
//  -H 'Accept: */*' \
//  -H 'Origin: https://www.wiz.cn' \
//  -H 'Sec-Fetch-Site: same-site' \
//  -H 'Sec-Fetch-Mode: cors' \
//  -H 'Sec-Fetch-Dest: empty' \
//  -H 'Referer: https://www.wiz.cn/' \
//  -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8' \
//  --compressed
//CURL;
//
//    $content = $autoLogin->execCurl($curl);
//    $resArr = json_decode($content, true);
//    $dir = __DIR__ . '/cache/type_1/' . ltrim($resArr['info']['category'], '/');
//    if(!is_dir($dir)) {
//        mkdir($dir, 0755, true);
//    }
//    if(!empty($resArr['html'])) {
//        file_put_contents($dir . str_replace("/", ":", $resArr['info']['title']), $resArr['html']);
//    } else {
//        echo "empty content: {$resArr['info']['category']}{$title}\n";
//    }
//}
//echo "逐个请求，结束:" . date('Y-m-d H:i:s') . "\n";
//$timeCost = microtime(true) - $start;
//echo "逐个请求，耗时:" . $timeCost . "秒 \n";

//批量下载示例
//测试下载460个文档，耗时45秒
$multiStart = microtime(true);
echo "批量请求，开始:" . date('Y-m-d H:i:s') . "\n";

//解析下载url，获取url、header等信息，由于url是依赖每个笔记的ID，因此埋入一个替换标记[docGuid]
$curl = <<<CURL
curl 'https://kshttps0.wiz.cn/ks/note/download/{$kbGuid}/[docGuid]?downloadInfo=1&downloadData=1&withFavor=false&withShare=true&clientType=web&clientVersion=4.0&lang=zh-cn' \
  -H 'Connection: keep-alive' \
  -H 'X-Wiz-Token: {$wizToken}' \
  -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36' \
  -H 'X-Wiz-Referer: https://www.wiz.cn' \
  -H 'Accept: */*' \
  -H 'Origin: https://www.wiz.cn' \
  -H 'Sec-Fetch-Site: same-site' \
  -H 'Sec-Fetch-Mode: cors' \
  -H 'Sec-Fetch-Dest: empty' \
  -H 'Referer: https://www.wiz.cn/' \
  -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8' \
  --compressed
CURL;
$curlInfo = $autoLogin->parseCurl($curl);

$multiCurl = new \Ares333\Curl\Curl();
//回调打印最后一个下载完成的时间
$multiCurl->onInfo = function($info, $_this, $isLast) {
    global $multiStart;
    if($isLast) {
        echo "批量请求，结束:" . date('Y-m-d H:i:s') . "\n";
        $timeCost = microtime(true) - $multiStart;
        echo "批量请求，耗时:" . $timeCost . "秒 \n";
    }
};
$docCount = count($docGuidArr);
echo "开始下载文档，共{$docCount}个\n";
$index = 0;
//把全部下载地址加入批量下载队列
foreach ($docGuidArr as $docGuid => $title) {
    //替换文档ID变量，得到真实下载地址
    $url = str_replace('[docGuid]', $docGuid, $curlInfo['url']);
    //加入下载队列
    $multiCurl->add([
        'opt' => array(
            CURLOPT_URL => $url,                                    //指定下载地址
            CURLOPT_HTTPHEADER => $curlInfo['header'],              //引用下载header参数
            CURLOPT_COOKIEFILE => $autoLogin->getLastCookieFile(),  //引用前面模拟登录的cookie
            CURLOPT_RETURNTRANSFER => true,                         //抓取结果不直接输出，返回内容
        ),
        'args' => [ //下载完成回调参数，传入一下后续要用的参数，方便打印记录
            'index' => $index + 1,
            'title' => $title,
        ],
    ], 'parseDocResult');
    $index++;
}
//开始下载
$multiCurl->start();

//功能函数
/**
 * 文档下载结果解析
 * @param $r
 * @param $args
 */
function parseDocResult($r, $args) {
    if($r['info']['http_code'] == 200) {
        $resArr = json_decode($r['body'], true);
        $dir = __DIR__ . '/cache/type_2/' . ltrim($resArr['info']['category'], '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if(!empty($resArr['html'])) {
            echo "下载第[{$args['index']}]个文档: {$resArr['info']['category']}{$args['title']}\n";
            file_put_contents($dir . str_replace("/", ":", $resArr['info']['title']), $resArr['html']);
        } else {
            echo "下载第[{$args['index']}]个文档: {$resArr['info']['category']}{$args['title']}，无内容\n";
        }
    } else {
        echo "fail {$r['info']['url']}\n";
    }
}

/**
 * 执行登录
 * @param $username
 * @param $password
 */
function doLogin($autoLogin, $username, $password, $loginResultFile, $loginCookieFile)
{
    echo "无登录态，模拟登录...\n";
    $curl = <<<CURL
curl 'https://as.wiz.cn/as/user/login?clientType=web&clientVersion=4.0&lang=zh-cn' \
  -H 'Connection: keep-alive' \
  -H 'X-Wiz-Token: ' \
  -H 'X-Wiz-Referer: https://www.wiz.cn' \
  -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36' \
  -H 'Content-Type: application/json' \
  -H 'Accept: */*' \
  -H 'Origin: https://www.wiz.cn' \
  -H 'Sec-Fetch-Site: same-site' \
  -H 'Sec-Fetch-Mode: cors' \
  -H 'Sec-Fetch-Dest: empty' \
  -H 'Referer: https://www.wiz.cn/' \
  -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8' \
  -H 'Cookie: _ga=GA1.2.444535747.1602034289; _gid=GA1.2.888404600.1602034289; _gat_gtag_UA_141913729_1=1; Hm_lvt_df6808d18fab4a1dfa7454a064069692=1602034290; Hm_lpvt_df6808d18fab4a1dfa7454a064069692=1602034290' \
  --data-binary '{"userId":"{$username}","password":"{$password}","autoLogin":true,"domain":"wiz.cn"}' \
  --compressed
CURL;

    $content = $autoLogin->execCurl($curl);
    $resArr = json_decode($content, true);
    file_put_contents($loginResultFile, $content);  //存储登录结果
    file_put_contents($loginCookieFile, $autoLogin->getLastCookieContent());    //存储登录后的cookie
    
    return $resArr;
}
