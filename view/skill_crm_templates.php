<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url =  $this->url('task=get-tools-data&act=STemplate');
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 20;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	//$grid->hidecaption=true;
	$grid->CustomSearchOnTopGrid=false;
	$grid->multisearch=false;	
	//$grid->AddTitleRightHtml('<a class="btn btn-xs btn-info" href="'."add-faq-item".'" ><i class="fa fa-plus"></i>Add New</a>');
	$grid->AddModelNonSearchable("Template ID", "template_id", 80,"center");
	$grid->AddModelNonSearchable("Title", "title", 80,"center");
	$grid->AddModelNonSearchable("Design", "design", 80,"center");
	$grid->AddModelNonSearchable("Disposition Code", "disposition", 80,"center");
	$grid->AddModelNonSearchable("Service Type", "type", 80,"center");
	$grid->AddModelNonSearchable("Action", "action", 80,"center");
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>