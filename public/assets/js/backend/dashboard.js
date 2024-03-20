define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {

            //todo 实时成功率的统计图表 websocket

            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');
            //获取七天的订单数
            Fast.api.ajax(Fast.api.fixurl('dashboard/chart'), function (data) {

                // 指定图表的配置项和数据
                var option = {
                    title: {
                        text: '',
                        subtext: ''
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'cross'
                        },
                        padding: [5, 10]
                    },
                    legend: {
                        data: ['成功金额', '成功订单数', '成功率']
                    },
                    toolbox: {
                        show: false,
                        feature: {
                            magicType: {show: true, type: ['stack', 'tiled']},
                            saveAsImage: {show: true}
                        }
                    },
                    xAxis: {
                        data: data.days,
                        boundaryGap: true,
                        axisTick: {
                            show: false
                        }
                    },
                    yAxis: {
                        axisTick: {
                            show: false
                        }
                    },
                    grid: {
                        left: 10,
                        right: 10,
                        bottom: 20,
                        top: 30,
                        containLabel: true
                    },
                    series: [{
                        name: '成功金额', itemStyle: {
                            normal: {
                                color: '#FF005A',
                                lineStyle: {
                                    color: '#FF005A',
                                    width: 2
                                }
                            }
                        },
                        smooth: true,
                        type: 'line',
                        data: data.totalAmountList,
                        animationDuration: 2800,
                        animationEasing: 'cubicInOut'
                    },
                        {
                            name: '成功订单数',
                            smooth: true,
                            type: 'line',
                            itemStyle: {
                                normal: {
                                    color: '#3888fa',
                                    lineStyle: {
                                        color: '#3888fa',
                                        width: 2
                                    },
                                    areaStyle: {
                                        color: '#f3f8ff'
                                    }
                                }
                            },
                            data: data.totalOrdersList,
                            animationDuration: 2800,
                            animationEasing: 'quadraticOut'
                        },
                        {
                            name: '成功率',
                            smooth: true,
                            type: 'line',
                            itemStyle: {
                                normal: {
                                    color: 'green',
                                    lineStyle: {
                                        color: 'green',
                                        width: 2
                                    },
                                    areaStyle: {
                                        color: '#f3f8ff'
                                    }
                                }
                            },
                            data: data.totalSuccessRate,
                            animationDuration: 2800,
                            animationEasing: 'quadraticOut'
                        }]
                };
                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option, true);
                return false;
            })


            $(window).resize(function () {
                myChart.resize();
            });

            $(document).on("click", ".btn-checkversion", function () {
                top.window.$("[data-toggle=checkupdate]").trigger("click");
            });

        }
    };

    return Controller;
});