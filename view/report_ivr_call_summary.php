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
$grid->shrinkToFit=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;


$grid->AddModelCustomSearchable('Date', "sdate", 100, "center","report-datetime");
$grid->SetDefaultValue("sdate", date(REPORT_DATE_FORMAT." H:00"),date(REPORT_DATE_FORMAT." 23:59"));

$grid->AddSearhProperty("Type", "report_type",'select', $report_type_list);
$grid->SetDefaultValue("report_type", $report_type);


$grid->AddModelNonSearchable('Hour', "shour", 100,"center");
$grid->AddModelNonSearchable('Year', "syear", 100,"center");
$grid->AddModelNonSearchable('Month', "smonth", 100,"center");
//$grid->AddModelNonSearchable('Hour', "shour", 70,"center");
//$grid->AddModelNonSearchable('Minute', "sminute", 70,"center");
$grid->AddModelNonSearchable('Quarter No.', "quarter_no", 100,"center");


$grid->AddModelCustomSearchable('IVR', "ivr_id", 100,"center",'select',$ivrNames);
$grid->SetDefaultValue("ivr_id", $ivr_id);

$grid->AddModelNonSearchable("Call Offered", "calls_count", 100, "center");
$grid->AddModelNonSearchable("Calls Answered", "calls_ans_count", 100, "center");
$grid->AddModelNonSearchable("Avg Time in IVR (sec)", "avg_ivr_time", 100,"center");


$grid->show("#searchBtn");
?>

