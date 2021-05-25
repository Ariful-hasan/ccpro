<?php
include_once "lib/jqgrid_report.php";
$intervals = [
    'daily'=> 'Daily', 'monthly'=> 'Monthly'
];

$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 200;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->footerRow = false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

// $grid->AddSearhProperty("Date Format", "date_format",'select', $report_date_format_list);
//$grid->SetDefaultValue("date_format", $report_date_format);
/* $grid->AddModelCustomSearchable("Date", "sdate", 100, "center",'report-datetime');
$grid->SetDefaultValue("sdate", date(REPORT_DATE_FORMAT." 00:00"),date(REPORT_DATE_FORMAT." 00:00")); */

$grid->AddModelCustomSearchable('Date & Time', "sdate", 200, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty('Type','interval','select',$intervals);
$grid->AddSearhProperty("Skill Type", "skill_type",'select', $skill_type_list);
$grid->SetDefaultValue("skill_type", $skill_type);
$grid->AddSearhProperty('Skill','skill_id','select', ['*'=>'All']+$skill_list[$skill_type]);

foreach ($skill_list[$skill_type] as $skill_id => $skill_name){
    $grid->AddModelNonSearchable($skill_name, $skill_id, 100, "center");
}

$grid->show("#searchBtn");
// echo "<p class='text-danger'>***For Hourly interval end date will be the same as start date and time range will be 00:00:00 to 23:59:59</p>";

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });

</script>

