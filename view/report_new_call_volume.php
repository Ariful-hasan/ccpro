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

    $grid->AddSearhProperty('Date', 'sdate', 'date', $report_date_format);
    $grid->SetDefaultValue('sdate', date($report_date_format), date($report_date_format, strtotime("+1 day")));

    $grid->AddSearhProperty('Skill','skill_id','select',$skills);



    $grid->AddModelNonSearchable("Date", "sdate", 100, "center");
    $grid->AddModelNonSearchable("Skill", "skill_name", 100, "center");
    $grid->AddModelNonSearchable("Offered Call", "calls_offered", 100, "center");
    $grid->AddModelNonSearchable("Repeated Call", "calls_repeated", 100, "center");
    $grid->AddModelNonSearchable("Daily Avg. Call", "daily_avg_call", 100, "center");

    //$grid->AddModelNonSearchable("Average Call<br>per Agent Hour", "avg_agent_per_hour_call", 120, "center");
    $grid->AddModelNonSearchable("Total <br>Answered Call", "calls_answerd", 120, "center");
    $grid->AddModelNonSearchable("Answered Within <br>Service Level", "answerd_within_service_level", 120, "center");
    $grid->AddModelNonSearchable("Abandoned <br>within Threshold Time", "abandoned_within_th", 150, "center");
    $grid->AddModelNonSearchable("Abandoned <br>after Threshold Time", "abandoned_after_threshold", 150, "center");
    $grid->AddModelNonSearchable("Total <br>Abandoned", "calls_abandoned", 150, "center");
    $grid->AddModelNonSearchable("Abandoned <br>Ratio", "abandoned_ratio", 100, "center");
    //$grid->AddModelNonSearchable("Number of <br>Call Status", "no_of_call_status", 100, "center");
    //$grid->AddModelNonSearchable("Number of <br>Call Ration", "no_of_call_ration", 100, "center");



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

