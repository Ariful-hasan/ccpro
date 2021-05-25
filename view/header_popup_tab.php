<html>
<head>
	<title><?php if (isset($pageTitle)) echo $pageTitle;?></title>
	<?php if (!isset($fullload)){?>
	<link rel="stylesheet" href="<?php echo site_url().base_url();?>css/main.css?v=1.0.3" />
    <link rel="stylesheet" href="<?php echo site_url().base_url();?>css/main.popup.css" />
    <?php }?>
	<?php if (isset($headerContent)) {
		echo $headerContent;
	}?>
	 <!-- CORE JS FRAMEWORK - START --> 
	 <script src="<?php echo site_url().base_url();?>assets/js/jquery-1.11.2.min.js" type="text/javascript"></script> 
	<?php if (isset($fullload)){?>
	<!-- CORE CSS FRAMEWORK - START -->
       
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
		<!-- CORE CSS TEMPLATE - END -->
        <?php if(!empty($loadbv)){?>
           <link href="<?php echo site_url().base_url();?>js/bootstrapValidation/css/bootstrapValidator.min.css" rel="stylesheet" type="text/css"/>
           <script src="<?php echo site_url().base_url();?>js/bootstrapValidation/js/bootstrapValidator.min.js" type="text/javascript"></script>
        <?php }?>
        <link href="<?php echo site_url().base_url();?>css/gsstyle.css?v=1.0.6" rel="stylesheet" media="screen">
		<script src="<?php echo site_url().base_url();?>js/gsscript.js?v=1.0.2" type="text/javascript"></script>
		
	<?php }?>
<style type="text/css">
html {
	overflow:auto;
}
section .content-body {
  -moz-border-bottom-colors: none;
  -moz-border-left-colors: none;
  -moz-border-right-colors: none;
  -moz-border-top-colors: none;
  background-color: #ffffff;
  border-color: -moz-use-text-color #e8e8e8 #e8e8e8;
  border-image: none;
  border-style: none solid solid;
  border-width: 0 1px 1px;
  padding: 30px;
  transition: none !important;
}
</style>

</head>
<body class="popup-body">
<center>

<div class="title-area">
    <div class="top-title"><?php if (isset($pageTitle)) echo htmlentities($pageTitle);?></div>
    <div class="clearb"></div>
</div>
