<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<link href="css/form.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_agent" id="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<table class="form_table table">
<tbody>
	<tr class="form_row_head">
	  <td colspan=3>Server Settings</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Primary Server IP:</td>
		<td>
			<?php echo $settings->switch_ip;?>
		</td>
	</tr>
	<?php if (!empty($settings->switch_ip_dr)):?>
	<tr class="form_row_alt">
		<td class="form_column_caption">DR Server IP:</td>
		<td>
			<?php echo $settings->switch_ip_dr;?>
		</td>
	</tr>

	<tr class="form_row">
                <td class="form_column_caption">Active Server:</td>
                <td>
                        <select name="active_sip_srv">
                                <option value="P" <?php if($settings->active_sip_srv == 'P') echo 'selected';?>>Primary (PR)</option>
                                <option value="D" <?php if($settings->active_sip_srv == 'D') echo 'selected';?>>Disaster Recovery (DR)</option>
                        </select>
                </td>
        </tr>
	
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button btn btn-success" type="submit" value="  Update  " name="submitagent" /> <br><br>
		</td>
	</tr>
	<?php endif;?>
</tbody>
</table>
</form>

