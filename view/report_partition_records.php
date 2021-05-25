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
    $grid->multisearch=FALSE;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;

    $grid->AddModelNonSearchable('Record ID', "record_id", 60, "center");
    $grid->AddModelNonSearchable('Partition', "partition_id", 60, "center");
    $grid->AddModelNonSearchable('Type', "type", 60, "center");
    $grid->AddModelNonSearchable('Action', "action", 60, "center");


    $grid->show("#searchBtn");

