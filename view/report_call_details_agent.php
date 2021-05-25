<link href="css/report.css" rel="stylesheet" type="text/css">
<table class="report_table" width="800" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr">SL</td>
	<td class="cntr">Time</td>
	<td class="cntr">Agent ID</td>
	<td>Nick<br />name</td>
	<td class="cntr">Ring time<br />(h:m:s)</td>
	<td class="cntr">Service time<br />(h:m:s)</td>
	<td class="cntr">Hold time<br />(h:m:s)</td>
	<td class="cntr">Hold count</td>
	<td class="cntr">ACW time<br />(h:m:s)</td>
	<td class="cntr">Status</td>
</tr>
<?php if (is_array($calls)):?>
<?php
	$i = 0;
	foreach ($calls as $call) {
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
		$time = empty($call->tstamp) ? '-' : date("Y-m-d H:i:s", $call->tstamp);
		
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td class="cntr"><?php echo $time;?></td>
    <td class="cntr">&nbsp;<?php echo $call->agent_id;?></td>
    <td align="left">&nbsp;<?php echo $call->nick;?></td>
	<td class="cntr"><?php echo DateHelper::get_formatted_time($call->ring_time);?></td>
	<td class="cntr"><?php echo DateHelper::get_formatted_time($call->service_time);?></td>
	<td class="cntr"><?php echo DateHelper::get_formatted_time($call->hold_time);?></td>
    <td class="cntr">&nbsp;<?php echo $call->hold_count;?></td>
	<td class="cntr"><?php echo DateHelper::get_formatted_time($call->acw_time);?></td>
	<td class="cntr"><?php if ($call->is_answer == 'Y') echo 'Answered'; else echo 'Alarm';?></td>
</tr>

<?php
	}
?>
<?php else:?>
<tr class="report_row_empty"><td colspan="10">
<?php if (!empty($errMsg)):?><?php echo $errMsg;?><?php else:?>No Record Found!<?php endif;?>
</td></tr>
<?php endif;?>
</table>