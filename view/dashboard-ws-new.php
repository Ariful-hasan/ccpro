<html>
<head>
<title><?php echo $pageTitle;?></title>

<script type="text/JavaScript" src="js/jquery.min.js"></script>

<script type="text/javascript" src="js/jquery-ui.min.js"></script>

<script src="js/contextMenu/jquery.ui.position.js" type="text/javascript"></script>
<script src="js/contextMenu/jquery.contextMenu.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/contextMenu/jquery.contextMenu.css">

<script src="ccd/wss.gPlexCCDashboardWS.js" type="text/javascript"></script>

<?php 

        $show_agent_id_in_dashboard = isset($this->agent_id_in_dashboard) && $this->agent_id_in_dashboard ? true : false;
        if(UserAuth::getDBSuffix()=="AB") {
                $show_agent_id_in_dashboard = true;
        }
        
        $wss = 'ws';
        $ws_port = $account_info->ws_port + 1;

        if(isset($_SERVER['HTTPS'])) {
                if ($_SERVER['HTTPS'] == "on") {
                        $wss = 'wss';
                        $ws_port = $account_info->ws_port;
                }
        }
        
        $is_expanded_db = "Y";

        $isInfoAllow = true;
        $isSupervisor = UserAuth::hasRole('supervisor');
        if(UserAuth::getDBSuffix()=="AA" || UserAuth::getDBSuffix()=="AC") {
                $isInfoAllow = true;
        }
?>
<script type="text/javascript">
var isSupervisor = <?php if ($isSupervisor) echo 'true'; else echo 'false';?>;
var allCalls = 0;
var inQueue = 0;
var inService = 0;
var mhtDBHead = 0;
var agStaff = 0;
var agIdle = 0;
var natPingTimeout = null;
var natPingTimes = 0;
var isDBLoggedIn = false;
var dbLoggedUrl = "<?php echo $this->url("task=agent&act=add-dashboard-logged-in");?>";
var strSeatStatus = "hidden unreachable hold-out idle ring dial active activeo no-agent-talk chat-only chat-busy chat-call-busy enable";
var dimvalue = '<span class="dim-value">0</span>';
var wsUri = "<?php echo $wss;?>://<?php echo $_SERVER['SERVER_NAME'] == '169.55.61.34' ?  'ccportal.gplex.com' : $_SERVER['SERVER_NAME']; echo ':' . $ws_port;?>/chat";
var numSeats = <?php echo is_array($seats) ? count($seats) : '0';?>;

var obj = null;

//-----Dduplicate window will not open -- start------//
var dt = new Date();
var cts = Math.floor(dt.getTime()/1000);
$.cookie("isDashBoardAlreadyOpen",cts);

window.setInterval(function(){
        var dt = new Date();
        var cts = Math.floor(dt.getTime()/1000);
        $.cookie("isDashBoardAlreadyOpen",cts);
}, 15000);
//-----Dduplicate window will not open -- end------//

$(document).ready(function(){

        $(document).bind("contextmenu",function(e){
                e.preventDefault();
        });
        $(document).disableSelection();

        var tstamp = new Date().getTime();
        var is_agentinfo_visible = false;

        $(".seat div.agent").hover(
                function (event) {

                        var tPosX = event.pageX - 5;
                        var tPosY = event.pageY + 20;
                        $('#agent_queue_info').css({top: tPosY, left: tPosX});
                        agent_queue_info_display = $(this).attr('agent');
                        tstamp++;

                        if (!is_agentinfo_visible) {
                                is_agentinfo_visible = true;
                                $('#agent_queue_info').html('<div class="loading">Loading....</div>');
                                $('#agent_queue_info').css('display','block');

                                obj.MemberInfo(agent_queue_info_display, tstamp);
                        }

                }, function () {
                        $('#agent_queue_info').css('display','none');
                        is_agentinfo_visible = false;
                }
        );

});

var max_calls_in_q = 6;
var agent_queue_info_display = null;
var queue_detail_info_display = null;
var ivr_detail_info_display = null;
var netErrTry = 0;
var netErrRetryTime = 0;
var available_services = new Array();
var available_agents = new Array();
var available_aux_msgs = new Array();
var aux_out_ids = [];
var objSkills = [];
var objAgents = [];
var objAuxs = [];
var maxLanguageCount = <?php if ($languages) echo count($languages); else echo '0';?>;
//
var objLanguages = [];
var colorSequence = ["blue","purple","green","red"];
objLanguages = jQuery.parseJSON('<?php echo json_encode($languages);?>');

var lang_n_color = {};
var classes = "";
var classes2 = "";
for (i=0; i<objLanguages.length; i++) {
        //
        var l = objLanguages[i];
        var color = typeof colorSequence[i]!='undefined' ? colorSequence[i] : "";
        lang_n_color[l.lang_key] = color;
        classes += "."+color+",";
        classes2 += color+" ";
}

var dashboarduser = '<?php echo UserAuth::getCurrentUser();?>';

<?php
$colorSeq = array("blue","purple","green","red");
$language_n_color = array();
foreach($languages as $key=>$lang) {
        $language_n_color[$lang->lang_key] = $colorSeq[$key];
}

?>

<?php if (is_array($skills)):?>
objSkills = jQuery.parseJSON('<?php echo json_encode($skills);?>');
<?php foreach ($skills as $skill):?>
available_services['<?php echo $skill->skill_id;?>'] = '<?php echo $skill->short_name;?>';
<?php endforeach;?>
<?php endif;?>

<?php if (is_array($agents)):?>
objAgents = jQuery.parseJSON('<?php echo json_encode($agents);?>');
<?php
foreach ($agents as $agent):
?>
available_agents['A<?php echo $agent->agent_id;?>'] = '<?php echo $show_agent_id_in_dashboard ? $agent->agent_id . ' ' . substr($agent->nick, 0, 7) : $agent->nick;?>';
<?php
endforeach;?>
<?php endif;?>

<?php if (is_array($aux_messages)):?>
<?php foreach ($aux_messages as $amsg):?>
<?php 

if ($amsg->aux_type == 'I') {
        $msg = $amsg->message . ' [I]';
} else {
        $msg = $amsg->message;
?>
aux_out_ids.push(<?php echo $amsg->aux_code;?>);
<?php
}
?>
available_aux_msgs['<?php echo $amsg->aux_code;?>'] = '<?php echo $msg;?>';
objAuxs = jQuery.parseJSON('<?php echo json_encode($aux_messages);?>');
<?php endforeach;?>
<?php endif;?>
//
objSeats = jQuery.parseJSON('<?php echo json_encode($seats);?>');

$(document).ready(function(){

try {
        startTimer();
        obj = new gPlexCCDashboardWSApi();
        obj.loadSettings(wsUri, dashboarduser, $.cookie("wsCCSeatId"), $.cookie("wsWebKey"));
        obj.setNumLoggedIn(<?php echo UserAuth::numDBLoggedIn();?>);
        obj.Connect();
} catch (ex) {
        alert("Warning1: " + ex.message);
}

});

function secondsToMMSS(diff) {
        //diff = diff*60;
        if(diff == 0 || diff > 20000) return diff;

        var secs_diff = diff % 60;
        secs_diff = secs_diff<10 ? "0" + secs_diff : secs_diff;
        diff = Math.floor(diff / 60);
        //var mins_diff = diff % 60;
        mins_diff = diff;
        mins_diff = mins_diff < 10 ? "0" + mins_diff : mins_diff;
        //
        var lap = mins_diff + ":" +secs_diff;

        return lap;
}

function getClientTime(offset)
{
        var i = parseInt(Date.now());
        return i - (offset * 1000);
}

function GetNetRetryMsg(curTs, retryTs)
{
        var retryTime = Math.floor((retryTs-curTs)/1000);
        var txt_timeout = '';
        if (retryTime < 60) {
                //
                if (retryTime <= 1) {
                        //
                        txt_timeout = 'now';
                } else {
                        txt_timeout = 'in ' + retryTime + ' seconds';
                }
        } else {
                var secs_diff = retryTime % 60;
                var mins_diff = Math.floor(retryTime / 60);
                txt_timeout = 'in';
                if (mins_diff > 0) {
                        if (mins_diff == 1) txt_timeout = txt_timeout + ' 1 minute';
                        else txt_timeout = txt_timeout+' '+mins_diff+' minutes';
                }
                if (secs_diff > 0) {
                        if (secs_diff == 1) txt_timeout = txt_timeout + ' 1 second';
                        else txt_timeout = txt_timeout+' '+secs_diff+' seconds';
                }
        }
        return "Connection to the server is lost, Trying " + txt_timeout + " ...";
}

function WebSockConnectError()
{
        netErrTry++;
        netErrRetryTime = 0;
        var retryTime = 0;
        var curTs = Date.now();

        if (netErrTry <= 3) {
                //
                retryTime = 6;
        } else {
                retryTime = 20;
        }

        netErrRetryTime = curTs + retryTime*1000;

        if (refreshBy != 'SYSTEM') {
                $("#overlay").find("span").html(GetNetRetryMsg(curTs, netErrRetryTime));
                $("#overlay").show();
        }
}

function NatPing()
{
        obj.Ping();
        natPingTimes++;
        natPingTimeout = setTimeout(NatPing, 20000);
}

var _DashBoard = {
        Close: function(ts) {
                obj.Close();
                window.close();
        },
        UpdateDBTime: function(ts) {

                if (netErrTry > 0) {
                        $("#overlay").hide();
                }
                netErrTry = 0;

                var date = new Date(ts*1000);
                var hours = date.getHours();
                var minutes = date.getMinutes();
                var ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // the hour '0' should be '12'
                minutes = minutes < 10 ? '0'+minutes : minutes;
                //
                $("#db_hhmm").html(hours + ':' + minutes);
                $("#db_ampm").html(ampm);
        },
        PopulateOBSkill: function(ob) {

                var call_count = 0;
                var curTs = Date.now();
                var fmtTs = '';

                if (ob.call_count > 0) {
                        call_count = ob.call_count;
                        ob.max_time = ob.max_time + ob.event_change_time;
                        ob.avg_time = ob.avg_time + ob.event_change_time;
                }

                var avg_time = getClientTime(ob.avg_time);
                var max_time = getClientTime(ob.max_time);

                $('#obAvgTsOB').text(avg_time);
                $('#obMaxTsOB').text(max_time);
                $("#cntTCOB").text(call_count);

                if (call_count != 0) {
                        avg_time = TimeDiff(curTs, avg_time);
                        fmtTs = secondsToMMSS(avg_time);
                        $("#atOB").html(fmtTs);
                        max_time = TimeDiff(curTs, max_time);
                        fmtTs = secondsToMMSS(max_time);
                        $("#mtOB").html(fmtTs);

                        $("#cntOB").html(call_count);
                        $('#isOBTsCountOB').text('1');
                } else {
                        $("#atOB").html(dimvalue);
                        $("#mtOB").html(dimvalue);
                        $("#cntOB").html(dimvalue);
                        $('#isOBTsCountOB').text('0');
                }
        },

        PopulateIvr: function(ivr) {

                var call_count = 0;
                var curTs = Date.now();
                var fmtTs = '';

                if (ivr.call_count > 0) {
                        call_count = ivr.call_count;
                        ivr.max_time = ivr.max_time + ivr.event_change_time;
                        ivr.avg_time = ivr.avg_time + ivr.event_change_time;
                }

                var avg_time = getClientTime(ivr.avg_time);
                var max_time = getClientTime(ivr.max_time);

                $('#ivAvgTsI0').text(avg_time);
                $('#ivMaxTsI0').text(max_time);
                $("#cntTCI0").text(call_count);

                if (call_count != 0) {
                        avg_time = TimeDiff(curTs, avg_time);
                        fmtTs = secondsToMMSS(avg_time);
                        $("#atI0").html(fmtTs);
                        max_time = TimeDiff(curTs, max_time);
                        fmtTs = secondsToMMSS(max_time);
                        $("#mtI0").html(fmtTs);

                        $("#cntI0").html(call_count);
                        $('#isIvTsCountI0').text('1');
                } else {
                        $("#atI0").html(dimvalue);
                        $("#mtI0").html(dimvalue);
                        $("#cntI0").html(dimvalue);
                        $('#isIvTsCountI0').text('0');
                }
        },
        PopulateSkill: function(skill_id, skill) {

                var total_ciq = skill.ciq_call_count_0 + skill.ciq_call_count_1;
                var total_cis = skill.cis_call_count_0 + skill.cis_call_count_1;
                var curTs = Date.now();
                var fmtTs = '';

                var avg_time = getClientTime(skill.avg_time);
                var max_time = getClientTime(skill.max_time);

                $('#qAvgTs'+skill_id).text(avg_time);
                $('#qMaxTs'+skill_id).text(max_time);

                if (skill.ciq_call_count_0 > 0) {
                        $("#inqLang"+skill_id).find("span.lang0").addClass("blue");
                } else {
                        $("#inqLang"+skill_id).find("span.lang0").removeClass("blue");
                }

                if (skill.ciq_call_count_1 > 0) {
                        $("#inqLang"+skill_id).find("span.lang1").addClass("purple");
                } else {
                        $("#inqLang"+skill_id).find("span.lang1").removeClass("purple");
                }

                if (skill.cis_call_count_0 > 0) {
                        $("#insLang"+skill_id).find("span.lang0").addClass("blue");
                } else {
                        $("#insLang"+skill_id).find("span.lang0").removeClass("blue");
                }

                if (skill.cis_call_count_1 > 0) {
                        $("#insLang"+skill_id).find("span.lang1").addClass("purple");
                } else {
                        $("#insLang"+skill_id).find("span.lang1").removeClass("purple");
                }


                if (total_ciq != 0) $("#inq"+skill_id).html(total_ciq);
                else $("#inq"+skill_id).html(dimvalue);

                if (total_cis != 0) $("#srv"+skill_id).html(total_cis);
                else $("#srv"+skill_id).html(dimvalue);

                $("#cntInQ"+skill_id).html(total_ciq);
                $("#cntInSrv"+skill_id).html(total_cis);


                if (total_ciq > 0) {
                        avg_time = TimeDiff(curTs, avg_time);
                        fmtTs = secondsToMMSS(avg_time);
                        $("#at"+skill_id).html(fmtTs);
                        max_time = TimeDiff(curTs, max_time);
                        fmtTs = secondsToMMSS(max_time);
                        $("#mt"+skill_id).html(fmtTs);
                        $('#isQTsCount' + skill_id).text('1');
                } else {
                        $("#at"+skill_id).html(dimvalue);
                        $("#mt"+skill_id).html(dimvalue);
                        $('#isQTsCount' + skill_id).text('0');
                }
        },
        PopulateSeat: function(seat_id, seat) {

                if (seat_id.length != 3) {
                        return;
                }
                
                if (seat.status == 'U') {
                        $("#ag_"+seat_id).text('0');
                        $("#seat_"+seat_id).removeClass(strSeatStatus + " show").addClass("hidden");
                        $('#seat_'+seat_id+' div.status').html("");
                        return;
                }
                
                var agent_name = '';
                var aindex = 'A' + seat.agent_id;
                agent_name = (typeof available_agents[aindex]=='undefined') ? seat.agent_id : available_agents[aindex];

                if (isSupervisor && agent_name == seat.agent_id && typeof available_agents[aindex]=='undefined') {
                        if (numSeats > 25 || seat.agent_id.length == 4) {
                                $("#ag_"+seat_id).text('0');
                                $("#seat_"+seat_id).removeClass(strSeatStatus + " show").addClass("hidden");
                                return;
                        }
                }
                
                if (seat.agent_id.length != 4) {
                        $("#ag_"+seat_id).text('0');
                        if (seat.status == 'X') {
                                $("#seat_"+seat_id).removeClass(strSeatStatus).addClass("enable");
                        } else if (seat.status == 'U') {
                                $("#seat_"+seat_id).removeClass(strSeatStatus);
                        } else if (seat.status == 'T' || seat.status == 'S' || seat.status == 'O') {
                                $("#seat_"+seat_id).removeClass(strSeatStatus).addClass("no-agent-talk");
                        } else if (seat.status == 'N') {
                                $("#seat_"+seat_id).removeClass(strSeatStatus).addClass("unreachable");
                        }

                        $('#seat_'+seat_id+' div.language .lang0').removeClass("blue purple");
                        $('#seat_'+seat_id+' div.language .lang1').removeClass("blue purple");

                        $('#seat_'+seat_id+' div.status').html("");
                        return;
                }

                //if (seat.status == 'I') {
                if (aux_out_ids.indexOf(seat.aux_code) >= 0) {
                        $("#ag_"+seat_id).text('1');
                } else {
                        $("#ag_"+seat_id).text('2');
                }

                var status = "";
                var cssClass = "idle";

                if (seat.status == 'I') {
                        status = "Idle";
                        cssClass = "idle";
                } else if (seat.status == 'B') {
                        cssClass = "hold-out";
                        status = (typeof available_aux_msgs[seat.aux_code]=='undefined') ? 'Busy' : available_aux_msgs[seat.aux_code];
                } else if (seat.status == 'R') {
                        cssClass = "ring";
                } else if (seat.status == 'D') {
                        cssClass = "dial";
                } else if (seat.status == 'S') {
                        if (seat.chat_count > 0) {
                                if (seat.call_count > 0) {
                                        cssClass = "chat-call-busy";
                                } else {
                                        cssClass = "chat-busy";
                                }
                        } else {
                                cssClass = "active";
                        }
                } else if (seat.status == 'O') {
                        cssClass = "activeo";
                } else if (seat.status == 'U') {
                        cssClass = "";
                } else if (seat.status == 'C') {
                        cssClass = "chat-only";
                } else if (seat.status == 'N') {
                        cssClass = "unreachable";
                }

                var skill_name = '';

                try {
                        if (seat.srv_id.length > 0) {
                                skill_name = (typeof available_services[seat.srv_id]=='undefined') ? seat.srv_id : available_services[seat.srv_id];
                        }
                } catch (e) {}

                skill_name = (typeof skill_name!='undefined' && skill_name.length>0) ? skill_name : '';

                var lap = seat.event_change_time;

                var ccnt = seat.call_count+seat.chat_count-1;

                if (ccnt > 0) {
                        skill_name = skill_name + ' +' + ccnt;
                }

                $('#seat_'+seat_id).removeClass(strSeatStatus).addClass("show " + cssClass);
                $('#seat_'+seat_id+' div.status').html(agent_name +'<br>'+skill_name+status+'<br>(<span class="ts-seat"><span class="ts-fmt-lap">'+secondsToMMSS(lap)+'</span><span class="hidden ts-lap">'+getClientTime(lap)+'</span></span>)');
                for(var _j=0; _j < maxLanguageCount; _j++ ) {
                //
                        var lang_index = 'language_' + _j;
                        try {
                                if (seat[lang_index].length == 2) {
                                        var lang_key = seat[lang_index];
                                        if($('#seat_'+seat_id+' div.language .lang .'+lang_n_color[lang_key]).length==0) {
                                                //$('#seat_'+seat_id+' div.language .lang'+_j).addClass(lang_n_color[lang_key]);
                                                
                                                if (lang_key == seat.srv_language) {
                                                        $('#seat_'+seat_id+' div.language .lang'+_j).addClass(lang_n_color[lang_key] + ' active');
                                                } else {
                                                        $('#seat_'+seat_id+' div.language .lang'+_j).removeClass('active');
                                                        $('#seat_'+seat_id+' div.language .lang'+_j).addClass(lang_n_color[lang_key]);
                                                }
                                                
                                        }
                                } else {
                                        $('#seat_'+seat_id+' div.language .lang'+_j).removeClass("blue purple");
                                }
                        } catch (e) {
                        }
                }
        }
}

function ucfirst(str) {
  str += '';
  str = str.toLowerCase();
  var f = str.charAt(0).toUpperCase();
  return f + str.substr(1);
}


function TimeDiff(curTs, preTs)
{
        return Math.ceil((curTs-preTs)/1000);
}

var refreshTimer = 0;
var maxRefreshTimer = 60*60;
var maxWebPingTime = 4*60;
var curWebPingTime = maxWebPingTime;
var refreshBy = '';
function startTimer() {
        var sid = '';
        var sts = 0;
        var fmtTs = '';
        var curTs = Date.now();
        allCalls = 0;
        inQueue = 0;
        inService = 0;
        mhtDBHead = 0;

        refreshTimer++;
        if(refreshTimer >= maxRefreshTimer) {
                refreshTimer=0;
                if (obj.isRetryNeeded()) {
                        refreshBy = 'SYSTEM';
                        window.location.href=window.location.href;
                }
                return;
        }
        if(refreshTimer >= curWebPingTime) {
                curWebPingTime = curWebPingTime + maxWebPingTime;                
                $.ajax({
                	type: "POST",
                        url: "web_ping.php"
                });
        }

        if (netErrTry > 0) {
                $("#overlay").find("span").html(GetNetRetryMsg(curTs, netErrRetryTime));
                if (curTs > netErrRetryTime) {
                        try {
                                var _cws = new WebSocket(wsUri);
                                _cws.onopen = function () {
                                        window.location.href=window.location.href;
                                };
                                _cws.onerror = function (error) {
                                        WebSockConnectError();
                                };
                                _ws.onclose = function (event) {
                                        //console.log("WS-Conn-Close");
                                };
                        } catch (evt) {
                        }
                }
                setTimeout("startTimer()",1000);
                return;
        }

        $( "span.q-ts-count" ).each(function( index ) {
                sid = $(this).attr('id').substring(10, 12);

                inQueue += parseInt($('#cntInQ' + sid).text());
                inService += parseInt($('#cntInSrv' + sid).text());
                allCalls = inQueue + inService;

                if ($( this ).text() == '1') {
                        sts = $('#qAvgTs' + sid).text();
                        //sts++;
                        sts = TimeDiff(curTs, sts);

                        //$('#qAvgTs' + sid).text(sts);
                        fmtTs = sts > 0 ? secondsToMMSS(sts) : dimvalue;
                        $("#at"+sid).html(fmtTs);
                        sts = $('#qMaxTs' + sid).text();
                        //sts++;
                        sts = TimeDiff(curTs, sts);
                        if (sts>mhtDBHead) mhtDBHead = sts;
                        //$('#qMaxTs' + sid).text(sts);
                        fmtTs = sts > 0 ? secondsToMMSS(sts) : dimvalue;
                        $("#mt"+sid).html(fmtTs);
                } else {
                        $("#at"+sid).html(dimvalue);
                        $("#mt"+sid).html(dimvalue);
                }
        });
        sid = 'I0';
        $( "span.iv-ts-count" ).each(function( index ) {
                //allCalls += parseInt($('#cntTC' + sid).text());
                if ($( this ).text() == '1') {

                        sts = $('#ivAvgTs'+sid).text();
                        sts = TimeDiff(curTs, sts);
                        fmtTs = sts > 0 ? secondsToMMSS(sts) : dimvalue;
                        $("#at"+sid).html(fmtTs);

                        sts = $('#ivMaxTs' + sid).text();
                        sts = TimeDiff(curTs, sts);
                        fmtTs = sts > 0 ? secondsToMMSS(sts) : dimvalue;
                        $("#mt"+sid).html(fmtTs);
                } else {
                        $("#at"+sid).html(dimvalue);
                        $("#mt"+sid).html(dimvalue);
                }
        });
        sid = 'OB';
        $( "span.ob-ts-count" ).each(function( index ) {
                //allCalls += parseInt($('#cntTC' + sid).text());
                if ($( this ).text() == '1') {
                        sts = $('#obAvgTs'+sid).text();
                        sts = TimeDiff(curTs, sts);
                        fmtTs = sts > 0 ? secondsToMMSS(sts) : dimvalue;
                        $("#at"+sid).html(fmtTs);

                        sts = $('#obMaxTs' + sid).text();
                        sts = TimeDiff(curTs, sts);
                        fmtTs = sts > 0 ? secondsToMMSS(sts) : dimvalue;
                        $("#mt"+sid).html(fmtTs);
                } else {
                        $("#at"+sid).html(dimvalue);
                        $("#mt"+sid).html(dimvalue);
                }
        });
        $( "span.ts-seat" ).each(function( index ) {
                sts = $( this ).find("span.ts-lap").text();
                sts = TimeDiff(curTs, sts);
                $( this ).find("span.ts-fmt-lap").text(secondsToMMSS(sts));
        });
        agStaff = 0;
        agIdle = 0;
        $( "span.agent-status" ).each(function( index ) {
                sts = parseInt($( this ).text());
                if (sts>0) {
                        agStaff++;
                        if (sts == 1) agIdle++;
                }
        });
        //$("#sum_calls").html(allCalls + ':' + inQueue + ':' + inService);
        $("#sum_calls").html(inQueue + ':' + inService);
        $("#sum_mht").html(secondsToMMSS(mhtDBHead));
        $("#sum_staff").html((agStaff-agIdle) + ':' + agIdle);
        setTimeout("startTimer()",1000);
}
</script>


<style type="text/css">
*{
        padding: 0px;
        margin: 0px;
}
.agent{
        padding-left:20px;
        font-weight:bold;
        cursor:pointer;
}
#seats {
        clear: left;
}
.number{
        /*padding-left: 20px;*/
        /*background:url(images/call.png) no-repeat 10% 0%;*/
}
#seats .seat{
        /*background:url(images/user.png) no-repeat top center;*/
        color: #222;
        background-repeat: no-repeat;
        background-position: 3 6;
        /*background-color: #eee;*/
        margin: 5px;
        padding: 2px 2px 10px 2px;
        border-color:#e1e1e1 #bbbbbb #bbbbbb #e1e1e1;
        border-style:solid;
        border-width:1px;
        background-color: #c7d6e9;/*#eaeaea;*/

        /*height: 40px;*/

        width: 128px;
        float:left;
        font-size:9px;
        display:block;
}
.agentpic {
        float:left;
        width: 45px;
        height: 50px;
        font-size: 19px;
        text-align: right;
        padding-top: 20px;
}
.agentpic.serving {
        /*cursor: pointer;*/
}
.seat-text{
        float:left;
        padding: 0px;
        margin:0px;
        /*margin: 2px 0px 0px 22px;*/
        margin-top: 2px;
        width: 74px;
        height: 52px;
        border-left: 1px solid #c7d6e9;/*2px dashed #eaeaea;*/
        border-right: 1px solid #c7d6e9;/*2px dashed #eaeaea;*/
        font-family:verdana;
        overflow: hidden;
}

html>body .seat-text{
        /*margin: 2px 0px 0px 50px;*/
}
html>body #seats .seat{
        /*height: 53px;*/
        height: 60px;
}
#seats .seat .agent{
        padding: 0px;
        margin: 0px;
}
#seats .seat .status{
        min-height: 12px;
}

#seats .seat .idle{
}
#seats div.seat.dial{
        background-image:url(image/agent_st_dial.gif);
}
#seats .seat.hidden{
        display: none;
}
#seats .seat.empty{
        background-image:url(image/seat_empty.gif);
}
#seats .seat.enable{
        background-image:url(image/seat_enable.gif);
}
#seats .seat.no-agent-talk{
        background-image:url(image/head_phone.gif) !important;
}
#seats .seat.active{
        background-image:url(image/agent_talking.gif);
}
#seats .seat.activeo{
        background-image:url(image/agent_talking_out.gif);
}

#seats .seat.idle{
        background-image:url(image/agent_idle.gif);
}
#seats .seat.ring{
        background-image:url(image/agent_ringing.gif);
}
#seats .seat.hold{
        background-image:url(image/agent_st_hold.gif);
}
#seats .seat.hold-out{
        background-image:url(image/agent_st_hold_out.gif);
}
#seats .seat.unreachable{
        background-image:url(image/tn_delete.gif);
}
#seats .seat.chat-only {
        background-image:url(image/agent_chat.gif);
}
#seats .seat.chat-busy {
        background-image:url(image/agent_chat_busy.gif);
}
#seats .seat.chat-call-busy {
        background-image:url(image/agent_call_chat_busy.gif);
}

span.hidden, div.hidden {
        display: none;
}

#seats .seat.hide{
        /*background-image:url(images/agent_st_hold.gif);*/
        display: none;
}

#seats .seat.show{
        /*background-image:url(images/agent_st_hold.gif);*/
        display: block;
}


.loading {

}
.queue_calls_count{
        font-weight: bold;
}
.numbers{
        padding-top: 6px;
}
.queueinfo{
        font-family:verdana;
        font-size: 11px;
        color:white;font-weight:bold;text-align:center;
}
#queues .queue, #queues .ivr{
        height: 150px;
        width: 179px;
        margin: 1px;
        float:left;
        font-size:9px;
}

#queues{
        height: 130px;
        /*width: 100%;*/
        /*float:left;*/
        text-align:center;
        border: 0px solid red;
}

#ivrs .ivr{
        height: 100px;
        width: 90px;
        margin: 1px;
        padding: 22px 2px 2px 2px;
        border: 1px solid gray;
        float:left;
        font-size:9px;
}

#ivrs{
        height: 200px;
        width: 350px;
        float:left;
        margin-left:20px;
        border: 0px solid gray;
        clear:right;
}
.heading{
        font-weight:bold;
        font-size:15px;
        clear:both;
        border-top: 3px dotted #EEAAAA;
        border-bottom: 3px dotted #EEAAAA;
        text-align:center;
        padding: 10px 0px 10px 0px;
        color: #FF2323;
}
.seatinfo{
        text-align: center;
        padding: 1px;
        margin: 0px;
        font-weight:bold;
}
#agent_queue_info, #queue_detail_info{
        /*position:fixed;*/
        position:absolute;
        background: #E0ECFF;
        border: 5px solid #C3D9FF;
        font-size: 11px;
        font-family: verdana;
}
.popup-user-name, .popup-queue-name{
        display:block;
        text-align:left;
        font-weight:bold;
        margin: 1px;
        padding: 1px;
        padding-left: 18px;

}
.popup-user-name{
        background: #ABCABC url(images/user.png) no-repeat left center;
}
.popup-queue-name{
        background: #ABCABC url(images/queue.png) no-repeat left center;
}
.queuemoreinfo{
        text-align: right;
        padding: 0px;
        cursor: pointer;
        font-size:9px;
        /*
        background:url(images/queue_menu_top.gif) no-repeat 0 0;
        */
}
.queuemoreinfo a {
        text-decoration: none;
}
.agqinfo {
        border: 0px dotted blue;
        background: #ABCDAB;
        padding:2px;
        margin: 1px;
}
.agqinfo span{
        text-align:left;
        display:block;
}
.qnumberinfo {
        background: #DDDDDD;
        margin: 1px;
        padding: 1px;
        text-align:left;
}
.ageventinfo{
        width: 28px;
        float: right;
}
.seat_ip{
}
.ageventinfo a{

}
.ageventinfo a img{
        float: right;
}
#wrapper{
width: 99%;
/*background-color:#F1E0CE;*/
margin: 0 auto;
margin-top:5px;
margin-bottom: 5px;
text-align: center;
/*background: #F1E0CE;*/
border:1px solid #000000;
padding-bottom: 25px;
height:100%;
vertical-align:middle;
text-align:center;
}
#uptime {
        font-weight: bold;
}
#container-uptime {
        /*vertical-align:text-bottom;*/
        /*padding-left: 300px;*/
        float: left;
}

.bxIvr, .bxOutbound {
        width: 150px;
        border: 1px solid #BCBCBC;
        padding: 1px;
        float:left;
        /*margin-right: 10px;*/
}
.bxQueue {
        width: 150px;
        border: 1px solid #BCBCBC;
        padding: 0px;
        float:left;
        margin-right: 10px;
}
.bxSum {
        width: 300px;
        border: 1px solid #BCBCBC;
        padding: 0px;
        float:left;
        margin-right: 10px;
}
.infoQueue, .infoSum {
        width: 100%;
        height:136px;
}
.infoQueue td, .infoSum td {
        font-size: 30px;
        border: 1px solid #ffffff;
        text-align:center;
        font-family:"Times New Roman", Times, serif;
}
.infoSum td {
        font-size: 30px;
        background-color:#ffffff;
}
.infoQueue .qhead {
        font-size: 11px;
        font-weight: bold;
        text-align:center;
}
.infoQueue .qinfo, .infoSum .qinfo {
        font-size: 9px;
        text-align: center;
        font-family:Verdana, Arial, Helvetica, sans-serif;
}
td.qhead-td {
        /*background-color:#F96B0F;*/
        /*
        background:linear-gradient(to bottom, #4A98AF 44%, #328AA4 45%) repeat scroll 0 0 rgba(0, 0, 0, 0);
        color: #ffffff;
        padding: 1px;
        */

        background: #4a98af; /* Old browsers */
/* IE9 SVG, needs conditional override of 'filter' to 'none' */
background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSI0NCUiIHN0b3AtY29sb3I9IiM0YTk4YWYiIHN0b3Atb3BhY2l0eT0iMSIvPgogICAgPHN0b3Agb2Zmc2V0PSI0NSUiIHN0b3AtY29sb3I9IiMzMjhhYTQiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
background: -moz-linear-gradient(top,  #4a98af 44%, #328aa4 45%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(44%,#4a98af), color-stop(45%,#328aa4)); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top,  #4a98af 44%,#328aa4 45%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top,  #4a98af 44%,#328aa4 45%); /* Opera 11.10+ */
background: -ms-linear-gradient(top,  #4a98af 44%,#328aa4 45%); /* IE10+ */
background: linear-gradient(to bottom,  #4a98af 44%,#328aa4 45%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#4a98af', endColorstr='#328aa4',GradientType=0 ); /* IE6-8 */
color: #ffffff;
padding: 1px;
}
.qhead-td div.qhead{
        font-size: 14px;
}

.qinfo-tr {
        background-color:#D8D8D8;
}
.qinfo-tr td {
        padding: 2px;
}
td.qbox-col-left-border {
        border-left: 1px solid #BFBEBE;
}
.dim-value{
        color:#DDDDDD;
}



table.dbinfo {
        display:inline;
        border: 1px solid white;
}
td.qheadinfo span{
        padding:0;
        margin:0;
}
td.qheadinfo {
        margin:0px;
        padding:0px;
        font-size: 34px;
        font-weight: bolder;
        /*color:#F96B0F;*/
}
table.q-panel, table.ag-panel {
        border-collapse: collapse;
    border-radius: 0.417em 0.417em 0.417em 0.417em;
    box-shadow: 0 0 5px #091B21;
    margin: 5px;
    width: 98%;
}



/*--------------language color box---------------*/
.clear { clear:both; width:0; height:0; }

#seats .seat .language {
  border-collapse: separate;
  border-spacing: 1px;
  display: table;
  position: absolute;
  right: 5px;
  width: 109%;
}

div.q-lang {
        border-collapse: separate;
        border-spacing: 1px;
        display: table;
        position: relative;
        right: 5px;
        width: 109%;
}

#queues .language {
        top: 2px;
        width: 105%;
        left: -1px;
}

#seats .seat .language {
        top: 66px;
        width: 104%;
        left: -2px;
}

#queues .language .lang, #seats .seat .language .lang, .note {
  border: 1px solid transparent;
  display: table-cell;
  height: 5px;
  text-align: center;
  background-color:#b4b4b4;
}
.note { width:35px; height:7px; border:none; }

/*#queues .language .lang.none, #seats .seat .language .lang.none { background-color:#b4b4b4; }*/
#queues .language .lang.english, #seats .seat .language .lang.english, .note.english, #infoContainer .lng.english, { background-color:#6F69FF; }
#queues .language .lang.blue, #seats .seat .language .lang.blue, .note.blue, #infoContainer .lng.blue { background-color:#6F69FF; }
#seats .language .lang.blue.active {
        border-top: 4px dashed #4E49C9;
}

#queues .language .lang.bengali, #seats .seat .language .lang.bengali, .note.bengali, .note.bangla, #infoContainer .lng.bengali  { background-color:#E663C7; }
#queues .language .lang.purple, #seats .seat .language .lang.purple, .note.purple, #infoContainer .lng.purple  { background-color:#E663C7; }
#seats .language .lang.purple.active {
        border-top: 4px dashed #F888F7;
}

#queues .language .lang.spanish, #seats .seat .language .lang.spanish, .note.spanish, #infoContainer .lng.spanish { background-color:#24E322;/*green;*/ }
#queues .language .lang.green, #seats .seat .language .lang.green, .note.green, #infoContainer .lng.green { background-color:#24E322;/*green;*/ }

#queues .language .lang.hindi, #seats .seat .language .lang.hindi, .note.hindi, #infoContainer .lng.hindi { background-color:#F29F93;/*blue;*/ }
#queues .language .lang.red, #seats .seat .language .lang.red, .note.red, #infoContainer .lng.red { background-color:#F29F93;/*blue;*/ }

.note.english,.note.spanish,.note.hindi, .note.bengali, .note.bangla { display:block !important; }
.note.blue,.note.green,.note.red, .note.purple { display:block !important; }

#infoContainer { position:absolute; display:none; background-color:#FFFFFF; width:620px; border:5px solid #75A5B3; z-index:1; border-radius:5px; }
#infoContainer table { border-collapse: collapse; }
#infoContainer table th { background-color:#E5F1F4; text-align:left; text-indent:20px; padding:3px; }
#infoContainer table td { border:1px solid #75A5B3; padding:5px; }
#infoContainer .lng { width:50px; height:5px; display:block; margin:2px; }
.tooltips { position:absolute; background-color:#FFFFFF; padding:5px; border:3px solid #75A5B3; border-radius:3px; z-index:1; font-size:11px; display:none;  }
.tooltips b { font-size:15px; }




#overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  text-align: center;
  background-color: #000;
  filter: alpha(opacity=50);
  -moz-opacity: 0.5;
  opacity: 0.5;

}

#overlay span {
    padding: 5px;
    border-radius: 5px;
    color: #000;
    background-color: #fff;
    position:relative;
    top:50%;
}
</style>

<!--[if lt IE 9]>
<style type="text/css">
table.q-panel, table.ag-panel {
border :0.1em solid #BDBBB0 !important;
}
</style>
<![endif]-->

<!--[if gte IE 9]>
  <style type="text/css">
    .gradient {
       filter: none;
    }
  </style>
<![endif]-->

</head>
<?php
        $image_src = 'agents_picture/ccowner_logo.png';
    if (file_exists($image_src)) {
                $image_src = 'agents_picture/ccowner_logo.png?t=' . time();
        } else {
            $image_src = 'image/blank_logo.png';
        }

        $infoArray = array(
                array(
                        array("img"=>"seat_enable.gif","desc"=>"Agent not logged in"),
                        array("img"=>"agent_idle.gif","desc"=>"Agent idle")
                ),
                array(
                        array("img"=>"seat_empty.gif","desc"=>"Unregistered Seat"),
                        array("img"=>"agent_talking.gif","desc"=>"Agent attending inbound call")
                ),
                array(
                        array("img"=>"head_phone.gif","desc"=>"Agent talking without logging in"),
                        array("img"=>"agent_talking_out.gif","desc"=>"Agent attending outbound call"),
                ),
                array(
                        array("img"=>"ringing.png","desc"=>"Call ringing"),
                        array("img"=>"agent_st_hold_out.gif","desc"=>"Agent busy (work/short break)")
                ),
				array(
                        array("img"=>"agent_chat.gif","desc"=>"Agent attending chat"),
                        array("img"=>"agent_chat_busy.gif","desc"=>"Agent busy in chat")
                )
        );



?>
<body style="text-align:center;">

<!--
<div id="wrapper">
-->
<table cellpadding="0" cellspacing="0" id="wrapper">
        <tr>
        <td valign="top";>

<center>

<table cellpadding="0" cellspacing="0"  width="98%">
        <tr>
        <td style="background:url(image/dashboard_image.png) no-repeat left top;" width="225" height="48"></td>
                <td align="center" style="font-size:18px; font-family:Verdana, Arial, Helvetica, sans-serif; color:#F26522;">

                <table align="center"><tr><td>
                <table width="300"><tr>
                                <td width="60" style="font-size:20px;" class='note-tips'>
                                Q/S:
                                <?php if($isInfoAllow) { ?>
                                <div class='tooltips'>
                                <strong>Q/S</strong><br>
                                Total Calls in Queue <b>:</b> Total Calls in Service
                                </div>
                                <?php } ?>
                                </td>
                                <td class="qheadinfo" align="left"><span id="sum_calls"></span> &nbsp;</td></tr></table>
                </td><td id="tr-mht">
            <table width="205"><tr>
                        <td width="60" style="font-size:20px;" class='note-tips'>
                                MHT:
                                <?php if($isInfoAllow) { ?>
                                <div class='tooltips'>
                                <strong>Maximum Hold Time</strong><br>
                                Minutes <b>:</b> Seconds
                                </div>
                                <?php } ?>
                                </td><td class="qheadinfo" align="left"><span id="sum_mht"></span> &nbsp;</td></tr></table>
                        </td><td>
                <table width="225"><tr><td width="100" style="font-size:20px;" class='note-tips'>In/Out:
                        <?php if($isInfoAllow) { ?>
                                <div class='tooltips'>
                                        <strong>In/Out</strong><br>
                                Total agents in seat <b>:</b> Total agents out of seat
                                </div>
                                <?php } ?>
                        </td><td class="qheadinfo" align="left"><span id="sum_staff"></span></td></tr></table>
                        </td></tr></table>

        </td>
        <td style="background:url(<?php echo $image_src; ?>) no-repeat right bottom; width:115px; height:46px;">

                        <table height='100%'>
                                <?php
                                if(count($languages)>1) {
                                foreach ($languages as $language): ?>
                                <tr>
                                        <td><div class='note <?php echo strtolower($language_n_color[$language->lang_key]);?>'></div></td>
                                        <td><?php echo $language->lang_title;?></td>
                                </tr>
                                <?php endforeach;
                                }
                                ?>
                        </table>
        </td>
        <td width="130" style="font-size:35px;font-weight:bolder;">
                <span id="db_hhmm"></span><span id="db_ampm" style="font-size:20px;">PM</span>
        </td>
        <td width='60'>

                        <?php if($isInfoAllow) { ?>

                        <img id='btnInfo' src='image/info.png'></a>
                        <div id="infoContainer"><table width="100%">
                                <tr>
                                        <th colspan='4'>Legend</th>
                                </tr>
                                <tr>
                                        <td width='80' align='center'><b>Figure</b></td>
                                        <td><b>Description</b></td><td width='80' align='center'><b>Figure</b></td>
                                        <td><b>Description</b></td>
                                </tr>
                                <?php
                                foreach($infoArray as $array) {
                                ?>
                                        <tr>
                                                <td align='center'><img src='image/<?php echo $array[0]["img"]; ?>'></td>
                                                <td><?php echo $array[0]["desc"]; ?></td>
                                                <td align='center'><img src='image/<?php echo $array[1]["img"]; ?>'></td>
                                                <td><?php echo $array[1]["desc"]; ?></td>
                                        </tr>
                                <?php
                                }
                                ?>
                                <tr height='60'>
									<td align='center'><img src='image/agent_call_chat_busy.gif'></td>
                                    <td>Agent busy in call and chat</td>
									<td align='center'>
										<div class="language">
											<?php
											if(count($languages)>1) {
												$k = 0;
												foreach ($languages as $language):?>
												<span class="lng <?php echo $colorSeq[$k]; ?>"></span>
												<?php
												$k++;
												endforeach;
											}
											?>
											<div class='clear'></div>
										</div>
									</td>
									<td>Language indicator</td></tr>
                                </table>
                        </div>
                        <?php
                        }
                        ?>
                </td>
                <td align='right' class='note-tips'><img src='image/logout.png' height='28' id='LogoutBtn' style='cursor:pointer;'>
                                <div class='tooltips' style='margin:5px 10px 0 0;'>
                                <strong style='color:#ff0000'>Logout</strong>
                                </div>
                        </td>
        </tr>
</table>


<table class="q-panel" style="" cellpadding="0" border="0" cellspacing="0" width="98%" height="150" align="center">
    <tr>
        <td style="vertical-align:top;background: none repeat scroll 0 0 #E5F1F4; padding:5px;">


<div id="queues">

<?php if (is_array($skills)):?>
<?php foreach ($skills as $skill):?>
<div class="bxQueue">
<table class="infoQueue" bgcolor="#ffffff" cellspacing="0">
<tr><td colspan="2" class="qhead-td"><div class="qhead"><?php echo $skill->short_name; if ($skill->type == 'out') echo ' [out]';?></div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo"><?php echo $skill->type == 'out' ? 'In Progress' : 'In Queue';?></div></td><td class="qbox-col-left-border"><div class="qinfo">In Service</div></td></tr>
<tr>
        <td style='position:relative;'><div id="inq<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div>
        <div id='inqLang<?php echo $skill->skill_id;?>' class="q-lang language">

                <?php
                if(count($languages)>1) {
                $_i = 0;
                foreach ($languages as $language):?>
                <span class="lang lang<?php echo $_i;?>"></span>
                <?php
                $_i++;
                endforeach;
                }
                ?>

                <div class='clear'></div>
        </div>
        </td>
        <td class="qbox-col-left-border position:relative;"><div id="srv<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div>
        <div id="insLang<?php echo $skill->skill_id;?>" class="q-lang language">
                <?php
                if(count($languages)>1) {
                $_i = 0;
                foreach ($languages as $language):?>
                <span class="lang lang<?php echo $_i;?>"></span>
                <?php
                $_i++;
                endforeach;
                }
                ?>

                <div class='clear'></div>
        </div>
        </td>
</tr>
<tr class="qinfo-tr"><td><div class="qinfo"><?php echo $skill->type == 'out' ? 'ATT' : 'AHT';?></div></td><td class="qbox-col-left-border"><div class="qinfo"><?php echo $skill->type == 'out' ? 'MTT' : 'MHT';?></div></td></tr>
<tr><td><div id="at<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div></td><td class="qbox-col-left-border"><div id="mt<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div></td></tr>
</table>
<span class="hidden q-ts-count" id="isQTsCount<?php echo $skill->skill_id;?>">0</span>
<span class="hidden ts" id="qAvgTs<?php echo $skill->skill_id;?>">0</span>
<span class="hidden ts" id="qMaxTs<?php echo $skill->skill_id;?>">0</span>
<span class="hidden" id="cntInQ<?php echo $skill->skill_id;?>">0</span>
<span class="hidden" id="cntInSrv<?php echo $skill->skill_id;?>">0</span>
</div>
<?php endforeach;?>
<?php endif;?>

<div class="bxIvr">
<table class="infoQueue" bgcolor="#ffffff" cellspacing="0">
<tr><td colspan="2" class="qhead-td"><div class="qhead">IVR</div></td></tr>

<tr class="qinfo-tr"><td colspan="2"><div class="qinfo">IVR Count</div></td></tr>
<tr><td colspan="2"><div id="cntI0"><span class="dim-value">0</span></div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo">Avg Time</div></td><td class="qbox-col-left-border"><div class="qinfo">Max Time</div></td></tr>
<tr><td><div id="atI0"><span class="dim-value">0</span></div></td><td class="qbox-col-left-border"><div id="mtI0"><span class="dim-value">0</span></div></td></tr>
</table>
<span class="hidden iv-ts-count" id="isIvTsCountI0">0</span><span class="hidden ts" id="ivAvgTsI0">0</span><span class="hidden ts" id="ivMaxTsI0">0</span>
<span class="hidden" id="cntTCI0">0</span>
</div>

<div class="bxOutbound">
<table class="infoQueue" bgcolor="#ffffff" cellspacing="0">
<tr><td colspan="2" class="qhead-td"><div class="qhead">Outbound</div></td></tr>

<tr class="qinfo-tr"><td colspan="2"><div class="qinfo">Total Calls</div></td></tr>
<tr><td colspan="2"><div id="cntOB"><span class="dim-value">0</span></div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo">Avg Time</div></td><td class="qbox-col-left-border"><div class="qinfo">Max Time</div></td></tr>
<tr><td><div id="atOB"><span class="dim-value">0</span></div></td><td class="qbox-col-left-border"><div id="mtOB"><span class="dim-value">0</span></div></td></tr>
</table>
<span class="hidden ob-ts-count" id="isOBTsCountOB">0</span><span class="hidden ts" id="obAvgTsOB">0</span><span class="hidden ts" id="obMaxTsOB">0</span>
<span class="hidden" id="cntTCOB">0</span>
</div>


</div>


        </td>

    </tr>


</table>

<div style="text-align:center; position: relative; height: 24px; line-height: 24px;">
    <div id="poweredBy" style="position: absolute; right: 18px; top: 0;"><img alt="gPlex Contact Center" src="image/gplex_poweredby.png"></div>
</div>

<table class="ag-panel" cellpadding="0" border="0" cellspacing="0" width="98%" height="320" align="center">
    <tr>
        <td style="text-align:center; vertical-align:top;background: none repeat scroll 0 0 #E5F1F4; padding:5px;">


<div id="seats">
<?php if (is_array($seats)):?>
<?php foreach ($seats as $seat):?>

<div id="seat_<?php echo $seat->seat_id;?>" class="seat empty hidden" style='position:relative;'>
        <div class="agentpic" id="ap_<?php echo $seat->seat_id;?>" agent="">&nbsp;</div>
        <div class="seat-text">
        <div class="agent"></div>
        <div class="status"></div>
        <div class="seatinfo"><?php echo $seat->label;?></div>
    </div>
    <div style="display:none;" class="seat_ip"><?php echo $seat->ip;?></div>
    <div class="language">


                <?php
                if(count($languages)>1) {
                $k = 0;
                foreach ($languages as $language):?>
                <span class="lang lang<?php echo $k; ?>"></span>
                <?php
                $k++;
                endforeach;
                }
                ?>

                <div class='clear'></div>
    </div>
    <span class="hidden agent-status" id="ag_<?php echo $seat->seat_id;?>">0</span>
</div>

<?php endforeach;?>
<?php endif;?>

</div>
        </td>
    </tr>
</table>

</center>

</td>
</tr>
</table>

<div id="agent_queue_info" style="display:none; width: 160px;"></div>

<div id="queue_detail_info" style="display:none; width:270px;"></div>
<!--
</div>
-->

<div id="overlay" class="hidden">
    <span>Connection to the server is lost, Trying ...</span>
</div>
</body>
</html>
<script>
        $("#LogoutBtn").click(function(){
                obj.Logout();
                $.ajax({
                        type: "POST",
                        url: "<?php echo $this->url("task=agent&act=add-dashboard-logged-out");?>"
                }).done(function (data) {
                   window.close();
                });
        });
        $("#btnInfo").mouseover(function(){
                var left = $(this).position().left;
                var top = $(this).position().top;
                var width = $(this).width();
                var height = $(this).height();
                $("#infoContainer").css({"left":left+width-600+'px',"top":top+height+10});
                $("#infoContainer").show();
        }).mouseout(function(){
                $("#infoContainer").hide();
        });
        $(".note-tips").mouseover(function(){
                $(this).find(".tooltips").show();
        }).mouseout(function(){
                $(this).find(".tooltips").hide();
        });
</script>