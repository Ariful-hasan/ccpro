<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
$grid->loadComplete="showHideColumn";


$grid->AddModelCustomSearchable('Date', "sdate", "200", "center", "date");
$grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));
$grid->AddModelCustomSearchable('Agent ID', "agent_id", "200", "center",'select',$agent_list,TRUE);
$grid->AddModelCustomSearchable('Shift Type', "shift_code", "200", "center",'select',$shifts);
$grid->AddSearhProperty("Sum Date", "sum_date",'select', array("N"=>"No","Y" => "Yes"));
$grid->AddModelNonSearchable('Avg.<br>Talk Time', "avg_talk_time", "200","center");
$grid->AddModelNonSearchable('Avg.<br>Hold Time', "hold_time", "220","center");
$grid->AddModelNonSearchable('AVg.<br>ACW Time', "aux_11_time", "220","center");
$grid->AddModelNonSearchable('Avg.<br>Handling Time', "avg_call_handling_time", "220","center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
    $(function(){
        showHideColumn();
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });

    function showHideColumn() {

        var grid_id = "<?php echo $grid->GetGridId(); ?>";
        myGrid = $(grid_id);

        sum_date = $("#sum_date").val();

        (sum_date == "Y") ?  myGrid.jqGrid('hideCol', "sdate") : myGrid.jqGrid('showCol', "sdate");
    }

</script>

