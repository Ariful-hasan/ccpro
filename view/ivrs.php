<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url = isset($dataUrl) ? $dataUrl : "";
	$grid->width="auto";
	$grid->height = "auto";
	$grid->rowNum = 100;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	//$grid->hidecaption=true;
	$grid->CustomSearchOnTopGrid=false;
	$grid->multisearch=false;	
	
	$grid->AddModelNonSearchable("Name", "ivr_name", 80,"center");
	$grid->AddModelNonSearchable("DTMF timeout (sec)", "dtmf_timeout", 80,"center");
	$grid->AddModelNonSearchable("IVR timeout (sec)", "ivr_timeout", 80,"center");
	//$grid->AddModelNonSearchable("Language", "languages", 80,"center");
	$grid->AddModelNonSearchable("Action", "editLink", 80,"center");
	
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>