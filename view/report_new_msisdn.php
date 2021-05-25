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
$grid->footerRow = TRUE;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;
$grid->setGridID('dateTest');

// $grid->AddSearhProperty("Date", "sdate","date", REPORT_DATE_FORMAT." H:00");

$grid->AddModelCustomSearchable('Date & Time', "sdate", 200, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Skill Type", "skill_type",'select', $skill_type_list);
$grid->SetDefaultValue("skill_type", $skill_type);
$grid->AddSearhProperty("Skill", "skill_id",'select', ['*'=>'All']+$skill_list[$skill_type]);

$grid->AddModelNonSearchable('MSISDN ( 880 )', "msisdn_880", 200, "center");
$grid->AddModelNonSearchable('MSISDN', "msisdn", 200, "center");
$grid->show("#searchBtn");

?>

<script type="text/javascript">
    var skill_list = JSON.parse('<?php echo json_encode($skill_list); ?>');
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
        getSkillTypeDropDown();
    });
</script>