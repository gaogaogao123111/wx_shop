<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Order extends Model
{
    protected $table = 'shop_order';
    public $timestamps = false;
    public static function orderno($user_id)
    {
        $order_no = date("ymdH").'-';
        $str = time() . $user_id . rand(1111,9999) . Str::random(16);
        $order_no .=  substr(md5($str),5,16);
        return $order_no;
    }
}
