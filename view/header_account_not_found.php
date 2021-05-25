<?php 
$_profile_img_url = site_url().base_url()."data/profile/profile.png?v=1";
//$url=$this->url().'agents_picture/'.UserAuth::getCurrentUser().".png";
if(UserAuth::hasRole('agent') && file_exists('agents_picture/'.UserAuth::getCurrentUser().".png")){
   $_profile_img_url=$this->url().'agents_picture/'.UserAuth::getCurrentUser().".png";
}?>
<!DOCTYPE html> 
<html class="<?php echo $this->IsIE()?"b-ie":"";?> " lang="en_US">
  <head>  		
   		<meta charset="utf-8">  
        <?php if( $this->IsIE()){?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <?php }?>       
       <?php echo !empty($metaText) ? $metaText : "";?>
        <title>gPlex Contact Center</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta content="" name="description" />
        <meta content="" name="author" />

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

        <!-- OTHER SCRIPTS INCLUDED ON THIS PAGE - START --> 
        <!-- OTHER SCRIPTS INCLUDED ON THIS PAGE - END --> 
		<!-- GS Font -->
		 <link href="<?php echo site_url().base_url();?>css/gs-fonts/style.css?v=1.0" rel="stylesheet" type="text/css"/>
		
        <!-- CORE CSS TEMPLATE - START -->
        <link href="<?php echo site_url().base_url();?>assets/css/style.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo site_url().base_url();?>assets/css/responsive.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo site_url().base_url();?>assets/css/theme/<?php echo strtolower(UserAuth::getDBSuffix()); ?>_style.css" rel="stylesheet" type="text/css"/>
        <!-- CORE CSS TEMPLATE - END -->
		
		<link href="<?php echo site_url().base_url();?>lib/jqui/jquery-ui-1.10.0.custom.css" rel="stylesheet" media="screen">
		<link href="<?php echo site_url().base_url();?>lib/grid/css/ui.jqgrid.css" rel="stylesheet" media="screen">
		
		
		
        <!-- CORE JS FRAMEWORK - START --> 
        <script src="<?php echo site_url().base_url();?>assets/js/jquery-1.11.2.min.js" type="text/javascript"></script> 
        <script src="<?php echo site_url().base_url();?>assets/js/jquery.easing.min.js" type="text/javascript"></script> 
        <script src="<?php echo site_url().base_url();?>assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script> 
		<script type="text/javascript" src="<?php echo site_url().base_url();?>lib/jqui/jquery-ui-1.10.4.min.js"></script>
		<script type="text/javascript" src="<?php echo site_url().base_url();?>lib/grid/js/i18n/grid.locale-en.js"></script>
		<script type="text/javascript" src="<?php echo site_url().base_url();?>lib/grid/js/jquery.jqGrid.min.js"></script>
        <script src="<?php echo site_url().base_url();?>assets/plugins/pace/pace.min.js" type="text/javascript"></script>  
        <script src="<?php echo site_url().base_url();?>assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js" type="text/javascript"></script> 
        <script src="<?php echo site_url().base_url();?>assets/plugins/viewport/viewportchecker.js" type="text/javascript"></script>  
        <!-- CORE JS FRAMEWORK - END --> 


        <!-- OTHER SCRIPTS INCLUDED ON THIS PAGE - START --> 
        <!-- OTHER SCRIPTS INCLUDED ON THIS PAGE - END --> 


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
		
		
		<link href="<?php echo site_url().base_url();?>css/gsstyle.css?v=1.0.6" rel="stylesheet" media="screen">
		<script src="<?php echo site_url().base_url();?>js/gsscript.js?v=1.0.2" type="text/javascript"></script>
		<script src="<?php echo site_url().base_url();?>js/jquery.ba-resize.min.js" type="text/javascript"></script>
		 <?php if( $this->IsIE()){?>
		<script src="<?php echo site_url().base_url();?>js/modernizr.js" type="text/javascript"></script>
		<?php }?>

    </head>
    <!-- END HEAD -->

    <!-- BEGIN BODY -->
    <body class=" "><!-- START TOPBAR -->
        <div class='page-topbar'>
            <a href="<?php echo $this->getBasePath();?>">
            <div class='logo-area'></div></a>
            <div class='quick-area'>
            </div>

        </div>
        <!-- END TOPBAR -->
        <!-- START CONTAINER -->
        <div class="page-container row-fluid">      
            <!-- START CONTENT -->

            <section id="" class=" container">
                <section class="wrapper" style='margin-top:60px;display:inline-block;width:100%;padding:15px 0 0 15px;'>
									
					<?php if ($this->isBoxWrapperEnabled()):?>
					<div class="col-md-12">
						<section class="box ">
					    	<header class="panel_header">
								<h2 class="title pull-left"><?php echo !empty($pageTitle)?$pageTitle:"Undefined Page Title";?> </h2>
								<div class="actions panel_actions pull-right">
									
                					
            					</div>
							</header>
    						<div class="content-body no-content-padding">
    				<?php endif;?>		  
					
					