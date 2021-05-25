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
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

//$grid->AddSearhProperty("Date Time", "start_time", "datetime");
//$grid->SetDefaultValue("start_time", date("Y-m-d 00:00"));
/*
if ($option == 'monthly'){
    $grid->AddSearhProperty("YYYY-MM", "sdate", "dateonly",'Y-m');//Select any date from date picker, only month will select
    $grid->SetDefaultValue("sdate", date("Y-m"));
} else {
    $grid->AddSearhProperty("YYYY-MM-DD", "sdate", "dateonly", "Y-m-d");
    $grid->SetDefaultValue("sdate", date("Y-m-d"));
}
*/
//$grid->AddModelNonSearchable('Agent ID', "agent_id", 80, "center");
//$grid->AddModelNonSearchable('Nick name', "agent_name", 80,"left");

$grid->AddModelNonSearchable('Campaign', "campaign_title", 80, "center");
$grid->AddModelNonSearchable('Total records', "num_records", 80, "center");
$grid->AddModelNonSearchable('Total dialed', "num_dialed", 80, "center");
$grid->AddModelNonSearchable('In progress', "num_progress", 80, "center");
$grid->AddModelNonSearchable('Yet to dial', "num_available", 80, "center");

include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");

?>
