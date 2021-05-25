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
$grid->AddSearhProperty("Skill", "skill_id",'select', array('*' => 'All') + $skill_list[$skill_type]);
$grid->AddSearhProperty("Agent", "agent_id",'select', $agent_list);

$grid->AddModelNonSearchable('Callid', "callid", 110,"center");
$grid->AddModelNonSearchable('Skill Name', "skill_name", 105,"center");
$grid->AddModelNonSearchable('Campaign Id', "customer_id", 100,"center");
$grid->AddModelNonSearchable('Agent Id', "agent_id", 100,"center");
$grid->AddModelNonSearchable('Agent Name', "agent_name", 100,"center");
$grid->AddModelNonSearchable('Answer Time', "answer_time", 100,"center");
$grid->AddModelNonSearchable('Call From', "call_from", 75,"center");
$grid->AddModelNonSearchable('MSISDN', "dial_number", 75,"center");
$grid->AddModelNonSearchable('Dial Count', "dial_count", 75,"center");
$grid->AddModelNonSearchable('Ring Time', "ring_time", 75,"center");
$grid->AddModelNonSearchable('Talk Time (sec)', "talk_time", 100,"center");
$grid->AddModelNonSearchable('Disconnect Cause', "disc_cause", 130,"center");
$grid->AddModelNonSearchable('Disconnect Cause MSG', "disc_cause_text", 180,"center");
$grid->AddModelNonSearchable('Status', "status", 75,"center");
$grid->AddModelNonSearchable('Disposition', "custom_title", 75,"center");


// $grid->AddModelNonSearchable('Hangup Initiator', "disc_party", 110,"center");
//$grid->AddModelNonSearchable('Disconnect Party', "disc_party", 110,"center");

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
    });
</script>