<?php 
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport('skillset_summary_report');
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
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

// $grid->AddSearhProperty("Date Format", "date_format",'select', $report_date_format_list);
// $grid->SetDefaultValue("date_format", $report_date_format);
$grid->AddModelCustomSearchable('Date&Time', "sdate", 110, "center","report-datetime");
$grid->SetDefaultValue("sdate", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddSearhProperty("Type", "report_type",'select', $report_type_list);
$grid->SetDefaultValue("report_type", $report_type);
$grid->AddSearhProperty("Skill Type", "skill_type",'select', $skill_type_list);
$grid->SetDefaultValue("skill_type", $skill_type);
$grid->AddSearhProperty("Skill", "skill_id",'select', ['*'=>'All']+$skill_list[$skill_type]);
// $grid->AddSearhProperty("Category", "category",'select', $skill_category_list);

$grid->AddModelNonSearchable('Year', "syear", 70,"center");
$grid->AddModelNonSearchable('Month', "smonth", 70,"center");
$grid->AddModelNonSearchable('Hour', "shour", 70,"center");
$grid->AddModelNonSearchable('Minute', "sminute", 70,"center");
$grid->AddModelNonSearchable('Quarter No.', "quarter_no", 70,"center");
$grid->AddModelNonSearchable('Half Hour', "half_hour", 70,"center");
$grid->AddModelNonSearchable('Hour:Minute', "hour_minute", 90,"center");

$grid->AddModelNonSearchable('Skill Name', "skill_name", 105,"center");
$grid->AddModelNonSearchable('Calls Answered', "calls_answerd", 110,"center");
$grid->AddModelNonSearchable('ICE Message Sent', "ice_sent", 110,"center");
$grid->AddModelNonSearchable('Total ICE Count', "ice_count", 110,"center");
$grid->AddModelNonSearchable('ICE %', "ice_percentage", 75,"center");
$grid->AddModelNonSearchable('ICE Positive Feedback', "ice_positive_count", 150,"center");
$grid->AddModelNonSearchable('ICE Negative Feedback', "ice_negative_count", 150,"center");
$grid->AddModelNonSearchable('ICE Score', "ice_score", 75,"center");

$grid->show("#searchBtn");

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
    var skill_list = JSON.parse('<?php echo json_encode($skill_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
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

