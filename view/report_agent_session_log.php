<?php 
    include_once "lib/jqgrid.php";
    $grid = new jQGrid();
    //$grid->caption = "Agent List";
    //echo "2013-08-01 10:02 to: 2013-09-30 10:02";
    $grid->url = isset($dataUrl) ? $dataUrl : "";
    $grid->width="auto";//$grid->minWidth = 800;
    $grid->height = "auto";//390;
    $grid->rowNum = 20;
    $grid->pager = "#pagerb";
    $grid->container = ".content-body";
    //$grid->hidecaption=false;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;
    
    $grid->AddSearhProperty("YYYY-MM-DD", "sdate","dateonly", "Y-m-d");
    $grid->SetDefaultValue("sdate", date("Y-m-d"));
    
    $grid->AddModelNonSearchable('Agent ID', "agent_id", 80, "center");
    $grid->AddModelNonSearchable('Nick name', "agent_name", 80,"left");
    
    $grid->AddModelNonSearchable('First login', "agentinfo", 100, "center");
    $grid->AddModelNonSearchable('Last logout', "logout", 100, "center");
    $grid->AddModelNonSearchable('Session', "s_count", 60,"center");
    $grid->AddModelNonSearchable('Working time', "staffed_time", 80,"center");
    $grid->AddModelNonSearchable('ACW count', "acw_count", 60,"center");
    $grid->AddModelNonSearchable('ACW time', "acw_time", 80,"center");
    $grid->AddModelNonSearchable('Busy count', "pause_count", 60,"center");
    $grid->AddModelNonSearchable('Busy time', "pause_time", 80,"center");    
    $grid->AddModelNonSearchable('Missed calls', "missed_count", 60,"center");
    $grid->AddModelNonSearchable('Missed time', "missed_time", 80,"center");
    
    include('view/grid-tool-tips.php');
    $grid->addModelTooltips($tooltips);
    
    $grid->show("#searchBtn");

?>
