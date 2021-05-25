<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url = isset($dataUrl) ? $dataUrl : "";
	$grid->width="auto";
	$grid->height = "auto";
	$grid->rowNum = 50;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	//$grid->hidecaption=true;
	$grid->CustomSearchOnTopGrid=true;
	$grid->multisearch=true;
	
	$grid->AddSearhProperty("Skill", "skillid", "select", $skill_options);
	
	$grid->AddModel("Title", "title", 80, "center");
	$grid->AddModelNonSearchable("Skill", "skill_name", 80,"center");
	//$grid->AddModelNonSearchable("Skill", "skill_text", 80,"center");
	$grid->AddModelNonSearchable("IVR Branch", "ivr_branch", 80,"center");	
	$grid->AddModelNonSearchable("Verified Call Only", "verified_call_txt", 80,"center");
	$grid->AddModelNonSearchable("Status", "active_txt", 80,"center");
	$grid->AddModelNonSearchable("Action", "actUrl", 80,"center");
	
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>