<!DOCTYPE html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]> <html class="lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]> <html class="lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <!--meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"-->
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <title>Call Control</title>
    <link rel="stylesheet" href="ccd/ccd.css">
    <link rel="stylesheet" href="ccd/bootstrap.min.css">
    <link rel='stylesheet' href='ccd/calllog.css' type='text/css'>
    <!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <style>
        #disposition-container{padding: 0 15px;border: 1px solid #ddd;background: #efefef;}
        .cj{color: #ffffff;}
    </style>
</head>
<body>

<div class="iframebox">
    <div class='row header'>

        <div class="ccd" style='display:none;'>
            <div class='ccd-body ccd-blue disabled'>

                <ul class='item col-sm-8 col-md-8 col-lg-8' >
                    <li class='item-li'>
                        <div class='dial-control'>
                            <a class='call primary button bottomTips' title="Call" id="btnCall" title="Call"></a>
                            <div class='fields child-view'>
                                <div class='dial-view'>
                                    <div class='dialno'>
                                        <input type='text' id='dialNo' class='dial-no' placeholder="Dial Number" />
                                        <input type='button' value='Call' class='dial-button uibutton' onclick="MakeCall()">
                                    </div>
                                    <div>
                                        <label for='skillList'>Skill:</label>
                                        <select id='skillList' onchange="ChangeSkill()"></select>
                                    </div>
                                </div>
                                <div class='dial-button-view'>
                                    <input type='button' value='Transfer' id="btnAgentTransfer" class='dial-button uibutton special'>
                                    <input type='button' value='Return' id="btnAgentTransferCancel" class='dial-button uibutton'>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class='item-li' id="holdSection"><a class='hold primary button bottomTips' title="Hold" id="btnHold" title="Hold"></a></li>
                    <li class='item-li' id="unHoldSection">
                        <div class='unhold-control'>
                            <a class='unhold primary button bottomTips' title="Unhold" id="btnUnhold" title="Unhold"></a>
                            <div class='unhold-view child-view'>

                                <table id='holdedCallList'></table>

                            </div>
                        </div>
                    </li>
                    <li class='item-li' id="transferSection">
                        <div class='transfer-control'>
                            <a class='transfer primary button bottomTips' title="Transfer" id="btnTransfer" title="Transfer"></a>
                            <div class='transfer-view child-view'>
                                <div><b>Do Transfer</b> <div class='close' style='float:right;'>X</div></div>
                                <fieldset id="callTransferWindow" class="dial-view">
                                    <legend>Destination</legend>
                                    <table>
                                        <?php if (!empty($callTransferConfig)): ?>

                                            <?php if ($callTransferConfig->agents == "Y"): ?>
                                                <tr>
                                                    <td align='right'><b>Agent</b></td><td>:</td>
                                                    <td><select id="allAgentList"></select></td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php if ($callTransferConfig->supervisors == "Y"): ?>
                                                <tr>
                                                    <td align='right'><b>Supervisor</b></td><td>:</td>
                                                    <td><select id="supervisorList"></select></td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php if ($callTransferConfig->skills == "Y"): ?>
                                                <tr>
                                                    <td align='right'><b>Skill</b></td><td>:</td>
                                                    <td><select id="optSkill"></select></td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php if ($callTransferConfig->ivrs == "Y"): ?>
                                                <tr>
                                                    <td align='right'><b>IVR</b></td><td>:</td>
                                                    <td><select id='ivrList'></select></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td colspan='2'></td>
                                                <td><input type='button' value='Transfer' class='dial-button uibutton special' onclick="Transfer()"></td>
                                            </tr>
                                        <?php endif; ?>
                                    </table>
                                </fieldset>
                                <fieldset id="callTransferCancelWindow">
                                    <legend>Transfer Cancellation</legend>
                                    <table>
                                        <tr>
                                            <td><input type='button' value='Transfer Cancel' class='dial-button uibutton' onclick="TransferCancel()"></td>
                                        </tr>
                                    </table>
                                </fieldset>
                                <div class='dial-button-view'>
                                    <input type='button' value='Transfer' id="btnCommonTransfer" class='dial-button uibutton special'>
                                    <input type='button' value='Return' id="btnCommonTransferCancel" class='dial-button uibutton'>
                                </div>

                            </div>
                        </div>
                    </li>
                    <li id="callDuration" class="bottomTips item-li" title="Call duration"></li>
                    <li id="cname" class='bottomTips item-li' ondblclick="copyClientName()" title='CName'></li>
                    <li id="cli" class='bottomTips item-li'  ondblclick="copyCLI()" title='CLI'></li>
                    <li id="skill" class='bottomTips item-li' ondblclick="copySkill()" title='Skill (DID)'></li>
                    <li id="holdTime" class='bottomTips item-li' title='Hold in Queue'></li>
                    <li id="lang" class='bottomTips item-li' title='Language'></li>
                    <li id="pdStatus" class='bottomTips item-li' ondblclick="copyPDNumber()" title='PD Status'></li>
                    <li id="disposition-set-button" class='bottomTips item-li' title='Set Disposition'>Disposition
                        <!--<button type="button" class="hide btn btn-primary btn-xs">Disposition</button>-->
                    </li>
                </ul>


                <div class="col-sm-4 col-md-4 col-lg-4">

                    <!--   <ul class='item mini-dashboard hide'>
                           <li class='item-li' id="callsInQ" title='Call in queue'>0</li>
                           <li class='item-li'>:</li>
                           <li class='item-li' id="callsInService" title='Call in Service'>0</li>
                       </ul>-->

                    <ul class='item pull-right'>
                        <li class="item-li">
                            <a id="customer_journey" class="cj" target="_blank" title="Customer Journey" href="">CJ</a>
                        </li>
                        <li class='bottomTips item-li' title='Footprint' id="footprint" style="display:none;">
                            <div class='footprint-dtls'>
                                <a class='footprint primary button bottomTips' title="Footprint" id="btnFootprint"></a>
                                <div class='fields child-view'>
                                    <div class='dial-view' id="footprintTxtDtl">

                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class='bottomTips item-li' id="footprintTitle"></li>
                        <li id="btnPD" class='btn item-li pd-off'>PD</li>
                        <li class='selfId bottomTips item-li' title='Agent ID'><?php echo UserAuth::getCurrentUser(); ?></li>
                        <li id='nick' class='bottomTips item-li' title='Agent'><?php echo UserAuth::getUserNick(); ?></li>
                        <li class='seat bottomTips item-li' id='seatId' title='Seat ID'></li>

                        <li class='item-li'>
                            <div class='aux busy loadAux' id="btnAux"><span class='caption'>Busy</span><span class='drop'></span><div class='clear'></div></div>
                        </li>
                    </ul>
                </div>
                <div class="clicker">
                    <img src='image/menu.png'>
                    <div class="click-nav">
                        <ul>
                            <li><div href="#" class="loadHelp" id='callLog'><!--img src="image/i-2.png" alt="Icon" align='texttop'-->Call Log</div></li>
                            <li><div href="#" class="loadHelp" id='chatMenu'><!--img src="image/i-2.png" alt="Icon" align='texttop'-->Chat</div></li>
                            <li><div href="#" id='textMenu'><!--img src="image/i-2.png" alt="Icon" align='texttop'-->Text</div></li>
                            <?php if(UserAuth::getRoleID()=="S") { ?>
                                <li><div href="#" class="loadHelp" id='chatAgentList'><!--img src="image/i-2.png" alt="Icon" align='texttop'-->Supervisor Monitor</div></li>
                            <?php } ?>
                            <?php if(UserAuth::getDBSuffix()!="AD") { ?>
                                <li id="btnAuthenticated"><div href="#">Set caller as authenticated</div></li>
                            <?php } ?>

                            <li><div href="#" id="resetCallSession">Reset Call-Sessions</div></li>
                            <li><div id="sessionSummary">Session Summary</div></li>
                            <li><div id="callHistory">Call History</div></li>
                            <li><div href="#">About</div></li>
                            <li><div href="#" onclick="Logout()">Sign out</div></li>
                        </ul>
                    </div>
                </div>
                <div class='clear'></div>
            </div>
        </div>

    </div> <!-- row header -->



    <div id="disposition-container" class="hide ">
        <div class="single-disposition-form">
            <form action="javascript:void(0)" class="form-inline form small">

                <div class="form-group form-group-sm caller disposition-box">
                    <label for="callid" class="control-label">Caller</label>
                    <select name="callid" id="callid" class="callid form-control" disabled>
                        <option value="">---Select---</option>
                    </select>
                </div>

                <div class="form-group form-group-sm disposition disposition-box">
                    <label for="disposition_id" class="control-label">Disposition</label>
                    <select name="disposition_id[]" id="disposition_id" class="disposition-class form-control">
                        <option value="">---Select---</option>
                    </select>
                </div>

                <div class="form-group form-group-sm">
                    <label for="note" class="control-label">Note</label>
                    <input name="note" id="note" maxlength="255" class="form-control">
                </div>

                <button type="button"  class="btn btn-primary btn-xs create-disposition-box">+</button>
                <button type="button" id="set-disposition" class="btn btn-success btn-xs set-disposition">Set Disposition</button>
            </form>
        </div>
    </div>
    <div class="row wrapper">
        <ul id="tabs">
            <!-- Tabs go here -->
        </ul>
        <div id="content">
            <!-- Tab content goes here -->
        </div>
    </div>
</div>

<!--div id='seatAssignForm'>
    <fieldset>
        <legend>This PC is not defined as a seat</legend>
        <div>
            <div class='row'><label for='seat_id'>Seat ID:</label><input type='text' id='seat_id'></div>
            <div class='row'><label for='supervisor_id'>Supervisor ID:</label><input type='text' id='supervisor_id'></div>
            <div class='row'><label for='supervisor_pass'>Supervisor Password:</label><input type='password' id='supervisor_pass'></div>
            <div class='row'><label></label><input type='button' id='saveSeat' value="Send"></div>
        </div>
    </fieldset>
</div-->

<!--a href='javascript://' onclick="LoadCall('116','1016','2476853682829-1')">Call 1</a>
<a href='javascript://' onclick="LoadCall('117','1017','2476853682829-2')">Call 2</a>
<a href='javascript://' onclick="LoadCall('118','1018','2476853682829-3')">Call 3</a-->

<!--OBJECT id="EventListener" name="EventListener" classid="clsid:5373BCCC-9599-4578-8C54-B1A80564A57F" VIEWASTEXT codebase="EventListener.cab"></OBJECT-->
<!--iframe id="purl" class="iframe-url" src="about:blank" style="display:none;"></iframe-->
<!--a style="position: fixed; bottom:10px; left:10px;" href="<?php //echo $this->url("task=agents");?>" > Home</a-->
<audio id="chatAudio">
    <source src="ccd/notify.wav" type="audio/wav">
</audio>
<?php

$ws_port = $account_info->ws_port + 1;

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
    $ws_port = $account_info->ws_port;
}

$token = UserAuth::getOCXToken();

if(empty($token)) {
    include_once('model/MAgent.php');
    //AddModel("MAgent");
    $user = UserAuth::getCurrentUser();
    $agent_model = new MAgent();
    $token=$agent_model->GenerateToken($user);
}
?>
<?php /* ========================= SMS chat service ============================ */ ?>
<div id="sms-container"></div>

</body>
</html>

<script type="text/javascript">
    var dispositionUrl = "<?php echo $this->url('task=agent&act=chat_disposition_mail'); ?>";
    var chatTemplateUrl = "<?php echo $this->url('task=agent&act=ChatTemplate'); ?>";
    var coBrowserUrl = "<?php echo $this->url('task=agent&act=CoBrowserLinks'); ?>";
    //var auxListData = [];
</script>

<script  src="ccd/jquery.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="ccd/tipsy/tipsy.css">
<script  src="ccd/tipsy/jquery.tipsy.js" type="text/javascript"></script>
<script src="ccd/wss.gPlexCCWS.js?v=1.0.6" type="text/javascript"></script>
<script src="ccd/shortcut.js" type="text/javascript"></script>
<script src="ccd/jquery.cookie.js" type="text/javascript"></script>

<!--link rel="stylesheet" href="ccd/chosen/chosen.css">
<script src="ccd/chosen/chosen.jquery.js" type="text/javascript"></script-->

<script type="text/javascript" src="ccd/autosize.js"></script>
<script type="text/javascript" src="ccd/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="ccd/jquery-ui.css"/>
<script type="text/javascript" src="ccd/new/plugins/jquery.browser.min.js"></script>
<script type="text/javascript" src="ccd/new/plugins/bnKb/driver.phonetic.js?v=1.0"></script>
<script type="text/javascript" src="ccd/new/plugins/bnKb/engine.js?v=1.0"></script>
<link rel="stylesheet" href="ccd/message.css?<?php echo time(); ?>">
<script  src="ccd/message.js?v=1.0.6" type="text/javascript"></script>

<script  src="ccd/select2/select2.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="ccd/select2/select2.min.css">

<link rel="stylesheet" href="ccd/button.css">
<script type="text/javascript" src="ccd/js-serialize.js"></script>

<link href="js/lightbox/colorbox.css" rel="stylesheet" media="screen">
<script src="js/lightbox/jquery.colorbox.js" type="text/javascript"></script>
<!----------------------Messenger Notification Plugin--------------------------->
<script src="assets/plugins/messenger/js/messenger.min.js"></script>
<script src="assets/plugins/messenger/js/messenger-theme-future.js"></script>
<script src="assets/js/lodash.min.js"></script>
<link rel="stylesheet" href="assets/plugins/messenger/css/messenger.css">
<link rel="stylesheet" href="assets/plugins/messenger/css/messenger-theme-future.css">

<!--  localstorage  -->
<script src="assets/js/gplex.localStorage.js" type="text/javascript"></script>

<?php
$skillArray = array();
if (is_array($SkillList)) {
    /*
    foreach ($SkillList as $val) {
             $obj = new stdClass();
             $obj->skill_id = $val->skill_id;
             $obj->skill_name = $val->skill_name;
             $skillArray[] = $obj;
     }
     */
    $skillArray = $SkillList;
}
?>

<script type="text/javascript">
    window.onerror = function (msg, url, line) {
        $.ajax({
            url: "<?php echo $this->url('task=agent&act=js-error-log')?>",
            type: "post",
            dataType: 'json',
            data: {msg: msg, url: url, line: line},
            success: function (result) {
            }
        });
    };

    var wsUri = "ws://<?php echo $CCSettings->switch_ip .":".$ws_port."/chat" ?>";
    var token = "<?php echo $token;?>";

    var isInCall = false;
    var isOutBoundCall = false;
    var inCallInfo = null;
    var currentSCeq = 100;
    var requestTimeoutInterval = 3; //seconds;
    var startedSessionThread = false;
    var SwCallStatusID = '';
    var SessionData = new Array();
    var obj;
    //var selectedAuxId = $.cookie("wsCCSelectedAuxId");
    //var selectedSkillId = $.cookie("wsCCSelectedSkillId");
    var tempAuxId = "";
    var seat_id;
    var thisPcMac = "";
    var curSuffix = "<?php echo UserAuth::getDBSuffix();?>";
    var agentNick = "<?php echo UserAuth::getUserNick(); ?>";
    var agentId = "<?php echo UserAuth::getCurrentUser();?>";
    var userType = "<?php echo UserAuth::getRoleID();?>";
    var wsLoggedUrl = "<?php echo $this->url("task=agent&act=add-web-sock-logged-in");?>";
    var pdSkillUrl = "<?php echo $this->url("task=agent&act=get-pd-skills");?>";
    var pdSkills = [];
    var fprintUrl = "<?php echo $this->url("task=agent&act=get-footprints");?>";
    var wsSignInUrl = "<?php echo $this->url("task=logout");?>";
    var pd_engines = [];
    var pdPreviewDialStatus = 0;
    var pdPreviewSkillId = '';
    var pd_preview_message = null;
    //var skilListData = [];
    // var skilListData = <?php //echo json_encode($skillArray); ?>;
    var skilListData = <?php echo $SkillList; ?>;
    var skilListAllData = [];
    //var agentListData = [];
    var agentListData = <?php echo $AgentList;?>;
    var cobrowseLinkData = <?php echo $cobrowseLinks;?>; 
    var skillListToTransferCall = <?php echo $SkillList;?>;
    //$.cookie("wsCCListAgent",JSON.stringify(agentListData));
    //var ivrListData = [];
    var ivrListData = <?php echo $IvrList;?>;
    //console.log(ivrListData);
    var auxListData = <?php echo $AUXList;?>;
    var supervisorListData = <?php echo !empty($supervisors) ? $supervisors : []; ?>; // to transfer call to supervisor
    var allAgentListData = <?php echo !empty($allAgent) ? $allAgent : []; ?>; // to transfer call to any agent

    var hotKeys = {"CALL":"Ctrl+C","HOLD":"Ctrl+H","TRANSFER":"Ctrl+T","READY":"Ctrl+R","BUSY":"Ctrl+B","MENU":"Ctrl+M"};
    var tempHotKeys = '<?php echo json_encode($hotKeySettings); ?>';
    $.extend( hotKeys, jQuery.parseJSON(tempHotKeys) );
    var try_timeout = null;
    var hold_timeout = null;
    var duration_timeout = null;
    var max_sec_limit_to_try = 10;
    var countdown_sec_limit_to_try = max_sec_limit_to_try;
    var isLoggedIn = false;
    var currentCallId = "";
    var currentSkillId = "";
    var currentAuthStatus = "";
    //var holdedCallId = "";
    var unHoldedCallId = "";
    //var callsStack = !$.cookie("wsCCCallsStack") ? [] : JSON.parse($.cookie("wsCCCallsStack"));
    //console.log("V:"+$.cookie("wsCCCallsStack"));
    var transferAgentId = "";
    var transferCallId = "";
    var commonTransferTo = '';
    var missedCall = false;
    var tempSkillId = "";
    var regResponseSuccess = true;
    var lastRegMsgSendAt = new Date();
    var isHoldedCall = false;
    var webSiteKey = "";
    var natPingInterval = null;
    var lastNatPingTime = 0;
    var baseUrl = "<?php echo site_url().base_url(); ?>";

    //var agentIsInMonitor = [];
    $.cookie("ws_port","<?php echo $account_info->ws_port;?>");
    var natPingResponseCount = 0;
    var socketAlreadyOpen = false;

    agentCallStack = [];

    var PDStatus = 0;
    var pd_call_template = {};	

    var datetime;
    var cookieVars = {
        callsStack:"wsCCCallsStack",
        selectedSkillId:"wsCCSelectedSkillId",
        selectedAuxId:"wsCCSelectedAuxId",
        webKey:"wsWebKey",
        webSiteKeyToChat:"wsWebSiteKeyToChat",
        callIdToChat:"wsCallIdToChat",
        customerNameToChat:"wsCustomerNameToChat",
        popup:"wsPopup"
    }
    var cookieValue = {
        callsStack : !$.cookie(cookieVars.callsStack) ? [] : JSON.parse($.cookie(cookieVars.callsStack)),
        selectedSkillId : !$.cookie(cookieVars.selectedSkillId) ? "" : $.cookie(cookieVars.selectedSkillId),
        selectedAuxId : !$.cookie(cookieVars.selectedAuxId) ? "" : $.cookie(cookieVars.selectedAuxId),
        webKey: !$.cookie(cookieVars.webKey) ? "" : $.cookie(cookieVars.webKey),
        webSiteKeyToChat: !$.cookie(cookieVars.webSiteKeyToChat) ? "" : $.cookie(cookieVars.webSiteKeyToChat),
        callIdToChat: !$.cookie(cookieVars.callIdToChat) ? "" : $.cookie(cookieVars.callIdToChat),
        customerNameToChat: !$.cookie(cookieVars.customerNameToChat) ? "" : $.cookie(cookieVars.customerNameToChat),
        popup: !$.cookie(cookieVars.popup) ? {"cid":"","cli":"","url":""} : JSON.parse($.cookie(cookieVars.popup))
    }

	console.log("cookie value");
	console.log(cookieValue);
	//console.log(obj);
    //console.log($.cookie(cookieVars.selectedAuxId));
	
	//webchat storage var
	var storageVars = {
        callIdToChat: curSuffix.toLowerCase()+"WsslCallIdToChat",
        chatConversation: curSuffix.toLowerCase()+"WsslChatConversation",
        chatRequestInfo: curSuffix.toLowerCase()+"WsslChatRequestInfo",
        chatBoxes: curSuffix.toLowerCase()+"WsslChatBoxes",
    }
	
	var storageValue = {
        callIdToChat: !statusHtmlStorage(storageVars.callIdToChat) ? [] : getHtmlStorage(storageVars.callIdToChat),
        chatConversation: !statusHtmlStorage(storageVars.chatConversation) ? {} : getHtmlStorage(storageVars.chatConversation),
        chatRequestInfo: !statusHtmlStorage(storageVars.chatRequestInfo) ? {} : getHtmlStorage(storageVars.chatRequestInfo),
        chatBoxes: !statusHtmlStorage(storageVars.chatBoxes) ? {} : getHtmlStorage(storageVars.chatBoxes),
    }
	console.log("storage value");
	console.log(storageVars);
	console.log(storageValue);

    var $ccd = $(".ccd");
    var $ccdBody = $(".ccd .ccd-body");
    var $holdedCallList = $("#holdedCallList");
    var $callDuration = $("#callDuration");
    var $cli = $("#cli");
    var $cname = $("#cname");
    var $skill = $("#skill");
    var $lang = $("#lang");
    var $holdTime = $("#holdTime");
    var $callsInQ = $("#callsInQ");
    var $callsInService = $("#callsInService");
    var $holdSection = $("#holdSection");
    var $unHoldSection = $("#unHoldSection");
    var $transferSection = $("#transferSection");
    var $skillList = $('#skillList');
    var $optSkill = $("#optSkill");
    var $ivrList = $("#ivrList");
    var $supervisorList = $("#supervisorList"); //transfer call to supervisor
    var $allAgentListData = $("#allAgentList"); //transfer call to any agent
    var $callTransferWindow = $("#callTransferWindow");
    var $callTransferCancelWindow = $("#callTransferCancelWindow");
    var $dispositionButton = $("#disposition-set-button button");
    dispositions = [];
    var did_prefix_replace = <?php echo $did_prefix_replace ?>;
    var show_sccb = <?php echo $show_sccb ?>;

    $(function() {
        $(document).on("click", "#submit-selected-pd-skill", e => {
            let skill = $("#choose-pd-skill option:selected").val();
            if(!skill){
                alert("Select a skill to continue with predictive dial");
                return;
            }
            obj.PD_Action(PDStatus, skill, cookieValue.webKey);
            $("#cboxClose").trigger('click');
        });
        $skillList.select2();
        $optSkill.select2();
        $ivrList.select2();
        $supervisorList.select2(); //transfer call to supervisor
        $allAgentListData.select2(); //transfer call to any agent
        SetMissedCallAckNotification();
        SetReturnCallAckNotification();
        $("#btnAuthenticated").hide();
        $("#btnPD").hide();



        //SeatStatusNotification();
        //IframeOpen("http://www.genuitysystems.com");

        $(".bottomTips").tipsy({html: true, gravity:'n', delayOut:10,clsStyle: 'blue'});
        $("#nick").html(agentNick);

        //	$(".ccd").hide();
        $(".ccd").show();

        //$ccdBody.removeClass("disabled").addClass("enabled");

        if(cookieValue.selectedAuxId==0 && cookieValue.selectedAuxId != "") {
            $("#btnAux").addClass("primary").removeClass("busy active").html("<span class='caption'>Ready</span><span class='drop'></span><div class='clear'></div>");
        }

        try {
            obj = new gPlexCCWSApi();
            //173.192.129.82
            obj.loadSettings(wsUri, '<?php echo UserAuth::getCurrentUser();?>', token, curSuffix);
            obj.setNumLoggedIn(<?php echo UserAuth::numWSLoggedIn();?>);
            obj.Connect();

            $.ajax({
                type: "POST",
                url: pdSkillUrl,
                dataType: "json"
            }).done(function (data) {
                if (data != null && data.length >= 1) {
                    pdSkills = data;
                    $("#btnPD").show();
                    /* get pd skill with dial engine as array of object */
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->url('task=agent&act=get-pd-skill-engine'); ?>",
                        dataType: "json"
                    }).done(function (data) {
                        if (data !== 'undefined' && data != null && data.length >= 1) {
                            $.each(data, function (index, value) {
                                pd_engines[value.skill_id] = value.dial_engine;
                            });
                        }
                    });
                }
            });
        } catch (ex) {
            alert("Warning1: " + ex.message);
            $(".ccd").show();
        }

        $('.clicker').click(function(e) {
            if(!isLoggedIn || missedCall) return;

            RemoveAuxView();
            $('.click-nav').slideToggle(200);
            e.stopPropagation();
        });

        $(document).click(function(e) {
            if ($('.click-nav').is(':visible')) {
                $('.click-nav', this).slideUp();
            }

            if($(e.target).parents(".fields").length>0  || $(e.target).parents("#callLogWindow").length>0 || $(e.target).parents(".select2-container").length>0) {

            } else {
                $(".ccd .fields").hide();
                $("#btnCall").removeClass("active").addClass("primary");
            }

            if($(e.target).parents(".unhold-control").length==0) {
                $(".ccd .unhold-view").hide();
                $("#btnUnhold").removeClass("active").addClass("primary");
                clearTimeout(hold_timeout);
            }

            if($(e.target).parents(".transfer-view").length>0 || $(e.target).parents(".select2-container").length>0) {

            } else {
                $(".ccd .transfer-view").hide();
                //isOutBoundCall = false;
                $("#btnTransfer").removeClass("active").addClass("primary");
            }

            if($(e.target).parents("#btnAux").length==0) {
                RemoveAuxView();
            }

        });

        $("#btnPD").click(function(e) {
            if(!isLoggedIn) return;
            PD_Action();
            e.stopPropagation();
        });

        $("#btnFootprint").click(function(e) {
            ShowFootprint();
            e.stopPropagation();
        });

        $("#btnCall").click(function(e) {

            if(!isLoggedIn || missedCall) return;

            //if(typeof skilListData !== 'undefined' && skilListData.length==0) {
            //RecordCurrentSCeq();
            //SendGUI("LIST","SKILL");
            //} else {
            ShowSkillView(skilListData);
            //}

            e.stopPropagation();
        });

        $("#btnAgentTransfer").click(function(){
            //$(".agent-transfer-item").hide();
            $(".fields").hide();
            $("#btnCall").removeClass("active").addClass("primary");
            $(".dial-view").show();
            $(".dial-button-view").hide();
            obj.Transfer( transferCallId, "AG"+transferAgentId, cookieValue.webKey);

            CancelCall(transferCallId);
        });

        $("#btnCommonTransfer").click(function(){
            //$(".dial-view").show();
            //$(".dial-button-view").hide();
            obj.Transfer( transferCallId, commonTransferTo, cookieValue.webKey);

            //CancelCall(transferCallId);
        });

        $("#btnAgentTransferCancel, #btnCommonTransferCancel").click(function(){
            $(".dial-view").show();
            $(".dial-button-view").hide();
            TransferCancel();
        });

        $(document).on("click",".loadAux .caption",function(){

            if(!isLoggedIn) return;

            missedCall = false;
            /*
            if($(this).hasClass("busy")) {
                console.log("Aux zero");
                tempAuxId = "0";
                obj.ChangeAUX(tempAuxId, cookieValue.webKey, function(err, data) {});
            } else {
                ShowAuxView(auxListData);
            }*/

            if($(this).text()!=="Ready") {
                //console.log("Aux zero");
                // logAgentAux(tempAuxId, cookieValue.webKey);////log agent aux
                tempAuxId = "0";
                obj.ChangeAUX(tempAuxId, cookieValue.webKey, function(err, data) {});
            } else {
                ShowAuxView(auxListData);
            }
        });

        $(document).on("click",".loadAux .drop",function(){

            if(!isLoggedIn) return;

            missedCall = false;

            //if($(this).text("Busy")) {
            //	console.log("Aux zero");
            //	tempAuxId = "0";
            //	obj.ChangeAUX(tempAuxId, cookieValue.webKey, function(err, data) {});
            //} else {
            ShowAuxView(auxListData);
            //}
        });

        $(".loadSkill").click(function(){
            //$("#btnSkill").removeClass("primary").addClass("active");
            if(skilListData.length==0) {
                RecordCurrentSCeq();
                SendGUI("LIST","SKILL");
            } else {
                ShowSkillView(skilListData);
            }
        });

        $("#loadAgent").click(function(){
            RecordCurrentSCeq();
            SendGUI("LIST","AGENT");
        });

        $("#loadASI").click(function(){
            RecordCurrentSCeq();
            SendGUI("LIST","ASI");
        });

        $("#loadIVR").click(function(){
            RecordCurrentSCeq();
            SendGUI("LIST","IVR");
        });

        $(".ccd .close").click(function(){
            $("#btnTransfer").removeClass("active").addClass("primary");
            $(this).parent().parent().hide();
            //isOutBoundCall = false;
        });


        $("#btnHold").click(function(){
            if(!isLoggedIn || cookieValue.callsStack.length==0 || missedCall) return;

            //if($(this).hasClass("active")) {
            //obj.UnHold(currentCallId);
            //Unhold(currentCallId);
            //$(this).removeClass("active").addClass("primary");
            //} else {
            //obj.Hold(currentCallId);
            if($(this).hasClass("primary")) {
                Hold(currentCallId);
                $(this).addClass("active").removeClass("primary");
            }
        });

        $("#btnUnhold").click(function(){
            if(!isLoggedIn || cookieValue.callsStack.length==0 || missedCall) return;

            if(cookieValue.callsStack.length==1) {
                var o = cookieValue.callsStack[0];
                Unhold(o.callId);
                ChangeHoldStatToUnhold(o.callId,false);
                return;
            }

            var html = "";
            for(i=0; i<cookieValue.callsStack.length; i++) {
                var o = cookieValue.callsStack[i];
                if(!o.callHold) continue;
                html += "<tr><td>"+o.CLI+"</td><td>"+o.CName+"</td><td width='100'>Hold Time: <span id='ht"+o.CLI+"'>01:48</span></td><td><input type='button' value='Unhold' onClick=\"Unhold('"+o.callId+"')\"></td></tr>";
            }
            $("#holdedCallList").html(html);
            updateCallHoldTime();

            $(this).removeClass("primary").addClass("active");
            $(".ccd .unhold-view").show();

        });


        $("#btnTransfer").click(function(e){
            console.log("btnTransfer Clicked");
            if(!isLoggedIn ||  cookieValue.callsStack.length==0 || missedCall) return;

            $("#btnTransfer").removeClass("primary").addClass("active");
            $(".ccd .transfer-view").show();

            $(".ccd .dial-control .fields").hide();
            $("#btnCall").removeClass("active").addClass("primary");

            if(Object.keys(skillListToTransferCall).length > 0) {

                var listRow = "<option value=''>None</option>";

                for (var key in skillListToTransferCall) {
                    listRow += "<option value='" + skillListToTransferCall[key].skill_id + "'>" + skillListToTransferCall[key].skill_name + "</option>";
                }

                // $optSkill.find('option').remove().end().append(listRow);
                $optSkill.empty().append(listRow);

                $optSkill.trigger("change");
            }
	    //console.log(currentSkillId);
            if(ivrListData.length > 0) {
		//console.log(currentSkillId);

                var listRow = "<option value=''>None</option>";

                for(i=0; i<ivrListData.length; i++) {

                    if (ivrListData[i].skill_id === "" || ivrListData[i].skill_id === currentSkillId) {
                        if (ivrListData[i].verified_call_only !== 'Y' || currentAuthStatus === 1) {
                            listRow += "<option value='"+ivrListData[i].ivr_branch+"'>"+ivrListData[i].title+"</option>";
                        }
                    }
                }

                // $ivrList.find('option').remove().end().append(listRow);
                $ivrList.empty().append(listRow);

                $ivrList.trigger("change");
            }

            if(Object.keys(supervisorListData).length > 0) {
                var listRow = "<option value=''>None</option>";
                $.each(supervisorListData, function(id, name){
                    listRow += "<option value='"+ id +"'>"+ name +"</option>";
                });

                // $supervisorList.find('option').remove().end().append(listRow);
                $supervisorList.empty().append(listRow);
                $supervisorList.trigger("change");
            }

            if(Object.keys(allAgentListData).length > 0) {
                var listRow = "<option value=''>None</option>";
                $.each(allAgentListData, function(id, name){
                    listRow += "<option value='"+ id +"'>"+ name +"</option>";
                });

                // $allAgentListData.find('option').remove().end().append(listRow);
                $allAgentListData.empty().append(listRow);
                $allAgentListData.trigger("change");
            }

            e.stopPropagation();
        });
        /*
        $("#clearPopupScreen").click(function(){
            $("#purl").attr('src', "").hide();
        });*/
        $("#resetCallSession").click(function(){
            ResetCallSession();
        });



        HotKeyRegister(hotKeys);

        /*
        $("#saveSeat").click(function(){

            var seat_id = $("#seat_id").val();
            var supervisor_id = $("#supervisor_id").val();
            var supervisor_pass = $("#supervisor_pass").val();
            //thisPcMac
            //var url = echo $this->url("task=agent&act=assignseat")";
            //alert(url);
            //var data = '{"seat":'+seat_id+',"pcmac":'+thisPcMac+',"suid":'+supervisor_id+',"supass":'+supervisor_pass+'}';
            //alert("Sending Data: "+data);
            $.ajax({
                type:'POST',
                url:url,
                data:{"seat":seat_id,"pcmac":thisPcMac,"suid":supervisor_id,"supass":supervisor_pass},
                dataType:"json",
                cache: false,
                success: function(response) {
                    if(response.status) {
                        try {

                            $("#seatAssignForm").remove();
                            $(".ccd").show();

                            $(".seat").html(seat_id);

                            obj.Login();

                        } catch (ex) {
                            alert("Warning1: " + ex.message);
                            $(".ccd").show();
                        }
                    } else {
                        alert(response.msg);
                    }
                },
                error: function(e) {
                    //called when there is an error
                    alert(e.message);
                }
            });
        });*/


        /* Never allow multiple selected value for call transfer
        * for example agent can't select Skill and IVR at the same
        * time to transfer a call--------------------------------*/

        $("#trns_agent").keypress(function(){
            /* $("#optSkill").val("").trigger('change');
             $("#ivrList").val("").trigger('change');
             $("#trns_ext_number").val("").trigger('change');
             $("#supervisorList").val("").trigger('change');
             $("#allAgentList").val("").trigger('change');*/
            // $("#trns_ext_number, #optSkill, #ivrList, #supervisorList, #allAgentList").val("").trigger('change');
        });
        $("#trns_ext_number").keypress(function(){
            /*  $("#trns_agent").val("").trigger('change');
              $("#optSkill").val("").trigger('change');
              $("#ivrList").val("").trigger('change');
              $("#supervisorList").val("").trigger('change');
              $("#allAgentList").val("").trigger('change');*/
            // $("#trns_agent, #optSkill, #ivrList, #supervisorList, #allAgentList").val("").trigger('change');
        });
        $("#optSkill").change(function(){
            /*  $("#trns_agent").val("").trigger('change');
              $("#ivrList").val("").trigger('change');
              $("#trns_ext_number").val("").trigger('change');
              $("#supervisorList").val("").trigger('change');
              $("#allAgentList").val("").trigger('change');*/

            // $("#trns_agent, #trns_ext_number, #ivrList, #supervisorList, #allAgentList").val("").trigger('change');
        });
        $("#ivrList").change(function(){
            /*   $("#trns_agent").val("").trigger('change');
               $("#optSkill").val("").trigger('change');
               $("#trns_ext_number").val("").trigger('change');
               $("#supervisorList").val("").trigger('change');
               $("#allAgentList").val("").trigger('change');*/
            //  $("#trns_agent, #trns_ext_number, #optSkill, #supervisorList, #allAgentList").val("").trigger('change');
        });

        $("#supervisorList").change(function(){
            /* $("#trns_agent").val("").trigger('change');
            $("#optSkill").val("").trigger('change');
            $("#trns_ext_number").val("").trigger('change');
            $("#ivrList").val("").trigger('change');
            $("#allAgentList").val("").trigger('change'); */
            // $("#trns_agent, #trns_ext_number, #optSkill, #ivrList, #allAgentList").val("").trigger('change');
        });

        $("#allAgentList").change(function(){
            /*    $("#trns_agent").val("").trigger('change');
                $("#optSkill").val("").trigger('change');
                $("#trns_ext_number").val("").trigger('change');
                $("#ivrList").val("").trigger('change');
                $("#supervisorList").val('').trigger('change');*/

            //  $("#trns_agent, #trns_ext_number, #optSkill, #ivrList, #supervisorList").val("").trigger('change');
        });



        $("#dialNo").keypress(function(e){
            var k = e.keyCode === 0 ? e.which : e.keyCode;
            if(k === 13) {
                MakeCall();
            }
        });

        $("#btnAuthenticated").click(function(){
            try {
                if (obj) {
                    obj.Authenticated(currentCallId, cookieValue.webKey);
                    $("#btnAuthenticated").hide();
                } else {
                    //alert("Object is not created!");
                }
            } catch (ex) {
                //alert("Warning2: " + ex.message);
            }
        });

        $("#textMenu").click(function(){
            $.colorbox({ href:"<?php echo $this->url("task=agent&act=sendtext");?>", width:500, height:335});
        });

        //---------call log------------
        $("#callLog").click(function(){
            if($("#callLogWindow").length === 0) {
                var html = "<div class='call-log-window' id='callLogWindow'>\
							<div class='tab-call-log'>\
								<div class='tabCL tabFirst active' rel='inbound'>Incoming Call</div>\
								<div class='tabCL' rel='outbound'>Outgoing Call</div>\
								<div class='btn-close' style='float:right; border: 1px solid #999999; background-color:#ffffff;'>&times;</div>\
								<div class='clear'></div>\
							</div>\
							 <div class='call-log-container'>\
							 <table class='table table-bordered text-center' style='margin-bottom:0'>\
								<thead><tr class=''><th class='text-center'>Number</th><th class='text-center'>Time</th><th class='text-center'>Duration</th></tr></thead>\
								<tbody id='inboundCallLog' class='inbound'>\
									\
								</tbody>\
								<tbody id='outboundCallLog' class='outbound'>\
									\
								</tbody>\
							</table>\
							</div>\
						</div>";
                $('body').append(html);
            }



            $(".tabCL, .tabFirst").click(function(){
                $(".tabCL").removeClass('active');
                $(this).addClass('active');
                var rel = $(this).attr("rel");
                var it = this;

                var url = "<?php echo $this->url('task=get-agent-data&act=agentskillcdr&type=')?>" + rel;
                $.ajax({
                    type:"POST",
                    url:url,
                    dataType: 'json',
                    success: function(resp) {
                        populateCalllog(resp.rowdata, it);
                    }
                });
            });

            $("#callLogWindow .btn-close").click(function(){
                $("#callLogWindow").remove();
            });

            $(".tabFirst").click();
        });

        /*---------Session Summary------------ */
        $("#sessionSummary").click(function(){
            if($("#sessionSummaryWindow").length === 0) {

                var html = "<div class='call-log-window' id='sessionSummaryWindow'>\
                    <div class='tab-call-log'>\
                    <div class='btn-close'>&times;</div>\
                    <div class='clear'></div>\
                    </div>\
                    <div class='call-log-container'>\
                        <table class='table table-bordered small' style='margin-bottom: 0'>\
                            <tr> <th>Agent : </th> <td class='agent'></td></tr>\
                            <tr> <th>Shift : </th> <td class='shift_code'></td></tr>\
                            <tr> <th>Logged In : </th> <td class='first_login'></td></tr>\
                            <tr> <th>Logged In Duration : </th> <td class='staff_time'></td></tr>\
                            <tr> <th>Wrap Up Time : </th> <td class='wrap_up_time'></td></tr>\
                            <tr> <th>Not Ready Time : </th> <td class='total_break_time'></td></tr>\
                            <tr> <th>Login Count : </th> <td class='login_count'></td></tr>\
                        </table>\
                    </div>\
					</div>";
                $('body').append(html);
            }

            var url = "<?php echo $this->url('task=agent&act=get-session-summary') ?>";

            $.ajax({
                type:"POST",
                url:url,
                dataType: 'json',
                data: {
                },

                success: function(resp) {
                    populateSessionSummary(resp);
                }
            });

            $(".btn-close").click(function(){
                $(this).closest('#sessionSummaryWindow').remove();
            });

        });


        /*----------------------Session Summary------------------------*/


        /*---------------------Last 15 days call history ------------ */

        $("#callHistory").click(function(){

            if($("#callHistoryWindow").length > 0) {
                return;
            }

            var html = "<div class='call-log-window' id='callHistoryWindow'>\
                    <div class='tab-call-log'>\
                    <div class='btn-close'>&times;</div>\
                    <div class='clear'></div>\
                    </div>\
                    <div class='call-log-container'>\
                        <table class='table table-bordered small text-center' style='margin-bottom: 0'>\
                            <tr> <th class='text-center' >Reporting Date  </th>  <th class='text-center'>Shift</th> <th class='text-center'>Answered Calls</th> <th class='text-center'>Outbound Attempted</th> <th class='text-center'>Outbound Reached</th></tr>\
                        </table>\
                    </div>\
					</div>";
            $('body').append(html);


            var url = "<?php echo $this->url('task=get-report-new-data&act=agent-call-history') ?>";

            $.ajax({
                type:"POST",
                url:url,
                dataType: 'json',
                data: {
                    ms : $.param({
                        ms: {
                            duration: 15
                        }
                    }),
                    isMultiSearch: true
                },

                success: function(resp) {
                    populateCallHistory(resp.rowdata);
                }
            });

            $(".btn-close").click(function(){
                $(this).closest('#callHistoryWindow').remove();
            });

        });


        /*----------------------Session Summary------------------------*/
    });

    function populateCalllog(inCalls, it)
    {
        $("#inboundCallLog, #outboundCallLog").hide();
        var rel = $(it).attr("rel");
        var $container = $("#"+rel+'CallLog');
        $container.show();

        var html = "";
        for(i=0; i<inCalls.length; i++) {
            var o = inCalls[i];
            var call_to = rel=='inbound' ? o.cli : o.callto;
            html += "<tr class=''><td class=''>"+call_to+"</td><td class=''>"+o.start_time+"</td><td class=''>"+o.service_time+"</td></tr>";
        }
        $container.html(html);

        $(".call-log-window .call-log-container .row").unbind("click").bind("click",function(e){
            $(".call-log-window .call-log-container .row").removeClass("active");
            var phone_no = $(this).find(".cell:first").text();

            $(this).addClass("active");
            $("#btnCall").click();
            $("#dialNo").val(phone_no);
        });
    }


    function populateSessionSummary(data)
    {
        $.each(data, function (index, value) {
            $("#sessionSummaryWindow .agent").html(value.agent_id);
            $("#sessionSummaryWindow .shift_code").html(value.shift_code);
            $("#sessionSummaryWindow .first_login").html(value.first_login);
            $("#sessionSummaryWindow .staff_time").html(value.staff_time+" (Until previous session)");
            //$("#sessionSummaryWindow .no_login_time").html(value.no_login_time);
            //$("#sessionSummaryWindow .total_aux_in_time").html(value.total_aux_in_time);
            //$("#sessionSummaryWindow .total_aux_out_time").html(value.total_aux_out_time);
            $("#sessionSummaryWindow .wrap_up_time").html(value.wrap_up_time);
            $("#sessionSummaryWindow .total_break_time").html(value.total_break_time);
            //$("#sessionSummaryWindow .total_unready_time").html(value.total_unready_time);
            $("#sessionSummaryWindow .login_count").html(value.login_count);
        });
    }


    /*------------------Call Summary----------------------*/

    function populateCallHistory(data) {
        var html = '';
        if (data == null){
            html = "<tr colspan='4'><td class='text-center text-danger'>No Record Found!</td></tr>";
        }else{
            $.each(data, function (index, value) {
                html += "<tr> <td class=''>"+ value.sdate +"</td> <td class=''>"+ value.shift_code +"</td> <td class=''>"+ value.calls_in_ans +"</td> <td class=''>"+ value.calls_out_attempt +"</td> <td class=''>"+ value.calls_out_reached +"</td></tr>";
            });
        }

        $(".call-log-container table").append(html);
    }

    function updateCallHoldTime() {

        var endTime = new Date();

        for( i=0; i<cookieValue.callsStack.length; i++ ) {
            var o = cookieValue.callsStack[i];

            if(!o.callHold) continue;

            var startTime = new Date(o.callTime);

            var seconds = get_time_diff(startTime, endTime);

            var minutes = Math.floor(seconds / 60);
            var seconds = parseInt(seconds - minutes * 60);
            minutes = minutes<10 ? "0"+minutes : minutes;
            seconds = seconds<10 ? "0"+seconds : seconds;

            $holdedCallList.find("#ht"+o.CLI).html(minutes+":"+seconds);


        }
        holdTimeout = setTimeout(updateCallHoldTime, 1000);
    }

    function fixNameCliForInboundAndOutbound(callType) {
        callType === "IN" ? $cname.empty() : $cli.empty();
    }



    function LoadCall(cli, cname, callid, skill, lang, did, holdTime, callsInQ, callsInService, callsAuthenticated)
    {
        $callTransferWindow.show();
        $callTransferCancelWindow.hide();

        $callDuration.html("00:00");
        $cli.html(cli);
        cname = cname.replace('"','');
        if (cli !== cname){
            $cname.html(cname);
        }

        var skillId = skill;
        if(skill.length>0) {
            console.log("Cons .. display .. for skill name");
            console.log(skilListData);
            var result = $.grep(skilListData, function(e){ return e.skill_id == skill; });
            var o = result[0];
            if (typeof o !== 'undefined') {
                skill = o.skill_name;
            }
            try {

            }catch (e) {

            }
            getDispositionBySkillId(skillId);
        }

        if(did.length>0) {
            try {
                if (did_prefix_replace.length){
                    did_prefix_replace.forEach(function (item, index) {
                        if(did.startsWith(item.from)){
                            did = did.replace(item.from, item.to ? item.to : '' );
                        }
                    });
                }
            }catch(e){
                console.error(e);
            }

            skill += "(" + did + ")";
        }
        $skill.html(skill);
        $lang.html(lang);
        if(holdTime.length>0) $holdTime.html(secondsToMinSec(holdTime));
        $callsInQ.html(callsInQ);
        $callsInService.html(callsInService);

        datetime = new Date();

        $("#btnHold").removeClass("active").addClass("primary");

        var curTime = new Date();
        cookieValue.callsStack.push({
            "callId":callid,
            "callType":"IN",
            "CLI":cli,
            "did":did,
            "skill":skill,
            "skillId": skillId,
            "CName":cname,
            "lang":lang,
            "holdTime":holdTime,
            "callTime":curTime,
            "callReceived":false,
            "callHold":false,
            "callsAuthenticated":callsAuthenticated
        });

        $.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));

    }

    function CallTransferSuccess()
    {
        $(".dial-view").show();
        $(".dial-button-view").hide();
        CancelCall(transferCallId);
    }

    function CancelCall(callid)
    {
        if(typeof callid === 'undefined' || callid == null || callid.length < 2) {
            return false;
        }
        var result = $.grep(cookieValue.callsStack, function(e){ return e.callId == callid; });
        var o = result[0];
        var index = cookieValue.callsStack.indexOf(o);
        cookieValue.callsStack.splice( index, 1 );
        $.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));
        if(!o.callHold) {
            clearTimeout(duration_timeout);
            //$callDuration.html("00:00");
            //$cli.html("");
        }

        if(cookieValue.callsStack.length==0)
        {
            clearTimeout(duration_timeout);
            $holdSection.hide();
            $unHoldSection.hide();
            $transferSection.hide();
            $(".dial-button-view").hide();
            isOutBoundCall = false;
        }
    }

    function CallSuccess()
    {
        console.log('CallSuccess: ' + isInCall);
        if (isInCall) {
            //$holdSection.show();
            //$transferSection.show();
            $(".dial-view").hide();
            $(".dial-button-view").show();
        }
    }

    function SetAuxCode(code)
    {
        if (typeof code !== 'undefined' && code.length>0) {
            cookieValue.selectedAuxId = code;
            $.cookie(cookieVars.selectedAuxId, cookieValue.selectedAuxId);
            if (code != "0") {
                if (code == "21") {
                    $("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("<span class='caption'>Bounced Call</span>");
                } else {
                    var txt = "Busy";
                    try {
                        var result = $.grep(auxListData, function(e){ return e.aux_code == code; });
                        var o = result[0];
                        txt = o.message;
                    } catch (exception) {}
                    $("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("<span class='caption'>"+txt+"</span><span class='drop'></span><div class='clear'></div>");
                }
            } else {
                $("#btnAux").addClass("primary").removeClass("busy active").html("<span class='caption'>Ready</span><span class='drop'></span><div class='clear'></div>");
            }
        }
    }

    function ShowAuthenticatedButton()
    {
        $("#btnAuthenticated").show();
    }

    function TransferCallBusy()
    {
        $(".dial-view").show();
        $(".dial-button-view").hide();
    }

    function AnswerCall(callid)
    {
        $(".ccd .transfer-view").hide();
        $("#btnTransfer").removeClass("active").addClass("primary");

        currentCallId = callid;
        datetime = new Date();
        currentSkillId = "";
        currentAuthStatus = 0;
        var result = $.grep(cookieValue.callsStack, function(e){ return e.callId == callid; });

        if(result.length>0) {
            var o = result[0];
            var index = cookieValue.callsStack.indexOf(o);
            o.callReceived = true;
            o.callTime = new Date();
            currentSkillId = o.skillId;
            currentAuthStatus = o.callsAuthenticated;
            cookieValue.callsStack[index] = o;

            if(o.callsAuthenticated==0) ShowAuthenticatedButton();

            $.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));
        }
        //if (cookieValue.callsStack.length>0) {
        //	$(".dial-view").hide();
        //	$(".dial-button-view").show();
        //} else {
        //$holdSection.show(); //commented to hide hold button temporarilly
        console.log("isOutBoundCall (2) ------------------------- timer called : " + isOutBoundCall);
        /*
        if (!isOutBoundCall) $transferSection.show();
        else $transferSection.hide();
        */
        $transferSection.show();
        $(".ccd .fields").hide();
        $("#btnCall").removeClass("active").addClass("primary");
        $("#dialNo").val("");
        //$("#skillList").val("");
        //}

        timerFunction();
    }

    function HangupCall(callid)
    {
        if(typeof callid === 'undefined' || callid == null || callid.length < 2) {
            return false;
        }

        RemoveCallFromCallsStack(callid);
        clearTimeout(duration_timeout);

        if(cookieValue.callsStack.length==0)
        {
            $holdSection.hide();
            $unHoldSection.hide();
            $transferSection.hide();
            isOutBoundCall = false;
        }
    }

    function ResetCallSession()
    {
        cookieValue.callsStack = [];
        $.cookie(cookieVars.callsStack,cookieValue.callsStack);
        clearTimeout(duration_timeout);

        //if(cookieValue.callsStack.length==0)
        //{
        $holdSection.hide();
        $unHoldSection.hide();
        $transferSection.hide();
        isOutBoundCall = false;
        //}
    }

    function HoldCall(callid)
    {
        //datetime = new Date();
        //callid = holdedCallId;
        //holdedCallId = "";

        var result = $.grep(cookieValue.callsStack, function(e){ return e.callId == callid; });
        var o = result[0];
        var index = cookieValue.callsStack.indexOf(o);
        o.callHold = true;
        cookieValue.callsStack[index] = o;
        $.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));

        $holdSection.hide();
        //$unHoldSection.show(); //commented to hide hold button temporarilly

        //$transferSection.show();

    }

    function UnHoldCall(callid)
    {
        callid = unHoldedCallId;
        unHoldedCallId = "";

        try{
            let result = $.grep(cookieValue.callsStack, function(e){ return e.callId === callid; });
            let call_object = result[0];
            let index = cookieValue.callsStack.indexOf(call_object);
            call_object.callHold = false;
            cookieValue.callsStack[index] = call_object;
            $.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));

            result = $.grep(cookieValue.callsStack, function(e){ return e.callHold === true; });
            if(result.length === 0) {
                $unHoldSection.hide();
            }
        }catch(e){console.log(e);}
    }

    function RemoveCall(callid)
    {

        if(typeof callid === 'undefined' || callid == null || callid.length < 2) {
            return false;
        }
        showDispositionSetArea();
        RemoveCallFromCallsStack(callid);
        //clearTimeout(duration_timeout);
        clearTimerFunction();
        console.log(cookieValue.callsStack);
        if(cookieValue.callsStack.length==0)
        {
            $holdSection.hide();
            $unHoldSection.hide();
            $transferSection.hide();
            isOutBoundCall = false;
        }
        $("#btnAuthenticated").hide();
    }

    function RemoveAuxView()
    {
        $("#btnAux ul").remove();
        $("#btnAux .drop").removeClass("active");
        $("#btnAux").removeClass("active").addClass("primary");
    }

    function RemoveCookie()
    {
        $.cookie(cookieVars.selectedAuxId,null);
        //$.cookie(cookieVars.selectedSkillId,null);
        $.cookie(cookieVars.callsStack,null);
        $.cookie("wsCCMissedCallAck", null);
        $.cookie("wsCCReturnCallAck",null);
        $.cookie("chatBoxes", JSON.stringify({}));
    }

    function Login(){
        try {
            if (obj) {
                obj.Login();
            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning2: " + ex.message);
        }
    }

    function loginSuccessCallback(){
        /* Show disposition button in call control bar */
        $("#disposition-set-button .btn").removeClass('hide');
    }

    function Logout(){
        try {
            if (obj) {

                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->url("task=agent&act=add-web-sock-logged-out");?>"
                }).done(function (data) {
                    //opener.location.reload();
                    RemoveCookie();
                    window.close();
                });

                obj.Logout(cookieValue.webKey);
                isLoggedIn = false;
                $("#disposition-set-button .btn").addClass('hide'); //Hide disposition button in call control bar
            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning3: " + ex.message);
        }
    }

    /*function logAgentAux(aux_code, web_key) {
        $.ajax({
            dataType : "JSON",
            type     : "POST",
            url      : "<?php echo $this->url("task=agent&act=log-aux"); ?>",
            data     : {aux_code: aux_code, web_key: web_key},
            success     : function (res) {
                console.log(res);
            }
        });
    }*/

    function MakeCall() {
        try {
            if (obj) {

                var dialNo = $('#dialNo').val();
                dialNo = dialNo.replace("+", "");
                dialNo = dialNo.replace("(", "");
                dialNo = dialNo.replace(")", "");
                dialNo = dialNo.replace(" ", "");
                dialNo = dialNo.replace("-", "");
                if(dialNo.length==0) return;
                var skillid = $("#skillList").val();

                alert(dialNo);

                if(cookieValue.callsStack.length>0) {
                    //$(".agent-transfer-item").show();

                    /*
                    $(".dial-view").hide();
                    $(".dial-button-view").show();
                    */
                    isInCall = true;
                    var o = cookieValue.callsStack[0];
                    transferAgentId = dialNo;
                    transferCallId = currentCallId;//o.callId;
                    //obj.InServiceCall( dialNo, o.callId );
                    obj.InServiceCall( dialNo, currentCallId, cookieValue.webKey);
                    inCallInfo = o;
                } else {
                    //alert("Calling to: "+dialNo);
                    isInCall = false;
                    inCallInfo = null;
                    isOutBoundCall = true;
                    console.log("isOutBoundCall = " + isOutBoundCall);
                    obj.Dial( dialNo, cookieValue.webKey, skillid );
                }
                return;

                /*
                if(!isInCall)  {
                    isInCall = true;
                    $("#btnDial").removeClass("dial").addClass("hangup");
                    var dialNo = $('#dialNo').val();
                    alert("Dialing to: "+dialNo);
                    //obj.DialNumber = bla;
                    //obj.MakeCall( "9726794804", "4804", "9725342205", "SIP");
                    obj.MakeCall( dialNo, "SIP");

                    $(".tempButtons").show();

                    timer--Function();

                } else {
                    isInCall = false;
                    $("#btnDial").removeClass("hangup").addClass("dial");

                    alert("Call ID:"+callid);
                    obj.Hangup(callid);
                    callid = "";

                    //$(".tempButtons").hide();
                    clearTimeout(myVar);

                }*/
            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning4: " + ex.message);
        }
    }

    function Answer(){
        try {
            if (obj) {
                //var bla = $('#txt_name').val()
                //obj.DialNumber = bla;
                //obj.Answer( "9726794804", "CALL_ID");
                obj.Answer( currentCallId, cookieValue.webKey);
            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning5: " + ex.message);
        }
    }

    function Hangup(){
        try {
            if (obj) {
                //obj.Hangup( "9726794804", "CALL_ID");
                //alert(callid);
                obj.Hangup(callid, cookieValue.webKey);
            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning6: " + ex.message);
        }
    }

    function ChangeAUX(){
        try {
            if (obj) {
                //obj.ChangeAUX( "9726794804", "LUNCH", "YES");

                var auxId = $("#auxList").val();
                //alert(auxId);
                tempAuxId = auxId;
                $(".aux-view").remove();
                obj.ChangeAUX( auxId, cookieValue.webKey );
            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning7: " + ex.message);
        }
    }

    function LoadSkill() {
        try {
            if (obj) {
                //obj.LoadSkill("9726794804");
                obj.LoadSkill(cookieValue.webKey);
            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning8: " + ex.message);
        }
    }

    function LoadIVR(){
        try {
            if (obj) {
                //obj.LoadIVR("9726794804");
                obj.LoadIVR(cookieValue.webKey);
            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning9: " + ex.message);
        }
    }

    function Transfer(){
        try {
            if (obj) {
                var transfer_to = '';

                var agid = $("#allAgentList").val();
                var sqid = $("#optSkill").val();
                var ivid = $("#ivrList").val();
                var supervisor_id = $("#supervisorList").val();

                if (agid !== null && typeof agid !== 'undefined' && agid.length > 0) {
                    transfer_to = "AG"+agid;
                }
                else if(sqid !== null && typeof sqid !== 'undefined' && sqid.length > 0) {
                    transfer_to = "SQ"+sqid;
                    obj.Transfer(currentCallId, transfer_to, cookieValue.webKey);
                    return;
                } else if(ivid !== null && typeof ivid !== 'undefined' && ivid.length > 0) {
                    transfer_to = "IV"+ivid;
                    obj.Transfer(currentCallId, transfer_to, cookieValue.webKey);
                    return;
                } else if(supervisor_id !== null && typeof supervisor_id !== 'undefined' && supervisor_id.length > 0) {
                    transfer_to = "AG"+supervisor_id;
                }

                // Assign data to globally so that we can use it later to cancel or actual transfer
                commonTransferTo = transfer_to;
                transferCallId = currentCallId;

                //Before transfer make a call to the agent/supervisor/skill and if response is true than transfer
                var call_to = transfer_to.slice(2);
                obj.InServiceCall(call_to, currentCallId, cookieValue.webKey);

            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning10: " + ex.message);
        }
    }

    function TransferCancel()
    {
        isInCall = false;
        obj.TransferCancel( currentCallId, cookieValue.webKey );
        $callTransferCancelWindow.hide();
        $callTransferWindow.show();

        TransferCallBusy();

        clearTimeout(duration_timeout);
    }

    function Hold(callid){

        try {
            if (obj) {
                //obj.Hold("9726794804", "CALL_ID");
                //$("#unHoldSection").show();
                //holdedCallId = callid;
                obj.Hold(callid, $.cookie("wsCCSeatId"), cookieValue.webKey);
            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning11: " + ex.message);
        }
    }

    function Unhold(callid){
        //obj.Hold("9726794804", "CALL_ID");

        try {
            if (obj) {

                //ChangeHoldStatToUnhold(callid,false);
                unHoldedCallId = callid;

                obj.UnHold(callid, $.cookie("wsCCSeatId"), cookieValue.webKey);

            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            alert("Warning11: " + ex.message);
        }
    }

    function PDLoggedIn()
    {
        PDStatus = 1;
        $("#btnPD").removeClass("pd-off").addClass("pd-on");
    }

    function PDLoggedOut()
    {
        PDStatus = 0;
        $("#btnPD").removeClass("pd-on").addClass("pd-off");
    }

    function PDStatusUpdate(statusMsg)
    {
        $("#pdStatus").html(statusMsg);
    }

    function generatePDSkillChooser(skills = []) {
        let pd_skill_select_box = `
            <div id='inline_content' style='padding:10px; background:#fff;'>
            <div class="form-group">
            <label class="control-label">Skill:</label>
            <select class="form-control" id="choose-pd-skill">`;
        pd_skill_select_box += `<option value=''>---Select---</option>`;
        skills.forEach((skill_obj, index) => {
            pd_skill_select_box += `<option value="${skill_obj.skill_id}">${skill_obj.skill_name} </option>`;
        });

        pd_skill_select_box += `</select>
            </div>
            <div class="form-group">
            <button type="button" id="submit-selected-pd-skill" class="btn btn-success pull-right">Submit</button>
            </div>
            </div>
`;
        $.colorbox({ html: pd_skill_select_box, 'iframe':false, 'width':'300px', 'height':'200px'});
    }

    function PD_Action(callid){
        try {
            if (obj) {
                if (PDStatus == 0) {
                    $.ajax({
                        type: "POST",
                        url: pdSkillUrl,
                        dataType: "json"
                    }).done(function (data) {
                            if (data != null && data.length === 1) {
                                obj.PD_Action(PDStatus, data[0].skill_id, cookieValue.webKey);
                            }
                            if (data != null && data.length > 1) {
                                generatePDSkillChooser(data);
                            }
                        });
                } else {
                    obj.PD_Action(PDStatus, '', cookieValue.webKey);
                }
            }
        } catch (ex) {
            alert("Warning11: " + ex.message);
        }
    }

    function ChangeHoldStatToUnhold(callid,holdStat)
    {
        var result = $.grep(cookieValue.callsStack, function(e){ return e.callId == callid; });
        var o = result[0];
        var i = cookieValue.callsStack.indexOf(o);
        o.callHold = holdStat;
        cookieValue.callsStack[i] = o;
        $.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));

        result = $.grep(cookieValue.callsStack, function(e){ return e.callHold == true; });
        if(result.length==0) {
            $("#unHoldSection").hide();
        }
        $(".ccd .unhold-view").hide();
        $("#btnHold").removeClass("active").addClass("primary");
        $("#btnUnhold").removeClass("active").addClass("primary");
    }

    function ShowHoldUnholdTransferButton()
    {
        if(cookieValue.callsStack.length>0) {
            //$holdSection.show(); //commented to hide hold button temporarilly
            $transferSection.show();

            var result = $.grep(cookieValue.callsStack, function(e){ return e.callHold == true; });
            if(result.length>0) {
                // $unHoldSection.show(); //commented to hide hold button temporarilly
            }

            var result = $.grep(cookieValue.callsStack, function(e){ return e.callHold == false; });
            if(result.length>0) {
                var o = result[0];
                $cname.html(o.CName);
                $cli.html(o.CLI);
                //$lang.html(o.lang);
                datetime = new Date(o.callTime);
                debug('ShowHoldUnholdTransferButton ------------------------------ timer');
                timerFunction();
            }
        }
    }

    function SyncCallStack()
    {
		console.log("SyncCallStack");
        if (cookieValue.callsStack.length > 0) {
            for(i=0; i<cookieValue.callsStack.length; i++) {
                var o = cookieValue.callsStack[i];
                obj.CallStatus(o.callId, cookieValue.webKey);
            }
        }else if(typeof storageValue.callIdToChat != 'undefined' && storageValue.callIdToChat.length > 0){
			console.log("SyncCallStack --- storageValue");
			var lt = 0;
			recallWebchat(lt);
			/*storageValue.callIdToChat.forEach(function(callId){	
				console.log(lt);
				setTimeout(obj.NVCStatus(callId, cookieValue.webKey), lt);
				//setTimeout(function(){ console.log("NVCStatus "); }, lt);
				lt +=2000;
				console.log(lt);
				//obj.NVCStatus(callId, cookieValue.webKey);
			});*/
		}
    }
	
	function recallWebchat(lt){
		setTimeout(function () {    
			obj.NVCStatus(storageValue.callIdToChat[lt], cookieValue.webKey);
			lt++;                    
			if (lt < storageValue.callIdToChat.length) {            
				recallWebchat(lt);             
			}                        
		}, 400)
	}

    function AgentInService(call_id)
    {
        var result = $.grep(cookieValue.callsStack, function(e){ return e.callId.indexOf(call_id) >= 0; });

        if(result.length>0) {
            return true;
        }
        if (SwCallStatusID == call_id) return true;

        SwCallStatusID = '';
        obj.CallStatus(call_id, cookieValue.webKey);

        return false;
    }

    function AuthenticateUser()
    {
        currentAuthStatus = 1;
    }

    function RemoveCallFromCallsStack(callid)
    {
        clearTimeout(duration_timeout);
        //console.log("RemoveCallFromCallsStack: " + callid);
        var result = $.grep(cookieValue.callsStack, function(e){ return e.callId == callid; });
        var o = result[0];
        console.log("RemoveCall");
        console.log(cookieValue.callsStack);
        console.log(o);
        var index = cookieValue.callsStack.indexOf(o);
        cookieValue.callsStack.splice( index, 1 );
        $.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));
    }

    /*
    functio ChatMsg(receiverId, receiverName) {

        var msg = "Hello World";
        var item = { from:agentId, name:agentId, msg:msg, pic:"ccd/image/pic.png" };
        var receiver = { receiverId:receiverId, receiverName:receiverName };
        SendMsg(item, receiver);
    }
    */

    function SendMsg(from, msg) {
        try {
            if (obj) {
                //obj.SendMessage("9726794804", "9725342205", "Hello World");
                //obj.SendMessage("9725342205", "Hello World");//to_agent_id,message

                //var msg = '{"SCeq":'+currentSCeq+',"Method":"'+Method+'","Type":"'+Type+'"}';
                //var encodedMsg = btoa(msg);

                obj.SendMessage(from, msg, cookieValue.webKey);


            } else {
                alert("Object is not created!");
            }
        } catch (ex) {
            //alert("Some error happens, error message is: " + ex.Description);
            alert("Warning12: " + ex.message);
        }
    }

    function ChangeSkill() {
        //alert("Not implemented yet");
        //RecordCurrentSCeq();
        //SendGUI("LIST","XSKILL");

        var skillId = $("#skillList").val();
        cookieValue.selectedSkillId = skillId;
        $.cookie(cookieVars.selectedSkillId,cookieValue.selectedSkillId);


        //tempSkillId = skillId;
        obj.ChangeSkill(skillId, cookieValue.webKey, function(err, data) {});

    }


    function SendGUI(Method, Type){


        var guiMsg = '{"SCeq":'+currentSCeq+',"Method":"'+Method+'","Type":"'+Type+'","agent_id":"'+agentId+'"}';
        var encodedGuiMsg = btoa(guiMsg);

        //var decodedString = atob(encodedString);
        //alert(decodedString);

        var curTime = new Date();
        SessionData.push({"SCeq":currentSCeq,"Type":Type,"Time":curTime});

        if(!startedSessionThread) checkSession();



        obj.SendGUI(encodedGuiMsg, cookieValue.webKey, function(err, data) {
            console.log(data);
        });

        /*
        //var data = '{"RespCode":"200","data":[{"aux_code":"01","message":"Lunch"}],"SCeq":"'+currentSCeq+'"}';
        var data = '{"RespCode":"200","data":[{"skill_id":"01","skill_name":"Skill-A"}],"SCeq":"'+currentSCeq+'"}';
        var msg = "GUI|200|"+btoa(data)+"|";
        TempJsCallBack(msg);
        */

    }

    function RecordCurrentSCeq() {
        if(currentSCeq>9999) currentSCeq = 201;
        else currentSCeq++;
    }
    /*
    function EventListener::JsCallBack(msg){

        //alert(msg);

        if(typeof(msg) === 'undefined') {
            alert("No value");
            return;
        }

        msg = trimChar(msg, "|");

        var resp_ary = msg.split("|");

        switch(resp_ary[0]) {
            case "LOGIN":
                //var data = atob(resp_ary[1]);
                if(resp_ary[1] == 401) {
                    alert(resp_ary[2]);
                } else if(resp_ary[1] == 200) {
                    seat_id = resp_ary[3];
                    $(".seat").html(seat_id);
                    thisPcMac = resp_ary[4];
                    $(".ccd").show();

                    RecordCurrentSCeq();
                    //SendGUI("LIST","IVR");
                    SendGUI("LIST","ASI");

                } else {
                    $(".ccd").hide();
                    thisPcMac = resp_ary[4];
                    $("#seatAssignForm").show();
                }
                break;
            case "GUI":
                var data = atob(resp_ary[2]);
                //alert(data);
                var jsonData = jQuery.parseJSON(data);

                var res_code = jsonData.ResCode;

                var sceq = jsonData.SCeq;

                var result = $.grep(SessionData, function(e){ return e.SCeq == sceq; });
                var type = result[0].Type;

                SessionData = jQuery.removeFromArray(result[0], SessionData);

                if(type=="ASI") {
                    //alert("jsonData.data.AUX: "+jsonData.data.AUX);
                    //alert("jsonData.data['AUX']: "+jsonData.data["AUX"]);

                    auxListData = jsonData.data.AUX;
                    ShowAuxView(auxListData);

                    skilListData = jsonData.data.SKILL;
                    ivrListData = jsonData.data.IVR;

                    RecordCurrentSCeq();
                    SendGUI("LIST","AGENT");

                } else if(type=="AGENT") {
                    //alert(jsonData.data);
                    agentListData = jsonData.data;

                    //RecordCurrentSCeq();
                    //SendGUI("LIST","SKILL");

                }
    */
    /*
    else if(type=="AUX") {
        auxListData = jsonData.data;
        ShowAuxView(jsonData.data);
    } else if(type=="SKILL") {
        skilListData = jsonData.data;
        //ShowSkillView(jsonData.data);
    } else if(type=="IVR") {
        ivrListData = jsonData.data;

        RecordCurrentSCeq();
        SendGUI("LIST","AGENT");

    }*/

    /*
    break;
case "DIAL":
    alert("Call ID: "+resp_ary[2]);
    callid = resp_ary[2];
    break;
case "CHANGE_AUX":
    if(resp_ary[1]=="200 OK") {
        selectedAuxId = tempAuxId;
        if(selectedAuxId != "0") {
            $("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("<span class='caption'>Busy</span><span class='drop'></span><div class='clear'></div>");
        } else {
            $("#btnAux").addClass("primary").removeClass("busy active").html("<span class='caption'>Ready</span><span class='drop'></span><div class='clear'></div>");
        }
    }
    break;
case "RECVMSG":
    try {
        var msgFrom = resp_ary[1];
        var msg = resp_ary[2];
        //var item = { from: msgFrom, name: msgFrom, msg: msg, pic:"ccd/image/pic.png" };
        //item.from = msgFrom;
        //item.name = msgFrom;
        //item.msg = msg;
        msgReceived(msg);
    } catch (exception) {
        alert("Chat warning: " + exception.message);
    }
    break;
default:
    //alert("Nothing");
    break;
}
}

function EventListener::NewCallEvent( call_id, cli, dial, agent, direction, acc_no ) {
//alert(msg);
$("#status").html("Command: New Call<br>" + "Call-ID: " + call_id + "<br>" + "CLI: " + cli + "<br>" + "Dial: " + dial + "<br>" + "Agent: " + agent + "<br>" + "Direction: " + direction + "<br>" + "Acc-No: " + acc_no + "<br>");
}

function EventListener::DelCallEvent( call_id ) {
//alert(msg);
$("#status").html("Command: Delete Call<br>" + "Call-ID: " + call_id + "<br>");
}

*/


    /*
    function TempJsCallBack(msg){

        alert(msg);

        if(typeof(msg) === 'undefined') {
            alert("No value");
            return;
        }

        msg = trimChar(msg, "|");

        var resp_ary = msg.split("|");

        switch(resp_ary[0]) {
            case "LOGIN":
                //var data = atob(resp_ary[1]);
                if(resp_ary[1] == 401) {
                    alert(resp_ary[2]);
                } else if(resp_ary[1] == 200) {
                    seat_id = resp_ary[3];
                    $(".seat").html("Seat: "+seat_id);
                    thisPcMac = resp_ary[4];
                    $(".ccd").show();
                } else {
                    $(".ccd").hide();
                    $("#seatAssignForm").show();
                }
                break;
            case "GUI":
                var data = atob(resp_ary[2]);
                //alert(data);
                var jsonData = jQuery.parseJSON(data);

                var res_code = jsonData.ResCode;

                var sceq = jsonData.SCeq;

                var result = $.grep(SessionData, function(e){ return e.SCeq == sceq; });
                var type = result[0].Type;

                SessionData = jQuery.removeFromArray(result[0], SessionData);

                if(type=="AUX") {
                    //ShowAuxView(jsonData.data);
                } else if(type=="SKILL") {
                    //ShowSkillView(jsonData.data);
                } else {
                    alert(data);
                }

                break;
            case "DIAL":
                alert(resp_ary[2]);
                callid = resp_ary[2];
                break;
            case "CHANGE_AUX":
                if(resp_ary[1]=="200 OK") {
                    selectedAuxId = tempAuxId;
                    if(selectedAuxId != "0") {
                        $("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("<span class='caption'>Busy</span><span class='drop'>v</span><div class='clear'></div>");
                    } else {
                        $("#btnAux").addClass("primary").removeClass("busy active").html("<span class='caption'>Ready</span><span class='drop'>v</span><div class='clear'></div>");
                    }
                }
                break;
            default:
                //alert("Nothing");
                break;
        }

    }
    */

    function ShowAuxView(auxList)
    {
        if($("#btnAux ul").length>0) return;

        $("#btnAux .drop").addClass("active");

        var rows = "<ul>";
        for(i=0; i<auxList.length; i++) {
            //rows += "<li aux_code='"+auxList[i].aux_code+"'>"+auxList[i].aux+"</li>";
            rows += "<li aux_code='"+auxList[i].aux_code+"'>"+auxList[i].message+"</li>";
        }
        rows += "</ul><div class='clear'></div>";


        $("#btnAux .drop").after(rows);

        $("#btnAux ul li").click(function(e){
            e.stopPropagation();
            try {

                var auxId = $(this).attr("aux_code");
                //alert(auxid);
                tempAuxId = auxId;
                $("#btnAux ul").remove();

                obj.ChangeAUX(tempAuxId, cookieValue.webKey);
            } catch (ex) {
                alert("Warning13: " + ex.message);
            }
        });
    }

    function LoadFootprint(callId)
    {
        console.log("Ajax call: " + callId);
        $.ajax({
            type: "POST",
            url: fprintUrl + "&callid=" + callId,
            dataType: "json"
        }).done(function (data) {
            if (data.data.length >= 1) {
                $("#footprintTxtDtl").html(data.data);
                $("#footprintTitle").html(data.title);
            }
            //$(".ccd .footprint-dtls .fields").show();
        });
        $("#footprint").show();
        $("#footprintTitle").show();
    }

    function ShowFootprint()
    {
        $("#btnFootprint").removeClass("primary").addClass("active");
        /*
        $.ajax({
                type: "POST",
                    url: fprintUrl,
                    dataType: "json"
            }).done(function (data) {
                if (data.data.length >= 1) {
                        $("#footprintTxtDtl").html(data.data);
                        $("#footprintTitle").html(data.title);
                    }
            $(".ccd .footprint-dtls .fields").show();
            });
            */
        //$(".ccd .footprint-dtls .fields").show();
        $(".ccd .footprint-dtls .fields").show();
    }

    function ShowSkillView(auxList)
    {
        debug("Constructing .. SKill ...");
        debug(auxList);
        var auxRow = '';//"<option value=''>&nbsp;</option>";
        auxRow += "<option value='' selected>---Select---</option>";
        for(i=0; i<auxList.length; i++) {
            auxRow += "<option value='"+auxList[i].skill_id+"'>"+auxList[i].skill_name+"</option>";
        }
        //auxRow += "<option value=''>DID</option>";


        $skillList.find('option')
            .remove()
            .end()
            .append(auxRow)
            .val(cookieValue.selectedSkillId)
            .trigger("change");

        //$skillList.trigger("change");
        $("#dialNo").focus();

        $("#btnCall").removeClass("primary").addClass("active");
        $(".ccd .dial-control .fields").show();

        //if (isInCall) {
        //	$(".dial-view").hide();
        //	$(".dial-button-view").show();
        //} else {
        $(".dial-view").show();
        $(".dial-button-view").hide();
        //}

        $(".ccd .transfer-view").hide();
        $("#btnTransfer").removeClass("active").addClass("primary");
    }

    function trimChar(string, charToRemove) {
        while(string.charAt(0)==charToRemove) {
            string = string.substring(1);
        }

        while(string.charAt(string.length-1)==charToRemove) {
            string = string.substring(0,string.length-1);
        }

        return string;
    }

    var ASIreponseFailedCount = 0;
    var AGENTresponseFailedCount = 0;
    function checkSession() {
        startedSessionThread = true;

        for( i=0; i<SessionData.length; i++ ) {
            if(SessionData[i] !== null && typeof SessionData[i] === 'object') {
                var startTime = SessionData[i].Time;
                var endTime = new Date();
                var seconds = get_time_diff(startTime, endTime);

                if(seconds>requestTimeoutInterval) {

                    //var result = $.grep(SessionData, function(e){ return e.SCeq == sceq; });
                    //var type = result[0].Type;
                    //alert("Request Timeout");
                    SessionData = jQuery.removeFromArray(SessionData[i], SessionData);
                    if(typeof SessionData[i]=="undefined") return;
                    if(SessionData[i].Type=="ASI" && ASIreponseFailedCount==0) {
                        ASIreponseFailedCount++;
                        $("#loadASI").click();
                    }
                    if(SessionData[i].Type=="AGENT" && AGENTresponseFailedCount==0) {
                        AGENTresponseFailedCount++;
                        $("#loadAgent").click();
                    }
                }
            }
        }
        timeout = setTimeout(checkSession, 1000);
    }

    function get_time_diff(startTime, endTime)
    {

        //var datetime = typeof datetime !== 'undefined' ? datetime : "2014-01-01 01:02:03.123456";

        //var now = new Date();

        var seconds = (endTime.getTime() - startTime.getTime())/1000;

        return seconds;
        //$("#callDuration").html(seconds);

    }

    function timerFunction() {


        //var datetime = typeof datetime !== 'undefined' ? datetime : "2014-01-01 01:02:03.123456";

        var now = new Date();

        var seconds = (now.getTime() - datetime.getTime())/1000;

        var minutes = Math.floor(seconds / 60);
        var seconds = parseInt(seconds - minutes * 60);
        minutes = minutes<10 ? "0"+minutes : minutes;
        seconds = seconds<10 ? "0"+seconds : seconds;

        $("#callDuration").html(minutes+":"+seconds);

        debug("TimerFunc:");
        debug(cookieValue.callsStack);
        if (cookieValue.callsStack.length > 0) {
            duration_timeout = setTimeout(timerFunction, 1000);
        }
    }

    function clearTimerFunction()
    {
        debug('clearTimerFunction');
        debug(duration_timeout);
        clearTimeout(duration_timeout);
        debug(duration_timeout);
    }

    jQuery.removeFromArray = function(value, arr) {
        return jQuery.grep(arr, function(elem, index) {
            return elem !== value;
        });
    };

    /*
    window.addEventListener("beforeunload", function (e) {
        var confirmationMessage = 'If you leave this page, you dialer session will be lost.';

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });
    */

    /*
    $(window).on('beforeunload', function () {
       return 'If you leave this page, you dialer session will be lost.';
    });
    $(window).on('unload', function () {
       $.ajax({
            type: 'POST',
            url:'localhost/test.php',
            cache:false,
        });
    });
    */

    function HotKeyRegister(hotKeys)
    {
        shortcut.add(hotKeys.CALL, function() {
            $("#btnCall").click();
        });
        shortcut.add(hotKeys.HOLD, function() {
            if(!isHoldedCall) $("#btnHold").click();
            else $("#btnUnhold").click();
        },{
            'type':'keydown',
            'propagate':false,
            'target':document
        });
        //shortcut.add(hotKeys.UNHOLD, function() {
        //	$("#btnUnhold").click();
        //});
        shortcut.add(hotKeys.TRANSFER, function() {
            $("#btnTransfer").click();
        },{
            'type':'keydown',
            'propagate':false,
            'target':document
        });
        shortcut.add(hotKeys.READY, function() {
            if($("#btnAux").hasClass("busy")) {
                $(".loadAux").click();
            }
        });
        shortcut.add(hotKeys.BUSY, function() {
            //$(".loadAux").click();
            if(!$("#btnAux").hasClass("busy")) {
                tempAuxId = auxListData[0].aux_code;
                obj.ChangeAUX(tempAuxId, cookieValue.webKey, function(err, data) {});
            }
        });
        shortcut.add(hotKeys.MENU, function() {
            $('.clicker').click();
        });
    }

    function NatPing()
    {
        /*
        //if (isLoggedIn) {
            var curTime = new Date();
            var seconds = get_time_diff(lastRegMsgSendAt, curTime);
            if(regResponseSuccess) {
                if(seconds>=60) {
                    regResponseSuccess = false;
                    lastRegMsgSendAt = new Date();
                    obj.Reg(cookieValue.webKey);
                }
            } else {
                lastRegMsgSendAt = new Date();
                obj.Reg(cookieValue.webKey);
            }
        //}
        //natPingInterval = setTimeout(NatPing,15000);//55000
        */
        obj.Reg(cookieValue.webKey);
        natPingInterval = setTimeout(NatPing,20000);
    }

    /* Notification */
    function SeatStatus(status, message)
    {
        if($("#seatStatusAck").length>0) return;
        if(status=="200") {
            $("#seatStatusAck").remove();
            return;
        }
        var seatStatusAck = "<div class='ccNotification' id='seatStatusAck'>&nbsp;&nbsp;&nbsp;Seat Status:&nbsp"+message+"&nbsp;&nbsp;&nbsp;</div>";
        //$.cookie("wsCCSeatStatusAck", seatStatusAck);
        SeatStatusNotification(seatStatusAck);
    }
    function SeatStatusNotification(seatStatusAck)
    {
        //var seatStatusAck = $.cookie("wsCCSeatStatusAck");
        //if(seatStatusAck==='null') return;

        $("body").append(seatStatusAck);
        $("#seatStatusAck").animate({
            top:'0px'
        },300);
    }
    function ReturnCallAcknowledge()
    {
        if($("#returnCallAck").length>0) return;
        missAck = "<div class='ccNotification' id='returnCallAck'>";
        missAck += "<div class='left'>This customer was called you before at 03:05 am";
        missAck += "<a class='button' id='btnReturnQueue'>Send to queue</a> or, ";
        missAck += "</div>";
        missAck += "<div class='right'>";
        missAck += "I will attend this call after <input type='text' id='returnCallAttendTime'> min <a class='button' id='btnReturnAttend'>OK</a>";
        missAck += "</div>";
        missAck += "</div>";
        $.cookie("wsCCReturnCallAck", missAck);
        SetReturnCallAckNotification();
    }
    function MissedCallAcknowledge()
    {
        if($("#missCallAck").length>0) return;
        missAck = "<div class='ccNotification' id='missCallAck'><div class='left'>You missed a call &nbsp;</div><div class='right'><a class='button' id='btnAck'>Acknowledge</a></div></div>";
        $.cookie("wsCCMissedCallAck", missAck);
        SetMissedCallAckNotification();
    }
    function SetMissedCallAckNotification()
    {
        var missAck = $.cookie("wsCCMissedCallAck");
        if(missAck==='null') {
            //missedCall = false;
            return;
        }
        //missedCall = true;

        $("body").append(missAck);
        $("#missCallAck").animate({
            top:'0px'
        },300);
        $("#btnAck").click(function($missCallAck){
            $.cookie("wsCCMissedCallAck",null);
            $("#missCallAck").animate({top:'-40px'},500,function(){$("#missCallAck").remove();});
            console.log(obj);
            missedCall = false;
            obj.MissAck(cookieValue.webKey);
        });
    }
    function SetReturnCallAckNotification()
    {
        var missAck = $.cookie("wsCCReturnCallAck");
        if(missAck==='null') return;

        $("body").append(missAck);
        $("#returnCallAck").animate({
            top:'0px'
        },300);
        $("#btnReturnQueue").click(function(){
            $.cookie("wsCCReturnCallAck",null);
            $("#returnCallAck").animate({top:'-130px'},500,function(){$("#returnCallAck").remove();});
        });
        $("#btnReturnAttend").click(function(){
            var attendTime = $("#returnCallAttendTime").val();
            //alert("GUI message will send");
            $("#returnCallAck").animate({top:'-130px'},500,function(){$("#returnCallAck").remove();});
        });
    }
    function IframeOpen(call_id, caller_id, url, language='EN',skill_id, direction, did='')
    {
        NewTabCreate(call_id, caller_id, url, language, skill_id, direction, did);        
        try {
            get_customer_lms_info(caller_id);
        }catch(e){
            console.error(e);
        }
    }

    function OpenTargetUrl(url, callid, cSkillId, callerid, agentId, cLang, cDid, callsAuthenticated, additionalArgs, isCrm)
    {
        var skill = "";
        if (typeof url != 'undefined' && url.length>0) {
            if (url.indexOf("&param=") >=0 ) {
                var templateArr = [];
                templateArr["callid"] = callid;
                var url_ary = url.split("&param=");
                template_id = url_ary[1];
                templateArr["template_id"] = template_id;

                if(cSkillId.length>0) {
                    var result = $.grep(skilListData, function(e){ return e.skill_id == cSkillId; });
                    var o = result[0];
                    if (typeof o != 'undefined') {
                        templateArr["skill_name"] = typeof o.skill_name != 'undefined' ? o.skill_name : "";
                    } else {
                        templateArr["skill_name"] = cSkill;
                    }
                }
                templateArr["callerid"] = callerid;
                templateArr["altid"] = agentId;
                templateArr["agent_id"] = agentId;
                templateArr["language"] = cLang;
                templateArr["did"] = cDid;
                templateArr["caller_auth_by"] = parseInt(callsAuthenticated)==1 ? "I" : "";

                additionalArgs = additionalArgs.replace(/(^;)|(;$)/g, "");// trim semicolon(;)
                var aArgsAry = new Array();
                aArgsAry = additionalArgs.split(";");

                if(aArgsAry.length>0) {
                    for(i=0; i<aArgsAry.length; i++) {
                        var arg = aArgsAry[i];
                        var arg_ary = arg.split("=");
                        var val_ary = typeof arg_ary[1]!='undefined' ? arg_ary[1].split(":") : new Array();
                        if(typeof val_ary[0]!='undefined' && typeof val_ary[1]!='undefined') {
                            var k = val_ary[0];
                            var v = val_ary[1];
                            templateArr[k] = v;
                        }
                    }
                }

                var serializedVal = serialize(templateArr);
                var encodedParam = Base64.encode(serializedVal);

                if (isCrm) {
                    url = 'crm/' + url_ary[0]+"&param="+encodedParam;
                } else {
                    url = url_ary[0]+"&param="+encodedParam;
                }
            }
            console.log(url);
            //$.cookie(cookieVars.popup, {"cid":resp_ary[6],"cli":callerid,"url":url});
            cookieValue.popup = {"cid":callid, "cli":callerid, "url":url};
            $.cookie(cookieVars.popup, JSON.stringify(cookieValue.popup));
            IframeOpen(callid, callerid, url,cLang,cSkillId);
        }
    }

    function show_verify_text(resp_call_id){
        $.ajax({
            type:"post",
            url:baseUrl+"?task=agent&act=check-verify-user",
            data:{
                cCustomerCallId: resp_call_id
            },
            dataType:"text",
            contentType: 'application/x-www-form-urlencoded',
            success:function(resp) {
                // console.log(resp);
                var resp_data = JSON.parse(resp);
                $.cookie("cChatVerifyUser", resp_data.verify_user);
                console.log(resp_data);
                console.log(resp_data.verify_user);

                if(resp_data.verify_user == 'Y'){
                    console.log(resp_data.verify_user);
                    console.log("#chatbox_"+resp_call_id);
                    console.log($("#chatbox_"+resp_call_id).find("#verify_user"));
                    $("#chatbox_"+resp_call_id).find("#verify_user").html('Verify User: <b>Yes</b>');
                }else if(resp_data.verify_user == 'N'){
                    console.log(resp_data.verify_user);
                    console.log("#chatbox_"+resp_call_id);
                    console.log($("#chatbox_"+resp_call_id).find("#verify_user"));
                    $("#chatbox_"+resp_call_id).find("#verify_user").html('Verify User: <b>No</b>');
                }
            }
        });
    }
    /*
    window.onbeforeunload = function() {
       alert("test");
    }*/
    function secondsToMinSec(seconds)
    {
        if(seconds==0) return "00:00";

        var minutes = Math.floor(seconds / 60);
        var seconds = parseInt(seconds - minutes * 60);
        minutes = minutes<10 ? "0"+minutes : minutes;
        seconds = seconds<10 ? "0"+seconds : seconds;

        return minutes+":"+seconds;
    }

    var _Chat = {
        ChatRequestAccept : function(resp) {

            //notifyMe(resp.from, atob(resp.message));

            //alert("CHAT_JOIN GUI ----------------");
            //if($("#chatAcceptRequest").length>0) return;
            //var chatRequest = "<div class='ccNotification' id='chatAcceptRequest'><div class='left'>You have a chat request &nbsp;</div><div class='right'><a class='button' id='btnChatAccept'>Accept</a></div></div>";
            //$("body").append(chatRequest);
            //$("#chatAcceptRequest").animate({ top:'0px' },300);

            //$('#chatAudio')[0].play();

            //$("#btnChatAccept").click(function($missCallAck){
            //$("#chatAcceptRequest").animate({top:'-40px'},500,function(){ $("#chatAcceptRequest").remove(); });

            // check verify user
            var resp_call_id = resp.call_id;
            show_verify_text(resp_call_id);
            cookieValue.webSiteKeyToChat = resp.web_key;
            cookieValue.callIdToChat = resp.call_id;
            cookieValue.customerNameToChat = resp.name;
            var email = resp.email;
            var contact = typeof resp.contact=='undefined' ? "" : resp.contact;
            var transfered = typeof resp.auto_answer=='undefined' ? "" : resp.auto_answer;

            //$.cookie("cChatLanguage",resp.language);
            $.cookie("cChatLanguage", 'BN');
            $.cookie("cChatSkill",resp.skill_id);
            $.cookie("cChatSubject",resp.subject);

            var location = resp.location;//+"|"+resp.language+"|"+resp.skill_id+"|"+resp.subject;
            //console.log(location);
            //var location_ary = location.split("*");

            //location = location_ary[0];
            var web_site_url = resp.url;//location_ary[1];
            var user_arival_duration = resp.duration;//location_ary[2];
            var service_id = $.trim(resp.service_id);

            console.log(service_id+"|"+location+"|"+web_site_url+"|"+user_arival_duration);
            $.cookie(cookieVars.callIdToChat,cookieValue.callIdToChat);
            $.cookie(cookieVars.customerNameToChat,cookieValue.customerNameToChat);

            obj.ChatAccept(cookieValue.webKey, cookieValue.callIdToChat);

            if ($("#chatbox_"+cookieValue.webSiteKeyToChat).length <= 0) {
                maxZIndex++;
                var idleTime = new Date();
                var lastTypingAt = idleTime;
                var textTyping = false;

                var options = {
                    email:email,
                    contact:contact,
                    transfered:transfered,
                    isIdle:true,
                    conversationClosed:false,
                    isMinimize:false,
                    web_site_url:web_site_url,
                    user_arival_duration:user_arival_duration,
                    service_id:service_id,
                    location:location,
                    lastRegTime: new Date(),
                    skill_id: resp.skill_id,
                    subject: $.trim(resp.subject),
                };
                //console.log(options);
                var len = $.map(chatBoxes, function(n, i) { return i; }).length;
                var right = 235 + len*304;

                // test = "test string";
                // console.log(test);

                createChatBox( cookieValue.callIdToChat, cookieValue.customerNameToChat, "", right, 300, 380, maxZIndex, idleTime, lastTypingAt, textTyping, false, options );

                if(transfered=="NO") {
                    setTimeout(function() {
                        var $box = $("#chatbox_"+resp.call_id);
                        var $boxBody = $box.find(".gcaw-body");
						var ag_msg = "";
						//if(curSuffix=='AA'){
						//	ag_msg = "Welcome to the #1 network of friends where you can enjoy the best 4G+ experience, data packs and tariff. To ensure you receive the best service, our customer service agents are now available 24 hours! ";
						//}else{
							ag_msg = "Welcome to Robi Live Chat Service. To ensure you receive the best service, our customer service agents are now available 24 hours! ";
						//}
						
                        var msg = {
                            from:agentId,
                            name:agentNick,
                            msg:ag_msg,
                            // msg:"আমার নাম " + agentNick,
                            from_type: "AGNT",
                            timestamp:"",
							callid: cookieValue.callIdToChat		   
                        };
                        var row = msgRow(msg);
                        $boxBody.append(row);

                    }, 1000);

                    setTimeout(function() {
                        var $box = $("#chatbox_"+resp.call_id);
                        var $boxBody = $box.find(".gcaw-body");
                        var ag_msg = "";
                        //if(curSuffix=='AA'){
                        //    ag_msg = "This is "+agentNick+" from Airtel Livechat, How may I help you? ";
                        //}else{
                            ag_msg = "How may I help you? ";
                        //}
                        
                        var msg = {
                            from:agentId,
                            name:agentNick,
                            msg:"This is "+ agentNick +" from Robi Livechat, "+ ag_msg,
                            // msg:"আমার নাম " + agentNick,
                            from_type: "AGNT",
                            timestamp:"",
							callid: cookieValue.callIdToChat
                        };
                        var row = msgRow(msg);
                        $boxBody.append(row);

                    }, 3000);

                    //setTimeout(function(){
                    //   var $box = $("#chatbox_"+resp.call_id);
                    //   var $boxBody = $box.find(".gcaw-body");
                    //   var msg = {
                    //       from:agentId,//$.cookie(cookieValue.callIdToChat),
                    //       name:agentNick,
                    //       msg:"How may I help you?",
                    //       // msg:"আমি কিভাবে আপনাকে সাহায্য করতে পারি?",
                    //       from_type: "AGNT",
                    //       timestamp:""
                    //   };
                    //   row = msgRow(msg);
                    //   $boxBody.append(row);
                    //
                    //}, 3000);
                }

            }
            //});

            //if(resp.audo_answer!='NO') {
            //$("#chatAcceptRequest").hide();
            //$("#btnChatAccept").click();
            //}
        },
        EmailChatHistory: function(callid, email, skill_id) {
            $.ajax({
                type:"POST",
                url:"<?php echo $this->url("task=agent&act=email-chat-history&cid=");?>"+callid+"&mailid="+email.replace(" ","")+"&skill_id="+skill_id,
                success:function(response) {

                }
            });
        },
        ChatText: function(callIdToChat, message) {
            try {
                obj.ChatText(callIdToChat, cookieValue.webKey, message);
            } catch(ex) {
                alert("Chat send warning: "+ex.message);
            }
        },

        ChatTransfer: function(callid, transferTo) {
            try {
                obj.ChatTransfer(callid, cookieValue.webKey, transferTo);
            } catch(ex) {
                alert("Chat transfer warning: "+ex.message);
            }
        },

        ChatTransferConfirm: function(callid, transferTo) {
            try {
                obj.ChatTransferConfirm(callid, cookieValue.webKey, transferTo);
            } catch(ex) {
                alert("Chat transfer warning: "+ex.message);
            }
        },
        ChatConferance: function(conf_user, callid) {
            try {
                obj.ChatConferance(conf_user, cookieValue.webKey, callid);
            } catch(ex) {
                alert("Chat transfer warning: "+ex.message);
            }
        },

        ChatWriting: function(callid) {
            try {
                obj.ChatWriting(cookieValue.webKey, callid);
            } catch(ex) {
                alert("Chat writing warning: "+ex.message);
            }
        },

        LoadMoreChat: function($box, response) {
            if(response==null) {

                $box.find(".btnShowRecentMsg").after("<div class='gcaw-closed-conversation'>No recent message found.</div>");
                $box.find(".btnShowRecentMsg").remove();
                //$box.find(".gcaw-body").append("<div class='gcaw-closed-conversation'>No recent message found.</div>");
                return;
            }
            //if(response.length>0) {
            $boxBody = $box.find(".gcaw-body");
            var html = "";
            for(var index in response) {
                response[index]["msg"] = b64d(response[index].msg);
                html += msgRow(response[index]);
            }
            if(html.length>0) {
                $("._m_r").remove();
                $box.find(".btnShowRecentMsg").after(html);
                $box.find(".btnShowRecentMsg").remove();
            }
            //$boxBody.html(html);
            //}
        },

        ChatClose: function(callid, is_incoming_close, disc_party) {
            try {
                _Chat.ConversationClosedNotification(callid, disc_party);
                if(typeof chatBoxes[callid]!='undefined') chatBoxes[callid].isIdle = false;
                if(typeof chatBoxes[callid]!='undefined') chatBoxes[callid].conversationClosed = true;
                $.cookie("chatBoxes", JSON.stringify(chatBoxes));
				newChatBoxStorage(chatBoxes);
                if (!is_incoming_close) obj.ChatClose(callid, cookieValue.webKey);
            } catch (ex) {
                alert("Exception: " + ex.message);
            }
        },
        ConversationClosedNotification: function(callid, disc_party) {
            $box = $("#chatbox_"+callid);
            var $boxBody = $box.find(".gcaw-body");
            var name = typeof chatBoxes[callid]!='undefined' ? chatBoxes[callid].name : "";
			if(disc_party=='S')
				$boxBody.append("<div id='gcaw-closed-conversation'>Conversation closed by System</div>");
			else
				$boxBody.append("<div id='gcaw-closed-conversation'>Conversation closed by "+name+"</div>");
            if(typeof $boxBody[0]!='undefined') $boxBody.scrollTop($boxBody[0].scrollHeight);
        },

        ChatData: function(resp) {
            try {
                var data = JSON.parse(atob(resp.data));

                var endTime = new Date();

                for(user_id in chatBoxes) {
                    var startTime = new Date(chatBoxes[user_id].lastRegTime);
                    var seconds = get_time_diff(startTime, endTime);
                    var maxTime = 2*60;
                    var onOffclass = "online";
                    if(seconds <= maxTime) onOffclass = "online";
                    else onOffclass = "offline";

                    $box = $("#chatbox_"+user_id);
                    $box.find(".gcaw-status").removeClass("online offline").addClass(onOffclass);

                    chatBoxes[user_id].lastRegTime = endTime;
                    $.cookie("chatBoxes", JSON.stringify(chatBoxes));
					newChatBoxStorage(chatBoxes);
                    //console.log(chatBoxes);
                    //console.log(user_id);
                    //console.log(cookieValue.webKey);
                    if (user_id.length>0) obj.SendChatData(user_id, cookieValue.webKey, "Ping");
                }
            } catch (ex) {
                //alert("Exception: " + ex.message);
            }
        }

    }


    $( init );
    function init() {
        _Supervisor.AgentList();
    }
    var _Supervisor = {
        AgentList: function() {
            $("#chatAgentList").click(function(){
                $agentList = $("#agentList");
                if($agentList.length==0) {
                    $(" <div />" ).attr("id","agentList").html('<div class=\"progress\">&nbsp;</div>')
                        .appendTo($( "body" ));
                } else {
                    if($agentList.css("display")!= "none") {
                        $agentList.css("display","none");
                    } else {
                        $agentList.css("display","block");
                    }
                    return;
                }

                var rows = "";
                for(i=0; i<agentListData.length; i++) {
                    var inMon = "";
                    //var found = $.inArray(agentListData[i].agent_id, agentIsInMonitor) > -1;
                    //if(found) {
                    //	inMon = "<span class='uibutton confirm'>Close</span>";
                    //}
                    rows += "<li href=\"javascript:void(0)\" class=\"open-chat-session\" aid='"+agentListData[i].agent_id+"' nick='"+agentListData[i].nick+"'><div class='info'><span>"+agentListData[i].agent_id+"</span><span> - "+agentListData[i].nick+"</span>"+inMon+"</div></li>";
                }
                $("#agentList").html("<div class='header'><span class='caption'><input type='text' id='agentSearchText' placeholder='Agent search...' ></span><span class='close'>X</span><div class='clear'></div></div><ul>"+rows+"</ul>");

                $("#agentList .header .close").click(function(){
                    $("#agentList").remove();
                });

                $("#agentSearchText").keyup(function(){
                    var searchText = $(this).val();

                    var rows = "";
                    for(i=0; i<agentListData.length; i++) {
                        var agent_id = agentListData[i].agent_id;
                        var nick = agentListData[i].nick;
                        var tempAgent = agent_id.toLowerCase();
                        var tempNick = nick.toLowerCase();
                        var searchText = searchText.toLowerCase();
                        if(tempAgent.indexOf(searchText)!== -1 || tempNick.indexOf(searchText)!== -1) {
                            console.log(searchText, agent_id , nick, agent_id.indexOf(searchText), nick.indexOf(searchText));
                            rows += "<li href=\"javascript:void(0)\" class=\"open-chat-session\" aid='"+agentListData[i].agent_id+"' nick='"+agentListData[i].nick+"'><div class='info'><span>"+agentListData[i].agent_id+"</span><span> - "+agentListData[i].nick+"</span></div></li>";
                        }
                    }
                    $("#agentList").find("ul").html(rows);
                });

                $(".open-chat-session").unbind("click").bind("click",function(){
                    var user_id = $(this).attr('aid');
                    var user_name = $(this).attr('nick');

                    // console.log(user_id);
                    // console.log(user_name);

                    _Supervisor.OpenChatSession(user_id, user_name);
                    $("#agentList").remove();
                    //agentIsInMonitor.push(user_id);
                });
            });
        },
        OpenChatSession: function(agent_id, agent_nick) {
            try {
                obj.ChatMonitor(agent_id, cookieValue.webKey);
            } catch(ex) {
                alert("Chat transfer warning: "+ex.message);
            }
        },
        chatMonitorResponse: function(resp) {
            var count = 0;
            resp = resp.session;
            for(i=0; i<resp.length; i++) {
                maxZIndex++;
                var idleTime = new Date();
                var lastTypingAt = idleTime;
                var textTyping = false;
                var options = {
                    email: resp[i].email,
                    contact: resp[i].contact,
                    transfered: false,
                    isIdle: true,
                    conversationClosed: false,
                    isMinimize: false,
                    web_site_url:'',//web_site_url,
                    user_arival_duration: 0,//user_arival_duration,
                    service_id: resp[i].service_id,
                    service_name: resp[i].subject,
                    location: '', //location,
                    lastRegTime: new Date(),
                    skill_id: resp[i].skill_id,
                    subject: $.trim(resp[i].subject)
                };
                var email = "";
                var right = 5 + count*304;

                createChatBox( resp[i].call_id, resp[i].name, "", right, 300, 380, maxZIndex, idleTime, lastTypingAt, textTyping, true, options );
                count++;
            }
        },
        ChatPickUp: function(callid) {
            try {
                //alert("chat pick up");
                //console.log("call_id: "+callid);
                //console.log("web_key: "+cookieValue.webKey);
                obj.ChatPickUp(callid, cookieValue.webKey);
            } catch(ex) {
                alert("Chat transfer warning: "+ex.message);
            }
        }/*,
	ChatMonitorClose: function(callid) {
		try {
			//alert("chat monitor close");
			//console.log("call_id: "+callid);
			//console.log("web_key: "+cookieValue.webKey);
			obj.ChatMonitorClose (callid, cookieValue.webKey);
		} catch(ex) {
			alert("Chat transfer warning: "+ex.message);
		}
	}*/

    }

    function notifyMe(title, msg) {

        if (!("Notification" in window) || window.location.protocol === "http:") {
            console.log("Notification is not supported.");
            return false;
        }

        // Let's check whether notification permissions have already been granted
        if (Notification.permission === "granted") {

            let img = 'ccd/notification.png';
            let notification = new Notification(title, { body: msg, icon: img });

            notification.onclick = function(){
                notification.close();
                window.focus();
            };
        }
        else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(function(result) {
                if (result === 'denied') {
                    console.log('Permission denied.');
                    return false;
                }
                if (result === 'default') {
                    console.log('The permission request was dismissed.');
                    return false;
                }
                // Do something with the granted permission.
                if (result === "granted") {
                    let img = 'ccd/notification.png';
                    let notification = new Notification(title, { body: msg, icon: img });
                    notification.onclick = function(){
                        notification.close();
                        window.focus();
                    };
                }
            });
        }
       return true;
    }

    function spawnNotification(theBody,theIcon,theTitle) {
        let options = {
            body: theBody,
            icon: theIcon };
        let n = new Notification(theTitle,options);
    }

    function CallNetworkDashboard(call_id, caller_id, skill_id, did=''){
        console.log("CallNetworkDashboard");
        console.log(caller_id);
	    console.log(did);
	    console.log(did.length);
        var brand_prefix = caller_id.slice(0,-8);
        console.log(brand_prefix);

        if(did.length > 8){
            if(brand_prefix.length==2 && (brand_prefix=='18' || brand_prefix=='16')){
                window.open('http://192.168.52.147:8019/?msinds=0'+caller_id, '_blank', "toolbar=no,titlebar=no, height=500,width='100%',scrollbars=yes,resizable=yes,fullscreen=yes");
                // window.open('http://192.168.52.147:8019/?msinds=0'+caller_id, '_blank');
            }else{
                $.ajax({
                    type:"post",
                    url: baseUrl+"getmnp.php",
                    data:{
                        caller_id: caller_id,
                    },
                    dataType:"json",
                    success:function(resp){
                        console.log(resp);
                        if(resp.result_type){
                            window.open('http://192.168.52.147:8019/?msinds=0'+caller_id, '_blank', "toolbar=no,titlebar=no, height=500,width='100%',scrollbars=yes,resizable=yes,fullscreen=yes");
                        }
                    },
                    error: function(xhr,status,error){
                        console.log(xhr);
                        console.log(status);
                        console.log(error);
                    }
                });
            }            
        }else{
            window.open('http://192.168.52.147:8019/?msinds=0'+caller_id, '_blank', "toolbar=no,titlebar=no, height=500,width='100%',scrollbars=yes,resizable=yes,fullscreen=yes");
            // window.open('http://192.168.52.147:8019/?msinds=0'+caller_id, '_blank');
        }
    }

    function NewTabCreate(call_id, caller_id, url, language, skill_id, direction, did='')
    {
        if(pdSkills){
            pdSkills.forEach(function(ps){
                if(ps.skill_id === skill_id){
                    let tmp = caller_id;
                    caller_id = did;
                    did = tmp;
                    tmp = '';
                }
            });
        }
        var isEnableTabClose = true;
        var maxTabOpen = 3;

        if($("#tabs li").length >= maxTabOpen) {
            var tabid = $('#tabs li:first a:first').attr('id');
            RemoveTabItem(tabid);
        }

        agentCallStack.push({caller:caller_id, callid:call_id, direction: direction, disposition_saved: false });
        prepareDispositionCaller(call_id);
        hideDispositionSetArea();

        $("#tabs li").removeClass("current");
        $("#content iframe").hide();

        if ($("#tab_"+call_id).length){
            RemoveTabItem("tab_"+call_id);
        }

        var tabHtml = "<li class='current'><a class='tab' id='tab_" +call_id+ "' data-skill-id='"+ skill_id +"' href='javascript:void(0)'>" + caller_id + " ("+ language +")</a>";
        if(isEnableTabClose) tabHtml += "<a href='javascript:void(0)' class='remove'>&times;</a>";
        tabHtml += "</li>";
        $("#tabs").append(tabHtml);

        $(".wrapper #content").append('<iframe id="iframe_'+call_id+'" src="'+url+'" frameborder="0" scrolling="yes"></iframe>');
        $("#iframe_" + call_id ).show();
        $(".iframebox .wrapper #content").addClass("bgcolor");

        // window.open('http://192.168.10.64/ccprodev/index.php?task=password','Test1',"toolbar=no,titlebar=no, height=200,  width=500, scrollbars=yes, resizable=yes,fullscreen=yes");

        CallNetworkDashboard(call_id, caller_id, skill_id, did);
    }

    $(document).ready(function() {

        $('#tabs a.tab').live('click', function() {
            var tabid = $(this).attr("id");
            FocusTab(tabid);
            if (show_sccb) {
                console.log("show SCCB");
                try {
                    let cli = $(this).data("cli");
                    get_customer_lms_info(cli);
                }catch(e){
                    console.error(e);
                }
            }
        });

        $('#tabs a.remove').live('click', function() {

            if(!confirm('Do you want to delete?')) return;

            var tabid = $(this).parent().find(".tab").attr("id");

            RemoveTabItem(tabid);
        });

        initializeDisposition();

        $("#customer_journey").hide();

    });

    function initializeDisposition(){
        $(document).on('click', '#disposition-set-button', function () {
            $("#disposition-container").toggleClass('hide');
        });

        $(document).on('click', '.create-disposition-box', function () {

            $("#disposition_id").select2();
            $("#disposition_id").select2('destroy');

            $(".single-disposition-form:first").clone(true).appendTo("#disposition-container");
            $(".caller:not(:first)").remove();
            $(".single-disposition-form:last .disposition-box:not(:first)").remove();
            $(".single-disposition-form:last #note").val('');
            $(".create-disposition-box:not(:first)")
                .addClass('remove-disposition-box btn-danger')
                .removeClass('create-disposition-box btn-primary').text('-');
            $(".set-disposition:not(:first)").hide();
        });

        $(document).on('click', '.remove-disposition-box', function () {
            $(this).closest('.single-disposition-form').remove();
            $(".set-disposition").hide();
            $(".set-disposition:last").show();
        });
    }

    function afterTabRemove(call_id){
        for (var i = agentCallStack.length - 1; i >= 0; --i) {
            if (agentCallStack[i].callid === call_id) {
                agentCallStack.splice(i,1);
                break;
            }
        }
        prepareDispositionCaller(call_id);
    }

    function RemoveTabItem(tabid)
    {
        var call_id = tabid.replace("tab_","");
        $("#iframe_" + call_id).remove();
        $("#"+tabid).parent().remove();

        /*--------------Remove data from agent call stack (array)-----------------*/
        afterTabRemove(call_id);

        if ($("#tabs li.current").length === 0 && $("#tabs li").length > 0) {
            var tabid = $("#tabs li:last a:first").attr('id');
            FocusTab(tabid);
            $(".iframebox .wrapper #content").addClass("bgcolor");
        } else {
            hideDispositionSetArea();
            $(".iframebox .wrapper #content").removeClass("bgcolor");
        }
    }

    function FocusTab(tabid)
    {
        var call_id = tabid.replace("tab_","");

        $(".wrapper #content iframe").hide();
        $("#tabs li").removeClass("current");

        $("#iframe_" + call_id).show();
        $("#"+tabid).parent().addClass("current");

        var skillIdOfTab = $("#tab_" + call_id).data("skill-id");
        if (skillIdOfTab != 'undefined'){
            getDispositionBySkillId(skillIdOfTab);
        }

        prepareDispositionCaller(call_id);
    }

    function DispositionSaved(call_id)
    {
        console.log("CALL ID: "+call_id);
        //var isEnableTabClose = true;
        //if(!isEnableTabClose)
        //{
        //var result = $.grep(cookieValue.callsStack, function(e){ return e.callId.indexOf(call_id) >= 0; });
        //var call_id = "";
        //if(typeof result[0].callid == 'undefined') return;
        //call_id = result[0].callid;
        var tabid = "tab_"+call_id;
        RemoveTabItem(tabid);
        //}
    }

    function debug(msg)
    {
        console.log(msg);
    }

    function showDispositionSetArea(){
        $element = $("#disposition-container");
        if($element.hasClass('hide')){
            $element.removeClass('hide');
        }
    }

    function hideDispositionSetArea(){
        $element = $("#disposition-container");
        if(! $element.hasClass('hide')){
            $element.addClass('hide');
        }
    }

    $(document).on('focusout', function(){
        setTimeout(function(){
            // using the 'setTimout' to let the event pass the run loop
            if (document.activeElement instanceof HTMLIFrameElement) {
                //console.log("FOCUSOUT");
                $(document).click();
            }
        },0);
    });


    function prepareDisposition(dispositions) {
        $("#disposition_id").empty();
        $("#disposition_id").append("<option value=''>---Select---</option>");
        $.each(dispositions, function (index, disposition) {
            $("#disposition_id").append("<option value='"+disposition.disposition_id+"'>"+ disposition.title +"</option>");
        });
    }

    function prepareDispositionCaller(selected = '') {
        $("#callid").empty();
        var reverseAgentCallStack = agentCallStack.reverse();
        $.each(reverseAgentCallStack, function (index, call) {
            var isSelectedItem = selected == call.callid ? 'selected' : '';
            $(".callid").append("<option value='"+ call.callid+"'  "+ isSelectedItem +"  >"+ call.caller +"</option>");
        })
    }

    function addDispositionRow(dispositions,index){

        var row = '<div class="form-group form-group-sm disposition disposition-box ">'+
            '<label for="disposition_id" class="control-label">--></label>'+
            '<select name="disposition_id[]"  class="disposition-class form-control">'+
            '<option value="">---Select---</option>';

        jQuery.each(dispositions, function (index, disposition) {
            row += "<option value='"+ disposition.disposition_id +"'> "+ disposition.title + "</option>";
        });

        row += "</select> </div> ";

        $(".disposition").eq(index).after(row);

    }


    function jsonSearch(obj, key, val) {
        var objects = [];
        if (val == ''){
            return objects;
        }
        for (var i in obj) {
            if (!obj.hasOwnProperty(i)) continue;
            if (typeof obj[i] == 'object') {
                objects = objects.concat(jsonSearch(obj[i], key, val));
            } else
            //if key matches and value matches or if key matches and value is not passed
            // (eliminating the case where key matches but passed value does not)
            if (i == key && obj[i] == val || i == key && val == '') { //
                objects.push(obj);
            } else if (obj[i] == val && key == ''){
                //only add if the object is not already in the array
                if (objects.lastIndexOf(obj) == -1){
                    objects.push(obj);
                }
            }
        }
        return objects;
    }

    function getSelelctedDisposition(){

        var dispositions = [];

        $( "#disposition-container .form" ).each(function( index ) {
            var thisForm = this;
            var total_disposition_dropdown = $(".disposition-class", thisForm).length;
            var disposition = $(".disposition-class", thisForm).last().val();
            if (disposition === 'undefined' || disposition === null || disposition.length <= 0){
                if (total_disposition_dropdown >= 1){
                    disposition = $(".disposition-class", thisForm).eq(total_disposition_dropdown - 2).val();
                }
            }
            if (disposition !== 'undefined' && disposition !== null && disposition.length > 0){
                dispositions.push(disposition);
            }
        });

        return dispositions;
    }

    function getDispositionNotes()
    {
        var notes = [];

        $( "#disposition-container .form" ).each(function( index ) {
            var thisForm = this;
            var note = $("#note", thisForm).val();
            notes.push(note);
        });

        return notes;
    }

    function saveDisposition() {

        var dispositions = getSelelctedDisposition();

        var raw_callid = $("#disposition-container  #callid").val();
        var cli = $("#disposition-container #callid option:selected").text();
        var notes = getDispositionNotes();
        var callStack = _.keyBy(agentCallStack, 'callid');
        var call_info = callStack[raw_callid];		
		
        try{
            if(call_info.disposition_saved){
                showFlashNotification("Disposition already saved for this call. Can't save anymore.", 'error');
                return;
            }
        }catch(e) {
            console.log(e);
        }


        if (typeof raw_callid === 'undefined' || raw_callid === '' || raw_callid === null || raw_callid.length === 0) {
            alert("Select Caller");
            return;
        }

        if (typeof dispositions === 'undefined' || dispositions.length === 0 || dispositions[0] === '') {
            alert('Select disposition !!');
            return;
        }

        var callid = raw_callid.split("-"); // 123456789456123-12
        callid = callid[0];         // 123456789456123


        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: "<?php echo $this->url('task=agent&act=save-disposition') ?>",
            data: {
                dispositions: dispositions,
                callid: callid,
                cli : cli,
                notes : notes,
                direction: call_info.direction || 'IN'
            }
        }).done(function( msg ) {
            Messenger.options = {
                extraClasses: 'messenger-fixed messenger-on-top messenger-on-right',
                theme: 'future'
            };

            showFlashNotification(msg.message, msg.status == true ? 'success' : 'error');
            if (msg.status){
                agentCallStack.forEach(function (acs) {
                    if(acs.callid === raw_callid){
                        acs.disposition_saved = true;
                    }
                });

                $(".single-disposition-form:not(:first)").remove();
                $("#set-disposition", ".single-disposition-form:first").removeAttr('style');
                $(".disposition:not(:first)").remove();
                $("#disposition_id, #note").val('');
                $('#disposition_id').trigger('change');
            }
        });
    }

    function OpenPdPreviewPrompt(){
        Messenger.options = {
            extraClasses: 'messenger-fixed messenger-on-top',
            theme: 'future'
        };

        pd_preview_message = Messenger().post({
            message: "Would you like to attend the call?",
            id: "singleton",
            hideAfter:72000,
            actions: {
                confirm: {
                    label: "Yes, Continue",
                    action: function(){
                        console.log("Ok, Continue");
                        try {
                            pdPreviewDialStatus = 0;
                            obj.PD_Number_Dial(true,pdPreviewSkillId, cookieValue.webKey);
                        }
                        catch(e){
                            console.error(e);
                        }

                        Messenger.options = {
                            extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right',
                            theme: 'future'
                        };

                        Messenger().post({
                            message: 'Connecting...',
                            type: 'success',
                            showCloseButton: true
                        });

                        pd_preview_message.hide();

                    }
                },

                cancel: {
                    label: "Skip",
                    action: function(){
                        console.log("No, Skip");
                        try {
                            pdPreviewDialStatus = 0;
                            obj.PD_Number_Dial(false, pdPreviewSkillId, cookieValue.webKey);
                        }
                        catch(e){
                            console.error(e);
                        }
                        Messenger.options = {
                            extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right',
                            theme: 'future'
                        };
                        Messenger().post({
                            message: 'Call has been skipped.',
                            type: 'error',
                            showCloseButton: true
                        });
                        pd_preview_message.hide();
                    }
                }
            }
        })
    }

    function getDispositionBySkillId(skill_id){
        $.ajax({
            type: "POST",
            url: "<?php echo $this->url('task=agent&act=get-skill-wise-disposition&skill_id='); ?>" + skill_id,
            dataType: "json"
        }).done(function (data) {
            if (Object.keys(data).length > 0){
                dispositions = data;
                prepareDisposition(data);
            }
        });
    }



    $(function () {
        // OpenPdPreviewPrompt();
        //copyToClipBoard();

        $(document).on("change",".disposition-class", function (e) {
            console.log(dispositions);
            var selected = $(this).val();
            if (selected === ''){
                $(this).closest( ".disposition" ).nextAll('.disposition').remove();
                return;
            }
            console.log(selected);
            var index = $( ".disposition-class" ).index( this );
            console.log(index);

            $(e.target ).closest( ".disposition" ).nextAll('.disposition').remove();

            var disposition = jsonSearch(dispositions,'disposition_id',selected);

            var children =  disposition[0].children;
            console.log(children);

            if (children !== "undefined" && Object.keys(children).length > 0){
                addDispositionRow(children,index);

            }
            if(selected === 'undefined' || selected.length <= 0 || selected === ''){
                $(e.target ).closest( ".disposition" ).nextAll('.disposition').remove();
            }

        });

        $(document).on('click',"#set-disposition",function (e) {
            e.stopPropagation();
            saveDisposition();
        });

        $(document).on('mouseenter','.disposition-class',function (e) {
            $(this).select2();
        });
    });

    function copySkill() {
        selectAndCopy('#skill');
    }
    function copyCLI(){
        selectAndCopy('#cli');
    }

    function copyClientName(){
        selectAndCopy('#cname');
    }

    function copyPDNumber(){
        selectAndCopy('#pdStatus');
    }

    function selectAndCopy(element){
        var selectedNode = document.querySelector(element);
        var range = document.createRange();

        range.selectNode(selectedNode);
        window.getSelection();

        copyToClipboard();
    }

    function copyToClipboard(){
        try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'Data copied to clipboard' : 'Failed to copy data to clipboard';
            var msg_type = successful ? 'success' : 'error';
            showFlashNotification(msg,msg_type);
        } catch(err) {
            showFlashNotification('Oops, unable to copy','error');
        }
        window.getSelection().removeAllRanges();
    }

    function showFlashNotification(message, type='success'){
        Messenger().post({
            message: message,
            type: type,
            showCloseButton: true
        });
    }
	/*function LoadCustomerJourney(callerId) {
        $("#customer_journey").attr("href", baseUrl+"?task=customer-journey&cli="+callerId);
        $("#customer_journey").show();
    }*/
    function LoadCustomerJourney(callid, callerId, skillid) {
        $("#customer_journey").attr("href", baseUrl+"?task=customer-journey&callid="+callid+"&cli="+callerId+"&skill="+skillid);
        $("#customer_journey").show();
    }
	
	function checkChangeAUXTime(auxId, webKey){
		console.log("checkChangeAUXTime");
		console.log(auxId);
		console.log($.cookie("ccAuxModeDateTime"));
		let auxTime = $.cookie("ccAuxModeDateTime");
		
		if(auxTime && auxTime == new Date()){
			return false;
		}
		$.cookie("ccAuxModeDateTime", new Date());
		return true;
	}
</script>

<!--========================= secondary call control bar ============================ -->
<?php if ($show_sccb): ?>
    <script>
        user_lms_info_url = "<?php echo $this->url('task=agent&act=get-customer-lms-info'); ?>";
        user_last_month_disposition_url = "<?php echo $this->url('task=agent&act=get-customer-last-month-disposition'); ?>";
    </script>
    <link rel="stylesheet" href="ccd/sccb/sccb.css">
    <script src="ccd/sccb/sccb.js"></script>
<?php endif; ?>

<?php /* ?>  ========================= SMS chat service ============================ <?php */ ?>

<script>
    user_sms_session_url = "<?php echo $this->url('task=sms&act=get-user-sms-by-session'); ?>";
    skill_wise_disposition_url = "<?php echo $this->url('task=agent&act=get-skill-wise-disposition&skill_id='); ?>";
    sms_disposition_store_url = "<?php echo $this->url('task=sms&act=close-sms-session'); ?>";
    sms_send_url = "<?php echo $this->url('task=sms&act=send-sms-to-user'); ?>";
    sms_ping_url = "<?php echo $this->url('task=sms&act=send-sms-ping'); ?>";
    predefined_sms_template_url = "<?php echo $this->url('task=sms&act=get-sms-templates'); ?>";
	sms_log_text_url = "<?php echo $this->url('task=sms&act=log-sms-from-js'); ?>";
	chat_co_browse_log_url = "<?php echo $this->url('task=agent&act=log-chat-co-browse'); ?>";
</script>
<link rel='stylesheet' href='assets/fonts/font-awesome/css/font-awesome.css' type='text/css'>
<link rel='stylesheet' href='ccd/sms-chat/sms.css' type='text/css'>
<script src="ccd/sms-chat/sms.js"></script>
<script src="ccd/sms-chat/moment.js"></script>

<?php /* ?> ========================= SMS chat service End ============================ <?php */ ?>
