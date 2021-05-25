<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=true;

$grid->AddModelNonSearchable("Date", "sdate", 80, "center");
$grid->AddModelNonSearchable("No. of calls taken", "calls_taken", 80,"center");
$grid->AddModelNonSearchable("Talk time", "t_time", 80,"center");

$grid->AddModelNonSearchable("Working hours", "w_hour", 80,"center");
$grid->AddModelNonSearchable("Busy count", "b_count", 80,"center");
$grid->AddModelNonSearchable("Busy time", "b_time", 80,"center");
$grid->AddModelNonSearchable("Alarm", "alarm", 80,"center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>