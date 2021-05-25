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
        <link href="<?php echo site_url().base_url();?>css/gsstyle.css?v=1.0.7" rel="stylesheet" media="screen">
		<script src="<?php echo site_url().base_url();?>js/gsscript.js?v=1.0.7" type="text/javascript"></script>
		
	<?php }?>
<style>
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
<?php if (isset($fullload)){?>
.alert {
  border: medium none;
  box-shadow: none;
  margin: 0 0 2px;
  padding: 5px;
  text-shadow: none;
}
<?php }?>
</style>

</head>
<?php if (!isset($fullload)){?>
<body class="popup-body">

<center>

<div class="title-area"><div class="top-title"><?php if (isset($pageTitle)) echo htmlentities($pageTitle);?></div><div class="clearb"></div></div>
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
               <?php /*?> <i id="cboxClose" onclick="parent.$.colorbox.close();" class="fa fa-times"></i> <?php */ ?>
				
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