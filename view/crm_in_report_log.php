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
$grid->shrinkToFit=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;

$grid->AddModel("Date Time", "start_time", 125, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("start_time", date("Y-m-d 00:00"));
$grid->AddSearhProperty("Account Number", "account_no", "text");
$grid->AddModel("Template", "tempid", 80, "center", "", "", "", true, "select", $template_options);
$grid->AddSearhProperty("Contact Number", "contact_no", "text");
$grid->AddModel("Disposition", "did", 80, "center", "", "", "", true, "select", $dp_options);

$grid->AddModelNonSearchable("Contact Number", "contact_id", 80, "center");
$grid->AddModelNonSearchable("Account Number", "account_id", 80, "center");
$grid->AddModelNonSearchable("Agent", "agent_id", 80, "center");
$grid->AddModelNonSearchable("Auth by", "auth_by", 80, "center");
$grid->AddModelNonSearchable("Note", "note", 80, "center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>