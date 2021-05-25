<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
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

/*$grid->AddSearhProperty("Date", "sdate","date", $report_date_format);
$grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));*/
$grid->AddModelCustomSearchable('Date', "sdate", 110, "center","date");
$grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));

$grid->AddSearhProperty("Skill", "skill_id",'select', ['*'=>'All']+$skill_list[$skill_type]);
$grid->AddModelNonSearchable('Skill Name', "skill_name", 105,"center");
$grid->AddModelNonSearchable('SMS Offered', "total_offered", 120,"center");
$grid->AddModelNonSearchable('SMS Served', "total_served", 120,"center");
$grid->AddModelNonSearchable('In KPI', "total_in_kpi", 80,"center");
$grid->AddModelNonSearchable('Total Served <br> Time (hh:mm:ss)', "total_serve_time", 150,"center");
$grid->AddModelNonSearchable('Wrap-up <br> Time (hh:mm:ss)', "total_wrapup_time", 150,"center");
//$grid->AddModelNonSearchable('Avg. Wrap-up <br>Time (sec)', "avg_wrap_up_time", 100,"center");
$grid->AddModelNonSearchable('Average Handling <br> Time (hh:mm:ss)', "aht", 120,"center");
$grid->AddModelNonSearchable('Total Wait <br> Time (hh:mm:ss)', "total_wait_time", 120,"center");
$grid->AddModelNonSearchable('Avg. Wait <br> Time (hh:mm:ss)', "avg_wait_time", 120,"center");
$grid->AddModelNonSearchable('Service Level % <br> (10 min)', "service_level", 100,"center");
$grid->AddModelNonSearchable('Max Wait <br> Time (hh:mm:ss)', "max_wait_time", 120,"center");
$grid->AddModelNonSearchable('Total <br>ICE Count', "ice_count", 75,"center");
$grid->AddModelNonSearchable('ICE Positive <br>Feedback', "ice_positive_count", 110,"center");
$grid->AddModelNonSearchable('ICE Negative <br>Feedback', "ice_negative_count", 110,"center");
$grid->show("#searchBtn");
?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
    var skill_list = JSON.parse('<?php echo json_encode($skill_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        //SetNewReportDateTimePicker('<?php //echo $report_date_format ?>//');
        SetNewReportDatePicker('<?php echo $report_date_format ?>');

        $("#skill_type").on('change', function(){
            var val = $(this).val();
            var option_str = '';

            if(val){
                option_str = '<option value="*">All</option>';
                $.each(skill_list[val], function(idx, item){
                    option_str += '<option value="'+idx+'">'+item+'</option>';
                });

                $('#skill_id').html(option_str);
                $('#skill_id').select2("val", "*");
                $('#skill_id').trigger('change');
            }
        });
    });
</script>

