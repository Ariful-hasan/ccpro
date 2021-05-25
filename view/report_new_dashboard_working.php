<?php 
    include_once "lib/jqgrid_report.php";
    $grid = new jQGridReport('skillset_summary_report');
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
    $grid->DownloadFileName=$pageTitle;
    if(!empty($report_restriction_days)){
        $grid->DateRange=$report_restriction_days;
    }
    $grid->floatingScrollBar=true;

    // $grid->AddSearhProperty("Date Format", "date_format",'select', $report_date_format_list);
    // $grid->SetDefaultValue("date_format", $report_date_format);
    $grid->AddModelCustomSearchable('Date&Time', "sdate", 110, "center","report-datetime");
    $grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
    $grid->AddSearhProperty("Type", "report_type",'select', $report_type_list);
    $grid->SetDefaultValue("report_type", $report_type);
    $grid->AddSearhProperty("Skill Type", "skill_type",'select', $skill_type_list);
    $grid->SetDefaultValue("skill_type", $skill_type);
    $grid->AddSearhProperty("Skill", "skill_id",'select', ['*'=>'All']+$skill_list[$skill_type]);
    // $grid->AddSearhProperty("Category", "category",'select', $skill_category_list);

    $grid->AddModelNonSearchable('Year', "syear", 70,"center");
    $grid->AddModelNonSearchable('Month', "smonth", 70,"center");
    $grid->AddModelNonSearchable('Hour', "shour", 70,"center");
    $grid->AddModelNonSearchable('Minute', "sminute", 70,"center");
    $grid->AddModelNonSearchable('Quarter No.', "quarter_no", 70,"center");
    $grid->AddModelNonSearchable('Half Hour', "half_hour", 70,"center");
    $grid->AddModelNonSearchable('Hour:Minute', "hour_minute", 90,"center");

    // $grid->AddModelNonSearchable('Skill ID', "skill_id", 65, "center");
    $grid->AddModelNonSearchable('Skill Name', "skill_name", 105,"center");
    // $grid->AddModelCustomSearchable('Category', "category", 105,"center",'select',$skill_category_list,TRUE);

    // $grid->AddModelNonSearchable('MSC <br>Offered', "calls_offered", 75,"center");
    $grid->AddModelNonSearchable('RGB', "rgb_call_count", 70,"center");
    $grid->AddModelNonSearchable('Forecasted <br>Calls', "forecasted_call_count", 75,"center");
    $grid->AddModelNonSearchable('Calls <br>Offered', "calls_offered", 75,"center");
    $grid->AddModelNonSearchable('Calls <br>Answered', "calls_answerd", 75,"center");
    $grid->AddModelNonSearchable('Calls <br>Abandoned', "calls_abandoned", 75,"center");
    $grid->AddModelNonSearchable('Calls Answered <br>within 10 sec', "ans_lte_10_count", 100,"center");
    $grid->AddModelNonSearchable('Calls Answered <br>within 20 sec', "ans_lte_20_count", 100,"center");
    $grid->AddModelNonSearchable('Calls Answered <br>within 30 sec', "ans_lte_30_count", 100,"center");
    $grid->AddModelNonSearchable('Calls Answered <br>within 60 sec', "ans_lte_60_count", 100,"center");
    $grid->AddModelNonSearchable('Calls Answered <br>within 90 sec', "ans_lte_90_count", 100,"center");
    $grid->AddModelNonSearchable('Calls Answered <br>within 120 sec', "ans_lte_120_count", 100,"center");
    // $grid->AddModelNonSearchable('Calls Answered <br>after 120 sec', "ans_gt_120_count", 100,"center");
    $grid->AddModelNonSearchable('Calls Abandoned <br>within 10 sec', "abd_lte_10_count", 110,"center");
    $grid->AddModelNonSearchable('Calls Abandoned <br>within 20 sec', "abd_lte_20_count", 110,"center");
    $grid->AddModelNonSearchable('Calls Abandoned <br>within 30 sec', "abd_lte_30_count", 110,"center");
    $grid->AddModelNonSearchable('Calls Abandoned <br>within 60 sec', "abd_lte_60_count", 110,"center");
    $grid->AddModelNonSearchable('Calls Abandoned <br>within 90 sec', "abd_lte_90_count", 110,"center");
    $grid->AddModelNonSearchable('Calls Abandoned <br>within 120 sec', "abd_lte_120_count", 110,"center");
    // $grid->AddModelNonSearchable('Calls Abandoned <br>after 120 sec', "abd_gt_120_count", 110,"center");
    $grid->AddModelNonSearchable('Forecast <br>Accuracy %', "forecasted_call_percentage", 75,"center");
    $grid->AddModelNonSearchable('Service Level <br>(10 Sec)', "service_level_lte_10_count", 90,"center");
    $grid->AddModelNonSearchable('Service Level <br>(20 Sec)', "service_level_lte_20_count", 90,"center");
    $grid->AddModelNonSearchable('Service Level <br>(30 Sec)', "service_level_lte_30_count", 90,"center");
    $grid->AddModelNonSearchable('Service Level <br>(60 Sec)', "service_level_lte_60_count", 90,"center");
    $grid->AddModelNonSearchable('Service Level <br>(90 Sec)', "service_level_lte_90_count", 90,"center");
    $grid->AddModelNonSearchable('Service Level <br>(120 Sec)', "service_level_lte_120_count", 90,"center");
    $grid->AddModelNonSearchable('Average Handling <br>Time(sec)', "avg_handling_time", 120,"center");
    $grid->AddModelNonSearchable('Abandoned <br>Ratio', "abandoned_ratio", 120,"center");
    $grid->AddModelNonSearchable('Abandoned <br>Ratio (10 Sec)', "abandoned_ratio_10", 100,"center");
    $grid->AddModelNonSearchable('Abandoned <br>Ratio (20 Sec)', "abandoned_ratio_20", 100,"center");
    $grid->AddModelNonSearchable('Abandoned <br>Ratio (30 Sec)', "abandoned_ratio_30", 100,"center");
    $grid->AddModelNonSearchable('Abandoned <br>Ratio (60 Sec)', "abandoned_ratio_60", 100,"center");
    $grid->AddModelNonSearchable('Abandoned <br>Ratio (90 Sec)', "abandoned_ratio_90", 100,"center");
    $grid->AddModelNonSearchable('Abandoned <br>Ratio (120 Sec)', "abandoned_ratio_120", 100,"center");
    $grid->AddModelNonSearchable('Complaint <br>Calls', "complaint_call_count", 80,"center");
    $grid->AddModelNonSearchable('Request <br>Calls', "request_call_count", 80,"center");
    $grid->AddModelNonSearchable('Query <br>Calls', "query_call_count", 80,"center");    
    $grid->AddModelNonSearchable('% of Complaint <br>Calls', "per_complaint_call_count", 100,"center");
    $grid->AddModelNonSearchable('% of Request <br>Calls', "per_request_call_count", 100,"center");
    $grid->AddModelNonSearchable('% of Query <br>Calls', "per_query_call_count", 100,"center");
    $grid->AddModelNonSearchable('Wrap-up <br>Count', "wrap_up_call_count", 75,"center");
    $grid->AddModelNonSearchable('Wrap-up %', "wrap_up_percentage", 75,"center");
    $grid->AddModelNonSearchable('Total <br>ICE Count', "ice_count", 75,"center");
    $grid->AddModelNonSearchable('ICE Positive <br>Feedback', "ice_positive_count", 110,"center");
    $grid->AddModelNonSearchable('ICE Negative <br>Feedback', "ice_negative_count", 110,"center");
    $grid->AddModelNonSearchable('ICE Score', "ice_score", 75,"center");
    $grid->AddModelNonSearchable('1-Day Repeat % <br>(Only CLI)', "per_repeat_cli_1_count", 110,"center");
    $grid->AddModelNonSearchable('2-Day Repeat % <br>(Only CLI)', "per_repeat_cli_2_count", 110,"center");
    $grid->AddModelNonSearchable('3-Day Repeat % <br>(Only CLI)', "per_repeat_cli_3_count", 110,"center");
    $grid->AddModelNonSearchable('7-Day Repeat % <br>(Only CLI)', "per_repeat_cli_7_count", 110,"center");
    $grid->AddModelNonSearchable('30-Day Repeat % <br>(Only CLI)', "per_repeat_cli_30_count", 110,"center");
    $grid->AddModelNonSearchable('1-Day Repeat % <br>(Both)', "per_repeat_1_count", 110,"center");
    $grid->AddModelNonSearchable('2-Day Repeat % <br>(Both)', "per_repeat_2_count", 110,"center");
    $grid->AddModelNonSearchable('3-Day Repeat % <br>(Both)', "per_repeat_3_count", 110,"center");
    $grid->AddModelNonSearchable('7-Day Repeat % <br>(Both)', "per_repeat_7_count", 110,"center");
    $grid->AddModelNonSearchable('30-Day Repeat % <br>(Both)', "per_repeat_30_count", 110,"center");
    $grid->AddModelNonSearchable('FCR', "fcr_call_count", 75,"center");
    $grid->AddModelNonSearchable('FCR %', "fcr_call_percentage", 75,"center");
    $grid->AddModelNonSearchable('Average Wait <br>Time (Sec)', "ave_hold_time_in_queue", 110,"center");
    $grid->AddModelNonSearchable('Max. Wait <br>Time (Sec)', "max_hold_time_in_queue", 110,"center");
    $grid->AddModelNonSearchable('CPC', "cpc", 75,"center");

    // $grid->AddModelNonSearchable('Talk <br>time(sec)', "talk_time", 75,"center");
    // $grid->AddModelNonSearchable('Ring <br>time(sec)', "ring_time", 75,"center");
    // $grid->AddModelNonSearchable('Hold <br>time(sec)', "agent_hold_time", 75,"center");
    // $grid->AddModelNonSearchable('Break <br>Time(sec)', "wrap_up_time", 75,"center");
    // $grid->AddModelNonSearchable('Avg. Break <br>Time(sec)', "avg_wrap_up_time", 75,"center");
    // $grid->AddModelNonSearchable('ASA(sec)', "asa", 75,"center");
    // $grid->AddModelNonSearchable('Short Call', "short_call_count", 75,"center");
    // $grid->AddModelNonSearchable('Short <br>Call%', "short_call_percentage", 75,"center");
    // $grid->AddModelNonSearchable('Unique <br>Caller', "unique_caller", 75,"center");
    // $grid->AddModelNonSearchable('Unique <br>Call %', "unique_caller_percentage", 75,"center");
    // $grid->AddModelNonSearchable('Multiple <br>Call %', "repeat_call_percentage", 75,"center");
    // $grid->AddModelNonSearchable('Agent Call <br>Hangup', "agent_hangup_count", 80,"center");
    // $grid->AddModelNonSearchable('Agent Call <br>Hangup %', "agent_hangup_percentage", 80,"center");
    // $grid->AddModelNonSearchable('CPC', "cpc", 75,"center");

    $grid->show("#searchBtn");

    ?>

<script type="text/javascript">
    var skill_list = JSON.parse('<?php echo json_encode($skill_list); ?>');
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
        $("#skill_type").on('change', function(){
            var val = $(this).val();
            var option_str = '';

            if(val){
                option_str = '<option value="*">All</option>';
                $.each(skill_list[val], function(idx, item){
                    option_str += '<option value="'+idx+'">'+item+'</option>'; 
                });
                
                $('#skill_id').html(option_str);
                $('#skill_id').select2("val", "*");
                $('#skill_id').trigger('change');
            }
        });
    });
</script>

