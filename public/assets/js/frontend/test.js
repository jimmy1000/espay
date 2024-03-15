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
        index:function () {

            //本地验证未通过时提示
            $("#test-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#test-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = data.payurl;
                }, 1000);
                return false;
            });


        }
    };
    return Controller;
});