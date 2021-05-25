<?php
    include_once "lib/jqgrid_report.php";
    $grid = new jQGridReport();
    $grid->url = isset($dataUrl) ? $dataUrl : "";
    $grid->width="auto";//$grid->minWidth = 800;
    $grid->height = "auto";//390;
    $grid->rowNum = 20;
    $grid->pager = "#pagerb";
    $grid->container = ".content-body";
    $grid->shrinkToFit = FALSE;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;

    $grid->AddSearhProperty('Date', 'sdate', 'date', $report_date_format);
    $grid->SetDefaultValue('sdate', date($report_date_format), date($report_date_format));

    $grid->AddSearhProperty('Skill','skill_id','select',$skills);

    $grid->AddModelNonSearchable("Hourly Situation", "hourly_situation", 120, "center");
    $grid->AddModelNonSearchable("00:00 AM", "Shour_00", 60, "center");
    $grid->AddModelNonSearchable("1:00 AM", "Shour_01", 60, "center");
    $grid->AddModelNonSearchable("2:00 AM", "Shour_02", 60, "center");
    $grid->AddModelNonSearchable("3:00 AM", "Shour_03", 60, "center");
    $grid->AddModelNonSearchable("4:00 AM", "Shour_04", 60, "center");
    $grid->AddModelNonSearchable("5:00 AM", "Shour_05", 60, "center");
    $grid->AddModelNonSearchable("6:00 AM", "Shour_06", 60, "center");
    $grid->AddModelNonSearchable("7:00 AM", "Shour_07", 60, "center");
    $grid->AddModelNonSearchable("8:00 AM", "Shour_08", 60, "center");
    $grid->AddModelNonSearchable("9:00 AM", "Shour_09", 60, "center");
    $grid->AddModelNonSearchable("10:00 AM", "Shour_10", 60, "center");
    $grid->AddModelNonSearchable("11:00 AM", "Shour_11", 60, "center");
    $grid->AddModelNonSearchable("12:00 PM", "Shour_12", 60, "center");
    $grid->AddModelNonSearchable("1:00 PM", "Shour_13", 60, "center");
    $grid->AddModelNonSearchable("2:00 PM", "Shour_14", 60, "center");
    $grid->AddModelNonSearchable("3:00 PM", "Shour_15", 60, "center");
    $grid->AddModelNonSearchable("4:00 PM", "Shour_16", 60, "center");
    $grid->AddModelNonSearchable("5:00 PM", "Shour_17", 60, "center");
    $grid->AddModelNonSearchable("6:00 PM", "Shour_18", 60, "center");
    $grid->AddModelNonSearchable("7:00 PM", "Shour_19", 60, "center");
    $grid->AddModelNonSearchable("8:00 PM", "Shour_20", 60, "center");
    $grid->AddModelNonSearchable("9:00 PM", "Shour_21", 60, "center");
    $grid->AddModelNonSearchable("10:00 PM", "Shour_22", 60, "center");
    $grid->AddModelNonSearchable("11:00 PM", "Shour_23", 60, "center");
   // $grid->AddModelNonSearchable("Average", "average", 60, "center");

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

