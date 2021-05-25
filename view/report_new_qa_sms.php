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

$grid->AddModelCustomSearchable('Date&Time', "sdate", 120, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Agent Score", "agent_score");
$grid->AddMultisearchOperator('agent_score');
$grid->SetDefaultValue("agent_score", '');


$grid->AddModelNonSearchable('Evaluator <br /> ID', "evaluator_id", 150,"center");
$grid->AddModelNonSearchable('Evaluator <br /> Name', "evaluator_name", 150,"center");
$grid->AddModelNonSearchable('Agent ID', "agent_id", 100,"center");
$grid->AddModelNonSearchable('Agent Name', "agent_name", 150,"center");
$grid->AddModelNonSearchable('SkillSet <br /> Name', "skill_name", 150,"center");
$grid->AddModelNonSearchable('Contact <br /> Date&Time', "sdate", 120,"center");
$grid->AddModelNonSearchable('Evaluation <br /> Date', "evaluation_time", 120,"center");
$grid->AddModelNonSearchable('Phone <br /> Number', "phone_number", 150,"center");
$grid->AddModelNonSearchable('Wrap-up <br /> Code', "wrap_up_code", 100,"center");
//$grid->AddModelNonSearchable('Form <br /> Total', "form_total", 100,"center");
$grid->AddModelNonSearchable('Agent <br /> Score', "agent_score", 100,"center");
$grid->AddModelNonSearchable('Form  <br />Total (In%)', "score_percentage", 100,"center");

foreach ($form_list as $data){
    $grid->AddModelNonSearchable($data->report_label, $data->fid, 180, "center");
}
$grid->show("#searchBtn");
?>

<script type="text/javascript">

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });
</script>

