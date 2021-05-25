<?php 
    include_once "lib/jqgrid.php";
    $grid = new jQGrid();
    //$grid->caption = "Agent List";
    //echo "2013-08-01 10:02 to: 2013-09-30 10:02";
    $grid->url = isset($dataUrl) ? $dataUrl : "";
    $grid->width="auto";//$grid->minWidth = 800;
    $grid->height = "auto";//390;
    $grid->rowNum = 20;
    $grid->pager = "#pagerb";
    $grid->container = ".content-body";
    //$grid->hidecaption=false;
    //$grid->shrinkToFit=false;
    $grid->CustomSearchOnTopGrid=true;
    $grid->multisearch=true;
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName=$pageTitle;
    
    $grid->AddModel('Log time', "start_time", 110, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
    //$grid->SetDefaultValue("start_time", date("Y-m-d 00:00", strtotime("-1 month")), date("Y-m-d 23:59"));
    $grid->SetDefaultValue("start_time", date("Y-m-d 00:00"));
    
    $grid->AddModel('Agent ID', "agent_id", 60, "center");
    $grid->AddModelNonSearchable('Nick name', "agent_name", 80,"center");
    
    if ($type == 'inbound'){
        $grid->AddModel("Caller ID", "cli", 80, "center");
        $grid->AddModelNonSearchable("DID", "did", 80, "center");
    } elseif ($type == 'omanual'){
        $grid->AddModelNonSearchable("Skill", "skill_id", 80, "center");
        $grid->AddModel("Call to", "callto", 80, "center");
    }

    $grid->AddModelCustomSearchable('Answered', "is_answer", 60, "center",'select',array('*'=>"Select", 'Y'=>"Yes", "N" => 'No'));
    
    if ($type == 'inbound'){
        $grid->AddModelNonSearchable('Ring<br> time', "ring_time", 60, "center");
        $grid->AddModelNonSearchable('Service<br> time', "duration", 65,"center");
    } elseif ($type == 'omanual') {
        $grid->AddModelNonSearchable('Talk<br> time', "talk_time", 70, "center");
    }
    
    $grid->AddModelNonSearchable('Hold time', "hold_time", 65,"center");
    $grid->AddModelNonSearchable('Hold<br> count', "hold_count", 50,"center");
    $grid->AddModelNonSearchable('ACW time', "acw_time", 60,"center");
    if ($type == 'inbound'){
        $grid->AddModelNonSearchable("Disc<br> party", "disc_party", 50, "center");
    }
    $grid->AddModel('Call ID', "callid", 125,"center");
    $grid->AddModelNonSearchable('Audio', "audio", 70,"center");
    $grid->AddModelNonSearchable('SIP<br> log', "sip", 40,"center");
    $grid->AddModelNonSearchable('Service Quality', "service_quality", 120,"center");

    include('view/grid-tool-tips.php');
    $tooltips = $tooltips_override['report_cdr_agent'] + $tooltips;
    $grid->addModelTooltips($tooltips);
    
    $grid->show("#searchBtn");

?>
<style type="text/css">
audio {
    width: 100%;
}
</style>
<script type="text/javascript">
var playing_id = null;
function playAudio(audioSource, myCallId, btnObj){
	if(typeof audioSource != 'undefined' && audioSource != null && audioSource != ""){
        var reload = false; 
        var callid = myCallId;
        if (playing_id==null || playing_id.attr('id')!=callid) reload = true;
        var aobj = $(btnObj);
        var txt = aobj.html();
        if (txt == '<i class="fa fa-play-circle"></i>') {
            if (reload) {
                aobj.html('Wait..');
                if($("#audio-player-container").hasClass("hidden")){
                    $("#audio-player-container").show();
                    $("#audio-player-container").removeClass("hidden");	
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                }
                var player= $('<audio id="player" controls> <source src="'+audioSource+'" type="audio/mpeg" />Your browser does not support the audio element.</audio>');
                //var player= $('<audio id="player" controls> <source src="'+msg+'" type="audio/wav" />Your browser does not support the audio element.</audio>');
                $("#gsmplayer").html("").append(player);
                playing_id = aobj;
                $('#player')[0].play();
                playing_id.attr('id', myCallId);
                $('.playAudioFile').html('<i class="fa fa-play-circle"></i>');
                aobj.html('<i class="fa fa-stop"></i>');
                loadAudioListener(playing_id);
            } else {
                playing_id = aobj;
                $('#player')[0].play();
                if($("#audio-player-container").hasClass("hidden")){
                    $("#audio-player-container").show();
                    $("#audio-player-container").removeClass("hidden");
                }
                $('.playAudioFile').html('<i class="fa fa-play-circle"></i>');
                playing_id.html('<i class="fa fa-stop"></i>');
            }
        } else {
        	aobj.html('<i class="fa fa-play-circle"></i>');
            	$('#player')[0].pause();
            	$("#audio-player-container").hide();
    		$("#audio-player-container").addClass("hidden");
        }
	}
}

function loadAudioListener(playing_id){
	$('#player').on('ended', function() {
    	playing_id.html('<i class="fa fa-play-circle"></i>');
    	playing_id = null;
    });
}

$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);

	$('#player').on('ended', function() {
    	playing_id.html('<i class="fa fa-play-circle"></i>');
    	playing_id = null;
    });

	var appendTxt = '<div class="col-xs-12">';
    	appendTxt += '<div id="audio-player-container" class="panel panel-info hidden">';
    	appendTxt += '<div class="panel-body" style="height:31px; padding:0;">';
    	appendTxt += '<div id="gsmplayer"></div></div></div></div>';
	$('.gs-grid-serach').append(appendTxt);
});
window.onbeforeunload = function(){};
var audio = null;
</script>