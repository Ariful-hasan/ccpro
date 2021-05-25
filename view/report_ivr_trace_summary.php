<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;


$grid->AddSearhProperty("Date", "sdate","date", REPORT_DATE_FORMAT);
$grid->SetDefaultValue("sdate", date(REPORT_DATE_FORMAT),date(REPORT_DATE_FORMAT));
$grid->AddSearhProperty("Service", "trace_id", "select", $dp_options);

$grid->AddModel("Service", "trace_id", 80, "center");
$grid->AddModelNonSearchable("UCID", "callid_cti", 80, "center");
$grid->AddModelNonSearchable("ANI", "callid", 80, "center");
$grid->AddModelNonSearchable("Call Date", "call_date", 80, "center");
$grid->AddModelNonSearchable("Start Time", "start_time", 80,"center");
$grid->AddModelNonSearchable("End Time", "end_time", 80,"center");
$grid->AddModelNonSearchable("Traversal", "traversal", 80,"center");
$grid->AddModelNonSearchable("Time On IVR (Sec)", "sum_time_in_ivr", 80,"center");


$grid->show("#searchBtn");
?>

