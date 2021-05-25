<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="favicon.ico" />
<script src="js/jquery.min.js" type="text/javascript"></script>
<LINK href="css/main.css" rel="stylesheet" type="text/css">
<title>gPlex Contact Center</title>



<style>
ul, ol {
    margin: 0 0 10px 25px;
    padding: 0;
}
.btn-group {
    display: inline-block;
    font-size: 0;
    position: relative;
    vertical-align: middle;
    white-space: nowrap;
}

.btn-group > .btn {
    border-radius: 0;
    position: relative;
}
.btn-group > .btn, .btn-group > .dropdown-menu, .btn-group > .popover {
    font-size: 14px;
}
.btn-group > .btn:last-child, .btn-group > .dropdown-toggle {
    border-bottom-right-radius: 4px;
    border-top-right-radius: 4px;
}
.btn-group > .btn:first-child {
    border-bottom-left-radius: 4px;
    border-top-left-radius: 4px;
    margin-left: 0;
}



.dropup, .dropdown {
    position: relative;
}
.dropdown-toggle {
}
.dropdown-toggle:active, .open .dropdown-toggle {
    outline: 0 none;
}
.caret {
    border-left: 4px solid rgba(0, 0, 0, 0);
    border-right: 4px solid rgba(0, 0, 0, 0);
    border-top: 4px solid #000000;
    content: "";
    display: inline-block;
    height: 0;
    vertical-align: top;
    width: 0;
}
.dropdown .caret {
    margin-left: 2px;
    margin-top: 8px;
}
.dropdown-menu {
    background-clip: padding-box;
    background-color: #FFFFFF;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 6px;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
    display: none;
    float: left;
    left: 0;
    list-style: none outside none;
    margin: 2px 0 0;
    min-width: 160px;
    padding: 5px 0;
    position: absolute;
    top: 100%;
    z-index: 1000;
}
.dropdown-menu.pull-right {
    left: auto;
    right: 0;
}
.dropdown-menu .divider {
    background-color: #E5E5E5;
    border-bottom: 1px solid #FFFFFF;
    height: 1px;
    margin: 9px 1px;
    overflow: hidden;
}
.dropdown-menu > li > a {
    clear: both;
    color: #333333;
    display: block;
    font-weight: normal;
    line-height: 20px;
    padding: 3px 20px;
    white-space: nowrap;
}
.dropdown-menu > li > a:hover, .dropdown-menu > li > a:focus, .dropdown-submenu:hover > a, .dropdown-submenu:focus > a {
    background-color: #0081C2;
    background-image: linear-gradient(to bottom, #0088CC, #0077B3);
    background-repeat: repeat-x;
    color: #FFFFFF;
    text-decoration: none;
}
.dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus {
    background-color: #0081C2;
    background-image: linear-gradient(to bottom, #0088CC, #0077B3);
    background-repeat: repeat-x;
    color: #FFFFFF;
    outline: 0 none;
    text-decoration: none;
}
.dropdown-menu > .disabled > a, .dropdown-menu > .disabled > a:hover, .dropdown-menu > .disabled > a:focus {
    color: #999999;
}
.dropdown-menu > .disabled > a:hover, .dropdown-menu > .disabled > a:focus {
    background-color: rgba(0, 0, 0, 0);
    background-image: none;
    cursor: default;
    text-decoration: none;
}
.open {
}
.open > .dropdown-menu {
    display: block;
}
.dropdown-backdrop {
    bottom: 0;
    left: 0;
    position: fixed;
    right: 0;
    top: 0;
    z-index: 990;
}
.pull-right > .dropdown-menu {
    left: auto;
    right: 0;
}
.dropup .caret, .navbar-fixed-bottom .dropdown .caret {
    border-bottom: 4px solid #000000;
    border-top: 0 none;
    content: "";
}
.dropup .dropdown-menu, .navbar-fixed-bottom .dropdown .dropdown-menu {
    bottom: 100%;
    margin-bottom: 1px;
    top: auto;
}
.dropdown-submenu {
    position: relative;
}
.dropdown-submenu > .dropdown-menu {
    border-radius: 0 6px 6px;
    left: 100%;
    margin-left: -1px;
    margin-top: -6px;
    top: 0;
}
.dropdown-submenu:hover > .dropdown-menu {
    display: block;
}
.dropup .dropdown-submenu > .dropdown-menu {
    border-radius: 5px 5px 5px 0;
    bottom: 0;
    margin-bottom: -2px;
    margin-top: 0;
    top: auto;
}
.dropdown-submenu > a:after {
    border-color: rgba(0, 0, 0, 0) rgba(0, 0, 0, 0) rgba(0, 0, 0, 0) #CCCCCC;
    border-style: solid;
    border-width: 5px 0 5px 5px;
    content: " ";
    display: block;
    float: right;
    height: 0;
    margin-right: -10px;
    margin-top: 5px;
    width: 0;
}
.dropdown-submenu:hover > a:after {
    border-left-color: #FFFFFF;
}
.dropdown-submenu.pull-left {
    float: none;
}
.dropdown-submenu.pull-left > .dropdown-menu {
    border-radius: 6px 0 6px 6px;
    left: -100%;
    margin-left: 10px;
}
.dropdown .dropdown-menu .nav-header {
    padding-left: 20px;
    padding-right: 20px;
}



label {
	display:block;
}
.open > .dropdown-menu {
    display: block;
}




.open {
}

.btn-primary .caret, .btn-warning .caret, .btn-danger .caret, .btn-info .caret, .btn-success .caret, .btn-inverse .caret {
    border-bottom-color: #FFFFFF;
    border-top-color: #FFFFFF;
}
.btn .caret {
    margin-left: 0;
    margin-top: 8px;
}
a {
    color: #0088CC;
    text-decoration: none;
}

.multiselect-container > li > a > label.radio, .multiselect-container > li > a > label.checkbox {
    margin: 0;
}





.multiselect-container > li > a {
    padding: 0;
}





.radio, .checkbox {
    min-height: 20px;
    padding-left: 20px;
}

.radio input[type="radio"], .checkbox input[type="checkbox"] {
/*    float: left;*/
    margin-left: -20px;
}
.controls > .radio:first-child, .controls > .checkbox:first-child {
    padding-top: 5px;
}
.radio.inline, .checkbox.inline {
    display: inline-block;
    margin-bottom: 0;
    padding-top: 5px;
    vertical-align: middle;
}
.radio.inline + .radio.inline, .checkbox.inline + .checkbox.inline {
    margin-left: 10px;
}
input[type="file"], input[type="image"], input[type="submit"], input[type="reset"], input[type="button"], input[type="radio"], input[type="checkbox"] {
    width: auto;
}


</style>

<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-3.1.1.min.js"></script>
 
<!-- Include the plugin's CSS and JS: -->
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>


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

</script>

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
<div id="top-bar">
	<div id="top-bar-inner">
		<div class="fleft"><a href="./"><img src="image/logo_white.png" class="bottom" border="0" /></a></div>
        <div id="nav">
						<a href="./"><img src="image/icon/house.png" class="bottom" border="0" width="16" height="16" /> Home</a>
			<a href="index.php?task=password"><img src="image/top_menu_chpass.gif" class="bottom" border="0" width="16" height="16" /> Change Password</a>
									<a href="index.php?task=logout"><img src="image/user_go.png" class="bottom" border="0" width="16" height="16" /> Logout</a>
			        </div>
	</div>
</div>


<div id="wrapper">
	<div id="navigation">
			<div class="title-area">
				<div class="top-title">
					<span class="top-title-prefix"><a href="/p/cc/index.php?task=agents">Home</a> &raquo; <a href="/p/cc/index.php?task=email">Email</a> &raquo;</span> Ticket Details				</div>
            	<div class="fright">
															</div>
        	    <div class="clearb"></div>
			</div>
	</div>
<table class="body-content" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
<tr>
   			<td height="25" valign="top" class="side-menu-td">
        <table id="side_menu" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td class="side_menu_item icon-module"><b>Module</b></td></tr><tr><td class="side_menu_item icon-email smi_selected"><a href="/p/cc/index.php?task=email">Email</a></td></tr>        </table>
        <br />
		</td>
		<td height="330" valign="top" class="content-main">
	<script type="text/javascript">
function checkMsg(){
	if($("#message").val() != ""){		
		$("#ticket_message").attr('action', '/p/cc/index.php?task=email&act=details&tid=7328369');
		$("#ticket_message").submit();
	}else{
		alert("Message can not be empty!");
		return false;
	}
}
$(document).ready(function() {
	$(".email_title").click(function() {
		$("#b"+$(this).attr('id')).toggle();
	});
	
	$("#status_change").click(function() {
		$("#spn_new_status").show();
	});
	
	$("#btn_status_change").click(function() {
		var txt = $("#new_status option:selected").text();
		var val = $("#new_status").val();

		if (confirm('Are you sure to change the status to "' + txt + '"?')) {
			$.post('/p/cc/index.php?task=email&act=statusupdate', { 'tid':'7328369', 'status':val },
				function(data, status){
					if(status=='success' && data == 'Y') {
						window.location.href=window.location.href;
					} else {
						alert('Failed to update status!!' + status);
					}
				}
			);
		}
	});
	
	$(document).ready(function() {
		$('.multiselect').multiselect({buttonClass:'btn btn-info'});
	});
});
</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">

	<table width="90%" cellspacing="0" cellpadding="5" border="0" class="form_table" style="margin-bottom: 5px;">
	<tbody>
	<tr class="form_row_head">
		<td>Tiket Details :: [7328369] test Jan 20, 2013</td>
	</tr>
	<tr class="form_row">
		<td>
			<table width="100%">
				<tbody>
				<tr>
					<td width="25%"><b>User:</b> shamim@genuitysystems.com</td>
					<td width="50%"><b>Created:</b> 2013-01-20 04:26:59 by shamim@genuitysystems.com</td>
				</tr>
				<tr>
					<td><b>Skill:</b> </td>
					<td>
                    	<b>Status:</b> <font style="background-color:#eee;padding:1px;"><i>Open</i></font> on 2013-01-22 03:17:20 by root                    	 <span id="status_change" style="text-decoration:underline;color:#0000aa;cursor:pointer;">change status</span>
								&nbsp;<span id="spn_new_status" style="display:none;"><select id="new_status"><option value="P">Pending</option><option value="C">Pending - Client</option><option value="S">Served</option></select> <input type="button" id="btn_status_change" value="OK" />
								</span>
								                    </td>
				</tr>
				</tbody>
			</table>
		</tr>
	</tbody>
	</table>

	<table class="form_table">
			            <tr class="form_row">
				<td valign="top">
				<form method="post" enctype="application/x-www-form-urlencoded" name="ticket_message" id="ticket_message">
					<label for="message">Message <font class="required">*</font></label><br>
					<textarea style="width:90%; height: 145px;" id="message" name="message"></textarea><br /><br />
					<input class="form_submit_button" type="button" onClick="checkMsg()" value="Add Message" id="submitTicket" name="submitTicket">
					
                    
                    <select class="multiselect" multiple="multiple">
<option value="cheese">Cheese</option>
<option value="tomatoes">Tomatoes</option>
<option value="mozarella">Mozzarella</option>
<option value="mushrooms">Mushrooms</option>
<option value="pepperoni">Pepperoni</option>
<option value="onions">Onions</option>
</select>
				</form>
				</td>
			</tr>
            						<tr class="form_row_head">
				<td style="cursor:pointer;" class="email_title" id="m002">&nbsp;<div style="display:inline-block;width:300px;"><b>User:</b> root </div>&nbsp; <b>Time:</b> processing &nbsp; <b>Subject:</b> [7328369] test Jan 20, 2013</td>
			</tr>
			<tr>
				<td bgcolor="#FFFFFF" style="text-align:left;padding:5px;display:block;" id="bm002"><span>test reply<br></span></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
						<tr class="form_row_head">
				<td style="cursor:pointer;" class="email_title" id="m001">&nbsp;<div style="display:inline-block;width:300px;"><b>User:</b> shamim@genuitysystems.com </div>&nbsp; <b>Time:</b> 2013-01-20 04:26:59 &nbsp; <b>Subject:</b> [7328369] test Jan 20, 2013</td>
			</tr>
			<tr>
				<td bgcolor="#FFFFFF" style="text-align:left;padding:5px;display:none;" id="bm001"><span><HTML><br />
<HEAD><br />
<META content="text/html; charset=windows-1257" http-equiv=Content-Type><br />
<META content="OPENWEBMAIL" name=GENERATOR><br />
</HEAD><br />
<BODY bgColor=#ffffff><br />
<font size="2">Test 1<font size="2"><font size="2"><font size="2"><font size="2"><br />
<br /><br />
<br />
<br />Thanks &amp; Regards <br />
<br />
<br /><br />
 <br />
<br />
<br /><br />
Shamim Rayhan Mazumder <br />
<br />
<br /><br />
System Engineer, <br />
<br />
<br /><br />
Genuity Systems Ltd. <br />
<br />
<br /><br />
E-mail: shamim@genuitysystems.com <br />
<br />
<br /><br />
Tel: +880-2-8057038 <br />
<br />
<br /><br />
Cell: <br />
<br />
+88-0171-4020407<br />
<br />
<br /><br />
=================== <br />
<br />
<br /><br />
Powered By Genuity Systems Ltd. <br />
<br />
<br /><br />
<br />
<br /><br />
</font><br />
<br />
</font><br />
<br />
</font><br />
<br />
</font><br />
<br />
</font><br />
<br />
</BODY><br />
</HTML><br />
<br />
<br></span></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
				</table>          <br />

        </td>
	</tr>
</table>
<div id="footer"><p>&copy; 2007 - 2014 <a href="http://www.gplex.us" target="_blank" class="btn-link">gPlex</a>. All rights reserved</p></div>
</div>
</body>
</html>
