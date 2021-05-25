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

//$grid->AddSearhProperty("Date Time", "sdate", "report-datetime");
//$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddModelCustomSearchable('Date Time', "sdate", 110, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));

//$grid->AddSearhProperty('Skills:', 'skill_id', 'select', $skills);
$grid->AddSearhProperty('Agent', 'agent_id', 'select', $agents);


//$grid->AddModelNonSearchable("Date", "sdate", 100, "center");
$grid->AddModelNonSearchable("Agent ID", "agent_id", 100, "center");
$grid->AddModelNonSearchable("Agent Name", "agent_name", 100, "center");
//$grid->AddModelNonSearchable("Skill", "skill_id", 100, "center");
//$grid->AddModelNonSearchable("Service Name", "service_id", 100, "center");
//$grid->AddModelNonSearchable("Start Time", "start_time", 100, "center");
//$grid->AddModelNonSearchable("End Time", "end_time", 100, "center");
//$grid->AddModelNonSearchable("Hold Time (sec)", "hold_time", 100, "center");
//$grid->AddModelNonSearchable("Talk Time (sec)", "duration", 100, "center");
//$grid->AddModelNonSearchable("Email", "email", 100, "center");
//$grid->AddModelNonSearchable("Contact Number", "contact_number", 100, "center");





//$grid->AddModelNonSearchable('Agent ID', "agent_id", 80, "center");
//$grid->AddModelNonSearchable('Very Bad', "very_bad", 80, "center");
$grid->AddModelNonSearchable('Bad', "bad", 80,"center");
//$grid->AddModelNonSearchable('Normal', "normal", 80,"center");
$grid->AddModelNonSearchable('Good', "good", 80,"center");
$grid->AddModelNonSearchable('No Rating', "no_rating", 80, "center");
//$grid->AddModelNonSearchable('Very Good', "very_good", 80,"center");
//$grid->AddModelNonSearchable('Average', "average_rating", 100,"center");


$grid->show("#searchBtn");

?>

<script type="text/javascript">
    <?php /* ?>$(function () {
        $(document).on("click", "#cboxClose", function () {
            location.reload(true);
        });
        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });
<?php */?>


    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });
</script>

