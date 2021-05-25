<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url = isset($dataUrl) ? $dataUrl : "";
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 100;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	//$grid->hidecaption=true;
	$grid->CustomSearchOnTopGrid=false;
	$grid->multisearch=false;	

	
	$grid->AddModelNonSearchable("Name", "cti_name", 80,"center");
	$grid->AddModelNonSearchable("DID", "cti_did", 80,"center");
//	$grid->AddModelNonSearchable("Interface", "cti_type", 80,"center");
	$grid->AddModelNonSearchable("Action type", "actionType", 80,"center");
	$grid->AddModelNonSearchable("Description", "description", 80,"center");
	$grid->AddModelNonSearchable("Status", "active", 80,"center");
	//$grid->AddModelNonSearchable("Priority", "priority", 80,"center");
	//$grid->AddModelNonSearchable("CLI length", "cli_length", 80,"center");
	//$grid->AddModelNonSearchable("CLI padding prefix", "cli_padding_prefix", 100,"center");
	
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>