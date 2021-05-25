<?php

if (isset($_REQUEST['download'])) {
	require_once('lib/DownloadHelper.php');
	$dl_helper = new DownloadHelper($pageTitle, $this);
	$dl_helper->create_file('report_agent_time.csv');
	if (!empty($country_prefix)) $dl_helper->write_in_file("Login ID,");
	$dl_helper->write_in_file("Login ID,Date,Agent Name,Time,Interval,Staffed time,AUX time,Available time");
	if (is_array($aux_messages)) {
		foreach ($aux_messages as $aux) {
			$dl_helper->write_in_file(",$aux->message");
		}
	}
	$dl_helper->write_in_file("\n");

	$logs = $report_model->getAgentTime($dateinfo);
	if (is_array($logs)) {

		foreach ($logs as $log) {

			if (!empty($country_prefix)) $dl_helper->write_in_file($country_prefix . $log->agent_id . ',');
			$cdate = date("d/m/Y", $log->tstamp);
			$ctime = date("H:i", $log->tstamp);
			$dl_helper->write_in_file("$log->agent_id,$cdate,$log->nick,$ctime,30,$log->staff_time,$log->aux_time,$log->available_time");
				
			if (is_array($aux_messages)) {
				foreach ($aux_messages as $aux) {
					$key = 'aux_' . $aux->aux_code;
					$ax_value = isset($log->$key) ? $log->$key : 0;
					$dl_helper->write_in_file(",$ax_value");
				}
			}

			$dl_helper->write_in_file("\n");
		}
	}
	$dl_helper->download_file();
	exit;
}

?><link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>


<script type="text/javascript">
	data_function="{$data_function}";
	Date.format = 'yyyy-mm-dd';
	$(function()
	{
		$('.date-pick').datePicker({clickInput:true,createButton:false,startDate:'2007-01-01'})
	});
	
	function checkDate()
	{
		if(document.getElementById("sdate").value.length == 0)
		{
			alert('Please provide search date!!');
			document.getElementById("sdate").focus();
			return false
		}
		var datePat = /^(\d{4})(\-)(\d{2})(\-)(\d{2})$/;
		var matchArray = document.getElementById("sdate").value.match(datePat); 

		if (matchArray == null) {
			alert("Invalid date format. Provide format as yyyy-mm-dd !!");
			document.getElementById("sdate").focus();
			return false;
		}
	}
</script>

<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_search" method="post" onsubmit="return checkDate()" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
<table class="form_table">
	<tr class="form_row">
		<td>
			From:
			<input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" class="date-pick" size="12" maxlength="10">
			<input type="text" name="stime" id="stime" value="<?php echo $dateinfo->stime;?>" size="5" maxlength="5"> &nbsp;&nbsp;
			To:
			<input type="text" name="edate" id="edate" value="<?php echo $dateinfo->edate;?>" class="date-pick" size="12" maxlength="10">
			<input type="text" name="etime" id="etime" value="<?php echo $dateinfo->etime;?>" size="5" maxlength="5"> &nbsp;&nbsp;&nbsp;

			<input class="form_submit_button" type="submit" name="search" value="Search" />
		</td>										
	</tr>
</table>
</form>

<?php $total_column = empty($country_prefix) ? 8 + count($aux_messages) : 9 + count($aux_messages);?>
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
<?php else:?>
<br />
<?php endif;?>
<table class="report_table" width="98%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr">Login ID</td>
<?php if (!empty($country_prefix)):?>
	<td class="cntr">Login ID</td>
<?php endif;?>
	<td class="cntr">Date</td>
	<td class="cntr">Agent Name</td>
	<td class="cntr">Time</td>
	<td class="cntr">Interval</td>
	<td class="cntr">Staffed time</td>
	<td class="cntr">AUX time</td>
	<td class="cntr">Available time</td>
<?php
if (is_array($aux_messages)) {
	foreach ($aux_messages as $aux) {
		echo '<td class="cntr">'.$aux->message.'</td>';
	}
}
?>
</tr>

<?php if (is_array($logs)):?>

<?php
	$i = $pagination->getOffset();
	//var_dump($agents);
	//exit;
	foreach ($logs as $log):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
<?php if (!empty($country_prefix)):?>
	<td class="cntr">&nbsp;<?php echo $country_prefix . $log->agent_id;?></td>
<?php endif;?>
	<td class="cntr">&nbsp;<?php echo $log->agent_id;?></td>
	<td class="cntr"><?php echo date("d/m/Y", $log->tstamp);?></td>
	<td class="cntr"><?php echo $log->nick;?></td>
	<td class="cntr"><?php echo date("H:i", $log->tstamp);?></td>
	<td class="cntr">30</td>
	<td class="cntr">&nbsp;<?php echo $log->staff_time;?></td>
	<td class="cntr">&nbsp;<?php echo $log->aux_time;?></td>
	<td class="cntr">&nbsp;<?php echo $log->available_time;?></td>
<?php
if (is_array($aux_messages)) {
	foreach ($aux_messages as $aux) {
		$key = 'aux_' . $aux->aux_code;
		$ax_value = isset($log->$key) ? $log->$key : 0;
		echo '<td class="cntr">&nbsp;'.$ax_value.'</td>';
	}
}
?>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
	<td>
	<?php
		if (is_array($logs)) {
			$session_id = session_id();
			$download = md5($pageTitle.$session_id);
			$dl_link = "download=$download";
	    	$url = $pagination->base_link . "&$dl_link";
			echo '<img class="bottom" height="11" width="20" border="0" src="image/down_excel.gif"/><a class="btn-link" href="' . $url . '">download current records</a>';
		}
    	echo '&nbsp; &nbsp;' . $pagination->createLinks();
	?>
	</td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="<?php echo $total_column;?>">No Record Found!</td>
</tr>
<?php endif;?>
</table>
