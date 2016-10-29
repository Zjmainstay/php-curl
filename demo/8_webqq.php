<?php

/**
 * 直接拷贝curl运行示例
 * @author Zjmainstay
 * @website http://www.zjmainstay.cn
 * @project https://github.com/Zjmainstay/php-curl
 * 
 * 基于web2 qq 发送群消息和个人消息（注：频繁发送会导致账号锁定，慎用！！！）
 */

require_once __DIR__.'/../vendor/autoload.php';

$autologin = new PHPCurl\CurlAutoLogin();

$lineBreak = $autologin->getLineBreak();

//测试发送信息
for($i = 1; $i <= 3; $i++) {
    $message = rawurlencode(date('Y-m-d H:i:s') . ': message_' . $i);
    $curl1 = "curl 'http://d1.web2.qq.com/channel/send_qun_msg2' -H 'Host: d1.web2.qq.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:48.0) Gecko/20100101 Firefox/48.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate' -H 'Content-Type: application/x-www-form-urlencoded' -H 'Referer: http://d1.web2.qq.com/proxy.html?v=20151105001&callback=1&id=2' -H 'Cookie: ts_uid=6512215782; ts_refer=www.popoho.com/tool/denglu/; p_uin=o01000088; p_skey=miwiAfcSoDeZCoFJa6OefkUZ7w03HWmRVKzLxUZZ-1U_; pt4_token=we*K*xMoPd5*BqM4m16h8yI*NSvonZ3MjeyEb5r90sw_; pgv_info=ssid=s4959849500; pgv_pvid=649892905; pt2gguin=o01000088; uin=o01000088; skey=@a5s8A3qb3; ptisp=cnc; RK=YIGuZAlSQS; ptwebqq=37848925dee6af86f6579c9c46fed9f19a; o_cookie=1000088' --data 'r=%7B%22group_uin%22%3A3978381470%2C%22content%22%3A%22%5B%5C%22{$message}%5C%22%2C%5B%5C%22font%5C%22%2C%7B%5C%22name%5C%22%3A%5C%22%E5%AE%8B%E4%BD%93%5C%22%2C%5C%22size%5C%22%3A10%2C%5C%22style%5C%22%3A%5B0%2C0%2C0%5D%2C%5C%22color%5C%22%3A%5C%22000000%5C%22%7D%5D%5D%22%2C%22face%22%3A0%2C%22clientid%22%3A53999199%2C%22msg_id%22%3A20080001%2C%22psessionid%22%3A%228368046764001d636f6e6e7365727665725f77656271714031302e3133332e34312e383400001ad00000066b026e040015808a206d0000000a406172314338344a69526d000000285918d94e66218548d1ecb1a12513c86126b3afb97a3c2955b1070324790733ddb059ab166de6857%22%7D'";

    //注：频繁发送会导致账号锁定，慎用！！！
    $msg = rawurlencode(date('Y-m-d H:i:s ') . '我轰死你个煞笔' . $i);
    $curl1 = "curl 'http://d1.web2.qq.com/channel/send_buddy_msg2' -H 'Host: d1.web2.qq.com' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:48.0) Gecko/20100101 Firefox/48.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate' -H 'Content-Type: application/x-www-form-urlencoded' -H 'Referer: http://d1.web2.qq.com/proxy.html?v=20151105001&callback=1&id=2' -H 'Cookie: ts_uid=6512215782; ts_refer=www.popoho.com/tool/denglu/; p_uin=o01000088; p_skey=2rnpdQKMZJz2IP9zqvixxKNUVgm2DwFNm2NiHEH0F0E_; pt4_token=ArYeZZXriUbYi157LEY2QhbQKHIwvEUdWDyNFpEgSPw_; pgv_info=ssid=s1842987996; pgv_pvid=5589981100; pt2gguin=o01000088; uin=o01000088; skey=@9KF9yztXZ; ptisp=cnc; RK=mIGmcAlTQS; ptwebqq=1554f1205a9ff09c1ce44' -H 'Connection: keep-alive' --data 'r=%7B%22to%22%3A3820995309%2C%22content%22%3A%22%5B%5C%22{$msg}%5C%22%2C%5B%5C%22font%5C%22%2C%7B%5C%22name%5C%22%3A%5C%22%E5%AE%8B%E4%BD%93%5C%22%2C%5C%22size%5C%22%3A10%2C%5C%22style%5C%22%3A%5B0%2C0%2C0%5D%2C%5C%22color%5C%22%3A%5C%22000000%5C%22%7D%5D%5D%22%2C%22face%22%3A0%2C%22clientid%22%3A53999199%2C%22msg_id%22%3A45070001%2C%22psessionid%22%3A%228368046764001d636f6e6e7365727665725f77656271714031302e3133332e34312e383400001ad00000066b026e040015808a206d0000000a406172314338344a69526d000000285918d94e66218548d1ecb1a12513c86126b3afb97a3c2955b1070324790733ddb059ab166de6857%22%7D'";
    $content = $autologin->execCurlWithCookie($curl1);
    var_dump($content);
    sleep(rand(3, 5));
}
