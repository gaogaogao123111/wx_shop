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


    function sdkticket(){
        $key='sdk_ticket';
        $ticket = Redis::get($key);
        if($ticket){
            return $ticket;
        }else{
            $access_token=sdk_accesstoken();
            $url='https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
            $ticket_info = json_decode(file_get_contents($url),true);
            Redis::set($key,$ticket_info['ticket']);
            Redis::expire($key,3600);
            $ticket = $ticket_info['ticket'];
        }
        return $ticket;
    }