if (self == top) {
    var goeasyConfig = Config.goeasy;
    require(['jquery', 'https://' + goeasyConfig.cdnhost + '/goeasy.js', '../addons/goeasy/js/common'], function ($, GoEasy, undefined) {
        // 连接属性
        var connection = {
            appkey: goeasyConfig.subkey,
            onConnected: function () {
                if (goeasyConfig.logger == '1') {
                    console.log('成功连接到GoEasy。');
                }
                $(top).trigger('GoeasyConnetcted');
            },
            onConnectFailed: function (error) {
                if (goeasyConfig.logger == '1') {
                    console.log("与GoEasy连接失败，错误编码：" + error.code + "错误信息：" + error.content);
                }
                $(top).trigger('GoeasyConnetFailed');
            },
            onDisconnected: function () {
                if (goeasyConfig.logger == '1') {
                    console.log("与GoEasy连接断开");
                }
                $(top).trigger('GoeasyDisconneted');
            }
        };
        if (goeasyConfig['otp']) {
            connection.otp = goeasyConfig['otp'];
        }

        // 全局化GoEasy以便自定义扩展消息
        top.Goeasy = new GoEasy(connection);

        // 前台消息
        if (goeasyConfig.frontend == '1' && Config.modulename == 'index' && goeasyConfig.userChannelClient) {
            // 广播消息
            top.Goeasy.subscribe({
                channel: goeasyConfig.userChannelCommon,
                onMessage: function (message) {
                    var content = JSON.parse(message.content);
                    if (goeasyConfig.logger == '1') {
                        console.log(content);
                    }
                    $(top).trigger('GoeasyUserCommon', content);
                }
            });
            // 用户消息
            top.Goeasy.subscribe({
                channel: goeasyConfig.userChannelClient,
                onMessage: function (message) {
                    var content = JSON.parse(message.content);
                    if (goeasyConfig.logger == '1') {
                        console.log(content);
                    }
                    $(top).trigger('GoeasyUserMsg', content);
                }
            });
        }

        // 后台消息
        if (goeasyConfig.backend == '1' && Config.modulename == 'admin' && goeasyConfig.adminChannelClient) {
            // 广播消息
            top.Goeasy.subscribe({
                channel: goeasyConfig.adminChannelCommon,
                onMessage: function (message) {
                    var content = JSON.parse(message.content);
                    if (goeasyConfig.logger == '1') {
                        console.log(content);
                    }
                    $(top).trigger('GoeasyAdminCommon', content);
                }
            });
            // 管理员消息
            top.Goeasy.subscribe({
                channel: goeasyConfig.adminChannelClient,
                onMessage: function (message) {
                    var content = JSON.parse(message.content);
                    if (goeasyConfig.logger == '1') {
                        console.log(content);
                    }
                    $(top).trigger('GoeasyAdminMsg', content);
                }
            });
        }
    });
}