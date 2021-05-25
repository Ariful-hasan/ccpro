<link rel="stylesheet" href="css/form.css" type="text/css">
<style type="text/css">
.form_table input[type="text"] {
    width: auto;
}
</style>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="rid" value="<?php if (isset($record_id)) echo $record_id;?>" />
<input type="hidden" name="account_id_old" value="<?php echo $accountId;?>" />
<input type="hidden" name="zip_old" value="<?php echo $zip_old;?>" />
<input type="hidden" name="fax_old" value="<?php echo $fax_old;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan="4">CRM Record Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" align="right"><span class="required">*</span> Account ID: &nbsp;</td>
		<td width="30%">
			<input type="text" name="account_id" size="30" maxlength="20" value="<?php echo $crmData->account_id;?>" />
     	</td>
     	<td class="form_column_caption" align="right"> Title: &nbsp;</td>
		<td width="30%">
			<input type="text" name="title" size="30" value="<?php echo $crmData->title;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" align="right"> First Name: &nbsp;</td>
		<td>
			<input type="text" name="first_name" size="30" value="<?php echo $crmData->first_name;?>" />
     	</td>
     	<td class="form_column_caption" align="right"> Middle Name: &nbsp;</td>
		<td width="30%">
			<input type="text" name="middle_name" size="30" value="<?php echo $crmData->middle_name;?>" />
     	</td>
	</tr>
    <tr class="form_row_alt">
		<td class="form_column_caption" align="right"> Last Name: &nbsp;</td>
		<td>
			<input type="text" name="last_name" size="30" value="<?php echo $crmData->last_name;?>" />
     	</td>
     	<td class="form_column_caption" align="right"> Date of Birth: &nbsp;</td>
		<td width="30%">
			<input type="text" name="DOB" size="30" maxlength="10" class="gs-date-picker" value="<?php echo $crmData->DOB;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" align="right"> House No: &nbsp;</td>
		<td>
			<input type="text" name="house_no" size="30" value="<?php echo $crmData->house_no;?>" />
     	</td>
     	<td class="form_column_caption" align="right"> Street: &nbsp;</td>
		<td width="30%">
			<input type="text" name="street" size="30" value="<?php echo $crmData->street;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" align="right"> Landmarks: &nbsp;</td>
		<td>
			<input type="text" name="landmarks" size="30" value="<?php echo $crmData->landmarks;?>" />
     	</td>
     	<td class="form_column_caption" align="right"> City: &nbsp;</td>
		<td width="30%">
			<input type="text" name="city" size="30" value="<?php echo $crmData->city;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" align="right"> State: &nbsp;</td>
		<td>
			<input type="text" name="state" size="30" value="<?php echo $crmData->state;?>" />
     	</td>
     	<td class="form_column_caption" align="right"> Zip Code: &nbsp;</td>
		<td width="30%">
			<input type="text" name="zip" size="30" value="<?php echo $crmData->zip;?>" />
     	</td>
	</tr>	
	<tr class="form_row_alt">
		<td class="form_column_caption" align="right"> Country: &nbsp;</td>
		<td>
			<input type="text" name="country" size="30" value="<?php echo $crmData->country;?>" />
     	</td>
     	<td class="form_column_caption" align="right"> Email: &nbsp;</td>
		<td width="30%">
			<input type="text" name="email" size="30" value="<?php echo $crmData->email;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" align="right"> Mobile Phone: &nbsp;</td>
		<td>
			<input type="text" name="mobile_phone" size="30" value="<?php echo $crmData->mobile_phone;?>" />
     	</td>
     	<td class="form_column_caption" align="right"> Office Phone: &nbsp;</td>
		<td width="30%">
			<input type="text" name="office_phone" size="30" value="<?php echo $crmData->office_phone;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" align="right"> Home Phone: &nbsp;</td>
		<td>
			<input type="text" name="home_phone" size="30" value="<?php echo $crmData->home_phone;?>" />
     	</td>
     	<td class="form_column_caption" align="right"> Other Phone: &nbsp;</td>
		<td width="30%">
			<input type="text" name="other_phone" size="30" value="<?php echo $crmData->other_phone;?>" />
     	</td>
	</tr>
	
	<tr class="form_row">
	    <td class="form_column_caption" align="right"> Fax: &nbsp;</td>
		<td width="30%">
			<input type="text" name="fax" size="30" value="<?php echo $crmData->fax;?>" />
     	</td>
		<td class="form_column_caption" align="right"> Status: &nbsp;</td>
		<td>
			<select id="status" name="status" style="width:230px;">
				<option value="I"<?php if ($crmData->status != 'A') echo ' selected="selected"';?>>Disable</option>
				<option value="A"<?php if ($crmData->status == 'A') echo ' selected="selected"';?>>Enable</option>
			</select>
		</td>		
	</tr>
	
	<tr class="form_row">
	    <td class="form_column_caption" align="right"> Priority Label: &nbsp;</td>
		<td width="30%">
			<input type="text" name="priority_label" size="30" value="<?php echo $crmData->priority_label;?>" maxlength="10" />
     	</td>
		<td class="form_column_caption" align="right">&nbsp;</td>
		<td>&nbsp;</td>		
	</tr>

	<tr class="form_row_alt">
		<td colspan="4" align="center">
			<br><input class="form_submit_button" type="submit" value="  <?php if (!empty($record_id)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>