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
$grid->footerRow = false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}

$grid->AddModelCustomSearchable('Date&Time', "sdate", 110, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Ivr ID", "ivr_id",'select', $ivrs);

$grid->AddModelNonSearchable('Skill Name', "skill_name", 100,"center");
$grid->AddModelNonSearchable('MSISDN (880)', "msisdn_880", 100,"center");
$grid->AddModelNonSearchable('MSISDN', "cli", 100,"center");
$grid->AddModelNonSearchable('ICE Feedback', "ice_feedback", 100,"center");

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

