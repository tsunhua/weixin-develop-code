<?php

$info="我不知道";
$userid="sdfd";
$url = 'http://www.tuling123.com/openapi/api?key=51add1929e6e97e3f36e5e6efcc46b52&info='."$info"."&userid="."$userid";

$res=json_decode(request($url));


echo $res->text;

//发送curl请求，获取返回数据
function request($url,$is_https = true,$method = 'GET',$data = null){
    $ch = curl_init();
    
    curl_setopt($ch,CURLOPT_URL,$url);
    if($is_https){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);   
    }   
    if($method='POST'){
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    }
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

?>