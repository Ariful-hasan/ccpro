<?php 
    include_once "lib/jqgrid_report.php";
    $grid = new jQGridReport();
    $grid->url = isset($dataUrl) ? $dataUrl : "";
    $grid->width="auto";
    $grid->height = "auto";//390;
    $grid->rowNum = 20;
    $grid->pager = "#pagerb";
    $grid->container = ".content-body";
    $grid->shrinkToFit = TRUE;
    $grid->footerRow=TRUE;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;

    $grid->AddSearhProperty("Date", "sdate","date", $report_date_format);
    $grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime("+1 day")));
    $grid->AddSearhProperty("Skill", "skill_id",'select', $skills);

    $grid->AddModelNonSearchable('Date', "sdate", 150, "center");
    $grid->AddModelNonSearchable('Skill', "skill_id", 150, "center");
    $grid->AddModelNonSearchable('Average Handling Time', "average_handling_time", 150,"center");

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

