<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Goods;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
class GoodsdetailController extends Controller
{
    public function detail($goods_id=0){
        $goods_id = intval($goods_id);
        if(!$goods_id){
            header('Refresh:3;url=/');
            die("参数错误");
        }
        $list = Goods::where(['goods_id'=>$goods_id])->first();
        if($list==NULL){
            header('Refresh:3;url=/');
            die("商品不存在");
        }

        //商品点击刷新加1
        $key='paihang';
        $viewa = Redis::incr($key,$goods_id);

        //排行
        $key ='ss:goods:view';
        Redis::zAdd($key,$viewa,$goods_id);
//        $list1 = Redis::zRangeByScore($key,0,10000,['withscores'=>true]);//正序
        $list2 = Redis::zRevRange($key,0,10000,true);//倒序
        $a=[];
        foreach ($list2 as $k=>$v) {
            $where=[

                'goods_id'=>$k
            ];
            $a[]=Goods::where($where)->first();
        }
//        var_dump($a);die;
        //浏览记录
        $history_key='history:goods_id:view:'.Auth::id();
        Redis::zAdd($history_key,time(),$goods_id);
        $goods_id=Redis::zRevRange($history_key,0,100000000000,true);//倒序
        $b=[];
        foreach ($goods_id as $k=>$v) {
            $where=[
                'goods_id'=>$k
            ];
            $b[]=Goods::where($where)->first();
        }

        $data = [
          'list'=>$list,
          'viewa'=>$viewa,
        ];

        return view('Goods/goodsdetail',$data,compact('a','b'));

    }


    //排行实例
    public function getsort(){
        $key = 'ss:goods:view';
        $list1 = Redis::zRangeByScore($key,0,10000,['withscores'=>true]);
        echo "<pre>";print_r($list1);echo "</pre>";
        $list2 = Redis::zRevRange($key,0,10000,true);
        echo "<pre>";print_r($list2);echo "</pre>";
    }


}
