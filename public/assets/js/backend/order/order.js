define(['jquery', 'bootstrap', 'backend', 'table', 'form','echarts'], function ($, undefined, Backend, Table, Form,Echarts) {

    var Controller = {

        index: function () {


            var myChart = Echarts.init(document.getElementById('echart'), 'walden');

            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');


            var getChartData= function(){
                Fast.api.ajax(Fast.api.fixurl('order/order/chart'), function (result) {

                    // 指定图表的配置项和数据
                    var option = {
                        title: {
                            text: '实时订单统计',
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                crossStyle: {
                                    color: '#999'
                                }
                            }
                        },
                        legend: {
                            data:['订单总量','成功数量','成功率']
                        },
                        toolbox: {
                            feature: {
                                dataView: {show: true, readOnly: false},
                                magicType: {show: true, type: ['line', 'bar']},
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        xAxis: {
                            data: result.mins,
                            type: 'category',
                            axisPointer: {
                                type: 'shadow'
                            }
                        },
                        yAxis: [
                            {
                                type: 'value',
                                name: '交易量',
                                min: 0,
                                max: 1000,
                                interval: 100,
                                axisLabel: {
                                    formatter: '{value} 单'
                                }
                            },
                            {
                                type: 'value',
                                name: '成功率',
                                min: 0,
                                max: 100,
                                interval: 10,
                                axisLabel: {
                                    formatter: '{value} %'
                                }
                            }
                        ],
                        series: [
                            {
                                name:'订单总量',
                                type:'bar',
                                data:result.allList,
                                label: {
                                    normal: {
                                        show: true,
                                        position: 'top',
                                        formatter:"总数：{c}单"
                                    }
                                },
                            },
                            {
                                name:'成功数量',
                                type:'bar',
                                data:result.succList,
                                label: {
                                    normal: {
                                        show: true,
                                        position: 'top',
                                        formatter:"成功：{c}单"
                                    },

                                },
                            },
                            {
                                name:'成功率',
                                type:'line',
                                yAxisIndex: 1,
                                data:result.succRateList,
                                label: {
                                    normal: {
                                        show: true,
                                        position: 'top',
                                        formatter:"{c}%"
                                    }
                                },
                            }
                        ]
                    };
                    // 使用刚指定的配置项和数据显示图表。
                    myChart.setOption(option,true);
                    return false;
                })
            };

            getChartData();


            $(window).resize(function () {
                myChart.resize();
            });

            //每5分钟获取一次
            setInterval(function () {
                getChartData()
            },1000 * 60 * 5);

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/order/index' + location.search,
                    del_url: 'order/order/del',
                    table: 'order',
                }
            });

            var table = $("#table");

            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {

                $("#todayMoney").text('￥'+data.extend.todayMoney);
                $("#expenseMoney").text('￥'+(data.extend.todayExpenseMoney))

                $("#allMoney").text('￥'+data.extend.allMoney)

                $("#allExpenseMoney").text('￥'+data.extend.allExpenseMoney)

                $("#listMoney").text('￥' + data.extend.listMoney)
                $("#listHaveMoney").text('￥' + data.extend.listHaveMoney)

            });


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id'),searchable:false},
                        {field: 'merchant_id', title: __('Merchant_id')},
                        {field: 'orderno', title: __('Orderno'), operate: 'LIKE'},
                        {field: 'sys_orderno', title: __('Sys_orderno'), operate: 'LIKE'},
                        {field: 'up_orderno', title: __('Up_orderno'), operate: 'LIKE'},
                        {field: 'total_money', title: __('Total_money'), operate: 'BETWEEN'},
                        {field: 'have_money', title: __('Have_money'), operate: 'BETWEEN',searchable:false},
                        {field: 'upstream_money', title:'上游金额', operate: 'BETWEEN',searchable:false},

                        {
                            field: 'style',
                            title: __('Style'),
                            searchList: {"0": __('Style 0'), "1": __('Style 1')},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {"0": __('Status 0'), "1": __('Status 1'), "2": __('Status 2')},
                            formatter: Table.api.formatter.status
                        },
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
                        // {field: 'rate', title: __('Rate'), operate:'BETWEEN'},
                        // {field: 'channel_rate', title: __('Channel_rate'), operate:'BETWEEN'},

                        {field: 'apitype.name', title: '交易类型', searchable: false},
                        {field: 'upstream.name', title: '上游', searchable: false},
                        {field: 'account.name', title: '接口账户', searchable: false},

                        {
                            field: 'api_type_id', title: '接口类型',
                            searchList: $.getJSON('api/type/items'),
                            visible: false
                        },

                        {
                            field: 'api_upstream_id', title: '上游类型',
                            searchList: $.getJSON('api/upstream/items'),
                            visible: false
                        },

                        {
                            field: 'api_account_id', title: '接口账户',
                            searchList: $.getJSON('api/account/items'),
                            visible: false
                        },


                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'paytime',
                            title: __('Paytime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'repair',
                            title: __('Repair'),
                            searchList: {"0": __('Repair 0'), "1": __('Repair 1')},
                            formatter: Table.api.formatter.normal
                        },
                        // {field: 'repair_admin_id', title: __('Repair_admin_id')},
                        // {field: 'repair_time', title: __('Repair_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'req_ip', title: 'ip地址'},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            events:Controller.events.handler,
                            searchable:false,
                            buttons:[
                                {
                                    name: 'detail',
                                    title: '订单详情',
                                    text: '订单详情',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    url: function (row, j) {
                                        return 'order/order/detail/id/'+row.id;
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: '重发通知',
                                    text: '重发通知',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    url: function (row, j) {
                                        return 'order/order/notify/id/'+row.id;
                                    },
                                    hidden:function (row, j) {
                                        return row.status != '1';
                                    },
                                },

                                {
                                    name: 'repair',
                                    title: '手动补单',
                                    text: '手动补单',
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    url: 'order/order/repair',
                                    hidden:function (row, j) {
                                        j.url = 'order/order/repair/id/'+row.id;
                                        return row.status == '1' || row.status == '2' ;
                                    }
                                },
                                {
                                    name: 'chargeback',
                                    title: '手动退单',
                                    text: '手动退单【已成功的订单退单非常危险】',
                                    classname: 'btn btn-xs btn-warning chargeback',
                                    visible:function (row, j) {
                                        return row.status == '1' || row.status == '2';
                                    }
                                },
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //清除未支付的订单
            $(document).on('click','.clear-fail-btn',function () {
                Layer.confirm('确定删除一天之前的所有未支付订单',
                    {icon: 2, title: __('Warning'), shadeClose: true},
                    function (index) {
                        //发送ajajx请求
                        Fast.api.ajax({
                            url:Fast.api.fixurl('order/order/clearfail'),
                        },function (data, ret) {
                            Layer.close(index);
                        })
                    }
                );
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        repair:function(){
            Controller.api.bindevent();
        },
        chargeback:function(){
            Controller.api.bindevent();
        },
        notify:function(){
            Controller.api.bindevent();
            $(document).on('click','#notifyBtn',function () {
                var id = $("#id").val();
                var url = Fast.api.fixurl('order/order/notify/id/'+id);

                Fast.api.ajax(url,function (data) {
                    parent.$(".btn-refresh").trigger("click");
                    // var index = parent.Layer.getFrameIndex(window.name);
                    // parent.Layer.close(index);
                });
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },
        events: {
            // 事件处理
            handler:{
                'click .chargeback':function (e, value, row, index) {
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
                        __('是否确定退掉【%s】该笔订单，请务必告知客户！ ',row['orderno']),
                        {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                        function (index) {
                            var table = $(that).closest('table');
                            var options = table.bootstrapTable('getOptions');
                            //发送ajajx请求
                            Fast.api.ajax({
                                url:Fast.api.fixurl('order/order/chargeback'),
                                data:{
                                    id:row[options.pk]
                                }
                            },function (data, ret) {
                                Layer.close(index);
                            })
                        }
                    );


                },
            }
        }
    };
    return Controller;
});