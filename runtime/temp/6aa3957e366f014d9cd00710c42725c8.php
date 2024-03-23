<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:69:"/mnt/projects/espay/public/../application/admin/view/index/index.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/meta.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/header.html";i:1574294804;s:59:"/mnt/projects/espay/application/admin/view/common/menu.html";i:1574294804;s:61:"/mnt/projects/espay/application/admin/view/common/script.html";i:1574294804;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <!-- 加载样式及META信息 -->
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
    <body class="hold-transition skin-purple sidebar-mini fixed <?php if($config['fastadmin']['multiplenav']): ?>multiplenav<?php endif; ?>" id="tabs">
        <div class="wrapper">

            <!-- 头部区域 -->
            <header id="header" class="main-header">
                <!-- Logo -->
<a href="javascript:;" class="logo">
    <!-- 迷你模式下Logo的大小为50X50 -->
    <span class="logo-mini"><?php echo mb_strtoupper(mb_substr($site['name'],0,4,'utf-8'),'utf-8'); ?></span>
    <!-- 普通模式下Logo -->
    <span class="logo-lg"><b><?php echo $site['name']; ?></b></span>
</a>

<!-- 顶部通栏样式 -->
<nav class="navbar navbar-static-top">

    <!--第一级菜单-->
    <div id="firstnav">
        <!-- 边栏切换按钮-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only"><?php echo __('Toggle navigation'); ?></span>
        </a>

        <!--如果不想在顶部显示角标,则给ul加上disable-top-badge类即可-->
        <ul class="nav nav-tabs nav-addtabs disable-top-badge hidden-xs" role="tablist">
            <?php echo $navlist; ?>
        </ul>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">

                <li>
                    <a href="/" target="_blank"><i class="fa fa-home" style="font-size:14px;"></i></a>
                </li>

                <!-- 账号信息下拉框 -->
                <li class="hidden-xs">
                    <a href="javascript:;" data-toggle="checkupdate" title="<?php echo __('Check for updates'); ?>">
                        <i class="fa fa-refresh"></i>
                    </a>
                </li>

                <!-- 清除缓存 -->
                <li>
                    <a href="javascript:;" data-toggle="dropdown" title="<?php echo __('Wipe cache'); ?>">
                        <i class="fa fa-trash"></i>
                    </a>
                    <ul class="dropdown-menu wipecache">
                        <li><a href="javascript:;" data-type="all"><i class="fa fa-trash"></i> <?php echo __('Wipe all cache'); ?></a></li>
                        <li class="divider"></li>
                        <li><a href="javascript:;" data-type="content"><i class="fa fa-file-text"></i> <?php echo __('Wipe content cache'); ?></a></li>
                        <li><a href="javascript:;" data-type="template"><i class="fa fa-file-image-o"></i> <?php echo __('Wipe template cache'); ?></a></li>
                        <li><a href="javascript:;" data-type="addons"><i class="fa fa-rocket"></i> <?php echo __('Wipe addons cache'); ?></a></li>
                    </ul>
                </li>

                <!-- 多语言列表 -->
                <?php if(\think\Config::get('lang_switch_on')): ?>
                <li class="hidden-xs">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-language"></i></a>
                    <ul class="dropdown-menu">
                        <li class="<?php echo $config['language']=='zh-cn'?'active':''; ?>">
                            <a href="?ref=addtabs&lang=zh-cn">简体中文</a>
                        </li>
                        <li class="<?php echo $config['language']=='en'?'active':''; ?>">
                            <a href="?ref=addtabs&lang=en">English</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- 全屏按钮 -->
                <li class="hidden-xs">
                    <a href="#" data-toggle="fullscreen"><i class="fa fa-arrows-alt"></i></a>
                </li>

                <!-- 账号信息下拉框 -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?php echo cdnurl($admin['avatar']); ?>" class="user-image" alt="<?php echo $admin['nickname']; ?>">
                        <span class="hidden-xs"><?php echo $admin['nickname']; ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?php echo cdnurl($admin['avatar']); ?>" class="img-circle" alt="">

                            <p>
                                <?php echo $admin['nickname']; ?>
                                <small><?php echo date("Y-m-d H:i:s",$admin['logintime']); ?></small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="general/profile" class="btn btn-primary addtabsit"><i class="fa fa-user"></i>
                                    <?php echo __('Profile'); ?></a>
                            </div>
                            <div class="pull-right">
                                <a href="<?php echo url('index/logout'); ?>" class="btn btn-danger"><i class="fa fa-sign-out"></i>
                                    <?php echo __('Logout'); ?></a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <?php if($config['fastadmin']['multiplenav']): ?>
    <!--第二级菜单,只有在multiplenav开启时才显示-->
    <div id="secondnav">
        <ul class="nav nav-tabs nav-addtabs disable-top-badge" role="tablist">
            <?php if($fixedmenu): ?>
            <li role="presentation" id="tab_<?php echo $fixedmenu['id']; ?>" class="<?php echo $referermenu?'':'active'; ?>"><a href="#con_<?php echo $fixedmenu['id']; ?>" node-id="<?php echo $fixedmenu['id']; ?>" aria-controls="<?php echo $fixedmenu['id']; ?>" role="tab" data-toggle="tab"><i class="fa fa-dashboard fa-fw"></i> <span><?php echo $fixedmenu['title']; ?></span> <span class="pull-right-container"> </span></a></li>
            <?php endif; if($referermenu): ?>
            <li role="presentation" id="tab_<?php echo $referermenu['id']; ?>" class="active"><a href="#con_<?php echo $referermenu['id']; ?>" node-id="<?php echo $referermenu['id']; ?>" aria-controls="<?php echo $referermenu['id']; ?>" role="tab" data-toggle="tab"><i class="fa fa-list fa-fw"></i> <span><?php echo $referermenu['title']; ?></span> <span class="pull-right-container"> </span></a> <i class="close-tab fa fa-remove"></i></li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>
</nav>
            </header>

            <!-- 左侧菜单栏 -->
            <aside class="main-sidebar">
                <!-- 左侧菜单栏 -->
<section class="sidebar">
    <!-- 管理员信息 -->
    <div class="user-panel hidden-xs">
        <div class="pull-left image">
            <a href="general/profile" class="addtabsit"><img src="<?php echo cdnurl($admin['avatar']); ?>" class="img-circle" /></a>
        </div>
        <div class="pull-left info">
            <p><?php echo $admin['nickname']; ?></p>
            <i class="fa fa-circle text-success"></i> <?php echo __('Online'); ?>
        </div>
    </div>

    <!-- 菜单搜索 -->
    <form action="" method="get" class="sidebar-form" onsubmit="return false;">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="<?php echo __('Search menu'); ?>">
            <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
            </span>
            <div class="menuresult list-group sidebar-form hide">
            </div>
        </div>
    </form>

    <!-- 移动端一级菜单 -->
    <div class="mobilenav visible-xs">

    </div>

    <!--如果想始终显示子菜单,则给ul加上show-submenu类即可,当multiplenav开启的情况下默认为展开-->
    <ul class="sidebar-menu <?php if($config['fastadmin']['multiplenav']): ?>show-submenu<?php endif; ?>">

        <!-- 菜单可以在 后台管理->权限管理->菜单规则 中进行增删改排序 -->
        <?php echo $menulist; ?>


    </ul>
</section>
            </aside>

            <!-- 主体内容区域 -->
            <div class="content-wrapper tab-content tab-addtabs">
                <?php if($fixedmenu): ?>
                <div role="tabpanel" class="tab-pane <?php echo $referermenu?'':'active'; ?>" id="con_<?php echo $fixedmenu['id']; ?>">
                    <iframe src="<?php echo $fixedmenu['url']; ?>?addtabs=1" width="100%" height="100%" frameborder="no" border="0" marginwidth="0" marginheight="0" scrolling-x="no" scrolling-y="auto" allowtransparency="yes"></iframe>
                </div>
                <?php endif; if($referermenu): ?>
                <div role="tabpanel" class="tab-pane active" id="con_<?php echo $referermenu['id']; ?>">
                    <iframe src="<?php echo $referermenu['url']; ?>?addtabs=1" width="100%" height="100%" frameborder="no" border="0" marginwidth="0" marginheight="0" scrolling-x="no" scrolling-y="auto" allowtransparency="yes"></iframe>
                </div>
                <?php endif; ?>
            </div>

            <!-- 底部链接,默认隐藏 -->
            <footer class="main-footer hide">
                <div class="pull-right hidden-xs">
                </div>
                <strong>Copyright &copy; 2017-2018 <a href="https://www.fastadmin.net">Fastadmin</a>.</strong> All rights reserved.
            </footer>

        </div>

        <!-- 加载JS脚本 -->
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>