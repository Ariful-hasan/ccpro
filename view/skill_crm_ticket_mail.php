<?php 
if ($this->chat_module && $emailmod == 'Y'){
    $num_select_box = 6;
?>
<script src="js/ckeditor/ckeditor.js" type="text/javascript"></script>
<script src="js/ckeditor/adapters/jquery.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/form.css" type="text/css">

<!-- Include the plugin's CSS and JS: -->
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
<script src="js/dpselections.js"></script>
<style type="text/css">
.multiselect-container > li > a > label > input[type="checkbox"] {
    margin-left: -30px;
    margin-top: 3px;
}
.form_table input[type="submit"] {
    width: auto !important;
}
</style>

<script type="text/javascript">
set_loadurl("<?php echo $this->url("task=email&act=dispositionchildren");?>");
set_num_select_box(<?php echo $num_select_box;?>);

$(document).ready(function() {
	parent.$.fn.colorbox.resize({
        innerWidth: 950,
        innerHeight: 600
    });
    
	CKEDITOR.config._skillid = '';
	CKEDITOR.config._disp_id = '';
	CKEDITOR.config.extraPlugins = 'mtemplate';
	
    $('.multiselect').multiselect({buttonClass:'btn'});
    $( 'textarea#mail_body' ).ckeditor();
    load_sels(<?php echo count($disposition_ids);?>);

    if($('.alert-success').length > 0 && $('.frm_ticket_tab').length > 0){
        $('.frm_ticket_tab').remove();
    }
});

function reload_sels_e(i, val) {
	CKEDITOR.config._disp_id = val;
	reload_sels(i, val);
}

function reload_skill_emails(val)
{
	$select = $("#skill_email");
	$select.html('<option value="">Select</option>');

	if (val.length > 0) {

		CKEDITOR.config._skillid = val;
		
		$.ajax({
			type: "POST",
			url: "<?php echo $this->url("task=email&act=skillemails");?>",
			data: { sid: val },
			dataType: "json"
		})
		.done(function(data) {
			if (data.length > 0) {
				$.each(data, function(key, val){
					$select.append('<option value="' + val + '">' + val + '</option>');
				})
			}
		})
		.fail(function() {
			//
		})
		.always(function() {
		});

		$.ajax({
			type: "POST",
			url: "<?php echo $this->url("task=email&act=signature-text");?>",
			data: { sid: val }
		})
		.done(function(data) {
			if (typeof data != 'undefined') {
				$( 'textarea#mail_body' ).val(data);
			}
		})
		.fail(function() {
			//
		})
		.always(function() {
		});

		$.ajax({
			type: "POST",
			url: "<?php echo $this->url("task=email&act=skillDispositions");?>",
			data: { sid: val },
			dataType: "json"
		})
		.done(function(data) {
			if (typeof data != 'undefined' && data != null && data != null) {
				$selector = $("#disposition_id0");
				$selector.html('<option value="">Select</option>');
				$.each(data, function(key, val){
					$selector.append('<option value="' + key + '">' + val + '</option>');
				});
				var totalBox = "<?php echo $num_select_box; ?>";
				totalBox = parseInt( totalBox );
				for ( var idnum = 1; idnum < totalBox; idnum++) {
					option = $('<option></option>').attr("value", "").text("Select");
					selectId = "#disposition_id" + idnum;
					$(selectId).empty().append(option);
					$(selectId).hide();
				}
			}
		})
		.fail(function() {
		})
		.always(function() {
		});
	}
}

function checkMsg() {
	
	var pattern = "<?php echo $replace_text_pattern; ?>";
	if($("#mail_body").val() != ""){
		var msg = $("#mail_body").val().toLowerCase();
		if (pattern != "" && pattern != null && msg.indexOf(pattern.toLowerCase()) >= 0) {
			alert("You must replace the fixed text: "+pattern);
		} else {
			var st = $("#status").val();
			if (st.length == 0) {
				alert('Please set the status of the ticket!');
				$( "#status" ).focus();
				return false;
			}
			return true;
		}
	} else {
		alert("Message can not be empty!");
	}
	return false;
}

</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<?php if (!empty($errMsg) && $errType === 0){;}else { ?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" onsubmit="return checkMsg();">
<table class="form_table" id="frm_ticket_tab" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Ticket Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Ticket Owner's Name:</td>
		<td>
			<input type="text" name="name" size="30" maxlength="50" value="<?php echo $name;?>" />
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption"><span class="required">*</span> Ticket Owner's Email:</td>
		<td>
			<input type="text" name="email" size="30" maxlength="50" value="<?php echo $email;?>" />
     	</td>
	</tr>

	<tr class="form_row_alt">
		<td class="form_column_caption">CC:</td>
		<td>
			<select class="multiselect" multiple="multiple" name="cc[]"><?php 
						if (is_array($ccmails)) { 
							foreach ($ccmails as $ccmail) { 
								echo '<option value="'.$ccmail->email.'">'.$ccmail->name.'</option>';
							}
						} ?></select>
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">BCC:</td>
		<td>
			<select class="multiselect" multiple="multiple" name="bcc[]"><?php 
						if (is_array($ccmails)) { 
							foreach ($ccmails as $ccmail) { 
								echo '<option value="'.$ccmail->email.'">'.$ccmail->name.'</option>';
							}
						} ?></select>
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Skill:</td>
		<td>
			<select name="skill_id" id="skill_id" onChange="reload_skill_emails(this.value);">
				<option value="">Select</option>
				<?php if (is_array($skills)) {
					foreach ($skills as $skill) {
						if (isset($skill->active) && $skill->active == 'Y'){
							echo '<option value="' . $skill->skill_id . '"';
							if ($skill_id == $skill->skill_id) echo ' selected';
							echo '>' . $skill->skill_name . '</option>';
						}
					}
				}
				?>
			</select>
		</td>
	</tr>
<?php  /* if (count($emails) > 0) { */ ?>
	<tr class="form_row">
		<td class="form_column_caption"><span class="required">*</span> Skill Email:</td>
		<td>
			<select name="skill_email" id="skill_email">
				<option value="">Select</option>
				<?php if (is_array($emails)) {
					foreach ($emails as $semail) {
						echo '<option value="' . $semail . '"';
						if ($skill_email == $semail) echo ' selected';
						echo '>' . $semail . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
<?php /*}*/ ?>

	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Disposition:</td>
		<td>
<?php
				for ($i=0; $i<$num_select_box; $i++) {
					echo '<select name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels_e('.$i.', this.value);"><option value="">Select</option>';
					echo '</select> ';
				}
				
?>
		</td>
	</tr>
	
	<tr class="form_row">
		<td class="form_column_caption"><span class="required">*</span> Ticket Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="100" value="<?php echo $title;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%" valign="top"><span class="required">*</span> Text:</td>
		<td>
			<textarea name="mail_body" id="mail_body" style="width:600px;height:200px;"><?php echo $mail_body;?></textarea>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Status:</td>
		<td>
			<select name="status" id="status">
				<option value="">Select</option>
				<?php if (is_array($changable_status)) {
					foreach ($changable_status as $st) {
						echo '<option value="' . $st . '"';
						if ($status == $st) echo ' selected';
						echo '>' . $email_model->getTicketStatusLabel($st) . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
		    <input type='hidden' name='sid' value="<?php echo $serviceid; ?>">
    		<input type='hidden' name='tid' value="<?php echo $templateid; ?>">
    		<input type='hidden' name='callid' value="<?php echo $callid; ?>">
    		<input type='hidden' name='agentid' value="<?php echo $agentid; ?>">
    		<input type='hidden' name='emailmod' value="<?php echo $emailmod; ?>">
			<input class="form_submit_button" type="submit" value="  Generate Ticket  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
<?php 
}
}else {
?>
<script src="js/ckeditor/ckeditor.js" type="text/javascript"></script>
<script src="js/ckeditor/adapters/jquery.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/form.css" type="text/css">

<style type="text/css">
.form_table td.form_column_caption {
    font-family: Tahoma;
    font-size: 12px;
}
.form_table { border:1px solid #B6B6B6; }
.form_table tr.errMsg td {
    font-family: Tahoma;
    font-size: 14px;
	color: #CB5959;
}
div.errMessage{
	font-family: Georgia;
    font-size: 16px;
	color: #CB5959;
	font-weight: bold;
    padding-top: 200px;
}
div.sucMessage{
	font-family: Georgia;
    font-size: 16px;
	color: #648F4E;
	font-weight: bold;
    padding-top: 200px;
}
</style>
<?php 
//echo "Has Email Module=".$this->chat_module;
if ($errtype == 2 && !empty($errmsg)){
    echo "<div class='errMessage'>$errmsg</div>";    
}elseif ($errtype == 0 && !empty($errmsg)){
    echo "<div class='sucMessage'>$errmsg</div>";
}else {
?>
<form name='dispositionForm' id='dispositionForm' action="<?php echo $this->url("task=crm_in&act=ticket-submit");?>" method='post'>
<table class="form_table" cellspacing="0" cellpadding="4" border="0" align="center">
	<tbody>
	<tr class="form_row_head">
	    <td colspan="2" align="center">Ticket Information</td>
	</tr>
	<tr class="form_row errMsg"><td colspan="2" align="center">&nbsp;<?php echo $errtype==1 && !empty($errmsg) ? "<b>Error:</b> ".$errmsg : ""; ?></td></tr>
	<tr class="form_row">
		<td class="form_column_caption" align="right">Email(s):</td>
		<td>
			<input type="text" name="tkt_email_to" id="tkt_email_to" size="30" maxlength="120" value="<?php echo $ticketEmail; ?>" style="height:unset; padding:3px 10px;" />
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" align="right">Subject:</td>
		<td>
			<input type="text" name="email_subject" id="email_subject" size="30" maxlength="100" value="<?php echo $ticketSubject; ?>" style="height:unset; padding:3px 10px;" />
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" align="right">Text:</td>
		<td>
			<textarea id="mail_body" name="mail_body" class="note" rows="5"><?php echo $ticketMailBody; ?></textarea>
		</td>
	</tr>
	<tr class="form_row">
		<td>&nbsp;</td>
		<td>
		<input type='hidden' name='sid' value="<?php echo $serviceid; ?>">
		<input type='hidden' name='tid' value="<?php echo $templateid; ?>">
		<input type='hidden' name='callid' value="<?php echo $callid; ?>">
		<input type='hidden' name='agentid' value="<?php echo $agentid; ?>">
		<input type='hidden' name='emailmod' value="<?php echo $emailmod; ?>">
		<input id='btnSend' type='submit' class='btn btn-default btn-send' value='Generate Ticket'>
		</td>
	</tr>
	</tbody>
</table>
</form>
<script type="text/javascript">
$( document ).ready( function() {
	$( 'textarea#mail_body' ).ckeditor();
});
</script>
<?php 
}
}
?>