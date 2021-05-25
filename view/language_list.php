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
	
	$grid->ShowReloadButtonInTitle=true;
	$grid->ShowDownloadButtonInTitle=true;
	$grid->DownloadFileName = $pageTitle;	
	
	$grid->ShowDownloadButtonInBottom=false;
	$grid->multisearch=false;	
	//$grid->AddTitleRightHtml('<a class="btn btn-xs btn-info" href="'."add-faq-item".'" ><i class="fa fa-plus"></i>Add New</a>');
	
	//$grid->AddModelNonSearchable("ID", "lang_key", 80,"center");
	$grid->AddModelNonSearchable("Title", "lang_title", 80,"center");
	$grid->AddModelNonSearchable("Status", "status", 80,"center");	
	$grid->AddModelNonSearchable("Action", "action", 80,"center");	
	
?>

    <?php //echo print_r($_SESSION);?>
<?php 
$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
