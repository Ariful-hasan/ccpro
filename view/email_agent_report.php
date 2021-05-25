<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 11/25/2018
 * Time: 4:55 PM
 */
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->shrinkToFit=false;
$grid->ShowDownloadButtonInTitle=true;
$grid->EmailMultipleDate=true;
$grid->floatingScrollBar=true;
if(!empty($report_restriction_days)){
    $grid->DateRange=$report_restriction_days;
}

$grid->AddModel("Customer ID", "customer_id", 100, "center");
$grid->AddModelCustomSearchable('Arrival Date\Time', "create_time", 110, "center","report-datetime");
$grid->SetDefaultValue("create_time", date($report_date_format." 00:00"), date($report_date_format." 00:00", strtotime('+1day')));
$grid->AddModelNonSearchable("Waiting Duration(mins)", "waiting_duration", 150, "center");
$grid->AddModelNonSearchable("Email ID", "ticket_id", 100, "center");
$grid->AddModelNonSearchable("Skill Set Name", "skill_name", 100, "center");
$grid->AddSearhProperty("Closed Reason Code", "did", "select", $did_options);
if (empty($isAgent)){
    $grid->AddSearhProperty("Agent", "agent_id", "select", $agent_list);
    $grid->SetDefaultValue("agent_id", $agent_id);
    $grid->AddSearhProperty("Skill", "skill_id", "select", $skill_list);
}
$grid->AddModelNonSearchable("Agent Name 1", "agent_1", 150,"center");
$grid->AddModelNonSearchable("Agent Name 2", "agent_2", 150,"center");
$grid->AddModelCustomSearchable('First Open Time', "first_open_time", 110, "center","report-datetime");
$grid->SetDefaultValue("first_open_time","");
//$grid->AddModel("Open Time", "last_update_time", 130, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->AddModelCustomSearchable('Open Time', "last_update_time", 110, "center","report-datetime");
//$grid->AddModel("Closed Time", "close_time", 130, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->AddModelCustomSearchable('Closed Time', "close_time", 110, "center","report-datetime");
$grid->AddModelCustomSearchable("In KPI", "in_kpi", 100,"center",'select', $in_kpi_list);
$grid->AddModelNonSearchable("Open Duration(mins)", "open_duration", 150,"center");
$grid->AddModelNonSearchable("Closed Reason Code", "disposition_id", 100,"center");
$grid->AddModel("Email Subject", "subject",180, "center");
$grid->AddModel("From Email", "from_email", 100, "center");
$grid->AddModelNonSearchable("Mail From", "created_for", 150,"center");
$grid->AddModelNonSearchable("Mail To", "mail_to", 150,'center');
$grid->AddModelNonSearchable("RS/TR", "rs_tr", 100,"center");
$grid->AddModelNonSearchable("Rescheduled Time", "reschedule_time", 130,"center");
$grid->AddModelNonSearchable("RS/TR Creation Time", "rs_tr_create_time", 130,"center");
$grid->AddSearhProperty("Contact Status", "status", "select", $status_options);
$grid->SetDefaultValue("status", $status);
$grid->AddModelNonSearchable("Contact Status", "emailStatus", 100,"center");
$grid->AddModelNonSearchable("Email From Customer", "customer_email_count", 100,"center");
$grid->AddModelNonSearchable("Phone", "phone", 100,"center");
//$grid->AddDownloadHiddenProperty('Comments');
$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
    var report_date_format = '<?php echo $report_date_format ?>';
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');

    });
    $("document").ready(function () {
        $("input[name='ms[first_open_time][from]']").val("");
        $("input[name='ms[first_open_time][to]']").val("");

        $("input[name='ms[close_time][from]']").val("");
        $("input[name='ms[close_time][to]']").val("");

        $("input[name='ms[last_update_time][from]']").val("");
        $("input[name='ms[last_update_time][to]']").val("");

        // $(".src-heading .btn-warning").on('click', function(){
        //     try{      
        //         if(!beforeFormSubmitEmail(180, "input[name='ms[create_time][from]']", "input[name='ms[create_time][to]']", "Arrival Date\Time")){
        //             return false;
        //         }
        //         if(!beforeFormSubmitEmail(180, "input[name='ms[first_open_time][from]']", "input[name='ms[first_open_time][to]']", "First Open Time"))
        //         {
        //             return false;
        //         }
        //         if(!beforeFormSubmitEmail(180, "input[name='ms[close_time][from]']", "input[name='ms[close_time][to]']", "Closed Time"))
        //         {
        //             return ;
        //         }
        //         if(!beforeFormSubmitEmail(180, "input[name='ms[last_update_time][from]']", "input[name='ms[last_update_time][to]']", "Open Time")){
        //             return;
        //         }
        //     }catch(e){
        //         console.log(e.message);
        //     }
        // });
    });

    
</script>