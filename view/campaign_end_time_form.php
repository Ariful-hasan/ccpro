<link href="css/form.css" rel="stylesheet" type="text/css">
<?php if (!empty($errMsg)):?><font style="color:brown"><b><?php echo $errMsg;?></b></font><br /><br /><?php endif;?>

<script>
/*
function reset_calc()
{
	document.getElementById("frm_campaign").reset();
}

function calc_etime()
{
	var tstamp = <?php echo $campaign->end_time;?>;
	var given_val = document.getElementById("ctime").value;
	var added_sec = 0;
	
	if (given_val.length == 0) {
		alert('Provide time value');
		document.getElementById('ctime').foucs();
		return false;
	}
	
	if (isNaN(given_val)) {
		alert('Provide valid time');
		document.getElementById('ctime').foucs();
		return false;
	}
	var topt = document.getElementById("topt").value;

	if (topt == 'day') {
		added_sec = given_val * 86400;
	} else if (topt == 'hrs') {
		added_sec = given_val * 3600;
	} else {
		added_sec = given_val * 60;
	}
	
	var newsec = tstamp + added_sec;
	var date = new Date(newsec * 1000);
	
	var datevalues = [
         date.getFullYear()
        ,date.getMonth()+1
        ,date.getDate()
        ,date.getHours()
        ,date.getMinutes()
        ,date.getSeconds()
     ];
	 alert(datevalues);
}
*/
</script>

<form name="frm_campaign" id="frm_campaign" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="cid" value="<?php echo $cid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>End Time Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="40%">Campaign ID:</td>
		<td>
			<?php echo $campaign->campaign_id;?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Existing end time:</td>
		<td>
			<?php echo empty($campaign->end_time) ? '-' : date("Y-m-d H:i:s", $campaign->end_time);?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Time: </td>
		<td>
			<input type="text" name="ctime" id="ctime" size="5" maxlength="4" value="<?php echo $ctime;?>" style="width:48%" /> 
            <select name="topt" id="topt" style="width:48%"><option value="min"<?php if ($topt == 'min') echo ' selected';?>>Minute</option><option value="hrs"<?php if ($topt == 'hrs') echo ' selected';?>>Hour</option><option value="day"<?php if ($topt == 'day') echo ' selected';?>>Day</option></select>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">&nbsp;</td>
		<td>
			<input type="radio" name="calc_opt" id="calc_add" value="A"<?php if ($calc_opt == 'A') echo ' checked="checked"';?> /> <label for="calc_add">Add</label> &nbsp; &nbsp;
			<input type="radio" name="calc_opt" id="calc_less" value="L"<?php if ($calc_opt == 'L') echo ' checked="checked"';?> /> <label for="calc_less">Less</label>
		</td>
	</tr>

	<tr class="form_row">
		<td colspan="2" align="center" style="padding:20px;">
			<input class="form_submit_button" type="submit" value=" Calculate " name="submitcalc" style="padding:3px 8px;" /> &nbsp; 
			<input class="form_submit_button" type="button" value=" Cancel " name="submitcancel" onClick="parent.$.colorbox.close();" style="font-size:14px;" /> 
		</td>
	</tr>
<?php if (!empty($new_end_time)):?>
	<tr class="form_row_alt">
		<td class="form_column_caption">New end time</td>
		<td>
			<?php echo date("Y-m-d H:i:s", $new_end_time);?>
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" align="center" style="padding:20px;">
			<input class="form_submit_button" type="submit" value=" Save " onclick="" name="submitsave" /> 
			<input class="form_submit_button" type="submit" value=" Reset " name="submitreset" /> 
			<input class="form_submit_button" type="button" value=" Cancel " name="submitcancel" onClick="parent.$.colorbox.close();" /> 
		</td>
	</tr>
<?php endif;?>
</tbody>
</table>
</form>
