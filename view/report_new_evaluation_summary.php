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
$grid->AddSearhProperty('Service:', 'service_id', 'select', $service_list);
$grid->AddSearhProperty('Evaluator Name:', 'agent_id', 'select', $evaluator_list);

$grid->AddModelNonSearchable("Date", "sdate", 100, "center");
$grid->AddModelNonSearchable('Service Name', "service_name", 105,"center");
$grid->AddModelNonSearchable('Evaluator Name', "evaluator_name", 105,"center");
$grid->AddModelNonSearchable('Evaluate Count', "evaluate_count", 105,"center");

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

