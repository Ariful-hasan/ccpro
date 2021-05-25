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
    
   $grid->AddSearhProperty("Date", "dial_time","date", "Y-m-d");
    $grid->SetDefaultValue("dial_time", date("Y-m-d"),date("Y-m-d",strtotime("+ 30 days")));

    $grid->AddModelCustomSearchable('Date', "dial_time", 60, "center",'date');
    $grid->AddModelNonSearchable('Skill', "skill_id", 60, "center");
    $grid->AddModelNonSearchable('Agent', "agent_id", 60, "center");
    $grid->AddModelNonSearchable('Customer', "title", 60, "center");
    $grid->AddModelNonSearchable('Number 1', "number_1", 60, "center");
    $grid->AddModelNonSearchable('Number 2', "number_2", 60, "center");
    $grid->AddModelNonSearchable('Number 3', "number_3", 60, "center");

    $grid->show("#searchBtn"); ?>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
</script>

