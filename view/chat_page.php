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

$grid->AddModelNonSearchable("Page", "page_id_url", 100,"center");
$grid->AddModelNonSearchable("Domain", "www_domain", 100, "center");
$grid->AddModelNonSearchable("IP", "www_ip", 80, "left");
$grid->AddModelNonSearchable("Skill", "skill_name", 80, "center");
$grid->AddModelNonSearchable("Site Key", "site_key", 150, "left");
$grid->AddModelNonSearchable("Language", "language", 80,"center");
$grid->AddModelNonSearchable("Max Session", "max_session_per_agent", 80, "center");
$grid->AddModelNonSearchable("Status", "statusTxt", 80,"center");
$grid->AddModelNonSearchable("Action", "actUrl", 60,"center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>