<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:80:"/mnt/projects/espay/public/../application/admin/view/api/account/statistics.html";i:1574294804;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
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
    <?php echo build_heading(); ?>

    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">
                        <a href="javascript:;" class="btn btn-primary btn-refresh" title="<?php echo __('Refresh'); ?>" ><i class="fa fa-refresh"></i> </a>
                    </div>
                    <table id="table" class="table table-striped table-bordered table-hover table-nowrap"
                           width="100%">
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script id="customformtpl" type="text/html">
    <!--form表单必须添加form-commsearch这个类-->
    <form action="" class="form-commonsearch form-horizontal">
        <div style="border-radius:2px;margin-bottom:10px;background:#f5f5f5;padding:20px;">
            <h4>筛选条件</h4>
            <hr>
            <div class="row">
                <div class="form-group col-xs-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label class="control-label col-xs-4">商户号</label>
                        <div class="col-xs-8">
                            <input class="operate" type="hidden" data-name="merchant_id" value="="/>
                            <input type="text" class="form-control " name="merchant_id" />
                        </div>
                    </div>
                </div>

                <div class="form-group col-xs-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label class="control-label col-xs-4">日期</label>
                        <div class="col-xs-8">
                            <input type="hidden" class="form-control operate" name="createtime-operate" data-name="createtime" value="RANGE" readonly="">
                            <input type="text" class="form-control datetimerange" name="createtime" value="" placeholder="添加时间" id="createtime" autocomplete="off" />
                        </div>
                    </div>
                </div>


                <div class="form-group col-xs-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="col-sm-8 col-xs-offset-4">
                        <button type="submit" class="btn btn-success" >立即查询</button>
                        <button type="reset" class="btn btn-primary">重置</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</script>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>