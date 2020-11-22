<?php

namespace PHPCurl;
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
    //全局opt，方便使用代理之类的请求
    protected $globalOpts = [];
    //最后一次请求参数，用于重放请求
    protected $lastExecParams = [];

    public function __construct($logPath = '') {
        if(!empty($logPath) && is_writable($logPath)) {
            $this->logPath = $logPath;
        } else {
            $this->logPath = dirname(__FILE__) . '/../logs/run.log';
            if(!file_exists($this->logPath)) {
                if(!is_dir(dirname($this->logPath))) {
                    mkdir(dirname($this->logPath), 0755, true);
                }
            }
        }
    }

    /**
     * 设置全局请求opt（方便使用代理之类的请求）
     * @param $opts
     */
    public function setGlobalOpts($opts = [])
    {
        $this->globalOpts += $opts;
        return $this;
    }

    /**
     * 根据curl信息执行并解析结果，核心方法常用方法之一
     * @param  string  $curlContent    利用Firefox浏览器复制cURL命令
     * @param  boolean $callbackBefore 对curl结果前置处理，如更换用户名、密码等
     * @param  boolean $callbackAfter  对采集结果后置处理，如解析结果的csrf token等
     * @param  boolean $storeParams    是否存储最后请求参数供重放使用，默认存储
     * @return mixed
     */
    public function execCurl($curlContent, $callbackBefore = false, $callbackAfter = false, $storeParams = true) {
        //存储参数供请求重放使用
        if($storeParams) {
            $this->lastExecParams = func_get_args();
        }
        $parseCurlResult = $this->parseCurl($curlContent);
        if(is_callable($callbackBefore)) {
            $parseCurlResult = $callbackBefore($parseCurlResult);
        }
        $parseCurlResult['opt'] += $this->globalOpts;
        $execCurlResult  = $this->_execCurl($parseCurlResult);

        if(is_callable($callbackAfter)) {
            $execCurlResult = $callbackAfter($parseCurlResult, $execCurlResult);
        }

        return $execCurlResult;
    }

    /**
     * 携带cookie执行curl命令，核心方法常用方法之一，直接利用curl命令里的header头cookie参数
     * @param  string  $curlContent    利用Firefox浏览器复制cURL命令
     * @param  boolean $callbackBefore 对curl结果前置处理，如更换用户名、密码等
     * @param  boolean $callbackAfter  对采集结果后置处理，如解析结果的csrf token等
     * @return mixed
     */
    public function execCurlWithCookie($curlContent, $callbackBefore = false, $callbackAfter = false) {
        return $this->execCurl($curlContent, function($parseCurlResult) use ($callbackBefore) {
            $parseCurlResult['header'][] = $parseCurlResult['cookie'];
            if(is_callable($callbackBefore)) {
                $parseCurlResult = $callbackBefore($parseCurlResult);
            }
            return $parseCurlResult;
        }, $callbackAfter);
    }

    /**
     * 重放请求，依赖登录的场景出现登录失败的情况，重新登录后，调用此方法重试上一次的请求
     * @return mixed
     */
    public function repeatRequest()
    {
        //最后一次请求参数不为空才重放
        if(!empty($this->lastExecParams)) {
            list($curlContent, $callbackBefore, $callbackAfter) = array_pad($this->lastExecParams, 3, false);
            return $this->execCurl($curlContent, $callbackBefore, $callbackAfter, true);
        }
        return null;
    }

    /**
     * 获取最后一次请求参数
     * @return array
     */
    public function getLastExecParams()
    {
        return $this->lastExecParams;
    }

    /**
     * 主动销毁最后一次请求参数
     * @return $this
     */
    public function unsetLastExecParams()
    {
        $this->lastExecParams = [];
        return $this;
    }

    /**
     * 解析curl信息
     * @param  string $curlContent 利用Firefox浏览器复制cURL命令
     * @return bool|array
     */
    public function parseCurl($curlContent) {
        if(!preg_match("#curl '([^']*)'#is", $curlContent, $matchUrl)
        && !preg_match("#curl.*'([^']*)'\s*$#is", $curlContent, $matchUrl)
        ) {
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
        if(!preg_match_all("#-H '([^']*)'#is", $curlContent, $headerMatches)) {
            $httpHeader = array();
        } else {
            $httpHeader = $headerMatches[1];
        }

        //get data
        //单引号
        if(!preg_match("#(?:--data\S*|-d) \\$?'([^']*)'#is", $curlContent, $postDataMatch)) {
            //双引号
            if(!preg_match('#(?:--data\S*|-d) \\$?"([^"]*)"#is', $curlContent, $postDataMatch)) {
                $postDataMatch[1] = '';
            }
        }
        $postData = $postDataMatch[1];

        return array(
            'url'       => $matchUrl[1],
            'header'    => $httpHeader,
            'post'      => $postData,
            'opt'       => array(),         //扩展opt，在callbackBefore里添加
            'cookie'    => $cookieData,
        );
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
            // curl_setopt($ch, CURLOPT_SSLVERSION,1);          //error:14077458:SSL routines:SSL23_GET_SERVER_HELLO:reason(1112)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
        }

        //add 302 support
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        //add cookie support
        //设置一个不存在的目录以在系统临时目录随机生成一个缓存文件，避免多进程cookie覆盖
        $cookieFile = @tempnam('/not_exist_dir/', 'autologin');
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
                curl_setopt($ch, $key, $value);
            }
        }

        $content = '';
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
        return $this;
    }

    /**
     * 记录日志
     * @param  [type] $msg [description]
     * @return [type]      [description]
     */
    protected function _log($msg) {
        try {
            $res = file_put_contents($this->logPath, $msg . "\n", FILE_APPEND);
        } catch (\Exception $e) {
            error_log("CurlAutoLogin 无法写入日志文件 {$this->logPath}: {$msg}");
        }
    }

    /**
     * 获取上一次存储cookie的文件
     * @return [type] [description]
     */
    public function getLastCookieFile() {
        return $this->lastCookieFile;
    }

    /**
     * 获取最后一次存储的cookie内容
     * @return string
     */
    public function getLastCookieContent() {
        if($file = $this->getLastCookieFile()) {
            if(file_exists($file)) {
                return file_get_contents($file);
            }
        }
        return '';
    }

    /**
     * 手动追加cookie内容到最后一次存储的cookie文件
     * @param $content
     * @return bool|int
     */
    public function appendCookieContent($content)
    {
        if(file_exists($file = $this->getLastCookieFile())) {
            return file_put_contents($file, $content . "\n", FILE_APPEND);
        }
        return false;
    }

    /**
     * 设置上一次存储cookie的文件
     * @param [type] $cookieFile [description]
     */
    public function setLastCookieFile($cookieFile) {
        if(!$this->lockedLastCookieFile) {
            $this->lastCookieFile = $cookieFile;
        }
        return $this;
    }

    /**
     * 清空上次存储的cookie
     */
    public function removeLastCookie() {
        if($file = $this->getLastCookieFile()) {
            //文件存在才清空
            if(file_exists($file)) {
                file_put_contents($file, '');
            }
        }
        return $this;
    }

    /**
     * 登录成功后，锁定上一次存储cookie的文件，避免覆盖
     * @return [type] [description]
     */
    public function lockLastCookieFile() {
        $this->lockedLastCookieFile = true;
        return $this;
    }

    /**
     * 解锁上一次存储cookie的文件
     * @return [type] [description]
     */
    public function unlockLastCookieFile() {
        $this->lockedLastCookieFile = false;
        return $this;
    }

    /**
     * 登录成功后，锁定cookie，可以基于get方式获取url信息
     * @param  [type]  $url    [description]
     * @param  boolean $header [description]
     * @param  array   $opts   [description]
     * @return [type]          [description]
     */
    public function getUrl($url, $header = false, $opts = []) {
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
            // curl_setopt($ch, CURLOPT_SSLVERSION,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
        }

        //add 302 support
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch,CURLOPT_COOKIEFILE, $this->lastCookieFile); //使用提交后得到的cookie数据

        //extend opt
        $opts += $this->globalOpts;
        if(!empty($opts)) {
            foreach ($opts as $key => $value) {
                curl_setopt($ch, $key, $value);
            }
        }

        $content = '';
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
     * 登录成功后，锁定cookie，可以基于post方式获取url信息
     * @param  [type]  $url      [description]
     * @param  boolean $postData [description]
     * @param  boolean $header   [description]
     * @param  array   $opts     [description]
     * @return [type]            [description]
     */
    public function postUrl($url, $postData = false, $header = false, $opts = []) {
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
            // curl_setopt($ch, CURLOPT_SSLVERSION,1);
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

        //extend opt
        $opts += $this->globalOpts;
        if(!empty($opts)) {
            foreach ($opts as $key => $value) {
                curl_setopt($ch, $key, $value);
            }
        }

        $content = '';
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
     * @param string $content  内容
     * @param string $subStr 包含字符串
     */
    public function assertContainStr($content, $subStr) {
        if(false !== stripos($content, $subStr)) {
            return true;
        }

        return false;
    }

    /**
     * 获取换行符
     */
    public function getLineBreak() {
        if('cli' == PHP_SAPI) {
            $lineBreak = "\n";
        } else {
            $lineBreak = "<br>";
        }

        return $lineBreak;
    }
}
