<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 10;
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

$grid->AddModelNonSearchable("Agent <br> ID", "agent_id", 100, "center");
$grid->AddModelNonSearchable("Agent <br> Name", "agent_name", 150, "center");
$grid->AddModelNonSearchable("Skill Set", "skill_set", 300, "center");

$grid->AddModelNonSearchable("First login Time", "first_login", 200, "center");
$grid->AddModelNonSearchable("Login Duration", "login_time", 100, "center");
$grid->AddModelNonSearchable("Total Idle Time", "total_idle_time", 100, "center");
$grid->AddModelNonSearchable("Logout Time", "logout_time", 200, "center");
$grid->AddModelNonSearchable("Total Not <br> Ready Time (sec)", "not_ready_time", 150, "center");
$grid->AddModelNonSearchable("Total Avail. <br> Time", "available_time", 130, "center");
$grid->AddModelNonSearchable("Number of Not <br> Ready Count", "not_ready_count", 135, "center");

$grid->AddModelNonSearchable("Workcode <br> Count", "disposition", 100, "center");
$grid->AddModelNonSearchable("Workcode %", "workcode_percent", 120, "center");
$grid->AddModelNonSearchable("Total View", "view", 100, "center");
$grid->AddModelNonSearchable("Total Pull", "pull", 100, "center");
$grid->AddModelNonSearchable("Total Pending", "pending", 100, "center");
$grid->AddModelNonSearchable("Total <br> Pending-Client", "pen_client", 100, "center");
$grid->AddModelNonSearchable("Total Served", "served", 100, "center");
$grid->AddModelNonSearchable("Total Closed", "closed", 100, "center");
$grid->AddModelNonSearchable("Total <br> Rescheduled", "rescheduled", 100, "center");
$grid->AddModelNonSearchable("Total Park", "park", 100, "center");
$grid->AddModelNonSearchable("Total New", "new", 100, "center");
$grid->AddModelNonSearchable("Total <br> Send Email", "mail_send", 100, "center");

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

