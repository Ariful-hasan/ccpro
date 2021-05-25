<?php
if (isset($_REQUEST['download'])) {
	require_once('lib/DownloadHelper.php');
	//echo 'asd';exit;
	$dl_helper = new DownloadHelper('gplexcc', $this);
	$pageTitle = "Agent[$agentid] Session Details : " . DateHelper::get_date_title($dateinfo);
	$dl_helper->set_title($pageTitle);
	$dl_helper->create_file('agent_session_details.csv');
	
	$edate = $dateinfo->edate;
	if (empty($edate)) $edate = $dateinfo->sdate;
	
	$dl_helper->write_in_file("SL,Time,Status\n");
	$logs = $report_model->getAgentSessionLog($agentid, $dateinfo->sdate . ' 00:00:00', $edate . ' 23:59:59');
	//var_dump($logs);
	//exit;
	if (is_array($logs)) {
		$i = 0;
		foreach ($logs as $log) {
			$i++;
			$type = '-';
			if (!empty($log->type)) {
				if ($log->type == 'I') {
					$type = 'Login';
					if (!empty($log->value)) $type .= ' ('.$log->value.')';
				} else if ($log->type == 'O') {
					$type = 'Logout';
					if (!empty($log->value)) $type .= ' ('.$log->value.')';
				} else if ($log->type == 'R') {
					$type = 'Ready';
				} else if ($log->type == 'X') {
					$type = isset($aux_messages[$log->value]) ?  $aux_messages[$log->value] : '-';
				}
			}

			$dl_helper->write_in_file("$i," . date("Y-m-d H:i:s", $log->tstamp) . ",$type\n");
		}
	}
	$dl_helper->download_file();
	exit;
}
?>
<style type="text/css">
.chart .bar {
	width:720px;
	/*background-image: url(images/gridline58.gif);*/
	background-repeat: repeat-x;
	background-position: left top;
	border-left: 1px solid #e5e5e5;
	border-right: 1px solid #e5e5e5;
	padding:0;
	border-bottom: none;
	background-color:#cccccc;
	height:15px;
}
.chart .bar div {
	float:left;
	height:15px;
}
.chart .filled {
	background-color: #dd7777;
}
</style>
<link rel="stylesheet" type="text/css" media="screen" href="css/form.css?v=1.0.3">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">

<table class="form_table"><tr class="form_row"><td><?php echo $session_chart;?></td></tr></table>
<br />

<?php
	$session_id = session_id();
	$download = md5('gplexcc'.$session_id);
?>

<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
<table class="form_table">
	<tr class="form_row">
		<td>
			YYYY-MM-DD: <input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" class="date-pick " size="12" maxlength="10" />
			to <input type="text" name="edate" id="edate" value="<?php echo $dateinfo->sdate;?>" class="date-pick" size="12" maxlength="10" />
			<input type="hidden" name="download" value="<?php echo $download;?>" />
			<input type="hidden" name="agentid" value="<?php echo $agentid;?>" />
			&nbsp; <input class="form_submit_button" type="submit" name="search" value="Download" />
		</td>										
	</tr>
</table>
</form>
<br />
<table class="report_table" width="90%" border="0" align="center" cellpadding="1" cellspacing="1">
	<tr class="report_row_head">
		<td class="cntr">SL</td>
		<td class="cntr">Time</td>
		<td class="cntr">Status</td>
	</tr>

	<?php if (is_array($logs)):?>
		<?php $i = 0;?>
		<?php foreach ($logs as $log):?>
		<?php
			$i++;
			$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
		?>
			<tr class="<?php echo $_class;?>">
				<td class="cntr"><b><?php echo $i;?></b>&nbsp;</td>
                <td class="cntr"><?php echo date("Y-m-d H:i:s", $log->tstamp);?></td>
				<td class="cntr">&nbsp;
				<?php
					if (!empty($log->type)) {
						if ($log->type == 'I') {
							echo 'Login';
							if (!empty($log->value)) echo ' ('.$log->value.')';
						} else if ($log->type == 'O') {
							echo 'Logout';
							if (!empty($log->value)) echo ' ('.$log->value.')';
						} else if ($log->type == 'R') {
							echo 'Ready';
						} else if ($log->type == 'X') {
							echo isset($aux_messages[$log->value]) ?  $aux_messages[$log->value] : '-';
						}
					} else {
						echo '-';
					}
				?>
				</td>
			</tr>
		<?php endforeach;?>
		<tr>

	<?php else:?>
		<tr class="report_row_empty">
			<td colspan="3">
				No Record Found!
			</td>
		</tr>
	<?php endif;?>
</table>
<script type="text/javascript">
window.onbeforeunload = function(){};
</script>
