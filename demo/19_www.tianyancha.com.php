<?php

/**
 * 简单的模拟登录示例
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 * 访问登录页，提交登录表单，查看登录结果
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

#php 19_www.tianyancha.com.php 霍山衡济堂中药饮片有限公司

if(empty($argv[1])) {
    echo "Usage: php 19_www.tianyancha.com.php 霍山衡济堂中药饮片有限公司 \n";
    exit;
}

$searchKey = urlencode($argv[1]);

//1. 首页
$curl = "curl 'http://www.tianyancha.com/search?key={$searchKey}&checkFrom=searchBox' -H 'Host: www.tianyancha.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' --compressed -H 'Cookie: aliyungf_tc=AQAAAPZekiabgwQAyl8XDgCN/TtlujOh; TYCID=387bf43d7b7b483ebcdf4e877a912dc6; tnet=14.23.95.202; _pk_id.1.e431=06a39c0c47b56b73.1492395992.1.1492398252.1492395992.; _pk_ses.1.e431=*; gr_user_id=bea87b53-255e-4110-b23a-0be163ed57f1; gr_session_id_869c1f8ef123f88b=fe5a8a32-50d0-4eac-b30c-5c9c0c674712; Hm_lvt_e92c8d65d92d534b0fc290df538b4758=1492395993; Hm_lpvt_e92c8d65d92d534b0fc290df538b4758=1492398252; paaptp=e5aac1e6ae5073f6908951e4d421d8ca1f7676b9c8048d424015b79de23db; token=a3de4fea3ac445e09517850c3399ca84; _utm=07122d7ca1704290a7ae62364faf09d2; _pk_id.6835.e431=1d6b2bfd25408756.1492396001.1.1492398252.1492396001.; _pk_ses.6835.e431=*; RTYCID=a5c8142e447f4601859c85d0ba826e29' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -H 'Cache-Control: max-age=0, no-cache' -H 'Pragma: no-cache'";
// $content = $autologin->execCurl($curl);
// var_dump($content);

//2. 统计
/*
    //对应js
    _tongJi = function(companyName, service, $q) {
        var deferred = $q.defer();
        return service.tongji(companyName).then(function(data) {
            for (var arr = data.data.data.v.split(","), fnStr = "", i = 0; i < arr.length; i++) fnStr += String.fromCharCode(arr[i]);
            eval(fnStr),
            deferred.resolve()
        },
        function(e) { (403 == e.status || 404 == e.status || 501 == e.status || 502 == e.status || 503 == e.status || 504 == e.status) && QueryService.errorMassageSend(e.status)
        }),
        deferred.promise
    }
 */
$random = time() . rand(100,999);
$curl = "curl 'http://www.tianyancha.com/tongji/{$searchKey}.json?random={$random}' -H 'Host: www.tianyancha.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0' -H 'Accept: application/json, text/plain, */*' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' --compressed -H 'Tyc-From: normal' -H 'Referer: http://www.tianyancha.com/search?key={$searchKey}&checkFrom=searchBox' -H 'Cookie: aliyungf_tc=AQAAAPZekiabgwQAyl8XDgCN/TtlujOh; TYCID=387bf43d7b7b483ebcdf4e877a912dc6; tnet=14.23.95.202; _pk_id.1.e431=06a39c0c47b56b73.1492395992.1.1492398258.1492395992.; _pk_ses.1.e431=*; gr_user_id=bea87b53-255e-4110-b23a-0be163ed57f1; gr_session_id_869c1f8ef123f88b=fe5a8a32-50d0-4eac-b30c-5c9c0c674712; Hm_lvt_e92c8d65d92d534b0fc290df538b4758=1492395993; Hm_lpvt_e92c8d65d92d534b0fc290df538b4758=1492398258; paaptp=dc8bd40bc98ddb35b74c80efedd60de3ed9ccf63c705bc19e815b79e5729b; token=b9bef624b51d4d278827803ed618f2b3; _utm=03492b4af4b3429ea43988dc944b8f43; _pk_id.6835.e431=1d6b2bfd25408756.1492396001.1.1492398258.1492396001.; _pk_ses.6835.e431=*; RTYCID=a5c8142e447f4601859c85d0ba826e29' -H 'Connection: keep-alive'";
$content = $autologin->execCurl($curl);
$dataArr = json_decode($content, true);
$charsArr = explode(',', $dataArr['data']['v']);
$realChar = '';
foreach($charsArr as $char) {
    $realChar .= chr($char);
}
preg_match("#token=([^;]+)#", $realChar, $match);
$token = $match[1];

//3. 数据
$curl = "curl 'http://www.tianyancha.com/v2/search/{$searchKey}.json?' -H 'Host: www.tianyancha.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:52.0) Gecko/20100101 Firefox/52.0' -H 'Accept: application/json, text/plain, */*' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' --compressed -H 'Tyc-From: normal' -H 'loop: null' -H 'CheckError: check' -H 'Referer: http://www.tianyancha.com/search?key={$searchKey}&checkFrom=searchBox' -H 'Cookie: aliyungf_tc=AQAAAPZekiabgwQAyl8XDgCN/TtlujOh; TYCID=387bf43d7b7b483ebcdf4e877a912dc6; tnet=14.23.95.202; _pk_id.1.e431=06a39c0c47b56b73.1492395992.1.1492398732.1492395992.; _pk_ses.1.e431=*; gr_user_id=bea87b53-255e-4110-b23a-0be163ed57f1; gr_session_id_869c1f8ef123f88b=fe5a8a32-50d0-4eac-b30c-5c9c0c674712; Hm_lvt_e92c8d65d92d534b0fc290df538b4758=1492395993; Hm_lpvt_e92c8d65d92d534b0fc290df538b4758=1492398732; paaptp=4fd9f7303262c59b3d0fc19092ffd756ba8731bc790119a4fa15b79e573f9; token=669baeb13d1d4979b19ce6aa4d0164c2; _utm=e30a65104ce74c8a85a2d855cfcec817; _pk_id.6835.e431=1d6b2bfd25408756.1492396001.1.1492398258.1492396001.; _pk_ses.6835.e431=*; RTYCID=a5c8142e447f4601859c85d0ba826e29' -H 'Connection: keep-alive' -H 'Cache-Control: max-age=0'";
$content = $autologin->execCurl($curl, function($parseCurlResult) use ($token) {
    $parseCurlResult['header'][] = "Cookie:token={$token}";
    return $parseCurlResult;
});

var_dump($content);
