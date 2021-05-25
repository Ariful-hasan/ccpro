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
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
$grid->footerRow = false;
$grid->userDataOnFooter = false;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}
$grid->floatingScrollBar=true;

$grid->AddModelCustomSearchable('Date', "sdate", 100, "center","date");
$grid->SetDefaultValue("sdate", date($report_date_format), date($report_date_format, strtotime('+1day')));
$grid->AddSearhProperty('Agent:', 'agent_id', 'select', $agent_list);
$grid->AddModelNonSearchable("Agent ID", "agent_id", 100, "center");
$grid->AddModelNonSearchable("Agent Name", "agent_name", 100, "center");
//$grid->AddModelNonSearchable("Skill", "skill_name", 100, "center");
$grid->AddModelNonSearchable("Activity", "activity", 100, "center");
$grid->show("#searchBtn");
/*echo "<p class='text-danger'> * Difference between start time and end time should be less than or equal 24 hour. <br>
** If your selected time range exceeds 24 hour then start time will be your selected start time and end time will be start time + 24 hour.</p>"; */

?>

<script type="text/javascript">
    var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');

    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
        SetNewReportDatePicker('<?php echo $report_date_format ?>');
    });


    ////var date_format_list = JSON.parse('<?php ////echo json_encode($report_date_format_list); ?>////');
    //$(function(){
    //    $(document).on("click","#cboxClose",function () {
    //        location.reload(true);
    //    });
    //    SetNewReportDatePicker('<?php //echo $report_date_format ?>//');
    //});


    //var date_format_list = JSON.parse('<?php //echo json_encode($report_date_format_list); ?>//');
    //var date_format = "<?php //echo $report_date_format ?>//";
    //
    //$(function(){
    //    $(document).on("click","#cboxClose",function () {
    //        location.reload(true);
    //    });
    //
    //    SetNewReportDateTimePicker('<?php //echo $report_date_format ?>//');
    //});
    //
    //function beforeFormSubmit(){
    //    var start_date = $('.srcMFrom').val();
    //    var end_date = $('.srcMTo').val();
    //
    //    var start = start_date.split(' ');
    //    var start_date = start[0];
    //    var start_time = start[1];
    //
    //    if ('d/m/Y' == date_format){
    //        var start_day = start_date.split('/')[0];
    //        var start_month = start_date.split('/')[1];
    //        var start_year = start_date.split('/')[2];
    //    }else if ('m/d/Y' == date_format){
    //        var start_day = start_date.split('/')[1];
    //        var start_month = start_date.split('/')[0];
    //        var start_year = start_date.split('/')[2];
    //    }
    //
    //
    //    var d = new Date(start_year, start_month -1 , start_day, start_time.split(':')[0], start_time.split(':')[1]);
    //
    //    var end = end_date.split(' ');
    //    var end_date = end[0];
    //    var end_time = end[1];
    //
    //    if ('d/m/Y' == date_format){
    //        var end_day = end_date.split('/')[0];
    //        var end_month = end_date.split('/')[1];
    //        var end_year = end_date.split('/')[2];
    //    }else if ('m/d/Y' == date_format){
    //        var end_day = end_date.split('/')[1];
    //        var end_month = end_date.split('/')[0];
    //        var end_year = end_date.split('/')[2];
    //    }
    //
    //
    //    var d2 = new Date(end_year, end_month -1 , end_day, end_time.split(':')[0], end_time.split(':')[1]);
    //
    //    if ((d2.getTime() - d.getTime()) / (1000*60*60*24) > 1) {
    //        alert('Provide date range within 1 day');
    //        return false;
    //    }
    //
    //    return true;
    //}

</script>

