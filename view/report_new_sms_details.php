<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = false;
$grid->footerRow = false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

$grid->AddModelCustomSearchable('Date&Time', "sdate", 110, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));

$grid->AddModelNonSearchable("Session ID", "session_id", 100, "center");
//$grid->AddModelNonSearchable("Arrival Time", "last_in_msg_time", 120, "center");
// $grid->AddModelNonSearchable("Arrival Time", "arrival_time", 120, "center");
$grid->AddModelNonSearchable("Receive Time", "receive_time", 120, "center");
$grid->AddModelNonSearchable("Served Time", "last_out_msg_time", 120, "center");
$grid->AddModelNonSearchable("Last Update Time", "last_update_time", 120, "center");
$grid->AddModelNonSearchable("Call ID", "callid", 120,"center");
$grid->AddModelNonSearchable("Disposition", "title", 150, "center");
$grid->AddModelNonSearchable("Agent ID", "agent_id", 80, "center");
$grid->AddModel("MSISDN", "phone_number", 80,"center");
$grid->AddModelNonSearchable("DID", "did", 80,"center");
$grid->AddModelNonSearchable("Skill", "skill_id", 80,"center");
$grid->AddModelNonSearchable("Status", "status_code", 50,"center");
$grid->AddModelNonSearchable("Wait Time <br/>(hh:mm:ss)", "wait_time", 80,"center");
$grid->AddModelNonSearchable("Agent Handling Time <br/> (hh:mm:ss)", "agent_handling_time", 140,"center");
$grid->AddModelNonSearchable("Service Level", "sl", 130,"center");
$grid->AddModelNonSearchable("Wrap Up Time <br/>(hh:mm:ss)", "wrapup_time", 100,"center");
$grid->AddModelNonSearchable("ICE Feedback", "customer_feedback", 100,"center");
$grid->AddModelNonSearchable("Action", "actUrl", 50,"center");

$grid->show("#searchBtn");?>

<script type="text/javascript">

    $(function(){
        // $(document).on("click","#cboxClose",function () {
        //     location.reload(true);
        // });
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });
</script>

