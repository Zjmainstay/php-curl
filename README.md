# php-curl
PHP利用cURL实现模拟登录与采集，提供大量示例。（关键词：php curl login） 

了解PHP cURL使用请参考：《[PHP cURL实现模拟登录与采集使用方法详解](http://www.zjmainstay.cn/php-curl)》

基于浏览器cURL命令快速生成PHP代码：[基于cURL命令解析获得PHP代码](http://demo.zjmainstay.cn/php/github/php-curl/demo/parseCurlGetPhpCode.php)

自动模拟登录+批量请求示例：[模拟登录后批量下载笔记](https://github.com/Zjmainstay/php-curl/blob/master/demo/62_curlmulti/62_wiz.cn.php)

## 依赖

PHP: >=5.4

## 单独使用

```php
git clone https://github.com/Zjmainstay/php-curl.git
cd php-curl
composer install
#测试
php demo/1_simpleAutoLogin.php
```

## composer引入

```php
composer require zjmainstay/php-curl

#测试
cd vendor/zjmainstay/php-curl
composer install
php demo/1_simpleAutoLogin.php
```

## 直接引入类

```php
require_once __DIR__.'/../src/CurlAutoLogin.php'; # 使用时注意路径
$autologin = new PHPCurl\CurlAutoLogin();

# 参考demo/0_simpleAutoLogin.php
```



## CurlAutoLogin 介绍

### 属性

```php
//最后一次cookie存储文件
protected $lastCookieFile = '';
//登录成功后，锁定cookie的更新
protected $lockedLastCookieFile = false;
//日志路径
protected $logPath = '';
//全局opt，方便使用代理之类的请求
protected $globalOpts = [];
//最后一次请求参数，用于重放请求
protected $lastExecParams = [];
```

### 方法

```php
// ================== 1.常用方法 ==================
//根据curl信息执行并解析结果，核心方法常用方法之一
public function execCurl($curlContent, $callbackBefore = false, $callbackAfter = false, $storeParams = true)

//携带cookie执行curl命令，核心方法常用方法之一，直接利用curl命令里的header头cookie参数
public function execCurlWithCookie($curlContent, $callbackBefore = false, $callbackAfter = false)

// ================== 2.普通公用方法 ==================
//设置全局请求opt（方便使用代理之类的请求）
public function setGlobalOpts($opts = [])

//重放请求，依赖登录的场景出现登录失败的情况，重新登录后，调用此方法重试上一次的请求
public function repeatRequest()

//获取最后一次请求参数，用于重放请求
public function getLastExecParams()

//主动销毁最后一次请求参数
public function unsetLastExecParams()

//解析curl信息，返回结果包含url/header/post/opt/cookie，post是post提交的数据，opt是curl备用扩展
public function parseCurl($curlContent)

//设置日志路径
public function setLogPath($logPath) 

//获取上一次存储cookie的文件
public function getLastCookieFile()

//获取最后一次存储的cookie内容
public function getLastCookieContent()

//手动追加cookie内容到最后一次存储的cookie文件
public function appendCookieContent($content)

//设置上一次存储cookie的文件
public function setLastCookieFile($cookieFile)

//清空上次存储的cookie
public function removeLastCookie()

//登录成功后，锁定上一次存储cookie的文件，避免覆盖
public function lockLastCookieFile()

//解锁上一次存储cookie的文件
public function unlockLastCookieFile()

//登录成功后，锁定cookie，可以基于get方式获取url信息
public function getUrl($url, $header = false)

//登录成功后，锁定cookie，可以基于post方式获取url信息
public function postUrl($url, $postData = false, $header = false)

//断言内容中包含某个字符（判断登录信息，如“退出”字眼）
public function assertContainStr($content, $substr)

//获取换行符，用于输出信息显示换行
public function getLineBreak()

// ================== 3.底层方法 ==================
//执行curl请求，底层核心方法，内置了请求的cookie存储与跟踪上一次请求的cookie，实现模拟登录cookie依赖
protected function _execCurl($parseCurlResult)

//记录日志，底层方法，出现异常时记录日志
protected function _log($msg)
```



## demo介绍

```php
//简易模拟登录示例，直接引用CurlAutoLogin类进行调用
0_simpleAutoLogin.php

//简易模拟登录示例，基于composer引用CurlAutoLogin类进行调用
//简介：包含execCurl回调$callbackBefore实现curl请求前替换用户名
1_simpleAutoLogin.php

//浏览器登录微信，利用登录后cookie进行后续访问
//简介：【代码已不可用，参考思路】
//方法一：利用execCurl前置回调添加cookie到header中
//方法二：直接调用parseCurl对curl命令进行解析，并组装包含cookie的header头进行请求
//方法三：直接调用execCurlWithCookie调用
2_wxqq.php
  
//预加载cookie，然后访问其他页面
//简介：模拟登录有时候依赖首页或者其他页面的cookie，所以需要调用execCurl先请求一次，存下该页面的cookie，再做后续的请求
3_preloadCookieAndVisit.php

//中国知网下载
//简介：增加CURLOPT_HEADER属性返回请求头信息，然后解析下载地址进行下载请求
7_cnki.net.php

//webqq发送消息
//简介：【功能已不可用】基于web2 qq 发送群消息和个人消息（注：频繁发送会导致账号锁定，慎用！！！）
8_webqq.php

//博客园模拟登录
//简介：【网站改版，功能已不可用】
//基于nodejs提供js加密处理，返回结果给php请求登录使用
11_cnblogLogin

//（验证码）坐车网登录
//简介：坐车网网页显示验证码，用户手动录入后提交给程序，实现模拟登录
12_zuocheWithCaptcha.php

//模拟登录360doc
//简介：360doc登录，中间有一层cookie依赖，类似demo 3_preloadCookieAndVisit.php
14_360doc.php

//为知笔记内容下载
//简介：利用js在控制台运行获取所有笔记采集链接，然后下载对应笔记
15_downloadWizNote.php

//页面采集通用方法
//简介：日常使用curl请求可用
16_commonCurl/curlPage.php

//（验证码）命令行下输入验证码登录dev.360.cn
//简介：命令行执行获取验证码，写入到文件，命令行等待录入验证码，录入后提交给程序，实现模拟登录
17_dev.360.cn_STDINCaptcha.php

//天眼查关键词搜索
//简介：【功能已不可用】天眼查关键词搜索，最终数据页有token依赖，提取后通过$callbackBefore传入
19_www.tianyancha.com.php

//（验证码）木蚂蚁用户登录
//简介：木蚂蚁用户登录，网页显示验证码，用户手动录入后提交给程序，实现模拟登录
20_u.mumayi.com_OnlineCaptcha.php

//环球外汇网即时行情数据采集
//简介：【页面改版已不需要这么复杂】
//加载页面，解析出js，调用phantomjs执行js获取cookie加密结果，交给php模拟程序调用
22_tools.cnforex.com-phantomjs-parse-js.php

//（验证码）好单库登录
//简介：命令行执行获取验证码，写入到文件，命令行等待录入验证码，录入后提交给程序，实现模拟登录
26_publish.haodanku.com.php
  
//阿里文学js解密
//简介：阿里文学网小说解析，基于phantomjs执行js进行内容解析
34_www.aliwx.com.cn

//图床图片上传
//简介：【网站502功能已不可用】访问初始页获取x-csrf-token，解析后通过$callbackBefore传入，实现模拟请求上传图片到图床网站
38_upload.otar.im_image_upload.php

//草料图片上传二维码解析
//简介：上传图片到草料，然后利用上传结果调用解析接口，获得解析结果
41_cli.im.php

//重放请求
//简介：首先模拟无登录态请求，异常后模拟登录，然后重放请求得到正常结果
48_request_repeat.php

//tophub.today抓取demo
//简介：知乎热榜网站抓取
61_tophub.today.php
  
//【精华推荐】为知笔记模拟登录后，批量下载笔记
//简介：本示例实现模拟登录与ares333/php-curl批量执行类库结合，演示如何模拟登录后，基于登录态进行批量下载笔记
62_curlmulti/62_wiz.cn.php

//基于curl解析得到PHP代码
parseCurlGetPhpCode.php

```

