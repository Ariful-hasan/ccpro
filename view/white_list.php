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
$grid->CustomSearchOnTopGrid=true;

$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName = $pageTitle;

$grid->ShowDownloadButtonInBottom=false;
$grid->multisearch=true;

$grid->AddSearhProperty("CLI", "cli","text");

$grid->AddModelNonSearchable("CLI", "cli", 80,"center");
$grid->AddModelNonSearchable("Grade", "grade", 80,"center");
$grid->AddModelNonSearchable("Category", "category", 80,"center");
$grid->AddModelNonSearchable("Action", "action", 80,"center");

$grid->show("#searchBtn");
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
</script>
