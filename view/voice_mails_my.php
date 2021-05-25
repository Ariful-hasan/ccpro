<style type="text/css">
#pl_close_button {
        background-color: #5c595a;
        float: left;
        width: 28px;
        height: 28px;
        text-align: center;
        color: #ffffff;
        border-right: 1px solid #858585;
        cursor: pointer;
        line-height: 28px;
}
</style>

<div id="player_container" style="display:none;padding:5px;">
        <div id="pl_close_button">&#10006;</div>
        <audio id="pl_audio_player" controls style="width:95%; float:left;">
                <source id="pl_audio_source" src="" type="audio/mp3">
                Your browser does not support the audio element.
        </audio>
<br />
</div>


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

    $grid->AddModel('Date time', "stop_time", 120, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
    $grid->SetDefaultValue("stop_time", date("Y-m-d 00:00:00", strtotime("-7 days")), date("Y-m-d H:i:s"));
    
    $grid->AddModel('CLI', "cli", 80, "center");    
    //$grid->AddModel('DID', "did", 80, "center");
    $grid->AddModelNonSearchable('Duration<br/>(h:m:s)', "duration", 80,"center");
    $grid->AddModelNonSearchable('Status', "status", 80,"center");
    //$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="The time of latest update of Status.">Update time</span>', "served_time", 120,"center");
    //$grid->AddModel('Agent ID', "agent_id", 80, "center");
    //$grid->AddModelNonSearchable('<span title="Nick name of the agent.">Nick name<span>', "nick", 100,"center");
    $grid->AddModelNonSearchable('Audio', "audio", 80,"center");
    //$grid->loadComplete = 'alert';//$grid->ReloadMethod();    
    
    include('view/grid-tool-tips.php');
    $tooltips = $tooltips_override['voice_mails'] + $tooltips;
    $grid->addModelTooltips($tooltips);
    
    $grid->show("#searchBtn");
    
?>


<script type="text/javascript">
var audioPlayer = null;
var audioSrc = null;
<?php
$_tburl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
$_tbport = $_SERVER['SERVER_PORT'];
$_tb_disp_port = ($_tburl == 'http' && $_tbport == 80 || $_tburl == 'https' && $_tbport == 443) ? '' : ":$_tbport";

echo "var audioSrcBase = '$_tburl://".$_SERVER['SERVER_NAME'].$_tb_disp_port.$this->url('task=vm&act=voice')."';";
?>

function playVMB(cid, ts)
{
        if (audioPlayer == null) {
                audioPlayer = document.getElementById('pl_audio_player');
                audioSrc = document.getElementById('pl_audio_source');
                audioPlayer.addEventListener('ended', <?php echo $grid->ReloadMethod();?>);
                $("#pl_close_button").click(function(){
                        audioPlayer.pause();
                        audioPlayer.currentTime = 0;
                        $("#player_container").hide();
                        //container.html("");
                });
        }

	audioSrc.src = audioSrcBase + '&cid='+cid+'&ts='+ts+'&op=A&ct='+Date.now();
        audioPlayer.load();
        audioPlayer.play();
        $("#player_container").show();
}

$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
setTimeout(<?php echo 'Grid_' . $grid->GetGridId(true).'_custom_reload';?>,500);
});
//setInterval(<?php echo 'Grid_' . $grid->GetGridId(true).'_custom_reload';?>,1000);
window.onbeforeunload = function(){};
</script>

