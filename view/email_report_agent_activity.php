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

$grid->AddSearhProperty("Agent", "agentid", "select", $agent_options);
$grid->AddSearhProperty("Date", "create_time", "datetime");

$grid->AddModelNonSearchable("Agent", "agent_name", 80, "center");
$grid->AddModelNonSearchable("View", "num_views", 80, "center");
$grid->AddModelNonSearchable("Assign", "num_assigns", 80, "center");
$grid->AddModelNonSearchable("Response", "num_responses", 80, "center");
$grid->AddModelNonSearchable("Disposition", "num_dispositions", 80, "center");
$grid->AddModelNonSearchable("Status", "num_statuses", 80, "center");
$grid->AddModelNonSearchable("Total count", "num_records", 80, "center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>