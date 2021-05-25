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

$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
$grid->footerRow = TRUE;
$grid->userDataOnFooter = true;

$grid->AddModelCustomSearchable('Start Time', "sdate", 110, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty('IVR', 'ivr_id','select',$ivrs);
$grid->AddSearhProperty('Source', 'source','select',$sources);
$grid->AddSearhProperty('DID', 'did','select', $dids);

//$grid->AddModelNonSearchable("IVR Enter Time", "enter_time", 100, "center");
$grid->AddModelNonSearchable("Stop Time", "stop_time", 100, "center");
$grid->AddModelNonSearchable("IVR", "ivr_name", 100, "center");
$grid->AddModelNonSearchable("Source", "source", 100, "center");
$grid->AddModelNonSearchable("CLI", "cli", 100, "center");
$grid->AddModelNonSearchable("DID", "did", 100, "center");
$grid->AddModelNonSearchable("Time in IVR", "time_in_ivr", 100, "center");

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