<?php
if (isset($_REQUEST['download'])) {
	require_once('lib/DownloadHelper.php');
	$dl_helper = new DownloadHelper($pageTitle, $this);
	$dl_helper->create_file('agent_full_day_inbound_log.csv');
	$line = 'SL,Agent,Calls offered,Calls ans.,Calls abd.,% of call answerd,Avg handling time (h:m:s),'.
		'Avg talk time (h:m:s),Avg ACD hold time,Avg ACW time (h:m:s),Total staffed time (h:m:s),'.
		'Total handling time (h:m:s),Total AUX time (h:m:s),Total Avail Time (h:m:s)';

	if (is_array($aux_messages)) {
		foreach ($aux_messages as $aux) {
			$line .= ',' . $aux->message . ' (h:m:s)';
		}
	}
	$dl_helper->write_in_file($line."AUX Others (h:m:s)\n");

	$logs = $report_model->getAgentInboundLog($dateinfo);

	if (is_array($logs)) {
		$i = 0;
		foreach ($logs as $log) {
			$i++;
			$log->agentinfo = $report_model->getAgentSessionInfo($log->agent_id, $dateinfo);
			$total_calls_offered = $log->answered_calls+$log->alarm;

			$ans_percent = 0;
			if ($total_calls_offered > 0) {
				$ans_percent = $log->answered_calls*100/$total_calls_offered;
			}
			$ans_percent = sprintf("%02d", $ans_percent);
			$avg_handling_time = 0;
			if ($log->answered_calls > 0) {
				$avg_handling_time = ($log->aring_time+$log->talk_time+$log->acw_time)/$log->answered_calls;
			}
			$avg_handling_time = DateHelper::get_formatted_time($avg_handling_time);
			$avg_talk_time = 0;
			if ($log->answered_calls > 0) {
				$avg_talk_time = $log->talk_time/$log->answered_calls;
			}
			$avg_talk_time = DateHelper::get_formatted_time($avg_talk_time);

			$avg_hold_time = 0;
			if ($log->answered_calls > 0) {
				$avg_hold_time = $log->hold_time/$log->answered_calls;
			}
			$avg_hold_time = DateHelper::get_formatted_time($avg_hold_time);
			$avg_acw_time = 0;
			if ($log->answered_calls > 0) {
				$avg_acw_time = $log->acw_time/$log->answered_calls;
			}
			$avg_acw_time = DateHelper::get_formatted_time($avg_acw_time);
			$staffed_time = DateHelper::get_formatted_time($log->agentinfo->staffed_time);
			$handling_time = $log->aring_time+$log->talk_time+$log->acw_time;
			$handling_time = DateHelper::get_formatted_time($handling_time);
			$pause_time = DateHelper::get_formatted_time($log->agentinfo->pause_time);

			$avail_time = $log->agentinfo->staffed_time-$log->aring_time-$log->talk_time-$log->acw_time-$log->agentinfo->pause_time;
			$avail_time = DateHelper::get_formatted_time($avail_time);

			$line = "$i,$log->agent_id,$total_calls_offered,$log->answered_calls,".
				"$log->alarm,$ans_percent,$avg_handling_time,$avg_talk_time,$avg_hold_time,".
				"$avg_acw_time,$staffed_time,$handling_time,$pause_time,$avail_time";


			if (is_array($aux_messages)) {
				foreach ($aux_messages as $aux) {
					$auxindex = 'p'.$aux->aux_code;
					if (isset($log->agentinfo->$auxindex)) {
						$line .= ',' . DateHelper::get_formatted_time($log->agentinfo->$auxindex);
					} else {
						$line .= ',' . DateHelper::get_formatted_time(0);
					}
				}
			}
			
			$aux_others = isset($log->agentinfo->p) ? $log->agentinfo->p : 0;
			$line .= ',' . DateHelper::get_formatted_time($aux_others);
				
			$dl_helper->write_in_file($line."\n");
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

Date.format = 'yyyy-mm-dd';
$(function()
{
	$('.date-pick').datePicker({clickInput:true,createButton:false,startDate:'2011-01-01'})
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

<?php $total_column = is_array($aux_messages) ? count($aux_messages) + 16 : 16; ?>
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
<table class="form_table">
	<tr class="form_row">
		<td>
			From:
			<input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" class="date-pick" size="12" maxlength="10" />
			To:
			<input type="text" name="edate" id="edate" value="<?php echo $dateinfo->edate;?>" class="date-pick" size="12" maxlength="10" />
			&nbsp;
			<input class="form_submit_button" type="submit" name="search" value="Search" />
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
<?php else:?>
<br />
<?php endif;?>

<table class="report_table">
<tr class="report_row_head">
	<td class="cntr">SL</td>
	<td class="cntr">Agent</td>
	<td class="cntr">Calls offered</td>
	<td class="cntr">Calls ans.</td>
	<td class="cntr">Calls abd.</td>
	<td class="cntr">% of call answerd</td>
	<td class="cntr">Avg handling time (h:m:s)</td>
	<td class="cntr">Avg talk time (h:m:s)</td>
	<td class="cntr">Avg ACD hold time</td>
	<td class="cntr">Avg ACW time (h:m:s)</td>
	<td class="cntr">Total staffed time (h:m:s)</td>
	<td class="cntr">Total handling time (h:m:s)</td>
	<td class="cntr">Total AUX time (h:m:s)</td>
	<td class="cntr">Total Avail Time (h:m:s)</td>
	<?php if (is_array($aux_messages)):?>
	<?php foreach ($aux_messages as $aux):?>
	<td class="cntr"><?php echo $aux->message;?> (h:m:s)</td>
	<?php endforeach;?>
	<td class="cntr">AUX Others (h:m:s)</td>
	<?php endif;?>
</tr>

<?php if (is_array($logs)):?>

<?php
	$i = $pagination->getOffset();
	foreach ($logs as $log):
		$i++;
		$log->agentinfo = $report_model->getAgentSessionInfo($log->agent_id, $dateinfo);
		//var_dump($log->agentinfo);
		$total_calls_offered = $log->answered_calls+$log->alarm;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td class="cntr"><?php echo $log->agent_id;?></td>
	<td class="cntr"><?php echo $total_calls_offered;?></td>
	<td class="cntr">&nbsp;<?php echo $log->answered_calls;?></td>
	<td class="cntr">&nbsp;<?php echo $log->alarm;?></td>
	<td class="cntr">
	<?php
		$ans_percent = 0;
		if ($total_calls_offered > 0) {
			$ans_percent = $log->answered_calls*100/$total_calls_offered;
		}
		echo sprintf("%02d", $ans_percent);
	?>
	</td>
	<td class="cntr">
	<?php
		$avg_handling_time = 0;
		if ($log->answered_calls > 0) {
			$avg_handling_time = ($log->aring_time+$log->talk_time+$log->acw_time)/$log->answered_calls;
		}
		echo DateHelper::get_formatted_time($avg_handling_time);
	?>
	</td>
	<td class="cntr">
	<?php
		$avg_talk_time = 0;
		if ($log->answered_calls > 0) {
			$avg_talk_time = $log->talk_time/$log->answered_calls;
		}
		echo DateHelper::get_formatted_time($avg_talk_time);
	?>
	</td>
	<td class="cntr">
	<?php
		$avg_hold_time = 0;
		if ($log->answered_calls > 0) {
			$avg_hold_time = $log->hold_time/$log->answered_calls;
		}
		echo DateHelper::get_formatted_time($avg_hold_time);
	?>
	</td>
	<td class="cntr">
	<?php
		$avg_acw_time = 0;
		if ($log->answered_calls > 0) {
			$avg_acw_time = $log->acw_time/$log->answered_calls;
		}
		echo DateHelper::get_formatted_time($avg_acw_time);
	?>
	</td>
	<td class="cntr"><?php echo DateHelper::get_formatted_time($log->agentinfo->staffed_time);?></td>
	<td class="cntr">
	<?php
		$handling_time = $log->aring_time+$log->talk_time+$log->acw_time;
		echo DateHelper::get_formatted_time($handling_time);
	?>
	</td>
	<td class="cntr"><?php echo DateHelper::get_formatted_time($log->agentinfo->pause_time);?></td>
	<td class="cntr">
	<?php
		$avail_time = $log->agentinfo->staffed_time-$log->aring_time-$log->talk_time-$log->acw_time-$log->agentinfo->pause_time;
		echo DateHelper::get_formatted_time($avail_time);
	?>
	</td>

	<?php if (is_array($aux_messages)) {
		foreach ($aux_messages as $aux) {
			$auxindex = 'p'.$aux->aux_code;
			echo '<td class="cntr">';
			if (isset($log->agentinfo->$auxindex)) {
				echo DateHelper::get_formatted_time($log->agentinfo->$auxindex);
			} else {
				echo DateHelper::get_formatted_time(0);
			}
			echo '</td>';
		}
	}?>
	<td class="cntr">
	<?php
		$aux_others = isset($log->agentinfo->p) ? $log->agentinfo->p : 0;
		echo DateHelper::get_formatted_time($aux_others);
	?>
	</td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
	<td>	<?php
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
