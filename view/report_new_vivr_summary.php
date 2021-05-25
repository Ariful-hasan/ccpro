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

$grid->AddModelCustomSearchable('Date', "sdate", 100, "center","date");
$grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));
$grid->AddSearhProperty('IVR', 'ivr_id','select',$ivrs);
$grid->AddSearhProperty('Source', 'source','select',$sources);

$grid->AddModelNonSearchable("VIVR", "ivr_name", 100, "center");
$grid->AddModelNonSearchable("Source", "source", 100, "center");
$grid->AddModelNonSearchable("Total Hit", "total_hit", 100, "center");
$grid->AddModelNonSearchable("VIVR in Time (sec)", "time_in_ivr", 100, "center");
$grid->AddModelNonSearchable("Avg. VIVR Time", "avg_time_in_ivr", 100, "center");
//$grid->AddModelNonSearchable("VIVR Ratio", "ivr_ratio", 100, "center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });

</script>

