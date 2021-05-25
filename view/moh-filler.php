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
	$grid->AddModelNonSearchable("Skill", "skill_id", 80,"center");
	$grid->AddModelNonSearchable("Filler Type", "filler_type", 80,"center");
	$grid->AddModelNonSearchable("Filler ID", "filler_id", 80,"center");
	$grid->AddModelNonSearchable("Interval", "filler_interval", 80,"center");
	$grid->AddModelNonSearchable("Loop Count", "loop_count", 80,"center");
	$grid->AddModelNonSearchable("Event", "event", 80,"center");
	$grid->AddModelNonSearchable("File", "file_id", 80,"center");
	$grid->AddModelNonSearchable("Action", "action", 80,"center");
	
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>