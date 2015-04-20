<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
//	2|0cfb6e64|bf5f791101d80c059fd8e84fb8c30b18|1425200731
//	2|a3b26539|1016724cae9107583091e312178a0045|1425200731

$url = 'https://simpalsid.com/user/login?project_id=999a46c6-e6a6-11e1-a45f-28376188709b';
$xsrf = '2|413b9670|fcf0a32d69c86a29f39ed895bf60a20f|1426016086';
$login = 'snakext';
$pass = 'xexexaxa';
$remember = '1';

$uagent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0';

if ($curl = curl_init()) {

    $curlOptions = array(
        CURLOPT_URL => $url,
        CURLOPT_VERBOSE => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS => array(
            '_xsrf' => $xsrf,
            'login' => $login,
            'password' => $pass,
            'remember' => $remember,
        ),
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPHEADER => array(
            'Host: simpalsid.com',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',
            'Referer: https://simpalsid.com/user/login?project_id=999a46c6-e6a6-11e1-a45f-28376188709b',
            'Cookie: _xsrf=2|413b9670|fcf0a32d69c86a29f39ed895bf60a20f|1426016086; foreign_cookie=1',
            'Connection: keep-alive',
        ),
//        CURLOPT_COOKIEFILE => 'cookie.txt',
//        CURLOPT_COOKIEJAR => 'cookie.txt',
    );
    curl_setopt_array($curl, $curlOptions);
    $result = curl_exec($curl);
//    echo $result;
    preg_match("/auth=\"(.*)\"/", $result, $matches);
    $auth = $matches[1];
//    echo $auth;
    curl_close($curl);
}


//if ($curl = curl_init()) {
//
//    $curlOptions = array(
//        CURLOPT_URL => $url,
//        CURLOPT_RETURNTRANSFER => true,
//        CURLOPT_POST => false,
//        CURLOPT_SSL_VERIFYPEER => false,
//        CURLOPT_HEADER => 1,
//        CURLOPT_HTTPHEADER => array(
//            'Host: simpalsid.com',
//            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0',
//            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
//            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
//            'Accept-Encoding: gzip, deflate',
//            'Referer: https://simpalsid.com/user/login?project_id=999a46c6-e6a6-11e1-a45f-28376188709b',
//            'Cookie: _xsrf=2|64b37506|d717627369901767f790f32dd08b107a|1425200731; foreign_cookie=1;',
//            'Connection: keep-alive',
//        ),
//        CURLOPT_COOKIE => "auth={$auth};",
////        CURLOPT_VERBOSE => true,
//        CURLOPT_COOKIEFILE => 'cookie.txt',
//        CURLOPT_COOKIEJAR => 'cookie.txt',
//    );
//    curl_setopt_array($curl, $curlOptions);
//    $result = curl_exec($curl);
////    var_dump($result);
//    curl_close($curl);
//}

if ($curl2 = curl_init()) {

    $curlOptions = array(
        CURLOPT_URL => 'https://999.md',
        CURLOPT_VERBOSE => true,
//        CURLOPT_COOKIEFILE => 'cookie.txt',
//        CURLOPT_COOKIEJAR => 'cookie.txt',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPHEADER => array(
            "Cookie: auth=\"{$auth}\"; expires=Tue, 24 Mar 2015 19:46:51 GMT; httponly; Path=/"
        ),
    );
    curl_setopt_array($curl2, $curlOptions);
    $result = curl_exec($curl2);
    echo $result;
//    var_dump($result);
    curl_close($curl2);
}






/**
 * https://999.md/add?category=transport&subcategory=transport%2Fsnowmobiles-and-jet-ski&offer_type=776
12	test
13	test
16	37376767049
7	12900
ad_id	
agree	1
category_url	transport
country_prefix	373
file	
form_key	9d88eb72dbbb4afcbbc7358b2dd20fc8
number	
offer_type	776
subcategory_url	transport/snowmobiles-and-jet-ski
 */


//  Accept	text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
//Accept-Encoding	gzip, deflate
//Accept-Language	ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3
//Connection	keep-alive
//Cookie	__utma=117578817.117283073.1425136766.1425212370.1425239113.6; __utmz=117578817.1425136766.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __utmc=117578817; auth=5c074592d593d7e3a591f9be11ffcd3f67ae82ef7994b86464b8e1d2ff7d074289d5dc5001867149285b4459b017f290e8dcbfd01681da7a45f3ed22660dd4e2ef53117b592be6785e9673ca1000ad1de9b37e9037c929fe4f65a755de123757b166abc8902346bd2ba6d8758f85a34400e60b595a47eff82f844ac5a58aae7f942c506e6700a631bc69af1a5c517d8ed79758b2bd8602e0aa1d53afb785439708b312b0c6d2fa8277de6a3ace70df8b; _ym_visorc_23318743=b; __utmb=117578817.2.10.1425239113; __utmt=1
//Host	999.md
//Referer	https://999.md/add?category=transport&subcategory=transport%2Fsnowmobiles-and-jet-ski&offer_type=776
//User-Agent	Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0
// 

//auth=""; expires=Tue, 24 Mar 2015 19:46:51 GMT; httponly; Path=/ 