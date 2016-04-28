<?php


$city = "广州";

$ch = curl_init();
$url = 'http://apis.baidu.com/heweather/weather/free?city='."$city";
$header = array(
    'apikey: eada14f7ad004be64c445de510ed8b44',
);
// 添加apikey到header
curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 执行HTTP请求
curl_setopt($ch , CURLOPT_URL , $url);
$weather = json_decode(curl_exec($ch));
//echo $weather;    
//var_dump($weather);
//-------------执行天气情况判定 start----------

$header = "HeWeather data service 3.0";
$weather3 = $weather->$header;
$status = $weather3[0]->status;
if($status=="ok" || $status=="OK"){
    
    $textTpl = "总体天气：%s \n
                当前气温：%s \n
                最高气温：%s \n
                最低气温：%s \n                
                当前湿度：%s \n
                当前风向：%s \n
                当前风级：%s \n
                日出：%s \n
                日落：%s \n
                ";
$today = $weather3[0]->daily_forecast[0]; 
$now = $weather3[0]->hourly_forecast[0];              
    
    $desc = sprintf($textTpl,$today->cond->txt_d,
                    $now->tmp.'度',
                    $today->tmp->max.'度',
                    $today->tmp->min.'度',
                    $now->hum.'%',
                    $now->wind->dir,
                    $now->wind->sc,
                    $today->astro->sr,
                    $today->astro->ss);
}else{
    $desc = "";
}

echo  $desc;

?>