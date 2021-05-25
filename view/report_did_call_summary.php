<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->shrinkToFit = true;
//$grid->loadComplete="onGridLoad";

$grid->AddSearhProperty("Date", "sdate_box", "datetime");
$grid->SetDefaultValue("sdate_box", date("Y-m-d 00:00"));

//$grid->AddModelNonSearchable('Date', "sdate", 100, "center");
//$grid->AddModelNonSearchable('Hour', "shour", 80, "center");
$grid->AddModelNonSearchable('DID', "did", 120, "center");
$grid->AddModelNonSearchable('Total calls', "calls_count", 150, "center");
$grid->AddModelNonSearchable('Total duration<br>(h:m:s)', "total_duration", 150, "center");
$grid->AddModelNonSearchable('Avg duration<br>(h:m:s)', "avg_duration", 130,"center");
$grid->AddModelNonSearchable('Max duration<br>(h:m:s)', "max_duration", 130,"center");

include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
