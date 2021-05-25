<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<link href="css/form.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>

<style type="text/css">
label.error {
    float: right;
    font-size: 12px;
    margin-top: 4px;
    width: 56%;
}
.form_table td.form_column_caption {
    padding-top: 14px;
    vertical-align: top;
}
</style>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_address" id="frm_address" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="aid" value="<?php if (isset($aid)) echo $aid;?>" />

<table class="form_table">
<tbody>
	<tr class="form_row_head">
	  <td colspan=3>Address Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Branch Name:</td>
		<td>
			<input type="text" name="branch_name" size="30" maxlength="150" value="<?php echo $address->branch_name;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Address:</td>
		<td>
			<textarea name="address" rows="10" cols="27"><?php echo $address->address;?></textarea>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Latitude:</td>
		<td>
			<input type="text" name="latitude" size="30" maxlength="9" value="<?php echo $address->latitude;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Longitude:</td>
		<td>
			<input type="text" name="longitude" size="30" maxlength="9" value="<?php echo $address->longitude;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($aid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitaddress"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>

