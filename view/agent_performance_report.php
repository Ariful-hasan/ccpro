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
$grid->showUserDataWithMainRows = true;


$grid->AddModelCustomSearchable('Date', "sdate", 250, 'center','report-datetime');
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 23:59"));


//$grid->AddModelNonSearchable("Date", "sdate", 100, "center");
$grid->AddModelNonSearchable("Agent <br> ID", "agent_id", 100, "center");
$grid->AddSearhProperty("Agent", "agent_id","select", $agents);

$grid->AddModelNonSearchable("Agent <br> Name", "name", 150, "center");
$grid->AddModelNonSearchable("Skill Set", "skill_set", 300, "center");
$grid->AddModelNonSearchable("Calls <br> Answered", "calls_answered", 100, "center");
$grid->AddModelNonSearchable("Ring <br> Time(sec)", "ring_time", 100, "center");
$grid->AddModelNonSearchable("Talk <br> Time (sec)", "talk_time", 100, "center");
$grid->AddModelNonSearchable("Wrap up <br> Time(sec)", "wrap_up_time", 100, "center");
$grid->AddModelNonSearchable("Hold <br> Time(sec)", "hold_time", 100, "center");
$grid->AddModelNonSearchable("Time in <br> Queue(sec)", "time_in_queue", 100, "center");
$grid->AddModelNonSearchable("AHT (sec)", "aht", 100, "center");
$grid->AddModelNonSearchable("Agent <br> Hangup", "agent_hangup", 100, "center");
$grid->AddModelNonSearchable("First login Time", "first_login", 200, "center");
$grid->AddModelNonSearchable("Login Duration", "login_time", 100, "center");
$grid->AddModelNonSearchable("Total Idle Time", "total_idle_time", 100, "center");
$grid->AddModelNonSearchable("Logout Time", "logout_time", 200, "center");
$grid->AddModelNonSearchable("Total Not <br> Ready Time (sec)", "not_ready_time", 150, "center");
$grid->AddModelNonSearchable("Total Avail. <br> Time", "available_time", 130, "center");
$grid->AddModelNonSearchable("Number of Not <br> Ready Count", "not_ready_count", 135, "center");
$grid->AddModelNonSearchable("Login <br> Count", "login_count", 135, "center");
$grid->AddModelNonSearchable("Logout <br> Count", "logout_count", 135, "center");
$grid->AddModelNonSearchable("Agent <br> Disc. Calls", "agent_disconnect_calls", 130, "center");
$grid->AddModelNonSearchable("Agent <br> Reject Calls", "agent_reject_calls", 130, "center");
$grid->AddModelNonSearchable("Short Call", "short_call", 100, "center");
$grid->AddModelNonSearchable("Count of <br> Workcode", "workcode_count", 120, "center");
$grid->AddModelNonSearchable("Workcode %", "workcode_percent", 120, "center");
$grid->AddModelNonSearchable("Multiple <br> Calls", "repeated_call", 100, "center");
$grid->AddModelNonSearchable("Multiple %", "repeated_percent", 100, "center");

$grid->show("#searchBtn");



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

    function beforeFormSubmit(){
        var start_date = $('.srcMFrom').val();
        var end_date = $('.srcMTo').val();

        var start = start_date.split(' ');
        var start_date = start[0];
        var start_time = start[1];

        if ('d/m/Y' == date_format){
            var start_day = start_date.split('/')[0];
            var start_month = start_date.split('/')[1];
            var start_year = start_date.split('/')[2];
        }else if ('m/d/Y' == date_format){
            var start_day = start_date.split('/')[1];
            var start_month = start_date.split('/')[0];
            var start_year = start_date.split('/')[2];
        }


        var d = new Date(start_year, start_month -1 , start_day, start_time.split(':')[0], start_time.split(':')[1]);

        var end = end_date.split(' ');
        var end_date = end[0];
        var end_time = end[1];

        if ('d/m/Y' == date_format){
            var end_day = end_date.split('/')[0];
            var end_month = end_date.split('/')[1];
            var end_year = end_date.split('/')[2];
        }else if ('m/d/Y' == date_format){
            var end_day = end_date.split('/')[1];
            var end_month = end_date.split('/')[0];
            var end_year = end_date.split('/')[2];
        }


        var d2 = new Date(end_year, end_month -1 , end_day, end_time.split(':')[0], end_time.split(':')[1]);

        if ((d2.getTime() - d.getTime()) / (1000*60*60*72) > 1) {
            alert('Provide date range within 48 hour');
            return false;
        }

        return true;
    }

</script>

