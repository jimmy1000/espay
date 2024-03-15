define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {

        statistics: function (){

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'api/account/statistics',
                    table: 'api_account',
                }
            });
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search:false,
                searchFormVisible: true,
                searchFormTemplate: 'customformtpl',
                columns: [
                    [
                        {field: 'id',title:'通道编号'},
                        {field: 'name',title:'通道名称'},
                        {field: 'allmoney',title:'总和交易金额'},
                        {field: 'successmoney',title: '成功交易金额'},
                        {field: 'allnum',title:'发起笔数'},
                        {field: 'successnum',title: '成功笔数'},
                        {field: 'usernum',title:'使用商户数'},
                        {field:'alllv',title:'成功转化率'},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


        },

        check:function(){
            Controller.api.bindevent();
            
            $(document).on('click','#checkBtn',function () {

                var account_id = $("#account_id option:selected").val();
                var channel = $("#api_type_id option:selected").val();
                var amount = $("#money").val();
                var params = $("#params").val();

                if(!account_id || !channel || !amount){
                    Toastr.error('请选择接口、通道以及填写金额');
                }
                //获取参数
                Fast.api.ajax({
                    url:Fast.api.fixurl('api/account/check'),
                    data:{id:account_id,channel:channel,amount:amount,params:params},
                    type:'POST'
                },function (data) {
                    Layer.alert(data.result,{
                        icon : 1,
                        title: '检查成功'
                    });
                    //渲染数据
                    return false;
                })
            })
        },
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'api/account/index' + location.search,
                    add_url: 'api/account/add',
                    edit_url: 'api/account/edit',
                    del_url: 'api/account/del',
                    multi_url: 'api/account/multi',
                    table: 'api_account',
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
                        {field: 'id', title: __('Id'),visible:false},
                        {field: 'name', title: __('Name')},
                        {field: 'api_upstream_id', title: __('Api_upstream_id'),visible:false,searchList: $.getJSON('api/upstream/items'),},
                        {field: 'upstream.name', title: '接口上游',searchable:false,formatter:function (value,row,index) {
                            return row['upstream']['name'] + '('+row['upstream']['code']+')'
                            }},
                        {field: 'ifrepay', title: __('Ifrepay'), searchList: {"0":__('Ifrepay 0'),"1":__('Ifrepay 1')}, formatter: Table.api.formatter.normal},
                        {field: 'ifrecharge', title: __('Ifrecharge'), searchList: {"0":__('Ifrecharge 0'),"1":__('Ifrecharge 1')}, formatter: Table.api.formatter.normal},
                        {field: 'domain', title: __('Domain')},
                        {field:'channel_list',title:'通道信息',searchable:false,formatter:function (value, row, index) {

                            var channelList = row.channel_list;
                            var html = '<br/><br/>';
                                for (var i = 0; i < channelList.length; i++) {
                                    html+= channelList[i].apitype.name + '('+channelList[i].apitype.code+')';
                                    html += channelList[i].status == '1' ? '<span style="color: red">【 开启 】</span>' : '【 关闭 】';
                                    html += '&nbsp;&nbsp;费率：'+channelList[i].rate+'%';
                                    html += '&nbsp;&nbsp;上游费率：'+channelList[i].upstream_rate+'%';
                                    html += '&nbsp;&nbsp;充值金额：'+channelList[i].minmoney+'-'+channelList[i].maxmoney;
                                    html += '&nbsp;&nbsp;每日限额：'+ (channelList[i].daymoney > 0 ? channelList[i].daymoney : '不限');
                                    html += '&nbsp;&nbsp;当日交易：'+ channelList[i].todaymoney;
                                    html += '<br/><br/>'
                                }
                                return html;

                            }},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,visible:false},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,visible:false},
                        {field: 'handler', title: __('Handler'),table: table, searchable:false, buttons:[
                                {
                                    name: 'content',
                                    text: '通道以及费率设置',
                                    classname: 'btn btn-xs btn-success btn-channel'
                                }
                            ], events:Controller.events.handler,formatter: Table.api.formatter.buttons},
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
        channel:function(){
            Controller.api.bindevent();
            var layer_index= parent.Layer.getFrameIndex(window.name);
            layer_index && parent.Layer.full(layer_index);
        },
        events:{
            handler:{

                //打开设置费率窗口
                'click .btn-channel':function (e, value, row, index){
                    e.stopPropagation();
                    var title = '费率以及通道设置【'+row.name+'】';
                    var id = row.id;
                    Fast.api.open('api/account/channel/id/'+id,title);

                }

            }
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                $(document).on("change", "#upstream_container .sp_input", function () {
                    var upstream_id = $(this).closest(".sp_container").find(".sp_hidden").val();
                    //获取参数
                    Fast.api.ajax({
                        url:Fast.api.fixurl('api/upstream/get'),
                        data:{id:upstream_id},
                        type:'POST'
                    },function (resp) {
                        var params = resp.params;

                        //修改select的项为数组
                        for (var index in params){
                            var param = params[index];
                            if(param.type == 'select'){
                                params[index].default = params[index].default.split(',')
                            }
                        }
                        var html = Template('params-tpl',{
                            params:params
                        });
                        $("#params").html(html);
                        Form.events.plupload($("form"));
                       //渲染数据
                        return false;
                    })

                });

            }
        }
    };
    return Controller;
});