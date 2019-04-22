<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Weixin\WXBizDataCryptController;
use Illuminate\Support\Str;
use App\Model\Order;


class WxPayController extends Controller
{
    public $unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';//统一下单接口
    public $notify_url = 'http://1809gaoxiangdong.comcto.com/Weixin/notify'; //支付后回调
    public $values = [];



    public function wxpay(){
        $orderid = intval($_GET['order_id']);
        $orderinfo = Order::where(['order_id'=>$orderid])->first();
        if(!$orderinfo){
            die("订单不存在");
        }
        $money = 1;   //支付的金额  以分为准
        $order_id = time().'_gaoxiangdong_'.mt_rand(1111,9999);   //测试订单号  当前时间+随机数
        $order_info = [
            'appid'         =>  env('WEIXIN_APPID_0'),      //微信支付绑定的服务号的APPID
            'mch_id'        =>  env('WEIXIN_MCH_ID'),       // 商户ID
            'nonce_str'     => Str::random(16),             // 随机字符串
            'sign_type'     => 'MD5',
            'body'          => '测试-'.mt_rand(1111,9999).Str::random(6),
            'out_trade_no'  => $order_id,                       //本地订单号
            'total_fee'     => $money,                          //支付金额
            'spbill_create_ip'  => $_SERVER['REMOTE_ADDR'],     //客户端IP
            'notify_url'    => $this->notify_url,               //通知回调地址
            'trade_type'    => 'NATIVE'                         // 交易类型
        ];
        $this->values = $order_info;
        $this->SetSign();
        $xml = $this->Xml(); //将数组转化为xml
        $res = $this->postXmlCurl($xml, $this->unifiedorder_url, $useCert = false, $second = 30);
        $da = simplexml_load_string($res);
        $data = [
          'code_url' => $da->code_url,
          'order_id' => $orderid
        ];
        return view('Weixin.pay',$data);
    }
    public function SetSign()
    {
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }
    //xml
    protected function Xml()
    {
        if(!is_array($this->values)
            || count($this->values) <= 0)
        {
            die("数据异常！");
        }
        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    protected function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    private  function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //结果为字符串输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            die("curl出错，错误:$error");
        }
    }
    //签名
    private function MakeSign()
    {
        //一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //二：在string后加入KEY
        $string = $string . "&key=".env('WEIXIN_MCH_KEY');
        //三：MD5加密
        $string = md5($string);
        //四：所有字符转为大写
        $res = strtoupper($string);
        return $res;
    }


    //支付回调
    public function notify(){
        $data = file_get_contents("php://input");
        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_notice.log',$log_str,FILE_APPEND);
        $xml = simplexml_load_string($data);
        if($xml->result_code=='SUCCESS' && $xml->return_code=='SUCCESS'){      //微信支付成功回调
            //验证签名
            $sign = true;
            if($sign){
                //签名验证成功
                $order_status = strtotime($xml->time_end);
                Order::where(['order_no'=>$xml->out_trade_no])->update(['pay_amount'=>$xml->cash_fee,'pay_status'=>$order_status]);
            }else{
                echo '验签失败，IP: '.$_SERVER['REMOTE_ADDR'];
            }
        }
        $response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo $response;
    }
    public function paysuccess()
    {
        $order_id = $_GET['order_id'];
        echo 'OID: '.$order_id . "支付成功";
    }
}
