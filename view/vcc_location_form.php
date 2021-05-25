<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="lid" value="<?php if (isset($loc_id)) echo $loc_id;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Location Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Location ID</td>
		<td>
			<select id="vcc_id" name="vcc_id">
			        <?php for($i='A'; $i<='Z'; $i++) {?>
				<option value="<?php echo $i;?>"<?php if ($vcc->vcc_id == $i) echo ' selected="selected"';?>><?php echo $i;?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Name:</td>
		<td>
			<input type="text" name="name" size="30" maxlength="25" value="<?php echo $vcc->name;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption"><span class="required">*</span> Call Ratio (%):</td>
		<td>
			<input type="text" name="call_ratio" size="30" maxlength="3" value="<?php echo $vcc->call_ratio;?>" />
		</td>
	</tr>
	
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($loc_id)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
