<?php

namespace App\Http\Controllers\Weixin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Model\Weixin\Weixin;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class WeixinController extends Controller
{

    //处理首次接入GET请求
    public function valid(){
        echo $_GET['echostr'];
    }
    //接收微信推送 post
    public function event()
    {
        $content = file_get_contents("php://input");
        $time = date('Y-m-d H:i:s');
        $str = $time . $content . "\n";
        file_put_contents("logs/liu_lan.log.log", $str, FILE_APPEND);
        $data = simplexml_load_string($content);
        $openid = $data->FromUserName;   //用户openid
        $wxid = $data->ToUserName;    //公总号id
        $event = $data->Event;
        $msgtype = $data->MsgType;      //消息类型
        $content = $data->Content;
        $value = '';
        $client = new Client();
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
             echo  "<xml>
               <ToUserName><![CDATA['.$wxid.']]></ToUserName>
               <FromUserName><!['.CDATA[$openid.']]></FromUserName>
               <CreateTime>'.time().'</CreateTime>
               <MsgType><![CDATA[news]]></MsgType>
               <ArticleCount>1</ArticleCount>
               <Articles>
                                <item>
                       <Title><![CDATA['.$v->goods_name.']]></Title>
                       <Description><![CDATA['.$v->goods_desc.']]></Description>
                       <PicUrl><![CDATA['.'http://1809gaoxiangdong.comcto.com/img/12.jpg'.']]></PicUrl>
                       <Url><![CDATA['.'http://1809gaoxiangdong.comcto.com/Goods/detail/?goods_id='.$v->goods_id.']]></Url>
                        </item>
                        </Articles>
                </xml>";
        }
    }
    //获取微信用户信息
    public function getuser($openid){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".sdk_accesstoken()."&openid=".$openid."&lang=zh_CN";
        $data = file_get_contents($url);
        $aa = json_decode($data,true);
        return  $aa;
    }
}
