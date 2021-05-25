<?php 
/* 
include_once "lib/jqgrid.php";

$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

$grid->AddTitleRightHtml('<a class="btn btn-xs btn-info" href=""> Mark selected items as served </a>');

$grid->AddModel("Date Time", "date_time", 100, "center", ".date", "Y-m-d", "Y-m-d", true, "date");
$grid->AddSearhProperty("Disposition Code", "disp_code", "select", $dp_options);
$grid->AddModelNonSearchable("Caller ID", "caller_id", 80, "center");
$grid->AddModelNonSearchable("Account ID", "account_id", 80,"center");
$grid->AddModelNonSearchable("Disposition Code", "disposition_code", 80,"center");
$grid->AddModelNonSearchable("<input type='checkbox' name='servedChkBox' id='servedChkBox' value='Y'>", "setServedChkBox", 80,"center");

$grid->show("#searchBtn");
 */
?>

<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">
<style type="text/css">
.rpt_table td.cntr{
	text-align: center !important;
}
.rpt_table {
	width: 100%;
    box-shadow: 0 0 3px #333;
	font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
}
.rpt_table tr.rpt_row_head {
    background: unset;
}
.rpt_table tr.rpt_row_head td {
    background-image: -webkit-linear-gradient(#fff, #fff 25%, #e6e6e6);
	background-image: -moz-linear-gradient(#fff, #fff 25%, #e6e6e6);
	background-image: -o-linear-gradient(#fff, #fff 25%, #e6e6e6);
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fff), color-stop(0.25, #fff), to(#e6e6e6));
	background-image: linear-gradient(#fff, #fff 25%, #e6e6e6);
	color: #333333;
	box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
	-webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
	-moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
	border-bottom: 1px solid #ccc;
	border-top: 0 none;
	padding: 0.5em;
}
.rpt_table tr.rpt_row td {
    background: #EFEFEF;
}
.rpt_table th, .rpt_table td {
    border: 1px solid #DDDDDD;
}
.rpt_table td {
	color: #404040;
	padding: 3px 5px;
}
.rpt_table tr.rpt_row_head td:first-child {
	border-left: 0 none;
}
.rpt_table tr.rpt_row_head td:last-child {
	border-right: 0 none;
}
.rpt_table tr:last-child td {
	border-bottom: 0 none;
}
.src-form .form_row td input, form[name="frm_search"] .form_row td input, .src-form .form_row td select, form[name="frm_search"] .form_row td select {
    border: 1px solid #e1e1e1;
    border-radius: 0;
    box-shadow: none;
	font-size: 13px;
    height: 24px;
    padding: 2px 9px;
}
.form_table input[type="submit"] {
    background-color: #fdb45c;
    background-image: none;
	font-weight: bold;
    text-shadow: unset;
}
</style>
<script type="text/javascript">
Date.format = 'yyyy-mm-dd';
$(function()
{
	//$('.date-pick').datePicker({clickInput:true,createButton:false,startDate:'2013-01-01'})
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
	
	 $('#check_all_req').change(function () {
		if($(this).prop('checked') == true){
			$('.chk_req').prop('checked', true);
		}else{
			$('.chk_req').prop('checked', false);
		}
		show_move_button();
    });
	
	$('.chk_req').change(function() {
		show_move_button();
	});
});

function show_move_button()
{
    var isCheckedAny = 'N';
	$('#mk_served').hide();
	$("input[name='sreqs[]']:checked").each(function (){
		isCheckedAny = 'Y';
	});
	if(isCheckedAny == 'Y'){
		$('#mk_served').show();
	}

	isCheckedAny = 'N';
	$("input[name='sreqs[]']:not(:checked)").each(function (){
		isCheckedAny = 'Y';
	});
	if (isCheckedAny == 'Y') {
		$('#check_all_req').prop('checked', false);
	} else {
		$('#check_all_req').prop('checked', true);
	}
}

</script>

<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
<table class="form_table">
	<tr class="form_row">
		<td>
			From:
			<input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" class="date-pick" size="12" maxlength="10"> &nbsp;&nbsp;
			To:
			<input type="text" name="edate" id="edate" value="<?php echo $dateinfo->edate;?>" class="date-pick" size="12" maxlength="10"> &nbsp;&nbsp;&nbsp;
			Disposition Code: <?php $this->html_options('dcode', 'dcode', $dp_options, $dcode);?> &nbsp;&nbsp;&nbsp;
			
			<input class="form_submit_button" type=submit name=search value="Search" />
		</td>										
	</tr>
</table>
</form>
<?php if (is_array($logs)):?>
<form name="frm_served" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=markservice");?>" onsubmit="return confirm('Are you sure to mark the selected item(s) as served?');">
<input type="hidden" name="page" value="<?php echo $pagination->current_page;?>" />
<input type="hidden" name="dcode" value="<?php echo $dcode;?>" />
<input type="hidden" name="sdate" value="<?php echo $dateinfo->sdate;?>" />
<input type="hidden" name="edate" value="<?php echo $dateinfo->edate;?>" />
<?php endif;?>


<?php if (is_array($logs)):?>
<table class="report_extra_info" style="width:100%; margin-top: 10px;">
<tr>
	<td>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
        <span id="spn_mk_button" style="float:right;"><input type="submit" class="btn btn-sm btn-info" name="mk_served" id="mk_served" value="Mark selected items as served" style="display:none;" /></span>
	</td>
</tr>
</table>
<?php else:?>
<br />
<?php endif;?>
<table class="rpt_table" style="margin-top: 10px;">
<tr class="rpt_row_head">
	<td class="cntr">SL</td>
	<td class="cntr">Date Time</td>
	<td>Skill Name</td>
	<td>Caller ID</td>
	<td>Account ID</td>
	<td class="cntr">Disposition</td>
	<td class="cntr"><?php if (is_array($logs)):?><input type="checkbox" id="check_all_req" name="check_all_req" value="Y" /><?php else:?>&nbsp;<?php endif;?></td>
</tr>

<?php if (is_array($logs)):?>

<?php
	$i = $pagination->getOffset();

	foreach ($logs as $log):
		$i++;
		$_class = $i%2 == 1 ? 'rpt_row' : 'rpt_row_alt';
                $disp_name = isset($dp_options[$log->disposition_code]) ? $dp_options[$log->disposition_code] : '-';
		
?>
<tr >
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td class="cntr"><?php echo date("Y-m-d H:i:s", $log->tstamp);?></td>
	<td align="left">&nbsp;<?php echo $log->skill_name;?></td>
	<td align="left">&nbsp;<?php echo $log->caller_id;?></td>
	<td align="left">&nbsp;<?php echo $log->account_id;?></td>
	<td class="cntr"><?php echo $disp_name;?></td>
	<td class="cntr"><input type="checkbox" class="chk_req" name="sreqs[]" value="<?php echo $log->tstamp . $log->caller_id; ?>" /></td>
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
</tr> */ ?>
</table>
<table class="report_extra_info">
<tr><td><?php echo $pagination->createLinks();?></td></tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="7">No Record Found!</td>
</tr>
<?php endif;?>
</table>
<?php if (is_array($logs)):?></form><?php endif;?>