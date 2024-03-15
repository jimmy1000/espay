define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'pay/pay/index' + location.search,
                    table: 'pay',
                    multi_url: 'pay/pay/multi',

                }
            });


            var table = $("#table");


            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                $("#todayMoney").text('￥' + data.extend.todayMoney);
                $("#todaySuccMoney").text('￥' + (data.extend.todaySuccMoney))
                $("#allMoney").text('￥' + data.extend.allMoney)
                $("#allSuccMoney").text('￥' + data.extend.allSuccMoney)
                $("#listMoney").text('￥' + data.extend.listMoney)
                $("#listChargeMoney").text('￥' + data.extend.listChargeMoney)

            });


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), visible: false},
                        {field: 'merchant_id', title: __('Merchant_id')},
                        {field: 'orderno', title: __('Orderno')},
                        {
                            field: 'style',
                            title: __('Style'),
                            searchList: {"0": __('Style 0'), "1": __('Style 1')},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'money', title: __('Money'), operate: 'BETWEEN'},
                        {field: 'name', title: __('Name')},
                        {field: 'ka', title: __('Ka')},
                        {field: 'bank', title: __('Bank')},
                        {field: 'province', title: __('Province')},
                        {field: 'city', title: __('City')},
                        {field: 'zhihang', title: __('Zhihang')},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "0": __('Status 0'),
                                "1": __('Status 1'),
                                "2": __('Status 2'),
                                "3": __('Status 3')
                            },
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'daifustatus',
                            title: __('Daifustatus'),
                            searchList: {
                                "0": __('Daifustatus 0'),
                                "1": __('Daifustatus 1'),
                                "2": __('Daifustatus 2'),
                                "3": __('Daifustatus 3')
                            },
                            formatter: Table.api.formatter.status
                        },
                        {field: 'charge', title: __('Charge'), operate: 'BETWEEN'},
                        {
                            field: 'notify_status',
                            title: __('Notify_status'),
                            searchList: {
                                "0": __('Notify_status 0'),
                                "1": __('Notify_status 1'),
                                "2": __('Notify_status 2')
                            },
                            formatter: Table.api.formatter.status
                        },
                        {field: 'notify_count', title: __('Notify_count')},
                        // {field: 'req_ip', title: __('Req_ip')},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Controller.events.handler,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'repay',
                                    title: '代付',
                                    text: '已取消订单',
                                    classname: 'btn btn-xs btn-danger btn-cancelled',
                                    visible: function (row, j) {
                                        return row.status == '3'
                                    }
                                },
                                {
                                    name: 'repay',
                                    title: '代付',
                                    text: '代付提交',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    visible: function (row, j) {
                                        return row.status != '3'
                                    },
                                    url: function (row, j) {
                                        return 'pay/pay/handle/id/' + row.id;
                                    }
                                },
                                //取消按钮
                                {
                                    name: 'repay',
                                    title: '取消',
                                    text: '取消订单',
                                    classname: 'btn btn-xs btn-danger btn-cancel',
                                    visible: function (row, j) {
                                        return (row.status == '0' || row.status == '2') && (row.daifustatus != '1' && row.daifustatus != '3')
                                    }
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // //刷新表格
            // $(top).on('GoeasyAdminMsg', function (e, content) {
            //     console.log('刷新')
            //     table.bootstrapTable('refresh');
            // });

        },
        handle: function () {
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                Layer.msg(data.msg, {
                    time: 3000
                }, function () {
                    window.location.reload()
                })
                return false;
            });

            $("#btn-select").data('success', function (data) {

                Layer.msg(data.msg, {
                    time: 3000
                }, function () {
                    window.location.reload()
                })

                return false
            })
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));


            }
        },
        events: {
            // 事件处理
            handler: {

                'click .btn-cancelled': function (e, value, row, index) {
                    Layer.msg('已取消订单禁止任何操作。');
                },
                'click .btn-cancel': function (e, value, row, index) {
                    e.stopPropagation();
                    e.preventDefault();
                    var that = this;
                    var top = $(that).offset().top - $(window).scrollTop();
                    var left = $(that).offset().left - $(window).scrollLeft() - 260;
                    if (top + 154 > $(window).height()) {
                        top = top - 154;
                    }
                    if ($(window).width() < 480) {
                        top = left = undefined;
                    }
                    Layer.confirm(
                        __('是否确定取消【%s】该笔订单，金额:%s 手续费:%s。', row['orderno'], row['money'], row['charge']),
                        {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                        function (index) {
                            var table = $(that).closest('table');
                            var options = table.bootstrapTable('getOptions');
                            //发送ajajx请求
                            Fast.api.ajax({
                                url: Fast.api.fixurl('pay/pay/cancel'),
                                data: {
                                    id: row[options.pk]
                                }
                            }, function (data, ret) {

                                //完事以后刷新表单一下
                                table.bootstrapTable('refresh');
                                Layer.close(index);
                            })
                        }
                    );
                }
            }
        }
    };
    return Controller;
});