<link rel="stylesheet" href="css/form.css" type="text/css">


<table class="form_table table" align="center" width="100%" cellpadding="3" cellspacing="1">
<tr class="form_row_head">
	<td colspan="2">License Details</td>
</tr>
<tr class="form_row_alt">
<td class="form_column_caption"><b>Account ID </b></td><td><span id="lsrv_ip"><?php if (isset($settings->account_id)) echo $settings->account_id;?></span></td>
</tr>
<tr class="form_row">
<td class="form_column_caption"><b>Domain </b></td><td><span id="lsrv_id"><?php if (isset($settings->domain)) echo $settings->domain;?></span></td>
</tr>
<tr class="form_row_alt">
<td class="form_column_caption"><b>Server IP </b></td><td><span id="lsrv_ip"><?php if (isset($settings->switch_ip)) echo $settings->switch_ip . ':' . $settings->switch_port;?></span></td>
</tr>
<?php if (isset($settings->switch_ip_dr) && !empty($settings->switch_ip_dr)):?>
<tr class="form_row_alt">
<td class="form_column_caption"><b>DR Server IP </b></td><td><?php echo $settings->switch_ip_dr . ':' . $settings->switch_port;?></td>
</tr>
<?php endif;?>
<tr class="form_row">
<td class="form_column_caption"><b>Dialer License Key </b></td><td ><span id="lnof_cti"><?php if (isset($settings->dialer_key)) echo $settings->dialer_key;?></span></td>
</tr>
<tr class="form_row_alt">
<td class="form_column_caption"><b>No. of IVR </b></td><td ><span id="lnof_ivr"><?php if (isset($settings->ivr_count)) echo $settings->ivr_count;?></span></td>
</tr>
<tr class="form_row">
<td class="form_column_caption"><b>Concurrent IVR Session </b></td><td ><span id="lnof_cti"><?php if (isset($settings->ivr_session_count)) echo $settings->ivr_session_count;?></span></td>
</tr>
<tr class="form_row_alt">
<td class="form_column_caption"><b>Voice Logger Count </b></td><td ><span id="lnof_ivr"><?php if (isset($settings->voice_logger_count) && $settings->voice_logger_count > 0)  echo $settings->voice_logger_count; else echo 'N/A';?></span></td>
</tr>
<tr class="form_row">
<td class="form_column_caption"><b>No. of Skill </b></td><td ><span id="lnof_service"><?php if (isset($settings->skill_count)) echo $settings->skill_count;?></span></td>
</tr>
<tr class="form_row_alt">
	<td class="form_column_caption"><b>No. of Seat </b></td>
	<td><span id="lnof_seat"><?php if (isset($settings->seat_count)) echo $settings->seat_count;?></span></td>
</tr>
<tr class="report_row">
<td class="form_column_caption"><b>No. of Agent </b></td><td ><span id="lnof_agent"><?php if (isset($settings->agent_count)) echo $settings->agent_count;?></span></td>
</tr>
<tr class="form_row_alt">
	<td class="form_column_caption"><b>No. of Supervisor </b></td>
	<td><span id="lnof_supervisor"><?php if (isset($settings->supervisor_count)) echo $settings->supervisor_count;?></span></td>
</tr>
<tr class="form_row_alt">
        <td class="form_column_caption"><b>License Expiration Date </b></td>
        <td><span id="lnof_supervisor"><?php if (isset($settings->license_expire)) echo $settings->license_expire;?></span></td>
</tr>
<tr class="form_row">
<td colspan="2" style="padding-left:100px; height:60px;"><a class="btn btn-success disabled" href="<?php echo '#';?>"><img src="image/icon/cog.png" class="bottom" border="0" width="16" height="16" /> Upgrade License</a></td>
</tr>
</table>
