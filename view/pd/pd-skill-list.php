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
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;
$grid->searchID = "unattended_cdr_search";
if (!empty($report_restriction_days)) {
    $grid->DateRange = $report_restriction_days;
}
$grid->floatingScrollBar = false;

$grid->AddModelNonSearchable("Skill Name", "skill_name", 100, "center");
$grid->AddModelNonSearchable("status", "status", 100, "center");
$grid->AddModelNonSearchable("Action", "action", 100, "center");


$grid->show("#searchBtn");

?>
<script>

    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });

    function AfterInsertRow(rowid, rowData, rowelem) {
        if(rowData.usertypemain=="S"){
            $('tr#' + rowid).addClass('bg-supervisor');
        }
    }

    $(document).ready(function () {
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
</script>



