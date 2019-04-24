<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>购物车</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <h1>商品详情:</h1>
        ------------------------------------------------------<br>
        <h3 style="color: red">浏览次数：{{$viewa}}</h3>
        <h3 style="color: red">商品id：    {{$list['goods_id']}}</h3>
        <h3 style="color: red">商品名称：{{$list['goods_name']}}</h3>
        <h3 style="color: red">商品价格：{{$list['self_price']}}</h3>
        ------------------------------------------------------<br>
        <h1>商品浏览次数排行:</h1>
        @foreach($a as $k=>$v)
            <h3 style="color: red">ID:{{$v['goods_id']}}----------名称:{{$v['goods_name']}}</h3><br>
        @endforeach
        ------------------------------------------------------<br>
        <h1>浏览历史:</h1>
        @foreach($b as $k=>$v)
            <h3 style="color: red">ID:{{$v['goods_id']}}----------商品名称：{{$v->goods_name}}----------商品价格：￥{{$v->self_price}}</h3>
        @endforeach
    </div>

</div>
</body>
</html>
