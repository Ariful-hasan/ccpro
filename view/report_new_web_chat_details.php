<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = false;
$grid->footerRow = TRUE;
$grid->CustomSearchOnTopGrid = true;
$grid->multisearch = true;
$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

$grid->AddModelCustomSearchable('Date&Time', "sdate", 150, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty('Skills:', 'skill_id', 'select', $skills);
$grid->AddModelNonSearchable("Skill", "skill_id", 150, "center");
$grid->AddModelNonSearchable("Agent ID", "agent_id", 150, "center");
$grid->AddModelNonSearchable("Agent Name", "agent_name", 150, "center");
$grid->AddModelNonSearchable("Mobile No", "customer_number", 150, "center");
$grid->AddModelNonSearchable("Customer Name", "customer_name", 150, "center");


$grid->AddModelNonSearchable('Abandoned before <br/>threshold (<=60 Sec)', "abandon_flag", 150,"center");
$grid->AddModelNonSearchable('Abandoned after <br/>threshold (>60 Sec)', "abandon_af_60", 150,"center");
$grid->AddModelNonSearchable("Received <br /> Date Time ", "agent_response_time", 120, "center");
$grid->AddModelNonSearchable("Agent Respose<br>Time", "agent_first_response", 120, "center");
$grid->AddModelNonSearchable("First Response<br>Duration", "first_respond_duration", 120, "center");
$grid->AddModelNonSearchable("WebChat Close <br /> Date Time", "stop_time", 150, "center");
$grid->AddModelNonSearchable("WebChat Duration <br /> (hh:mm:ss)", "service_time_min", 150, "center");
$grid->AddModelNonSearchable("Wait Time <br /> (hh:mm:ss)", "wait_time", 150, "center");
$grid->AddModelNonSearchable("Handling Time <br /> (hh:mm:ss)", "handling_time", 100, "center");
$grid->AddModelNonSearchable("SL (%)", "sl", 150, "center");
$grid->AddModelNonSearchable("Reason Code", "reason", 150, "center");
$grid->AddModelNonSearchable('Log', "sip_log", 150,"center");
$grid->AddModelNonSearchable("Customer's <br /> Feedback", "customer_feedback", 120,"center");
$grid->AddModelNonSearchable("Verified Customer", "is_verified", 150, "center");
$grid->AddModelNonSearchable("Disc Party", "disc_party", 150, "center");
$grid->show("#searchBtn");

?>

<script type="text/javascript">
    $(function () {
        // $(document).on("click", "#cboxClose", function () {
        //     location.reload(true);
        // });
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });
</script>

