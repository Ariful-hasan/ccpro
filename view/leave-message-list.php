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
	$grid->hidecaption=false;
	$grid->CustomSearchOnTopGrid=false;
	$grid->afterInsertRow="AfterInsertRow";
	$grid->ShowReloadButtonInTitle=true;
	$grid->ShowDownloadButtonInTitle=true;
	$grid->DownloadFileName = $pageTitle;	
	$grid->CustomSearchOnTopGrid=true;
	$grid->ShowDownloadButtonInBottom=false;
	$grid->multisearch=false;
	$grid->AddHiddenProperty("usertypemain");
	
	$grid->AddModel('Date', "created_at", 60,"center");
	$grid->AddModel('Name', "name", 35, "left");
	// $grid->AddModel('Email', "email", 50,"left");
	$grid->AddModel('Contact Number', "number", 30,"left");
	// $grid->AddModelNonSearchable('Service', "service", 25,"center");
	$grid->AddModelNonSearchable('Message', "msg", 120,"left");
	// $grid->AddModel('Customer Information', "customer_info", 80,"left");
	include('view/grid-tool-tips.php');
	$grid->addModelTooltips($tooltips);	
?>

<?php 	$grid->show("#searchBtn");?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);	
});


function AfterInsertRow(rowid, rowData, rowelem) {		 
	if(rowData.usertypemain=="S"){
		$('tr#' + rowid).addClass('bg-supervisor');    		
	}
} 
</script>
