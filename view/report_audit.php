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


//$grid->AddSearhProperty("User", "option", "select", $agent_options);
//$grid->AddModel("Join Date", "account_open_date", 120,"center","date","Y-m-d H:i:s","Y-m-d H:i:s",true,"date");
$grid->AddModel("Date time", "tstamp", 80, "center", "date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
//$grid->SetDefaultValue("tstamp", date("Y-m-d H:i"));
$grid->AddModel("User", "agent_id", 80, "center", "", "", "", true, "select", $agent_options);
$grid->AddModelNonSearchable("Action", "type", 80, "center");
$grid->AddModelNonSearchable("Section", "page", 80, "center");
$grid->AddModelNonSearchable("IP", "ip", 80,"center");
$grid->AddModelNonSearchable("Log", "log_text", 250,"left");

include('view/grid-tool-tips.php');
$tooltips = $tooltips_override['audit_log'] + $tooltips;
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");


?>

