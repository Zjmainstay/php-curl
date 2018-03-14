<?php

/**
 * 阿里文学网小说解析
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 * 访问登录页，提交登录表单，查看登录结果
 */

require_once __DIR__.'/../../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$bookId = '7379267';
$noteFile = __DIR__ . "/books/{$bookId}.txt";
$curl = "curl 'http://www.aliwx.com.cn/reader?bid={$bookId}' -H 'Host: www.aliwx.com.cn' -H 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.9 Safari/537.36' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2' --compressed -H 'Cookie: cna=r5J8EQLlvnwCAQ4XX8qwbRCp; isg=BEFBue5b_zfTexMDilnsrmkZU4tRfx_obeiyn6OWPcinimFc677FMG-IaH7MmU2Y' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1'";
$content = $autologin->execCurl($curl);

$pattern = '#<i class="page-data js-dataChapters">(.*)</i>#i';
preg_match($pattern, $content, $match);

$data = json_decode(str_replace('&quot;', '"', $match[1]), true);

foreach($data['chapterList'][0]['volumeList'] as $chapter) {
    $url = htmlspecialchars_decode("http://c13.shuqireader.com/pcapi/chapter/contentfree/{$chapter['contUrlSuffix']}");
    $curl = "curl '{$url}' -H 'Host: c13.shuqireader.com' -H 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.9 Safari/537.36' -H 'Accept: */*' -H 'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2' --compressed -H 'Referer: http://www.aliwx.com.cn/reader?bid=7379267' -H 'Origin: http://www.aliwx.com.cn' -H 'Connection: keep-alive'";
    $content = $autologin->execCurl($curl);

    if(empty($content)) {
        //到达VIP章节
        break;
    }
    $responeArr = json_decode($content, true);
    $chapterContent = $responeArr['ChapterContent'];

    $chapterContent = `phantomjs 34_www.aliwx.com.cn.js '{$chapterContent}'`;

    file_put_contents($noteFile, str_replace('<br/>', "\n", $chapter['chapterName'] . "({$chapter['chapterId']})\n" . $chapterContent) . "\n\n", FILE_APPEND);
}
