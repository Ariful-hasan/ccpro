<link rel="stylesheet" href="css/form.css" type="text/css">

<?php if (!empty($errMsg)):?>
	<br />
	<?php if ($errType === 0):?>
		<div class="alert alert-success">
	<?php else: ?>
		<div class="alert alert-error">
	<?php endif;?>	
	<?php echo $errMsg;?>
	</div>
<?php endif; ?>

<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
	<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
		<tbody>
			<tr class="form_row_head">
			  <td colspan="4">Daily Settings Information</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Avg. Call Duration(s)</td>
				<td>
					<input type="text" name="avg_call_duration" value="<?php echo (isset($settings_data['avg_call_duration'])) ? $settings_data['avg_call_duration'] : ''; ?>">
				</td>
				<td class="form_column_caption">Total Interval Length(s)</td>
				<td>
					<input type="text" name="total_interval_length" value="<?php echo (isset($settings_data['total_interval_length'])) ? $settings_data['total_interval_length'] : ''; ?>">
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Agent Occupancy(%)</td>
				<td>
					<input type="text" name="agent_occupancy" value="<?php echo (isset($settings_data['agent_occupancy'])) ? $settings_data['agent_occupancy'] : ''; ?>">
				</td>
				<td class="form_column_caption">Forecast Trend</td>
				<td>
					<select id="forecast_trend" name="forecast_trend" class="form-control" >
                        <option value="">---Select---</option>
                        <?php foreach ($trend_list as $key => $item) { ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($settings_data['forecast_trend']) && $settings_data['forecast_trend'] == $key) ? 'selected="selected"' : ''; ?> ><?php echo $item; ?></option>
                        <?php } ?>
                    </select>
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Forecast upper scale (%)</td>
				<td>
					<input type="text" name="forecast_upper_scale" value="<?php echo (isset($settings_data['forecast_upper_scale'])) ? $settings_data['forecast_upper_scale'] : ''; ?>">
				</td>
				<td class="form_column_caption">Forecast lower scale (%)</td>
				<td>
					<input type="text" name="forecast_lower_scale" value="<?php echo (isset($settings_data['forecast_lower_scale'])) ? $settings_data['forecast_lower_scale'] : ''; ?>">
				</td>
			</tr>
		</tbody>
	</table>
	<br/>
	<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
		<tbody>
			<tr class="form_row_head">
			  <td colspan="4">Hourly Settings Information</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Avg. Call Duration(s)</td>
				<td>
					<input type="text" name="avg_call_duration_hourly" value="<?php echo (isset($settings_data['avg_call_duration_hourly'])) ? $settings_data['avg_call_duration_hourly'] : ''; ?>">
				</td>
				<td class="form_column_caption">Total Interval Length(s)</td>
				<td>
					<input type="text" name="total_interval_length_hourly" value="<?php echo (isset($settings_data['total_interval_length_hourly'])) ? $settings_data['total_interval_length_hourly'] : ''; ?>">
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Agent Occupancy(%)</td>
				<td>
					<input type="text" name="agent_occupancy_hourly" value="<?php echo (isset($settings_data['agent_occupancy_hourly'])) ? $settings_data['agent_occupancy_hourly'] : ''; ?>">
				</td>
				<td class="form_column_caption">Forecast Trend</td>
				<td>
					<select id="forecast_trend_hourly" name="forecast_trend_hourly" class="form-control" >
                        <option value="">---Select---</option>
                        <?php foreach ($trend_list as $key => $item) { ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($settings_data['forecast_trend_hourly']) && $settings_data['forecast_trend_hourly'] == $key) ? 'selected="selected"' : ''; ?> ><?php echo $item; ?></option>
                        <?php } ?>
                    </select>
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">Forecast upper scale (%)</td>
				<td>
					<input type="text" name="forecast_upper_scale_hourly" value="<?php echo (isset($settings_data['forecast_upper_scale_hourly'])) ? $settings_data['forecast_upper_scale_hourly'] : ''; ?>">
				</td>
				<td class="form_column_caption">Forecast lower scale (%)</td>
				<td>
					<input type="text" name="forecast_lower_scale_hourly" value="<?php echo (isset($settings_data['forecast_lower_scale_hourly'])) ? $settings_data['forecast_lower_scale_hourly'] : ''; ?>">
				</td>
			</tr>			
			<tr class="form_row_alt">
				<td class="form_column_caption">Wait Time(s)</td>
				<td>
					<input type="text" name="wait_time_hourly" value="<?php echo (isset($settings_data['wait_time_hourly'])) ? $settings_data['wait_time_hourly'] : ''; ?>">
				</td>
				<td class="form_column_caption">Service Level (%)</td>
				<td>
					<input type="text" name="service_level_hourly" value="<?php echo (isset($settings_data['service_level_hourly'])) ? $settings_data['service_level_hourly'] : ''; ?>">
				</td>
			</tr>			
			<tr class="form_row_alt">
				<td class="form_column_caption">Shrinkage</td>
				<td>
					<input type="text" name="shrinkage_hourly" value="<?php echo (isset($settings_data['shrinkage_hourly'])) ? $settings_data['shrinkage_hourly'] : ''; ?>">
				</td>
				<td class="form_column_caption">&nbsp;</td>
				<td>
					&nbsp;
				</td>
			</tr>
		</tbody>
	</table>
	<div class="row">
		<div class="col-md-12">
			<br><br>
			<div class="form_column_submit text-center">
				<input class="btn btn-success" type="submit" value="Update" name="submitservice"> <br><br>
			</div>
		</div>
	</div>
</form>

<script type="text/javascript">
$( document ).ready(function() {
    
});
</script>