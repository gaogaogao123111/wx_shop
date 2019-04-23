<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>images</title>
</head>
<body>
<button id="btn">选择照片</button>
<br>
<img src="" alt="" id="imgs0" width="100">
<br>
<img src="" alt="" id="imgs1"  width="100">
<br>
<img src="" alt="" id="imgs2"  width="100">
<br>
<script src="/js/jquery/jquery-1.12.4.min.js"></script>
<script src="http://res2.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script>
    wx.config({
        // debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: "{{$jsconfigsdk['appId']}}", // 必填，公众号的唯一标识
        timestamp: "{{$jsconfigsdk['timestamp']}}", // 必填，生成签名的时间戳
        nonceStr: "{{$jsconfigsdk['nonceStr']}}", // 必填，生成签名的随机串
        signature: "{{$jsconfigsdk['signature']}}",// 必填，签名
        jsApiList: ['chooseImage','uploadImage'] // 必填，需要使用的JS接口列表
    });
    wx.ready(function(){
        // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
        $("#btn").click(function(){
            //图像接口
            wx.chooseImage({
                count: 3, // 默认9
                sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                success: function (res) {
                    var localIds  = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                    var img = "";
                    $.each(localIds ,function(i,v){
                        img +=v+',';
                        var node = "#imgs"+i;
                        $(node).attr('src',v);
                        console.log(node);
                        //上传图片接口
                        wx.uploadImage({
                            localId: v, // 需要上传的图片的本地ID，由chooseImage接口获得
                            isShowProgressTips: 1, // 默认为1，显示进度提示
                            success: function (msg) {
                                var serverId = msg.serverId; // 返回图片的服务器端ID
                                alert('aid:::::'+serverId);
                                // console.log(serverId);
                            }
                        });
                        wx.downloadImage({
                            serverId: v, // 需要下载的图片的服务器端ID，由uploadImage接口获得
                            isShowProgressTips: 1, // 默认为1，显示进度提示
                            success: function (arr) {
                                var localId = arr.localId; // 返回图片下载后的本地ID
                                alert('bid:::::'+serverId);
                            }
                        });
                    })


                    //传值
                    $.ajax({
                        url : '/Sdk/img?img='+img,     //将上传的照片id发送给后端
                        type: 'get',
                        success:function(d){
                            // console.log(d);
                        }
                    });
                    // console.log(img);
                }
            });
        });

    });

</script>
</body>
</html>