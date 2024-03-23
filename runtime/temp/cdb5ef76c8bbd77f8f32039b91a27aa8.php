<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:76:"/mnt/projects/espay/public/../application/admin/view/order/order/detail.html";i:1574294804;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
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
                                <table class="table table-striped">
    <thead>
    <tr>
        <th>内容</th>
        <th>数据</th>
    </tr>
    </thead>
    <tbody>
        <tr><td>订单类型</td><td><?php echo $order['style_text']; ?></td></tr>
        <tr><td>订单状态</td><td><?php echo $order['status_text']; ?></td></tr>
        <tr><td>通知状态</td><td><?php echo $order['notify_status_text']; ?></td></tr>
        <tr><td>发起时间</td><td><?php echo date('Y-m-d H:i:s',$order['createtime']); ?></td></tr>
        <tr><td>发起IP</td><td><?php echo $order->req_ip; ?></td></tr>
        <tr><td>支付时间</td><td><?php echo date('Y-m-d H:i:s',$order['paytime']); ?></td></tr>
        <tr><td>商户订单号</td><td><?php echo $order['orderno']; ?></td></tr>
        <tr><td>系统订单号</td><td><?php echo $order['sys_orderno']; ?></td></tr>
        <tr><td>上游订单号</td><td><?php echo $order['up_orderno']; ?></td></tr>
        <tr><td>订单金额</td><td><?php echo $order['total_money']; ?></td></tr>
        <tr><td>用户获得金额</td><td><?php echo $order['have_money']; ?></td></tr>
        <tr><td>代理获得金额</td><td><?php echo $order['agent_money']; ?></td></tr>
        <tr><td>订单费率</td><td><?php echo $order['rate']; ?>%</td></tr>
        <tr><td>接口费率</td><td><?php echo $order['channel_rate']; ?>%</td></tr>
        <tr><td>接口类型</td><td><?php echo $order['apitype']['name']; ?></td></tr>
        <tr><td>接口上游</td><td><?php echo $order['upstream']['name']; ?></td></tr>
        <tr><td>接口账户</td><td><?php echo $order['account']['name']; ?></td></tr>
        <?php if($order['repair'] == '1'): ?>
        <tr><td>补单时间</td><td><?php echo date('Y-m-d H:i:s',$order['repair_time']); ?></td></tr>
        <tr><td>补单会员编号</td><td><?php echo $order['repair_admin_id']; ?></td></tr>
        <?php endif; ?>
    </tbody>
</table>
<div class="hide layer-footer">
    <label class="control-label col-xs-12 col-sm-2"></label>
    <div class="col-xs-12 col-sm-8">
        <button type="reset" class="btn btn-primary btn-embossed btn-close" onclick="Layer.closeAll();"><?php echo __('Close'); ?></button>
    </div>
</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>