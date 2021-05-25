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
$grid->footerRow = true;
$grid->userDataOnFooter = true;



$grid->AddModel("Date", "cdate", 80, "center", ".date", "Y-m-d", "Y-m-d", true, "date");
$grid->SetDefaultValue("cdate", date("Y-m-d"));

$grid->AddModelNonSearchable("Skill", "skill_name", 80,"center");
//$grid->AddModelNonSearchable("Calls answered", "num_ans", 80, "center");
$grid->AddModelNonSearchable("Calls offered", "num_calls", 80, "center");
$grid->AddModelNonSearchable("Calls ans", "num_ans", 80, "center");
$grid->AddModelNonSearchable("Ans within service level", "ans_within_sl", 130,"center");
$grid->AddModelNonSearchable("Abd calls aft threshold*", "num_abdns_after_th", 130,"center");
$grid->AddModelNonSearchable("Service level (secs)", "service_level", 120,"center");
$grid->AddModelNonSearchable("% within service level**", "level_ratio", 120,"center");

include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");
//echo '<small style="font-size:80%;">* Threshold time is 20 second</small><br />';
if ($sl_method == 'A') echo '<small style="font-size:80%;">** % within service level = ( Ans. within service Level / Calls offered ) x 100</small>';
else if ($sl_method == 'B') echo '<small style="font-size:80%;">** % within service level = ( Ans. within service Level / ( Calls answered + Abd. calls aft. threshold ) ) x 100</small>';
?>
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>