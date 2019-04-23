<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
class SdkController extends Controller
{
    public function sdk(){
//        print_r($_SERVER);die;
        $noncestr = Str::random(10); //随机
        $ticket = sdk_ticket();echo "<br>"; //获取微信
        $timetamp = time(); //当前时间
        $current_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
//        echo 'nonceStr: '.$nonceStr;echo '</br>';
//        echo 'ticket: '.$ticket;echo '</br>';
//        echo '$timestamp: '.$timetamp;echo '</br>';
//        echo '$current_url: '.$current_url;echo '</br>';die;
        $string = "jsapi_ticket=$ticket&noncestr=$noncestr&timestamp=$timetamp&url=$current_url";
        $string = sha1($string);
        var_dump($string);
    }

}
