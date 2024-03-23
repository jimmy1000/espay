<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:75:"/mnt/projects/espay/public/../application/admin/view/order/order/index.html";i:1711158054;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
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
                                <div class="panel panel-default panel-intro">

    <div class="panel-heading">
        <?php echo build_heading(null,FALSE); ?>
        <ul class="nav nav-tabs" data-field="status">
            <li class="active"><a href="#t-all" data-value="" data-toggle="tab"><?php echo __('All'); ?></a></li>
            <?php if(is_array($statusList) || $statusList instanceof \think\Collection || $statusList instanceof \think\Paginator): if( count($statusList)==0 ) : echo "" ;else: foreach($statusList as $key=>$vo): ?>
            <li><a href="#t-<?php echo $key; ?>" data-value="<?php echo $key; ?>" data-toggle="tab"><?php echo $vo; ?></a></li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>


    <div class="panel-body">

        <div class="row">
            <div class="col-lg-12">
                <div id="echart" style="height:400px;width:100%;"></div>
            </div>
        </div>
        <div class="row" style="margin-top:15px;">

            <div class="col-lg-12">
            </div>
            <div class="col-xs-6 col-md-3">
                <div class="panel bg-blue">
                    <div class="panel-body">
                        <div class="panel-title">
                            <span class="label label-success pull-right">当日成功订单总额</span>
                            <h5>当日金额</h5>
                        </div>
                        <div class="panel-content">
                            <h1 class="no-margins" id="todayMoney">￥0.00</h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3">
                <div class="panel bg-aqua-gradient">
                    <div class="panel-body">
                        <div class="ibox-title">
                            <span class="label label-info pull-right">商户支出，代理支出，上游支出</span>
                            <h5>当日支出</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins" id="expenseMoney">￥0.00</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-6 col-md-3">
                <div class="panel bg-purple-gradient">
                    <div class="panel-body">
                        <div class="ibox-title">
                            <span class="label label-primary pull-right">总金额</span>
                            <h5>平台总金额</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins" id="allMoney">￥0.00</h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3">
                <div class="panel bg-green-gradient">
                    <div class="panel-body">
                        <div class="ibox-title">
                            <span class="label label-primary pull-right">商户支出，代理支出，上游支出</span>
                            <h5>平台总支出</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins" id="allExpenseMoney">￥0.00</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">
                        <a href="javascript:;" class="btn btn-primary btn-refresh" title="<?php echo __('Refresh'); ?>" ><i class="fa fa-refresh"></i> </a>
                        <a href="javascript:;" class="btn btn-danger  clear-fail-btn <?php echo $auth->check('order/order/clearfail')?'':'hide'; ?>"  ><i class="fa fa-trash"></i> 清除未支付订单【一天以上】</a>
                        <a href="javascript:;" class="btn btn-warning">当前列表金额:<span id="listMoney">￥.00</span>&nbsp;&nbsp;用户实际获得金额：<span id="listHaveMoney">￥.00</span></a>

                    </div>
                    <table id="table" class="table table-striped table-bordered table-hover table-nowrap"
                           data-operate-edit="<?php echo $auth->check('order/order/edit'); ?>"
                           data-operate-del="<?php echo $auth->check('order/order/del'); ?>"
                           width="100%">
                    </table>
                </div>
            </div>

        </div>
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