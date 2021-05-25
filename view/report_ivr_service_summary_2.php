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
$grid->AddModelNonSearchable('Disposition code', "disposition_code", 120, "center");
$grid->AddModelNonSearchable('Service title', "service_title", 120, "center");
$grid->AddModelNonSearchable('Request count', "service_count", 150, "center");


include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
