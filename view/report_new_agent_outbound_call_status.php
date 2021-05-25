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
    $grid->footerRow = false;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;

    $grid->AddSearhProperty("Date", "sdate", "date", $report_date_format);
    $grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime("+1 day")));

    $grid->AddSearhProperty("Agent", "agent_id",'select', $agent_list);
    $grid->AddSearhProperty("Shift", "shift_code",'select', $shifts);
    //$grid->SetDefaultValue("agent_id", '');

    
    $grid->AddModelNonSearchable('Date', "sdate", 70, "center");
    $grid->AddModelCustomSearchable('Shift Type', "shift_code", 70, "center",'select',$shifts);
    $grid->AddModelCustomSearchable('Agent ID', "agent_id", 70, "center",'select',$agent_list,TRUE);
    $grid->AddModelNonSearchable('Agent Name', "agent_name", 100,"center");
    
    $grid->AddModelNonSearchable('Staff Time <br/> (h:m:s)', "staff_time", 70, "center");
    $grid->AddModelNonSearchable('Calls out <br/> Attempt', "calls_out_attempt", 70, "center");
    $grid->AddModelNonSearchable('Calls out <br/> Reached', "calls_out_reached", 70, "center");
    $grid->AddModelNonSearchable('Success <br/>Rate (%)', "success_rate", 70, "center");
    //$grid->AddModelNonSearchable('Ans. <br/> < 6 Sec ', "ring_lt_6_count", 70, "center");
    //$grid->AddModelNonSearchable('Ans. <br/> 6 - 10 Sec ', "ring_6_to_10_count", 70, "center");
    //$grid->AddModelNonSearchable('Ans. <br/> > 10 Sec ', "ring_gt_11_count", 70, "center");
    $grid->AddModelNonSearchable('Talk Time <br/> (h:m:s)', "talk_time", 100, "center");
    $grid->AddModelNonSearchable('Ring Time <br/> (h:m:s)', "ring_out_time", 100, "center");
    $grid->AddModelNonSearchable('ACW Time <br/> (h:m:s)', "aux_11_time", 100,"center");
    $grid->AddModelNonSearchable('Hold Time <br/> (h:m:s)', "hold_time", 100,"center");

    include('view/grid-tool-tips.php');
    if (isset($aux_messages) && is_array($aux_messages)){
        foreach ($aux_messages as $aux){
            if ($aux->aux_code > 11 && $aux->aux_code <= 20) {
                $auxindex = 'aux_'.$aux->aux_code."_time";
                $grid->AddModelNonSearchable($aux->message." <br>(h:m:s)", $auxindex, 100, "center");
            }
        }
    }


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

