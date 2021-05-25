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
    $grid->footerRow = true;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;
    if(!empty($report_restriction_days)){
        $grid->DateRange=$report_restriction_days;
    }
    $grid->floatingScrollBar=true;

    $grid->AddSearhProperty("Date", "sdate","date", $report_date_format);
    $grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));
    $grid->AddSearhProperty("Agent", "agent_id",'select', $agent_list);
    $grid->AddSearhProperty("Skill", "skill_id",'select', array('*' => 'All') + $skill_list[$skill_type]);

    $grid->AddModelNonSearchable('Date', "sdate", 100, "center");
    $grid->AddModelNonSearchable('Skill', "skill_id", 110, "center");
    $grid->AddModelNonSearchable('Agent ID', "agent_id", 75, "center");
    $grid->AddModelNonSearchable('Agent Name', "agent_name", 110, "center");
    $grid->AddModelNonSearchable('Total Attempted <br/>Call', "total_call", 110, "center");
    $grid->AddModelNonSearchable('Successful <br/> Call Count', "success_call", 100, "center");
    $grid->AddModelNonSearchable('Unsuccessful <br/> Call Count', "failed_call", 100, "center");
    $grid->AddModelNonSearchable('Ring <br/> Time (Sec)', "ring_time", 100, "center");
    $grid->AddModelNonSearchable('Talk <br/> Time (Sec)', "talk_time", 100, "center");
    $grid->AddModelNonSearchable('Hold <br/> Time (Sec)', "hold_time", 100, "center");
    $grid->AddModelNonSearchable('Wrap Up <br/> Time (Sec)', "wrap_up_time", 100, "center");
    $grid->AddModelNonSearchable('AHT (Sec)', "aht", 100, "center");
//    $grid->AddModelNonSearchable('Shortest call in sec', "shortest_ob_call_time", 100, "center");
//    $grid->AddModelNonSearchable('Unique call count', "unique_call_count", 100, "center");
//    $grid->AddModelNonSearchable(' Multiple call count', "repeated_ob_call_count", 100, "center");
    $grid->show("#searchBtn");

    ?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function () {
        $(document).on("click", "#cboxClose", function () {
            location.reload(true);
        });

        var current_date = new Date();
        var start_default_date = '';
        var end_default_date = '';
        date_format = '<?php echo $report_date_format ?>';

        if(date_format == 'd/m/Y'){
            start_default_date = current_date.getDate()+'/0'+(+current_date.getMonth()+1)+'/'+current_date.getFullYear();
            current_date.setDate(current_date.getDate() + 1);
            end_default_date = current_date.getDate()+'/0'+(+current_date.getMonth()+1)+'/'+current_date.getFullYear();
        }else if(date_format == 'm/d/Y'){
            start_default_date = '0'+(+current_date.getMonth()+1)+'/'+current_date.getDate()+'/'+current_date.getFullYear();
            current_date.setDate(current_date.getDate() + 1);
            end_default_date = '0'+(+current_date.getMonth()+1)+'/'+current_date.getDate()+'/'+current_date.getFullYear();
        }
        SetNewReportDatePicker(date_format, start_default_date, end_default_date);
    });

</script>