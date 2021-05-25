<?php
$class1 = 'form_row_alt';
$class2 = 'form_row';
//$display_ttl_text = false;
if ($tts_support) {
	$class1 = 'form_row';
	$class2 = 'form_row_alt';
}
?>
<link href="css/form.css" rel="stylesheet" type="text/css">
<script src="js/func_list_box.js" type="text/javascript"></script>

<style type="text/css">

div.input-tag {
	width: 30px;
	display:inline-block;
	text-align:right;
	padding-right: 3px;
}
.form_table td.form_column_caption {
    width: 190px;
}
</style>
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
		//document.getElementById('spn_welcome_voice').style.display = 'inline';
		//document.getElementById('queue_position_time').value = '0';
	} else {
		//document.getElementById('spn_welcome_voice').style.display = 'none';
	}
}

function changeUnattendedFileStatus(action_name)
{
	if(action_name == "AH" || action_name == "AV") {
		//document.getElementById('tr_unattended_file').style.display = 'table-row';
		//document.getElementById('tr_submit').className = 'form_row_alt';
	} else {
		//document.getElementById('tr_unattended_file').style.display = 'none';
		//document.getElementById('tr_submit').className = 'form_row';
	}
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

	var announcement_file_en = document.getElementById('announcement_file_en').value;
	if(announcement_file_en.length > 0) {
		if(!checkExt('announcement_file_en')) return false;
	}

	var announcement_file_bn = document.getElementById('announcement_file_bn').value;
	if(announcement_file_bn.length > 0) {
		if(!checkExt('announcement_file_bn')) return false;
	}
	
	var find_l_agent = document.getElementById('sel_find_last_agent').value;
	if (find_l_agent > 0) {
		var time_limit = document.getElementById('find_last_agent').value;
		if (time_limit <= 0) {
			alert('Time limit of find last agent should be positive.');
			return false;
		} else if (time_limit > 600) {
			alert('Maximum allowed time limit of find last agent is 600 minute.');
			return false;
		}
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

function displayPopupOpt(ptype)
{
	//document.getElementById('spn_purl').style.display = 'none';
	//document.getElementById('spn_template').style.display = 'none';

	if(ptype == "U") {
		//document.getElementById('spn_purl').style.display = 'inline';
	} else if(ptype == "T") {
		//document.getElementById('spn_template').style.display = 'inline';
	} else {
	}
}

</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_skill" enctype="multipart/form-data" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=update");?>" onsubmit="return checkForm();">
<input type="hidden" name="skillid" value="<?php if (isset($skillid)) echo $skillid;?>" />

<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Skill Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="40%">Name:</td>
		<td>
			<input type="text" name="skill_name" size="30" maxlength="25" value="<?php echo $skill->skill_name;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
	        <td class="form_column_caption">Short Name:</td>
	        <td>
	                <input type="text" name="short_name" size="30" maxlength="8" value="<?php echo $skill->short_name;?>" />
	        </td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><b>PopUp type:</b></td>
		<td>
			<select name="popup_type">
            <option value="U"<?php if ($skill->popup_type == 'U') echo ' selected="selected"';?>>URL</option>
            <option value="T"<?php if ($skill->popup_type == 'T') echo ' selected="selected"';?>>Skill CRM Template</option>
            <option value="C"<?php if ($skill->popup_type == 'C') echo ' selected="selected"';?>>Campaign  CRM</option>
            </select>
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption"><b>PopUp URL:</b></td>
		<td>
			<input type="text" name="popup_url" size="30" maxlength="200" value="<?php echo $skill->popup_url;?>" />
		</td>
	</tr>
<?php 
$tts_support=false;
if ($tts_support):?>
	<tr class="form_row">
    	<td class="form_column_caption">Enable TTS :</td>
		<td>
			<select id="TTS" name="TTS">
				<option value="N"<?php if ($skill->TTS == 'N') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($skill->TTS == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
             &nbsp; <font style="font-size:9px;">Applicable only for English language</font>
		</td>
	</tr> 
<?php endif;?>

	<tr class="<?php echo $class2;?>">
		<td class="form_column_caption" valign="top"><b>Welcome voice:</b></td>
		<td>
			<select id="welcome_voice" name="welcome_voice" onchange="displayWelcomeSpan(this.value);">
				<option value="N"<?php if ($skill->welcome_voice == 'N') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($skill->welcome_voice == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
		</td>
	</tr>
	
	<tr class="<?php echo $class1;?>">
		<td valign=top class="form_column_caption"><b>Call routing:</b></td>
		<td >
			<?php $this->html_options('acd_mode', 'acd_mode', $acd_mode_options, $skill->acd_mode);?>
		</td>
	</tr>
	<tr class="<?php echo $class2;?>">
		<td valign=top class="form_column_caption"><b>Find last agent:</b></td>
		<td >
			<?php if ($skill->find_last_agent <= 0):?>
				<select name="sel_find_last_agent" id="sel_find_last_agent" onchange="displayMinuteSpan(this.value);">
					<option value="1">Yes</option>
					<option value="0" selected="selected">No</option>
				</select>
				<span id="spn_find_last_agent" style="display:none;">
					Time limit: <input type="text" name="find_last_agent" id="find_last_agent" SIZE="3" maxlength=3 value="<?php echo $skill->find_last_agent;?>" /> minute
				</span>
			<?php else:?>
				<select name="sel_find_last_agent" id="sel_find_last_agent" onchange="displayMinuteSpan(this.value)">
					<option value="1" selected="selected">Yes</option>
					<option value="0">No</option>
				</select>
				<span id="spn_find_last_agent">
					Time limit: <input type="text" name="find_last_agent" id="find_last_agent" SIZE="3" maxlength=3 value="<?php echo $skill->find_last_agent;?>" /> minute
				</span>
			<?php endif;?>
		</td>
	</tr>
	<tr class="<?php echo $class1;?>">
		<td valign=top class="form_column_caption"><b>Max call(s) in queue:</b></td>
		<td >
			<input type="text" name="max_calls_in_queue" size="10" maxlength="4" value="<?php echo $skill->max_calls_in_queue;?>">
		</td>
	</tr>
   
	<tr class="<?php echo $class1;?>">
		<td valign=top class="form_column_caption"><b>Agent ring time out:</b></td>
		<td >
			<input type="text" name="agent_ring_timeout" size="10" maxlength="4" value="<?php echo $skill->agent_ring_timeout;?>"> <span class="form_column_extra_info">(sec)</span>
        </td>
	</tr>
	<tr class="<?php echo $class2;?>">
		<td valign=top class="form_column_caption"><b>Auto answer:</b></td>
		<td >
			<select id="auto_answer" name="auto_answer">
				<?php if ($skill->auto_answer == 'N'):?>
				<option value="N" selected>Disable</option>
				<option value="Y">Enable</option>
				<?php else:?>
				<option value="N">Disable</option>
				<option value="Y" selected>Enable</option>
				<?php endif;?>
			</select>
		</td>
	</tr>
	<tr class="<?php echo $class1;?>">
		<td valign=top class="form_column_caption"><b>Service level:</b></td>
		<td >
			<input type="text" name="service_level" size="10" maxlength="4" value="<?php echo $skill->service_level;?>"> <span class="form_column_extra_info">(sec)</span>
        </td>
	</tr>


	<tr class="<?php echo $class2;?>">
		<td valign=top class="form_column_caption"><b>Delay between calls:</b></td>
		<td >
			<input type="text" name="delay_between_calls" size="10" maxlength="4" value="<?php echo $skill->delay_between_calls;?>"> <span class="form_column_extra_info">(sec)</span>
        </td>
	</tr>	
	<tr class="<?php echo $class1;?>">
		<td valign=top class="form_column_caption"><b>Voice logger:</b></td>
		<td>
			<?php $this->html_options('record_all_calls', 'record_all_calls', $record_all_calls_options, $skill->record_all_calls);?>
                </td>
	</tr>
	<tr class="<?php echo $class2;?>">
                <td valign=top class="form_column_caption"><b>Abandoned calls :</b></td>
                <td>
                        <?php $this->html_options('callback_type', 'callback_type', $callback_options, $skill->callback_type);?>
                </td>
        </tr>
	<tr class="<?php echo $class1;?>">
		<td valign=top class="form_column_caption"><b>Start Filler after:</b></td>
		<td >
			<input type="text" name="start_filler_after" size="10" maxlength="3" value="<?php echo $skill->start_filler_after;?>"> <span class="form_column_extra_info">(sec)</span>
        </td>
	</tr>

    <tr class="<?php echo $class2;?>">
		<td valign=top class="form_column_caption"><b>Start MOH Filler after:</b></td>
		<td >
			<input type="text" name="moh_filler_after" size="10" maxlength="3" value="<?php echo $skill->moh_filler_after;?>"> <span class="form_column_extra_info">(sec)</span>
        </td>
	</tr>

	<tr class="form_row_alt">
		<td class="form_column_caption">Skill Type:</td>
		<td>
			<?php 
			$skill_type_list = get_report_skill_type_list(); 
			?>
			<select id="skill_type" name="skill_type" class="form-control skill-type">
				<?php
				foreach ($skill_type_list as $key => $value) {
				?>
				<option value="<?php echo $key; ?>" <?php if($skill->skill_type == $key){?>selected="selected"<?php } ?> ><?php echo $value; ?></option>
				<?php } ?>
				<option value="O" <?php if($skill->skill_type == 'O'){?>selected="selected"<?php } ?> >Outbound</option>
			</select>
     	</td>
	</tr>
<?php if($skill_qtype == 'C'){ ?>
    <tr class="form_row">
		<td class="form_column_caption"> From Email Address:</td>
		<td>
			<input type="text" name="from_email" size="30" maxlength="50" value="<?php echo $chat_email->from_email;?>" />
		</td>
	</tr>
    <tr class="form_row_alt">
		<td class="form_column_caption"> From Email Name:</td>
		<td>
			<input type="text" name="from_name" size="30" maxlength="30" value="<?php echo $chat_email->from_name;?>" />
     	</td>
	</tr>
<?php } ?>
	<tr class="<?php echo $class2;?>" id="tr_submit">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button pull-right" type="submit" value="  Submit  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
<script type="text/javascript">
	//showUnattainOptions('{$unattain_announcement_file}', '{$unattain_ivr_name}');
	var luser = '<?php echo UserAuth::getCurrentUser();?>';
	changeUnattendedFileStatus('<?php echo $skill->unattended_calls_action;?>');
	displayPopupOpt('<?php echo $skill->popup_type;?>');
</script>
