define([], function () {
    require(['../addons/bootstrapcontextmenu/js/bootstrap-contextmenu', '../addons/bootstrapcontextmenu/js/test'], function (undefined,undefined) {
    if (Config.controllername == 'index' && Config.actionname == 'index') {
        $("body").append(
            '<div id="context-menu">' +
            '<ul class="dropdown-menu" role="menu">' +
            '<li><a tabindex="-1" data-operate="refresh"><i class="fa fa-refresh fa-fw"></i>刷新</a></li>' +
            '<li><a tabindex="-1" data-operate="refreshTable"><i class="fa fa-table fa-fw"></i>刷新表格</a></li>' +
            '<li><a tabindex="-1" data-operate="close"><i class="fa fa-close fa-fw"></i>关闭</a></li>' +
            '<li><a tabindex="-1" data-operate="closeOther"><i class="fa fa-window-close-o fa-fw"></i>关闭其他</a></li>' +
            '<li class="divider"></li>' +
            '<li><a tabindex="-1" data-operate="closeAll"><i class="fa fa-power-off fa-fw"></i>关闭全部</a></li>' +
            '</ul>' +
            '</div>');

        $(".nav-addtabs").contextmenu({
            target: "#context-menu",
            scopes: 'li[role=presentation]',
            onItem: function (e, event) {
                var $element = $(event.target);
                var tab_id = e.attr('id');
                var id = tab_id.substr('tab_'.length);
                var con_id = 'con_' + id;
                switch ($element.data('operate')) {
                    case 'refresh':
                        $("#" + con_id + " iframe").attr('src', function (i, val) {
                            return val;
                        });
                        break;
                    case 'refreshTable':
                        try {
                            if ($("#" + con_id + " iframe").contents().find(".btn-refresh").size() > 0) {
                                $("#" + con_id + " iframe")[0].contentWindow.$(".btn-refresh").trigger("click");
                            }
                        } catch (e) {

                        }
                        break;
                    case 'close':
                        if (e.find(".close-tab").length > 0) {
                            e.find(".close-tab").click();
                        }
                        break;
                    case 'closeOther':
                        e.parent().find("li[role='presentation']").each(function () {
                            if ($(this).attr('id') == tab_id) {
                                return;
                            }
                            if ($(this).find(".close-tab").length > 0) {
                                $(this).find(".close-tab").click();
                            }
                        });
                        break;
                    case 'closeAll':
                        e.parent().find("li[role='presentation']").each(function () {
                            if ($(this).find(".close-tab").length > 0) {
                                $(this).find(".close-tab").click();
                            }
                        });
                        break;
                    default:
                        break;
                }
            }
        });
    }
    $(document).on('click', function () { // iframe内点击 隐藏菜单
        try {
            top.window.$(".nav-addtabs").contextmenu("closemenu");
        } catch (e) {
        }
    });

});
if ($('.cropper', $('form[role="form"]')).length > 0) {
    var allowAttr = [
        'aspectRatio', 'autoCropArea', 'cropBoxMovable', 'cropBoxResizable', 'minCropBoxWidth', 'minCropBoxHeight', 'minContainerWidth', 'minContainerHeight',
        'minCanvasHeight', 'minCanvasWidth', 'croppedWidth', 'croppedHeight', 'croppedMinWidth', 'croppedMinHeight', 'croppedMaxWidth', 'croppedMaxHeight', 'fillColor'
    ];
    String.prototype.toLineCase = function () {
        return this.replace(/[A-Z]/g, function (match) {
            return "-" + match.toLowerCase();
        });
    };

    var btnAttr = [];
    $.each(allowAttr, function (i, j) {
        btnAttr.push('data-' + j.toLineCase() + '="<%=data.' + j + '%>"');
    });
    var btn = '<button class="btn btn-success btn-cropper btn-xs" data-input-id="<%=data.inputId%>" ' + btnAttr.join(" ") + ' style="position:absolute;top:10px;right:15px;">裁剪</button>';
    require(['upload'], function (Upload) {
        //图片裁剪
        $(document).on('click', '.btn-cropper', function () {
            var image = $(this).closest("li").find('.thumbnail').data('url');
            var input = $("#" + $(this).data("input-id"));
            var url = image;
            var data = $(this).data();
            var params = [];
            $.each(allowAttr, function (i, j) {
                if (typeof data[j] !== 'undefined' && data[j] !== '') {
                    params.push(j + '=' + data[j]);
                }
            });
            (parent ? parent : window).Fast.api.open('/addons/cropper/index/cropper?url=' + image + (params.length > 0 ? '&' + params.join('&') : ''), '裁剪', {
                callback: function (data) {
                    if (typeof data !== 'undefined') {
                        var arr = data.dataURI.split(','), mime = arr[0].match(/:(.*?);/)[1],
                            bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
                        while (n--) {
                            u8arr[n] = bstr.charCodeAt(n);
                        }
                        var urlArr = url.split('.');
                        var suffix = 'png';
                        url = urlArr.join('');
                        var filename = url.substr(url.lastIndexOf('/') + 1);
                        var exp = new RegExp("\\." + suffix + "$", "i");
                        filename = exp.test(filename) ? filename : filename + "." + suffix;
                        var file = new File([u8arr], filename, {type: mime});
                        Upload.api.send(file, function (data) {
                            input.val(input.val().replace(image, data.url)).trigger("change");
                        }, function (data) {
                        });
                    }
                },
                area: ["880px", "520px"],
            });
            return false;
        });

        var insertBtn = function () {
            return arguments[0].replace(arguments[2], btn + arguments[2]);
        };
        Upload.config.previewtpl = Upload.config.previewtpl.replace(/<li(.*?)>(.*?)<\/li>/, insertBtn);
        $(".cropper").each(function () {
            var preview = $("#" + $(this).data("preview-id"));
            if (preview.size() > 0 && preview.data("template")) {
                var tpl = $("#" + preview.data("template"));
                tpl.text(tpl.text().replace(/<li(.*?)>(.*?)<\/li>/, insertBtn));
            }
        });
    });
}
require.config({
    paths: {
        'async': '../addons/example/js/async',
        'BMap': ['//api.map.baidu.com/api?v=2.0&ak=mXijumfojHnAaN2VxpBGoqHM'],
    },
    shim: {
        'BMap': {
            deps: ['jquery'],
            exports: 'BMap'
        }
    }
});

require.config({
    paths: {
        'geetest': '../addons/geetest/js/geetest.min'
    }
});

require(['geetest'], function (Geet) {
    var geetInit = false;
    window.renderGeetest = function () {
        $("input[name='captcha']:visible").each(function () {
            var obj = $(this);
            var form = obj.closest('form');
            obj.parent()
                .removeClass('input-group')
                .html('<div class="embed-captcha"><input type="hidden" name="captcha" class="form-control" data-msg-required="请完成验证码验证" data-rule="required" /> </div> <p class="wait show" style="min-height:44px;line-height:44px;">正在加载验证码...</p>');

            Fast.api.ajax("/addons/geetest/index/start", function (data) {
                // 参数1：配置参数
                // 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
                initGeetest({
                    gt: data.gt,
                    https: true,
                    challenge: data.challenge,
                    new_captcha: data.new_captcha,
                    product: Config.geetest.product, // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                    width: '100%',
                    offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
                }, function (captchaObj) {
                    // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
                    geetInit = captchaObj;
                    captchaObj.appendTo($(".embed-captcha", form));
                    captchaObj.onReady(function () {
                        $(".wait", form).remove();
                    });
                    captchaObj.onSuccess(function () {
                        var result = captchaObj.getValidate();
                        if (result) {
                            $('input[name="captcha"]', form).val('ok');
                        }
                    });
                    captchaObj.onError(function () {
                        geetInit.reset();
                    });
                });
                // 监听表单错误事件
                form.on("error.form", function (e, data) {
                    geetInit.reset();
                });
                return false;
            });
        });
    };
    renderGeetest();
});

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
require.config({
    paths: {
        'nkeditor': '../addons/nkeditor/js/customplugin',
        'nkeditor-core': '../addons/nkeditor/nkeditor.min',
        'nkeditor-lang': '../addons/nkeditor/lang/zh-CN',
    },
    shim: {
        'nkeditor': {
            deps: [
                'nkeditor-core',
                'nkeditor-lang'
            ]
        },
        'nkeditor-core': {
            deps: [
                'css!../addons/nkeditor/themes/black/editor.min.css',
                'css!../addons/nkeditor/css/common.css'
            ],
            exports: 'window.KindEditor'
        },
        'nkeditor-lang': {
            deps: [
                'nkeditor-core'
            ]
        }
    }
});
if ($(".editor").size() > 0) {
    require(['nkeditor', 'upload'], function (Nkeditor, Upload) {
        var getImageFromClipboard, getImageFromDrop;
        getImageFromClipboard = function (data) {
            var i, item;
            i = 0;
            while (i < data.clipboardData.items.length) {
                item = data.clipboardData.items[i];
                if (item.type.indexOf("image") !== -1) {
                    return item.getAsFile() || false;
                }
                i++;
            }
            return false;
        };
        getImageFromDrop = function (data) {
            var i, item, images;
            i = 0;
            images = [];
            while (i < data.dataTransfer.files.length) {
                item = data.dataTransfer.files[i];
                if (item.type.indexOf("image") !== -1) {
                    images.push(item);
                }
                i++;
            }
            return images;
        };

        var getImageFromUrl = function (url, callback, outputFormat) {
            var canvas = document.createElement('CANVAS'),
                ctx = canvas.getContext('2d'),
                img = new Image;
            img.crossOrigin = 'Anonymous';
            img.onload = function () {
                var urlArr = url.split('.');
                var suffix = urlArr.pop();
                suffix = suffix.match(/^(jpg|png|gif|bmp|jpeg)$/i) ? suffix : 'png';

                try {
                    canvas.height = img.height;
                    canvas.width = img.width;
                    ctx.drawImage(img, 0, 0);
                    var dataURL = canvas.toDataURL(outputFormat || 'image/' + suffix);

                    var arr = dataURL.split(','), mime = arr[0].match(/:(.*?);/)[1],
                        bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
                    while (n--) {
                        u8arr[n] = bstr.charCodeAt(n);
                    }
                    var filename = url.substr(url.lastIndexOf('/') + 1);
                    var exp = new RegExp("\\." + suffix + "$", "i");
                    filename = exp.test(filename) ? filename : filename + "." + suffix;
                    console.log(filename, suffix, url);
                    var file = new File([u8arr], filename, {type: mime});
                } catch (e) {
                    callback.call(this, null);
                }

                callback.call(this, file);
                canvas = null;
            };
            img.onerror = function (e) {
                callback.call(this, null);
            };
            img.src = url;
        };

        Nkeditor.lang({
            remoteimage: '下载远程图片'
        });
        //远程下载图片
        Nkeditor.plugin('remoteimage', function (K) {
            var editor = this, name = 'remoteimage';
            editor.plugin.remoteimage = {
                download: function (e) {
                    var that = this;
                    var html = that.html();
                    var staging = {}, orgined = {}, index = 0, images = 0, completed = 0, failured = 0;
                    var checkrestore = function () {
                        if (completed + failured >= images) {
                            $.each(staging, function (i, j) {
                                that.html(that.html().replace("<code>" + i + "</code>", j));
                            });
                        }
                    };
                    html.replace(/<code>([\s\S]*?)<\/code>/g, function (code) {
                            staging[index] = code;
                            return "<code>" + index + "</code>";
                        }
                    );
                    html = html.replace(/<img([\s\S]*?)\ssrc\s*=\s*('|")((http(s?):)([\s\S]*?))('|")([\s\S]*?)[\/]?>/g, function () {
                        images++;
                        var url = arguments[3];
                        var placeholder = '<img src="' + Fast.api.cdnurl("/assets/addons/nkeditor/img/downloading.png") + '" data-index="' + index + '" />';
                        //如果是云存储的链接,则忽略
                        if (Config.upload.cdnurl && url.indexOf(Config.upload.cdnurl) > -1) {
                            completed++;
                            return arguments[0];
                        } else {
                            orgined[index] = arguments[0];
                        }
                        //下载远程图片
                        (function (index, url, placeholder) {
                            getImageFromUrl(url, function (file) {
                                if (!file) {
                                    failured++;
                                    that.html(that.html().replace(placeholder, orgined[index]));
                                    checkrestore();
                                } else {
                                    Upload.api.send(file, function (data) {
                                        completed++;
                                        that.html(that.html().replace(placeholder, '<img src="' + Fast.api.cdnurl(data.url) + '" />'));
                                        checkrestore();
                                    }, function (data) {
                                        failured++;
                                        that.html(that.html().replace(placeholder, orgined[index]));
                                        checkrestore();
                                    });
                                }
                            });
                        })(index, url, placeholder);
                        index++;
                        return placeholder;
                    });
                    if (index > 0) {
                        that.html(html);
                    } else {
                        Toastr.info("没有需要下载的远程图片");
                    }
                }
            };
            // 点击图标时执行
            editor.clickToolbar(name, editor.plugin.remoteimage.download);
        });

        $(".editor").each(function () {
            var that = this;
            Nkeditor.create(that, {
                width: '100%',
                filterMode: false,
                wellFormatMode: false,
                allowMediaUpload: true, //是否允许媒体上传
                allowFileManager: true,
                allowImageUpload: true,
                cssPath: Fast.api.cdnurl('/assets/addons/nkeditor/plugins/code/prism.css'),
                cssData: "body {font-size: 13px}",
                fillDescAfterUploadImage: false, //是否在上传后继续添加描述信息
                themeType: typeof Config.nkeditor != 'undefined' ? Config.nkeditor.theme : 'black', //编辑器皮肤,这个值从后台获取
                fileManagerJson: Fast.api.fixurl("/addons/nkeditor/index/attachment/module/" + Config.modulename),
                items: [
                    'source', 'undo', 'redo', 'preview', 'print', 'template', 'code', 'quote', 'cut', 'copy', 'paste',
                    'plainpaste', 'wordpaste', 'justifyleft', 'justifycenter', 'justifyright',
                    'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                    'superscript', 'clearhtml', 'quickformat', 'selectall',
                    'formatblock', 'fontname', 'fontsize', 'forecolor', 'hilitecolor', 'bold',
                    'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', 'image', 'multiimage', 'graft',
                    'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                    'anchor', 'link', 'unlink', 'remoteimage', 'about', 'fullscreen'
                ],
                afterCreate: function () {
                    var self = this;
                    //Ctrl+回车提交
                    Nkeditor.ctrl(document, 13, function () {
                        self.sync();
                        $(that).closest("form").submit();
                    });
                    Nkeditor.ctrl(self.edit.doc, 13, function () {
                        self.sync();
                        $(that).closest("form").submit();
                    });
                    //粘贴上传
                    $("body", self.edit.doc).bind('paste', function (event) {
                        var image, pasteEvent;
                        pasteEvent = event.originalEvent;
                        if (pasteEvent.clipboardData && pasteEvent.clipboardData.items) {
                            image = getImageFromClipboard(pasteEvent);
                            if (image) {
                                event.preventDefault();
                                Upload.api.send(image, function (data) {
                                    self.exec("insertimage", Fast.api.cdnurl(data.url));
                                });
                            }
                        }
                    });
                    //挺拽上传
                    $("body", self.edit.doc).bind('drop', function (event) {
                        var image, pasteEvent;
                        pasteEvent = event.originalEvent;
                        if (pasteEvent.dataTransfer && pasteEvent.dataTransfer.files) {
                            images = getImageFromDrop(pasteEvent);
                            if (images.length > 0) {
                                event.preventDefault();
                                $.each(images, function (i, image) {
                                    Upload.api.send(image, function (data) {
                                        self.exec("insertimage", Fast.api.cdnurl(data.url));
                                    });
                                });
                            }
                        }
                    });
                },
                //FastAdmin自定义处理
                beforeUpload: function (callback, file) {
                    var file = file ? file : $("input.ke-upload-file", this.form).prop('files')[0];
                    Upload.api.send(file, function (data) {
                        var data = {code: '000', data: {url: Fast.api.cdnurl(data.url)}, title: '', width: '', height: '', border: '', align: ''};
                        callback(data);
                    });

                },
                //错误处理 handler
                errorMsgHandler: function (message, type) {
                    try {
                        console.log(message, type);
                    } catch (Error) {
                        alert(message);
                    }
                }
            });
        });
    });
}

});
