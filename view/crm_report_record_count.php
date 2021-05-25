<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>


<script type="text/javascript">
	data_function="{$data_function}";
	Date.format = 'yyyy-mm-dd';
	$(function() {
		$('.date-pick').datePicker({clickInput:true,createButton:false,startDate:'2013-01-01'});
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
<style>
select {
width:auto;
}
</style>

<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_search" method="post" onsubmit="return checkDate()" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
<table class="form_table">
	<tr class="form_row">
		<td>
			Campaign : <select name="camid"><?php foreach ($campaign_options as $cokey => $coval) { echo '<option value="'.$cokey.'"';
			if ($cokey == $camid) echo ' selected';
			echo '>'.$coval.'</option>';}?></select> &nbsp; 
            Lead : <select name="leadid"><?php foreach ($lead_options as $cokey => $coval) { echo '<option value="'.$cokey.'"';
			if ($cokey == $leadid) echo ' selected';
			echo '>'.$coval.'</option>';}?></select> &nbsp; 
            From:
			<input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" class="date-pick" size="12" maxlength="10">
			<input type="text" name="stime" id="stime" value="<?php echo $dateinfo->stime;?>" size="5" maxlength="5"> &nbsp;&nbsp;
			To:
			<input type="text" name="edate" id="edate" value="<?php echo $dateinfo->edate;?>" class="date-pick" size="12" maxlength="10">
			<input type="text" name="etime" id="etime" value="<?php echo $dateinfo->etime;?>" size="5" maxlength="5"> &nbsp;&nbsp;&nbsp;

			<input class="form_submit_button" type="submit" name="search" value="Search">
		</td>										
	</tr>
</table>
</form>
<?php
$colspan = 4;
//var_dump($skills_out);
?>

<?php if (is_array($records)):?>
<table class="report_extra_info">
<tr>
	<td>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
			echo '&nbsp; :: &nbsp;Total record count: <b>' . $total_crm_records . '</b>';
		?>
	</td>
</tr>
</table>
<?php else:?>
<br />
<?php endif;?>
<table class="report_table" width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td width="15%">SL</td>
	<td width="45%">Disposition</td>
	<td width="20%">Record count</td>
	<td width="20%">%</td>
</tr>

<?php if (is_array($records)):?>

<?php

	$dps = array();
	if (is_array($dp_options)) {
		foreach ($dp_options as $dp) { 

			if ($dp->campaign_id == '0000') $camtitle = 'System';
			else if ($dp->campaign_id == '1111') $camtitle = 'General';
			else $camtitle = $dp->campaign_title;
			$dps[$dp->disposition_id] = $camtitle . ' - ' . $dp->title;
		}
	}

            
	$i = $pagination->getOffset();
	$session_id = session_id();
	foreach ($records as $row):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td align="center">&nbsp;<?php echo $i;?></td>
	<td align="left">&nbsp;<?php echo isset($dps[$row->last_disposition_id]) ? $dps[$row->last_disposition_id]: $row->last_disposition_id;?></td>
	<td align="center"><?php echo $row->numrecords;?></td>
	<td align="center"><?php $p = $total_crm_records > 0 ? sprintf("%2d", $row->numrecords*100/$total_crm_records) : '-';
	echo $p;?></td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
	<td><?php echo $pagination->createLinks();?></td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="<?php echo $colspan;?>">No record found!</td>
</tr>
<?php endif;?>
</table>

