<link href="css/form.css" rel="stylesheet" type="text/css">
<link href="css/report.css" rel="stylesheet" type="text/css">


<style type="text/css">
.ld_title, .ld_prio, .ld_rhour, .ld_st, .ld_weight, .ld_del {
 display:inline-block;
 float:left;
}
.ld_title {
	width: 22%;
}
.ld_prio, .ld_weight {
	width: 10%;
}
.ld_rhour {
	width: 45%;
}
.ld_st {
	width: 10%;
}
.label-right {
	width: 80px !important;
}

.report_table tr.report_row_head td {
	background-image: -webkit-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
	background-image: -moz-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
	background-image: -o-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fff), color-stop(0.25, #fff), to(#e6e6e6)) !important;
	background-image: linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
	color: #333333;
	box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
	-webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
	-moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
	border-bottom: 1px solid #ccc !important;
	border-top: 0 none;
	padding: 0.6em 0.5em;
	vertical-align: middle;
	text-align: center;
}
.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
	padding: 5px;
}
</style>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_campaign" enctype="multipart/form-data" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'));?>">
<input type="hidden" name="skill_id" value="<?php if (!empty($skill_id)) echo $skill_id;?>" />

<input type="hidden" name="dial_alt_number" value="N" />
<input type="hidden" name="drop_call_action" value="HU" />
<input type="hidden" name="vm_action" value="LM" />
<input type="hidden" name="fax_action" value="SF" />
<input type="hidden" name="max_out_bound_calls" value="10" />
<input type="hidden" name="max_drop_rate" value="10" />
<input type="hidden" name="max_call_per_agent" value="3" />
<input type="hidden" name="retry_count" value="1" />
<input type="hidden" name="timezone" value="America/Chicago" />
<input type="hidden" name="status" value="N" />
<input type="hidden" name="dial_engine" value="PD" />

<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Predictive Dial Information </td>
	</tr>

<?php /*
	<tr class="form_row">
		<td class="form_column_caption">Dial Engine: </td>
		<td>
			<select name="dial_engine" id="dial_engine" onchange="adjust_display();">
				<option value="">Select</option>
				<?php
					if (is_array($dial_engine_options)) {
						foreach ($dial_engine_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $predictive_model->dial_engine) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
		</td>
	</tr>

    <tr class="form_row">
		<td class="form_column_caption">Dial Alternative Number: </td>
		<td>
			<select name="dial_alt_number" id="dial_alt_number " onchange="adjust_display();">
				<option value="">Select</option>
				<?php
					if (is_array($dial_alt_number_options )) {
						foreach ($dial_alt_number_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $predictive_model->dial_alt_number ) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
		</td>
	</tr>


    <tr class="form_row">
		<td class="form_column_caption">Drop Call Action: </td>
		<td>
			<select name="drop_call_action" id="drop_call_action" >
				<option value="">Select</option>
				<?php
					if (is_array($drop_call_action_options )) {
						foreach ($drop_call_action_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $predictive_model->drop_call_action ) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
		</td>
	</tr>

    <tr class="form_row">
		<td class="form_column_caption">VM Action: </td>
		<td>
			<select name="vm_action" id="vm_action" >
				<option value="">Select</option>
				<?php
					if (is_array($vm_action_options )) {
						foreach ($vm_action_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $predictive_model->vm_action ) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
		</td>
	</tr>
*/
?>
	<tr class="form_row" id="param">
		<td class="form_column_caption">IVR Node: </td>
		<td>
			<input type="text" name="param" size="30" maxlength="10" value="<?php echo $predictive_model->param; ?>" />
		</td>
	</tr>
<?php /*
    <tr class="form_row">
		<td class="form_column_caption">Fax Action: </td>
		<td>
			<select name="fax_action" id="fax_action" >
				<option value="">Select</option>
				<?php
					if (is_array($fax_action_options )) {
						foreach ($fax_action_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $predictive_model->fax_action ) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
		</td>
	</tr>

    <tr class="form_row">
		<td class="form_column_caption">Status: </td>
		<td>
			<select name="status" id="status" >
				<option value="">Select</option>
				<?php
					if (is_array($status_options )) {
						foreach ($status_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $predictive_model->status ) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
		</td>
	</tr>
*/
?>
<?php /*
	<tr class="form_row" id="max_out_bound_calls">
		<td class="form_column_caption">MAX Outbound Call: </td>
		<td>
			<input type="text" name="max_out_bound_calls" size="30" maxlength="3" value="<?php echo $predictive_model->max_out_bound_calls; ?>" />
		</td>
	</tr>

    <tr class="form_row" id="max_drop_rate">
		<td class="form_column_caption">MAX Drop Rate: </td>
		<td>
			<input type="text" name="max_drop_rate" size="30" maxlength="5" value="<?php echo $predictive_model->max_drop_rate; ?>" />
		</td>
	</tr>

    <tr class="form_row" id="max_call_per_agent">
		<td class="form_column_caption">MAX Call/Agent: </td>
		<td>
			<input type="text" name="max_call_per_agent" size="30" maxlength="4" value="<?php echo $predictive_model->max_call_per_agent; ?>" />
		</td>
	</tr>
*/
?>
    <tr class="form_row" id="tr_pc_ratio">
		<td class="form_column_caption">MAX pacing ratio: </td>
		<td>
			<input type="text" name="max_pacing_ratio" size="30" maxlength="5" value="<?php echo $predictive_model->max_pacing_ratio;?>" />
		</td>
	</tr>

	<tr class="form_row_head">
	  <td colspan=2>Retry </td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Count: </td>
		<td>
			<input class="label-right" type="text" name="retry_count" size="6" maxlength="1" value="<?php echo $predictive_model->retry_count;?>" />
		</td>
	</tr>

<?php /*?>
	<tr class="form_row_alt">
		<td class="form_column_caption">Interval Drop: </td>
		<td>
			<input class="label-right" type="text" name="retry_interval_drop" size="30" maxlength="5" value="<?php echo $predictive_model->retry_interval_drop;?>" />  (sec)
		</td>
	</tr>
<?php */ ?>
    <tr class="form_row_alt">
		<td class="form_column_caption">Retry Interval: </td>
		<td>
			<input class="label-right" type="text" name="retry_interval_noanswer" size="30" maxlength="5" value="<?php echo $predictive_model->retry_interval_noanswer;?>" />  (sec)
		</td>
	</tr>
    <?php /*?>
    <tr class="form_row_alt">
		<td class="form_column_caption">Interval Unreachable: </td>
		<td>
			<input class="label-right" type="text" name="retry_interval_unreachable" size="30" maxlength="5" value="<?php echo $predictive_model->retry_interval_unreachable;?>" />  (sec)
		</td>
	</tr>

	<tr class="form_row">
		<td class="form_column_caption">Interval VM: </td>
		<td>
			<input class="label-right" type="text" name="retry_interval_vm" size="6" maxlength="5" value="<?php echo $predictive_model->retry_interval_vm;?>" />  (sec)
		</td>
	</tr>
    <?php */ ?>
    <tr class="form_row_head">
        <td colspan=2>Active Hour </td>
    </tr>
<?php /*
    <tr class="form_row">
		<td class="form_column_caption">Time zone: </td>
		<td>
			<select name="timezone" id="timezone">
				<option value="">Select</option>
				<?php
				if (is_array($timezones)) {
					foreach ($timezones as $tz) {
						echo '<option value="'.$tz.'"';
						if ($tz == $predictive_model->timezone) echo ' selected';
						echo '>'.$tz.'</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
*/
?>
    <tr class="form_row_alt">
        <td class="form_column_caption">Active hour: </td>
        <td><div class="col-xs-12 ">
                <?php
                $per_row_col = 7;
                for ($i = 0; $i <= 23; $i++) {
                    $padd = $i < 10 ? '0' : '';
                    if ($i%$per_row_col == 0 && $i != 0) echo '<br>';
                    echo '<div class="col-md-2"><input type="checkbox" name="rhour'.$i . '" value="1"';
                    if ($predictive_model->run_hour[$i] == '1') echo ' checked="true"';
                    echo ' /> ' . $padd . $i . '</div>';
                    //if ($i%$per_row_col == 0) echo '</div>';
                }
                ?>
            </div></td>
    </tr>



	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value=" <?php if (!empty($predictive_model->skill_id)):?>Update<?php else:?>Add<?php endif;?>  " name="submitlead" /> <br>
		</td>
	</tr>
</tbody>
</table>
</form>

