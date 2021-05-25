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

$grid->AddSearhProperty("Date", "tstamp", "datetime");
$grid->SetDefaultValue("tstamp", date("Y-m-d"));

$grid->AddModelNonSearchable('Agent ID', "agent_id", 80, "center");
$grid->AddModelNonSearchable('Nick name', "nick", 80, "center");
$grid->AddModelNonSearchable('Skill name', "skill_name", 100, "center");
$grid->AddModelNonSearchable('Calls <br>offered', "total_calls_offered", 60, "center");
$grid->AddModelNonSearchable('Calls <br>answered', "answered_calls", 60,"center");
$grid->AddModelNonSearchable('Missed <br>calls', "alarm", 60,"center");
$grid->AddModelNonSearchable('% of call <br>answered', "ans_percent", 60,"center");
$grid->AddModelNonSearchable('Talk time <br>(h:m:s)', "talk_time", 100,"center");

include('view/grid-tool-tips.php');

//$tooltips = $tooltips_override['agent_skill_report'] + $tooltips;
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
