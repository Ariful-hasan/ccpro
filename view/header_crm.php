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
    <script src="<?php echo site_url().base_url();?>assets/js/scripts.js" type="text/javascript"></script>
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
    <?php if ($this->IsIE()): ?> <script src="<?php echo site_url().base_url();?>js/modernizr.js" type="text/javascript"></script> <?php endif; ?>

    <style> .dropdown{padding: 15px 15px 0px 15px; color: #676767}  .dropdown a{color: #676767;padding: 0 15px; text-decoration:none;}  .dropdown li>a{color: #676767;}  .dropdown a:hover{text-decoration:none; } </style>
</head>
<!-- END HEAD -->

<!-- BEGIN BODY -->



<body class="hold-transition skin-blue layout-top-nav"><!-- START TOPBAR -->
<div class="col-md-12" class="bgcolor">
