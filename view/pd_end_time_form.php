<link href="css/form.css" rel="stylesheet" type="text/css">
<?php if (!empty($errMsg)):?><font style="color:brown"><b><?php echo $errMsg;?></b></font><br /><br /><?php endif;?>

<form name="frm_campaign" id="frm_campaign" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="sid" value="<?php echo $sid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>End Time Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="40%">Skill:</td>
		<td>
			<?php echo $skill_name;?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Existing end time:</td>
		<td>
			<?php 
			$end_time = strtotime($pd_profile->stop_time);
			echo empty($end_time) ? '-' : date("Y-m-d H:i:s", $end_time);
			?>
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
