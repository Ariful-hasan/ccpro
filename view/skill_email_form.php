<link href="css/form.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
function displayMinuteSpan(val) {
	//alert('1');
	if(val == 1) {
		document.getElementById('spn_find_last_agent').style.display = 'inline';
		//document.getElementById('find_last_agent').value = '0';
	} else {
		document.getElementById('spn_find_last_agent').style.display = 'none';
	}
}


function checkForm() {

	var find_l_agent = document.getElementById('sel_find_last_agent').value;
	if (find_l_agent > 0) {
		var time_limit = document.getElementById('find_last_agent').value;
		if (time_limit <= 0) {
			alert('Time limit of find last agent should be positive.');
			return false;
		} else if (time_limit > 168) {
			alert('Maximum allowed time limit of find last agent is 168 hour.');
			return false;
		}
	}
	
	return true;
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
		<td class="form_column_caption" width="36%">Name:</td>
		<td>
			<input type="text" name="skill_name" size="40" maxlength="25" value="<?php echo $skill->skill_name;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><b>PopUp application:</b></td>
		<td>
        	<input type="text" name="popup_url" size="40" maxlength="200" value="<?php echo $skill->popup_url;?>" />
     	</td>
	</tr>
	<tr class="form_row">
		<td valign=top class="form_column_caption"><b>Call routing:</b></td>
		<td >
			<?php $this->html_options('acd_mode', 'acd_mode', $acd_mode_options, $skill->acd_mode);?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td valign=top class="form_column_caption"><b>Find last agent:</b></td>
		<td >
			<?php if ($skill->find_last_agent <= 0):?>
				<select name="sel_find_last_agent" id="sel_find_last_agent" onchange="displayMinuteSpan(this.value);">
					<option value="1">Yes</option>
					<option value="0" selected="selected">No</option>
				</select>
				<span id="spn_find_last_agent" style="display:none;">
					Time limit: <input type="text" name="find_last_agent" id="find_last_agent" SIZE="3" maxlength=3 value="<?php echo $skill->find_last_agent;?>" /> hour
				</span>
			<?php else:?>
				<select name="sel_find_last_agent" id="sel_find_last_agent" onchange="displayMinuteSpan(this.value)">
					<option value="1" selected="selected">Yes</option>
					<option value="0">No</option>
				</select>
				<span id="spn_find_last_agent">
					Time limit: <input type="text" name="find_last_agent" id="find_last_agent" SIZE="3" maxlength=3 value="<?php echo $skill->find_last_agent;?>" /> hour
				</span>
			<?php endif;?>
		</td>
	</tr>
	<tr class="form_row">
		<td valign=top class="form_column_caption"><b>Max message(s) in queue:</b></td>
		<td >
			<input type="text" name="max_calls_in_queue" size="10" maxlength="4" value="<?php echo $skill->max_calls_in_queue;?>">
		</td>
	</tr>
	<tr class="form_row_alt">
		<td valign=top class="form_column_caption"><b>Agent ring time out:</b></td>
		<td >
			<input type="text" name="agent_ring_timeout" size="10" maxlength="4" value="<?php echo $skill->agent_ring_timeout;?>"> <span class="form_column_extra_info">(sec)</span>
        </td>
	</tr>
	<tr class="form_row">
		<td valign=top class="form_column_caption"><b>Delay between calls:</b></td>
		<td >
			<input type="text" name="delay_between_calls" size="10" maxlength="4" value="<?php echo $skill->delay_between_calls;?>"> <span class="form_column_extra_info">(sec)</span>
        </td>
	</tr>
	<tr class="form_row">
		<td valign=top class="form_column_caption"><b>Skill Type:</b></td>
		<td >
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
	<tr class="form_row_alt">
		<td valign=top class="form_column_caption"><b>Email(s):</b></td>
		<td >
			<textarea type="text" id="emails" name="emails" style="width:340px;height:80px;" warp><?php echo $skill->emails;?></textarea>
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
