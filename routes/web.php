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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');



Route::get('/Cart', 'CartController@index');//购物车列表
Route::get('/Cart/add/{goods_id?}', 'CartController@add');//购物车添加

Route::get('/Order/create','OrderController@create');//订单生成
Route::get('/Order/orderlist','OrderController@orderList');//订单列表
Route::get('/Order/paystatus','OrderController@paystatus');      //订单支付状态

Route::get('/Pay/wxpay', 'Weixin\WxPayController@wxpay');      //微信支付
Route::post('/Weixin/notify', 'Weixin\WxPayController@notify');      //支付通知回调


Route::get('/Weixin/paysuccess', 'Weixin\WxPayController@paysuccess');      //支付成功