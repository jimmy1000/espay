<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:71:"/mnt/projects/espay/public/../application/admin/view/user/user/add.html";i:1574294804;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
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
        <label for="c-group_id" class="control-label col-xs-12 col-sm-2"><?php echo __('Group'); ?>:</label>
        <div class="col-xs-12 col-sm-10">
            <?php echo $groupList; ?>
        </div>
    </div>


    <div class="form-group">
        <label for="c-username" class="control-label col-xs-12 col-sm-2"><?php echo __('Username'); ?>:</label>
        <div class="col-xs-12 col-sm-10">
            <input id="c-username" data-rule="required" class="form-control" name="row[username]" type="text" value="" placeholder="登录账号">
            <div class="alert alert-info" style="margin-top: 5px">
                请注意尽量不要填写手机号，如何非要填写手机号请保持和手机号一致，否则会造成账号登录错误。
            </div>
        </div>
    </div>


    <div class="form-group">
        <label for="c-nickname" class="control-label col-xs-12 col-sm-2"><?php echo __('Nickname'); ?>:</label>
        <div class="col-xs-12 col-sm-10">
            <input id="c-nickname" data-rule="required" class="form-control" name="row[nickname]" type="text" value="" placeholder="昵称">
        </div>
    </div>


    <div class="form-group">
        <label for="c-password" class="control-label col-xs-12 col-sm-2"><?php echo __('Password'); ?>:</label>
        <div class="col-xs-12 col-sm-10">
            <input id="c-password" data-rule="password" class="form-control" name="row[password]" type="text" value="" placeholder="商户的登录密码" autocomplete="new-password" />
        </div>
    </div>


    <div class="form-group">
        <label for="c-mobile" class="control-label col-xs-12 col-sm-2"><?php echo __('Mobile'); ?>:</label>
        <div class="col-xs-12 col-sm-10">
            <input id="c-mobile" data-rule="required;mobile" class="form-control" name="row[mobile]" type="text" value="" placeholder="商户手机号">
        </div>
    </div>

    <div class="form-group">
        <label  class="control-label col-xs-12 col-sm-2">手机号是否绑定:</label>
        <div class="col-xs-12 col-sm-10">
            <?php echo build_radios('row[c-mobile-mobilebind]',[0=>'未绑定',1=>'绑定']); ?>
        </div>
    </div>

    <div class="form-group">
        <label  class="control-label col-xs-12 col-sm-2">上级代理编号:</label>
        <div class="col-xs-12 col-sm-10">
            <input id="c-agent_id"  data-source="user/user/index" data-field="merchant_id" data-primary-key="merchant_id" data-searchField="merchant_id" data-autoFillResult="true" class="form-control selectpage" name="row[agent_id]" type="text" value="">
        </div>
    </div>


    <div class="form-group">
        <label  class="control-label col-xs-12 col-sm-2">结算类型:</label>
        <div class="col-xs-12 col-sm-10">
            <select name="row[balancestyle]" class="form-control selectpicker">
                <option value="-1" selected>按照系统默认配置</option>
                <option value="D">自然日</option>
                <option value="T">工作日</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label  class="control-label col-xs-12 col-sm-2">结算周期:</label>
        <div class="col-xs-12 col-sm-10">
            <input type="text" name="row[balancetime]" value="-1" class="form-control" data-rule="required" />
            <div class="alert alert-info" style="margin-top: 5px">
                例如T+0这里填0，D+0这里填0，T+1这里填1，与结算类型配合使用，-1代表使用系统默认设置
            </div>
        </div>
    </div>


    <div class="form-group">
        <label  class="control-label col-xs-12 col-sm-2">当日提现比例:</label>
        <div class="col-xs-12 col-sm-10">
            <input type="text" name="row[paylv]" value="-1" class="form-control" data-rule="required" />
            <div class="alert alert-info" style="margin-top: 5px">
                默认-1 按照系统参数配置，如果需要冻结当天20%结算费用，则输入80
            </div>
        </div>
    </div>


    <div class="form-group">
        <label  class="control-label col-xs-12 col-sm-2">提现费率类型:</label>
        <div class="col-xs-12 col-sm-10">
            <select name="row[payrate_type]" class="form-control selectpicker">
                <option value="-1" selected>按照系统默认配置</option>
                <option value="0">按笔收费</option>
                <option value="1">按百分比收费</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label  class="control-label col-xs-12 col-sm-2">提现费率:</label>
        <div class="col-xs-12 col-sm-10">
            <input type="text" name="row[payrate]" value="-1" class="form-control" data-rule="required" />

            <div class="alert alert-info" style="margin-top: 5px">
                单笔1.5元则输入1.5 按照百分比1.5%输入1.5 为0则不扣费 小于0则使用系统参数配置
            </div>
        </div>
    </div>


    <div class="form-group">
        <label  class="control-label col-xs-12 col-sm-2">是否关闭代理收益:</label>
        <div class="col-xs-12 col-sm-10">
            <select name="row[ifagentmoney]" class="form-control selectpicker">
                <option value="-1" selected>按照系统默认配置</option>
                <option value="0">关闭代理收益</option>
                <option value="1">开启代理收益</option>
            </select>
        </div>
    </div>





    <div class="form-group">
        <label for="content" class="control-label col-xs-12 col-sm-2"><?php echo __('Status'); ?>:</label>
        <div class="col-xs-12 col-sm-10">
            <?php echo build_radios('row[status]', ['normal'=>__('Normal'), 'hidden'=>__('锁定')]); ?>
        </div>
    </div>




    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
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