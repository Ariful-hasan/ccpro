<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="tid" value="<?php if (isset($tstamp_code)) echo $tstamp_code;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Template Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="100" value="<?php echo $service->title;?>" style="width:98%;" />
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%" valign="top"><span class="required">*</span> Text:</td>
		<td>
			<textarea name="message" id="message" style="width:98%;height:100px;" maxlength="255"><?php echo stripslashes($service->message);?></textarea>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"> Skill:</td>
		<td>
			<select name="skill_id" id="skill_id">
				<option value="">Select</option>
				<?php if (is_array($skills)) {
					foreach ($skills as $skill) {
						echo '<option value="' . $skill->skill_id . '"';
						if (isset($service->skill_id) && $service->skill_id == $skill->skill_id) echo ' selected="selected"';
						echo '>' . $skill->skill_name . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Status:</td>
		<td>
			<select id="status" name="status">
				<option value="N"<?php if ($service->status != 'Y') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($service->status == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
		</td>
	</tr>

	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($tstamp_code) && !$iscopy):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
