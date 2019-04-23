<?php
use Illuminate\Support\Facades\Redis;
    function  sdk_accesstoken(){
        $key = 'sdk_accesstoken';
        $token = Redis::get($key);
        if($token){
            echo "有:";
        }else{
            echo "没有  添加:";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSECRET');
            $response = file_get_contents($url);
            $arr  = json_decode($response,true);
            Redis::set($key,$arr['access_token']);
            Redis::expire($key,3600);
            $token = $arr['access_token'];
        }
        return $token;
    }


    function sdk_ticket(){
        $key = 'sdk_ticket';
        $sdk_accesstoken = sdk_accesstoken();
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$sdk_accesstoken.'&type=jsapi';
        $ticketinfo = json_decode(file_get_contents($url),true);
        print_r($ticketinfo);
        if(isset($ticketinfo['ticket'])){
            Redis::set($key,$ticketinfo['ticket']);
            Redis::expire($key,3600);
        }else{
            return false;
        }
    }