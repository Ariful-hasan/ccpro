<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php if(isset($metaText)) { echo $metaText; }?>
<link rel="shortcut icon" href="favicon.ico" />
<script src="js/jquery.min.js" type="text/javascript"></script>
<LINK href="css/main.css" rel="stylesheet" type="text/css">
<title>gPlex Contact Center</title>

<script type="text/javascript">

var winPopupWidth = 400; // sets a default width for browsers who do not understand screen.width below
var winPopupHeight = 400; // ditto for height
var newPopupWindow;
if (screen){ // weeds out older browsers who do not understand screen.width/screen.height
	winPopupWidth = screen.width;
	winPopupHeight = screen.height;
}

function popupWindow(win){
	
	newPopupWindow = window.open(win,'newWin','toolbars=no,menubar=no,location=no,scrollbars=no,resizable=yes,width='+winPopupWidth+',height='+winPopupHeight+',left=0,top=0');
	newPopupWindow.focus();
}
function closeMsg(divClass){
	if(typeof divClass != 'undefined' && divClass != ""){
		$("."+divClass).slideUp("slow", function() { $("."+divClass).remove();});
	}
}
</script>

<?php if (isset($reportHeader)):?>
<style type="text/css">
#wrapper {
	width: 98%;
}
</style>
<?php endif;?>
<!--[if lt IE 9]>
	<link rel="stylesheet" type="text/css" href="css/ie8-adjust.css" />
<![endif]-->

<!--[if gte IE 9]>
  <style type="text/css">
    .gradient {
       filter: none;
    }
  </style>
<![endif]-->

</head>

<body>
<?php	include('view/nav-menu.php'); ?>
<div id="top-bar">
	<div id="top-bar-inner">
		<div class="fleft"><a href="./"><img src="image/logo_white.png" class="bottom" border="0" /></a></div>
        <div id="nav">
			<?php if (UserAuth::isLoggedIn()):?>
			<a href="./"><img src="image/icon/house.png" class="bottom" border="0" width="16" height="16" /> Home</a>
			<a href="index.php?task=password"><img src="image/top_menu_chpass.png" class="bottom" border="0" width="16" height="16" /> Change Password</a>
			<?php endif;?>
			<?php if (UserAuth::isLoggedIn() || UserAuth::isPageLoggedIn()):?>
			<a href="index.php?task=logout"><img src="image/user_go.png" class="bottom" border="0" width="16" height="16" /> Logout</a>
			<?php endif;?>
        </div>
	</div>
</div>


<div id="wrapper">
<?php if (UserAuth::isLoggedIn()):?>
	<div id="navigation">
			<div class="title-area">
				<div class="top-title">
					<?php if (isset($pageTitle)) echo '<span class="top-title-prefix">' . $TopTitlePrefix . '</span> ' . $pageTitle;?>
				</div>
            	<div class="fright">
					<?php include('view/top-menu.php');?>
					<?php
					if (isset($topMenuItems)) {
						foreach ($topMenuItems as $tmenu) {
							$_cls_names = 'btn btn-small';
							if (isset($tmenu['class'])) $_cls_names .= ' ' . $tmenu['class'];
							echo '<a href="' . $this->url($tmenu['href']) . '" class="' . $_cls_names . '">';
							if (isset($tmenu['img'])) echo '<img src="image/' . $tmenu['img'] . '" class="bottom" border="0" width="16" height="16" /> ';
							echo $tmenu['label'] . '</a> ';
						}
					}
				?>
				</div>
        	    <div class="clearb"></div>
			</div>
	</div>
<?php endif;?>
<table class="body-content" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
<tr>
   	<?php if (isset($reportHeader) || !UserAuth::isLoggedIn()):?>
		<td height="330" class="content-main">
	<?php else: ?>
		<td height="25" valign="top" class="side-menu-td">
        <table id="side_menu" width="100%" border="0" cellspacing="0" cellpadding="0">
<?php
		foreach ($_menus as $_mitem) {
//			var_dump($CurrentMenuPages);
			if (isset($CurrentMenuPages[$_mitem])) {
				$_menu_item = $CurrentMenuPages[$_mitem];
				if (empty($_menu_item[2])) {
					if (empty($_menu_item[4])) echo '<tr><td class="side_menu_item icon-' . $_menu_item[1] . '"><b>' . $_menu_item[0] . '</b></td></tr>';
					else echo '<tr><td class="side_menu_item icon-' . $_menu_item[1] . '">' . $_menu_item[4] . $_menu_item[0] . '</a></td></tr>';
				} else {
					$_selected_class = '';
					$_url = 'task=' . $_menu_item[2];
					if (!empty($_menu_item[3])) $_url .= '&act=' . $_menu_item[3];
					if (!empty($_menu_item[4])) $_url .= '&' . $_menu_item[4];

					if ($_menu_item[2] == $this->getControllerName()) {
						if (isset($smi_selection)) {
							if ($_mitem == $smi_selection) $_selected_class = ' smi_selected';
						} else {
							$_sel_params =  $this->getControllerName() . '_' . $this->getActionName();
							//if (substr($_sel_params, -4) == 'init') $_sel_params = rtrim($_sel_params, 'init');
							if (substr($_sel_params, -4) == 'init') $_sel_params = substr($_sel_params, 0, -4);
							if ($_mitem == $_sel_params) $_selected_class = ' smi_selected';
						}
					} else {
						if (isset($smi_selection)) {
							if ($_mitem == $smi_selection) $_selected_class = ' smi_selected';
						}
					}
					$extraTxt = "";
					if (isset($_menu_item[2]) && $_menu_item[2] == "email" && isset($_menu_item[4]) && $_menu_item[4] == "type=myjob"){
						include('model/MMenuMyJobs.php');
						$myJobModel = new MMenuMyJobs();		
						$myjob = $myJobModel->getMyJobs(UserAuth::getCurrentUser());
						$newJob = !empty($myjob->num_news) ? $myjob->num_news : 0;
						$pendingJob = !empty($myjob->num_pendings) ? $myjob->num_pendings : 0;
						$extraTxt = " <div class='jobstatusdiv'><span class='newmyjob'>".$newJob."</span> <span class='penmyjob'>".$pendingJob."</span></div>";
					}
					
					echo '<tr><td class="side_menu_item icon-' . $_menu_item[1] . $_selected_class . '"><a href="' . $this->url($_url) . '">' . $_menu_item[0] . $extraTxt . '</a></td></tr>';
				}
			}
		}
?>
        </table>
        <br />
		</td>
		<td height="330" valign="top" class="content-main">
		<?php 
		if(isset($_GET['msgdays']) && isset($_SESSION['sesGCCPassExpDays']) && $_SESSION['sesGCCPassExpDays'] == $_GET['msgdays']){
			UserAuth::showPassMessage(false, '');
			echo '<div class="alert alert-error">';
			echo '<a style="display: inline-block; position: absolute; top: 4px; right: 19px;" onClick="javascript:closeMsg(\'alert-error\');" ';
			echo 'href="javascript:void(0)"><img src="image/cancel.png"></a>';
			echo 'Your current password will expire within '.$_GET['msgdays'];
			echo ' day(s).<br>Please change your current password.';
			echo '</div>';
		}
		?>
	<?php endif;?>
