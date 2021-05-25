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
$grid->shrinkToFit=false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
if ($sms_enabled) {
    $grid->ShowSMSBtnWithReload = true;
}
$grid->DownloadFileName=$pageTitle;


$grid->AddSearhProperty("Skill", "skillid", "select", $skill_options);
$grid->SetDefaultValue("skillid", $skillid);
//$grid->AddModel("Join Date", "account_open_date", 120,"center","date","Y-m-d H:i:s","Y-m-d H:i:s",true,"date");
$grid->AddModel("Start time", "start_time", 125, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
//$grid->SetDefaultValue("start_time", date("Y-m-d 00:00", strtotime("-1 month")), date("Y-m-d 23:59"));
//$grid->SetDefaultValue("start_time", date("Y-m-d 00:00"));
$grid->SetDefaultValue("start_time", $date_stime, $date_etime);
$grid->AddModelNonSearchable("Answer time", "answer_time", 125, "center");
$grid->AddModelNonSearchable("Stop time", "stop_time", 125, "center");

//$grid->AddModel("Campaign", "campaign_id", 100,"center");
//$grid->AddModel("Lead", "lead_id", 100,"center");
$grid->AddModel("Customer ID", "customer_id", 100,"center");

//$grid->AddModel("Agent", "agent_id", 80, "center");
//$grid->SetDefaultValue("agent_id", $cli);
//$grid->AddModelNonSearchable("Nick name", "nick", 80, "center");
$grid->AddModelNonSearchable("Call from", "call_from", 80, "center");

if ($sms_enabled) {
    $grid->AddModelNonSearchable('<input type="checkbox" name="cidSelectorAll" id="cidSelectorAll" value="Y">', "chkbox_sndsms", 30, "center");
}
$grid->AddModel("Call to", "dial_number", 100,"center");

$grid->AddModel("Dial# SL", "dial_index", 60,"center");
$grid->AddModel("Try# SL", "dial_count", 60,"center");
//$grid->SetDefaultValue("dial_number", $did);
//$grid->AddModelNonSearchable("Skill", "skill_name", 80,"center");

$grid->AddModelNonSearchable("Status", "status", 80,"center");
$grid->AddModelNonSearchable("Talk time", "talk_time", 90,"center");
$grid->AddModelNonSearchable("Duration", "duration", 90,"center");

$grid->AddModelNonSearchable("Disc. cause", "disc_cause", 50,"center");
$grid->AddModelNonSearchable("Disc. party", "disc_party", 50,"center");
//$grid->AddModelNonSearchable("Trunk", "trunkid", 40,"center");
$grid->AddModel("Callid", "callid", 135, "center");
$grid->SetDefaultValue("callid", $callid);
//$grid->AddModelNonSearchable("Log", "sip_log", 80,"center");
//$grid->AddModelNonSearchable("Audio", "audio", 80,"center");


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
                    //$(window).scrollTop( $("#audio-player-container").offset().top);
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

<?php if ($sms_enabled){ ?>
    try{
        $('#cidSelectorAll').click(function (event) {
            event.stopPropagation();
            if ($(this).is(":checked")){
                $(this).prop('checked', true);
                $('.cid-selector-chk').prop('checked', true);
            }else {
                $(this).prop('checked', false);
                $('.cid-selector-chk').prop('checked', false);
            }
        });

        $('#send_sms_btn').click(function(){
            if($(".ui-jqgrid-btable input[name='cid_selector[]']:checked").length) {
                $("#template_chose").click();
            }else {
                alert('Please select Call To');
            }
        });

        $(".smstplate").colorbox({
            iframe:true, width:"800", height:"450"
        });
    }catch(e){
        console.log(e.message);
    }
<?php } ?>
});
window.onbeforeunload = function(){};
var audio = null;
</script>
<?php if ($sms_enabled){ ?>
<a id="template_chose" href="index.php?task=cdr&act=smstemplate" class="smstplate" style="display: none;">0</a>
<?php } ?>