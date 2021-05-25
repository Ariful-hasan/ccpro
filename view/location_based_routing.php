<link rel="stylesheet" href="css/form.css" type="text/css">
<style>
.form_table td.form_column_caption {
        width: 270px;
}
</style>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<p>
To route calls to appropriate location configure the following options:
</p>
<form action="<?php echo $this->url('task='.$request->getControllerName()."&act=init");?>" method="post">
<table class="form_table table" border="0" width="630" align="center" cellpadding="6" cellspacing="0">
	<tr class="form_row_head">
		<td colspan=2>Select options:</td>
	</tr>
  	<tr class="form_row_head">
		<td colspan=2>
                	<input type="checkbox" name="staffing" id="staffing" value="Y" <?php if ($routing->staffing == 'Y') echo ' checked'?>  />
			<label for="staffing">Staffing of the locations</label>
		</td>
	</tr>
	<tr class="form_row_head">
		<td colspan=2>
                	<input type="checkbox" name="service_level" id="service_level" value="Y" <?php if ($routing->service_level == 'Y') echo ' checked'?>  />
			<label for="service_level">Service Level (SL)</label>
		</td>
	</tr>
	<tr class="form_row_alt sl">
		<td class="form_column_caption" width="30%"><label for="sl_threshold">SL Threshold (%): </label></td>
		<td>
		    <input type="text" name="sl_threshold" id="sl_threshold" size="20" maxlength="2" value="<?php echo $routing->sl_threshold;?>" />    
		</td>
	</tr>
	<tr class="form_row_alt sl">
                <td class="form_column_caption"><label for="call_reduce_on_sl">Reduce Call on SL Threshold (%):</label></td>
                <td>
                        <input type="text" name="call_reduce_on_sl" id="call_reduce_on_sl" size="20" maxlength="2" value="<?php echo $routing->call_reduce_on_sl;?>" />
                </td>
        </tr>
	<tr class="form_row_head">
		<td colspan=2>
                	<input type="checkbox" name="aht" id="aht" value="Y" <?php if ($routing->aht == 'Y') echo ' checked'?>  />
			<label for="aht">Average Handling Time (AHT)</label>
		</td>
	</tr>
	<tr class="form_row_alt aht">
		<td class="form_column_caption" width="30%"><label for="aht_threshold">AHT Threshold (sec): </label></td>
		<td>
		    <input type="text" name="aht_threshold" id="aht_threshold" size="20" maxlength="3" value="<?php echo $routing->aht_threshold;?>" />    
		</td>
	</tr>
	<tr class="form_row_alt aht">
                <td class="form_column_caption"><label for="call_reduce_on_aht">Reduce Call on AHT Threshold (%): </label></td>
                <td>
                        <input type="text" name="call_reduce_on_aht" id="call_reduce_on_aht" size="20" maxlength="2" value="<?php echo $routing->call_reduce_on_aht;?>" />
                </td>
        </tr>
	<tr class="form_row_head">
		<td colspan=2>
                	<input type="checkbox" name="ahq" id="ahq" value="Y" <?php if ($routing->ahq == 'Y') echo ' checked'?>  />
			<label for="ahq">Average Hold in Queue (AHQ)</label>
		</td>
	</tr>
	<tr class="form_row_alt ahq">
		<td class="form_column_caption" width="30%"><label for="ahq_threshold">AHQ Threshold (sec): </label></td>
		<td>
		    <input type="text" name="ahq_threshold" id="ahq_threshold" size="20" maxlength="3" value="<?php echo $routing->ahq_threshold;?>" />    
		</td>
	</tr>
	<tr class="form_row_alt ahq">
                <td class="form_column_caption"><label for="call_reduce_on_ahq">Reduce Call on AHQ Threshold (%):</label></td>
                <td>
                        <input type="text" name="call_reduce_on_ahq" id="call_reduce_on_ahq" size="20" maxlength="2" value="<?php echo $routing->call_reduce_on_ahq;?>" />
                </td>
        </tr>
        
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
                	<input class="form_submit_button btn btn-success" type="submit" name="save" value="Save" />
		</td>
	</tr>
</table>
</form>

<script>
function onLoadView()
{
        var isSelected = '';
        if ($('#service_level').is(':checked')) {
                $('.sl').show();
        } else {
                $('.sl').hide();
        }
        if ($('#aht').is(':checked')) {
                $('.aht').show();
        } else {
                $('.aht').hide();
        }
        if ($('#ahq').is(':checked')) {
                $('.ahq').show();
        } else {
                $('.ahq').hide();
        }
}
$(document).ready(function() {
        onLoadView();
        $('#service_level').change(function() {
                onLoadView();
        });
        $('#aht').change(function() {
                onLoadView();
        });
        $('#ahq').change(function() {
                onLoadView();
        });
});
</script>