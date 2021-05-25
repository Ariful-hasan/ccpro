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

$grid->AddModel("Skill", "skillid", 80, "center", "", "", "", true, "select", $skill_options);
$grid->AddSearhProperty("Date", "create_time", "datetime");
$grid->AddModel("Status", "sid", 80, "center", "", "", "", true, "select", $st_options);
$grid->AddModelNonSearchable("Record Count", "num_tickets", 80, "center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>