<link href="css/form.css" rel="stylesheet" type="text/css">
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Retry Interval Details</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" style="width:48%;">Campaign ID:</td>
		<td>
			<?php echo $campaign->campaign_id;?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Title: </td>
		<td>
			<?php echo $campaign->title;?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Retry count: </td>
		<td>
			<?php echo $campaign->retry_count;?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Retry interval drop: </td>
		<td>
			<?php echo $campaign->retry_interval_drop;?> sec
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Retry interval busy: </td>
		<td>
			<?php echo $campaign->retry_interval_busy;?> sec
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Retry interval noanswer: </td>
		<td>
			<?php echo $campaign->retry_interval_noanswer;?> sec
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Retry interval unreachable: </td>
		<td>
			<?php echo $campaign->retry_interval_unreachable;?> sec
		</td>
	</tr>
</tbody>
</table>
