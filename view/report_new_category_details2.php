<?php 
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = false;
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
$grid->AddSearhProperty("Skill Type", "skill_type",'select', $skill_type_list);
$grid->SetDefaultValue("skill_type", $skill_type);
$grid->AddSearhProperty("Skill", "skill_id",'select', ['*'=>'All']+$skill_list[$skill_type]);
$grid->AddSearhProperty("Hangup Initiator", "hangup_initiator",'select', $hangup_ini_list);
$grid->AddSearhProperty("Disposition","dispositions_ids",'select', [''=>'---- Select -----']+$disposition_ids);

// $grid->AddModelNonSearchable('Year', "syear", 70,"center");
// $grid->AddModelNonSearchable('Month', "smonth", 70,"center");
// $grid->AddModelNonSearchable('Hour', "shour", 70,"center");
// $grid->AddModelNonSearchable('Minute', "sminute", 70,"center");
// $grid->AddModelNonSearchable('Skill ID', "skill_id", 65, "center");
$grid->AddModelNonSearchable('Skill Name', "skill_name", 105,"center");
// $grid->AddModelCustomSearchable('Category', "category", 105,"center",'select',$skill_category_list,TRUE);
$grid->AddModelNonSearchable('MSISDN <br>(880)', "msisdn_880", 100,"center");
$grid->AddModelNonSearchable('MSISDN', "cli", 100,"center");
// $grid->AddModelNonSearchable('Call ID', "callid", 120,"center");
$grid->AddModelNonSearchable('Abandon <br>Flag', "abandon_flag", 75,"center");
$grid->AddModelNonSearchable('Abandon <br>CLI', "abandon_cli", 75,"center");
$grid->AddModelNonSearchable('Agent ID', "agent_id", 100,"center");
$grid->AddModelNonSearchable('Time In <br>Queue (sec)', "hold_in_q", 100,"center");
$grid->AddModelNonSearchable('Ring Time <br>(sec)', "ring_time", 100,"center");
$grid->AddModelNonSearchable('Talk Time <br>(sec)', "talk_time", 100,"center");
$grid->AddModelNonSearchable('Hold Time <br>(sec)', "agent_hold_time", 100,"center");
// $grid->AddModelNonSearchable('Delay Between <br>Calls (sec)', 'delay_between_call', 110,"center");
$grid->AddModelNonSearchable('Wrap-up Time <br>(sec)', "wrap_up_time", 100,"center");
$grid->AddModelNonSearchable('Agent Handling <br>Time (sec)', "agent_handling_time", 100,"center");
$grid->AddModelNonSearchable('Total <br>Wrap-up', "disposition_count", 100,"center");
$grid->AddModelNonSearchable('Wrap-up <br>Description', "custom_title", 100,"center");
$grid->AddModelNonSearchable('QRC Tagging', "qrc_tagging", 110,"center");
// $grid->AddModelNonSearchable('QRC Type <br>Tagging', "qrc_type_tagging", 110,"center");
$grid->AddModelNonSearchable('Responsible Party', "responsible_party", 110,"center");
$grid->AddModelNonSearchable('Short Caller', "short_call", 110,"center");
$grid->AddModelNonSearchable('Repeat Caller', "repeated_call_flag", 110,"center");
$grid->AddModelNonSearchable('Hangup <br>Initiator (disc_party)', "disc_party", 110,"center");
$grid->AddModelNonSearchable('ICE <br>Feedback', "ice_feedback", 110,"center");

$grid->show("#searchBtn");

?>

<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Disposition</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var skill_list = JSON.parse('<?php echo json_encode($skill_list); ?>');
    
    function showDispostionModal(data){
        // console.log(data);
        var arr = data.split(",");
        // console.log(arr);
        var str ="<table>";
        $.each(arr, function(idx, item){
            str +="<tr><td>"+item+"</td></tr>";
        });
        str +="<table>";
        // console.log(str);
        $('#exampleModalLong').find('.modal-body').html(str);
        $('#exampleModalLong').modal('show');
    }
    $(function(){
        var date_format_list = JSON.parse('<?php echo json_encode($report_date_format_list); ?>');
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        SetNewReportDateTimePicker('<?php echo $report_date_format ?>');
        getSkillTypeDropDown();
    });
</script>