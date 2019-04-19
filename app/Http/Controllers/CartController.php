<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Cart;
use App\Model\Goods;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
class CartController extends Controller
{
    public function index(){
        $cart_list = Cart::where(['user_id'=>Auth::id(),'session_id'=>Session::getId()])->get()->toArray();
        if($cart_list){
            $self_price = 0;
            foreach($cart_list as $k=>$v){
                $g = Goods::where(['goods_id'=>$v['goods_id']])->first()->toArray();
                $self_price +=$g['self_price'];
                $goods_list[]= $g;
            }
            //展示
            $data = [
                'goods_list'=>$goods_list,
                'self_price' =>$self_price
            ];
            return view('Cart/Index',$data);
        }else{
            header('Refresh:3;url=/');
            die("购物车为空,跳转至首页");
        }
    }
    public function add($goods_id=0){
        if(empty($goods_id)){
            header('Refresh:3;url=/Cart');
            die("请选择商品，自动跳转至购物车");
        }
        $goods = Goods::where(['goods_id'=>$goods_id,'is_up'=>1])->first();
        if(empty($goods)){
            header('Refresh:3;url=/Cart');
            die("商品已经被删除，自动跳转至首页");
        }else{
            //添加到购物车
            $cart_info = [
                'goods_id'  => $goods_id,
                'goods_name'    => $goods->goods_name,
                'self_price'    => $goods->self_price,
                'user_id'       => Auth::id(),
                'session_id' => Session::getId()
            ];
            //入库
            $cart_id = Cart::insertGetId($cart_info);
            if($cart_id){
                header('Refresh:3;url=/Cart');
                die("添加购物车成功，自动跳转至购物车");
            }else{
                header('Refresh:3;url=/');
                die("添加购物车失败");
            }

        }
    }
}
