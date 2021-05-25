<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url =  $this->url('task=get-tools-data&act=vcc-locations');
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 20;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	//$grid->hidecaption=true;
	$grid->CustomSearchOnTopGrid=false;
	$grid->multisearch=false;	
	$grid->IsCSVDownload=false;
	$grid->ShowDownloadButtonInTitle=false;
	$grid->ShowReloadButtonInTitle=true;
	$grid->AddModelNonSearchable("Location ID", "vcc_id", 80,"center");
	$grid->AddModelNonSearchable("Location Name", "name", 80,"center");	
	$grid->AddModelNonSearchable("Call Ratio", "call_ratio", 80,"center");
	$grid->AddModelNonSearchable("Effective Call Ratio", "effective_call_ratio", 80,"center");
	
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>