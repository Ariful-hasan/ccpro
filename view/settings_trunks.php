<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();

$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;

$grid->AddModelNonSearchable("Label", "trunk_name_url", 80, "center");
$grid->AddModelNonSearchable("IP", "ip", 80, "center");
$grid->AddModelNonSearchable("Capacity", "ch_capacity", 80, "center");
$grid->AddModelNonSearchable("Priority", "priority", 80, "center");
$grid->AddModelNonSearchable("Dial In Prefix", "dial_in_prefix", 80, "center");
$grid->AddModelNonSearchable("Dial Out Prefix", "dial_out_prefix", 80, "center");
$grid->AddModelNonSearchable("Register", "reg_status_txt", 80, "center");
$grid->AddModelNonSearchable("User", "user", 80, "center");
$grid->AddModelNonSearchable("Status", "statusTxt", 80, "center");
$grid->AddModelNonSearchable("Action", "act_links", 80, "center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>