<link rel="stylesheet" href="ccd/select2/select2.min.css">
<script src="ccd/select2/select2.min.js" type="text/javascript"></script>


<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="sid" value="<?php if (isset($dis_code)) echo $dis_code;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>IVR Disposition Code Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Disposition Code:</td>
		<td>
			<input type="text" name="disposition_code" size="30" maxlength="6" value="<?php echo $service->disposition_code;?>" />
			<span><small>Allowed: A-Z & 0-9</small></span>
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Service Title:</td>
		<td>
			<input type="text" name="service_title" size="30" maxlength="40" value="<?php echo $service->service_title;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Type</td>
		<td>
			<select id="service_type" name="service_type">
				<option value="A"<?php if ($service->service_type == 'A') echo ' selected="selected"';?>>Auto</option>
				<option value="M"<?php if ($service->service_type == 'M') echo ' selected="selected"';?>>Manual</option>
			</select>
		</td>
	</tr>
    <tr class="form_row_alt">
        <td class="form_column_caption">Parent</td>
        <td>
            <select id="parent_id" name="parent_id" class="select2">
                <option value="">Select</option>
                <?php if (!empty($disp_list)) {?>
                    <?php foreach ($disp_list as $key => $value) { ?>
                        <option value="<?php echo $key?>" <?php echo $service->parent_id==$key?"selected='selected'":''?> ><?php echo $value?></option>
                    <?php } ?>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr class="form_row_alt">
        <td class="form_column_caption">Report Category</td>
        <td>
            <select id="report_category" name="report_category">
                <option value="">Select</option>
                <?php foreach ($report_category_list as $key => $value) { ?>
                    <option value="<?php echo $key?>" <?php echo $service->report_category==$key?"selected='selected'":"" ?> ><?php echo $value?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr class="form_row_alt">
        <td class="form_column_caption">Report Type</td>
        <td>
            <select id="report_type" name="report_type">
                <option value="">Select</option>
                <?php foreach ($report_type_list as $key => $value) { ?>
                    <option value="<?php echo $key?>" <?php echo $service->report_type==$key?"selected='selected'":"" ?> ><?php echo $value?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($dis_code)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>


<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>