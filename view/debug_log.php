<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>


<script type="text/javascript">

Date.format = 'yyyy-mm-dd';
$(function()
{
	$('.date-pick').datePicker({clickInput:true,createButton:false,startDate:'2013-01-01'})
	$('#sdate').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				$('#edate').dpSetStartDate(d.addDays(0).asString());
			}
		}
	);
	$('#edate').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				$('#sdate').dpSetEndDate(d.addDays(-1).asString());
			}
		}
	);
});

</script>
<style type="text/css">
*{
	font-family:Arial, Helvetica, sans-serif;
}

.report-table td{
	white-space:nowrap;
}
</style>

<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
<table class="form_table">
	<tr class="form_row">
		<td>
			From:
			<input type="text" name='sdate' id='sdate' value="<?php echo $dateinfo->sdate;?>" class="date-pick" size='15' maxlength="10"> &nbsp;&nbsp;
			To:
			<input type="text" name='edate' id='edate' value="<?php echo $dateinfo->edate;?>" class="date-pick" size='15' maxlength="10"> &nbsp;&nbsp;&nbsp;
			<input class="form_submit_button" type=submit name=search value="Search">
		</td>										
	</tr>
</table>
</form>


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
<table class="report_table" style="table-layout:fixed;" width="98%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr" width="25">SL</td>
	<td class="cntr" width="110">Date Time</td>
	<td width="80">DID</td>
	<td width="80">CLI</td>
	<td>Message</td>
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
	<td class="cntr"><?php echo $log->tstamp;?></td>
	<td align="left">&nbsp;<?php echo $log->did;?></td>
	<td align="left">&nbsp;<?php echo $log->cli;?></td>
	<td class="td-fixed">&nbsp;<?php echo $log->msg;?></td>
</tr>
<?php endforeach;?>
	<?php /*
<tr>
	<td colspan="8" align="left" class="report_extra_info">

		if (is_array($logs)) {
			$session_id = session_id();
			$download = md5($pageTitle.$session_id);
			$dl_link = "download=$download";
	    	$url = $pagination->base_link . "&$dl_link";
			echo '<img class="bottom" height="11" width="20" border="0" src="image/down_excel.gif"/><a href="' . $url . '" style="color:white;">download current records</a>';
		}
		echo '&nbsp; &nbsp;' . $pagination->createLinks();
	</td>
</tr> */
	?>
</table>
<table class="report_extra_info">
    <tr><td><?php	echo $pagination->createLinks();
	?></td></tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="5">No Record Found!</td>
</tr>
<?php endif;?>
</table>
