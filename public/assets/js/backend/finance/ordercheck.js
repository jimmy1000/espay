define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/ordercheck/index' + location.search,
                    table: 'order',
                }
            });

            var table = $("#table");



            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {

                console.log('run');
                $("#total").text(data.total)
                $("#allMoney").text('￥'+data.extend.allMoney)
                $("#haveMoney").text('￥'+data.extend.haveMoney)
                $("#agentMoney").text('￥'+data.extend.agentMoney)

                $("#upstreamMoney").text('￥'+data.extend.upstreamMoney)
                var profitMoney = data.extend.allMoney - data.extend.haveMoney - data.extend.agentMoney - data.extend.upstreamMoney;

                profitMoney = profitMoney.toFixed(2);   //全部保留两位小数

                $("#profitMoney").text('￥'+profitMoney)

            });


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search:false,
                searchFormVisible: true,
                searchFormTemplate: 'customformtpl',
                //初始化查询条件
                queryParams: function (params) {

                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    //var filter = {},op = {};
                    filter.status = "1,2";
                    op.status = "IN";
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    return params;
                },

                columns: [
                    [
                        {checkbox: true},
                        {field: 'merchant_id', title:'商户号'},
                        {field: 'orderno', title:'订单号', searchable:false},
                        {field: 'sys_orderno', title: '系统单号', operate: 'LIKE'},
                        {field: 'up_orderno', title: '上游单号', operate: 'LIKE'},
                        {field: 'total_money', title: '订单金额', operate: 'BETWEEN'},
                        {field: 'have_money', title: '支出金额', operate: 'BETWEEN',searchable:false},
                        {field: 'agent_money', title: '代理金额', operate: 'BETWEEN',searchable:false},


                        {
                            field: 'createtime',
                            title: '添加时间',
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'paytime',
                            title: '支付时间',
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },
        events: {
            // 事件处理
            handler:{
            }
        }
    };
    return Controller;
});