<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>


<script type="text/javascript">Date.format = 'yyyy-mm-dd';
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
	
});</script>

<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
<table class="form_table">
	<tr class="form_row">
		<td>
			Date:
			<input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" class="date-pick" size="12" maxlength="10" />
			&nbsp;
			<input class="form_submit_button" type="submit" name="search" value="Search" />
		</td>										
	</tr>
</table>
</form>

<br />

<table class="form_table table">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" style="width:250px;">Calls offered:</td>
		<td>&nbsp;<?php $total_calls_offered = $log->answered_calls+$log->alarm; echo $total_calls_offered;?></td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Calls ans.:</td>
		<td>&nbsp;<?php echo $log->answered_calls;?></td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Calls abd.:</td>
		<td>&nbsp;<?php echo $log->alarm;?></td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">% to call answerd:</td>
		<td>&nbsp;<?php
		$ans_percent = 0;
		if ($total_calls_offered > 0) {
			$ans_percent = $log->answered_calls*100/$total_calls_offered;
		}
		echo sprintf("%02d", $ans_percent);
	?></td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Avg handling time (h:m:s):</td>
		<td>&nbsp;<?php
		$avg_handling_time = 0;
		if ($log->answered_calls > 0) {
			$avg_handling_time = ($log->aring_time+$log->talk_time+$log->acw_time)/$log->answered_calls;
		}
		echo DateHelper::get_formatted_time($avg_handling_time);
	?></td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Avg talk time (h:m:s):</td>
		<td>&nbsp;<?php
		$avg_talk_time = 0;
		if ($log->answered_calls > 0) {
			$avg_talk_time = $log->talk_time/$log->answered_calls;
		}
		echo DateHelper::get_formatted_time($avg_talk_time);
	?></td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Avg ACD hold time:</td>
		<td>&nbsp;<?php
		$avg_hold_time = 0;
		if ($log->answered_calls > 0) {
			$avg_hold_time = $log->hold_time/$log->answered_calls;
		}
		echo DateHelper::get_formatted_time($avg_hold_time);
	?></td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Avg ACW time (h:m:s):</td>
		<td>&nbsp;<?php
		$avg_acw_time = 0;
		if ($log->answered_calls > 0) {
			$avg_acw_time = $log->acw_time/$log->answered_calls;
		}
		echo DateHelper::get_formatted_time($avg_acw_time);
	?></td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Total staffed time (h:m:s):</td>
		<td>&nbsp;<?php echo DateHelper::get_formatted_time($agentinfo->staffed_time);?></td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Total handling time (h:m:s):</td>
		<td>&nbsp;<?php
		$handling_time = $log->aring_time+$log->talk_time+$log->acw_time;
		echo DateHelper::get_formatted_time($handling_time);
	?></td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Total AUX time (h:m:s):</td>
		<td>&nbsp;<?php echo DateHelper::get_formatted_time($agentinfo->pause_time);?></td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Total Avail Time (h:m:s):</td>
		<td>&nbsp;<?php
		$avail_time = $agentinfo->staffed_time-$log->aring_time-$log->talk_time-$log->acw_time-$agentinfo->pause_time;
		echo DateHelper::get_formatted_time($avail_time);
	?></td>
	</tr>
	<?php if (is_array($aux_messages)):$i = 0;?>
	<?php foreach ($aux_messages as $aux):
		
		$_class = $i%2 == 1 ? 'form_row' : 'form_row_alt';
		$i++;
		$val = '';
		$auxindex = 'p'.$aux->aux_code;
		if (isset($agentinfo->$auxindex)) {
			$val = DateHelper::get_formatted_time($agentinfo->$auxindex);
		} else {
			$val = DateHelper::get_formatted_time(0);
		}
	?>
	<tr class="<?php echo $_class;?>"><td class="form_column_caption"><?php echo $aux->message;?> (h:m:s):</td>
    <td>&nbsp;<?php echo $val;?></td></tr>
	<?php endforeach;?>
	<?php endif;?>
</tbody>
</table>

