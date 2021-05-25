<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

$grid->AddModel("SMS Send time", "tstamp", 125, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
//$grid->SetDefaultValue("start_time", $date_stime, $date_etime);
$grid->AddModelNonSearchable("Call ID", "callid", 125, "center");
$grid->AddModelCustomSearchable("Agent", "agent_id", 125, "center","select",$agents);
$grid->AddModelCustomSearchable("Skill", "skill_id", 125, "center","select",$skills);
$grid->AddModel("Destination Number", "dest_number", 100, "center");
$grid->AddModelNonSearchable("SMS Text", "sms_text", 200, "center");
$grid->AddModelNonSearchable("Template ID", "template_id", 80,"center");

$grid->ShowDownloadButtonInTitle = true;
$grid->show("#searchBtn");
?>	

<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>