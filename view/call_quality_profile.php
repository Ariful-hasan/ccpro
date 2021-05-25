<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	$grid->url = isset($dataUrl) ? $dataUrl : "";
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 20;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	$grid->hidecaption=false;
	$grid->CustomSearchOnTopGrid=false;
	//$grid->afterInsertRow="AfterInsertRow";
	$grid->ShowReloadButtonInTitle=true;
	$grid->ShowDownloadButtonInTitle=true;
	$grid->DownloadFileName = $pageTitle;	
	$grid->CustomSearchOnTopGrid=true;
	$grid->ShowDownloadButtonInBottom=false;
	$grid->multisearch=false;
	
	$grid->AddModelNonSearchable('ID', "rating_id", 80,"center");
	$grid->AddModelNonSearchable('Title', "label", 80,"center");
	$grid->AddModelNonSearchable('Status', "status", 80,"center");
	$grid->AddModelNonSearchable('Action', "action", 80,"center");


?>

<?php 	$grid->show("#searchBtn");?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    $(document).on("click","#cboxClose",function () {
        location.reload(true);
    });
});

</script>
