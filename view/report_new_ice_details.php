<?php 
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
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

$grid->AddModelCustomSearchable('Date&Time', "sdate", 110, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Agent", "agent_id",'select', $agent_list);
$grid->AddSearhProperty("Skill Type", "skill_type",'select', $skill_type_list);
$grid->SetDefaultValue("skill_type", $skill_type);
$grid->AddSearhProperty("Skill", "skill_id",'select', ['*'=>'All']+$skill_list[$skill_type]);
$grid->AddModelNonSearchable('Skill Name', "skill_name", 105,"center");
$grid->AddModelNonSearchable('MSISDN (880)', "msisdn_880", 100,"center");
$grid->AddModelNonSearchable('MSISDN', "cli", 75,"center");
$grid->AddModelNonSearchable('Agent ID', "agent_id", 100,"center");
$grid->AddModelNonSearchable('Agent Name', "agent_name", 100,"center");
$grid->AddModelNonSearchable('Wrap-up Description', "title", 100,"center");
$grid->AddModelNonSearchable('ICE Feedback', "ice_feedback", 70,"center");
$grid->AddModelNonSearchable('ICE Value', "ice_value", 70,"center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
	var skill_list = JSON.parse('<?php echo json_encode($skill_list); ?>');
    $(function(){
        var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
        getSkillTypeDropDown();
    });
</script>