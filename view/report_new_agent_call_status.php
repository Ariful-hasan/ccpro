<?php 
    include_once "lib/jqgrid_report.php";
    $grid = new jQGridReport();
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
    
    // $grid->AddSearhProperty("Date Format", "date_format",'select', $report_date_format_list);
    // $grid->SetDefaultValue("date_format", $report_date_format);
    $grid->AddModelCustomSearchable('Date', "sdate", 100, "center","date");
    $grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));

    $grid->AddSearhProperty("Agent", "agent_id",'select', $agent_list);
    $grid->AddSearhProperty("Shift", "shift_code",'select', $shifts);
    $grid->AddSearhProperty("Type", "type",'select', $report_type_list);
    //$grid->SetDefaultValue("agent_id", '');
    
    // $grid->AddModelNonSearchable('Date', "sdate", 75, "center");
    $grid->AddModelNonSearchable('Year', "syear", 70,"center");
    $grid->AddModelNonSearchable('Month', "smonth", 70,"center");
    $grid->AddModelNonSearchable('Quarter No.', "quarter_no", 70,"center");
    $grid->AddModelCustomSearchable('Shift Type', "shift_code", 70, "center",'select',$shifts);
    $grid->AddModelCustomSearchable('Agent ID', "agent_id", 65, "center",'select',$agent_list,TRUE);
    $grid->AddModelNonSearchable('Agent Name', "agent_name", 105,"center");
    $grid->AddModelNonSearchable('First login <br>Time', "first_login", 110, "center");
    $grid->AddModelNonSearchable('Login <br>Duration', "staff_time", 95,"center");
    $grid->AddModelNonSearchable('Total Idle <br>Time', "idle_time", 90,"center");
    $grid->AddModelNonSearchable('Logout Time', "last_logout", 110,"center");
    $grid->AddModelNonSearchable('Total Not Ready <br>Time (Sec)', "total_not_ready_time", 105,"center");
    $grid->AddModelNonSearchable('ASA (sec)', "asa_result", 80, "center");
    // $grid->AddModelNonSearchable('Total Ready <br>Time (Sec)', "total_ready_time", 80,"center");
//    $grid->AddModelNonSearchable('ASA <br>within 5 sec', "asa_in_5s", 80, "center");
//    $grid->AddModelNonSearchable('ASA <br>aft. 5 sec', "asa_aft_5s", 80,"center");
    
    $grid->AddModelNonSearchable('Calls <br>Answered', "calls_in_ans", 75, "center");
    $grid->AddModelNonSearchable('Total Talk <br>Time (Sec)', "talk_time", 80,"center");
    $grid->AddModelNonSearchable('Avg.<br>Talk Time (Sec)', "avg_talk_time", 100,"center");
    $grid->AddModelNonSearchable('Total Ring <br>Time (sec)', "ring_in_time", 80,"center");
    $grid->AddModelNonSearchable('Total Hold <br>Time (sec)', "hold_time", 80,"center");
    $grid->AddModelNonSearchable('Avg. <br>Hold Time', "avg_hold_time", 80,"center");
    $grid->AddModelNonSearchable('Delay <br>Between Call', "delay_between_call", 100,"center");
    $grid->AddModelNonSearchable('AHT (Sec)', "avg_call_handling_time", 90,"center");
//    $grid->AddModelNonSearchable('ACW Time', "aux_11_time", 80,"center");
    $grid->AddModelNonSearchable('Total<br>Avail. Time', "available_time", 80,"center");
    $grid->AddModelNonSearchable('Agent <br>Disc. Calls', "hangup_acd_calls", 80,"center");
    $grid->AddModelNonSearchable('Agent <br>Reject Calls', "drop_acd_calls", 80,"center");
    // $grid->AddModelNonSearchable('IVR<br>Transfer', "xfer_ivr_count", 70,"center");
    // $grid->AddModelNonSearchable('Skill<br>Transfer', "xfer_queue_count", 70,"center");
    $grid->AddModelNonSearchable('Wrap up <br>time (Sec)', "wrap_up_time", 70,"center");
    $grid->AddModelNonSearchable('Time in <br>Queue (Sec)', "hold_time_in_queue", 80,"center");
    $grid->AddModelNonSearchable('Short <br>Calls', "short_call_count", 70,"center");
    $grid->AddModelNonSearchable('Number of <br>Not Ready', "number_of_not_ready", 70,"center");
    $grid->AddModelNonSearchable('Count of <br>Workcode', "wrap_up_count", 70,"center");
    $grid->AddModelNonSearchable('Workcode <br>%', "wrap_up_count_percentage", 70,"center");
    $grid->AddModelNonSearchable('FCR Calls', "fcr_call_count", 70,"center");
    $grid->AddModelNonSearchable('FCR %', "fcr_call_percentage", 70,"center");
    $grid->AddModelNonSearchable('Repeated <br>Calls', "repeat_call_count", 70,"center");
    $grid->AddModelNonSearchable('Repeat %', "repeat_call_percentage", 70,"center");

    $grid->show("#searchBtn");

    ?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });
</script>

