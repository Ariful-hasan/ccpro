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

if ($option == 'monthly'){
    $grid->AddSearhProperty("YYYY-MM", "sdate", "dateonly",'Y-m');//Select any date from date picker, only month will select
    $grid->SetDefaultValue("sdate", date("Y-m"));
} else {
    $grid->AddSearhProperty("From Date", "sdate", "dateonly", "Y-m-d");
    $grid->AddSearhProperty("To Date", "edate", "dateonly", "Y-m-d");
    $grid->SetDefaultValue("sdate", date("Y-m-d"));
}

$grid->AddModelNonSearchable('Agent ID', "agent_id", 80, "center");
$grid->AddModelNonSearchable('Nick name', "agent_name", 80,"left");
$grid->AddModelNonSearchable('Calls taken', "answered_calls", 80, "center");
$grid->AddModelNonSearchable('Talk time', "talk_time", 80, "center");
$grid->AddModelNonSearchable('Working hours', "whour", 80,"center");
$grid->AddModelNonSearchable('ACW count', "acw_count", 80,"center");
$grid->AddModelNonSearchable('ACW time', "acw_time", 80,"center");
$grid->AddModelNonSearchable('Busy count', "bcount", 80,"center");
$grid->AddModelNonSearchable('Busy time', "btime", 80,"center");
//$grid->AddModelNonSearchable('<span title="">Alarm", "alarm", 80,"center");
$grid->AddModelNonSearchable('Missed calls', "missed_count", 80,"center");
$grid->AddModelNonSearchable('Missed time', "missed_time", 80,"center");

include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");

?>
