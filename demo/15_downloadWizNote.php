<?php
//创建笔记html/json目录
if(DIRECTORY_SEPARATOR == '/') {
    $jsonDir = '/tmp/wiz/json/';
    $htmlDir = '/tmp/wiz/html/';
} else {
    $jsonDir = 'C:\\wiz\\json\\';
    $htmlDir = 'C:\\wiz\\html\\';
}
if(!is_dir($jsonDir)) {
    if(!mkdir($jsonDir)) {
        exit("无法创建笔记json存储路径：{$jsonDir}，请手动创建。\n");
    }
}
if(!is_dir($htmlDir)) {
    if(!mkdir($htmlDir)) {
        exit("无法创建笔记html存储路径：{$htmlDir}，请手动创建。\n");
    }
}

/**
 * 基于已登录cookie下载为知笔记（加密笔记无法下载）
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 *
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$lineBreak = $autologin->getLineBreak();

// 步骤1：登录网页版为知笔记 https://note.wiz.cn/web
// 步骤2：查看近期笔记，并下拉刷新直到全部显示笔记
// 步骤3：利用下面的js，在控制台运行得到所有的笔记链接
// 步骤4：将上面得到的笔记地址，填入$notes数组
// 步骤5：运行php 15_downloadWizNote.php开始下载笔记 （注：加密笔记无法下载正确内容，考虑临时取消加密）
// 步骤6：解析笔记json，存储为html
/*

//控制台运行js代码
var res = '';
var token = document.cookie.match(/token=([^;]+)/)[1]
$(".note-list-item").each(function(){
  var title = $(this).find(".title").attr('title');
  res += '"https://note.wiz.cn/api/document/info?client_type=web2.0&api_version=6&token='
      +token+'&kb_guid='+$(this).attr('data-kbguid')+'&document_guid='+$(this).attr('data-docguid')
      +'&_='+(Math.floor(new Date().getTime() / 1000))+(parseInt(Math.random()*1000))+'", //' +title+ "\n";
});
console.log(res);

 */

//所有笔记采集链接，利用js在控制台运行获取
$notes = array(
                'https://note.wiz.cn/api/document/info?client_type=web2.0&api_version=6&token=ebeda6e29d35b1d113a7190ce8fd268at75l8wpsw8ybta&kb_guid=781635a1-97b6-44ee-a2c9-33ead2a3ab1e&document_guid=31efcb50-36dc-4c1c-9576-6b103cd219e1&_=1481679224449',
                'https://note.wiz.cn/api/document/info?client_type=web2.0&api_version=6&token=ebeda6e29d35b1d113a7190ce8fd268at75l8wpsw8ybta&kb_guid=781635a1-97b6-44ee-a2c9-33ead2a3ab1e&document_guid=10de4633-d3ed-441b-ac1a-9e899cda640b&_=1481679224449',
               );

//测试发送信息
foreach($notes as $noteUrl) {
    //网页版登录后，获取一条笔记加载的curl命令
    $curl = "curl 'https://note.wiz.cn/api/document/info?client_type=web2.0&api_version=6&token=ebeda6e29d35b1d113a7190ce8fd268at75l8wpsw8ybta&kb_guid=781635a1-97b6-44ee-a2c9-33ead2a3ab1e&document_guid=fcb921c1-8dbd-46a1-af24-7a43343c18c5&_=1481679224449' -H 'Accept: application/json, text/javascript, */*; q=0.01' -H 'Accept-Encoding: gzip, deflate, br' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Connection: keep-alive' -H 'Cookie: Hm_lvt_df6808d18fab4a1df17434a064069692=1481528227,1481678379; Hm_lpvt_df6808d183a14a1dfa7454a064069692=1481679228; token=ebeda6e29d35b1d113a7190ce8fd268at75l8wpsw8ybta; wizID=1000000%40qq.com; CertNo=dd233f28e1460fde5ef72d63c00a1e6f13d3790cf90a0e2d0f0c148a504adee3b893423f0d4341aad03470f6c79b620b; express:sess=eyJrYnMiOiIsNzExMmQ1Yj15Y2VjZSZkYzA3OTdkZjMxN2YwMTE0YTUifQ==; express:sess.sig=ioeZRP1j19691rDqGo6ZOnjHTUQ' -H 'Host: note.wiz.cn' -H 'Referer: https://note.wiz.cn/web?dc=f2b91bc1-8d1d-46a1-aff4-7a43c44c28c5&cmd=kw%2C&kb=78125a1-97b6-441e-12c9-33e322a3ab1e' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:50.0) Gecko/20100101 Firefox/50.0' -H 'X-Requested-With: XMLHttpRequest'";
    $content = $autologin->execCurlWithCookie($curl);
    //锁定cookie
    $autologin->lockLastCookieFile();

    //替换为最新的token
    $tokenPattern = '#token=([^&]+)#';
    preg_match($tokenPattern, $curl, $tokenMatch);
    $noteUrl = preg_replace($tokenPattern, 'token=' . $tokenMatch[1], $noteUrl);

    //替换为最新的kb_guid
    $kbGuidPattern = '#kb_guid=([^&]+)#';
    preg_match($kbGuidPattern, $curl, $kbGuidMatch);
    $noteUrl = preg_replace($tokenPattern, 'kb_guid=' . $kbGuidMatch[1], $noteUrl);

    echo "正在下载笔记：{$noteUrl}\n";

    $noteContent = $autologin->getUrl($noteUrl);

    //使用文档id作为文件名
    preg_match('#document_guid=([^&]+)#', $noteUrl, $documentGuidMatch);
    $filename = $documentGuidMatch[1] . '.json';

    //存储笔记json
    file_put_contents($jsonDir . $filename, $noteContent);
}

//解析json存入html文件
foreach(glob($jsonDir . '*.json') as $file) {
    $htmlFile = str_replace('/json/', '/html/', substr($file, 0, -4) . 'html');
    $content = file_get_contents($file);
    $dataArr = json_decode($content, true);
    if(empty($dataArr['document_info']['document_body'])) {
        echo "{$file} 无法解析，请确认是否为加密笔记，加密笔记无法下载内容。\n";
        continue;
    }
    $document = $dataArr['document_info']['document_body'];
    file_put_contents($htmlFile, $document);
}
die;
