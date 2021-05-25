<?php 

include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->shrinkToFit = false;
//$grid->loadComplete="onGridLoad";

$grid->AddSearhProperty("Date Time", "start_time", "datetime");
$grid->SetDefaultValue("start_time", date("Y-m-d 00:00"));

$grid->AddModelNonSearchable('Agent ID', "agent_id", 80, "center");
$grid->AddModelNonSearchable('Nick name', "nick", 80, "center");
$grid->AddModelNonSearchable('Calls <br>offered', "calls_offered", 60, "center");
$grid->AddModelNonSearchable('Calls <br>answered', "calls_in_ans", 60,"center");
$grid->AddModelNonSearchable('Calls <br>bounced', "bounce_count", 60,"center");
$grid->AddModelNonSearchable('% of call <br>answered', "ans_percent", 60,"center");
$grid->AddModelNonSearchable('Avg talk time <br>(h:m:s)', "avg_talk_time", 100,"center");
$grid->AddModelNonSearchable('Avg ACD hold time<br>(h:m:s)', "avg_hold_time", 100,"center");
$grid->AddModelNonSearchable('Calls [O]<br>attempted', "calls_out_attempt", 60, "center");
$grid->AddModelNonSearchable('Calls [O]<br>reached', "calls_out_reached", 60,"center");
$grid->AddModelNonSearchable('Avg talk time [O] <br>(h:m:s)', "avg_talk_time_o", 100,"center");
//$grid->AddModelNonSearchable('Avg ACW time <br>(h:m:s)', "avg_acw_time", 100,"center");
$grid->AddModelNonSearchable('Avg handling time <br>(h:m:s)', "avg_handling_time", 100,"center");
$grid->AddModelNonSearchable('Total staffed time <br>(h:m:s)', "staff_time", 100,"center");
$grid->AddModelNonSearchable('Total handling time <br>(h:m:s)', "total_handling_time", 100,"center");
$grid->AddModelNonSearchable('Total bounced time <br>(h:m:s)', "bounce_time", 100,"center");
$grid->AddModelNonSearchable('Total ACW time <br>(h:m:s)', "acw_time", 100,"center");
$grid->AddModelNonSearchable('Total AUX time <br>(h:m:s)', "aux_time", 100,"center");
$grid->AddModelNonSearchable('Total Avail Time <br>(h:m:s)', "avail_time", 100,"center");

include('view/grid-tool-tips.php');

if (isset($aux_messages) && is_array($aux_messages)){
    foreach ($aux_messages as $aux){
        if ($aux->aux_code > 11 && $aux->aux_code <= 20) {
        $auxindex = 'aux_'.$aux->aux_code. '_time';
        $grid->AddModelNonSearchable($aux->message." <br>(h:m:s)", $auxindex, 100, "center");
        $tooltips[$auxindex] = "Auxillary busy time for '".$aux->message."'";
        }
    }
    //$grid->AddModelNonSearchable('AUX Others <br>(h:m:s)', "aux_other", 100, "center");
}

$tooltips = $tooltips_override['agent_report'] + $tooltips;
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
/*
function onGridLoad()
{
    //$('[data-toggle="tooltip"]').tooltip({trigger:'click'});
    $('[data-toggle="popover"]').popover();
}
*/

$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
