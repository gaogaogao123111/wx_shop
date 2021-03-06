<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/url', function () {
    echo urlencode($_GET['url']);
});
Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
//微信支付
Route::get('/Cart', 'CartController@index');//购物车列表
Route::get('/Cart/add/{goods_id?}', 'CartController@add');//购物车添加
Route::get('/Order/create','OrderController@create');//订单生成
Route::get('/Order/orderlist','OrderController@orderList');//订单列表
Route::get('/Order/paystatus','OrderController@paystatus');      //订单支付状态
Route::get('/Pay/wxpay', 'Weixin\WxPayController@wxpay');      //微信支付
Route::get('/Weixin/notify', 'Weixin\WxPayController@notify');      //支付通知回调
Route::get('/Weixin/paysuccess', 'Weixin\WxPayController@paysuccess');      //支付成功
Route::get('/Goods/detail/{goods_id?}', 'GoodsdetailController@detail');//商品详情   排行
Route::get('/Goods/getsort', 'GoodsdetailController@getsort');//排行实例
//sdk
Route::get('/Sdk/sdk', 'SdkController@sdk');
Route::get('/Sdk/img', 'SdkController@img');
//计划
Route::get('/Crontab/crontab','CrontabController@crontab');//删除过去订单
//回复
Route::get('Weixin/valid','Weixin\WeixinController@valid');
Route::any('Weixin/valid','Weixin\WeixinController@event');
//网页授权
Route::get('Weixin/code','Weixin\WeixinController@code');

