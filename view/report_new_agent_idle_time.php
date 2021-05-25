<?php 
    include_once "lib/jqgrid.php";
    $grid = new jQGrid();
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


    $grid->AddSearhProperty("Date", "sdate", "date", $report_date_format);
    $grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime("+1 day")));

    $grid->AddSearhProperty("Agent", "agent_id",'select', $agent_list);
    //$grid->AddSearhProperty("Shift", "shift_code",'select', $shifts);

    
    $grid->AddModelNonSearchable('Date', "sdate", 150, "center");
    //$grid->AddModelCustomSearchable('Shift Type', "shift_code", 150, "center",'select',$shifts);
    $grid->AddModelCustomSearchable('Agent ID', "agent_id", 150, "center",'select',$agent_list,TRUE);
    $grid->AddModelNonSearchable('Agent Name', "agent_name", 150,"center");
    $grid->AddModelNonSearchable('Total<br>Idle Time', "idle_time", 150,"center");

    $grid->show("#searchBtn");

    ?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
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

