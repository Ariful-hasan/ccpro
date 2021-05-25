<?php
if (isset($_REQUEST['download'])) {
	require_once('lib/DownloadHelper.php');
	$dl_helper = new DownloadHelper($pageTitle, $this);
	$dl_helper->create_file('ivr_service_request_log.csv');
	$dl_helper->write_in_file("SL,Date Time,Served Time,Caller ID,Account. ID,Disposition Code,Agent ID,Nick Name,Status\n");

	$logs = $report_model->getServiceLog($dateinfo, $dcode, $clid, $alid);

	if (is_array($logs)) {
		$i = 0;
		foreach ($logs as $log) {
			$i++;	
			if ($log->status == 'S') $status = 'Served';
			else if ($log->status == 'B') $status = 'Bad Request';
			else if ($log->status == 'A') $status = 'Abandoned';
			else $status = $log->status;
			
			$dl_helper->write_in_file("$i," . date("Y-m-d H:i:s", $log->tstamp) . "," . 
				date("Y-m-d H:i:s", $log->served_time) . ",$log->caller_id,$log->account_id,".
				"$log->disposition_code,$log->agent_id,$log->nick,$status\n");
		}
	}
	$dl_helper->download_file();
	exit;
}
?>
<?php 
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


$grid->AddModel("Date Time", "date_time", 100, "center", ".date", "Y-m-d H:i", "Y-m-d H:i", true, "datetime");
$grid->SetDefaultValue("date_time", date("Y-m-d 00:00"), date("Y-m-d 23:59"));
//$grid->SetDefaultValue("date_time", $dateinfo->sdate);
$grid->AddSearhProperty("Disposition Code", "dcode", "select", $dp_options);
$grid->SetDefaultValue("dcode", $dcode);
$grid->AddSearhProperty("MSISDN", "clid", "text", "");
$grid->AddSearhProperty("IVR", "ivr_id", 'select', $ivrs);
//$grid->AddSearhProperty("Account ID", "alid", "text", "");

//$grid->AddModelNonSearchable("Skill Name", "skill_name", 80, "center");
//$grid->AddModelNonSearchable("Served Time", "servedTime", 80, "center");
$grid->AddModelNonSearchable("MSISDN", "caller_id", 80, "center");
//$grid->AddModelNonSearchable("Account ID", "account_id", 80,"center");
$grid->AddModelNonSearchable("Disposition", "disposition_code", 80,"center");
//$grid->AddModelNonSearchable("Agent ID", "agent_id", 80,"center");
//$grid->AddModelNonSearchable("Nick name", "nick", 80,"center");
$grid->AddModelNonSearchable("IVR", "ivr_name", 80,"center");
$grid->AddModelNonSearchable("Status", "statusTxt", 80,"center");

if ($_SERVER['REMOTE_ADDR'] == '72.48.199.118') 
$grid->AddTitleRightHtml('
        <a id="" class="monthselect btn btn-xs btn-info" href="" >Last Month</a>
        <a id="" class="monthselect btn btn-xs btn-info" href="" >Last Week</a>
');
$grid->show("#searchBtn");
?>
<script>
function LoadData(month){
        var data = jQuery("<?php echo $grid->GetGridId();?>").jqGrid("getGridParam", "postData");
        data.dateRange = month;
        //data.searchString = month;
        //data.searchOper = "eq";
        //data.searchField = "month";
        //console.log(data);
        //'ms%5Bdate_time%5D%5Bfrom%5D=&ms%5Bdate_time%5D%5Bto%5D=&ms%5Bclid%5D=&ms%5Bdcode%5D=*&ms%5Balid%5D='
        data._search = true;
        jQuery("<?php echo $grid->GetGridId();?>").jqGrid("setGridParam", { "postData": data });
        jQuery("<?php echo $grid->GetGridId();?>").trigger("reloadGrid");
}
$(function(){
        $("body").on("click",".monthselect",function(e){
                e.preventDefault();
                if(!$(this).hasClass("mselected")){
                        $(".monthselect").removeClass("mselected");
                        $(".monthselect").removeClass("btn-primary").addClass("btn-info");
                        $(this).removeClass("btn-info").addClass("mselected btn-primary");
                        
                        var cap=$(this).text();
                        //alert(cap);
                        LoadData(cap);
                }
        });
});
</script>
<!-- 
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
	<td class="cntr">Date Time</td>
	<td class="cntr">Served Time</td>
	<td>Caller ID</td>
	<td>Account. ID</td>
	<td class="cntr">Disposition Code</td>
	<td class="cntr">Agent ID</td>
	<td class="cntr">Status</td>
</tr>

<?php if (is_array($logs)):?>

<?php
	$i = $pagination->getOffset();
	
	foreach ($logs as $log):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
		if ($log->status == 'S') $status = 'Served';
		else if ($log->status == 'B') $status = 'Bad Request';
		else if ($log->status == 'A') $status = 'Abandoned';
		else $status = $log->status;
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td class="cntr"><?php echo date("Y-m-d H:i:s", $log->tstamp);?></td>
	<td class="cntr"><?php echo date("Y-m-d H:i:s", $log->served_time);?></td>
	<td align="left">&nbsp;<?php echo $log->caller_id;?></td>
	<td align="left">&nbsp;<?php echo $log->account_id;?></td>
	<td class="cntr"><?php echo $log->disposition_code;?></td>
	<td class="cntr"><?php echo $log->agent_id;?></td>
	<td class="cntr"><?php echo $status;?></td>
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
	<td colspan="8">No Record Found!</td>
</tr>
<?php endif;?>
</table>
 -->