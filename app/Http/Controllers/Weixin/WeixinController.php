<?php

namespace App\Http\Controllers\Weixin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Model\Weixin\Weixin;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class WeixinController extends Controller
{

    //处理首次接入GET请求
    public function valid(){
        echo $_GET['echostr'];
    }
    //接收微信推送 post
    public function event()
    {
        $nonceStr = Str::random(10); //随机
        $ticket = sdkticket(); //获取微信
        $timestamp = time(); //当前时间
        $current_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
        $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$current_url";
        $string = sha1($string);
        $jsconfig = [
            'appId' => env('WX_APPID'), //公众号ID
            'timestamp' => $timestamp,
            'nonceStr' => $nonceStr,   //随机字符串
            'signature' => $string,    //签名
        ];
        $da = [
            'jsconfig'  => $jsconfig
        ];
        $content = file_get_contents("php://input");
        $time = date('Y-m-d H:i:s');
        $str = $time . $content . "\n";
        file_put_contents("logs/liu_lan.log.log", $str, FILE_APPEND);
        $data = simplexml_load_string($content);
        $openid = $data->FromUserName;   //用户openid
        $wxid = $data->ToUserName;    //公总号id
        $event = $data->Event;
        $content = $data->Content;//消息
        //扫码关注
        if ($event == 'subscribe') {
            //根据openid判断用户是否已存在
            $localuser = Weixin::where(['openid' => $openid])->first();
            if ($localuser) {
                //用户关注过
                echo '<xml><ToUserName><![CDATA[' . $openid . ']]></ToUserName><FromUserName><![CDATA[' . $wxid . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[' . '欢迎回来 ' . $localuser['nickname'] . ']]></Content></xml>';
            } else {
                //用户关注aa
                //获取用户信息
                $aa = $this->getuser($openid);
                echo '<pre>';
                print_r($aa);
                echo '</pre>';
                //用户信息入户
                $aa_info = [
                    'openid' => $aa['openid'],
                    'nickname' => $aa['nickname'],
                    'sex' => $aa['sex'],
                    'headimgurl' => $aa['headimgurl'],
                    'subscribe_time' => $aa['subscribe_time'],
                ];
                Weixin::insertGetId($aa_info);
                echo '<xml><ToUserName><![CDATA[' . $openid . ']]></ToUserName><FromUserName><![CDATA[' . $wxid . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[' . '欢迎关注 ' . $aa_info['nickname'] . ']]></Content></xml>';
            }
        }else if($content=='最新商品'){
            $v = DB::table('shop_goods')->orderBy('create_time','desc')->first();
             echo  '<xml>
                          <ToUserName><![CDATA['.$openid.']]></ToUserName>
                          <FromUserName><![CDATA['.$wxid.']]></FromUserName>
                          <CreateTime>'.time().'</CreateTime>
                          <MsgType><![CDATA[news]]></MsgType>
                          <ArticleCount>1</ArticleCount>
                          <Articles>
                            <item>
                              <Title><![CDATA['.$v->goods_name.']]></Title>
                              <Description><![CDATA['.$v->goods_desc.']]></Description>
                              <PicUrl><![CDATA['.'http://1809gaoxiangdong.comcto.com/uploads/goodsImg/20190220/3a7b8dea4c6c14b2aa0990a2a2f0388e.jpg'.']]></PicUrl>
                              <Url><![CDATA['.'http://1809gaoxiangdong.comcto.com/Goods/detail/'.$v->goods_id.']]></Url>
                            </item>
                          </Articles>
                        </xml>';
        }
        return view('Goods.goodsaaa',$da);


    }
    //获取微信用户信息
    public function getuser($openid){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".sdk_accesstoken()."&openid=".$openid."&lang=zh_CN";
        $data = file_get_contents($url);
        $aa = json_decode($data,true);
        return  $aa;
    }
}
