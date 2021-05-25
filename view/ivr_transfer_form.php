<link href="css/form.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="ccd/select2/select2.min.css">
<script src="ccd/select2/select2.min.js" type="text/javascript"></script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_skill" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="tid" value="<?php if (isset($transferid)) echo $transferid;?>" />

<table class="form_table table" border="0" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Skill Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="40%">Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="20" value="<?php echo $tIvrData->title;?>" required />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><b>Skill:</b></td>
		<td>
			<select id="skill_id" class="select2" name='skill_id[]' multiple >
			<?php 
			if (!empty($skill_options)){
				foreach ($skill_options as $skid => $skTxt){
					$isSelected = "";
					if (in_array($skid, $tIvrData->skill_id)) {
						$isSelected = "selected='selected'";
					}
			?>
					<option value="<?php echo $skid; ?>" <?php echo $isSelected;?>><?php echo $skTxt; ?></option>
			<?php 
				}
			}
			?>
            </select>
     	</td>
	</tr>
	<tr class="form_row_alt">
	        <td class="form_column_caption">IVR Branch:</td>
	        <td>
	                <input type="text" name="ivr_branch" size="30" maxlength="20" value="<?php echo $tIvrData->ivr_branch;?>" required />
	        </td>
	</tr>
	
	<tr class="form_row">
		<td class="form_column_caption"><b>Verified Call Only:</b></td>
		<td>
			<select name="verified_call_only">
				<option value="">Select</option>
				<option <?php echo $tIvrData->verified_call_only=="Y" ? ' selected="selected"':"";?> value="Y">Yes</option>
				<option <?php echo $tIvrData->verified_call_only=="N" ? ' selected="selected"':"";?> value="N">No</option>
			</select>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><b>Status:</b></td>
		<td>
			<select name="active">
				<option <?php echo $tIvrData->active=="Y" ? ' selected="selected"':"";?> value="Y">Active</option>
				<option <?php echo $tIvrData->active=="N" ? ' selected="selected"':"";?> value="N">Inactive</option>
			</select>
		</td>
	</tr>

	<tr id="tr_submit">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  Submit  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>

    <script type="text/javascript">

        $(document).ready(function () {
            $('.select2').select2();
        });

    </script>