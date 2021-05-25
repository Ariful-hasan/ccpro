<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = false;
$grid->footerRow = TRUE;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

//$grid->AddSearhProperty("Date", "sdate","date", "Y-m-d");
//$grid->AddSearhProperty("Hour", "shour","select", $hour);
$grid->AddModel("Date", "sdate", 100, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->AddSearhProperty("Skill", "skill_id","select", $skills);
$grid->SetDefaultValue("sdate", date("Y-m-d"),date("Y-m-d"));

$grid->AddSearhProperty("Avg Condition", "avg_con","select", array("CO"=>"Calls Offered", "CA"=>"Calls Answered"));

//$grid->AddModelNonSearchable('Date', "sdate", 100, "center");
$grid->AddModelNonSearchable('Skill', "skill_id", 100, "center");

/*for ($i=0; $i<24; $i++){
    $x = $i < 10 ? "0".$i : $i;
    $str = $x > 11 ? "PM" : "AM";
    $grid->AddModelNonSearchable($x.':00 '.$str, "shour_".$x, 80,'center');
}*/


$grid->AddModelNonSearchable('00:00 AM', "shour_00", 80, "center");
$grid->AddModelNonSearchable('1:00 AM', "shour_01", 80, "center");
$grid->AddModelNonSearchable('2:00 AM', "shour_02", 80, "center");
$grid->AddModelNonSearchable('3:00 AM', "shour_03", 80, "center");
$grid->AddModelNonSearchable('4:00 AM', "shour_04", 80, "center");
$grid->AddModelNonSearchable('5:00 AM', "shour_05", 80, "center");
$grid->AddModelNonSearchable('6:00 AM', "shour_06", 80, "center");

$grid->AddModelNonSearchable('7:00 AM', "shour_07", 80, "center");
$grid->AddModelNonSearchable('8:00 AM', "shour_08", 80, "center");
$grid->AddModelNonSearchable('9:00 AM', "shour_09", 80, "center");
$grid->AddModelNonSearchable('10:00 AM', "shour_10", 80, "center");
$grid->AddModelNonSearchable('11:00 AM', "shour_11", 80, "center");
$grid->AddModelNonSearchable('12:00 PM', "shour_12", 80, "center");
$grid->AddModelNonSearchable('1:00 PM', "shour_13", 80, "center");
$grid->AddModelNonSearchable('2:00 PM', "shour_14", 80, "center");
$grid->AddModelNonSearchable('3:00 PM', "shour_15", 80, "center");
$grid->AddModelNonSearchable('4:00 PM', "shour_16", 80, "center");
$grid->AddModelNonSearchable('5:00 PM', "shour_17", 80, "center");
$grid->AddModelNonSearchable('6:00 PM', "shour_18", 80, "center");
$grid->AddModelNonSearchable('7:00 PM', "shour_19", 80, "center");
$grid->AddModelNonSearchable('8:00 PM', "shour_20", 80, "center");
$grid->AddModelNonSearchable('9:00 PM', "shour_21", 80, "center");
$grid->AddModelNonSearchable('10:00 PM', "shour_22", 80, "center");
$grid->AddModelNonSearchable('11:00 PM', "shour_23", 80, "center");

//$grid->AddModelNonSearchable('AVG Time', "avg_time", 80, "center");



$grid->show("#searchBtn"); ?>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
</script>

