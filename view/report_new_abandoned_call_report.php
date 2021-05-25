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
//$grid->styleLastRow = TRUE;
$grid->footerRow = true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
$grid->loadComplete="showHideColumn";


/* ---------------------------View grid data by group based on column value----------------------------*/
//$grid->addGroup('sdate');

//$grid->AddSearhProperty("Date", "sdate","date", "Y-m-d");
//$grid->SetDefaultValue("sdate", date("Y-m-d"),date("Y-m-d"));
//$grid->AddSearhProperty("Date Format", "date_format",'select', $report_date_format_list);
//$grid->SetDefaultValue("date_format", $report_date_format);
$grid->AddSearhProperty("Skill", "skill_id","select", $skills);
$grid->AddSearhProperty("Hourly Abandoned", "hourly_abandoned","select", array("N"=>"Number","P"=>"Percent"));
$grid->AddSearhProperty("Sum Date", "sum_date","select", array("N"=>"No","Y"=>"Yes"));

$grid->AddModelCustomSearchable('Date', "sdate", 100, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format . " 00:00"), date($report_date_format . " 00:00", strtotime("+1 day")));
//$grid->SetDefaultValue("sdate", date("Y-m-d 0:00"),date("Y-m-d 23:00"));
$grid->AddModelNonSearchable('Skill', "skill_name", 100, "center");
$grid->AddModelNonSearchable('Total Calls', "calls_offered", 100, "center");
$grid->AddModelNonSearchable('Abandoned', "calls_abandoned", 100, "center");
$grid->AddModelNonSearchable('Percentage (%)', "abandoned_percentage", 100, "center");
for ($i = 0; $i <= 23; $i++){

    $am_pm = ($i < 12) ? ":00 AM" : ":00 PM";

    $grid->AddModelNonSearchable($i % 12 == 0 ? "12{$am_pm}" : ($i % 12) ."{$am_pm}","Shour_".sprintf("%02d",$i), 100, "center");
}


$grid->show("#searchBtn");
?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){

        showHideColumn();
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });

    function showHideColumn() {

        var grid_id = "<?php echo $grid->GetGridId(); ?>";
        myGrid = $(grid_id);

        sum_date = $("#sum_date").val();
        from_hour = new Date($(".srcMFrom").val());
        shour = from_hour.getHours();

        to_hour = new Date($(".srcMTo").val());
        ehour = to_hour.getHours();


        if (shour > 0){
            for(i = 0; i < shour; i++)
            {
                var hourName = "Shour_";
                hourName += (i < 10) ? "0"+i : i
                myGrid.jqGrid('hideCol', hourName);
            }
        }

        if (ehour < 24){
            for(i = ehour+1; i <= 23; i++)
            {
                var hourName = "Shour_";
                hourName += (i < 10) ? "0"+i : i
                myGrid.jqGrid('hideCol', hourName);
            }
        }


        (sum_date == "Y") ? myGrid.jqGrid('hideCol', "sdate") : myGrid.jqGrid('showCol', "sdate");

    }

</script>

