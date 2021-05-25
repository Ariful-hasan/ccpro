<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = false;
//$grid->styleLastRow = TRUE;
$grid->footerRow = true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
$grid->shrinkToFit = true;



/* ---------------------------View grid data by group based on column value----------------------------*/
//$grid->addGroup('sdate');

$grid->AddSearhProperty("Skill", "skill_id","select", $skills);

$grid->AddModelCustomSearchable('Date', "sdate", 100, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format . " 00:00"), date($report_date_format . " 00:00"), strtotime("+1 day"));
$grid->AddModelNonSearchable('Skill', "skill_name", 100, "center");
$grid->AddModelNonSearchable('Total Calls', "calls_offered", 100, "center");
$grid->AddModelNonSearchable('Abandoned', "calls_abandoned", 100, "center");
$grid->AddModelNonSearchable('Percentage (%)', "abandoned_percentage", 100, "center");

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

