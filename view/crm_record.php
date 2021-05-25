<script src="js/jquery.watermark.min.js" type="text/javascript"></script>
<script src="js/jquery.clockpick.1.2.2.pack.js" type="text/javascript"></script>
<!-- <script src="js/jquery.datePicker.js" type="text/javascript"></script>
<script src="js/date.js" type="text/javascript"></script> -->
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>

<link rel="stylesheet" href="css/report.css" type="text/css">
<link rel="stylesheet" href="css/form.css" type="text/css">
<link rel="stylesheet" href="css/clockpick.css" type="text/css">
<!-- <link rel="stylesheet" href="css/datePicker.css" type="text/css"> -->

<script type="text/javascript">
$(document).ready(function() {
	var chatarea = $(".page-chatapi");
    var chatwindow = $(".chatapi-windows");
    var topbar = $(".page-topbar");
    var mainarea = $("#main-content");
    var menuarea = $(".page-sidebar");

    menuarea.addClass("collapseit").removeClass("expandit").removeClass("chat_shift");
    topbar.addClass("sidebar_shift").removeClass("chat_shift");
    mainarea.addClass("sidebar_shift").removeClass("chat_shift");
    topbar.addClass("force-collpse");
    
	Date.format = 'yyyy-mm-dd';
	//$("#clk_time").clockpick({starthour: 0, endhour: 23, minutedivisions:12,military:true});
	//$('#clk_date').datePicker({clickInput:true,createButton:false,startDate:'<?php echo date("Y-m-d");?>'});
	$('#clk_date').datetimepicker({
		 timepicker:false,
		 format:'Y-m-d',
		 startDate:'<?php echo date("Y-m-d");?>',
		 minDate: '<?php echo date("Y-m-d");?>',
	});
	//$('#clk_date').watermark('Date');
	//$('#clk_time').watermark('Time');
	$('#clk_time').datetimepicker({
		datepicker:false,
		format:'H:i'
	});
	
	$( "#form_cinfo" ).validate({
		rules: {
			home_phone: {
				digits: true
			},
			office_phone: {
				digits: true
			},
			mobile_phone: {
				digits: true
			},
			other_phone: {
				digits: true
			},
			fax: {
				digits: true
			}, 
			email: {
				email: true
			}
		}
	});
	$( "#form_ainfo" ).validate({
		rules: {
			zip: {
				digits: true
			}
		}
	});
	
	var txt_edit = '<img width="16" height="16" border="0" src="image/icon/page_edit.png" class="bottom"> edit';
	var txt_save = '<img width="16" height="16" border="0" src="image/icon/page_save.png" class="bottom"> save';
	
	$("#edit_personal_info").click(function(event){
		event.stopPropagation();
		var val = $(this).html();
		val = val.substr(val.length - 4);

		if (val == 'edit') {
			$(".personal-val").hide();
			$(".personal-inp").show();
			$(this).html(txt_save);
		} else {
			
			$.ajax({
				type: "POST",
				url: "<?php echo $this->url('task='.$request->getControllerName()."&act=saveprofile&record_id=".$profile->record_id);?>",
				data: { first_name: $('#first_name').val(), last_name: $('#last_name').val(), middle_name: $('#middle_name').val(), title: $('#title').val(), DOB: $('#DOB').val() },
				beforeSend: function ( xhr ) {
					$("#fl_msg_saving").show();
				}
			}).done(function( msg ) {
				$("#fl_msg_saving").hide();
				if (msg > 0) {
					$("#fl_msg_success").show();
					$("#fl_msg_success").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				} else {
					$("#fl_msg_err").show();
					$("#fl_msg_err").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				}
				$("#sp_first_name").html($('#first_name').val());
				$("#sp_last_name").html($('#last_name').val());
				$("#sp_middle_name").html($('#middle_name').val());
				$("#sp_title").html($('#title').val());
				$("#sp_DOB").html($('#DOB').val());
				$(".personal-val").show();
				$(".personal-inp").hide();
				$(this).html(txt_edit);
			});
		}
	});


	$("#edit_address_info").click(function(event){
		event.stopPropagation();
		var val = $(this).html();
		val = val.substr(val.length - 4);
		
		if (val == 'edit') {
			$(".address-val").hide();
			$(".address-inp").show();
			$(this).html(txt_save);
		} else {
			
			var isValid = $( "#form_ainfo" ).valid();
			if (isValid) {
			
			$.ajax({
				type: "POST",
				url: "<?php echo $this->url('task='.$request->getControllerName()."&act=saveaddress&record_id=".$profile->record_id);?>",
				data: { house_no: $('#house_no').val(), street: $('#street').val(), landmarks: $('#landmarks').val(), city: $('#city').val(), state: $('#state').val(), zip: $('#zip').val(), country: $('#country').val() },
				beforeSend: function ( xhr ) {
					$("#fl_msg_saving").show();
				}
			}).done(function( msg ) {
				$("#fl_msg_saving").hide();
				if (msg > 0) {
					$("#fl_msg_success").show();
					$("#fl_msg_success").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				} else {
					$("#fl_msg_err").show();
					$("#fl_msg_err").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				}
				$("#sp_house_no").html($('#house_no').val());
				$("#sp_street").html($('#street').val());
				$("#sp_landmarks").html($('#landmarks').val());
				$("#sp_city").html($('#city').val());
				$("#sp_state").html($('#state').val());
				$("#sp_zip").html($('#zip').val());
				$("#sp_country").html($('#country').val());
				$(".address-val").show();
				$(".address-inp").hide();
				$(this).html(txt_edit);
			});
			
			} else {
				alert('Provid valid information');
			}
		}
	});


	$("#edit_contact_info").click(function(e){
		e.stopPropagation();
		var val = $(this).html();
		val = val.substr(val.length - 4);
		
		if (val == 'edit') {
			$(".contact-val").hide();
			$(".contact-inp").show();
			$(this).html(txt_save);
		} else {
			var isValid = $( "#form_cinfo" ).valid();
			if (isValid) {

			$.ajax({
				type: "POST",
				url: "<?php echo $this->url('task='.$request->getControllerName()."&act=savecontact&record_id=".$profile->record_id);?>",
				data: { home_phone: $('#home_phone').val(), office_phone: $('#office_phone').val(), mobile_phone: $('#mobile_phone').val(), other_phone: $('#other_phone').val(), fax: $('#fax').val(), email: $('#email').val() },
				beforeSend: function ( xhr ) {
					$("#fl_msg_saving").show();
				}
			}).done(function( msg ) {
				$("#fl_msg_saving").hide();
				if (msg > 0) {
					$("#fl_msg_success").show();
					$("#fl_msg_success").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				} else {
					$("#fl_msg_err").show();
						$("#fl_msg_err").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				}
				$("#sp_home_phone").html($('#home_phone').val());
				$("#sp_office_phone").html($('#office_phone').val());
				$("#sp_mobile_phone").html($('#mobile_phone').val());
				$("#sp_other_phone").html($('#other_phone').val());
				$("#sp_fax").html($('#fax').val());
				$("#sp_email").html($('#email').val());
				$(".contact-val").show();
				$(".contact-inp").hide();
				$(this).html(txt_edit);
			});
			
			} else {
				alert('Provid valid information');
			}
		}
	});

	$("#set_disposition").click(function (e) {
		e.stopPropagation();
		var disposition = $("#disposition").val();
		if (disposition.length > 0) {
		
		var selectedAgOpt = "";
		var selected = $("input[type='radio'][name='agent_opt']:checked");
		if (selected.length > 0) selectedAgOpt = selected.val();
		
		var cb_opt = $('#chk_callback').is(':checked') ? 'Y' : 'N';
		
		if (cb_opt == 'Y') {
			if ($("#days").val().length == 0 && $("#hrs").val().length == 0 && $("#min").val().length == 0 && $("#clk_date").val().length == 0) {
				alert('Provide callback schedule time !!');
				return false;
			}
		}
		
		$.ajax({
			type: "POST",
			url: "<?php echo $this->url('task='.$request->getControllerName()."&act=savedisposition&record_id=".$profile->record_id);?>",
			data: { disposition: disposition, note: $("#comment").val(), agent_opt: selectedAgOpt, days: $("#days").val(), hrs: $("#hrs").val(), min: $("#min").val(),
			 clk_date: $("#clk_date").val(), clk_time: $("#clk_time").val(), chk_callback:cb_opt },
				beforeSend: function ( xhr ) {
					$("#fl_msg_saving").show();
				}
			}).done(function( msg ) {
				$("#fl_msg_saving").hide();
				if (msg > 0) {
					$("#fl_msg_success").show();
					$("#fl_msg_success").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				} else {
					$("#fl_msg_err").show();
					$("#fl_msg_err").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				}
		});
		} else {
			alert('Select disposition !!');
		}
	});
	
	$("#clear_dt").click(function (e) {
		e.stopPropagation();
		$('#clk_date').val('');
		$('#clk_time').val('');
	});
	
	$("#clear_dial_schedule").click(function (e) {
		e.stopPropagation();
		if (confirm('Are you sure to delete existing callback schedule?')) {
			$.ajax({
			type: "POST",
			url: "<?php echo $this->url('task='.$request->getControllerName()."&act=delschedule&record_id=".$profile->record_id);?>",
			data: { campaign_id: '<?php echo $profile->campaign_id;?>' },
				beforeSend: function ( xhr ) {
					$("#fl_msg_saving").show();
				}
			}).done(function( msg ) {
				$("#fl_msg_saving").hide();
				if (msg > 0) {
					$("#fl_msg_success").show();
					$("#fl_msg_success").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				} else {
					$("#fl_msg_err").show();
					$("#fl_msg_err").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&record_id=".$profile->record_id);?>";
					});
				}
			});
		}
	});
});

function adjust_callback_option()
{
	var cb_opt = $('#chk_callback').is(':checked') ? false : true;
	$("#days").prop('disabled', cb_opt).val('');
	$("#hrs").prop('disabled', cb_opt).val('');
	$("#min").prop('disabled', cb_opt).val('');
	$("#clk_date").prop('disabled', cb_opt).val('');
	$("#clk_time").prop('disabled', cb_opt).val('');
	$("#ag_self").prop('disabled', cb_opt);
	$("#ag_any").prop('disabled', cb_opt);
}

function playFile(evt)
{
	$.post('cc_web_agi.php', { 'page':'CDR', 'agent_id':'<?php echo UserAuth::getCurrentUser();?>', 'option':evt },
		function(data, status) {
			if (status == 'success') {
				if(data != 'Y') alert(data);
			} else {
				alert("Failed to communicate!");
			}
	});
}

</script>
<style>

label.error {
display:block;
font-size:11px;
}
.popup-page-title  {
	font-size: 20px;
}
.profile-details-form td {
	font-size:15px;
	vertical-align:top;
}

.profile-details td, .personal-val, .address-val, .contact-val {
	font-size:15px;
}
.p-head td {
	border-bottom: 3px solid #9AC9DE;
	font-size: 17px;
	font-weight: bold;
	color: #2583AD;
	margin-bottom: 10px;
}
.p-value {
	font-weight: bold;
}
.p-br {
line-height: 30px;
}
.p-head-br {
line-height: 8px;
}
.personal-inp, .address-inp, .contact-inp {
	display:none;
}
#fl_msg_success {
	background-color:#009933;
	color: white;
	padding: 2px 5px;
	display: none;
}
#fl_msg_saving {
	background-color:#006699;
	color: white;
	padding: 2px 5px;
	display: none;
}
#fl_msg_err {
	background-color:#990000;
	color: white;
	padding: 2px 5px;
	display: none;
}
.act-head {
    color: #2583AD;
    font-size: 15px;
    font-weight: bold;
    margin-bottom: 10px;
}
.report_table tr.report_row td, .report_table tr.report_row_alt td {
	font-size: 13px;
}
.report_table tr.report_row_head th {
	font-size: 13px;
	padding: 3px;
}
.report_extra_info td, .report_table td {
	font-size:11px;
}
select {
	width: auto;
}
span.flash {
	z-index: 50;
	position: absolute;
    left: 310px;
    top: 20px;
}
</style>

<span class="flash" id="fl_msg_success">Information saved successfully !!</span>
<span class="flash" id="fl_msg_err">Failed to save information !!</span>
<span class="flash" id="fl_msg_saving">Saving information ...</span>

<?php
if (!empty($errMsg)) {
?>

<table width="100%" align="center" border="0" class="profile-details" cellpadding="1">
<tr class="p-head"><td colspan="4">Error</td></tr><tr><td colspan="4" class="p-head-br">&nbsp;</td></tr>
<tr><td colspan="4" align="center"><span style=" font-size:18px; font-weight:bold; background-color:brown; color:white; padding: 5px 50px;">No profile found !!</span></td></tr>
</table>
<?php 
} else {
?>

<table width="100%"><tr><td width="20%" valign="top">

<table width="100%" align="center" border="0" class="profile-details" cellpadding="1">
<tr><td>
<div class="act-head" style="font-size:17px; padding:5px;background: linear-gradient(to bottom, #4A98AF 44%, #328AA4 45%) repeat scroll 0 0 rgba(0, 0, 0, 0); color:#fff;">Quick Actions</div><br />

<label class="act-head">Set Disposition:</label><br /><br />
<select name="disposition" id="disposition">
<?php
if (is_array($dp_options)) {
	foreach ($dp_options as $key=>$val) {
		echo '<option value="'.$key.'">'.$val.'</option>';
	}
}
?>
</select> 

<br /><br /><label class="act-head">Note:</label><br /><br />
<textarea name="note" id="comment" maxlength="255" style="width:90%; height:140px;"></textarea>
<br /><br /><label class="act-head">Schedule Callback:</label><br /><br />
<input type="checkbox" name="chk_callback" id="chk_callback" value="Y" onchange="adjust_callback_option();" /> <label for="chk_callback">Set Callback</label><br /><br />
<label>Agent Option:</label> <input type="radio" name="agent_opt" id="ag_self" value="S" checked="checked" /> <label for="ag_self">Self</label> &nbsp; <input type="radio" name="agent_opt" id="ag_any" value="A" /> <label for="ag_any">Any one</label>
<br /><br />
<label>After:</label> <select name="days" id="days"><option value="">Days</option><?php for ($i=1;$i<=31; $i++) {echo '<option value="'.$i.'">'.$i.'</option>';};?></select> <select name="hrs" id="hrs"><option value="">Hrs</option><?php for ($i=1;$i<=23; $i++) {echo '<option value="'.$i.'">'.$i.'</option>';};?></select> <select name="min" id="min"><option value="">Min</option><?php for ($i=1;$i<=59; $i++) {echo '<option value="'.$i.'">'.$i.'</option>';};?></select> <br /><br />
<label>On:&nbsp;&nbsp;&nbsp;</label> <input class="date-pick" id="clk_date" type="text" size="10" name="clk_date" /> <input id="clk_time" type="text" size="8" name="clk_time" /> <a id="clear_dt" href="#" title="reset date-time"><img src="image/arrow_refresh_small.png" class="bottom" border="0" width="16" height="16" /></a>
<br /><br /><label>* <?php if (!empty($schedule_dial)) {echo 'Schedule: <b>' . date("d M H:i", $schedule_dial->trigger_time) . '</b> by <b>'; if (empty($schedule_dial->agent_id)) echo 'Any one'; else echo $schedule_dial->agent_id; echo ' <a id="clear_dial_schedule" href="#" title="delete schedule"><img src="image/cancel.png" class="bottom" border="0" width="16" height="16" /></a> ';} else { echo 'No callback schedule exist';} ?></label><br /><br /><div style="text-align:center;"><input type="button" class="btn btn-info" id="set_disposition" name="set_disposition" value="Save" /></div>
</td></tr>

</table>

</td><td valign="top" style="padding-left: 10px;">

<table width="100%" align="center" border="0" class="profile-details" cellpadding="1">
<tr class="p-head"><td colspan="4">Service Information</td></tr><tr><td colspan="4" class="p-head-br">&nbsp;</td></tr>

<tr><td width="20%">Dial Number:</td><td width="30%" class="p-value">&nbsp;<?php echo $profile->dial_number;?></td><td width="20%">Dial Count:</td><td class="p-value" width="30%">&nbsp;<?php echo $profile->dial_count;?></td></tr>

<tr><td>Campaign:</td><td class="p-value">&nbsp;<?php if ($profile->campaign_id == '0000') echo 'System'; else if ($profile->campaign_id == '1111') echo 'General'; else echo $profile->cp_title;?></td><td>Last Disposition:</td><td class="p-value">&nbsp;<?php echo $profile->dp_title;?></td></tr>



<tr><td>Lead:</td><td class="p-value">&nbsp;<?php echo $profile->lp_title;?></td><td>Last Dial Time:</td><td class="p-value">&nbsp;<?php if (!empty($profile->last_dial_time)) echo date("Y-m-d H:i:s", $profile->last_dial_time);?></td></tr>

<tr><td>Dial Attempted:</td><td class="p-value">&nbsp;<?php echo $profile->dial_attempted;?></td><td>Churn:</td><td class="p-value">&nbsp;<?php echo $profile->churn;?></td></tr>

<tr><td colspan="4" class="p-br">&nbsp;</td></tr>
<tr class="p-head"><td colspan="3">Personal Information</td><td align="right"><a href="#" id="edit_personal_info" class="btn btn-info btn-mini"><img width="16" height="16" border="0" src="image/icon/page_edit.png" class="bottom" /> edit</a></td></tr><tr><td colspan="4" class="p-head-br">&nbsp;</td></tr>
<tr>
<td width="20%">First Name:</td><td width="30%" class="p-value">&nbsp;
<span class="personal-val" id="sp_first_name"><?php echo $profile->first_name;?></span>
<span class="personal-inp"><input type="text" name="first_name" id="first_name" value="<?php echo $profile->first_name;?>" size="30" maxlength="15" /></span>
</td>
<td width="20%">Last Name:</td><td class="p-value">&nbsp;
<span class="personal-val" id="sp_last_name"><?php echo $profile->last_name;?></span>
<span class="personal-inp"><input type="text" name="last_name" id="last_name" value="<?php echo $profile->last_name;?>" size="30" maxlength="15" /></span>
</td></tr>
<tr><td>Middle Name:</td><td class="p-value">&nbsp;
<span class="personal-val" id="sp_middle_name"><?php echo $profile->middle_name;?></span>
<span class="personal-inp"><input type="text" name="middle_name" id="middle_name" value="<?php echo $profile->middle_name;?>" size="30" maxlength="15" /></span>
</td>
<td>Title:</td><td class="p-value">&nbsp;
<span class="personal-val" id="sp_title"><?php echo $profile->title;?></span>
<span class="personal-inp"><input type="text" name="title" id="title" value="<?php echo $profile->title;?>" size="30" maxlength="6" /></span>
</td></tr>
<tr><td>DOB:</td><td class="p-value">&nbsp;
<span class="personal-val" id="sp_DOB"><?php echo $profile->DOB;?></span>
<span class="personal-inp"><input type="text" name="DOB" id="DOB" value="<?php echo $profile->DOB;?>" size="30" maxlength="10" /></span>
</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>
</table>

<form id="form_ainfo">
<table width="100%" align="center" border="0" class="profile-details-form" cellpadding="1">
<tr class="p-head"><td colspan="3">Address</td><td align="right"><a href="#" id="edit_address_info" class="btn btn-info btn-mini"><img width="16" height="16" border="0" src="image/icon/page_edit.png" class="bottom" /> edit</a></td></tr><tr><td colspan="4" class="p-head-br">&nbsp;</td></tr>
<tr><td width="20%">House:</td><td width="30%" class="p-value">&nbsp;
<span class="address-val" id="sp_house_no"><?php echo $profile->house_no;?></span>
<span class="address-inp"><input type="text" name="house_no" id="house_no" value="<?php echo $profile->house_no;?>" size="30" maxlength="10" /></span>
</td><td width="20%">Street:</td><td width="30%" class="p-value">&nbsp;
<span class="address-val" id="sp_street"><?php echo $profile->street;?></span>
<span class="address-inp"><input type="text" name="street" id="street" value="<?php echo $profile->street;?>" size="30" maxlength="15" /></span>
</td></tr>
<tr><td>Landmarks:</td><td class="p-value">&nbsp;
<span class="address-val" id="sp_landmarks"><?php echo $profile->landmarks;?></span>
<span class="address-inp"><input type="text" name="landmarks" id="landmarks" value="<?php echo $profile->landmarks;?>" size="30" maxlength="15" /></span>
</td><td>City:</td><td class="p-value">&nbsp;
<span class="address-val" id="sp_city"><?php echo $profile->city;?></span>
<span class="address-inp"><input type="text" name="city" id="city" value="<?php echo $profile->city;?>" size="30" maxlength="15" /></span>
</td></tr>
<tr><td>State:</td><td class="p-value">&nbsp;
<span class="address-val" id="sp_state"><?php echo $profile->state;?></span>
<span class="address-inp"><input type="text" name="state" id="state" value="<?php echo $profile->state;?>" size="30" maxlength="15" /></span>
</td><td>ZIP:</td><td class="p-value">&nbsp;
<span class="address-val" id="sp_zip"><?php echo $profile->zip;?></span>
<span class="address-inp"><input type="text" name="zip" id="zip" value="<?php echo $profile->zip;?>" size="30" maxlength="10" /></span>
</td></tr>
<tr><td>Country:</td><td class="p-value">&nbsp;
<span class="address-val" id="sp_country"><?php echo $profile->country;?></span>
<span class="address-inp"><input type="text" name="country" id="country" value="<?php echo $profile->country;?>" size="30" maxlength="15" /></span>
</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>
</table>
</form>

<form id="form_cinfo">
<table width="100%" align="center" border="0" class="profile-details-form" cellpadding="1">
<tr class="p-head"><td colspan="3">Contact Details</td><td align="right"><a href="#" id="edit_contact_info" class="btn btn-info btn-mini"><img width="16" height="16" border="0" src="image/icon/page_edit.png" class="bottom" /> edit</a></td></tr><tr><td colspan="4" class="p-head-br">&nbsp;</td></tr>
<tr><td width="20%">Home Phone:</td>
<td width="30%" class="p-value">&nbsp;
<span class="contact-val" id="sp_home_phone"><?php echo $profile->home_phone;?></span>
<span class="contact-inp"><input type="text" name="home_phone" id="home_phone" value="<?php echo $profile->home_phone;?>" size="30" maxlength="13" /></span>
</td>
<td width="20%">Office Phone:</td><td width="30%" class="p-value">&nbsp;
<span class="contact-val" id="sp_office_phone"><?php echo $profile->office_phone;?></span>
<span class="contact-inp"><input type="text" name="office_phone" id="office_phone" value="<?php echo $profile->office_phone;?>" size="30" maxlength="13" /></span>
</td></tr>
<tr><td>Mobile Phone:</td>
<td class="p-value">&nbsp;
<span class="contact-val" id="sp_mobile_phone"><?php echo $profile->mobile_phone;?></span>
<span class="contact-inp"><input type="text" name="mobile_phone" id="mobile_phone" value="<?php echo $profile->mobile_phone;?>" size="30" maxlength="13" /></span>
</td>
<td>Other Phone:</td><td class="p-value">&nbsp;
<span class="contact-val" id="sp_other_phone"><?php echo $profile->other_phone;?></span>
<span class="contact-inp"><input type="text" name="other_phone" id="other_phone" value="<?php echo $profile->other_phone;?>" size="30" maxlength="13" /></span>
</td></tr>
<tr><td>Fax:</td>
<td class="p-value">&nbsp;
<span class="contact-val" id="sp_fax"><?php echo $profile->fax;?></span>
<span class="contact-inp"><input type="text" name="fax" id="fax" value="<?php echo $profile->fax;?>" size="30" maxlength="13" /></span>
</td>
<td>Email:</td><td class="p-value">&nbsp;
<span class="contact-val" id="sp_email"><?php echo $profile->email;?></span>
<span class="contact-inp"><input type="text" name="email" id="email" value="<?php echo $profile->email;?>" size="30" maxlength="35" /></span>
</td></tr>
</table>
</form>

<table width="100%" align="center" border="0" class="profile-details" cellpadding="1">
<tr><td colspan="4">&nbsp;</td></tr>
<tr class="p-head"><td colspan="4">Disposition History</td></tr><tr><td colspan="4" class="p-head-br">&nbsp;</td></tr>
<tr><td colspan="4" align="left">
<?php
if (is_array($records)) {
?>
<table class="report_extra_info">
<tr>
	<td><small>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?></small>
	</td>
</tr>
</table>

<table class="report_table">
<tr class="report_row_head"><td class="cntr">SL</td><td class="cntr">Time</td><td>Disposition</td><td>Agent</td><td>Note</td><td class="cntr">Audio</td></tr>
<?php
$i = $pagination->getOffset();
foreach ($records as $rec) {
$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
    <td class="cntr"><?php echo $i;?></td>
    <td class="cntr"><?php echo !empty($rec->tstamp) ? date("Y-m-d H:i:s", $rec->tstamp) : "";?></td>
    <td align="left">&nbsp;<?php echo $rec->title;?></td>
    <td><?php echo $rec->agent_id . ' ['.$rec->nick.']';?></td>
    <td align="left">&nbsp;<?php echo $rec->note;?></td>
    <td class="cntr"><?php
		if (!empty($rec->callid)) {
			$file_timestamp = substr($rec->callid, 0, 10);
			$yyyy = date("Y", $file_timestamp);
			$yyyy_mm_dd = date("Y_m_d", $file_timestamp);
			$sound_file_gsm = $this->voice_logger_path . "$yyyy/$yyyy_mm_dd/" . $rec->callid . ".gsm";
			if (file_exists($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
				$log->audio = "<a href=\"#\" onClick=\"playFile('$rec->callid')\"><img src=\"image/play2.gif\" title=\"gsm\" border=0></a>";
				if ($this->is_voice_file_downloadable) {
					$log->audio .= " <a target=\"blank\" href=\"$sound_file_gsm\"><img src=\"image/play_in_browser2.gif\" title=\"Play in browser\" border=0 width=\"15\"></a>";
				}

			} else {
				$log->audio = "NONE";
			}
		} else {
			$log->audio = "NONE";
		}
    	echo $log->audio;
	?>
	</td></tr>
<?php
}
?>
</table>
<table class="report_extra_info">
<tr>
	<td><small>
	<?php echo $pagination->createLinks();?>
	</small></td>
</tr>
</table>
<?php
} else {
	echo '<b>No record found !!</b>';
}
?>
</td></tr>

</table>

<td></tr></table>
<script>adjust_callback_option();</script>
<?php } ?>

<br />