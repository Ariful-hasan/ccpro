<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

$grid->AddSearhProperty("Date Time", "tstamp", "datetime");
$grid->SetDefaultValue("tstamp", date("Y-m-d 00:00"));
$grid->AddModelNonSearchable("URL", "url", 180, "center");
$grid->AddModelNonSearchable("Chat Count", "numrecords", 180, "center");
$grid->AddModelNonSearchable("Average Duration", "avg_duration", 180, "center");

$grid->show("#searchBtn");
?>

<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
window.onbeforeunload = function(){};
</script>