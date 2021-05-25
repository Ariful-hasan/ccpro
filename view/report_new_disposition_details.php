<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->shrinkToFit=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->floatingScrollBar=true;

$grid->AddModel("Date Time", "start_time", 50, "center", ".date", $report_date_format." H:i:s", $report_date_format." H:i:s", true, "report-datetime");
$grid->SetDefaultValue("start_time", date($report_date_format." 00:00"),date($report_date_format." 23:59"));
$grid->AddSearhProperty("Agent", "agent_id", "select",$agents);
$grid->AddModel("Template", "tempid", 60, "center", "", "", "", true, "select", $template_options);
$grid->AddSearhProperty("Contact Number", "contact_no", "text");
$grid->AddModel("Disposition", "did", 80, "center", "", "", "", true, "select", $dp_options);

$grid->AddModelNonSearchable("Contact Number", "contact_id", 40, "center");
//$grid->AddModelNonSearchable("Account Number", "account_id", 80, "center");
$grid->AddModelNonSearchable("Agent", "agent_id", 40, "center");
$grid->AddModelNonSearchable("Auth by", "auth_by", 40, "center");
$grid->AddModelNonSearchable("Note", "note", 125, "center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    $(document).on("click","#cboxClose",function () {
        location.reload(true);
    });

    SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
});

</script>
