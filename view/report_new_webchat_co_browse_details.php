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
$grid->footerRow = false;
$grid->userDataOnFooter = false;
if (!empty($report_restriction_days)) {
    $grid->DateRange = $report_restriction_days;
}
$grid->floatingScrollBar = true;

$grid->AddModelCustomSearchable('Date&Time', "sdate", 120, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty('Agent ID:', 'agent_id', 'select', $agent_list);


$grid->AddModelNonSearchable("Agent ID", "agent_id", 100, "center");
$grid->AddModelNonSearchable("Agent Name", "agent_name", 120, "center");
$grid->AddModelNonSearchable("Customer Name", "customer_name", 120, "center");
$grid->AddModelNonSearchable("Customer Number", "customer_number", 120, "center");
$grid->AddModelNonSearchable("Call ID", "callid", 120, "center");
$grid->AddModelNonSearchable("Requested By", "request_type", 120, "center");
$grid->AddModelNonSearchable("Agent URL", "agent_url", 200, "center");
$grid->AddModelNonSearchable("Customer URL", "customer_url", 200, "center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function () {
        $(document).on("click", "#cboxClose", function () {
            location.reload(true);
        });
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
    });

</script>

