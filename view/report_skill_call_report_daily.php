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
$grid->shrinkToFit = false;
//$grid->loadComplete="onGridLoad";

$grid->AddSearhProperty("Date Time", "start_time", "datetime");
$grid->SetDefaultValue("start_time", date("Y-m-d 00:00"));
$grid->AddSearhProperty("Skill", "skillid", "select", $skill_options);
$grid->AddSearhProperty("Language", "lang_id", "select", $lang_options);

$grid->AddModelNonSearchable('Date', "sdate", 100, "center");
$grid->AddModelNonSearchable('Skill', "skill_name", 80, "center");
$grid->AddModelNonSearchable('Language', "language", 70, "center");
$grid->AddModelNonSearchable("% within </br>service level**", "percent_within_sl", 100,"center");
$grid->AddModelNonSearchable("% aban calls<br>(aft threshold*)", "percent_abd_calls", 100,"center");

$grid->AddModelNonSearchable('Calls <br>offered', "calls_offered", 65, "center");
$grid->AddModelNonSearchable("Avg hold in queue<br>(h:m:s)", "avg_hold_in_q", 100,"center");
$grid->AddModelNonSearchable('Calls <br>answered', "calls_answerd", 65,"center");
$grid->AddModelNonSearchable("Avg handling time <br>(h:m:s)", "avg_handling_time", 120,"center");

//$grid->AddModelNonSearchable('Answered<br>within<br>service level', "answerd_within_service_level", 100,"center");
//$grid->AddModelNonSearchable('Calls <br>abandoned', "calls_abandoned", 75,"center");
$grid->AddModelNonSearchable('Aban calls<br>(aft threshold*)', "abandoned_after_threshold", 100,"center");

$grid->AddModelNonSearchable("Avg aban time<br>(h:m:s)", "avg_abd_time", 140,"center");

//$grid->AddModelNonSearchable('Abandoned<br>calls<br>duration', "abandon_duration", 80,"center");
//$grid->AddModelNonSearchable('Calls <br>abandoned<br>after threshold', "abandoned_after_threshold", 100,"center");
//$grid->AddModelNonSearchable('Service<br>duration', "service_duration", 70,"center");
//$grid->AddModelNonSearchable('Hold time<br>in queue', "hold_time_in_queue", 70, "center");
$grid->AddModelNonSearchable('Max hold<br>in queue<br>(h:m:s)', "max_hold_time_in_queue", 90,"center");
//$grid->AddModelNonSearchable('Calls <br>xfar out', "calls_xfar_out", 80,"center");
//$grid->AddModelNonSearchable('Calls <br>xfar in', "calls_xfar_in", 80,"center");

include('view/grid-tool-tips.php');
$tooltips = $tooltips_override['skill_report_by_interval'] + $tooltips;
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");
echo '<small style="font-size:80%;">* Threshold time is 20 second</small><br>';
if ($sl_method == 'A') echo '<small style="font-size:80%;">** % within service level = ( Ans. within service Level / Calls offered ) x 100</small>';
else if ($sl_method == 'B') echo '<small style="font-size:80%;">** % within service level = ( Ans. within service Level / ( Calls answered + Abd. calls aft. threshold ) ) x 100</small>';

?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
