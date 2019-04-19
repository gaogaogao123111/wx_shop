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
        echo Order::orderno(Auth::id());die;
    }
}
