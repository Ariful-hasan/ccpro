<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->hidecaption = false;
$grid->CustomSearchOnTopGrid = true;

$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;

$grid->ShowDownloadButtonInBottom = false;
$grid->multisearch = true;


$grid->AddSearhProperty('Date', 'sdate', 'date', $report_date_format);
$grid->SetDefaultValue('sdate', date($report_date_format), date($report_date_format,strtotime("+1day")));

$grid->AddModelNonSearchable("Date", "sdate", 100, "center");
// $grid->AddModelNonSearchable("Skill ID", "skill_id", 100, "center");
$grid->AddModelNonSearchable("Skill Name", "skill_name", 100, "center");
$grid->AddModelNonSearchable("Forcast Value", "forcast_value", 100, "center");
// $grid->AddModelNonSearchable("RGB", "rgb", 100, "center");

$grid->show("#searchBtn");
?>
<script type="text/javascript">
    $(function () {
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
    SetNewReportDatePicker('<?php echo $report_date_format ?>');
</script>
