<?php
include('conf.php');
$_profile_img_url = site_url().base_url()."assets/images/".strtolower(UserAuth::getDBSuffix())."_client_logo.png?v=1";
// $_profile_img_url="assets/images/ab_client_logo.png?v=1";
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
        <link href="<?php echo site_url().base_url();?>assets/css/theme/<?php //echo strtolower(UserAuth::getDBSuffix()); ?>ab_style.css" rel="stylesheet" type="text/css"/>
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
		
		
		<link href="<?php echo site_url().base_url();?>css/gsstyle.css?v=1.0.7" rel="stylesheet" media="screen">
		<script src="<?php echo site_url().base_url();?>js/gsscript.js?v=1.0.7" type="text/javascript"></script>
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
            <?php if (UserAuth::isLoggedIn() || UserAuth::isPageLoggedIn()):?>
                <div class='pull-left'>
                	
                    <ul class="info-menu left-links list-inline list-unstyled">
                        <li class="sidebar-toggle-wrap">
                            <a href="#" data-toggle="sidebar" class="sidebar_toggle">
                                <i class="fa fa-bars"></i>
                            </a>
                        </li>
                        
                        <?php /* ?>
                        <li class="message-toggle-wrapper">
                            <a href="#" data-toggle="dropdown" class="toggle">
                                <i class="fa fa-envelope"></i>
                                <span class="badge badge-primary">7</span>
                            </a>
                            <ul class="dropdown-menu messages animated fadeIn">

                                <li class="list">

                                    <ul class="dropdown-menu-list list-unstyled ps-scrollbar">
                                        <li class="unread status-available">
                                            <a href="javascript:;">
                                                <div class="user-img">
                                                    <img src="data/profile/avatar-1.png" alt="user-image" class="img-circle img-inline">
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Clarine Vassar</strong>
                                                        <span class="time small">- 15 mins ago</span>
                                                        <span class="profile-status available pull-right"></span>
                                                    </span>
                                                    <span class="desc small">
                                                        Sometimes it takes a lifetime to win a battle.
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" status-away">
                                            <a href="javascript:;">
                                                <div class="user-img">
                                                    <img src="data/profile/avatar-2.png" alt="user-image" class="img-circle img-inline">
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Brooks Latshaw</strong>
                                                        <span class="time small">- 45 mins ago</span>
                                                        <span class="profile-status away pull-right"></span>
                                                    </span>
                                                    <span class="desc small">
                                                        Sometimes it takes a lifetime to win a battle.
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" status-busy">
                                            <a href="javascript:;">
                                                <div class="user-img">
                                                    <img src="data/profile/avatar-3.png" alt="user-image" class="img-circle img-inline">
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Clementina Brodeur</strong>
                                                        <span class="time small">- 1 hour ago</span>
                                                        <span class="profile-status busy pull-right"></span>
                                                    </span>
                                                    <span class="desc small">
                                                        Sometimes it takes a lifetime to win a battle.
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" status-offline">
                                            <a href="javascript:;">
                                                <div class="user-img">
                                                    <img src="data/profile/avatar-4.png" alt="user-image" class="img-circle img-inline">
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Carri Busey</strong>
                                                        <span class="time small">- 5 hours ago</span>
                                                        <span class="profile-status offline pull-right"></span>
                                                    </span>
                                                    <span class="desc small">
                                                        Sometimes it takes a lifetime to win a battle.
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" status-offline">
                                            <a href="javascript:;">
                                                <div class="user-img">
                                                    <img src="data/profile/avatar-5.png" alt="user-image" class="img-circle img-inline">
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Melissa Dock</strong>
                                                        <span class="time small">- Yesterday</span>
                                                        <span class="profile-status offline pull-right"></span>
                                                    </span>
                                                    <span class="desc small">
                                                        Sometimes it takes a lifetime to win a battle.
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" status-available">
                                            <a href="javascript:;">
                                                <div class="user-img">
                                                    <img src="data/profile/avatar-1.png" alt="user-image" class="img-circle img-inline">
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Verdell Rea</strong>
                                                        <span class="time small">- 14th Mar</span>
                                                        <span class="profile-status available pull-right"></span>
                                                    </span>
                                                    <span class="desc small">
                                                        Sometimes it takes a lifetime to win a battle.
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" status-busy">
                                            <a href="javascript:;">
                                                <div class="user-img">
                                                    <img src="data/profile/avatar-2.png" alt="user-image" class="img-circle img-inline">
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Linette Lheureux</strong>
                                                        <span class="time small">- 16th Mar</span>
                                                        <span class="profile-status busy pull-right"></span>
                                                    </span>
                                                    <span class="desc small">
                                                        Sometimes it takes a lifetime to win a battle.
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" status-away">
                                            <a href="javascript:;">
                                                <div class="user-img">
                                                    <img src="data/profile/avatar-3.png" alt="user-image" class="img-circle img-inline">
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Araceli Boatright</strong>
                                                        <span class="time small">- 16th Mar</span>
                                                        <span class="profile-status away pull-right"></span>
                                                    </span>
                                                    <span class="desc small">
                                                        Sometimes it takes a lifetime to win a battle.
                                                    </span>
                                                </div>
                                            </a>
                                        </li>

                                    </ul>

                                </li>

                                <li class="external">
                                    <a href="javascript:;">
                                        <span>Read All Messages</span>
                                    </a>
                                </li>
                            </ul>

                        </li>
                        <li class="notify-toggle-wrapper">
                            <a href="#" data-toggle="dropdown" class="toggle">
                                <i class="fa fa-bell"></i>
                                <span class="badge badge-orange">3</span>
                            </a>
                            <ul class="dropdown-menu notifications animated fadeIn">
                                <li class="total">
                                    <span class="small">
                                        You have <strong>3</strong> new notifications.
                                        <a href="javascript:;" class="pull-right">Mark all as Read</a>
                                    </span>
                                </li>
                                <li class="list">

                                    <ul class="dropdown-menu-list list-unstyled ps-scrollbar">
                                        <li class="unread available"> <!-- available: success, warning, info, error -->
                                            <a href="javascript:;">
                                                <div class="notice-icon">
                                                    <i class="fa fa-check"></i>
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Server needs to reboot</strong>
                                                        <span class="time small">15 mins ago</span>
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="unread away"> <!-- available: success, warning, info, error -->
                                            <a href="javascript:;">
                                                <div class="notice-icon">
                                                    <i class="fa fa-envelope"></i>
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>45 new messages</strong>
                                                        <span class="time small">45 mins ago</span>
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" busy"> <!-- available: success, warning, info, error -->
                                            <a href="javascript:;">
                                                <div class="notice-icon">
                                                    <i class="fa fa-times"></i>
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Server IP Blocked</strong>
                                                        <span class="time small">1 hour ago</span>
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" offline"> <!-- available: success, warning, info, error -->
                                            <a href="javascript:;">
                                                <div class="notice-icon">
                                                    <i class="fa fa-user"></i>
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>10 Orders Shipped</strong>
                                                        <span class="time small">5 hours ago</span>
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" offline"> <!-- available: success, warning, info, error -->
                                            <a href="javascript:;">
                                                <div class="notice-icon">
                                                    <i class="fa fa-user"></i>
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>New Comment on blog</strong>
                                                        <span class="time small">Yesterday</span>
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" available"> <!-- available: success, warning, info, error -->
                                            <a href="javascript:;">
                                                <div class="notice-icon">
                                                    <i class="fa fa-check"></i>
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Great Speed Notify</strong>
                                                        <span class="time small">14th Mar</span>
                                                    </span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class=" busy"> <!-- available: success, warning, info, error -->
                                            <a href="javascript:;">
                                                <div class="notice-icon">
                                                    <i class="fa fa-times"></i>
                                                </div>
                                                <div>
                                                    <span class="name">
                                                        <strong>Team Meeting at 6PM</strong>
                                                        <span class="time small">16th Mar</span>
                                                    </span>
                                                </div>
                                            </a>
                                        </li>

                                    </ul>

                                </li>

                                <li class="external">
                                    <a href="javascript:;">
                                        <span>Read All Notifications</span>
                                    </a>
                                </li>
                            </ul>
                        </li>      <?php // */?>                     
                    </ul>
                 
                </div>
				<div class='pull-right'>
                    <ul class="info-menu right-links list-inline list-unstyled">
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
                            <a href="#" data-toggle="dropdown" class="toggle">
                                <img src="<?php echo $_profile_img_url;?>" alt="user-image" class="img-circle img-inline">
                                <span><?php echo UserAuth::getCurrentUser() . '@' . UserAuth::getAccountName();?> <i class="fa fa-angle-down"></i></span>
                            </a>
                            <ul class="dropdown-menu profile animated fadeIn">
                                <?php if (UserAuth::isLoggedIn()):?>
                                <li>
                                    <a href="<?php echo $this->url('task=password');?>">
                                        <i class="fa fa-lock"></i>
                                        Change Password
                                    </a>
                                </li>
								<?php endif;?>
                                <li class="last">
                                    <a href="<?php echo $this->url('task=logout');?>">
                                        <i class="fa fa-sign-out"></i>
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                       <?php /*?> 
                       <li class="chat-toggle-wrapper">
                            <a href="#" data-toggle="chatbar" class="toggle_chat">
                                <i class="fa fa-comments"></i>
                                <span class="badge badge-warning">9</span>
                            </a>
                        </li>
                        <?php */?>
                    </ul>			
                </div>
				<?php endif;?>
            </div>

        </div>
        <!-- END TOPBAR -->
        <!-- START CONTAINER -->
        <div class="page-container row-fluid">
 		 <?php if (UserAuth::isLoggedIn() || UserAuth::isPageLoggedIn()):?>
            <!-- SIDEBAR - START -->
            <div class="page-sidebar ">


                <!-- MAIN MENU - START -->
                <div class="page-sidebar-wrapper" id="main-menu-wrapper"> 

                    <!-- USER INFO - START -->
                    <div class="profile-info row">

                        <div class="profile-image col-md-4 col-sm-4 col-xs-4">
                            <a href="index.php">                            	
                                <img src="<?php echo $_profile_img_url;?>" class="img-responsive img-circle">
                                
                            </a>
                        </div>

                        <?php 
                        $altname=UserAuth::getCurrentAltUser();
                        
                        ?>
                        <div class="profile-details col-md-8 col-sm-8 col-xs-8">

                            <h3>
                                <a href="<?php echo $this->url("task=agent");?>"><?php echo UserAuth::getCurrentUser().(!empty($altname)?" <small>(".$altname.")</small>":"");?></a>

                                <!-- Available statuses: online, idle, busy, away and offline -->
                                <span class="profile-status online"></span>
                            </h3>

                            <p class="profile-title"><?php echo UserAuth::getCurrentRoolTitle();?></p>

                        </div>
						
                    </div>
                    <!-- USER INFO - END -->


<?php include('view/nav-menu.php'); ?>
 <ul class='wraplist'>	

<?php
// 		if (isset($_menus)){
// 			$groupstart=false;
// 		foreach ($_menus as $_mitem) {
// //			var_dump($CurrentMenuPages);
// 			if (isset($CurrentMenuPages[$_mitem])) {
// 				$_menu_item = $CurrentMenuPages[$_mitem];				
// 				$_selected_class = '';
// 				if (empty($_menu_item[2])) {					
// 					if (empty($_menu_item[4])) {	
// 						if($groupstart){
// 							echo '</ul></li>';
// 							$groupstart=false;
// 						}					
// 						echo '<li class="gs-menu-title">
//                                 <a href="#" class="gs-menu-title-a  '.(!empty($_menu_item[5])?$_menu_item[5]:"").' " ><i class="fa fa-'.$_menu_item[1].'"></i>
//                                 <span class="title">'.$_menu_item[0] . '</span>
//         						<span class="arrow"></span>
//                             </a> <ul class="sub-menu" >
//        					';
// 						$groupstart=true;
						
// 						//echo '<tr><td class="side_menu_item icon-' . $_menu_item[1] . '"><b>' . $_menu_item[0] . '</b></td></tr>';
// 					} else {
// 						echo '<li class="'.$_selected_class.'">
//                             ' . $_menu_item[4] . '
//                                 <i class="fa fa-'.$_menu_item[1].'"></i>
//                                 <span class="title">'.$_menu_item[0] . $extraTxt.'</span>
//                             </a>
//                         </li>';
// 						//echo '<tr><td class="side_menu_item icon-' . $_menu_item[1] . '">' . $_menu_item[4] . $_menu_item[0] . '</a></td></tr>';
// 					}
					
// 				} else {
// 				        if ($_menu_item[2] == 'EXT_URL') {
// 				                $_url = $_menu_item[3];
// 				        } else {					
// 				        	$_url = 'task=' . $_menu_item[2];
// 				        	if (!empty($_menu_item[3])) $_url .= '&act=' . $_menu_item[3];
// 				        	if (!empty($_menu_item[4])) $_url .= '&' . $_menu_item[4];
//                                         }
                                        
// 					if ($_menu_item[2] == $this->getControllerName()) {
// 						if (isset($smi_selection)) {							
// 							if ($_mitem == $smi_selection) $_selected_class = 'open';
// 						} else {
// 							$_sel_params =  $this->getControllerName() . '_' . $this->getActionName();							
							
// 							//if (substr($_sel_params, -4) == 'init') $_sel_params = rtrim($_sel_params, 'init');
// 							if (substr($_sel_params, -4) == 'init') $_sel_params = substr($_sel_params, 0, -4);
// 							//GPrint($_mitem);GPrint($_sel_params);
// 							if ($_mitem == $_sel_params) $_selected_class = 'open';
// 							/*added by sarwar agent report monthly dalily*/
// 							foreach ($_GET as $value){							
// 								if ($_mitem == $_sel_params."_".$value){
// 									$_selected_class = 'open';
// 									break;
// 								}
// 							}
// 							/* end added*/
// 						}
// 					} else {
// 						if (isset($smi_selection)) {
// 							if ($_mitem == $smi_selection) $_selected_class = 'open';
// 						}
// 					}
// 					$extraTxt = "";
// 					if (isset($_menu_item[2]) && $_menu_item[2] == "email" && isset($_menu_item[4]) && $_menu_item[4] == "type=myjob"){
// 						include('model/MMenuMyJobs.php');
// 						$myJobModel = new MMenuMyJobs();		
// 						/*$myjob = $myJobModel->getMyJobs(UserAuth::getCurrentUser());
// 						$newJob = !empty($myjob->num_news) ? $myjob->num_news : 0;
// 						$pendingJob = !empty($myjob->num_pendings) ? $myjob->num_pendings : 0;
// 						$numclientpendingsJob = !empty($myjob->num_client_pendings) ? $myjob->num_client_pendings : 0;
// 						//$extraTxt = " <div class='pull-right' style='padding-right:10px;'><span class='badge badge-success'>".$newJob."</span> <span class='badge badge-danger'>".$pendingJob." </span></div>";
// 						$extraTxt = " <div class='pull-right' style='padding-right:10px;'><span class='badge badge-info'>".$newJob."</span> <span class='badge badge-danger'>".$pendingJob." </span> <span class='badge badge-warning'>".$numclientpendingsJob." </span></div>";*/

// 						//Change variable for remove chashing with Email Dashboard Filter
//                         $myjob_new = $myJobModel->getMyJobs(UserAuth::getCurrentUser());
//                         $newJob = !empty($myjob_new->num_news) ? $myjob_new->num_news : 0;
//                         $pendingJob = !empty($myjob_new->num_pendings) ? $myjob_new->num_pendings : 0;
//                         $numclientpendingsJob = !empty($myjob_new->num_client_pendings) ? $myjob_new->num_client_pendings : 0;
//                         //$extraTxt = " <div class='pull-right' style='padding-right:10px;'><span class='badge badge-success'>".$newJob."</span> <span class='badge badge-danger'>".$pendingJob." </span></div>";
//                         $extraTxt = " <div class='pull-right' style='padding-right:10px;'><span class='badge badge-info'>".$newJob."</span> <span class='badge badge-danger'>".$pendingJob." </span> <span class='badge badge-warning'>".$numclientpendingsJob." </span></div>";
// 					}
// 					if($_selected_class=="open"){
// 						$_selected_class="active";
// 					}
					
// 					if ($_menu_item[2] != 'EXT_URL') {
// 					        $_url = $this->url($_url);
// 					}
					
// 					echo '<li class="'.$_selected_class.'"> 
//                             <a class=" '.$_selected_class." ".(!empty($_menu_item[5])?$_menu_item[5]:"").' " href="' . $_url . '"';
// 					//Added to open Call Control page in new tab 27-08-2015
// 					if ($_menu_item[3] == 'dialer' || $_menu_item[2] == 'EXT_URL'){
// 					    echo ' target="_blank"';
// 					}
//                     echo '><i class="fa fa-'.$_menu_item[1].'"></i>
//                                 <span class="title">'.$_menu_item[0] . $extraTxt.'</span>
//                             </a>
//                         </li>';
// 				}
// 			}
			
// 		}
// 		if($groupstart){
// 			echo '</ul></li>';
// 			$groupstart=false;
// 		}
// }
?>
<script type="text/javascript">

	$(function(e){
		try{
			var parentli=$(".sub-menu a.active").parents('.gs-menu-title');
			var submenu=$(".sub-menu a.active").parents('.sub-menu');
			parentli.addClass("open");
			parentli.find(".arrow").addClass("open");
			submenu.show();

			
		}catch(e){
			
			}
	});
	function closePassMsg(divClass){
		if(typeof divClass != 'undefined' && divClass != ""){
			$("#"+divClass).slideUp("slow", function() { $("."+divClass).remove();});
		}
	}
</script>
<?php /*?>

                        <li class=""> <a href="javascript:;"> <i class="fa fa-folder-open"></i> <span class="title">Menu Levels</span> <span class="arrow "></span> </a>
                            <ul class="sub-menu">
                                <li > <a href="javascript:;"> <span class="title">Level 1.1</span> </a> </li>
                                <li > <a href="javascript:;"> <span class="title">Level 1.2</span> <span class="arrow "></span> </a>
                                    <ul class="sub-menu">
                                        <li > <a href="javascript:;"> <span class="title">Level 2.1</span> </a></li>
                                        <li > <a href="ujavascript:;"> <span class="title">Level 2.2</span> <span class="arrow "></span></a> 
                                            <ul class="sub-menu">
                                                <li > <a href="javascript:;"> <span class="title">Level 3.1</span> <span class="arrow "></span></a> 
                                                    <ul class="sub-menu">
                                                        <li > <a href="ujavascript:;"> <span class="title">Level 4.1</span> </a> </li>
                                                        <li > <a href="ujavascript:;"> <span class="title">Level 4.2</span> </a> </li>
                                                    </ul>
                                                </li>
                                                <li > <a href="ujavascript:;"> <span class="title">Level 3.2</span> </a> </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

<?php */?>
                    </ul>

                </div>
                <!-- MAIN MENU - END -->


				<?php /*?>
                <div class="project-info">
					
                    <div class="block1">
                        <div class="data">
                            <span class='title'>New&nbsp;Orders</span>
                            <span class='total'>2,345</span>
                        </div>
                        <div class="graph">
                            <span class="sidebar_orders">...</span>
                        </div>
                    </div>

                    <div class="block2">
                        <div class="data">
                            <span class='title'>Visitors</span>
                            <span class='total'>345</span>
                        </div>
                        <div class="graph">
                            <span class="sidebar_visitors">...</span>
                        </div>
                    </div>
                   

                </div>
			 <?php // */?>


            </div>
            <!--  SIDEBAR - END -->
            <?php endif;?>
            <!-- START CONTENT -->

            <section id="<?php if (UserAuth::isLoggedIn() || UserAuth::isPageLoggedIn()){?>main-content<?php }?>" class=" ">
                <section class="wrapper" style='margin-top:60px;display:inline-block;width:100%;padding:15px 0 0 15px;'>
					<?php
            		if(isset($_GET['msgdays']) && UserAuth::isSetSesGCCPassExpDays() && UserAuth::getSesGCCPassExpDays() == $_GET['msgdays']){
            			UserAuth::showPassMessage(false, '');
            			echo '<div id="showPassExpInfo" class="col-lg-12 col-md-12 col-sm-12 col-xs-12"><div class="alert alert-warning" style="border-radius:3px; padding:5px 10px;">';
            			echo '<a style="display:inline-block; position:absolute; top:0; right:15px; background:#d38b4d;" onClick="javascript:closePassMsg(\'showPassExpInfo\');" ';
            			echo 'href="javascript:void(0)" class="btn btn-xs btn-danger"><i class="fa fa-times"> </i></a>';
            			echo 'Your current password will expire within '.$_GET['msgdays'].' day(s).<br>';
            			echo 'Please change your current password.';
            			echo '</div></div>';
            		}
            		?>
					<?php if (isset($pageTitle)):?>
                    <div class='col-lg-12 col-md-12 col-sm-12 col-xs-12 p-b-zero top-header'>
                        <div class="page-title">

                            <div class="pull-left">
                            	<ol class="breadcrumb">
                                    <?php if (isset($TopTitlePrefix)) echo $TopTitlePrefix;?>
                                    <li class="active">
                                        <strong><?php echo $pageTitle;?></strong>
                                    </li>
                                </ol>
                                     
							</div>

                            <div class="pull-right hidden-xs">
                                
                                <!--
                                <h1 class="title"><?php echo $pageTitle;?></h1> 
                                <ol class="breadcrumb">
                                    <?php if (isset($TopTitlePrefix)) echo $TopTitlePrefix;?>
                                    <li class="active">
                                        <strong><?php echo $pageTitle;?></strong>
                                    </li>
                                </ol>
                                 -->
                            </div>

                        </div>
                    </div>
                    <div class="clearfix"></div>
					<?php endif;?>
					
					<?php if ($this->isBoxWrapperEnabled()):?>
					<div class="col-md-12">
						<section class="box ">
					    	<header class="panel_header">
								<!--<h2 class="title pull-left"><?php /*echo !empty($pageTitle)?$pageTitle:"Undefined Page Title";*/?> </h2>-->
                                <h2 class="title pull-left">
                                    <?php
                                        $pageTitle2=!empty($pageTitle2)?$pageTitle2:'';
                                        if (!empty($pageTitle2)) echo $pageTitle2;
                                        else {
                                            echo !empty($pageTitle) ? $pageTitle : "Undefined Page Title";
                                        }
                                    ?>
                                </h2>
								<div class="actions panel_actions pull-right">
									<!--
									<i class="box_toggle fa fa-chevron-down"></i>
                					<i class="box_setting fa fa-cog" data-toggle="modal" href="#section-settings"></i>
                					<i class="box_close fa fa-times"></i>
                					-->
                					<?php include('view/top-menu.php');?>
									<?php
										if (isset($topMenuItems)) {
											foreach ($topMenuItems as $tmenu) {
												$dataattr="";
												if(!empty($tmenu['dataattr']) && is_array($tmenu['dataattr'])){
													foreach ($tmenu['dataattr'] as $kk=>$vv){
														$dataattr.=" data-$kk=\"$vv\" "; 
													}
												}
												
												if(!empty($tmenu['property']) && is_array($tmenu['property'])){
													foreach ($tmenu['property'] as $kk=>$vv){
														$dataattr.=" $kk=\"$vv\" ";
													}
												}
												if (isset($tmenu['title']))	$dataattr.=" title=\"$tmenu[title]\" ";
												$_cls_names = 'btn btn-'.(!empty($tmenu['color'])?$tmenu['color']:'purple');
												if (isset($tmenu['class'])) $_cls_names .= ' ' . $tmenu['class'];
												//if (isset($tmenu['img'])) $_cls_names .= ' fa fa-' . $tmenu['img'];
												echo '<a '.$dataattr.'  href="' . $this->url($tmenu['href']) . '" class="' . $_cls_names . '">';
												if (isset($tmenu['img'])) echo '<i class="'. $tmenu['img'] .'"></i> ';
													echo $tmenu['label'] . '</a> ';
											}
										}
				?>
                					
            					</div>
							</header>
    						<div class="content-body no-content-padding">
                                <?php if (!empty($grid_info_msg)){ ?>
                                <div class="alert alert-info fade in alert-dismissable" style="background-color: #d9edf7; border-color: #bce8f1; color: #31708f; padding: 6px 30px 6px 15px; border-radius: 3px;">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close" style="top: 0;">×</a>
                                    <?php echo $grid_info_msg; ?>
                                </div>
                                <?php } ?>
    				<?php endif;?>		  
					
					