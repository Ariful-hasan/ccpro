<?php

var_dump($_FILES);

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
<tr><td class="side_menu_item icon-module"><b>Module</b></td></tr><tr><td class="side_menu_item icon-monitor"><a href="/p/cc/index.php?task=email&act=dashboard">Dashboard</a></td></tr><tr><td class="side_menu_item icon-email smi_selected"><a href="/p/cc/index.php?task=email">Email</a></td></tr><tr><td class="side_menu_item icon-email"><a href="/p/cc/index.php?task=email&type=myjob">My Tickets</a></td></tr><tr><td class="side_menu_item icon-email"><a href="/p/cc/index.php?task=emailtemplate">Email Template</a></td></tr><tr><td class="side_menu_item icon-module"><b>Report</b></td></tr><tr><td class="side_menu_item icon-table"><a href="/p/cc/index.php?task=emailreport&act=disposition">Disposition</a></td></tr><tr><td class="side_menu_item icon-table"><a href="/p/cc/index.php?task=emailreport&act=status">Status</a></td></tr><tr><td class="side_menu_item icon-user"><a href="/p/cc/index.php?task=emailreport&act=agentactivity">Agent Activity</a></td></tr>        </table>
        <br />
		</td>
		<td height="330" valign="top" class="content-main">
	<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />
<script src="js/lightbox/js/jquery.colorbox-min.js"></script>
<script src="js/ckeditor/ckeditor.js"></script>
<script src="js/ckeditor/adapters/jquery.js"></script>

<script src="js/jquery.MultiFile.pack.js"></script>

<script>
//$( document ).ready( function() {
	//$( 'textarea#mail_body' ).ckeditor();
//} );
</script>
<script type="text/javascript">
var isSubmit = false;
var ticketStatus = '';

function setStatus()
{
	ticketStatus = $('#pstatus').val();
	$.colorbox.close();
}

function submit_form()
{
	if (isSubmit) {
		$("#st").val(ticketStatus);
		if($("#message").val() != ""){
//		alert($("#st").val());
//		return false;
			$("#ticket_message").attr('action', '/p/cc/index.php?task=email&act=details&tid=9676684');
			$("#ticket_message").submit();
		}else{
			alert("Message can not be empty!");
			return false;
		}
	}
	isSubmit = false;
}

function checkMsg(){

	if($("#message").val() != ""){		
		isSubmit = true;
		$('#pstatus').prop('selectedIndex',0);
		ticketStatus = '';
		$.colorbox({inline:true, width:"450", href:"#inline_content", onClosed:function(){ submit_form(); }});
		return false;
		//$("#ticket_message").attr('action', '/p/cc/index.php?task=email&act=details&tid=9676684');
		//$("#ticket_message").submit();
	}else{
		alert("Message can not be empty!");
		return false;
	}
}

$(document).ready(function() {

	 $('#att_file').MultiFile();

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
			$.post('/p/cc/index.php?task=email&act=statusupdate', { 'tid':'9676684', 'status':val },
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

$(".set-options").colorbox({
	onClosed:function(){ window.location = window.location.href;},
	iframe:true, width:"450", height:"300"
});

CKEDITOR.config.extraPlugins = 'mtemplate';
$( 'textarea#message' ).ckeditor();

});


</script>

<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">

	<table width="90%" cellspacing="0" cellpadding="5" border="0" class="form_table" style="margin-bottom: 5px;">
	<tbody>
	<tr class="form_row_head">
		<td>Tiket Details :: [9676684] First Mail With Multi Attachment</td>
	</tr>
	<tr class="form_row">
		<td>
			<table width="100%">
				<tbody>
				<tr>
					<td width="25%"><b>User:</b> ccenter21@gmail.com</td>
					<td width="50%"><b>Created:</b> 2014-05-13 23:54:15 by ccenter21@gmail.com</td>
				</tr>
				<tr>
					<td><b>Skill:</b> EMailTech</td>
					<td>
                    	<b>Status:</b> <font style="background-color:#eee;padding:1px;"><i>New</i></font> on 2014-05-13 23:54:15 by ccenter21@gmail.com                    	                        <a class="set-options" href="/p/cc/index.php?task=email&act=status&tid=9676684">
							Change status
						</a>
						                    </td>
				</tr>
				<tr>
					<td>
						<b>Assigned To:</b> 
						 
						                        <a class="set-options" href="/p/cc/index.php?task=email&act=assign&tid=9676684">
							Assign						</a>
											</td>
					<td>
						<b>Disposition:</b> 
						 
						                        <a class="set-options" href="/p/cc/index.php?task=email&act=disposition&tid=9676684">
							Add						</a>
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
				<form method="post" enctype="multipart/form-data" name="ticket_message" id="ticket_message" action="ui.php">
					<label for="message">Message <font class="required">*</font></label><br>
					<input type="hidden" name="st" id="st" value="" maxlength="1" />
                    <textarea style="width:90%; height: 145px;" id="message" name="message"></textarea>
					<input type="file" id="att_file" name="att_file[]" /><br />
					<input class="form_submit_button" type="submit" value="Reply" id="submitTicket" name="submitTicket">
				</form>
				</td>
			</tr>
            						<tr class="form_row_head">
				<td style="cursor:pointer;" class="email_title" id="m001">&nbsp;<div style="display:inline-block;width:300px;"><b>User:</b> ccenter21@gmail.com </div>&nbsp; <b>Time:</b> 2014-05-13 23:54:15 &nbsp; <b>Subject:</b> [9676684] First Mail With Multi Attachment</td>
			</tr>
			<tr>
				<td bgcolor="#FFFFFF" style="text-align:left;padding:5px;display:block;" id="bm001"><span>
                <div style="background-color:#E5F1F4;padding:3px 5px;margin-bottom:4px;"><font color="green">Attachments:</font> <a href="/p/cc/index.php?task=email&act=attachment&tid=9676684&sl=001&p=1">Minutes of Meeting-10_20140430.pdf</a> &nbsp; <a href="/p/cc/index.php?task=email&act=attachment&tid=9676684&sl=001&p=2">Download Rate Plan - new_2_2.csv</a> &nbsp; </div>Prefix,Name,Free Second,FPulse,NPulse,Rate,New_Rate<br />
88019,Bangladesh Banglalink,0,0,0,0.2690,0.2690<br />
88017,Bangladesh GP,0,0,0,1.5004,1.5004<br />
88015,Bangladesh Teletalk,0,0,0,1.0004,1.0004<br />
<br></span></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
				</table>

<div style='display:none'>
	<div id='inline_content'>
<div class="popup-body">
<center>
<div class="title-area">
<div class="top-title">Update status of ticket</div>
<div class="clearb"></div>
</div>
<table class="form_table" style="width:90%;">
	<tr class="form_row_alt">
		<td class="form_column_caption" style="text-align:center;"><b>Status:</b>
			<select name="pstatus" id="pstatus">
				<option value="">Select</option>
				<option value="P">Pending</option><option value="C">Pending - Client</option><option value="S">Served</option>			</select>
		</td>
	</tr>
<tr class="form_row">
		<td class="form_column_submit" style="text-align:center;padding:20px 0;">
			<input class="form_submit_button" type="button" value="OK" name="submitagent" onclick="setStatus();" />  &nbsp; &nbsp;
            <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="$.colorbox.close();" />
            
		</td>
	</tr>
</table>
</center>
</div>
	</div>
</div>
          <br />

        </td>
	</tr>
</table>
<div id="footer"><p>&copy; 2007 - 2014 <a href="http://www.gplex.us" target="_blank" class="btn-link">gPlex</a>. All rights reserved</p></div>
</div>
</body>
</html>
