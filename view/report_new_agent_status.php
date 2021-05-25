<?php
    include_once "lib/jqgrid_report.php";
    $grid = new jQGridReport();
    $grid->url = isset($dataUrl) ? $dataUrl : "";
    $grid->width="auto";//$grid->minWidth = 800;
    $grid->height = "auto";//390;
    $grid->rowNum = 20;
    $grid->pager = "#pagerb";
    $grid->container = ".content-body";
    //$grid->hidecaption=false;
    $grid->shrinkToFit =true;
    $grid->footerRow = TRUE;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;


    /*-----------------------------------Header Column Group Add-------------------------------------*/
    //$grid->addHeaderColumnGroup("agent_id",2,"Agent");
    //$grid->addHeaderColumnGroup("staff_time",6,"Total Time (00:00:00)");
//    $grid->AddSearhProperty("Date", "sdate","date");
    $grid->AddModelCustomSearchable('Date', "sdate", 100, "center","date");
    $grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));
    // $grid->AddModelCustomSearchable('Date', "sdate", 100, "center","report-datetime");
    // $grid->SetDefaultValue("sdate", date(REPORT_DATE_FORMAT." H:00"),date(REPORT_DATE_FORMAT." 23:59"));

    $grid->AddSearhProperty("Agent", "agent_id",'select', $agent_list);
    $grid->AddSearhProperty("Shift", "shift_code",'select', $shifts);
    //$grid->SetDefaultValue("agent_id", '');

    
//    $grid->AddModelNonSearchable('Date', "sdate", 100, "center");
    $grid->AddModelCustomSearchable('Shift Type', "shift_code", 100, "center",'select',$shifts);
    $grid->AddModelCustomSearchable('ID', "agent_id", 100, "center",'select',$agent_list,TRUE);
    $grid->AddModelNonSearchable('Name', "agent_name", 100,"center");
    $grid->AddModelNonSearchable('Login Time', "first_login", 150, "center");
    $grid->AddModelNonSearchable('Logout Time', "last_logout", 150, "center");
    $grid->AddModelNonSearchable('Login', "staff_time", 100,"center");
    $grid->AddModelNonSearchable('Logout', "no_login_time", 100,"center");
    $grid->AddModelNonSearchable('AUX-In', "total_aux_in_time", 100,"center");
    $grid->AddModelNonSearchable('AUX-Out', "total_aux_out_time", 100,"center");
    $grid->AddModelNonSearchable('Break', "total_break_time", 100,"center");
    $grid->AddModelNonSearchable('Not Ready', "total_unready_time", 100,"center");

    $grid->AddModelNonSearchable('Login<br>Count', "login_count", 100,"center");
    //$grid->AddModelNonSearchable('Logout<br>Count', "login_count", 100,"center");

    
    //include('view/grid-tool-tips.php');
    //$grid->addModelTooltips($tooltips);
    
    $grid->show("#searchBtn");

?>

<script type="text/javascript">
    $(function () {
        $(document).on("click", "#cboxClose", function () {
            location.reload(true);
        });

        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });
</script>
