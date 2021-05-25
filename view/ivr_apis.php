<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url =  $this->url('task=get-tools-data&act=IvrApi');
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 20;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	//$grid->hidecaption=true;
	$grid->CustomSearchOnTopGrid=false;
	$grid->multisearch=false;	
	//$grid->AddTitleRightHtml('<a class="btn btn-xs btn-info" href="'."add-faq-item".'" ><i class="fa fa-plus"></i>Add New</a>');
	$grid->AddModelNonSearchable("Conn. Name", "conn_name", 80,"center");
	$grid->AddModelNonSearchable("Conn. Method", "conn_method", 80,"center");	
	$grid->AddModelNonSearchable("Pass Credential", "pass_credential", 80,"center");
	$grid->AddModelNonSearchable("Submit Method", "submit_method", 80,"center");
	$grid->AddModelNonSearchable("Submit Param", "submit_param", 80,"center");
	$grid->AddModelNonSearchable("Return Method", "return_method", 80,"center");
	$grid->AddModelNonSearchable("Return Param", "return_param", 80,"center");
	$grid->AddModelNonSearchable("Status", "active", 80,"center");
	$grid->AddModelNonSearchable("Action", "action", 80,"center");
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>