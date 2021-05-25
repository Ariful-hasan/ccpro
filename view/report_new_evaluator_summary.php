<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->CustomSearchOnTopGrid = true;
$grid->multisearch = true;
$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;
$grid->footerRow = true;
$grid->userDataOnFooter = false;
if (!empty($report_restriction_days)) {
    $grid->DateRange = $report_restriction_days;
}
$grid->floatingScrollBar = true;

$grid->AddModelCustomSearchable('Date', "sdate", 100, "center", "date");
$grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));
$grid->AddSearhProperty('Evaluator:', 'agent_id', 'select', $agent_list);


$grid->AddModelNonSearchable("Evaluator ID", "evaluator_id", 100, "center");
$grid->AddModelNonSearchable("Evaluator Name", "evaluator_name", 100, "center");
$grid->AddModelNonSearchable("Total", "total_count", 100, "center");
$grid->AddModelNonSearchable("Inbound", "inbound", 100, "center");
$grid->AddModelNonSearchable("Outbound", "outbound", 100, "center");
$grid->AddModelNonSearchable("Web Chat", "webchat", 100, "center");
$grid->AddModelNonSearchable("SMS", "sms", 100, "center");
$grid->AddModelNonSearchable("Email", "email", 100, "center");
$grid->AddModelNonSearchable("PD", "pd", 100, "center");
$grid->show("#searchBtn");

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function () {
        $(document).on("click", "#cboxClose", function () {
            location.reload(true);
        });
        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });

</script>

