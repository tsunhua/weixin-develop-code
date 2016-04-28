<?php

    $ch = curl_init();
    $url = 'http://apis.baidu.com/showapi_open_bus/showapi_joke/joke_text?page=1';
    $header = array(
        'apikey: eada14f7ad004be64c445de510ed8b44',
    );
    // 添加apikey到header
    curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // 执行HTTP请求
    curl_setopt($ch , CURLOPT_URL , $url);
    $res = curl_exec($ch);
    $xiaohua = json_decode($res);
    
    $contentlist = $xiaohua->showapi_res_body->contentlist;
    
    $title = $contentlist[0]->title;
    $desc = $contentlist[0]->text;
    echo $title;
    echo $desc; 
    //echo $res;
    //var_dump(json_decode($res));
?>