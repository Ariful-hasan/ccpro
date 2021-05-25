<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_email" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="eid" value="<?php if (isset($email)) echo $email;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan="2">Email Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Name:</td>
		<td>
			<input type="text" name="name" size="30" maxlength="30" value="<?php echo $service->name;?>" />
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Email:</td>
		<td>
			<input type="text" name="email" size="30" maxlength="50" value="<?php echo $service->email;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Status</td>
		<td>
			<select id="status" name="status">
				<option value="Y"<?php if ($service->status == 'Y') echo ' selected="selected"';?>>Enable</option>
				<option value="N"<?php if ($service->status == 'N') echo ' selected="selected"';?>>Disable</option>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Skill</td>
		<td>
			<select id="skill_id" name="skill_id">
				<option value="">Global</option>
				<?php
					if (is_array($skills)) {
						foreach ($skills as $_skillid=>$_skillname) {
							echo '<option value="'.$_skillid.'"';
							if ($service->skill_id == $_skillid) echo ' selected="selected"';
							echo '>'.$_skillname.'</option>';
						}
					}
				?>
			</select>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($email)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
