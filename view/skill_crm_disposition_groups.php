<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();

$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit=true;
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;

$grid->AddModelNonSearchable("Service Type", "title_link", 180, "center");
$grid->AddModelNonSearchable("Ticketing Status", "status", 120, "center");
$grid->AddModelNonSearchable("From Email", "from_email", 250, "center");
$grid->AddModelNonSearchable("To Email", "to_email", 320, "center");
$grid->AddModelNonSearchable("Use Email Module", "email_module", 125, "center");
$grid->AddModelNonSearchable("Action", "action", 60, "center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>