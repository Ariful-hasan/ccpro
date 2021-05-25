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

//$grid->AddModel("Date", "tstamp", 80, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
//$grid->SetDefaultValue("tstamp", date("Y-m-d"));

$grid->AddSearhProperty("Date", "tstamp", "datetime");
$grid->SetDefaultValue("tstamp", date("Y-m-d"));

$grid->AddModelNonSearchable("Agent<br>ID", "agent_id", 80, "center");
$grid->AddModelNonSearchable("Nick<br>name", "agent_name", 80, "center");
//$grid->AddModelNonSearchable("Staffed<br>time<br>(h:m:s)", "", 80,"center");
$grid->AddModelNonSearchable("Attempted<br>calls", "attempted_calls", 80, "center");
$grid->AddModelNonSearchable("Reached<br>calls", "reached_calls", 80, "center");
$grid->AddModelNonSearchable("AHT<br>(h:m:s)", "avg_handling_time", 80,"center");
$grid->AddModelNonSearchable("Talk<br>time<br>(h:m:s)", "talk_time", 80, "center");
$grid->AddModelNonSearchable("ACW<br>time<br>(h:m:s)", "acw_time", 80, "center");
//$grid->AddModelNonSearchable("Break<br>time<br>(h:m:s)", "", 80,"center");
//$grid->AddModelNonSearchable("Available<br>time<br>(h:m:s)", "", 80, "center");
//$grid->AddModelNonSearchable("Utilization<br>(%)", "", 80, "center");
//$grid->AddModelNonSearchable("Occupancy<br>(%)", "", 80,"center");
$grid->AddModelNonSearchable("Success<br>rate<br>(%)", "success_rate", 80, "center");

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