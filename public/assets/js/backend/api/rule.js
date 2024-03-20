define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'api/rule/index' + location.search,
                    add_url: 'api/rule/add',
                    edit_url: 'api/rule/edit',
                    del_url: 'api/rule/del',
                    multi_url: 'api/rule/multi',
                    table: 'api_rule',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'apitype.name', title: '接口类型',operate:'LIKE'},
                        {field: 'type', title: __('Type'), searchList: {"0":__('Type 0'),"1":__('Type 1'),"2":__('Type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {

            dragevent:function(){
                // 每次更新完毕都要注册一次事件
                require(['dragsort'], function (undefined) {
                    //拖拽排序
                    $("#account_ids table").dragsort({
                        itemSelector: 'tr',
                        dragSelector: ".btn-dragsort",
                        dragEnd: function () {
                        },
                        placeHolderTemplate: "<tr></tr>"
                    });
                });
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));

                Controller.api.dragevent();
                //绑定支付类型选择事件重新渲染下方的table
                $(document).on('change','#api_type_container .sp_input',function () {

                    var api_type_id = $(this).closest(".sp_container").find(".sp_hidden").val();

                    Fast.api.ajax({
                        url:Fast.api.fixurl('api/type/getAccount'),
                        data:{id:api_type_id},
                        type:'POST'
                    },function (resp) {
                       //渲染表单
                        var html = Template('params-tpl',{
                            params:resp
                        });
                        for(var i in resp){

                        }
                        Controller.api.dragevent();
                        $("#account_ids").html(html);
                        return false;

                    });

                })
            }
        }
    };
    return Controller;
});