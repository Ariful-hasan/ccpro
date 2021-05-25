<?php
include('conf.php');
$_profile_img_url = site_url().base_url()."data/profile/profile.png?v=1";
//$url=$this->url().'agents_picture/'.UserAuth::getCurrentUser().".png";
if (UserAuth::hasRole('agent') && file_exists('agents_picture/'.UserAuth::getCurrentUser().".png")) {
    $_profile_img_url=$this->url().'agents_picture/'.UserAuth::getCurrentUser().".png";
}?>
<!DOCTYPE html>
<html class="<?php echo $this->IsIE()?"b-ie":"";?> " lang="en_US">
<head>
    <meta charset="utf-8">
    <?php if ($this->IsIE()): ?> <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> <?php endif; ?>
    <?php echo !empty($metaText) ? $metaText : "";?>
    <title>gPlex Contact Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />


    <link rel="shortcut icon" href="<?php echo $this->url();?>favicon.ico" type="image/x-icon" />    <!-- Favicon -->
    <link rel="apple-touch-icon-precomposed" href="<?php echo site_url().base_url();?>assets/images/apple-touch-icon-57-precomposed.png">	<!-- For iPhone -->
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo site_url().base_url();?>assets/images/apple-touch-icon-114-precomposed.png">    <!-- For iPhone 4 Retina display -->
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo site_url().base_url();?>assets/images/apple-touch-icon-72-precomposed.png">    <!-- For iPad -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo site_url().base_url();?>assets/images/apple-touch-icon-144-precomposed.png">    <!-- For iPad Retina display -->

    <!-- CORE CSS FRAMEWORK - START -->
    <link href="<?php echo site_url().base_url();?>assets/plugins/pace/pace-theme-flash.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="<?php echo site_url().base_url();?>assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo site_url().base_url();?>assets/plugins/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo site_url().base_url();?>assets/fonts/font-awesome/css/font-awesome.css?v=4.4" rel="stylesheet" type="text/css"/>
    <link href="<?php echo site_url().base_url();?>assets/css/animate.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo site_url().base_url();?>assets/plugins/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" type="text/css"/>
    <!-- CORE CSS FRAMEWORK - END -->

    <!-- GS Font -->
    <link href="<?php echo site_url().base_url();?>css/gs-fonts/style.css?v=1.0" rel="stylesheet" type="text/css"/>

    <!-- CORE CSS TEMPLATE - START -->
    <link href="<?php echo site_url().base_url();?>assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo site_url().base_url();?>assets/css/responsive.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo site_url().base_url();?>assets/css/report_menu.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo site_url().base_url();?>assets/css/theme/<?php echo strtolower(UserAuth::getDBSuffix()); ?>_style.css" rel="stylesheet" type="text/css"/>
    <!-- CORE CSS TEMPLATE - END -->

    <link href="<?php echo site_url().base_url();?>lib/jqui/jquery-ui-1.10.0.custom.css" rel="stylesheet" media="screen">
    <link href="<?php echo site_url().base_url();?>lib/grid/css/ui.jqgrid.css?v=1.0.1" rel="stylesheet" media="screen">

    <!-- CORE JS FRAMEWORK - START -->
    <script src="<?php echo site_url().base_url();?>assets/js/jquery-1.11.2.min.js" type="text/javascript"></script>
    <script src="<?php echo site_url().base_url();?>assets/js/jquery.easing.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="<?php echo site_url().base_url();?>lib/jqui/jquery-ui-1.10.4.min.js"></script>
    <script src="<?php echo site_url().base_url();?>assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

    <script type="text/javascript" src="<?php echo site_url().base_url();?>lib/grid/js/i18n/grid.locale-en.js"></script>
    <script type="text/javascript" src="<?php echo site_url().base_url();?>lib/grid/js/jquery.jqGrid.min.js"></script>
    <script src="<?php echo site_url().base_url();?>assets/plugins/pace/pace.min.js" type="text/javascript"></script>
    <script src="<?php echo site_url().base_url();?>assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js" type="text/javascript"></script>
    <script src="<?php echo site_url().base_url();?>assets/plugins/viewport/viewportchecker.js" type="text/javascript"></script>
    <!-- CORE JS FRAMEWORK - END -->

    <!-- CORE TEMPLATE JS - START -->
    <script src="<?php echo site_url().base_url();?>assets/js/scripts.js?v=1.0.1" type="text/javascript"></script>
    <!-- END CORE TEMPLATE JS - END -->

    <!-- Sidebar Graph - START -->
    <script src="<?php echo site_url().base_url();?>assets/plugins/sparkline-chart/jquery.sparkline.min.js" type="text/javascript"></script>
    <script src="<?php echo site_url().base_url();?>assets/js/chart-sparkline.js" type="text/javascript"></script>
    <!-- Sidebar Graph - END -->

    <link href="<?php echo site_url().base_url();?>js/lightbox/colorbox.css" rel="stylesheet" media="screen">
    <script src="<?php echo site_url().base_url();?>js/lightbox/jquery.colorbox-min.js" type="text/javascript"></script>

    <link href="<?php echo site_url().base_url();?>js/datetimepicker/jquery.datetimepicker.css" rel="stylesheet" media="screen">
    <script src="<?php echo site_url().base_url();?>js/datetimepicker/jquery.datetimepicker.js" type="text/javascript"></script>

    <!-- Notification -->
    <link href="<?php echo site_url().base_url();?>assets/plugins/messenger/css/messenger.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="<?php echo site_url().base_url();?>assets/plugins/messenger/css/messenger-theme-flat.css" rel="stylesheet" type="text/css" media="screen"/>
    <script src="<?php echo site_url().base_url();?>assets/plugins/messenger/js/messenger.min.js" type="text/javascript"></script>
    <script src="<?php echo site_url().base_url();?>assets/plugins/messenger/js/messenger-theme-flat.js" type="text/javascript"></script>
    <!-- Notification - END -->

    <link href="<?php echo site_url().base_url();?>css/gsstyle.css?v=1.0.7" rel="stylesheet" media="screen">
    <script src="<?php echo site_url().base_url();?>js/gsscript.js?v=1.0.7" type="text/javascript"></script>
    <script src="<?php echo site_url().base_url();?>js/jquery.ba-resize.min.js" type="text/javascript"></script>

    <link rel="stylesheet" href="<?php echo site_url().base_url();?>ccd/select2/select2.min.css">
    <script src="<?php echo site_url().base_url();?>ccd/select2/select2.min.js" type="text/javascript"></script>

    <script src="<?php echo site_url().base_url();?>assets/plugins/floating-scrollbar/jquery.floatingscroll.js"></script>

    <?php if ($this->IsIE()): ?> <script src="<?php echo site_url().base_url();?>js/modernizr.js" type="text/javascript"></script> <?php endif; ?>
</head>
<!-- END HEAD -->

<!-- BEGIN BODY -->
<style>
    li a:hover {  color: black !important;}
    .lia{  height: 34px;  width: 100%;  line-height: 1.9 !important;  }
    .dvdr-mrgn{  margin-top: 0%;  margin-bottom: 0%; }
    .halfmenu {
        width: 100%!important;
    }
    .dropdown-menu{
        min-width: 850px!important;
        box-sizing: border-box;
    }
    ul.dropdown-menu{
        -webkit-box-shadow: 0 10px 6px -6px #777;
        -moz-box-shadow: 0 10px 6px -6px #777;
        box-shadow: 0 10px 6px -6px #777;
    }
    @media screen and (max-width: 767px) {
        .dropdown-menu{
            float: none;
            min-width: 250px!important;
        }
    }
</style>

<body class="hold-transition skin-blue layout-top-nav"><!-- START TOPBAR -->
<div class="col-md-12">
    <div class='report-new-top-bar page-topbar page-topbar'>
        <a href="<?php echo $this->getBasePath();?>">
            <div class='logo-area report-logo-area'></div>
        </a>
        <div class='quick-area'>
            <?php if (UserAuth::isLoggedIn() || UserAuth::isPageLoggedIn()):?>
                <div class='pull-left'>
                    <div class="dropdown">
                        <div class="row">
                            <div class="col-md-12">
                                <div id='cssmenu' class="<?php echo REPORT_MULTIPLE_MENU_OFF=='Y' ? 'no-copy-menu' : ''; ?>">
                                    <?php
                                    $menu_list = UserAuth::getCurrentUserMenu();
                                    $menu_list = json_decode($menu_list, false);
                                    
                                    include('model/MMenuMyJobs.php');
                                    $myJobModel = new MMenuMyJobs();
                                    $myjob_count_list = $myJobModel->getMyJobs(UserAuth::getCurrentUser());

                                    $report_menu = '';
                                    foreach ($menu_list as $key){
                                        if ($key->id == '1530444424') $report_menu = $key;
                                    }

                                    if(!empty($report_menu) && !empty($report_menu->children)){
                                    ?>
                                        <ul>
                                            <li class="gs-menu-title gs-menu-title-a"><a href="<?php echo $this->getBasePath();?>"><span>Main Menu</span></a></li>
                                            <?php echo Helper::TopMenu($report_menu->children, $this->url(), $myjob_count_list); ?>
                                        </ul>
                                    <?php } ?>
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>

                <div class='pull-right'>
                    <ul class="info-menu right-links list-inline list-unstyled" style="padding-right: 50px">
                        <li class="">
                            <a href="#" data-toggle="dropdown" class="toggle" title="User Manual">
                                <span> <i class="fa fa-th"></i></span>
                            </a>
                            <ul class="dropdown-menu info-menu profile animated fadeIn" style="min-width: 250px;">
                                <li><a href="<?php echo site_url().base_url();?>manual/gPlex_Hosted_Contact_Center_User_Manual_1.0.3.pdf" target="new"><span class="txt-success"><strong><i class="fa fa-file-pdf-o"></i> User Manual</strong></span></a></li>
                                <li><a href="<?php echo site_url().base_url();?>manual/gPlex_Hosted_Contact_Center_Softphone_User_Manual_1.0.1.pdf" target="new"><span class="txt-success"><strong><i class="fa fa-file-pdf-o"></i> PC Dialer User Instructions</strong></span></a></li>
                                <li><a href="http://ticket.gplex.com" target="new"><strong><i class="fa fa-ticket"></i> Open Ticket</strong></a></li>
                            </ul>
                        </li>
                        <!-- Show contact number only for cloud CC-->
                        <?php if (property_exists($db,"cctype") && $db->cctype == 0): ?>
                            <li class="message-toggle-wrapper showopacity" title="24/7 Customer Support" style="cursor:pointer;"><i class="glyphicon glyphicon-phone-alt"></i> <span class="badge badge-md badge-danger"> (972) 200-9800</span> &nbsp;</li>
                        <?php endif; ?>
                        <li class="profile">
                            <a href="#" data-toggle="dropdown" class="toggle lia">
                                <img src="<?php echo $_profile_img_url;?>" alt="user-image" class="img-circle img-inline">
                                <span><?php echo UserAuth::getCurrentUser() . '@' . UserAuth::getAccountName();?> <i class="fa fa-angle-down"></i></span>
                            </a>
                            <ul class="dropdown-menu profile animated fadeIn">
                                <?php if (UserAuth::isLoggedIn()):?>
                                    <li>
                                        <a class="lia" href="<?php echo $this->url('task=password');?>">
                                            <i class="fa fa-lock"></i>
                                            Change Password
                                        </a>
                                    </li>
                                <?php endif;?>
                                <li class="last">
                                    <a class="lia" href="<?php echo $this->url('task=logout');?>">
                                        <i class="fa fa-sign-out"></i>
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            <?php endif;?>
        </div>

    </div>
    <!-- END TOPBAR -->
    <!-- START CONTAINER -->
    <!--<div class="container-fluid">-->
    <!-- SIDEBAR - START -->

    <!--  SIDEBAR - END -->

    <!-- START CONTENT -->

    <div id="<?php if (UserAuth::isLoggedIn() || UserAuth::isPageLoggedIn()) { ?>content<?php }?>" class="row">
        <section class="wrapper" style='margin-top:60px;'>
            <?php
            if (isset($_GET['msgdays']) && UserAuth::isSetSesGCCPassExpDays() && UserAuth::getSesGCCPassExpDays() == $_GET['msgdays']) {
                UserAuth::showPassMessage(false, '');
                echo '<div id="showPassExpInfo" class="col-lg-12 col-md-12 col-sm-12 col-xs-12"><div class="alert alert-warning" style="border-radius:3px; padding:5px 10px;">';
                echo '<a style="display:inline-block; position:absolute; top:0; right:15px; background:#d38b4d;" onClick="javascript:closePassMsg(\'showPassExpInfo\');" ';
                echo 'href="javascript:void(0)" class="btn btn-xs btn-danger"><i class="fa fa-times"> </i></a>';
                echo 'Your current password will expire within '.$_GET['msgdays'].' day(s).<br>';
                echo 'Please change your current password.';
                echo '</div></div>';
            }
            ?>

            <?php if ($this->isBoxWrapperEnabled()):?>
            <div class="col-md-12">
                <section class="box ">
                    <header class="panel_header">
                        <h2 class="title pull-left"><?php echo !empty($pageTitle)?$pageTitle:"Undefined Page Title";?> </h2>
                        <div class="actions panel_actions pull-right">
                            <?php include('view/top-menu.php');?>
                            <?php
                            if (isset($topMenuItems)) {
                                foreach ($topMenuItems as $tmenu) {
                                    $dataattr="";
                                    if (!empty($tmenu['dataattr']) && is_array($tmenu['dataattr'])) {
                                        foreach ($tmenu['dataattr'] as $kk=>$vv) {
                                            $dataattr.=" data-$kk=\"$vv\" ";
                                        }
                                    }

                                    if (!empty($tmenu['property']) && is_array($tmenu['property'])) {
                                        foreach ($tmenu['property'] as $kk=>$vv) {
                                            $dataattr.=" $kk=\"$vv\" ";
                                        }
                                    }
                                    if (isset($tmenu['title'])) {
                                        $dataattr.=" title=\"$tmenu[title]\" ";
                                    }
                                    $_cls_names = 'btn btn-'.(!empty($tmenu['color'])?$tmenu['color']:'purple');
                                    if (isset($tmenu['class'])) {
                                        $_cls_names .= ' ' . $tmenu['class'];
                                    }
                                    //if (isset($tmenu['img'])) $_cls_names .= ' fa fa-' . $tmenu['img'];
                                    echo '<a '.$dataattr.'  href="' . $this->url($tmenu['href']) . '" class="' . $_cls_names . '">';
                                    if (isset($tmenu['img'])) {
                                        echo '<i class="'. $tmenu['img'] .'"></i> ';
                                    }
                                    echo $tmenu['label'] . '</a> ';
                                }
                            }
                            ?>

                        </div>
                    </header>
                    <div class="content-body no-content-padding">

                        <?php endif;?>
