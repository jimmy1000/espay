<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:74:"/mnt/projects/espay/public/../application/admin/view/order/log/detail.html";i:1574294804;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
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
    <tr><td>订单号:</td><td><?php echo $row['orderno']; ?></td></tr>
    <tr><td>商户号:</td><td><?php echo $row['merchant_id']; ?></td></tr>
    <tr><td>来源:</td><td><?php echo $row['http']; ?></td></tr>
    <tr><td>状态:</td><td><?php echo $row['status_text']; ?></td></tr>
    <tr><td>结果:</td><td><?php echo $row['result']; ?></td></tr>
    <tr><td>金额:</td><td><?php echo $row['total_money']; ?></td></tr>
    <tr><td>支付类型:</td><td><?php echo $row['channel']; ?></td></tr>
    <tr><td>ip:</td><td><?php echo $row['ip']; ?></td></tr>
    <tr><td>时间:</td><td><?php echo date('Y-m-d H:i:s',$row['createtime']); ?></td></tr>

    <tr><td>请求参数:</td><td></td></tr>
    <?php if(is_array($row['content']) || $row['content'] instanceof \think\Collection || $row['content'] instanceof \think\Paginator): if( count($row['content'])==0 ) : echo "" ;else: foreach($row['content'] as $key=>$vo): ?>
    <tr><td class=""><?php echo $key; ?>:</td><td><?php echo $vo; ?></td></tr>
    <?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
</table>

<table class="table table-striped">

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