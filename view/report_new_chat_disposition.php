<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->footerRow = TRUE;
$grid->CustomSearchOnTopGrid = true;
$grid->multisearch = true;
$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

$grid->AddSearhProperty("Disposition", "disposition_id", "select", $dp_options);
$grid->AddSearhProperty("Date Time", "sdate", "report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format . " 00:00"), date($report_date_format . " 00:00", strtotime('+1day')));

$grid->AddModelNonSearchable("Disposition", "title", 180, "center");
$grid->AddModelNonSearchable("Record count", "numrecords", 180, "center");
$grid->AddModelNonSearchable("%", "percentage", 180, "center");

$grid->show("#searchBtn");

?>
<script type="text/javascript">
   <?Php /*?>

    $(function () {
        $(document).on("click", "#cboxClose", function () {
            location.reload(true);
        });
        var selector_prefix = ".gs";
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>', selector_prefix);
    });
 <?Php */ ?>


   var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
   $(function(){
       $(document).on("click","#cboxClose",function () {
           location.reload(true);
       });
       SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
   });
</script>

