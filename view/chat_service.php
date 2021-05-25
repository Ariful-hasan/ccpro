<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;

$grid->AddModelNonSearchable("Service Name", "service_name_url", 180, "center");
$grid->AddModelNonSearchable("Page", "page_id", 180,"center");
$grid->AddModelNonSearchable("Skill", "skill_name", 80,"center");
$grid->AddModelNonSearchable("Status", "statusTxt", 180,"center");
$grid->AddModelNonSearchable("Action", "actUrl", 180,"center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>