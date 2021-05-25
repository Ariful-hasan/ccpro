<?php
	$is_update = false;
	if (!empty($cname)) {
		$is_update = true;
	}
?>
<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_api" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="cid" value="<?php if (isset($cname)) echo $cname;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>IVR API Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><?php if (!$is_update):?><span class="required">*</span> <?php endif;?>Connection Name:</td>
		<td>
			<?php if ($is_update):?><b><?php echo $connection->conn_name;?></b>
			<?php else:?><input type="text" name="conn_name" size="30" maxlength="8" value="<?php echo $connection->conn_name;?>" /><?php endif;?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Connection Method:</td>
		<td>
			<?php $this->html_options('conn_method', 'conn_method', $conn_method_options, $connection->conn_method);?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">URL</td>
		<td>
			<input type="text" name="url" size="30" maxlength="255" value="<?php echo $connection->url;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Credential</td>
		<td>
			<input type="text" name="credential" size="30" maxlength="255" value="<?php echo $connection->credential;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Pass Credential</td>
		<td>
			<?php $this->html_options('pass_credential', 'pass_credential', $yn_options, $connection->pass_credential);?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Submit Method</td>
		<td>
			<?php $this->html_options('submit_method', 'submit_method', $submit_methods, $connection->submit_method);?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Submit Param</td>
		<td>
			<input type="text" name="submit_param" size="30" maxlength="8" value="<?php echo $connection->submit_param;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Return Method</td>
		<td>
			<?php $this->html_options('return_method', 'return_method', $submit_methods, $connection->return_method);?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Return Param</td>
		<td>
			<input type="text" name="return_param" size="30" maxlength="8" value="<?php echo $connection->return_param;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Status</td>
		<td>
			<?php $this->html_options('active', 'active', $yn2_options, $connection->active);?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if ($is_update):?>Update<?php else:?>Add<?php endif;?>  " name="submitapi"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
