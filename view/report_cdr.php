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


$grid->AddSearhProperty("Option", "option", "select", $select_options);
$grid->AddSearhProperty("Search Text", "stext");
//$grid->AddModel("Join Date", "account_open_date", 120,"center","date","Y-m-d H:i:s","Y-m-d H:i:s",true,"date");
$grid->AddModel("Start time", "start_time", 120, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->AddModelNonSearchable("Answer time", "answer_time", 120, "center");
$grid->AddModelNonSearchable("Stop time", "stop_time", 120, "center");
$grid->AddModelNonSearchable($cliColumn, "cli", 90, "center");
$grid->AddModelNonSearchable($didColumn, "did", 90,"center");
$grid->AddModelNonSearchable("Duration", "duration", 75,"center");
$grid->AddModelNonSearchable("Talk time", "talk_time", 75,"center");
$grid->AddModelNonSearchable("Trunk", "trunkid", 75,"center");
$grid->AddModelNonSearchable("Disc. cause", "disc_cause", 75,"center");
$grid->AddModelNonSearchable("Disc. party", "disc_party", 80,"center");


$grid->show("#searchBtn");


?>

