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

$grid->AddSearhProperty("Users not logged in since", "sdate", "date");

$grid->AddModelNonSearchable("Agent ID", "agent_id", 80, "center");
$grid->AddModelNonSearchable("Nick name", "nick", 80, "center");
$grid->AddModelNonSearchable("Name", "name", 80, "center");
$grid->AddModelNonSearchable("User Type", "usertype", 80, "center");
$grid->AddModelNonSearchable("Status", "active", 80,"center");

include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);
    
$grid->show("#searchBtn");


?>
