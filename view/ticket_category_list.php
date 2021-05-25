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
$grid->shrinkToFit=true;

//RND
//$grid->colModel['name']="comments";
$grid->ShowDownloadButtonInTitle=true;

$grid->AddModelNonSearchable("Title", "titleUrl", 100,'center');
$grid->AddModelCustomSearchable("Status", "status", 100, 'center','select',array("*"=>"All","A"=>"Active", "I"=>"Inactive"));
$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>

<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
</script>