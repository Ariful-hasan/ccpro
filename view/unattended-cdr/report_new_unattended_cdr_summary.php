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

$grid->AddModelCustomSearchable('DateTime', "sdate", 150, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Skill", "skill_id", 'select', array_merge(['' => 'All'], $skill_list[$skill_type]));

$grid->AddModelNonSearchable("Skill", "skill", 100, "center");
$grid->AddModelNonSearchable("Total Call", "total_call", 100, "center");
$grid->AddModelNonSearchable("Average Q Time", "avg_hold_in_q", 150, "center");
$grid->AddModelNonSearchable("Call Back Request", "cb_request", 150, "center");
$grid->AddModelNonSearchable("Call Back Within Threshold", "cb_within_threshold", 150, "center");
$grid->AddModelNonSearchable("Call Back After Threshold", "cb_after_threshold", 150, "center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    $(function () {
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });
</script>

