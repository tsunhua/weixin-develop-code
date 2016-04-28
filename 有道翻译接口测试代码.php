<?php

//解决中文乱码问题
header("Content-Type: text/html;charset=utf-8"); 

$keyword = "翻译we";
$word = explode("翻译",$keyword)[1];
echo $word;

$url = 'http://fanyi.youdao.com/openapi.do?keyfrom=dowhiledone&key=1527788024&type=data&doctype=json&version=1.1&q='."$word";
$ch = curl_init();
curl_setopt($ch , CURLOPT_URL , $url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
$res = curl_exec($ch);
curl_close($ch);

$fanyi = json_decode($res);
$text = $fanyi->translation[0];
echo $text;
?>