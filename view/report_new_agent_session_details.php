<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
$grid->footerRow = false;
$grid->userDataOnFooter = false;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

$grid->AddSearhProperty('Date', "sdate",'report-datetime');
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 23:59"));
$grid->AddSearhProperty('Agent', "agent_id",'select', $agents);
$grid->SetDefaultValue("agent_id",'*');
$grid->AddModelNonSearchable("Time", "tstamp", 100, "center");
$grid->AddModelNonSearchable("Agent ID", "agent_id", 100, "center");
$grid->AddModelNonSearchable("Agent Name", "agent_name", 100, "center");
$grid->AddModelNonSearchable("Status", "type", 100, "center");



$grid->show("#searchBtn");


?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });


</script>

