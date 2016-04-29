<?php

$keyword = "生日快乐";
$url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title=".urlencode($keyword)."$$";
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
$data = curl_exec($ch);
curl_close($ch);
try{
    
    $menus =simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
    foreach($menus as $menu){
        if(isset($menu->encode) && isset($menu->decode)
            && !strpos($menu->encode,"baidu.com")
            && !strpos($menu->encode,"12530.com")
            && strpos($menu->decode,".mp3")){
            $result = substr($menu->encode,0,strripos($menu->encode,'/')+1).$menu->decode;
            if(!strpos($result,"?") && !strpos($result,"xcode")){
                $title = $keyword;
                $desc = "";
                $music_url=urldecode($result);
                $HQ_music_url=urldecode($result);
                echo $title."<br/>";
                echo $desc."<br/>";
                echo $music_url."<br/>";
                echo $HQ_music_url."<br/>";
            }
        }
    }
}catch(Exception $e){
    //do nothing
}

?>