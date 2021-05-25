<link rel="stylesheet" href="css/form.css" type="text/css">


<table class="form_table table" align="center" width="100%" cellpadding="3" cellspacing="1">
<tr class="form_row_head">
	<td colspan="2">License Details</td>
</tr>
<tr class="form_row">
<td class="form_column_caption"><b>Server ID </b></td><td><span id="lsrv_id"><?php if (isset($settings->server_id)) echo $settings->server_id;?></span></td>
</tr>
<tr class="form_row_alt">
<td class="form_column_caption"><b>Server IP </b></td><td><span id="lsrv_ip"><?php if (isset($settings->server_ip)) echo $settings->server_ip;?></span></td>
</tr>
<tr class="form_row">
<td class="form_column_caption"><b>No. of CTI </b></td><td ><span id="lnof_cti"><?php if (isset($settings->num_ctis)) echo $settings->num_ctis;?></span></td>
</tr>
<tr class="form_row_alt">
<td class="form_column_caption"><b>No. of IVR </b></td><td ><span id="lnof_ivr"><?php if (isset($settings->num_ivrs)) echo $settings->num_ivrs;?></span></td>
</tr>
<tr class="form_row">
<td class="form_column_caption"><b>No. of Skill </b></td><td ><span id="lnof_service"><?php if (isset($settings->num_skills)) echo $settings->num_skills;?></span></td>
</tr>
<tr class="form_row_alt">
	<td class="form_column_caption"><b>No. of Seat </b></td>
	<td><span id="lnof_seat"><?php if (isset($settings->num_seats)) echo $settings->num_seats;?></span></td>
</tr>
<tr class="report_row">
<td class="form_column_caption"><b>No. of Agent </b></td><td ><span id="lnof_agent"><?php if (isset($settings->num_agents)) echo $settings->num_agents;?></span></td>
</tr>
<tr class="form_row_alt">
	<td class="form_column_caption"><b>No. of Supervisor </b></td>
	<td><span id="lnof_supervisor"><?php if (isset($settings->num_svis)) echo $settings->num_svis;?></span></td>
</tr>
<tr class="form_row">
<td colspan="2" style="padding-left:100px; height:60px;"><a class="btn btn-success disabled" href="<?php echo '#';?>"><img src="image/icon/cog.png" class="bottom" border="0" width="16" height="16" /> Upgrade License</a></td>
</tr>
</table>
