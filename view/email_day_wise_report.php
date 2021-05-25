<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->shrinkToFit=false;
$grid->ShowDownloadButtonInTitle=true;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}

$grid->AddModelCustomSearchable('Date', "last_update_time", 110, "center","date");
$grid->SetDefaultValue("last_update_time", date($report_date_format), date($report_date_format, strtotime('+1day')));
$grid->AddModelNonSearchable("Skill Set Name", "skill_id", 100, "center");
$grid->AddModelNonSearchable("Total Email", "total_email", 100, "center");
$grid->AddModelNonSearchable("Number Of Closed Email", "total_closed", 180, "center");
$grid->AddModelNonSearchable("Total In KPI", "total_in_kpi", 120, "center");
$grid->AddModelNonSearchable("Total Out KPI (if exceed 24hr)", "total_out_kpi", 200, "center");
$grid->AddModelNonSearchable("Avg Wait Time(hh:mm:ss)", "awt", 180, "center");
$grid->AddModelNonSearchable("Max Wait Time(hh:mm:ss)", "mwt", 180, "center");
$grid->AddModelNonSearchable("Total Open Time(hh:mm:ss)", "tod", 180, "center");
$grid->AddModelNonSearchable("AHT", "aht", 100, "center");
$grid->AddModelNonSearchable("SL %", "sl", 100, "center");
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
