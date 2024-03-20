define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function ($, undefined, Frontend, Form, Template) {
    var validatoroptions = {
        timely:0,
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        show:function () {
            $(document).ready(function () {
                var md = $("#key").val();
                var ddh = $('#ordernum').text();
                var postflag = 0;
                var returnUrl = $('#returnUrl').val();
                $(function () {
                    timer(360);
                });
                $('#orderDetail .arrow').click(function (event) {
                    if ($('#orderDetail').hasClass('detail-open')) {
                        $('#orderDetail .detail-ct').slideUp(500, function () {
                            $('#orderDetail').removeClass('detail-open');
                        });
                    } else {
                        $('#orderDetail .detail-ct').slideDown(500, function () {
                            $('#orderDetail').addClass('detail-open');
                        });
                    }
                });
                //定时检测订单支付情况
                var myTimer;
                function timer(intDiff) {
                    var i = 0;
                    myTimer = window.setInterval(function () {
                        i++;
                        var day = 0,
                            hour = 0,
                            minute = 0,
                            second = 0;//时间默认值
                        if (intDiff > 0) {
                            day = Math.floor(intDiff / (60 * 60 * 24));
                            hour = Math.floor(intDiff / (60 * 60)) - (day * 24);
                            minute = Math.floor(intDiff / 60) - (day * 24 * 60) - (hour * 60);
                            second = Math.floor(intDiff) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
                        }
                        if ((intDiff-1) % 20 == 0) {
                            checkOrder();
                        }
                        if (minute <= 9)
                            minute = '0' + minute;
                        if (second <= 9)
                            second = '0' + second;
                        $('#hour_show').html('<s id="h"></s>' + hour + '时');
                        $('#minute_show').html('<s></s>' + minute + '分');
                        $('#second_show').html('<s></s>' + second + '秒');
                        if (hour <= 0 && minute <= 0 && second <= 0) {
                            qrcode_timeout()
                            clearInterval(myTimer);
                        }
                        intDiff--;
                    }, 1000);
                }
                qrcode_timeout = function () { //二维码超时则停止显示二维码
                    $("#show_qrcode").attr("src", '');
                    $("#show_qrcode").attr("alt", '二维码失效');
                    $("#msg h1").html("支付超时 请重新提交订单"); //过期提醒信息
                }

                checkOrder = function () { //获取订单状态
                    if(postflag==1){
                        return ;
                    }
                    var url = Fast.api.fixurl('/index/ewm/getstatus');
                    Fast.api.ajax({
                        url :url,
                        loading: false,
                        data: {'orderno':ddh, 'key':md, 't': Math.random()}
                    },function (data) {
                        var status = data.status;
                        if(status == '1'){
                            postflag=1;
                            window.location.href = returnUrl;
                        }
                        return false;
                    })
                }
            });


        }
    };
    return Controller;
});