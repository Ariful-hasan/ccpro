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



$grid->AddModelNonSearchable("Title", "titleUrl", 80, "center");
$grid->AddModelNonSearchable("Disposition", "disposition", 200, "left");
$grid->AddModelNonSearchable("Status", "status", 80,"center");
if (!UserAuth::hasRole('agent')){
    $grid->AddModelNonSearchable("Action", "actUrl", 80,"center");
}

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>