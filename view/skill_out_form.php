<script src="js/func_list_box.js" type="text/javascript"></script>

<script type="text/javascript">
$(document).ready(function(){
	$('.record_voice').click(function(){
		var elm_id = $(this).attr('id');
		if(confirm('Are you sure to record file?')) {
			$.post('cc_web_agi.php', { 'page':'ivr', 'agent_id':luser, 'option':elm_id },
				function(data, status){
					if(status == 'success') {
						if(data!='Y')	alert(data);
					} else {
						alert('Failed to commuinicate!!');
					}
				});
		}
	});
});

function displayMinuteSpan(val) {
	//alert('1');
	if(val == 1) {
		document.getElementById('spn_find_last_agent').style.display = 'inline';
		//document.getElementById('find_last_agent').value = '0';
	} else {
		document.getElementById('spn_find_last_agent').style.display = 'none';
	}
}

function displayQPosition(val)
{
	if(val == 'QPosition') {
		document.getElementById('spn_announce_queue_position').style.display = 'inline';
		document.getElementById('queue_position').value = 'Once';
		document.getElementById('spn_queue_position').style.display = 'none';
		//document.getElementById('queue_position_time').value = '60';
	} else if(val == 'Simple') {
		document.getElementById('spn_announce_queue_position').style.display = 'none';
		//document.getElementById('queue_position').value = 'Once';
		document.getElementById('queue_position_time').value = '60';
		document.getElementById('spn_queue_position').style.display = 'inline';
		
	} else {
		document.getElementById('spn_announce_queue_position').style.display = 'none';
		document.getElementById('spn_queue_position').style.display = 'none';
	}
}

function displayQPositionTime(val)
{
	if(val == 'Periodic') {
		document.getElementById('spn_queue_position').style.display = 'inline';
		document.getElementById('queue_position_time').value = '60';
	} else {
		document.getElementById('spn_queue_position').style.display = 'none';
		document.getElementById('queue_position_time').value = '999';
	}
}

function displayWelcomeSpan(val) {
	if(val == 'Y') {
		document.getElementById('spn_welcome_voice').style.display = 'inline';
		//document.getElementById('queue_position_time').value = '0';
	} else {
		document.getElementById('spn_welcome_voice').style.display = 'none';
	}
}

function changeUnattendedFileStatus(action_name, file_value)
{
	if(action_name == "AH" || action_name == "AV") {
		document.getElementById('spn_unattended_file_text').style.visibility = 'visible';
		document.getElementById('spn_unattended_file').style.visibility = 'visible';
	} else {
		document.getElementById('spn_unattended_file_text').style.visibility = 'hidden';
		document.getElementById('spn_unattended_file').style.visibility = 'hidden';
	}
	//document.getElementById('off_hour_announcement_file').value = file_value;
}

function LoadListBoxValues( toLst, fromLst, values ) {
	var TSel = document.getElementById(toLst);
	var FSel = document.getElementById(fromLst);
	var val_arr = values.split(',');
	var val_len = val_arr.length;
	if(val_len <= 1) {
		if(val_arr[0].length == 0)
			return false;
	}
	
	for(i=0; i<val_len; i++) {
		var theVal = val_arr[i];
		if(theVal.length == 0) continue;
		for(j=0; j<FSel.length; j++) {
			if(FSel.options[j].value == theVal) {
				newValue=FSel.options[j].value;
				newText=FSel.options[j].text;
				var newOpt1 = new Option(newText, newValue);
				TSel.options[TSel.length] = newOpt1;
				FSel.options[j] = null;
				break;
			}
		}
	}
}

function checkForm() {
	var welcome_file = document.getElementById('welcome_file').value;
	if(welcome_file.length > 0) {
		if(!checkExt('welcome_file')) return false;
	}

	var announcement_file = document.getElementById('announcement_file').value;
	if(announcement_file.length > 0) {
		if(!checkExt('announcement_file')) return false;
	}
	
	return true;
}

function checkExt(fileId) {
	var filename = document.getElementById(fileId).value;
	var filelength = parseInt(filename.length) - 3;
	var fileext = filename.substring(filelength,filelength + 3);

	if (fileext != 'wav'){
		alert ('You can only upload wav files.');
		document.getElementById(fileId).focus();
		return false;
	}

	return true;
}

function showDialType(dltype)
{
	if (dltype == 'P') {
		$('.dial-type').show();
	} else {
		$('.dial-type').hide();
	}
}

function playFile(evt)
{
	$.post('cc_web_agi.php', { 'page':'Q', 'agent_id':luser, 'option':evt },
		function(data, status) {
			if (status == 'success') {
				if(data != 'Y') alert(data);
			} else {
				alert("Failed to communicate!");
			}
	});
}

</script>

<?php if (!empty($errMsg)):?><div class="error-msg"><?php echo $errMsg;?></div><?php endif;?>

<form name="frm_skill" enctype="multipart/form-data" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=update");?>" onsubmit="return checkForm();">
<input type="hidden" name="skillid" value="<?php if (isset($skillid)) echo $skillid;?>" />
<input type="hidden" name="type" value="out" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Skill Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Name:</td>
		<td>
			<input type="text" name="skill_name" size="30" maxlength="25" value="<?php echo $skill->skill_name;?>" />
		</td>
	</tr>
	<tr class="form_row_alt" style="display:none;">
		<td class="form_column_caption"><b>Dial Type:</b></td>
		<td>
			<select id="dial_type1" name="dial_type1" onchange="showDialType(this.value);" disabled="disabled">
				<option value="M"<?php if ($skill->dial_type == 'M') echo ' selected="selected"';?>>Manual</option>
				<option value="P"<?php if ($skill->dial_type == 'P') echo ' selected="selected"';?>>Predictive</option>
			</select>
            <input type="hidden" name="dial_type" value="<?php echo $skill->dial_type;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td valign=top class="form_column_caption"><b>Voice logger:</b></td>
		<td>
			<?php $this->html_options('record_all_calls', 'record_all_calls', $yes_no_options, $skill->record_all_calls);?>
        </td>
	</tr>

	<tr class="form_row">
		<td class="form_column_caption"><b>PopUp application:</b></td>
		<td>
        	<input type="text" name="popup_url" size="30" maxlength="200" value="<?php echo $skill->popup_url;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt dial-type">
		<td class="form_column_caption"><b>Welcome voice:</b></td>
		<td>
			<select id="welcome_voice" name="welcome_voice" onchange="displayWelcomeSpan(this.value);">
				<option value="N"<?php if ($skill->welcome_voice == 'N') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($skill->welcome_voice == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
            
			<span id="spn_welcome_voice"<?php if ($skill->welcome_voice == 'N') echo ' style="display:none;"';?>>

			<br /><br />
			ENG : <input name="welcome_file_en" id="welcome_file_en" type="file" /> 
			<?php if (file_exists($this->file_upload_path . 'Q/' . $skillid . '/E/fl_welcome.wav')):?>
				<a href="#" onClick="playFile('<?php echo $skillid;?>~E~fl_welcome');"><img style="vertical-align:bottom;" border="0" src="image/btn_play.gif" /></a>
			<?php endif;?>

            <br />BAN : <input name="welcome_file_bn" id="welcome_file_bn" type="file" /> 
			<?php if (file_exists($this->file_upload_path . 'Q/' . $skillid . '/B/fl_welcome.wav')):?>
				<a href="#" onClick="playFile('<?php echo $skillid;?>~B~fl_welcome');"><img style="vertical-align:bottom;" border="0" src="image/btn_play.gif" /></a>
			<?php endif;?>

			</span>
		</td>
	</tr>
	

	<tr class="form_row dial-type">
		<td valign=top class="form_column_caption"><b>Max call(s) per agent:</b></td>
		<td >
			<input type="text" name="max_out_calls_per_agent" size="10" maxlength="4" value="<?php echo $skill->max_out_calls_per_agent;?>">
		</td>
	</tr>
	<tr class="form_row_alt dial-type">
		<td valign=top class="form_column_caption"><b>Service level:</b></td>
		<td >
			<input type="text" name="service_level" size="10" maxlength="4" value="<?php echo $skill->service_level;?>"> <span class="form_column_extra_info">(sec)</span>
        </td>
	</tr>

    <tr class="form_row_alt">
		<td valign=top class="form_column_caption"><b>Auto answer:</b></td>
		<td >
			<?php $this->html_options('auto_answer', 'auto_answer', $yes_no_options, $skill->auto_answer);?>
        </td>
	</tr>
	<tr class="form_row_alt dial-type">
		<td valign=top class="form_column_caption"><b>Anouncement time out:</b></td>
		<td >
			<input type="text" name="anouncement_timeouot" size="10" maxlength="4" value="<?php echo $skill->anouncement_timeouot;?>"> <span class="form_column_extra_info">(sec)</span>
        </td>
	</tr>

	<tr class="form_row dial-type">
		<td valign=top class="form_column_caption"><span id="spn_unattended_file_text"><b>Announcement file:</b></span></td>
		<td>
			<span id="spn_unattended_file">

			ENG : <input name="announcement_file_en" id="announcement_file_en" type="file" /> 
			<?php if (file_exists($this->file_upload_path . 'Q/' . $skillid . '/E/fl_unattended.wav')):?>
				<a href="#" onClick="playFile('<?php echo $skillid;?>~E~fl_unattended');"><img style="vertical-align:bottom;" border="0" src="image/btn_play.gif" /></a>
			<?php endif;?>

			<br />BAN : <input name="announcement_file_bn" id="announcement_file_bn" type="file" /> 
			<?php if (file_exists($this->file_upload_path . 'Q/' . $skillid . '/B/fl_unattended.wav')):?>
				<a href="#" onClick="playFile('<?php echo $skillid;?>~B~fl_unattended');"><img style="vertical-align:bottom;" border="0" src="image/btn_play.gif" /></a>
			<?php endif;?>

			</span>
		</td>
	</tr>


	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  Submit  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
<script type="text/javascript">
	//showUnattainOptions('{$unattain_announcement_file}', '{$unattain_ivr_name}');
	var luser = '<?php echo UserAuth::getCurrentUser();?>';
	showDialType('<?php echo $skill->dial_type;?>');
</script>
