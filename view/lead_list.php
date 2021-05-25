<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 50;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;

$grid->AddModelNonSearchable("Lead", "lead_id_txt", 80, "center");
$grid->AddModel("Title", "title", 80, "center");
$grid->AddModelNonSearchable("Modify Date", "modify_date", 80, "center");
$grid->AddModelNonSearchable("Number(s)", "number_count_txt", 80, "center");
$grid->AddModelNonSearchable("Reference", "reference", 80, "center");
$grid->AddModelNonSearchable("Country Code", "country_code", 60,"center");
$grid->AddModelNonSearchable("Action", "action", 60,"center");
$grid->AddModelNonSearchable("", "batchdl", 40,"center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
	$(function(){
		AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
	});
</script>