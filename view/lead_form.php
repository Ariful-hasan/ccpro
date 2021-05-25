<link href="css/form.css" rel="stylesheet" type="text/css">

<script>
function readFile()
{
	var param= document.getElementById('number').value;
	var ext = param.substr(-3);
	if(ext == 'txt' || ext == 'csv'){
		showColumnDef(ext);
	} else {
		alert("Only text or excel file allowed");
		document.getElementById('number').value='';
	}
}
function showColumnDef(val)
{
	document.getElementById('tbl_csv_def').style.display = 'block';
	//if (val=="csv") document.getElementById('tbl_csv_def').style.display = 'block';
	//else document.getElementById('tbl_csv_def').style.display = 'none';
}

</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_trunk" enctype="multipart/form-data" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="lid" value="<?php if (!empty($lid)) echo $lid;?>" />

<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Lead Information </td>
	</tr>
<?php if (!empty($lid)):?>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Lead ID:</td>
		<td>
			<?php echo $lid;?>
		</td>
	</tr>
<?php endif;?>
	<tr class="form_row_alt">
		<td class="form_column_caption">Upload number file: </td>
		<td>
			<input type="file" id="number" name="number" size="30" value="" onChange="readFile()" style="float:left" accept=".csv" >
			<label for="is_overwrite"><input type="checkbox" id="is_overwrite" name="is_overwrite" value="Y" <?php if ($is_overwrite == 'Y') echo 'checked';?> /> Overwrite provious number(s)</label>
		</td>
	</tr>
    
	<tr class="form_row_alt">
		<td colspan="2">
			<table id="tbl_csv_def" width="100%" id="spn" align="center" style="display:none;">
			<tr class="form_row_alt">
				<td colspan="4" align="left"><b>Column Definition:</b><br /><br /></td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Customer ID:</td>
				<td>
					<input type="text" id="customer_id_heading" name="customer_id_heading" size="20" value="<?php echo $heading->customer_id_heading;?>" />
				</td>
				<td class="form_column_caption">&nbsp;</td>
				<td>
					&nbsp;
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Number 1:</td>
				<td>
					<input type="text" id="dial_number_heading" name="dial_number_heading" size="20" value="<?php echo $heading->dial_number_heading;?>" />
				</td>
				<td class="form_column_caption">Number 2:</td>
				<td>
					<input type="text" id="dial_number_2_heading" name="dial_number_2_heading" size="20" value="<?php echo $heading->dial_number_2_heading;?>" />
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Number 3:</td>
				<td>
					<input type="text" id="dial_number_3_heading" name="dial_number_3_heading" size="20" value="<?php echo $heading->dial_number_3_heading;?>" />
				</td>
				<td class="form_column_caption">Number 4:</td>
				<td>
					<input type="text" id="dial_number_4_heading" name="dial_number_4_heading" size="20" value="<?php echo $heading->dial_number_4_heading;?>" />
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Title:</td>
				<td>
					<input type="text" id="title_heading" name="title_heading" size="20" value="<?php echo $heading->title_heading;?>" />
				</td>
				<td class="form_column_caption">Street:</td>
				<td>
					<input type="text" id="street_heading" name="street_heading" size="20" value="<?php echo $heading->street_heading;?>" />
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">FName:</td>
				<td>
					<input type="text" id="first_name_heading" name="first_name_heading" size="20" value="<?php echo $heading->first_name_heading;?>" />
				</td>
				<td class="form_column_caption">City:</td>
				<td colspan="3">
					<input type="text" id="city_heading" name="city_heading" size="20" value="<?php echo $heading->city_heading;?>" />
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">LName:</td>
				<td>
					<input type="text" id="last_name_heading" name="last_name_heading" size="20" value="<?php echo $heading->last_name_heading;?>" />
				</td>
				<td class="form_column_caption">State:</td>
				<td>
					<input type="text" id="state_heading" name="state_heading" size="20" value="<?php echo $heading->state_heading;?>" />
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Agent Alt. ID</td>
				<td>
					<input type="text" id="agent_altid_heading" name="agent_altid_heading" size="20" value="<?php echo $heading->agent_altid_heading;?>" />
				</td>
				<td class="form_column_caption">Zip:</td>
				<td>
					<input type="text" id="zip_heading" name="zip_heading" size="20" value="<?php echo $heading->zip_heading;?>" />
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Email:</td>
				<td>
					<input type="text" id="email_heading" name="email_heading" size="20" value="<?php echo $heading->email_heading;?>" />
				</td>
				<td class="form_column_caption">&nbsp;</td>
				<td>
					&nbsp;
				</td>
			</tr>
			<?php 
			
			$columns = array();
			if (!empty($lead->custom_label_1)) $columns['custom_value_1'] = $lead->custom_label_1;
			if (!empty($lead->custom_label_2)) $columns['custom_value_2'] = $lead->custom_label_2;
			if (!empty($lead->custom_label_3)) $columns['custom_value_3'] = $lead->custom_label_3;
			if (!empty($lead->custom_label_4)) $columns['custom_value_4'] = $lead->custom_label_4;
				
			if (is_array($columns)) {
				$i = 0;
				foreach ($columns as $key=>$val) { 
					if ($i%2 == 0) echo '<tr class="form_row_at">';
					echo '<td class="form_column_caption">'.$val.':</td>';
					echo '<td><input type="text" id="'.$key.'_heading" name="'.$key.'_heading" size="20" value="'. $heading->{$key . '_heading'} . '" /></td>';
					$i++;
					if ($i%2 == 0) echo '</tr>';
				}
				
				if ($i%2 != 0) echo '<td>&nbsp;</td><td>&nbsp;</td></tr>';
			}
			?>
			
			</table>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Lead title: </td>
		<td>
			<input type="text" name="title" size="30" maxlength="30" value="<?php echo $lead->title;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Reference: </td>
		<td>
			<input type="text" name="reference" size="30" maxlength="12" value="<?php echo $lead->reference;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Country code: </td>
		<td>
			<input type="text" name="country_code" size="30" maxlength="2" value="<?php echo $lead->country_code;?>" />
		</td>
	</tr>
	
	<tr class="form_row_alt">
		<td class="form_column_caption"><h6>Custom Labels</h6></td>
		<td>&nbsp;</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Label 1: </td>
		<td>
			<input type="text" name="custom_label_1" size="30" maxlength="20" value="<?php echo $lead->custom_label_1;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Label 2: </td>
		<td>
			<input type="text" name="custom_label_2" size="30" maxlength="20" value="<?php echo $lead->custom_label_2;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Label 3: </td>
		<td>
			<input type="text" name="custom_label_3" size="30" maxlength="20" value="<?php echo $lead->custom_label_3;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Label 4: </td>
		<td>
			<input type="text" name="custom_label_4" size="30" maxlength="20" value="<?php echo $lead->custom_label_4;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value=" <?php if (!empty($lid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitlead" /> <br>
		</td>
	</tr>
</tbody>
</table>
</form>
<?php
$resp = FileManager::check_file_for_upload('number', 'csv');
if ($resp != FILE_UPLOADED) {
	$resp = FileManager::check_file_for_upload('number', 'txt');
}
if ($resp == FILE_UPLOADED) {
?>
<script>showColumnDef('csv');</script>
<?php
}
?>