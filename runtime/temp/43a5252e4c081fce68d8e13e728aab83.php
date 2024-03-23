<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:75:"/mnt/projects/espay/public/../application/admin/view/api/upstream/edit.html";i:1574294804;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">

    //require 配置
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>
    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !$config['fastadmin']['multiplenav']): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <form id="edit-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Name'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" data-rule="required" class="form-control" name="row[name]" type="text" value="<?php echo $row['name']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Code'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-code" data-rule="required" class="form-control" name="row[code]" type="text" value="<?php echo $row['code']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Params'); ?>:</label>
        <div class="col-xs-12 col-sm-10">
            <dl class="fieldlist" data-name="params" data-template="param-tpl">
                <dd>
                    <ins>名称</ins>
                    <ins>标识</ins>
                    <ins>类型</ins>
                    <ins>默认值</ins>
                </dd>
                <dd>
                    <a href="javascript:;" class="btn  btn-info btn-append"><i class="fa fa-plus"></i> 点这里添加字段</a>
                </dd>
                <dd>
                    <div class="alert alert-danger text-left">
                        1、当您选择下拉框和复选框为字段类型时，请在默认值一栏设置列表项，并且以英文
                        ','隔开，比如说列表的值为微信和支付宝则填写'微信,支付宝',注意没有单引号。<br/>
                        2、如果您对于接口文件的编写有任何疑问，请联系easypay官方。
                    </div>
                </dd>
                <textarea name="params"  class="form-control hide" cols="30" rows="5" disabled>
                    <?php echo json_encode($row['params']); ?>
                </textarea>
                <script type="text/html" id="param-tpl">
                    <dd class="form-inline">
                        <input type="text" name="row[<%=name%>][<%=index%>][name]" class="form-control" value="<%=row['name']%>" size="10">
                        <input type="text" name="row[<%=name%>][<%=index%>][code]" class="form-control" value="<%=row['code']%>" size="30">
                        <select name="row[<%=name%>][<%=index%>][type]" class="form-control select-api-type" data-selected="<%=row['type']%>">
                            <option value="input">文本框</option>
                            <option value="password">密码框</option>
                            <option value="text">多行文本</option>
                            <option value="select">下拉框</option>
                            <option value="checkbox">复选框</option>
                            <option value="upload">文件上传</option>
                        </select>
                        <input type="text" name="row[<%=name%>][<%=index%>][default]" class="form-control" value="<%=row['default']%>" size="30">
                        <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i>&nbsp;删除</span>
                    </dd>
                </script>
            </dl>
        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <input type="hidden" name="row[id]" value="<?php echo $row['id']; ?>" />
            <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>