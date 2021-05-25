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


$grid->AddModelCustomSearchable('Date&Time', "sdate", 110, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Type", "report_type",'select', $report_type_list);
$grid->SetDefaultValue("report_type", $report_type);

$grid->AddSearhProperty("Agents", "agent_id", 'select', $agent_list);
$grid->AddModelNonSearchable('Month', "smonth", 70,"center");

$grid->AddModelNonSearchable('Agent ID', "agent_id", 105,"center");
$grid->AddModelNonSearchable('Agent Name', "agent_name", 105,"center");
$grid->AddModelNonSearchable('Calls Answered', "call_ans", 110,"center");
$grid->AddModelNonSearchable('Total ICE Count', "ice_count", 110,"center");
$grid->AddModelNonSearchable('ICE %', "ice_percentage", 75,"center");
$grid->AddModelNonSearchable('ICE Positive Feedback', "ice_feedback_yes", 150,"center");
$grid->AddModelNonSearchable('ICE Negative Feedback', "ice_feedback_no", 150,"center");
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

