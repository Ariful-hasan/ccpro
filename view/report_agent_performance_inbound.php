<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit=false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;

$grid->AddSearhProperty("Date", "tstamp", "datetime");
$grid->SetDefaultValue("tstamp", date("Y-m-d"));

$grid->AddModelNonSearchable("Agent<br />ID", "agent_id", 80, "center");
$grid->AddModelNonSearchable("Nick<br />name", "agent_name", 80, "left");
$grid->AddModelNonSearchable("Staffed<br>time<br>(h:m:s)", "staffed_time", 80,"center");
$grid->AddModelNonSearchable("Ring<br>time<br>(h:m:s)", "aring_time", 80,"center");
$grid->AddModelNonSearchable("Talk<br>time<br>(h:m:s)", "talk_time", 80,"center");
$grid->AddModelNonSearchable("ACW<br>time<br>(h:m:s)", "acw_time", 80,"center");
$grid->AddModelNonSearchable("Bounced<br>call time<br>(h:m:s)", "missed_call_time", 80,"center");
//$grid->AddModelNonSearchable("Break<br>time<br>(h:m:s)", "pause_time", 80,"center");

include('view/grid-tool-tips.php');
if (isset($aux_messages) && is_array($aux_messages)){
    foreach ($aux_messages as $aux){
        if ($aux->aux_code > 11 && $aux->aux_code <= 20) {
        $auxindex = 'p'.$aux->aux_code;
        $grid->AddModelNonSearchable($aux->message." <br>(h:m:s)", $auxindex, 70, "center");
        $tooltips[$auxindex] = "Auxillary busy time for '".$aux->message."'";
        }
    }
    //$grid->AddModelNonSearchable('AUX Others <br>(h:m:s)', "aux_other", 100, "center");
}

$grid->AddModelNonSearchable("Avail<br>time<br>(h:m:s)", "available_time", 80,"center");
$grid->AddModelNonSearchable("Utilization<br>%", "utilization", 80,"center");
$grid->AddModelNonSearchable("Occupancy<br>%", "occupancy", 80,"center");
$grid->AddModelNonSearchable("ASA<br>(h:m:s)", "speed_of_ans", 80,"center");
$grid->AddModelNonSearchable("Longest<br>ring<br>(h:m:s)", "longest_ring", 80,"center");
$grid->AddModelNonSearchable("Longest<br>call<br>duration<br>(h:m:s)", "longest_call", 80,"center");
$grid->AddModelNonSearchable("Shortest<br>call<br>duration<br>(h:m:s)", "shortest_call", 80,"center");
$grid->AddModelNonSearchable("AHT<br>(h:m:s)", "avg_handling_time", 80,"center");

$grid->AddModelNonSearchable("Offered<br>calls", "calls_offered", 80, "center");
$grid->AddModelNonSearchable("Ans.<br>calls", "answered_calls", 80, "center");
//$grid->AddModelNonSearchable("Service<br>level in<br>sec", "service_time_queue", 80,"center");
$grid->AddModelNonSearchable("Ans.<br>&lt;30<br>sec", "answered_lt30", 80,"center");
$grid->AddModelNonSearchable("Ans.<br>30-60<br>sec", "answered_30to60", 80,"center");
$grid->AddModelNonSearchable("Ans.<br>60-90<br>sec", "answered_60to90", 80,"center");
$grid->AddModelNonSearchable("Ans.<br>90-120<br>sec", "answered_90to120", 80,"center");
$grid->AddModelNonSearchable("Ans.<br>&gt;120<br>sec", "answered_gt120", 80,"center");
$grid->AddModelNonSearchable("Total<br>hold<br>time<br>(h:m:s)", "hold_time", 80,"center");
$grid->AddModelNonSearchable("Hold<br>frequency", "hold_count", 80,"center");
//$grid->AddModelNonSearchable("Calls<br>transf.<br>in", "transf_in", 80,"center");
//$grid->AddModelNonSearchable("Calls<br>transf.<br>out", "transf_out", 80,"center");

//include('view/grid-tool-tips.php');
$tooltips = $tooltips_override['agent_perf_inbound'] + $tooltips;
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>