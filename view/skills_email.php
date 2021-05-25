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

$grid->AddModelNonSearchable("Name", "skill_name", 80, "center");
$grid->AddModelNonSearchable("Dispositions", "disposition", 80, "center");
$grid->AddModelNonSearchable("Auto Reply", "autyReply", 80,"center");
$grid->AddModelNonSearchable("Signature", "signature", 80,"center");
$grid->AddModelNonSearchable("Status", "status", 80,"center");
$grid->AddModelNonSearchable("Domain", "domain", 80, "center");
$grid->AddModelNonSearchable("Keyword", "keyword", 80, "center");
$grid->AddModelNonSearchable("In KPI", "inkpi", 80, "center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>