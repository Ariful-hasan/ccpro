<script type="text/javascript" src="js/bootbox.common.js"></script>
<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->footerRow = false;
//$grid->hidecaption=false;
$grid->CustomSearchOnTopGrid = true;
$grid->multisearch = true;
$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;
$grid->searchID = "unattended_cdr_search";
if (!empty($report_restriction_days)) {
    $grid->DateRange = $report_restriction_days;
}
$grid->floatingScrollBar = false;

$grid->AddModelCustomSearchable('Date & Time', "sdate", 130, "center", "datetime");
$grid->SetDefaultValue("sdate", date("Y-m-d 00:00"), date("Y-m-d 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Caller ID", "msisdn");
$grid->AddSearhProperty("DID", "did");
$grid->AddSearhProperty('Type', 'type', 'select', array_merge(['*' => 'All'], $type));
$grid->AddSearhProperty("Skill", "skill_id", 'select', array_merge(['*' => 'All'], $skill_list[$skill_type]));

$grid->AddModelNonSearchable("Type", "type", 100, "center");
$grid->AddModelNonSearchable("Caller ID", "cli", 100, "center");
$grid->AddModelNonSearchable("DID", "did", 100, "center");
//$grid->AddModelNonSearchable("IVR Enter Time", "ivr_enter_time", 120, "center");
//$grid->AddModelNonSearchable("IVR", "ivr", 120, "center");
//$grid->AddModelNonSearchable("Time In IVR", "time_in_ivr", 120, "center");
//$grid->AddModelNonSearchable("Language", "language", 120, "center");
//$grid->AddModelNonSearchable("Skill Enter Time", "skill_stop_time", 120, "center");
$grid->AddModelNonSearchable("Skill", "skill", 100, "center");
//$grid->AddModelNonSearchable("Hold in Queue", "hold_in_q", 120, "center");
//$grid->AddModelNonSearchable("Disc. Party", "disc_party", 120, "center");
$grid->AddModelNonSearchable("Status", "status", 100, "center");
//$grid->AddModelNonSearchable("Service Time", "service_time", 120, "center");
//$grid->AddModelNonSearchable("Agent ID", "agent_id", 120, "center");
//$grid->AddModelNonSearchable("Total Time", "total_time", 120, "center");
//$grid->AddModelNonSearchable("Alarm", "alarm", 120, "center");
//$grid->AddModelNonSearchable("Disc. Cause", "disc_cause", 120, "center");


$grid->AddModelNonSearchable("Call", "click_to_call", 120, "center");
$grid->AddModelNonSearchable("Remove", "remove", 120, "center");
//$grid->AddModelNonSearchable("Trunk", "trunc_id", 120, "center");
//$grid->AddModelNonSearchable("Audio", "audio", 120, "center");

$grid->show("#searchBtn");

?>
<script>
    $(document).ready(function () {
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        $("#unattended_cdr_search").on('click', function () {
            //autoRefreshList();
        });
    });

    function autoRefreshList() {
        setInterval(function () {
            $('#unattended_cdr_search').trigger('click');
        }, 30000);
    }
</script>



