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
$grid->DownloadFileName=$pageTitle;
$grid->shrinkToFit = false;

$grid->AddSearhProperty("Date", "tstamp", "datetime");
$grid->SetDefaultValue("tstamp", date("Y-m-d"));

$grid->AddModelNonSearchable("Date", "cdate", 80,"center");
$grid->AddModelNonSearchable("Skill", "skill_name", 80,"center");

$grid->AddModelNonSearchable("Calls offered", "num_calls", 80,"center");
$grid->AddModelNonSearchable("Calls ans", "num_ans", 80,"center");
$grid->AddModelNonSearchable("Aban calls<br>(aft threshold*)", "num_abdns_after_th", 80,"center");
$grid->AddModelNonSearchable("Aban calls<br>(bfr threshold*)", "num_abdns_b4_th", 80,"center");
$grid->AddModelNonSearchable("% within <br>service level**", "percent_within_sl", 80, "center");
$grid->AddModelNonSearchable("% aban calls<br>(aft threshold*)", "percent_abd_calls", 120, "center");
$grid->AddModelNonSearchable("Total talk time<br>(h:m:s)", "talktime", 100,"center");
$grid->AddModelNonSearchable("Avg speed ans<br>(h:m:s)", "speed_of_ans", 100,"center");
$grid->AddModelNonSearchable("Avg handling time<br>(h:m:s)", "avg_handling_time", 100,"center");
//$grid->AddModelNonSearchable("Aban calls<br>(aft threshold)", "num_abdns_after_th", 120,"center");
$grid->AddModelNonSearchable("Avg aban time<br>(h:m:s)", "avg_abd_time", 100,"center");
//$grid->AddModelNonSearchable("Extn out calls", "num_extn", 120,"center");
//$grid->AddModelNonSearchable("Avg extn out time<br>(h:m:s)", "avg_extn_time", 140,"center");

include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");
/*echo '<small style="font-size:80%;">* Threshold time is 20 second</small><br />';*/
if ($sl_method == 'A') echo '<small style="font-size:80%;">** % within service level = ( Ans. within service Level / Calls offered ) x 100</small>';
else if ($sl_method == 'B') echo '<small style="font-size:80%;">** % within service level = ( Ans. within service Level / ( Calls answered + Abd. calls aft. threshold ) ) x 100</small>';
?>
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
