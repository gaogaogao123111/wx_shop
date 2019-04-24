<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Model\Cart;
use App\Model\Order;
use App\Model\Orderdetail;
class OrderController extends Controller
{
    //生成订单
    public function create(){
//        echo Order::orderno(Auth::id());die;
        $goods = Cart::where(['user_id'=>Auth::id(),'session_id'=>Session::getId()])->get()->toArray();
        $order_amount = 0;
        foreach($goods as $k=>$v){
            $order_amount += $v['self_price'];
        }
        $order_info = [
            'user_id' => Auth::id(),
            'session_id'=>Session::getId(),
            'order_no' => Order::orderno(Auth::id()),
            'order_amount'=>$order_amount,
            'create_time' => time()
        ];
        $order = Order::insertGetId($order_info);
        if ($order){
            echo "加入订单表成功";

        }else{
            echo "加入订单表失败";
        }



        //订单详情表
        foreach($goods as $k=>$v){
            $detail = [
                'order_id' => $order,
                'goods_name' => $v['goods_name'],
                'goods_price' => $v['self_price'],
                'user_id'=>Auth::id(),
                'session_id'=>Session::getId()
            ];
            $orderdetail= Orderdetail::insertGetId($detail);
            if ($orderdetail){
                header('Refresh:3;url=/Order/orderlist');
                die("加入订单表成功,跳转至订单列表");
            }else{
                echo "加入订单详情表失败";
            }

        }
    }
    //订单列表
    public function orderList(){
        $orderlist = Order::where(['user_id'=>Auth::id()])->orderBy("order_id","desc")->get()->toArray();
//        var_dump($orderlist);die;
        $data = [
            'orderlist'  => $orderlist
        ];
        return view('Order/orderlist',$data);
    }
    //订单支付状态
    public function paystatus()
    {
        $order_id = intval($_GET['order_id']);
        $info = Order::where(['order_id'=>$order_id])->first();
        $response = [];
        if($info){
            //已支付
            if($info->pay_status>0){
                $response = [
                    'status'    => 0,
                    'msg'       => 'ok'
                ];
            }
//            var_dump($info->toArray());
        }else{
            die("订单不存在");
        }
        die(json_encode($response));
    }




}
