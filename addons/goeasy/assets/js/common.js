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

        console.log('收到消息')
        Toastr.success("后台收到用户消息:" + content);
    });


    function speak(text) {
        if ('speechSynthesis' in window) {
            var sentence = new SpeechSynthesisUtterance();
            var voices = window.speechSynthesis.getVoices();
            for (var i = 0; i < voices.length; i++) {
                if (voices[i]['name'] == "Alex") {
                    sentence.voice = voices[i];
                }
            }
            sentence.pitch = 1;
            sentence.rate = 0.7;//速度
            sentence.text = text;
            window.speechSynthesis.speak(sentence);
        } else {
            console.log("Oops! Your browser does not support HTML SpeechSynthesis.")
        }
    }

});