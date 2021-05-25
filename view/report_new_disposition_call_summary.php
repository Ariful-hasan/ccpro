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
    $grid->floatingScrollBar=true;

    $grid->AddSearhProperty("Date", "log_date","date", $report_date_format);
    $grid->SetDefaultValue("log_date", date($report_date_format), date($report_date_format,strtotime("+1 day")));
    $grid->AddSearhProperty("Disposition", "disposition_id","select", $dispositions);

    $grid->AddModelNonSearchable('Date', "log_date", 60, "center");
    $grid->AddModelNonSearchable('Disposition', "disposition_id", 60, "center");
    $grid->AddModelNonSearchable('Total', "call_count", 60, "center");
    $grid->AddModelNonSearchable('Percent (%)', "percent", 60, "center");

    

    
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
