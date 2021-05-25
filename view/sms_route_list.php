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


    
    $grid->AddModelNonSearchable('Gateway', "gw_id", 70, "center");
    $grid->AddModelCustomSearchable('Prefix', "prefix", 70, "center");
    $grid->AddModelCustomSearchable('Status', "status", 70, "center");
    $grid->AddModelNonSearchable('Action', "action", 60, "center");



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

