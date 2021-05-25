<script type="text/javascript" src="js/bootbox.common.js"></script>
<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->footerRow = false;
//$grid->hidecaption=false;
$grid->CustomSearchOnTopGrid = false;
$grid->multisearch = false;
$grid->ShowReloadButtonInTitle = true;
//$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;

$grid->floatingScrollBar = false;

$grid->AddModelNonSearchable("Settings ID", "settings_id", 100, "center");
$grid->AddModelNonSearchable("Type", "type", 100, "center");
$grid->AddModelNonSearchable("Settings Title", "title", 100, "center");
$grid->AddModelNonSearchable("Name", "name", 100, "center");
$grid->AddModelNonSearchable("Value", "value", 100, "center");
$grid->AddModelNonSearchable("Action", "action", 100, "center");

$grid->show("#searchBtn");
?>
<script>
    $(function () {
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
</script>
