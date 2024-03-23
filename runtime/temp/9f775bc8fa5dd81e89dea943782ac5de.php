<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:74:"/mnt/projects/espay/public/../application/admin/view/api/account/edit.html";i:1574294804;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
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
            <input id="c-name" class="form-control" name="row[name]" type="text" value="<?php echo $row['name']; ?>">
        </div>
    </div>
    <div class="form-group" id="upstream_container">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Api_upstream_id'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-api_upstream_id" data-rule="required" data-source="api/upstream/index" class="form-control selectpage" name="row[api_upstream_id]" type="text" value="<?php echo $row['api_upstream_id']; ?>">
        </div>
    </div>
    <div id="params">
        <?php foreach($row['upstream']['params'] as $param): if(isset($row['params'][$param['code']])): ?>
        <div class="form-group">
            <label class="control-label col-xs-12 col-sm-2"><?php echo $param['name']; ?>:</label>

            <div class="col-xs-12 col-sm-8">

                <?php if($param['type'] == 'input'): ?>
                <input type="text" name="row[params][<?php echo $param['code']; ?>]" class="form-control" value="<?php echo $row['params'][$param['code']]; ?>">
                <?php endif; if($param['type'] == 'password'): ?>
                <input type="password" name="row[params][<?php echo $param['code']; ?>]" class="form-control" value="<?php echo $row['params'][$param['code']]; ?>">
                <?php endif; if($param['type'] == 'text'): ?>
                <textarea class="form-control" rows="10" name="row[params][<?php echo $param['code']; ?>]"><?php echo $row['params'][$param['code']]; ?></textarea>
                <?php endif; if($param['type'] == 'select'): $option_list=explode(',',$param['default']); ?>
                <select  name="row[params][<?php echo $param['code']; ?>]" class="form-control">
                    <?php foreach($option_list as $option): ?>
                    <option value="<?php echo $option; ?>" <?php if($option == $row['params'][$param['code']]): ?>selected<?php endif; ?>><?php echo $option; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; if($param['type'] == 'upload'): ?>
                <div class="input-group">
                    <input id="c-param-<?php echo $param['code']; ?>" data-rule="" class="form-control" size="50" name="row[params][<?php echo $param['code']; ?>]" type="text" value="<?php echo $row['params'][$param['code']]; ?>">
                    <div class="input-group-addon no-border no-padding">
                        <span><button type="button"  id="plupload-<?php echo $param['code']; ?>" class="btn btn-info plupload" data-input-id="c-param-<?php echo $param['code']; ?>" data-mimetype="text/plain,application/x-mspublisher
,application/x-pkcs12" data-multiple="false" data-multipart='{"private":1}' ><i class="fa fa-upload"></i> 上传【只允许txt,pfx,p12类的文件】</button></span>
                    </div>
                    <span class="msg-box n-right" for="c-row[params][<?php echo $param['code']; ?>]"></span>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; endforeach; ?>
    </div>

    <script type="text/html" id="params-tpl">

        <% for(var i = 0; i < params.length; i++){ %>
        <div class="form-group">
            <label class="control-label col-xs-12 col-sm-2"><%= params[i].name%>:</label>

            <div class="col-xs-12 col-sm-8">
                <!--文本框-->
                <% if(params[i].type == 'input') { %>
                <input type="text" name="row[params][<%= params[i].code %>]" class="form-control" value="<%= params[i].default %>">
                <%}%>
                <!--密码框-->
                <% if(params[i].type == 'password') { %>
                <input type="password" name="row[params][<%= params[i].code %>]" class="form-control" value="<%= params[i].default %>">
                <%}%>
                <!--多行文本-->
                <%if(params[i].type == 'text'){%>
                <textarea class="form-control" rows="10" name="row[params][<%= params[i].code %>]"><%= params[i].default%></textarea>
                <%}%>

                <!--下拉选择框-->
                <%if(params[i].type == 'select'){%>
                <select  name="row[params][<%= params[i].code %>]" class="form-control">
                    <% for(var j = 0; j < params[i].default.length; j++){ %>
                    <option value="<%= params[i].default[j] %>"><%= params[i].default[j] %></option>
                    <% } %>
                </select>
                <%}%>
                <!--上传-->
                <%if(params[i].type == 'upload'){%>
                <div class="input-group">
                    <input id="c-param-<%= params[i].code%>" data-rule="" class="form-control" size="50" name="row[params][<%= params[i].code %>]" type="text" value="">
                    <div class="input-group-addon no-border no-padding">
                        <span><button type="button" id="plupload-<%= params[i].code%>" class="btn btn-info plupload" data-input-id="c-param-<%= params[i].code%>" data-mimetype="text/plain,application/x-mspublisher
,application/x-pkcs12" data-multiple="false" data-multipart='{"private":1}' ><i class="fa fa-upload"></i> 上传【只允许txt,pfx,p12类的文件】</button></span>
                    </div>
                    <span class="msg-box n-right" for="c-row[params][<%= params[i].code %>]"></span>

                </div>
                <%}%>
            </div>
        </div>
        <% } %>

    </script>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Ifrepay'); ?>:</label>
        <div class="col-xs-12 col-sm-8">

            <select  id="c-ifrepay" data-rule="required" class="form-control selectpicker" name="row[ifrepay]">
                <?php if(is_array($ifrepayList) || $ifrepayList instanceof \think\Collection || $ifrepayList instanceof \think\Paginator): if( count($ifrepayList)==0 ) : echo "" ;else: foreach($ifrepayList as $key=>$vo): ?>
                <option value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['ifrepay'])?$row['ifrepay']:explode(',',$row['ifrepay']))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

            <div class="alert alert-warning" style="margin-top: 10px">
                该账户是否具有代付功能。
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Ifrecharge'); ?>:</label>
        <div class="col-xs-12 col-sm-8">

            <select  id="c-ifrecharge" data-rule="required" class="form-control selectpicker" name="row[ifrecharge]">
                <?php if(is_array($ifrechargeList) || $ifrechargeList instanceof \think\Collection || $ifrechargeList instanceof \think\Paginator): if( count($ifrechargeList)==0 ) : echo "" ;else: foreach($ifrechargeList as $key=>$vo): ?>
                <option value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['ifrecharge'])?$row['ifrecharge']:explode(',',$row['ifrecharge']))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

            <div class="alert alert-warning" style="margin-top: 10px">
                该账户是否为内充通道，开启则该通道只能用于充值，一般适用于纯代付商户的充值。
            </div>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Domain'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-domain" data-rule="url" class="form-control" name="row[domain]" type="text" value="<?php echo $row['domain']; ?>">
        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <input type="hidden" name="row[id]" value="<?php echo $row['id']; ?>">
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