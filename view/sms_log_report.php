<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();

$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->shrinkToFit = false;

$grid->AddSearhProperty("Date Time", "tstamp", "datetime");
$grid->SetDefaultValue("tstamp", date("Y-m-d 00:00"),date("Y-m-d 23:59"));

$grid->AddModelNonSearchable("Start Time", "start_time", 120,"center");
$grid->AddModelNonSearchable("Session ID", "session_id", 100, "center");
$grid->AddModelNonSearchable("Arrival Time", "last_in_msg_time", 120, "center");
$grid->AddModelNonSearchable("Served Time", "last_out_msg_time", 120, "center");
$grid->AddModelNonSearchable("Last Update Time", "last_update_time", 120, "center");
$grid->AddModelNonSearchable("Call ID", "callid", 120,"center");
$grid->AddModelNonSearchable("Disposition", "title", 150, "center");
$grid->AddModelNonSearchable("Agent ID", "agent_id", 80, "center");
$grid->AddModelNonSearchable("MSISDN", "phone_number", 80,"center");
$grid->AddModelNonSearchable("DID", "did", 80,"center");
$grid->AddModelNonSearchable("Skill", "skill_id", 80,"center");
$grid->AddModelNonSearchable("Wait Time", "wait_time", 80,"center");
$grid->AddModelNonSearchable("Status", "status_code", 50,"center");
$grid->AddModelNonSearchable("Action", "actUrl", 50,"center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });


</script>