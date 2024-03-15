define(['jquery'], function ($) {
    // 连接成功
    $(top).on('GoeasyConnetcted', function (e) {

    });
    // 连接失败
    $(top).on('GoeasyConnetFailed', function (error) {

    });
    // 连接断开
    $(top).on('GoeasyDisconneted', function (e) {

    });

    // 前台收到广播消息
    $(top).on('GoeasyUserCommon', function (e, content) {
        Toastr.success("前台收到广播消息:" + content);
    });

    // 前台收到用户消息
    $(top).on('GoeasyUserMsg', function (e, content) {
        Toastr.success("前台收到用户消息:" + content);
    });

    // 后台收到广播消息
    $(top).on('GoeasyAdminCommon', function (e, content) {
        Toastr.success("后台收到广播消息:" + content);
    });

    // 后台收到用户消息
    $(top).on('GoeasyAdminMsg', function (e, content) {
        speechSynthesis.speak(new SpeechSynthesisUtterance(content)); //语音读取文字
        Toastr.success(content);
    });




});