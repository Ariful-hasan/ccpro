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
$grid->footerRow = true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

$grid->AddModelCustomSearchable('Date', "sdate", 100, "center","date");
$grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));
$grid->AddSearhProperty("Skill Type", "skill_type",'select', $skill_type_list);
$grid->SetDefaultValue("skill_type", $skill_type);

$grid->AddModelNonSearchable('Wrap-up Description', "title", 100,"center");
$grid->AddModelNonSearchable('ICE Negative Count', "dis_ice_negative", 70,"center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    $(function(){
        var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });
</script>