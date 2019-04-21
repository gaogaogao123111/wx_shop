<?php

namespace App\Http\Controllers\Weixin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Model\Weixin\Weixin;
use App\Model\Weixin\Qun;
use GuzzleHttp\Client;
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
        is_dir('logs') or mkdir('logs', 0777, true);
        file_put_contents("logs/wx_event.log", $str, FILE_APPEND);
        $data = simplexml_load_string($content);
        $openid = $data->FromUserName;   //用户openid
        $wxid = $data->ToUserName;    //公总号id
        $event = $data->Event;
        $msgtype = $data->MsgType;      //消息类型
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
        }else if($msgtype == 'voice') {      //保存语音文件
            $media_id = $data->MediaId;
            $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=' . $this->token() . '&media_id=' . $media_id;
            $arm = file_get_contents($url);
            $file_name = time() . mt_rand(1111, 9999) . '.amr';
            $arr = file_put_contents('wx/voice/'.$file_name, $arm);
            $data = [
                'openid' => $openid,
                'voice' => 'wx/voice/'.$file_name,
                'subscribe_time' => time()
            ];
            $id = Weixin::insertGetId($data);
            if (!$id) {
                echo "失败";
            } else {
                echo "成功";
            }
        }else if($msgtype == 'image'){
            $media_id = $data->MediaId;
            //url
            $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=' . $this->token() . '&media_id=' . $media_id;
            $img = $client->get($url);
            $headers = $img->getHeaders();//获取响应头信息
            $file_name = $headers['Content-disposition'][0];//获取文件名
            $file_info = rtrim(substr($file_name, -20), '"');//去除 截取
            $a_file_name = 'wx/img/'.substr(md5(time() . mt_rand(1111, 9999)), 5, 8) . '_' . $file_info;//截取加密后的文件名
            Storage::put($a_file_name, $img->getBody());//保存文件
            $data = [
                'img' => $a_file_name,
                'openid' => $openid,
                'subscribe_time' => time(),
            ];
            $id = Weixin::insertGetId($data);
            if (!$id) {
                Storage::delete('wx/image/' . $a_file_name);
                echo "失败";
            } else {
                echo "成功";
            }
        }else if($msgtype == 'text') {
            //自动回复天气
            if (strpos($data->Content,'+天气')) {
                //获取城市名
                $city = explode('+', $data->Content)[0];
                $url = 'https://free-api.heweather.net/s6/weather/now?key=HE1904161045001471&location=' . $city;
                $arr = json_decode(file_get_contents($url), true);
//                echo '<pre>';print_r($arr);echo '</pre>';
                $sheshidu = $arr['HeWeather6'][0]['now']['tmp'];                       //摄氏度
                $fengxiang = $arr['HeWeather6'][0]['now']['wind_dir'];       //风向
                $fengli = $arr['HeWeather6'][0]['now']['wind_sc'];             //风力
                $shidu = $arr['HeWeather6'][0]['now']['hum'];                     //湿度
                $res = "摄氏度：".$sheshidu."风向：".$fengxiang."风力：".$fengli."湿度：".$shidu;
                $xml='<xml>
                      <ToUserName><![CDATA['.$openid.']]></ToUserName>
                      <FromUserName><![CDATA['.$wxid.']]></FromUserName>
                      <CreateTime>.time().</CreateTime>
                      <MsgType><![CDATA[text]]></MsgType>                      
                      <Content><![CDATA['.$res.']]></Content>
                      </xml>';
                echo $xml;
                $data = [
                    'openid' => $openid,
                    'text' => $res,
                    'subscribe_time' =>time()
                ];
                $id = Weixin::insertGetId($data);
                if($id){
                    echo "成功";
                }else{
                    echo "失败";
                }
            }else{
               echo "<xml>
                     <ToUserName><![CDATA['.$openid.']]></ToUserName>
                     <FromUserName><![CDATA['.$wxid.']]></FromUserName>
                     <CreateTime>.time().</CreateTime>
                     <MsgType><![CDATA[text]]></MsgType>
                     <Content><![CDATA[城市信息有误]]></Content>    
                     </xml>";
            }
        }
    }


    //获取token
    public function token(){
        $key = 'wx_access_token';
        $token = Redis::get($key);
        if($token){
            echo "you:";
        }else{
            echo "meiyou:";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSECRET');
            $response = file_get_contents($url);
            $arr  = json_decode($response,true);
            Redis::set($key,$arr['access_token']);
            Redis::expire($key,200);
            $token = $arr['access_token'];
        }
        return $token;
    }
    //获取微信用户信息
    public function getuser($openid){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->token()."&openid=".$openid."&lang=zh_CN";
        $data = file_get_contents($url);
        $aa = json_decode($data,true);
        return  $aa;
    }
    //创建菜单
    public function createmenu()
    {
        // url
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->token();
        // 接口数据
        $post_arr = [
            'button'    => [
                [
                    'type'  => 'click',
                    'name'  => '高祥栋最帅是不是',
                    'key'   => 'key_menu_001'
                ],
                [
                    'type'  => 'click',
                    'name'  => '是',
                    'key'   => 'key_menu_002'
                ],
            ]
        ];
        $json_str = json_encode($post_arr,JSON_UNESCAPED_UNICODE);  //处理中文编码
        // 发送请求
        $clinet = new Client();
        $response = $clinet->request('POST',$url,[      //发送 json字符串
            'body'  => $json_str
        ]);
        //处理响应
        $res_str = $response->getBody();
        $arr = json_decode($res_str,true);
        //判断错误信息
        if($arr['errcode']>0){
            echo "创建失败";
        }else{
            echo "创建成功";
        }
    }




    public function sendmsg($openid_arr,$content){
        $msg = [
           'touser'=>$openid_arr,
           'msgtype'=>"text",
            "text" =>[
                "content" =>$content
            ]
        ];
        $data = json_encode($msg,JSON_UNESCAPED_UNICODE);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->token();
        $client = new Client();
        $res = $client->request('post',$url,[
            'body' => $data
        ]);
        return $res->getBody();
    }
    public function send(){
        $userlist = Weixin::where(['sub_status'=>1])->get()->toArray();
        $openid_arr = array_column($userlist,'openid');
        print_r($openid_arr);
        $msg = "是不是傻";
        $res = $this->sendmsg($openid_arr,$msg);
        if($res){
            echo "发送成功";
        }else{
            echo "发送失败";

        }
    }
}
