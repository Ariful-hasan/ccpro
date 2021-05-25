<!DOCTYPE html>
<html lang="en_US">
<head>
	<meta charset="utf-8">  
    <?php if( $this->IsIE()){?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <?php }?>       
   <?php echo !empty($metaText) ? $metaText : "";?>
    <title>gPlex Contact Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

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
    <!-- CORE CSS TEMPLATE - END -->
	
	<link href="<?php echo site_url().base_url();?>lib/jqui/jquery-ui-1.10.0.custom.css" rel="stylesheet" media="screen">
	<link href="<?php echo site_url().base_url();?>lib/grid/css/ui.jqgrid.css?v=1.0.1" rel="stylesheet" media="screen">	
	
    <!-- CORE JS FRAMEWORK - START --> 
    <script src="<?php echo site_url().base_url();?>assets/js/jquery-1.11.2.min.js" type="text/javascript"></script> 
    <script src="<?php echo site_url().base_url();?>assets/js/jquery.easing.min.js" type="text/javascript"></script> 
    <script type="text/javascript" src="<?php echo site_url().base_url();?>lib/jqui/jquery-ui-1.10.4.min.js"></script>
    <script src="<?php echo site_url().base_url();?>assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script> 

	<link href="<?php echo site_url().base_url();?>js/lightbox/colorbox.css" rel="stylesheet" media="screen">
	<script src="<?php echo site_url().base_url();?>js/lightbox/jquery.colorbox-min.js" type="text/javascript"></script>
	
	<link href="<?php echo site_url().base_url();?>css/gsstyle.css?v=1.0.10" rel="stylesheet" media="screen">
	<script src="<?php echo site_url().base_url();?>js/gsscript.js?v=1.0.5" type="text/javascript"></script>

    <link href="<?php echo site_url().base_url();?>js/datetimepicker/jquery.datetimepicker.css" rel="stylesheet" media="screen">
    <script src="<?php echo site_url().base_url();?>js/datetimepicker/jquery.datetimepicker.js" type="text/javascript"></script>
</head>
<?php if (!isset($fullload)){?>
<body class="popup-body">

<center>

<!--<div class="title-area"><div class="top-title">--><?php //if (isset($pageTitle)) echo htmlentities($pageTitle);?><!--</div><div class="clearb"></div></div>-->
    <!--23-09-18-->
    <div class="title-area"><div class="top-title">
        <?php
            $pageTitle2=!empty($pageTitle2)?$pageTitle2:'';
            if (!empty($pageTitle2)) echo htmlentities($pageTitle2);
            else {
                echo !empty($pageTitle) ? htmlentities($pageTitle) : "Undefined Page Title";
            }
        ?>
        <?php //if (isset($pageTitle)) echo htmlentities($pageTitle);?>
    </div><div class="clearb"></div></div>
    <!--23-09-18-->
<?php }else{
?>
<body class="">
<script type="text/javascript">
		$(function(){
			<?php if(!empty($isAutoResize)){?>				
			ResizeWindow();
			<?php }?>
		});		
		function ResizeWindow(){
			try{	
				 parent.$.colorbox.resize({			       
				        innerHeight:$("#pop-up-body").height()+75
				    });
				}catch(e){}
		}
</script>

<div class="col-md-12 m-b-zero">
	<div class="row">
	<section class="box m-t-zero m-b-zero">
		<header id="pop-up-header" class="panel_header">
			<h2 class="title pull-left"><?php if (isset($pageTitle)) echo htmlentities($pageTitle);?><?php echo !empty($pageSubTitle)?$pageSubTitle:"";?></h2>
			<div class="actions panel_actions pull-right">				
              	<i style="visibility: hidden;" class="box_setting fa fa-cog" data-toggle="modal"></i>
                <i onclick="parent.$.colorbox.close();" class="fa fa-times"></i>
				
			</div>
		</header>
		<div id="pop-up-body" class="content-body no-content-padding" style="min-height: 100px;">
			<?php if(HasUIMsg()){?>
				<div class="col-md-12">
					<div class="row" style=" border-bottom: 1px dashed #f1eded; padding-bottom: 4px;">
					<?php echo GetMsg()?>
					</div>					
				</div>
			<?php }?>
		
		
<?php }?>