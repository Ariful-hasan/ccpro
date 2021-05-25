<link rel="stylesheet" href="css/form.css" type="text/css">

<!-- <form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>"> -->

<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
	<tbody>
	<tr class="form_row_head">
		<td colspan="2">Template Information</td>
	</tr>

	<tr class="form_row">
		<td class="form_column_caption">Select SMS Template:</td>
		<td>
			<select id="title" name="title">
				<option value="">Select</option>
				<?php
				if (!empty($templates)){
					foreach ($templates as $smstmp){
						echo '<option value="'.$smstmp->tstamp.'" smstxt="'.$smstmp->sms_body.'">'.$smstmp->title.'</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>

	<tr class="form_row">
		<td class="form_column_caption" width="40%" valign="top"><span class="required">*</span> Text:</td>
		<td>
			<textarea name="sms_body" id="sms_body" style="width:530px;height:180px;" readonly="readonly"></textarea>
		</td>
	</tr>

	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  Send SMS  " name="submitsms" id="submitsms"> <br><br>
		</td>
	</tr>
	</tbody>
</table>
<!--</form> -->

<script type="text/javascript">
	$(function() {
		$('#submitsms').click(function(){
			var smsText = $('#title option:selected').attr('smstxt');
			if(typeof smsText != 'undefined' && smsText != "" && smsText != null) {
				$('#grid_form_data', window.parent.document).submit();
			}else {
				alert("Please Select SMS Template");
			}
		});

		$('#title').change(function(){
			var selected = $(this).val();
			var smsText = $('option:selected', this).attr('smstxt');
			if(typeof smsText != 'undefined' && smsText != "" && smsText != null){
				$('#ssms_template', window.parent.document).val(selected);
				$('#sms_body').text(smsText);
				$('#last_sms_text', window.parent.document).val(smsText);
			}
		});
	});

	$(document).ready(function(){
		$('#ssms_template', window.parent.document).val('');
		$('#sms_body').text('');
		$('#last_sms_text', window.parent.document).val('');
	});
</script>