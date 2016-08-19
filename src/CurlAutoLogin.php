<?php

namespace PHPCurl;

/**
 * class CurlAutoLogin
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn/php-curl
 *
 * 利用curl信息自动解析实现模拟登录
 */
/**
 * class CurlAutoLogin
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 *
 * 利用curl信息自动解析实现模拟登录
 */
class CurlAutoLogin {
    //最后一次cookie存储文件
    protected $lastCookieFile = '';
    //登录成功后，锁定cookie的更新
    protected $lockedLastCookieFile = false;
    //日志路径
    protected $logPath = '';

    public function __construct($logPath = '') {
        if(!empty($logPath) && is_writable($logPath)) {
            $this->logPath = $logPath;
        } else {
            $this->logPath = __DIR__ . '/../logs/run.log';
        }
    }

    /**
     * 根据curl信息执行并解析结果
     * @param  string  $curlContent    利用Firefox浏览器复制cURL命令
     * @param  boolean $callbackBefore 对curl结果前置处理，如更换用户名、密码等
     * @param  boolean $callbackAfter  对采集结果后置处理，如解析结果的csrf token等
     * @return mixed
     */
    public function execCurl($curlContent, $callbackBefore = false, $callbackAfter = false) {
        $parseCurlResult = $this->_parseCurl($curlContent);
        if(!empty($callbackBefore)) {
            $parseCurlResult = $callbackBefore($parseCurlResult);
        }
        $execCurlResult  = $this->_execCurl($parseCurlResult);

        if(!empty($callbackAfter)) {
            $execCurlResult = $callbackAfter($parseCurlResult, $execCurlResult);
        }

        return $execCurlResult;
    }

    /**
     * 解析curl信息
     * @param  string $curlContent 利用Firefox浏览器复制cURL命令
     * @return bool|array
     */
    protected function _parseCurl($curlContent) {
        if(!preg_match("#curl '([^']*?)'#is", $curlContent, $matchUrl)) {
            return false;
        }

        //remove cookie data in header
        $curlContent = preg_replace("#-H 'Cookie:[^']*'#is", '', $curlContent);

        if(!preg_match_all("#-H '([^']*?)'#is", $curlContent, $headerMatches)) {
            $httpHeader = [];
        } else {
            $httpHeader = $headerMatches[1];
        }

        if(!preg_match("#--data '([^']*?)'#is", $curlContent, $postDataMatch)) {
            $postData = '';
        } else {
            $postData = $postDataMatch[1];
        }

        return [
            'url'       => $matchUrl[1],
            'header'    => $httpHeader,
            'post'      => $postData,
            'opt'       => [],         //扩展opt，在callbackBefore里添加
        ];
    }

    /**
     * 执行curl请求
     * @param  array $parseCurlResult curl信息的解析结果，包含 url/header/post 三个键值参数
     * @return string
     */
    protected function _execCurl($parseCurlResult) {
        if(empty($parseCurlResult['url'])) {
            return '';
        }

        $ch = curl_init($parseCurlResult['url']);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回数据不直接输出
        curl_setopt($ch, CURLOPT_ENCODING, "gzip"); //指定gzip压缩

        //add header
        if(!empty($parseCurlResult['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $parseCurlResult['header']);
        }

        //add ssl support
        if(substr($parseCurlResult['url'], 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
        }

        //add 302 support
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        //add cookie support
        //设置一个不存在的目录以在系统临时目录随机生成一个缓存文件，避免多进程cookie覆盖
        $cookieFile = tempnam('/not_exist_dir/', 'autologin');
        curl_setopt($ch,CURLOPT_COOKIEJAR,$cookieFile); //存储提交后得到的cookie数据

        //add previous curl cookie
        if(!empty($this->lastCookieFile)) {
            curl_setopt($ch,CURLOPT_COOKIEFILE, $this->lastCookieFile); //使用提交后得到的cookie数据
        }

        //add post data support
        if(!empty($parseCurlResult['post'])) {
            curl_setopt($ch,CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $parseCurlResult['post']);
        }

        //extend opt
        if(!empty($parseCurlResult['opt'])) {
            foreach ($parseCurlResult['opt'] as $key => $value) {
                curl_setopt($ch,$key, $value);
            }
        }

        try {
            $content = curl_exec($ch); //执行并存储结果
        } catch (\Exception $e) {
            $this->_log($e->getMessage());
        }

        $curlError = curl_error($ch);
        if(!empty($curlError)) {
            $this->_log($curlError);
        }

        curl_close($ch);

        //update last cookie file
        $this->setLastCookieFile($cookieFile);

        return $content;
    }

    /**
     * 设置日志路径
     * @param string $logPath 绝对路径，必须可写
     */
    public function setLogPath($logPath) {
        $this->logPath = $logPath;
    }

    /**
     * 记录日志
     * @param  [type] $msg [description]
     * @return [type]      [description]
     */
    protected function _log($msg) {
        file_put_contents($this->logPath, $msg . "\n", FILE_APPEND);
    }

    /**
     * 获取上一次存储cookie的文件
     * @return [type] [description]
     */
    public function getLastCookieFile() {
        return $this->lastCookieFile;
    }

    /**
     * 获取cookie内容
     * @return string
     */
    public function getLastCookieContent() {
        return file_get_contents($this->getLastCookieFile());
    }

    /**
     * 设置上一次存储cookie的文件
     * @param [type] $cookieFile [description]
     */
    public function setLastCookieFile($cookieFile) {
        if(!$this->lockedLastCookieFile) {
            $this->lastCookieFile = $cookieFile;
        }
    }

    /**
     * 清空上次存储的cookie
     */
    public function removeLastCookie() {
        file_put_contents($this->getLastCookieFile(), '');
    }

    /**
     * 登录成功后，锁定上一次存储cookie的文件，避免覆盖
     * @return [type] [description]
     */
    public function lockLastCookieFile() {
        $this->lockedLastCookieFile = true;
    }

    /**
     * 解锁上一次存储cookie的文件
     * @return [type] [description]
     */
    public function unlockLastCookieFile() {
        $this->lockedLastCookieFile = false;
    }

    /**
     * 登录成功， get 方式获取url信息
     * @param  [type]  $url    [description]
     * @param  boolean $header [description]
     * @return [type]          [description]
     */
    public function getUrl($url, $header = false) {
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回数据不直接输出
        curl_setopt($ch, CURLOPT_ENCODING, "gzip"); //指定gzip压缩

        //add header
        if(!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        //add ssl support
        if(substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
        }

        //add 302 support
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch,CURLOPT_COOKIEFILE, $this->lastCookieFile); //使用提交后得到的cookie数据

        try {
            $content = curl_exec($ch); //执行并存储结果
        } catch (\Exception $e) {
            $this->_log($e->getMessage());
        }

        $curlError = curl_error($ch);
        if(!empty($curlError)) {
            $this->_log($curlError);
        }

        curl_close($ch);

        return $content;
    }

    /**
     * 登录成功， post 方式获取url信息
     * @param  [type]  $url      [description]
     * @param  boolean $postData [description]
     * @param  boolean $header   [description]
     * @return [type]            [description]
     */
    public function postUrl($url, $postData = false, $header = false) {
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回数据不直接输出
        curl_setopt($ch, CURLOPT_ENCODING, "gzip"); //指定gzip压缩

        //add header
        if(!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        //add ssl support
        if(substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
        }

        //add 302 support
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch,CURLOPT_COOKIEFILE, $this->lastCookieFile); //使用提交后得到的cookie数据

        //add post data support
        curl_setopt($ch,CURLOPT_POST, 1);
        if(!empty($postData)) {
            curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);
        }

        try {
            $content = curl_exec($ch); //执行并存储结果
        } catch (\Exception $e) {
            $this->_log($e->getMessage());
        }

        $curlError = curl_error($ch);
        if(!empty($curlError)) {
            $this->_log($curlError);
        }

        curl_close($ch);

        return $content;
    }

    /**
     * 断言内容中包含某个字符（判断登录信息，如“退出”字眼）
     * @param $content 内容
     * @param $substr 包含字符串
     */
    public function assertContainStr($content, $substr) {
        if(false !== stripos($content, $substr)) {
            return true;
        }

        return false;
    }
}
