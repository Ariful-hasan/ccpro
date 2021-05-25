<link rel="stylesheet" href="css/report.css" type="text/css">

<?php if (is_array($logs)):?>
<table class="report_extra_info">
<tr>
	<td>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
	</td>
</tr>
</table>
<?php endif;?>
<table class="report_table" width="60%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr">SL</td>
	<td class="cntr">Date time</td>
	<td class="cntr">Device ID</td>
	<td>Status</td>
</tr>

<?php if (is_array($logs)):?>

<?php
	$i = $pagination->getOffset();
	foreach ($logs as $log):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td class="cntr">&nbsp;<?php echo date("Y-m-d H:i:s", $log->tstamp);?></td>
	<td class="cntr">&nbsp;<?php echo $log->dev_id;?></td>
	<td align="left">&nbsp;<?php echo $log->log;?></td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
	<td><?php echo $pagination->createLinks();?></td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="4">No Record Found!</td>
</tr>
<?php endif;?>
</table>
