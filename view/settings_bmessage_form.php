<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form action="<?php echo $this->url('task='.$request->getControllerName()."&act=update");?>" method="post">
<input type="hidden" name="bid" value="<?php if (isset($bid)) echo $bid;?>" />

<table class="form_table" border="0" width="450" align="center" cellpadding="6" cellspacing="0">
	<tr class="form_row_head">
		<td colspan=2>Message Information</td>
	</tr>
  	<tr class="form_row">
		<td class="form_column_caption" width="40%">Message : </td>
		<td>
			<input type="text" name="message" value="<?php echo $busy_message->message;?>" maxlength="20">
		</td>
	</tr>
  	<tr class="form_row_alt">
		<td class="form_column_caption" width="40%">AUX Type : </td>
		<td>
			<select name="aux_type">
				<option value="O"<?php if ($busy_message->aux_type == 'O') echo ' selected';?>>AUX-OUT</option>
				<option value="I"<?php if ($busy_message->aux_type == 'I') echo ' selected';?>>AUX-IN</option>
				<option value="B"<?php if ($busy_message->aux_type == 'B') echo ' selected';?>>Break</option>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Active : </td>
		<td>
			<select name="active" id="active">
				<option value="Y"<?php if ($busy_message->active == 'Y') echo ' selected';?>>Yes</option>
				<option value="N"<?php if ($busy_message->active == 'N') echo ' selected';?>>No</option>
			</select>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
        	<input class="form_submit_button" type="submit" name="editmessage" value="Update">
		</td>
	</tr>
</table>

</form>
