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
	
	$grid->AddModelNonSearchable("Title", "edit_link", 80,"center");
	$grid->AddModelNonSearchable("Disposition Code", "disposition_code", 80,"center");
	$grid->AddModelNonSearchable("Type", "service_type", 80,"center");
	$grid->AddModelNonSearchable("Parent", "parent_id", 80,"center");
	$grid->AddModelNonSearchable("Report Category", "report_category", 80,"center");
	$grid->AddModelNonSearchable("Report Type", "report_type", 80,"center");

	$grid->AddModelNonSearchable("Action", "act_links", 80,"center");
	
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>