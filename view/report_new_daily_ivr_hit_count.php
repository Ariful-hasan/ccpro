<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowList = array(500);
$grid->rowNum = 500;
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

$grid->AddSearhProperty('Date', 'sdate', 'date', $report_date_format);
$grid->SetDefaultValue('sdate', date($report_date_format), date($report_date_format,strtotime("+1day")));
$grid->AddSearhProperty('IVR', 'ivr_id', 'select', $ivr_list);

$grid->AddModelNonSearchable('IVR Nodes', "service_title", 450,"center");
$grid->AddModelNonSearchable('Integration Info of Node', "integration_info", 250,"center");
$grid->AddModelNonSearchable('Total Count', "hit_count", 250,"center");
$grid->AddModelNonSearchable('Percentage', "percentage", 250,"center");
$grid->AddModelNonSearchable('Rank', "rank", 250,"center");

$grid->show("#searchBtn");
?>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        SetNewReportDatePicker('<?php echo $report_date_format ?>');
       /* $(".srcMTo ").hide();
        $(".srcMTo ").next().hide();*/
    });
</script>

