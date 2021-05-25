<!DOCTYPE html>
<html class=" <?php echo $this->IsIE()?"b-ie":"";?>">
    <head>       
        <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
        <meta charset="utf-8" />
         <?php if( $this->IsIE()){?>
        <meta http-equiv="X-UA-Compatible" content="IE=9,chrome=1">
        <?php }?>
        <title>gPlex Contact Center :: Login</title>
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
        <link href="<?php echo site_url().base_url();?>assets/fonts/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo site_url().base_url();?>assets/css/animate.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo site_url().base_url();?>assets/plugins/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" type="text/css"/>
        <!-- CORE CSS FRAMEWORK - END -->

        <!-- CORE CSS TEMPLATE - START -->
        <link href="<?php echo site_url().base_url();?>assets/css/style.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo site_url().base_url();?>assets/css/responsive.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo site_url().base_url();?>assets/css/theme/<?php echo strtolower(UserAuth::getDBSuffix()); ?>_style.css" rel="stylesheet" type="text/css"/>
        <!-- CORE CSS TEMPLATE - END -->
    </head>
    <!-- END HEAD -->

    <!-- BEGIN BODY -->
    <body class=" login_page">
        <div class="login-wrapper">
            <div id="login" class="login col-lg-4 col-md-5 col-sm-5 col-xs-6">
                <h1><a href="#" title="Login Page" tabindex="-1">User Login</a></h1>
                <div class="col-md-offset-2 col-md-8 col-sm-7 col-xs-12">
                    <form name="login" id="login" method="post" autocomplete="off" action="<?php echo $this->url('task='.$request->getControllerName());?>" class="col-sm-12">
    					<input type="hidden" name="lparam" value="<?php echo $lparam;?>" />                    
                        <div class="col-sm-12">
                            <h4>Account: <b><?php echo UserAuth::getAccountName();?></b></h4>
                        </div>
                        <div class="col-sm-12 mb-45">
                            <?php if (!empty($errMsg)):?><div class="alert alert-danger form_error_message"><i class="fa fa-times"></i> <?php echo $errMsg;?></div><?php endif;?>
                        </div>
                        
                        <div class="col-sm-12">
                            <div class="group">      
                                <input type="text" name="user" value="<?php echo (!empty($errMsg)) ? '' : $request->getPost('user');?>" size="20" maxlength="10" required="">
                                <span class="highlight"></span>
                                <span class="bar"></span>
                                <label>User</label>
                            </div>
                          
                            <div class="group">      
                                <input type="password" name="pass" size="20" maxlength="15" autocomplete="off" required="">
                                <span class="highlight"></span>
                                <span class="bar"></span>
                                <label>Password</label>
                            </div>
                            <!-- <input type="submit" name="wp-submit" id="wp-submit" class="btn btn-orange" value="Sign In" /> -->
                            <button name="wp-submit" class="pure-material-button-contained">Sign In</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="right_block_content" class="col-lg-8 col-md-7 col-sm-7 col-xs-8">
                <div class="row">
                    <div class="col-md-12">
                        <img src="<?php echo $this->url();?>assets/images/<?php echo strtolower(UserAuth::getDBSuffix()); ?>_client_logo.png" class="img-responsive logo-img">
                    </div>
                </div>
            </div>
        </div>
        <!-- LOAD FILES AT PAGE END FOR FASTER LOADING -->

        <!-- CORE JS FRAMEWORK - START --> 
        <script src="<?php echo site_url().base_url();?>assets/js/jquery-1.11.2.min.js" type="text/javascript"></script> 
        <script src="<?php echo site_url().base_url();?>assets/js/jquery.easing.min.js" type="text/javascript"></script> 
        <script src="<?php echo site_url().base_url();?>assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script> 
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


        <!-- General section box modal start -->
        <div class="modal" id="section-settings" tabindex="-1" role="dialog" aria-labelledby="ultraModal-Label" aria-hidden="true">
            <div class="modal-dialog animated bounceInDown">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Section Settings</h4>
                    </div>
                    <div class="modal-body">
                        Body goes here...
                    </div>
                    <div class="modal-footer">
                        <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                        <button class="btn btn-success" type="button">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- modal end -->
    </body>
</html>



