define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'api/upstream/index' + location.search,
                    add_url: 'api/upstream/add',
                    edit_url: 'api/upstream/edit',
                    del_url: 'api/upstream/del',
                    multi_url: 'api/upstream/multi',
                    table: 'api_upstream',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();



            var template = '                <tr class="params-item">\n' +
                '                    <td>\n' +
                '                        <input type="text" name="row[params][index][name]" class="form-control" placeholder="字段名称" data-rule="required"/>\n' +
                '                    </td>\n' +
                '                    <td>\n' +
                '                        <input type="text" name="row[params][index][code]" class="form-control" placeholder="英文标识" data-rule="required"/>\n' +
                '                    </td>\n' +
                '                    <td>\n' +
                '                       <select class="form-control" name="row[index][0][code]">\n' +
                '                           <option value="input">文本框</option>\n' +
                '                           <option value="text">多行文本</option>\n' +
                '                           <option value="select">下拉框</option>\n' +
                '                           <option value="checkbox">复选框</option>\n' +
                '                       </select>\n' +
                '                    </td>\n' +
                '                    <td>\n' +
                '                        <input type="text" name="row[params][index][default]" class="form-control" />\n' +
                '                    </td>\n' +
                '                    <td>\n' +
                '                        <button type="button" class="del btn btn-danger">删除</button>\n' +
                '                    </td>\n' +
                '                </tr>';
            $(document).on('click', '.operate-item .btn', function () {
                //获取当前参数的数量
                var table_item_index = $('table .params-item').size();
                var html = template.replace(/index/g,table_item_index);
                $(html).insertBefore('.operate-item');
            })

            $(document).on('click','.params-item .del',function () {
                var table_item_index = $('table .params-item').size();
                if(table_item_index == 1){
                    return;
                }
                $(this).parent().parent().remove();
            })





        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));

                    //默认全屏显示
                    var index = parent.Layer.getFrameIndex(window.name);
                    index && parent.Layer.full(index);
            }
        }
    };
    return Controller;
});