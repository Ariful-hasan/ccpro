<?php 
    include_once "lib/jqgrid_report.php";
    $grid = new jQGridReport();
    $grid->url = isset($dataUrl) ? $dataUrl : "";
    $grid->width="auto";//$grid->minWidth = 800;
    $grid->height = "auto";//390;
    $grid->rowNum = 20;
    $grid->pager = "#pagerb";
    $grid->container = ".content-body";
    $grid->shrinkToFit = TRUE;
    $grid->footerRow = TRUE;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;

    $grid->AddSearhProperty("Date", "sdate","date", $report_date_format);
    $grid->SetDefaultValue("sdate", date($report_date_format),date($report_date_format));
    $grid->AddSearhProperty("Agent", "agent_id",'select', $agents);

    
    $grid->AddModelNonSearchable('Date', "sdate", 140, "center");
    $grid->AddModelNonSearchable('Day', "day", 140, "center");
    $grid->AddModelNonSearchable('Total Agents', "agent_worked", 140, "center");
    $grid->AddModelNonSearchable('Total Call Initiated', "total_initiated", 140, "center");
    $grid->AddModelNonSearchable('Daily Avg. Call Initiated', "daily_avg_initiated", 140, "center");
    $grid->AddModelNonSearchable('Total Call Reached', "total_reached", 140, "center");
    $grid->AddModelNonSearchable('Total Call Unreached', "total_unreached", 140, "center");
    $grid->AddModelNonSearchable('Reached Call Ratio', "reached_ratio", 140, "center");
    $grid->AddModelNonSearchable('Unreached Call Ratio', "unreached_ratio", 140, "center");

    

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

