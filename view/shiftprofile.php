<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

//$grid->AddSearhProperty("Overlap", "day_overlap","select", $overlap);
//$grid->AddSearhProperty("Hour", "shour","select", $hour);
//$grid->SetDefaultValue("sdate", date("Y-m-d"),date("Y-m-d"));

$grid->AddModel('Shift Code', "shift_code", 100, "center");
$grid->AddModelNonSearchable('Label', "label", 80, "center");
$grid->AddModelNonSearchable('Start Time', "start_time", 80, "center");
$grid->AddModelNonSearchable('End Time', "end_time", 80, "center");
$grid->AddModelNonSearchable('Allowed Early login time', "early_login_cutoff_time", 80, "center");
$grid->AddModelNonSearchable('Allowed Late login time', "late_login_cutoff_time", 80, "center");
$grid->AddModelNonSearchable('Duration', "shift_duration", 80, "center");
$grid->AddModelNonSearchable('Late Login Mark', "tardy_cutoff_sec", 80, "center");
$grid->AddModelNonSearchable('Early Logout Mark', "early_leave_cutoff_sec", 80, "center");
$grid->AddModelNonSearchable('Overlap', "day_overlap", 80, "center");


$grid->show("#searchBtn");

