<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = false;
$grid->footerRow = false;
$grid->CustomSearchOnTopGrid = true;
$grid->multisearch = true;
$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

$grid->AddModelCustomSearchable('DateTime', "sdate", 150, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Skill", "skill_id", 'select', array_merge(['*' => 'All'], $skill_list[$skill_type]));
$grid->AddSearhProperty("CLI", "cli");
$grid->AddSearhProperty("Agent", "agent_id", 'select', $agent_list);
$grid->AddSearhProperty("DID", "did");
$grid->AddSearhProperty("Callback Within (Minute)", "callback_within");
$grid->AddSearhProperty("Removed By", "removed_by", 'select', $agent_list);
$grid->AddSearhProperty("Call Status", "status",'select', $call_status);
$grid->AddSearhProperty("Call Disposition", "disposition_id", 'select', $disposition_list);
$grid->AddModelCustomSearchable('Remove Time', "remove_time", 150, "center","report-datetime");
$grid->AddSearhProperty("Call ID", "callid");

$grid->AddModelNonSearchable("Stop Time", "stop_time", 150, "center");
$grid->AddModelNonSearchable("Skill", "skill", 150, "center");
$grid->AddModelNonSearchable("Agent ID", "agent_id", 150, "center");
$grid->AddModelNonSearchable("Agent Name", "agent_name", 150, "center");
$grid->AddModelNonSearchable("DID", "did", 150, "center");
$grid->AddModelNonSearchable("Callback Number", "cli", 150, "center");
$grid->AddModelNonSearchable("Queue Time", "hold_in_q", 150, "center");
$grid->AddModelNonSearchable("Call Back After", "outbound_time_difference", 150, "center");
$grid->AddModelNonSearchable("Threshold Time", "threshold_time", 150, "center");
$grid->AddModelNonSearchable("Threshold Status", "threshold_status", 150, "center");
$grid->AddModelNonSearchable("Call Status", "status", 150, "center");
$grid->AddModelNonSearchable("Talk Time", "talk_time", 150, "center");
$grid->AddModelNonSearchable("Disc. Cause", "disc_cause", 150, "center");
$grid->AddModelNonSearchable("Disc. Party", "disc_party", 150, "center");
$grid->AddModelNonSearchable("Removal Status", "removal_status", 150, "center");
$grid->AddModelNonSearchable("Removed By", "removed_by", 150, "center");
$grid->AddModelNonSearchable("Remove Time", "manual_update_time", 150, "center");
$grid->AddModelNonSearchable("Disposition", "disposition", 150, "center");
$grid->AddModelNonSearchable("Removal Disposition", "removal_disposition", 150, "center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    $(function () {
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });
</script>


