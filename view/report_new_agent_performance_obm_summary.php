<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
$grid->footerRow = false;
$grid->userDataOnFooter = false;
$grid->floatingScrollBar=true;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}

$grid->AddModelCustomSearchable('Date', "sdate", 250, 'center','report-datetime');
// $grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 12:00"));
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));

//$grid->AddModelNonSearchable("Date", "sdate", 100, "center");
$grid->AddModelNonSearchable("Agent <br> ID", "agent_id", 100, "center");
$grid->AddModelNonSearchable("Agent <br> Name", "agent_name", 150, "center");
$grid->AddModelNonSearchable("Skill Set", "skill_set", 300, "center");
$grid->AddModelNonSearchable("Calls Out <br> Attempt", "calls_out_attempt", 100, "center");
$grid->AddModelNonSearchable("Calls <br> Answered", "calls_answered", 100, "center");
$grid->AddModelNonSearchable("Ring <br> Time(sec)", "ring_time", 100, "center");
$grid->AddModelNonSearchable("Talk <br> Time (sec)", "talk_time", 100, "center");
$grid->AddModelNonSearchable("Wrap up <br> Time(sec)", "wrap_up_time", 100, "center");
$grid->AddModelNonSearchable("Hold <br> Time(sec)", "hold_time", 100, "center");
$grid->AddModelNonSearchable("AHT (sec)", "aht", 100, "center");
$grid->AddModelNonSearchable("Agent <br> Hangup", "agent_hangup", 100, "center");
$grid->AddModelNonSearchable("First login Time", "first_login", 200, "center");
$grid->AddModelNonSearchable("Login Duration", "login_time", 100, "center");
$grid->AddModelNonSearchable("Total Idle Time", "total_idle_time", 100, "center");
$grid->AddModelNonSearchable("Logout Time", "logout_time", 200, "center");
$grid->AddModelNonSearchable("Total Not <br> Ready Time (sec)", "not_ready_time", 150, "center");
$grid->AddModelNonSearchable("Total Avail. <br> Time", "available_time", 130, "center");
$grid->AddModelNonSearchable("Number of Not <br> Ready Count", "not_ready_count", 135, "center");
$grid->AddModelNonSearchable("Agent <br> Disc. Calls", "agent_disc_calls", 130, "center");
$grid->AddModelNonSearchable("Agent <br> Reject Calls", "agent_reject_calls", 130, "center");
// $grid->AddModelNonSearchable("Short Call", "short_call", 100, "center");
// $grid->AddModelNonSearchable("Count of <br> Workcode", "workcode_count", 120, "center");
// $grid->AddModelNonSearchable("Workcode %", "workcode_percent", 120, "center");

$grid->show("#searchBtn");
 /*echo "<p class='text-danger'> * Difference between start time and end time should be less than or equal 24 hour. <br>
 ** If your selected time range exceeds 24 hour then start time will be your selected start time and end time will be start time + 24 hour.</p>"; */

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
    var date_format = "<?php echo $report_date_format ?>";

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });
</script>

