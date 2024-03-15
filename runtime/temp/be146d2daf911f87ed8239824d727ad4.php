<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:73:"/mnt/projects/espay/public/../application/admin/view/dashboard/index.html";i:1598071924;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
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
                                <style type="text/css">
    .sm-st {
        background:#fff;
        padding:20px;
        -webkit-border-radius:3px;
        -moz-border-radius:3px;
        border-radius:3px;
        margin-bottom:20px;
        -webkit-box-shadow: 0 1px 0px rgba(0,0,0,0.05);
        box-shadow: 0 1px 0px rgba(0,0,0,0.05);
    }
    .sm-st-icon {
        width:60px;
        height:60px;
        display:inline-block;
        line-height:60px;
        text-align:center;
        font-size:30px;
        background:#eee;
        -webkit-border-radius:5px;
        -moz-border-radius:5px;
        border-radius:5px;
        float:left;
        margin-right:10px;
        color:#fff;
    }
    .sm-st-info {
        font-size:12px;
        padding-top:2px;
    }
    .sm-st-info span {
        display:block;
        font-size:24px;
        font-weight:600;
    }
    .orange {
        background:#fa8564 !important;
    }
    .tar {
        background:#45cf95 !important;
    }
    .sm-st .green {
        background:#86ba41 !important;
    }
    .pink {
        background:#AC75F0 !important;
    }
    .yellow-b {
        background: #fdd752 !important;
    }
    .stat-elem {

        background-color: #fff;
        padding: 18px;
        border-radius: 40px;

    }

    .stat-info {
        text-align: center;
        background-color:#fff;
        border-radius: 5px;
        margin-top: -5px;
        padding: 8px;
        -webkit-box-shadow: 0 1px 0px rgba(0,0,0,0.05);
        box-shadow: 0 1px 0px rgba(0,0,0,0.05);
        font-style: italic;
    }

    .stat-icon {
        text-align: center;
        margin-bottom: 5px;
    }

    .st-red {
        background-color: #F05050;
    }
    .st-green {
        background-color: #27C24C;
    }
    .st-violet {
        background-color: #7266ba;
    }
    .st-blue {
        background-color: #23b7e5;
    }

    .stats .stat-icon {
        color: #28bb9c;
        display: inline-block;
        font-size: 26px;
        text-align: center;
        vertical-align: middle;
        width: 50px;
        float:left;
    }

    .stat {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        margin-right: 10px; }
    .stat .value {
        font-size: 20px;
        line-height: 24px;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 500; }
    .stat .name {
        overflow: hidden;
        text-overflow: ellipsis; }
    .stat.lg .value {
        font-size: 26px;
        line-height: 28px; }
    .stat.lg .name {
        font-size: 16px; }
    .stat-col .progress {height:2px;}
    .stat-col .progress-bar {line-height:2px;height:2px;}

    .item {
        padding:30px 0;
    }
</style>
<?php if(preg_match('/\/admin\/|admin\.php|admin_d75KABNWt\.php/i', url())): ?>
<div class="alert alert-danger-light">
    <?php echo __('Security tips'); ?>
</div>
<?php endif; ?>
<div class="panel panel-default panel-intro">
    <div class="panel-heading">
    </div>
    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">

                <?php if($auth->check('dashbord/view')): ?>

                <div class="row">
                    <div class="col-sm-3 col-xs-6">
                        <div class="sm-st clearfix">
                            <span class="sm-st-icon st-red"><i class="fa fa-users"></i></span>
                            <div class="sm-st-info">
                                <span><?php echo $totalUser; ?></span>
                                商户总数
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="sm-st clearfix">
                            <span class="sm-st-icon st-violet"><i class="fa fa-vcard"></i></span>
                            <div class="sm-st-info">
                                <span><?php echo $totalAgent; ?></span>
                                代理总数
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="sm-st clearfix">
                            <span class="sm-st-icon st-blue"><i class="fa fa-shopping-bag"></i></span>
                            <div class="sm-st-info">
                                <span><?php echo $totalOrder; ?></span>
                                成功订单总数
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="sm-st clearfix">
                            <span class="sm-st-icon st-green"><i class="fa fa-cny"></i></span>
                            <div class="sm-st-info">
                                <span>￥<?php echo $totalOrderAmount; ?></span>
                                <?php echo __('Total order amount'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top:15px;">

                    <div class="col-lg-12">
                    </div>
                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">今日</span>
                                    <h2>订单统计</h2>
                                </div>
                                <div class="panel-content">
                                    <div class="no-margins">总数：<?php echo $todayOrderCount; ?>笔 成功：<?php echo $todaySuccOrderCount; ?>笔</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-aqua-gradient">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">今日</span>
                                    <h2>金额统计</h2>
                                </div>
                                <div class="panel-content">
                                    <div class="no-margins">收入金额：￥<?php echo $todayMoney; ?> 需要支出：￥<?php echo $todayExpendMoney; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-green-gradient">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">全部</span>
                                    <h2>订单统计</h2>
                                </div>
                                <div class="panel-content">
                                    <div class="no-margins">总数：<?php echo $allOrderCount; ?>笔 成功：<?php echo $totalOrder; ?>笔</div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-purple-gradient">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">全部</span>
                                    <h2>金额统计</h2>
                                </div>
                                <div class="panel-content">
                                    <div class="no-margins">收入金额：￥<?php echo $allMoney; ?> 需要支出：￥<?php echo $allExpendMoney; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row" style="margin-top: 20px">
                    <div class="col-lg-12">
                        <div id="echart" style="height:400px;width:100%;"></div>
                    </div>
                </div>
                <?php else: ?>
                <h1>欢迎登录。</h1>
                <br/>
                <?php endif; ?>

            </div>
            <div class="tab-pane fade" id="two">
                <div class="row">
                    <div class="col-xs-12">
                        <?php echo __('Custom zone'); ?>
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
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>