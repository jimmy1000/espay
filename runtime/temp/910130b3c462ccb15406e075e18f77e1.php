<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:75:"/mnt/projects/espay/public/../application/admin/view/api/account/check.html";i:1574294804;s:62:"/mnt/projects/espay/application/admin/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
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
    @media (max-width: 375px) {
        .edit-form tr td input{width:100%;}
        .edit-form tr th:first-child,.edit-form tr td:first-child{
            width:20%;
        }
        .edit-form tr th:nth-last-of-type(-n+2),.edit-form tr td:nth-last-of-type(-n+2){
            display: none;
        }
    }
    .edit-form table > tbody > tr td a.btn-delcfg{
        visibility: hidden;
    }
    .edit-form table > tbody > tr:hover td a.btn-delcfg{
        visibility: visible;
    }
</style>
<div class="panel panel-default panel-intro">
    <div class="panel-heading">
        <?php echo build_heading(null, false); ?>
    </div>

    <div class="panel-body">
        <div id="myTabContent" class="tab-content">

            <div class="alert alert-danger">附加参数请使用http报文串,只支持bankcode,user_id,ip。</div>
            <form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">


                <div class="form-inline" data-toggle="cxselect" data-selects="first,second">
                    <select class="first form-control" name="first" data-url="ajax/apiaccount" id="account_id"></select>
                    <select class="second form-control" name="second" data-url="ajax/apitype" data-query-name="account_id" id="api_type_id"></select>
                    <input type="number" name="money" id="money" class="form-control" placeholder="金额" value="10">
                    <input type="text" name="params" id="params" class="form-control" placeholder="附加参数，比如说用户ip，bankcode，user_id等" style="width: 300px">
                    <button type="button" class="btn btn-success" id="checkBtn">立即检测</button>
                </div>

            </form>

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