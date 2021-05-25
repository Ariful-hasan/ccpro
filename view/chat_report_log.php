<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit=false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

$grid->AddSearhProperty("Disposition", "disposition_id", "select", $dp_options);
$grid->AddModel("Date Time", "tstamp", 115, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("tstamp", date("Y-m-d 00:00"));
$grid->AddModel("Name", "client_name", 150, "center");
$grid->AddModel("Email", "email", 150, "center");
$grid->AddModel("Contact Number", "contact_number", 120, "center");
$grid->AddModelNonSearchable("Service", "service_name", 90, "center");
// $grid->AddModelNonSearchable("URL", "url", 120, "center");
// $grid->AddModelNonSearchable("URL Duration", "url_duration", 70, "center");
$grid->AddModelNonSearchable("Agent", "agent_id", 60, "center");
$grid->AddModelNonSearchable("Disposition", "title", 100, "center");
$grid->AddModelNonSearchable("Call ID", "callid", 120, "center");
// $grid->AddModelNonSearchable("History", "history", 50, "center");
$grid->AddModelNonSearchable("Note", "note", 220, "left");

$grid->show("#searchBtn");
?>

<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
window.onbeforeunload = function(){};
</script>