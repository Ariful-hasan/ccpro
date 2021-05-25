<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 50;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;

$grid->AddModelNonSearchable("Campaign ID", "campaign_id_url", 80, "center");
$grid->AddModel("Title", "title", 80, "center");
$grid->AddModelNonSearchable("Dial engine", "dial_engine", 80, "center");
$grid->AddModelNonSearchable("Voice Mail", "VMTxt", 100, "center");
$grid->AddModelNonSearchable("Caller ID", "cli", 100, "center");
$grid->AddModelNonSearchable("Retry count", "retry_count", 80, "center");
//$grid->AddModelNonSearchable("Retry interval", "retry_interval", 80, "center");
$grid->AddModelNonSearchable("Retry interval<br>VM (min)", "retry_interval_vm", 80, "center");
//$grid->AddModelNonSearchable("MAX call(s)", "max_out_bound_calls", 60,"center");
$grid->AddModelNonSearchable("Pacing ratio", "max_pacing_ratio", 70,"center");
//$grid->AddModelNonSearchable("Drop ratio", "max_drop_ratio", 80, "center");
$grid->AddModelNonSearchable("Status", "statusTxt", 60,"center");
$grid->AddModelNonSearchable("Start time", "startTimeTxt", 60,"center");
$grid->AddModelNonSearchable("Stop time", "stopTimeTxt", 60,"center");
$grid->AddModelNonSearchable("Action", "action", 60,"center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
