<?php

if (isset($_REQUEST['download'])) {
	require_once('lib/DownloadHelper.php');
	$dl_helper = new DownloadHelper($pageTitle, $this);
	$dl_helper->create_file('skill_report_source.csv');
	$dl_helper->write_in_file("Skill ID,Date,Skill   Name,Time,Interval,Calls offered,Calls answered,Calls ans within 10 secs,".
		"Calls ans within 20 secs,Avg speed of ans,Total handling time,ACD time,Hold ACD time,AUX out calls,AUX out time,".
		"ACW time,Total aban calls,Aban calls after 5 secs,Aban time,Flow in,Flow out,Extn out calls,Extn out time,Extn in calls,".
		"Extn in time,Conference,Transferred\n");
	$logs = $report_model->getSkillSourceLog($dateinfo);
	if (is_array($logs)) {
		foreach ($logs as $log) {

			$skillid = ord($log->skill_id) - 64;
			$skillid = sprintf("%02d", $skillid);
			$skillname = isset($skills[$log->skill_id]) ? $skills[$log->skill_id] : $skillid;
			$cdate = date("d/m/Y", $log->tstamp);
			$ctime = date("H:i", $log->tstamp);
			$handling_time = $log->acd_time+$log->acd_hold_time;

			$dl_helper->write_in_file($country_prefix . $skillid . ",$cdate,$skillname,$ctime,30,$log->calls_offered,".
            	"$log->calls_answered,$log->calls_answered_10,$log->calls_answered_20,$log->avg_answer_speed,".
                "$handling_time,$log->acd_time,$log->acd_hold_time,$log->acd_aux_out_calls,$log->acd_aux_out_time,".
                "$log->acw_time,$log->abn_calls,$log->abn_calls_5,$log->abn_time,$log->flow_in_calls,$log->flow_out_calls,".
                "$log->extn_out_calls,$log->extn_out_time,$log->extn_in_calls,$log->extn_in_time,$log->conference,$log->transferred\n");
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

<?php $total_column = 27;?>

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
	<td class="cntr">Skill ID</td>
	<td class="cntr">Date</td>
	<td class="cntr">Skill &nbsp;&nbsp;Name&nbsp;&nbsp;</td>
	<td class="cntr">&nbsp;Time&nbsp;</td>
	<td class="cntr">Inte<br />rval</td>
	<td class="cntr">Calls offered</td>
	<td class="cntr">Calls answered</td>
	<td class="cntr">Calls ans within 10 secs</td>
	<td class="cntr">Calls ans within 20 secs</td>
	<td class="cntr">Avg speed of ans</td>
	<td class="cntr">Total handling time</td>
	<td class="cntr">ACD time</td>
	<td class="cntr">Hold ACD time</td>
	<td class="cntr">AUX out calls</td>
	<td class="cntr">AUX out time</td>
	<td class="cntr">ACW time</td>
	<td class="cntr">Total aban calls</td>
	<td class="cntr">Aban calls after 5 secs</td>
	<td class="cntr">Aban time</td>
	<td class="cntr">Flow in</td>
	<td class="cntr">Flow out</td>
	<td class="cntr">Extn out calls</td>
	<td class="cntr">Extn out time</td>
	<td class="cntr">Extn in calls</td>
	<td class="cntr">Extn in time</td>
	<td class="cntr">Conf<br />erence</td>
	<td class="cntr">Trans<br />ferred</td>
</tr>

<?php if (is_array($logs)):?>

<?php
	$i = $pagination->getOffset();
	//var_dump($skills);
	foreach ($logs as $log):
		$i++;
		$skillid = ord($log->skill_id) - 64;
		$skillid = sprintf("%02d", $skillid);
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr"><?php echo $country_prefix . $skillid;?></td>
	<td class="cntr">&nbsp;<?php echo date("d/m/Y", $log->tstamp);?>&nbsp;</td>
	<td class="cntr"><?php echo isset($skills[$log->skill_id]) ? $skills[$log->skill_id] : $skillid;?></td>
	<td class="cntr"><?php echo date("H:i", $log->tstamp);?></td>
	<td class="cntr">30</td>
	<td class="cntr">&nbsp;<?php echo $log->calls_offered;?></td>
	<td class="cntr">&nbsp;<?php echo $log->calls_answered;?></td>
	<td class="cntr">&nbsp;<?php echo $log->calls_answered_10;?></td>
	<td class="cntr">&nbsp;<?php echo $log->calls_answered_20;?></td>
	<td class="cntr">&nbsp;<?php echo $log->avg_answer_speed;?></td>
	<td class="cntr"><?php echo $log->acd_time+$log->acd_hold_time;?></td>
	<td class="cntr"><?php echo $log->acd_time;?></td>
	<td class="cntr"><?php echo $log->acd_hold_time;?></td>
	<td class="cntr"><?php echo $log->acd_aux_out_calls;?></td>
	<td class="cntr"><?php echo $log->acd_aux_out_time;?></td>
	<td class="cntr">&nbsp;<?php echo $log->acw_time;?></td>
	<td class="cntr">&nbsp;<?php echo $log->abn_calls;?></td>
	<td class="cntr"><?php echo $log->abn_calls_5;?></td>
	<td class="cntr">&nbsp;<?php echo $log->abn_time;?></td>
	<td class="cntr">&nbsp;<?php echo $log->flow_in_calls;?></td>
	<td class="cntr">&nbsp;<?php echo $log->flow_out_calls;?></td>
	<td class="cntr">&nbsp;<?php echo $log->extn_out_calls;?></td>
	<td class="cntr">&nbsp;<?php echo $log->extn_out_time;?></td>
	<td class="cntr">&nbsp;<?php echo $log->extn_in_calls;?></td>
	<td class="cntr">&nbsp;<?php echo $log->extn_in_time;?></td>
	<td class="cntr">&nbsp;<?php echo $log->conference;?></td>
	<td class="cntr">&nbsp;<?php echo $log->transferred;?></td>
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
