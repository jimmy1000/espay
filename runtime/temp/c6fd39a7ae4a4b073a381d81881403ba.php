<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:68:"/mnt/projects/espay/public/../application/index/view/test/index.html";i:1574294804;s:62:"/mnt/projects/espay/application/index/view/layout/default.html";i:1574294804;s:59:"/mnt/projects/espay/application/index/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/index/view/common/script.html";i:1574294804;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?> – <?php echo $site['name']; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<?php if(isset($keywords)): ?>
<meta name="keywords" content="<?php echo $keywords; ?>">
<?php endif; if(isset($description)): ?>
<meta name="description" content="<?php echo $description; ?>">
<?php endif; ?>
<meta name="author" content="FastAdmin">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />

<link href="/assets/css/frontend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config: <?php echo json_encode($config); ?>
    };
</script>
    <link href="/assets/css/user.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#header-navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo url('/'); ?>" style="padding:6px 15px;"><img src="<?php echo $site['logo']; ?>" style="height:40px;" alt=""></a>
        </div>
        <div class="collapse navbar-collapse" id="header-navbar">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/" target="_blank"><?php echo __('Home'); ?></a></li>
                <li><a href="<?php echo url('/index/test'); ?>" target="_blank">支付体验</a></li>
                <li><a href="<?php echo $site['docurl']; ?>" target="_blank">对接文档</a> </li>
                <li><a href="<?php echo $site['frontend_url']; ?>/register">我要开户</a> </li>
                <li><a href="<?php echo $site['frontend_url']; ?>">商户后台</a> </li>
            </ul>
        </div>
    </div>
</nav>

<main class="content">
    

<div class="regWrap">
    <form action="" class="form-horizontal" id="test-form" name="form1" method="post">
        <div class="wzz">
            <dl style="height:50px;line-height:30px;margin-top:20px;">
                <dt>请选择支付方式</dt>
            </dl>
            <div class="form-group">
                <label  class="col-sm-3 control-label">*支付方式</label>
                <div class="col-sm-8 ">
                    <select class="channel form-control" name="channel">
                        <?php if($list): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$i): ?>
                                <option value="<?php echo $i['code']; ?>" ><?php echo $i['name']; ?></option>
                            <?php endforeach; endif; else: echo "" ;endif; else: ?>
                            <option value="">暂无可用接口</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">*支付金额</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        <input type="text" value="<?php echo (isset($site['testmoney']) && ($site['testmoney'] !== '')?$site['testmoney']:'1'); ?>" class="fxfee form-control" placeholder="支付金额" name="amount" data-rule="required; integer[+]" />
                        <span class="input-group-addon">元</span>
                    </div>
                </div>
            </div>
            <div class="form-group" style="text-align:center;padding-top:15px;">
                <button type="submit" name="add" class="zcBtn btn btn-info btn-lg" >立即支付</button>&nbsp;
            </div>
        </div>
    </form>
</div>

<style>
    .regWrap {border-radius: 9px;background-color:#fff; min-height:368px;width:80%;max-width: 760px;  margin: auto; padding-top: 28px; padding-bottom: 50px; border: 1px solid #d2d2d2;}
    .regWrap dl { width: 100%; margin: auto; margin-bottom: 18px;}
    .regWrap dl dt { height: 30px; border-bottom: 1px dashed #d2d2d2; color: #333; font-size: 16px;}
    .regWrap dl dt em { margin: 0 5px;}
    .regWrap dl dd { min-height: 30px; margin-top: 20px; }
    .regWrap dl dd label { display: inline-block; width: 134px; height: 30px; line-height: 30px; text-align: right; font-size: 14px; color: #333;}
    .regWrap dl dd input { width: 202px; height: 28px; line-height: 28px; padding-left: 5px; font-size: 12px; border: 1px solid #bbc4d3; vertical-align: middle;}
    .regWrap dl dd span { margin-left: 14px; font-size: 12px; color: #999;}
    .regWrap dl dd span.err { color: red;}
    .regWrap dl dd span.corr { color: green;}
    .regWrap dl dd select { width: 209px; height: 28px; line-height: 28px; padding-left: 5px; font-size: 12px; border: 1px solid #bbc4d3; vertical-align: middle;}
    .regWrap dl dd select option { color: #333;}
    .regWrap dl dd select.sel1 { width: 100px;}
    .regWrap dl dd select.sel2 { width: 100px; margin-left: 10px;}
    .regWrap p { width: 1112px; margin: auto; margin-top: 14px; padding-top: 30px; border-top: 1px dashed #d2d2d2;}
    .regWrap p input { width: 120px; height: 38px; margin-left: 132px; color: #FFF; font-size: 16px; border: none; background: #249cda; cursor: pointer;}

    .ft { height: 2px; margin-top: 25px; background: #313131;}

    .copyright { height: 38px; line-height: 38px; font-size: 12px; text-align: center; color: #bbb; background: #252525;}

    .wzz{padding:10px 30px 10px 11%;max-width:670px;overflow:hidden;}
    .regform .form-group .form-control{}

    .singlebg{background-color:#f7f7f7;}
    .verifyImg{cursor:pointer;border-radius: 4px;}
    .zcxx{ height:50px;line-height:30px;margin-top:20px;}

    .msg-box{
        display: none;
    }
    @media (max-width: 768px) {
        .hd2{height:150px;}
        .hd2 .logo {display:block;height:90px;margin-bottom:30px;float:none;margin:10px auto;padding:auto;width:auto;}
        .hd2 .logo a{margin:auto;padding:auto;width:230px;display:block;}
        .hd2 .logo img{}
        .hd2 .dlBtn{float:none;margin:10px auto;width:330px;display:block;}
        .hd2 .dlBtn a.tbtn{}
        .zcxx dt{text-align:center;}
        .logTc{width:80%;margin-left:-40%}
    }
</style>
</main>

<footer class="footer" style="clear:both">
    <p class="copyright">Copyright&nbsp;©&nbsp;2017-2019 Powered by <a href="https://pay.0533hf.com" target="_blank">Easypay</a> All Rights Reserved <?php echo $site['name']; ?> <?php echo __('Copyrights'); ?> <a href="http://www.miibeian.gov.cn" target="_blank"><?php echo $site['beian']; ?></a></p>
</footer>

<script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-frontend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>

</body>

</html>