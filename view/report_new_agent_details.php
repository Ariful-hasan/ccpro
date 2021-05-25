<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->footerRow = TRUE;
$grid->CustomSearchOnTopGrid = true;
$grid->multisearch = true;
$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;
$grid->floatingScrollBar=true;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}

// $grid->AddSearhProperty("Date Format", "date_format",'select', $report_date_format_list);

$grid->AddModelCustomSearchable('Date & Time', "sdate", 200, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty('Agent:', 'agent_id', 'select', $agent_list);
$grid->AddSearhProperty("Skill Type", "skill_type",'select', $skill_type_list);
$grid->SetDefaultValue("skill_type", $skill_type);
$grid->AddSearhProperty("Skill", "skill_id",'select', array_merge(['*'=>'All'], $skill_list[$skill_type]));
$grid->AddSearhProperty("MSISDN", "msisdn");

// $grid->AddModelNonSearchable("Date", "start_time", 100, "center");
$grid->AddModelNonSearchable("Agent <br>ID", "agent_id", 100, "center");
$grid->AddModelNonSearchable("Agent <br>Name", "agent_name", 150, "center");
$grid->AddModelNonSearchable("Skill <br>Name", "skill_name", 150, "center");
$grid->AddModelNonSearchable("MSISDN", "msisdn", 100, "center");
$grid->AddModelNonSearchable("Call <br>Answered", "answered", 100, "center");
$grid->AddModelNonSearchable("Ring <br>Time (sec)", "rtime", 100, "center");
$grid->AddModelNonSearchable("Talk <br>Time (sec)", "talk_time", 110, "center");
// $grid->AddModelNonSearchable("Delay Between <br>Call (sec)", "delay_between_call", 140, "center");
$grid->AddModelNonSearchable("Wrap up<br>Time (sec)", "wrap_time", 100, "center");
$grid->AddModelNonSearchable("Hold <br>Time (sec)", "hold_time", 100, "center");
$grid->AddModelNonSearchable("AHT (sec)", "aht", 100, "center");
$grid->AddModelNonSearchable("Time in <br>Queue (sec)", "wait_time", 100, "center");
$grid->AddModelNonSearchable("Agent <br>Hangup", "agent_hangup", 100, "center");
$grid->AddModelNonSearchable("Transfer Tag", "transfer_to", 100, "center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    $(function () {
        var skill_list = JSON.parse('<?php echo json_encode($skill_list); ?>');
        
        $(document).on("click", "#cboxClose", function () {
            location.reload(true);
        });
        
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
        getSkillTypeDropDown();
    });
</script>

