define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/moneylog/index' + location.search,
                    del_url: 'finance/moneylog/del',
                    multi_url: 'finance/moneylog/multi',
                    table: 'user_money_log',
                }
            });

            var table = $("#table");
            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {

                $("#listMoney").text('￥' + data.extend.listMoney)


            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user.merchant_id', title: '商户号'},
                        {field: 'orderno', title: __('Orderno')},
                        {
                            field: 'style',
                            title: '类型',
                            searchList: {"1": '充值订单', "2": '提现订单',"3":'代理佣金'},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'before', title: __('Before'), searchable:false},
                        {field: 'after', title: __('After'), searchable:false},
                        {field: 'memo', title: __('Memo')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});