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

// $grid->AddModelNonSearchable('Call ID', "callid", 100,"center");
$grid->AddModelNonSearchable('MSISDN', "cli", 75,"center");
$grid->AddModelNonSearchable('DID', "did", 100,"center");
$grid->AddModelNonSearchable('ICE Feedback Text', "sms_text", 70,"center");

$grid->show("#searchBtn");
echo "**MS Excel 2016 and above compatible"
?>

<script type="text/javascript">
	var skill_list = JSON.parse('<?php echo json_encode($skill_list); ?>');
    $(function(){
        var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
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