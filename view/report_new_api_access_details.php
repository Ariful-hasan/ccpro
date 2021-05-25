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

$grid->AddModelCustomSearchable('Date&Time', "log_time", 110, "center","report-datetime");
$grid->SetDefaultValue("log_time", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Call ID", "callid");

$grid->AddModelNonSearchable('IVR Branch', "ivr_branch", 70,"center");
$grid->AddModelNonSearchable('Connection Name', "conn_name", 70,"center");
$grid->AddModelNonSearchable('Connection Method', "conn_method", 90,"center");
$grid->AddModelNonSearchable('Function', "api_function", 110,"center");
$grid->AddModelNonSearchable('Response Code', "response_code", 75,"center");
$grid->AddModelNonSearchable('Response Time', "response_time", 80,"center");
$grid->AddModelNonSearchable('Call ID', "callid", 100,"center");
$grid->AddModelNonSearchable('Transfer Time', "transfer_time", 80,"center");
$grid->AddModelNonSearchable('Download Size', "dl_size", 80,"center");
$grid->AddModelNonSearchable('Download Speed', "download_speed", 80,"center");
$grid->AddModelNonSearchable('Upload Speed', "upload_speed", 80,"center");


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

