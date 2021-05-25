<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 500;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;

$grid->AddSearhProperty("Date", "tstamp", "datetime");
$grid->SetDefaultValue("tstamp", date("Y-m-d"));

$grid->AddModelNonSearchable("Agent<br />ID", "agent_id", 80, "center");
$grid->AddModelNonSearchable("Nick<br />name", "agent_name", 80, "left");
$grid->AddModelNonSearchable("Staffed<br>time<br>(h:m:s)", "staffed_time", 80,"center");
$grid->AddModelNonSearchable("Offered<br>calls", "calls_offered", 80, "center");
$grid->AddModelNonSearchable("Ans.<br>calls", "answered_calls", 80, "center");
//$grid->AddModelNonSearchable("Service<br>level in<br>sec", "service_time_queue", 80,"center");
$grid->AddModelNonSearchable("Longest<br>call<br>duration<br>(h:m:s)", "longest_call", 80, "center");
$grid->AddModelNonSearchable("Shortest<br>call<br>duration<br>(h:m:s)", "shortest_call", 80, "left");
$grid->AddModelNonSearchable("AHT<br>(h:m:s)", "avg_handling_time", 80,"center");
$grid->AddModelNonSearchable("&lt;10<br>sec", "answered_lt10", 80, "center");
$grid->AddModelNonSearchable("10-20<br>sec", "answered_10to20", 80, "center");
$grid->AddModelNonSearchable("20-30<br>sec", "answered_20to30", 80,"center");
$grid->AddModelNonSearchable("30-60<br>sec", "answered_30to60", 80, "center");
$grid->AddModelNonSearchable("60-90<br>sec", "answered_60to90", 80, "center");
$grid->AddModelNonSearchable("90-120<br>sec", "answered_90to120", 80, "center");
$grid->AddModelNonSearchable("&gt;120<br>sec", "answered_gt120", 80, "center");

include('view/grid-tool-tips.php');
//$tooltips = $tooltips_override['skill_report_by_interval'] + $tooltips;
$grid->addModelTooltips($tooltips);
	
$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>