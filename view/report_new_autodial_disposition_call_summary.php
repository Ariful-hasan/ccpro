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
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;
    
    $grid->AddSearhProperty("Date", "sdate","date", "Y-m-d");
    $grid->SetDefaultValue("sdate", date("Y-m-d"),date("Y-m-d"));

    $grid->AddModelNonSearchable('Date', "sdate", 60, "center");
    $grid->AddModelCustomSearchable('Skill', "skill_id", 60, "center","select",$skills);
    $grid->AddModelNonSearchable('Disposition', "disposition_id", 60, "center");
    $grid->AddModelNonSearchable('Total', "call_count", 60, "center");

    $grid->show("#searchBtn");

?>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
</script>
