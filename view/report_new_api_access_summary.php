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
$grid->footerRow = true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

$grid->AddModelCustomSearchable('Date&Time', "ldate", 110, "center","report-datetime");
$grid->SetDefaultValue("ldate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Type", "report_type",'select', $report_type_list);
$grid->SetDefaultValue("report_type", $report_type);

$grid->AddModelNonSearchable('Hour', "lhour", 70,"center");
$grid->AddModelNonSearchable('Connection', "conn_name", 70,"center");
$grid->AddModelNonSearchable('Total Count', "total_count", 70,"center");
$grid->AddModelNonSearchable('Success Count', "success_count", 90,"center");
$grid->AddModelNonSearchable('Error Count', "error_count", 110,"center");
$grid->AddModelNonSearchable('Timeout Count', "timeout_count", 75,"center");
$grid->AddModelNonSearchable('Total Response time', "total_response_time", 150,"center");
$grid->AddModelNonSearchable('Avg. Response Time', "avg_response_time", 150,"center");
$grid->AddModelNonSearchable('Success Ration', "success_ration", 150,"center");


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

