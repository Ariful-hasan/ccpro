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
$grid->footerRow = false;
$grid->CustomSearchOnTopGrid = true;
$grid->multisearch = true;
$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

$grid->AddModelCustomSearchable('Date', "sdate", 110, "center","date");
$grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));
$grid->AddSearhProperty('Skills:', 'skill_id', 'select', $skills);

$grid->AddModelNonSearchable("Skill", "skill_id", 150, "center");
$grid->AddModelNonSearchable("Offered", "offered_chat", 150, "center");
$grid->AddModelNonSearchable("Answered", "total_chat", 150, "center");
$grid->AddModelNonSearchable('Abandoned before <br/>threshold (<=60 Sec)', "abd_before_60", 180,"center");
$grid->AddModelNonSearchable('Abandoned after <br/>threshold (>60 Sec)', "abd_after_60", 180,"center");
$grid->AddModelNonSearchable('Total In KPI', "in_kpi", 180,"center");
$grid->AddModelNonSearchable("Total Out KPI", "out_kpi", 250,"center");
$grid->AddModelNonSearchable("Total Handling Time <br/> (hh:mm:ss)", "total_service_time", 150,"center");
$grid->AddModelNonSearchable("Total Wait Time <br/> (hh:mm:ss)", "total_wait_time", 150,"center");
$grid->AddModelNonSearchable("Avg. Wait Time <br> (hh:mm:ss)", "avg_wait_time", 150,"center");
$grid->AddModelNonSearchable("Avg. Handling Time <br> (hh:mm:ss)", "aht", 150,"center");
$grid->AddModelNonSearchable("SL", "sl", 100, "center");
$grid->AddModelNonSearchable("Verified", "verified", 150, "center");
$grid->AddModelNonSearchable("Non Verified", "non_verified", 150, "center");
$grid->AddModelNonSearchable('Total <br>ICE Count', "ice_count", 75,"center");
$grid->AddModelNonSearchable('ICE Positive <br>Feedback', "ice_positive_count", 110,"center");
$grid->AddModelNonSearchable('ICE Negative <br>Feedback', "ice_negative_count", 110,"center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    $(function () {
        // $(document).on("click", "#cboxClose", function () {
        //     location.reload(true);
        // });
        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });
</script>

