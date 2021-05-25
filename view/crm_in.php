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
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;

if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
	$grid->AddTitleRightHtml('<a data-w="500px"  data-h="500px" class="lightboxWIFR btn btn-xs btn-info" href="'.$this->url('task='.$request->getControllerName()."&act=upload").'" ><i class="fa fa-upload"></i>Upload</a>');
}

$grid->AddModel("Account Number", "account_id", 80, "center");
$grid->AddModel("First Name", "first_name", 80, "center");
$grid->AddModel("Last Name", "last_name", 80, "center");

$grid->AddSearhProperty(" Disp. Code", "dcode", "select", $dp_options);
$grid->AddModelNonSearchable("Disposition", "disposition", 80, "center");
$grid->AddModelNonSearchable("Agent", "agent_id", 80, "center");
$grid->AddModel("Time", "tstamp", 80, "center", ".date", "Y-m-d", "Y-m-d", true, "date");
$grid->AddModelNonSearchable("Note", "", 80,"center");
$grid->AddModelNonSearchable("History", "disposUrl", 80,"center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
