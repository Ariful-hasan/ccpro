<?php $num_select_box = 6;?>

<script src="js/dpselections.js"></script>
<script>
set_loadurl("<?php echo $this->url("task=email&act=dispositionchildren");?>");
set_num_select_box(<?php echo $num_select_box;?>);

$( document ).ready( function() {
	load_sels(<?php echo count($disposition_ids);?>);
} );

function get_templates_by_disp(val, selector) {
    var current_select_num = selector.substr(selector.length-1)-1;
    if (typeof val!= 'undefined' && val == '' && current_select_num > 0){
        var previous_select_num = current_select_num-1;
        var previous_select_val = $("#disposition_id"+previous_select_num).val();
        $("#dd").val(previous_select_val);
    } else {
        $("#dd").val(val);
    }
}

function reload_skill_emails(val)
{
	if (val.length > 0) {

		$.ajax({
			type: "POST",
			url: "<?php echo $this->url("task=email&act=skillDispositions");?>",
			data: { sid: val },
			dataType: "json"
		})
		.done(function(data) {
			if (typeof data != 'undefined' && data != null && data != null) {
				$selector = $("#disposition_id0");
				$selector.html('<option value="">Select</option>');
				$.each(data, function(key, val){
					$selector.append('<option value="' + key + '">' + val + '</option>');
				});
				var totalBox = "<?php echo $num_select_box; ?>";
				totalBox = parseInt( totalBox );
				for ( var idnum = 1; idnum < totalBox; idnum++) {
					option = $('<option></option>').attr("value", "").text("Select");
					selectId = "#disposition_id" + idnum;
					$(selectId).empty().append(option);
					$(selectId).hide();
				}
			}
		})
		.fail(function() {
		})
		.always(function() {
		});
	}
}
</script>
<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="tid" value="<?php if (isset($tstamp_code)) echo $tstamp_code;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Template Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Title:</td>
		<td>
			<input type="text" name="title" style="width: 100%" maxlength="100" value="<?php echo $service->title;?>" style="width:98%;" />
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%" valign="top"><span class="required">*</span> Text:</td>
		<td>
			<textarea name="mail_body" id="mail_body" style="width:100%;height:200px;"><?php echo !empty($service->mail_body)?nl2br(base64_decode($service->mail_body)):"";?></textarea>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"> Skill:</td>
		<td>
			<select name="skill_id" id="skill_id" onChange="reload_skill_emails(this.value);">
				<option value="">Select</option>
				<?php if (is_array($skills)) {
					foreach ($skills as $skill) {
						echo '<option value="' . $skill->skill_id . '"';
						if (isset($skill_id) && $skill_id == $skill->skill_id) echo ' selected="selected"';
						echo '>' . $skill->skill_name . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Disposition:</td>
		<td>
<?php
			
				for ($i=0; $i<$num_select_box; $i++) {
					echo '<select name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels('.$i.', this.value);"><option value="">Select</option>';
					if ((isset($request->_action_name) && ($request->_action_name == "update" || $request->_action_name == "copy")) && ($i == 0 || isset($disposition_ids[$i]))) {
						if (!isset(${'dispositions'.$i})) {
							if ($i > 0) {
								$dispositions = $email_model->getDispositionChildrenOptions($skill_id, $disposition_ids[$i-1][0]);
//var_dump($dispositions);
							} else {
								$dispositions = $email_model->getDispositionChildrenOptions($skill_id, '');
							}
						} else {
							$dispositions = ${'dispositions'.$i};
						}

						foreach ($dispositions as $_dispositionid=>$_title) {
							$did = isset($disposition_ids[$i]) ? $disposition_ids[$i][0] : '';
							echo '<option value="' . $_dispositionid . '"';
							if ($did == $_dispositionid) echo ' selected';
							echo '>' . $_title . '</option>';
						}
					}
					echo '</select> ';
				}
				
			/*
			?>
			
			<select name="disposition_id" id="disposition_id">
				<option value="">Select</option>
				<?php if (is_array($dispositions)) {
					foreach ($dispositions as $_dispositionid=>$_title) {
						echo '<option value="' . $_dispositionid . '"';
						if ($service->disposition_id == $_dispositionid) echo ' selected';
						echo '>' . $_title . '</option>';
					}
				}
				?>
			</select>
			<?php */ ?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Status:</td>
		<td>
			<select id="status" name="status">
				<option value="N"<?php if ($service->status != 'Y') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($service->status == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
		</td>
	</tr>

	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($tstamp_code) && !$iscopy):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
    <!--<input type="hidden" name="dd" id="dd" value="<?php /*echo $dd*/?>" maxlength="4" />-->
</form>
