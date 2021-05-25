<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="did" value="<?php if (isset($dis_code)) echo $dis_code; else $dis_code = '';?>" />
<input type="hidden" name="sid" value="<?php if (isset($sid)) echo $sid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Disposition Code Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Disposition Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="50" value="<?php echo $service->title;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%">Parent Disposition:</td>
		<td>
			<?php if (!empty($dis_code)):?>
				<?php echo $dc_parent_path;?>
			<?php else:?>
            <select name="parent_id" id="parent_id">
				<option value="" title="">Select</option>
				<?php			    
				    $disposArr = array();
				    $disParent = "";
					foreach ($dispositions as $key => $val) {						
						if ($key != $dis_code) {
							$disArray = explode('->', $val);
							$arrIndex = count($disArray) - 1;
							$disposArr[$arrIndex] = $disArray[$arrIndex];
							if (empty($disParent) || count($disArray) == 1) {
								$disParent = $disArray[$arrIndex];
							}else {
								$disParent = "";
								for ($i = 0; $i < count($disArray); $i++) {
									if (empty($disParent) && isset($disposArr[$i])) {
										$disParent .= $disposArr[$i];
									}elseif (isset($disposArr[$i])) {
										$disParent .= " ->" . $disposArr[$i];
									}
								}
							}
							echo '<option value="'.$key.'" title="'.$disParent.'"';
							if ($key == $service->parent_id) echo ' selected';
							echo '>' . $val . '</option>';
						}
					}
				?>
			</select>
			<?php endif; ?>
		</td>
	</tr>

    <tr class="form_row_alt">
        <td class="form_column_caption" width="33%"><span class="required">*</span> Disposition Type:</td>
        <td>
            <select name="disposition_type" id="disposition_type">
                <?php if (!empty($disposition_type)) { ?>
                    <?php foreach ($disposition_type as $key => $value) { ?>
                        <option value="<?php echo $key ?>" <?php echo $service->disposition_type == $key ? "selected" : "" ?> ><?php echo $value ?></option>
                    <?php } ?>
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