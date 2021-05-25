<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
$grid->footerRow = TRUE;
$grid->userDataOnFooter = true;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

$grid->AddModelCustomSearchable('Date', "sdate", 100, "center","date");
$grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));

// $grid->AddModelCustomSearchable('Date&Time', "sdate", 110, "center","report-datetime");
// $grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty('IVR', 'ivr_id','select',$ivrs);

$grid->AddModelNonSearchable("IVR", "ivr_name", 100, "center");
$grid->AddModelNonSearchable("Total Hit", "total_hit", 100, "center");
$grid->AddModelNonSearchable("IVR Only", "ivr_only", 100, "center");
$grid->AddModelNonSearchable("Agent Call", "agent_only", 100, "center");
$grid->AddModelNonSearchable("IVR in Time (sec)", "time_in_ivr", 100, "center");
$grid->AddModelNonSearchable("Avg. IVR Time", "avg_time_in_ivr", 100, "center");
$grid->AddModelNonSearchable("IVR Ratio", "ivr_ratio", 100, "center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        SetNewReportDatePicker('<?php echo $report_date_format ?>');
        // SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });

</script>

