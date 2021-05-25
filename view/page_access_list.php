<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
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
$grid->ShowDownloadButtonInTitle = false;
$grid->DownloadFileName = $pageTitle;
$grid->searchID = "unattended_cdr_search";
if (!empty($report_restriction_days)) {
    $grid->DateRange = $report_restriction_days;
}
$grid->floatingScrollBar = false;

$grid->AddModelNonSearchable('Name', "name", 120, "center");
$grid->AddModelNonSearchable('Key Value', "key", 150, "center");
$grid->AddModelNonSearchable('Action', "action", 120, "center");

?>

<?php $grid->show("#searchBtn"); ?>
<script type="text/javascript">
    $(function () {
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });

</script>
