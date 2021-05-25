<?php 
    //include_once "lib/jqgrid.php";
    include_once "lib/jqgrid_report.php";
//    $grid = new jQGrid();
    $grid = new jQGridReport();
    $grid->url = isset($dataUrl) ? $dataUrl : "";
    $grid->width="auto";//$grid->minWidth = 800;
    $grid->height = "auto";//390;
    $grid->rowNum = 20;
    $grid->pager = "#pagerb";
    $grid->container = ".content-body";
    $grid->shrinkToFit = true;
    $grid->footerRow = false;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;
    $grid->floatingScrollBar=true;

    $grid->AddModelCustomSearchable('Date', "start_date", 200, "center","report-datetime");
    $grid->SetDefaultValue("start_date", date($report_date_format." 00:00"),date($report_date_format." 00:00",strtotime("+1 day")));

//    $grid->AddModelNonSearchable('Start Date','start_date',100,'center');
    $grid->AddModelNonSearchable('Start Time','start_time',100,'center');
    $grid->AddModelNonSearchable('Call Type','calling_type',100,'center');
    $grid->AddModelNonSearchable('Calling Party','callerid',100,'center');
    $grid->AddModelNonSearchable('Called Party','callto',100,'center');
    $grid->AddModelNonSearchable('Duration(sec)','talk_time',100,'center');
    $grid->AddModelNonSearchable('End Date','end_date',100,'center');
    $grid->AddModelNonSearchable('End Time','end_time',100,'center');

    $grid->AddSearhProperty("Agent", "agent_id",'select', $agent_list);
    $grid->AddModelNonSearchable('Agent ID','agent_id',100,'center');
    $grid->AddModelNonSearchable('Agent Name','name',100,'center');
//    $grid->AddModelNonSearchable('Customer Segment','customer_segment',100,'center');
    $grid->AddModelNonSearchable('Call Status','disc_cause',100,'center');

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
        SetNewReportDateTimePicker(date_format, start_default_date+' 00:00', end_default_date+' 00:00');
    });

</script>