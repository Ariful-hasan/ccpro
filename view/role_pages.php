<link rel="stylesheet" href="ccd/select2/select2.min.css">
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
$grid->CustomSearchOnTopGrid = true;
$grid->multisearch = true;
$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = false;
$grid->DownloadFileName = $pageTitle;
$grid->searchID = "unattended_cdr_search";
if (!empty($report_restriction_days)) {
    $grid->DateRange = $report_restriction_days;
}
$grid->floatingScrollBar = false;
$grid->AddSearhProperty("Page:", "page_id", "select", $pages);

$grid->AddModelNonSearchable('Page ID', "id", 120, "center");
$grid->AddModelNonSearchable('Page Name', "name", 150, "center");
$grid->AddModelNonSearchable('Path', "path", 150, "center");
$grid->AddModelNonSearchable('Ajax Path', "ajax_path", 150, "center");
$grid->AddModelNonSearchable('Action', "action", 120, "center");

?>

<?php $grid->show("#searchBtn"); ?>
<script defer src="ccd/select2/select2.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('#page_id').select2();
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });

</script>
