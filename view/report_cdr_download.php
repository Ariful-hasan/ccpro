<?php
if (isset($_REQUEST['download'])) {
	require_once('lib/DownloadHelper.php');
	$dl_helper = new DownloadHelper($pageTitle, $this);
	$t = time();
	$dl_helper->create_file($t . '_skill_log_spectrum.csv');
	$dl_helper->write_in_file("SL,Start time,Answer time,Stop time,Call from,Call to,Duration,Talk time\r\n");
	$i = 0;
	$num_rows = 500;
	
	while ($num_rows == 500) {
                $logs = $report_model->getDLoadCDR($dateinfo, $i, 500);
                $num_rows = 0;
                if (is_array($logs)) {
                        $num_rows = count($logs);
        		foreach ($logs as $log) {
	        		$i++;
	        		$duration = gmdate("H:i:s", $log->duration);
	        		$talktime = empty($log->service_time) ? '00:00:00' : gmdate("H:i:s", $log->service_time);
        			$dl_helper->write_in_file("$i,$log->start_time,$log->answer_time,$log->tstamp,$log->cli,$log->did,$duration,$talktime\r\n");
	        	}
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
function Cdr_download(durl) {
	if(jQuery("#difrm").length==0){
		jQuery("body").append("<iframe id='difrm' style='border:none;height:0;width:0'></iframe>");
	}
	jQuery("#difrm").attr("src",durl);	
}
</script>
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
<table class="form_table">
	<tr class="form_row">
		<td>
			Date: 
			&nbsp;
			<input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" class="date-pick" size="12" maxlength="10"> &nbsp;
			to &nbsp;
			<input type="text" name="edate" id="edate" value="<?php echo $dateinfo->edate;?>" class="date-pick" size="12" maxlength="10">
			&nbsp;&nbsp;
			Time:
			<input type="text" name="stime" id="stime" value="<?php echo $dateinfo->stime;?>" size="6" maxlength="5" placeholder="00:00" /> &nbsp;
			to &nbsp;
			<input type="text" name="etime" id="etime" value="<?php echo $dateinfo->etime;?>" size="6" maxlength="5" placeholder="23:59" /> &nbsp;&nbsp;

			&nbsp;<input class="form_submit_button" type="submit" name="search" value="Search" />
		</td>
	</tr>
</table>
</form>

<?php if (is_array($logs)) {?>
<table class="report_extra_info" style="width: 100%">
<tr>
	<td style="padding: 6px 0;">
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
	</td>
	<?php if ($pagination->num_records <= 40000) {
	
	        $session_id = session_id();
                $download = md5($pageTitle.$session_id);
                $dl_link = "download=$download&skill=$skill&sdate=$dateinfo->sdate&edate=$dateinfo->edate&stime=$dateinfo->stime&etime=$dateinfo->etime";
                $url = $this->url('task='.$request->getControllerName().'&act='.$request->getActionName().'&'.$dl_link);
	?>
	<td style="padding: 6px 0;text-align:right;">
	<a class="btn btn-xs btn-success" onclick="Cdr_download('<?php echo $url;?>')"><i class="fa fa-download"></i> Download CSV</a>
	</td>
	<?php }?>
</tr>
</table>
<?php } else { ?>
<br />
<?php }?>
<table class="report_table" width="98%" border="0" align="center" cellpadding="1" cellspacing="1">

<tr class="report_row_head" height=20 align="center">
        <td class="cntr">SL</td>
	<td class="cntr">Start time</td>
	<td class="cntr">Answer time</td>
	<td class="cntr">Stop time</td>
	<td class="cntr">Call from</td>
	<td class="cntr">Call to</td>
	<td class="cntr">Duration</td>
	<td class="cntr">Talk time</td>
</tr>


<?php if (is_array($logs)):?>

<?php
	$i = 0;

	foreach ($logs as $log):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td class="cntr"><?php echo $log->start_time;?></td>
	<td class="cntr">&nbsp;<?php echo $log->answer_time;?></td>
	<td class="cntr">&nbsp;<?php echo $log->tstamp;?></td>
	<td class="cntr">&nbsp;<?php echo $log->cli;?></td>
	<td class="cntr">&nbsp;<?php echo $log->did;?></td>
	<td class="cntr">&nbsp;<?php echo gmdate("H:i:s", $log->duration);?></td>
	<td class="cntr">&nbsp;<?php echo empty($log->service_time) ? '00:00:00' : gmdate("H:i:s", $log->service_time);?></td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
        <td>
        <?php
                echo $pagination->createLinks();
        ?>
        </td>
</tr>                                                
<?php else:?>
<tr class="report_row_empty">
	<td colspan="8">No Record Found!</td>
</tr>
<?php endif;?>
</table>
