<?php
if (isset($_REQUEST['download'])) {
	require_once('lib/DownloadHelper.php');
	$dl_helper = new DownloadHelper($pageTitle, $this);
	$dl_helper->create_file('skill_log_spectrum.csv');
	$dl_helper->write_in_file("SL,Date,,,,Answered,,,,,,,Abandoned,,,\n");
	$dl_helper->write_in_file(",Duration(sec)>>,<5,5-10,10-20,20-30,30-45,45-60,>60,<5,5-10,10-20,20-30,30-45,45-60,>60\n");
	if (is_array($logs)) {
		$i = 0;
		$total_answered_l5 = 0;
		$total_answered_l10 = 0;
		$total_answered_l20 = 0;
		$total_answered_l30 = 0;
		$total_answered_l45 = 0;
		$total_answered_l60 = 0;
		$total_answered_g60 = 0;
		
		$total_abandoned_l5 = 0;
		$total_abandoned_l10 = 0;
		$total_abandoned_l20 = 0;
		$total_abandoned_l30 = 0;
		$total_abandoned_l45 = 0;
		$total_abandoned_l60 = 0;
		$total_abandoned_g60 = 0;

		foreach ($logs as $log) {
			$i++;
			$total_answered_l5 += $log->answered_l5;
			$total_answered_l10 += $log->answered_l10;
			$total_answered_l20 += $log->answered_l20;
			$total_answered_l30 += $log->answered_l30;
			$total_answered_l45 += $log->answered_l45;
			$total_answered_l60 += $log->answered_l60;
			$total_answered_g60 += $log->answered_g60;
			$total_abandoned_l5 += $log->abandoned_l5;
			$total_abandoned_l10 += $log->abandoned_l10;
			$total_abandoned_l20 += $log->abandoned_l20;
			$total_abandoned_l30 += $log->abandoned_l30;
			$total_abandoned_l45 += $log->abandoned_l45;
			$total_abandoned_l60 += $log->abandoned_l60;
			$total_abandoned_g60 += $log->abandoned_g60;

			$dl_helper->write_in_file("$i,$log->cdate,$log->answered_l5,$log->answered_l10,$log->answered_l20,$log->answered_l30,$log->answered_l45,$log->answered_l60,$log->answered_g60,$log->abandoned_l5,$log->abandoned_l10,$log->abandoned_l20,$log->abandoned_l30,$log->abandoned_l45,$log->abandoned_l60,$log->abandoned_g60\n");
		}
		$dl_helper->write_in_file(",Total,$total_answered_l5,$total_answered_l10,$total_answered_l20,$total_answered_l30,$total_answered_l45,$total_answered_l60,$total_answered_g60,$total_abandoned_l5,$total_abandoned_l10,$total_abandoned_l20,$total_abandoned_l30,$total_abandoned_l45,$total_abandoned_l60,$total_abandoned_g60\n");
	}
	$dl_helper->download_file();
	exit;
}
?><link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>
<style type="text/css">
.report_table tr.report_row_head td {
	background-image: -webkit-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
	background-image: -moz-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
	background-image: -o-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fff), color-stop(0.25, #fff), to(#e6e6e6)) !important;
	background-image: linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
	border-color: #dadada !important;
}
.report_table tr.report_row_empty td {
    color: #820a0a !important;
    font-size: 11px;
}
.report_table  td.cntr {
        text-align:center;
}
</style>
<script type="text/javascript">
/*
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
				$('#edate').dpSetStartDate(d.addDays(1).asString());
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
*/
</script>

<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
<table class="form_table">
	<tr class="form_row">
		<td>
			Skill: 
			<?php $this->html_options('skill', 'skill', $skills, $skill);?> &nbsp;
			From:
			<input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" class="date-pick" size="12" maxlength="10">
			<input type="text" name="stime" id="stime" value="<?php echo $dateinfo->stime;?>" size="5" maxlength="5"> &nbsp;&nbsp;
			To:
			<input type="text" name="edate" id="edate" value="<?php echo $dateinfo->edate;?>" class="date-pick" size="12" maxlength="10">
			<input type="text" name="etime" id="etime" value="<?php echo $dateinfo->etime;?>" size="5" maxlength="5"> &nbsp;&nbsp;

			&nbsp;<input class="form_submit_button" type="submit" name="search" value="Search" />
		</td>
	</tr>
</table>
</form>

<br />
<table class="report_table" width="98%" border="0" align="center" cellpadding="1" cellspacing="1">

<tr class="report_row_head" height=20 align="center">
	<td class="cntr">SL</td>
	<td class="cntr">Date</td>
	<td class="cntr" colspan="7">Answered</td>
	<td class="cntr" colspan="7">Abandoned</td>
</tr>
<tr class="report_row_head" height=20 align="center">
	<td class="cntr" colspan="2">Duration(sec)&gt;&gt;</td>
	<td class="cntr">&lt;5</td>
	<td class="cntr">5-10</td>
	<td class="cntr">10-20</td>
	<td class="cntr">20-30</td>
	<td class="cntr">30-45</td>
	<td class="cntr">45-60</td>
	<td class="cntr">&gt;60</td>
	<td class="cntr">&lt;5</td>
	<td class="cntr">5-10</td>
	<td class="cntr">10-20</td>
	<td class="cntr">20-30</td>
	<td class="cntr">30-45</td>
	<td class="cntr">45-60</td>
	<td class="cntr">&gt;60</td>
</tr>


<?php if (is_array($logs)):?>

<?php
	$i = 0;
	$total_answered_l5 = 0;
	$total_answered_l10 = 0;
	$total_answered_l20 = 0;
	$total_answered_l30 = 0;
	$total_answered_l45 = 0;
	$total_answered_l60 = 0;
	$total_answered_g60 = 0;
	$total_abandoned_l5 = 0;
	$total_abandoned_l10 = 0;
	$total_abandoned_l20 = 0;
	$total_abandoned_l30 = 0;
	$total_abandoned_l45 = 0;
	$total_abandoned_l60 = 0;
	$total_abandoned_g60 = 0;

	foreach ($logs as $log):
		$i++;
		$total_answered_l5 += $log->answered_l5;
		$total_answered_l10 += $log->answered_l10;
		$total_answered_l20 += $log->answered_l20;
		$total_answered_l30 += $log->answered_l30;
		$total_answered_l45 += $log->answered_l45;
		$total_answered_l60 += $log->answered_l60;
		$total_answered_g60 += $log->answered_g60;
		$total_abandoned_l5 += $log->abandoned_l5;
		$total_abandoned_l10 += $log->abandoned_l10;
		$total_abandoned_l20 += $log->abandoned_l20;
		$total_abandoned_l30 += $log->abandoned_l30;
		$total_abandoned_l45 += $log->abandoned_l45;
		$total_abandoned_l60 += $log->abandoned_l60;
		$total_abandoned_g60 += $log->abandoned_g60;

		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td class="cntr"><?php echo $log->cdate;?></td>
	<td class="cntr">&nbsp;<?php echo $log->answered_l5;?></td>
	<td class="cntr">&nbsp;<?php echo $log->answered_l10;?></td>
	<td class="cntr">&nbsp;<?php echo $log->answered_l20;?></td>
	<td class="cntr">&nbsp;<?php echo $log->answered_l30;?></td>
	<td class="cntr">&nbsp;<?php echo $log->answered_l45;?></td>
	<td class="cntr">&nbsp;<?php echo $log->answered_l60;?></td>
	<td class="cntr">&nbsp;<?php echo $log->answered_g60;?></td>
	<td class="cntr">&nbsp;<?php echo $log->abandoned_l5;?></td>
	<td class="cntr">&nbsp;<?php echo $log->abandoned_l10;?></td>
	<td class="cntr">&nbsp;<?php echo $log->abandoned_l20;?></td>
	<td class="cntr">&nbsp;<?php echo $log->abandoned_l30;?></td>
	<td class="cntr">&nbsp;<?php echo $log->abandoned_l45;?></td>
	<td class="cntr">&nbsp;<?php echo $log->abandoned_l60;?></td>
	<td class="cntr">&nbsp;<?php echo $log->abandoned_g60;?></td>
</tr>
<?php endforeach;?>
<?php $_class = $_class == 'report_row_alt' ? 'report_row' : 'report_row_alt'; ?>
<tr class="<?php echo $_class;?>">
	<td class="cntr" colspan="2">&nbsp;<strong>Total:</strong></td>
	<td class="cntr">&nbsp;<?php echo $total_answered_l5;?></td>
	<td class="cntr">&nbsp;<?php echo $total_answered_l10;?></td>
	<td class="cntr">&nbsp;<?php echo $total_answered_l20;?></td>
	<td class="cntr">&nbsp;<?php echo $total_answered_l30;?></td>
	<td class="cntr">&nbsp;<?php echo $total_answered_l45;?></td>
	<td class="cntr">&nbsp;<?php echo $total_answered_l60;?></td>
	<td class="cntr">&nbsp;<?php echo $total_answered_g60;?></td>
	<td class="cntr">&nbsp;<?php echo $total_abandoned_l5;?></td>
	<td class="cntr">&nbsp;<?php echo $total_abandoned_l10;?></td>
	<td class="cntr">&nbsp;<?php echo $total_abandoned_l20;?></td>
	<td class="cntr">&nbsp;<?php echo $total_abandoned_l30;?></td>
	<td class="cntr">&nbsp;<?php echo $total_abandoned_l45;?></td>
	<td class="cntr">&nbsp;<?php echo $total_abandoned_l60;?></td>
	<td class="cntr">&nbsp;<?php echo $total_abandoned_g60;?></td>
</tr>
</table>
<table class="report_extra_info">
<tr>
	<td>
	<?php if (is_array($logs)):?>
	<?php
		$session_id = session_id();
		$download = md5($pageTitle.$session_id);
		$dl_link = "download=$download&skill=$skill&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime";
    	$url = $this->url('task='.$request->getControllerName().'&act='.$request->getActionName().'&'.$dl_link);
	?>
		<img class="bottom" height="16" width="20" border="0" src="image/down_excel.gif"/><a class="btn-link" href="<?php echo $url;?>">download current records</a>
	<?php endif;?>
		&nbsp;
	</td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="17">No Record Found!</td>
</tr>
<?php endif;?>
</table>
