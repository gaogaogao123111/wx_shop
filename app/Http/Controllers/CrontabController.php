<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Order;
class CrontabController extends Controller
{
    //计划删除
    public function crontab(){
//        echo __METHOD__;
        $a = Order::all()->toArray();
        foreach($a as $k=>$v){
            if(time()-$v['create_time']>1800 && $v['order_status']==0){
                Order::where(['order_id'=>$v['order_id']])->update(['status'=>1]);
            }
        }
        var_dump($a);
    }
}
