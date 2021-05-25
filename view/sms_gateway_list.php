<?php 
    include_once "lib/jqgrid.php";
    $grid = new jQGrid();
    $grid->url = isset($dataUrl) ? $dataUrl : "";
    $grid->width="auto";//$grid->minWidth = 800;
    $grid->height = "auto";//390;
    $grid->rowNum = 20;
    $grid->pager = "#pagerb";
    $grid->container = ".content-body";
    $grid->shrinkToFit = TRUE;
    $grid->footerRow = false;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=false;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;


    
    $grid->AddModelNonSearchable('Gateway', "gw_id", 50, "center");
    $grid->AddModelNonSearchable('Route', "route", 50, "center");
    $grid->AddModelCustomSearchable('API', "api", 100, "center");
    $grid->AddModelNonSearchable('Status', "status", 50, "center");
    $grid->AddModelCustomSearchable('Action', "action", 50, "center");



    $grid->show("#searchBtn");

    ?>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
    function reloadSite() {
        location.reload(true);
    }
</script>

