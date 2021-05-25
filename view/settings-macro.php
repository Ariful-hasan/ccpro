<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();

$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;

$grid->AddModelNonSearchable("Code", "code", 80, "center");
$grid->AddModelNonSearchable("Title", "title", 80, "center");
$grid->AddModelNonSearchable("Action", "act_links", 80, "center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
