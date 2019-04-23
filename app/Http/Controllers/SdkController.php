<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
class SdkController extends Controller
{
    public function sdk(){
//        print_r($_SERVER);die;
        $noncestr = Str::random(10); //随机
        $ticket = sdkticket(); //获取微信
        $timestamp = time(); //当前时间
        $current_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
//        echo 'nonceStr: '.$nonceStr;echo '</br>';
//        echo 'ticket: '.$ticket;echo '</br>';
//        echo '$timestamp: '.$timetamp;echo '</br>';
//        echo '$current_url: '.$current_url;echo '</br>';die;
        $string = "jsapi_ticket=$ticket&noncestr=$noncestr&timestamp=$timestamp&url=$current_url";
        $string = sha1($string);
        $jsconfigsdk = [
            'appId' => env('WX_APPID'), //公众号ID
            'timestamp' => $timestamp,
            'nonceStr' => $noncestr,   //随机字符串
            'signature' => $string,    //签名
        ];
        $data = [
            'jsconfigsdk'  => $jsconfigsdk
        ];
        return view('Sdk.sdk',$data);

    }

}
