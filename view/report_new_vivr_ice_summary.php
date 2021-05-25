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
$grid->footerRow = TRUE;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
// $grid->AddSearhProperty("Date Format", "date_format",'select', $report_date_format_list);
// $grid->SetDefaultValue("date_format", $report_date_format);
$grid->AddModelCustomSearchable('Date&Time', "sdate", 110, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Type", "report_type",'select', $report_type_list);
$grid->SetDefaultValue("report_type", $report_type);
$grid->AddSearhProperty("Ivr ID", "ivr_id",'select', $ivrs);
$grid->AddSearhProperty("Source", "source",'select', $sources);
$grid->AddSearhProperty("DID", "did",'select', $dids);


$grid->AddModelNonSearchable('Month', "smonth", 70,"center");

$grid->AddModelNonSearchable('IVR ID', "ivr_id", 105,"center");
$grid->AddModelNonSearchable('Total Hit', "hit_count", 110,"center");
$grid->AddModelNonSearchable('Total ICE Count', "ice_count", 110,"center");
$grid->AddModelNonSearchable('ICE %', "ice_percentage", 75,"center");
$grid->AddModelNonSearchable('ICE Positive Feedback', "positive_ice_count", 150,"center");
$grid->AddModelNonSearchable('ICE Negative Feedback', "negative_ice_count", 150,"center");
$grid->AddModelNonSearchable('ICE Score', "ice_score", 75,"center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
    var skill_list = JSON.parse('<?php echo json_encode($skill_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');

    });
</script>

