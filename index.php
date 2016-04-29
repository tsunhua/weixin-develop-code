<?php

date_default_timezone_set("Asia/Shanghai");

define("TOKEN", "dowhiledone");
//解决中文乱码问题
//header("Content-Type: text/html;charset=utf-8"); 

$wechatObj = new WechatObj();

//$wechatObj->deleteMenu();

if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}


//$wechatObj->createMenu();
//$wechatObj->deleteMenu();

//$wechatObj->uploadMusic();

class WechatObj{
    public function valid(){
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    //删除菜单
    public function deleteMenu(){
        $token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='."$token";
        echo $this->request($url);
    }
    //上传音乐
    public function uploadMusic($title,$url){
        $token = $this->getAccessToken();
        $url='http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token='."$token".'&type=voice';
        
        $ch = curl_init();

        $data = array('name' => "$title".'.mp3', 'file' => '@'."$url");

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false); //  PHP 5.6.0 后必须开启
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $id= curl_exec($ch);
        
        curl_close($ch);
        return $id;
        
    }

    //查询自定义菜单
    public function getMenu(){
        $token=$this->getAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/menu/get?access_token='."$token";
        echo $this->request($url);
        return $this->request($url);
    }
    
    //创建自定义菜单
    public function createMenu(){
        $token = $this->getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$token";
        $data = '{
     "button":[
     {    
          "type":"click",
          "name":"今日歌曲",
          "key":"V1001_TODAY_MUSIC"
      },
      {
           "type":"click",
           "name":"歌手简介",
           "key":"V1001_TODAY_SINGER"
      },
      {
           "name":"菜单",
           "sub_button":[
           {    
               "type":"view",
               "name":"搜索",
               "url":"http://www.soso.com/"
            },
            {
               "type":"view",
               "name":"视频",
               "url":"http://v.qq.com/"
            },
            {
               "type":"click",
               "name":"赞一下我们",
               "key":"V1001_GOOD"
            }]
       }]
 	}';
        $result = $this->request($url,true,'POST',$data);
        $json = json_decode($result);
    }
    
    
    //发送curl请求，获取返回数据
    public function request($url,$is_https = true,$method = 'GET',$data = null){
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
    //获取Access Token
    public function getAccessToken(){
        $appid = 'wx4ecdd9a16ef022e2';
        $secret='c1a9b31a1d0fb8041c5fd9792f225624';
        $content='';
        $file ='god';
        if(file_exists($file)){
            $content = file_get_contents($file);
            $json = json_decode($content);
            if(time()-filemtime($file) < $json->expires_in){//Access Token有效
               return $json->access_token;
            }
        }
        
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='."$appid".'&secret='."$secret";
            $content = $this->request($url);
            file_put_contents($file,$content);
            $json = json_decode($content);
            return $json->access_token;
    }

    private function checkSignature(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    //响应信息
    public function responseMsg(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            
            $msgType = trim($postObj->MsgType);
            
            switch($msgType){
                case "text":
                    $resultStr = $this->receiveText($postObj);
                break;
                case "event":
                    $resultStr = $this->receiveEvent($postObj);
                break;
                default:
                    $resultStr = "";
                break;
            }
            echo $resultStr;
        }else{
            echo "";
            exit;
        }
    }
    //接收到的是事件
    private function receiveEvent($postObj){
        
        switch($postObj->Event){
            
            case "subscribe":
                $resultStr = "Hello World?";
                break;
            case "CLICK":
                switch($postObj->EventKey){
                    case "V1001_TODAY_MUSIC":
                    $media_id="sOQF0N7czNDHLammCqQ5SKpL0unksDvgCdaXLPmBrRJdWrpwh5ycZPqX0m9R8muE";
                       $resultStr = $this->sendAudio($postObj,$media_id);
                        break;
                    case "V1001_TODAY_SINGER":
                        $title="百度之谜";
                        $desc="先看看再说";
                        $picUrl="https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png";
                        $url="http://www.baidu.com";
                        $resultStr = $this->sendSingleTuwen($postObj,$title,$desc,$picUrl,$url);
                        break;
                    case "V1001_GOOD":
                        $tuwens = array(new Tuwen(),new Tuwen());
                        $tuwens[0]->title = "百度之谜";
                        $tuwens[0]->desc = "先看看再说";
                        $tuwens[0]->picUrl = "https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png";
                        $tuwens[0]->url = "http://www.baidu.com";
                        $tuwens[1]->title = "百度之谜";
                        $tuwens[1]->desc = "先看看再说";
                        $tuwens[1]->picUrl = "https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png";
                        $tuwens[1]->url = "http://www.baidu.com";
                        $resultStr = $this->sendMultipleTuwen($postObj,$tuwens);
                        break;
                }
                break;
            default:
                $resultStr = "";
                break;

        }
        return $resultStr;
        
    }
    //发送单图文消息
    private function sendSingleTuwen($postObj,$title,$desc,$picUrl,$url){
            
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>1</ArticleCount>
                    <Articles>
                    <item>
                    <Title><![CDATA[%s]]></Title> 
                    <Description><![CDATA[%s]]></Description>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <Url><![CDATA[%s]]></Url>
                    </item>
                    </Articles>
                    </xml> ";
        
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, 
                        $title,$desc,$picUrl,$url);
        return $resultStr;   
    }
    
    //发送多图文消息
    private function sendMultipleTuwen($postObj,$tuwenObjs){
        
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
    
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName> 
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>".count($tuwenObjs)."</ArticleCount>
                    <Articles>";
        foreach($tuwenObjs as $obj){
            $textTpl.="<item>
                    <Title><![CDATA[$obj->title]]></Title> 
                    <Description><![CDATA[$obj->desc]]></Description>
                    <PicUrl><![CDATA[$obj->picUrl]]></PicUrl>
                    <Url><![CDATA[$obj->url]]></Url>
                    </item>";
        }         
        $textTpl.="</Articles>
                    </xml>";
        
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time);
        return $resultStr;   
    }

    //发送音频
    private function sendAudio($postObj,$media_id){
        
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[voice]]></MsgType>
                    <Voice>
                    <MediaId><![CDATA[%s]]></MediaId>
                    </Voice>
                    </xml>";
        
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $media_id);
        return $resultStr;
    }
    // 发送文本信息
    private function sendText($postObj,$contentStr){
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
        return $resultStr;
    }
    
    //接收到的是文本
    private function receiveText($postObj){
		
        $keyword = trim($postObj->Content);

        if($keyword == "?" || $keyword == "？"){
            $contentStr = date("Y-m-d H:i:s",time());
            $resultStr = $this->sendText($postObj,$contentStr);
        }else if(strpos($keyword,"天气")){
            
            $city = explode("天气",$keyword)[0];

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
            //-------------执行天气情况判定 start----------
            
            $header = "HeWeather data service 3.0";
            $weather3 = $weather->$header;
            $status = $weather3[0]->status;
            if($status=="ok" || $status=="OK"){
                
                $textTpl = " 总体天气：%s \n\n 当前气温：%s \n\n 最高气温：%s \n\n 最低气温：%s \n\n 当前湿度：%s \n\n 当前风向：%s \n\n 当前风级：%s \n\n 日出：%s  \n\n 日落：%s \n\n 感觉：%s";
                    
                $today = $weather3[0]->daily_forecast[0]; 
                $now = $weather3[0]->hourly_forecast[0];
                $suggestion = $weather3[0]->suggestion;
                $desc = sprintf($textTpl,$today->cond->txt_d,
                                $now->tmp.'度',
                                $today->tmp->max.'度',
                                $today->tmp->min.'度',
                                $now->hum.'%',
                                $now->wind->dir,
                                $now->wind->sc,
                                $today->astro->sr,
                                $today->astro->ss,
                                $suggestion->comf->txt == ""? "暂无":$suggestion->comf->txt);
            }else{
                $desc = "暂未收录";
            }
            //-------------执行天气情况判定 end----------
            
            $title = "$city".'的天气';
            $resultStr = $this->sendSingleTuwen($postObj,$title,$desc,null,null);
        }else if($keyword == "笑话" || $keyword=="joke"){
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
                
                $index = rand(0,sizeof($contentlist));
                
                $title = $contentlist[$index]->title;
                $desc = $contentlist[$index]->text;
                echo $title;
                echo $desc; 
                $resultStr = $this->sendSingleTuwen($postObj,$title,$desc,null,null);
        }else if(strpos($keyword,"翻译") !== false){ //在第0个位置时为false

            $word = explode("翻译",$keyword)[1];
           // echo $word;

            $url = 'http://fanyi.youdao.com/openapi.do?keyfrom=dowhiledone&key=1527788024&type=data&doctype=json&version=1.1&q='.urlencode("$word");
            $ch = curl_init();
            curl_setopt($ch , CURLOPT_URL , $url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            $res = curl_exec($ch);
            //curl_close($ch);

            $fanyi = json_decode($res);
            $text = $fanyi->translation[0];
            //echo $text;
            $resultStr = $this->sendText($postObj,$text);
          
        }else if(strpos($keyword,"播放") !== false){
            $title = explode("播放",$keyword)[1];
            /*
            $title = "生日快乐";
             $desc = "无";
            $music_url="http://store3.nipic.com/file/20121121/227267_09451802797.mp3";
            $HQ_music_url="http://store3.nipic.com/file/20121121/227267_09451802797.mp3";
            //$resultStr = $this->sendText($postObj,'<a href="'."$music_url".'">'.$title.'</a>');
          
            //$resultStr = $this->sendMusic($postObj,$title,$desc,$music_url,$HQ_music_url);
            */
            $url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title=".urlencode($title)."$$";
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            $data = curl_exec($ch);
            curl_close($ch);
            try{
                $menus = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
                //$menus = simplexml_load_string($data,"SimpleXMLElement",LIBXML_NOCDATA);
                foreach($menus as $menu){
                    if(isset($menu->encode) && isset($menu->decode)
                    && !strpos($menu->encode,"baidu.com")
                    && strpos($menu->decode,".mp3")){
                    $result = substr($menu->encode,0,strripos($menu->encode,'/')+1).$menu->decode;
                        if(!strpos($result,"?") && !strpos($result,"xcode")){
                            $desc = "";
                            $music_url=urldecode($result);
                            $HQ_music_url=urldecode($result);
                            //$resultStr = $this->sendText($postObj,'<a href="'."$music_url".'">'.$title.'</a>');
                            //$music_url="http://store3.nipic.com/file/20121121/227267_09451802797.mp3";
                            //$HQ_music_url="http://store3.nipic.com/file/20121121/227267_09451802797.mp3";
                            
                            $resultStr = $this->sendMusic($postObj,$title,$desc,$music_url,$HQ_music_url);
                        }
                    }
                }
            }catch(Exception $e){
                //do nothing
            }
        }else{
            $info=$keyword;
            $userid=$postObj->FromUserName;
            $url = 'http://www.tuling123.com/openapi/api?key=51add1929e6e97e3f36e5e6efcc46b52&loc=广州&info='."$info"."&userid="."$userid";

            $res=json_decode($this->request($url));
            $resultStr=$this->sendText($postObj,$res->text);
        }  
        return $resultStr;
	}
    //发送音乐
    private function sendMusic($postObj,$title,$desc,$music_url,$HQ_music_url){
        
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        
        $my_logo_id = "ASQD1_6C4MeLdbkB2m_u2vyKh64ZlOo80HBIDoguSkvnQiecl4AAyA94w7N8FLEm";
        
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[music]]></MsgType>
                    <Music>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <MusicUrl><![CDATA[%s]]></MusicUrl>
                    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                    <ThumbMediaId><![CDATA[$my_logo_id]]></ThumbMediaId>
                    </Music>
                    </xml>";
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $title,$desc,$music_url,$HQ_music_url);
        return $resultStr;
    }
}


//图文类
 class Tuwen{
    public $title;
    public $desc;
    public $picUrl;
    public $url;
}
?>