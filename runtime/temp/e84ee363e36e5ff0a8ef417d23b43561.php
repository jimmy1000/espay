<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:78:"/mnt/projects/espay/public/../application/admin/view/user/user/apichannel.html";i:1574294804;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
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
                                <form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

<div class="form-group">
        <div class="alert alert-info">您正在设置:商户【<?php echo $user['merchant_id']; ?>】的通道以及费率。
        </div>
    </div>


    <div class="form-group">
        <table class="table-bordered table text-center">
            <tr>
                <th>接口名称</th>
                <th>费率(0代表使用系统默认的费率)</th>
                <th>接口规则</th>
                <th>是否开启</th>
            </tr>
            <?php if(is_array($api_type_ist) || $api_type_ist instanceof \think\Collection || $api_type_ist instanceof \think\Paginator): if( count($api_type_ist)==0 ) : echo "" ;else: foreach($api_type_ist as $key=>$type): if(empty($user_channel_list[$key])): ?>
            <tr>
                <td><?php echo $type['name']; ?><input type="hidden" name="row[types][]" value="<?php echo $key; ?>"></td>
                <td>
                    <input name="row[<?php echo $key; ?>][rate]" type="text" value="0" class="form-control"/>
                </td>
                <td>
                    <select class="form-control" name="row[<?php echo $key; ?>][rule]">
                        <option value="0">系统默认规则</option>
                        <?php if(is_array($type['rule_list']) || $type['rule_list'] instanceof \think\Collection || $type['rule_list'] instanceof \think\Paginator): if( count($type['rule_list'])==0 ) : echo "" ;else: foreach($type['rule_list'] as $k=>$rule): ?>
                            <option value="<?php echo $rule['id']; ?>"><?php echo $rule['name']; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="row[<?php echo $key; ?>][status]" id="c-switch-<?php echo $key; ?>" value="<?php echo $type['default']; ?>"/>
                    <a href="javascript:;" data-toggle="switcher" class="btn-switcher" data-input-id="c-switch-<?php echo $key; ?>" data-yes="1" data-no="0" >
                        <i class="fa fa-toggle-on text-success <?php if($type['default'] == '0'): ?> fa-flip-horizontal text-gray<?php endif; ?> fa-2x"></i>
                    </a>
                </td>
            </tr>
            <?php else: ?>
            <tr>
                <td><?php echo $type['name']; ?><input type="hidden" name="row[types][]" value="<?php echo $key; ?>"></td>
                <td>
                    <input name="row[<?php echo $key; ?>][rate]" type="text" value="<?php echo $user_channel_list[$key]['rate']; ?>" class="form-control"/>
                </td>
                <td>
                    <select class="form-control" name="row[<?php echo $key; ?>][rule]">
                        <option value="0" <?php if($user_channel_list[$key]['api_rule_id'] == '0'): ?>selected<?php endif; ?>>系统默认规则</option>
                        <?php if(is_array($type['rule_list']) || $type['rule_list'] instanceof \think\Collection || $type['rule_list'] instanceof \think\Paginator): if( count($type['rule_list'])==0 ) : echo "" ;else: foreach($type['rule_list'] as $k=>$rule): ?>
                        <option value="<?php echo $rule['id']; ?>" <?php if($user_channel_list[$key]['api_rule_id'] == $rule['id']): ?>selected<?php endif; ?>><?php echo $rule['name']; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="row[<?php echo $key; ?>][status]" id="c-switch-<?php echo $key; ?>" value="<?php echo $user_channel_list[$key]['status']; ?>"/>
                    <a href="javascript:;" data-toggle="switcher" class="btn-switcher" data-input-id="c-switch-<?php echo $key; ?>" data-yes="1" data-no="0" >
                        <i class="fa fa-toggle-on text-success <?php if($user_channel_list[$key]['status'] == '0'): ?> fa-flip-horizontal text-gray<?php endif; ?> fa-2x"></i>
                    </a>
                </td>
            </tr>
            <?php endif; endforeach; endif; else: echo "" ;endif; ?>
        </table>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <input type="hidden" name="row[id]" value="<?php echo $user['id']; ?>">
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