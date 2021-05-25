<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
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

$grid->AddSearhProperty('Date', 'sdate', 'date', $report_date_format);
$grid->SetDefaultValue('sdate', date($report_date_format), date($report_date_format,strtotime("+1day")));

// $grid->AddSearhProperty('Type:', 'report_type', 'select', $types);
$grid->AddSearhProperty('Skills:', 'skill_id', 'select', $skills);

$grid->AddModelNonSearchable("Date", "sdate", 100, "center");
$grid->AddModelNonSearchable('Calls Offered', "calls_offered", 75,"center");
$grid->AddModelNonSearchable('Calls Answered', "calls_answerd", 75,"center");
$grid->AddModelNonSearchable('Calls Abandoned', "calls_abandoned", 75,"center");
// $grid->AddModelNonSearchable('KPI IN', "kpi_in", 75,"center");
// $grid->AddModelNonSearchable('Talk Time', "talk_time", 75,"center");
$grid->AddModelNonSearchable('Average Handling Time(HH:mm:ss)', "avg_handling_time", 120,"center");
// $grid->AddModelNonSearchable('Wait Time(HH:mm:ss)', "hold_in_q", 120,"center");
$grid->AddModelNonSearchable('Service Level', "service_level", 120,"center");
// $grid->AddModelNonSearchable('Max. Wait Time(HH:mm:ss)', "max_hold_in_q", 110,"center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    $(function () {
        $(document).on("click", "#cboxClose", function () {
            location.reload(true);
        });
        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });
</script>

