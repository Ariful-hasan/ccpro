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
  <link rel='stylesheet' href='ccd/calllog.css' type='text/css'>
  <!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>
<body>

    <div class="ccd" style='display:none;'>
      <div class='ccd-body disabled'>

		<ul class='item'>
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
								<select id='skillList' onchange="ChangeSkill()">
									<!--option>Skill-A</option>
									<option>Skill-B</option-->
								</select>
							</div>
						</div>
						<div class='dial-button-view'>
							<input type='button' value='Transfer' id="btnAgentTransfer" class='dial-button uibutton special'>
							<input type='button' value='Hang Up' id="btnAgentTransferCancel" class='dial-button uibutton'>
						</div>
					</div>
				</div>
			</li>
			<!--li class='item-li agent-transfer-item'>
				<a class='agent-transfer primary button bottomTips' title="Transfer" id="btnAgentTransfer" title="Transfer"></a>
			</li>
			<li class='item-li agent-transfer-item'>
				<a class='agent-transfer primary button bottomTips' title="Transfer Cancel" id="btnAgentTransferCancel" title="Transfer Cancel"></a>
			</li-->
			<li class='item-li' id="holdSection"><a class='hold primary button bottomTips' title="Hold" id="btnHold" title="Hold"></a></li>
			<li class='item-li' id="unHoldSection">
				<div class='unhold-control'>
					<a class='unhold primary button bottomTips' title="Unhold" id="btnUnhold" title="Unhold"></a>
					<div class='unhold-view child-view'>
						
						<table id='holdedCallList'>
							<!--tr>
								<td>cli cname</td><td>HT: 01:12</td><td><input type='button' value='Unhold'></td>
							</tr>
							<tr>
								<td>cli cname</td><td>HT: 00:52</td><td><input type='button' value='Unhold'></td>
							</tr>
							<tr>
								<td>cli cname</td><td>HT: 00:09</td><td><input type='button' value='Unhold'></td>
							</tr-->
						</table>
						
					</div>
				</div>
			</li>	
			<li class='item-li' id="transferSection">
				<div class='transfer-control'>
					<a class='transfer primary button bottomTips' title="Transfer" id="btnTransfer" title="Transfer"></a>
					<div class='transfer-view child-view'>
						<div><b>Do Transfer</b> <div class='close' style='float:right;'>X</div></div>
						<fieldset id="callTransferWindow">
							<legend>Destination</legend>
								<table>
									<!--tr>
										<td align='right'><b>Agent ID</b></td><td>:</td>
										<td><input type='text' id="trns_agent" class='text-field' /></td>
									</tr-->
									<tr>
										<td align='right'><b>Skill</b></td><td>:</td>
										<td>
											<select id="optSkill"></select>
										</td>
									</tr>
									<tr>
										<td align='right'><b>IVR</b></td><td>:</td>
										<td>
											<select id='ivrList'></select>
										</td>
									</tr>
									<!--tr>
										<td nowrap align='right'><b>External Number</b></td><td>:</td>
										<td><input type='text' id="trns_ext_number"" class='text-field' /></td>
									</tr-->
									<tr>
										<td colspan='2'></td>
										<td><input type='button' value='Transfer' class='dial-button uibutton special' onclick="Transfer()"></td>
									</tr>
								</table>
						</fieldset>
						<fieldset id="callTransferCancelWindow">
							<legend>Transfer Cancelation</legend>
								<table>
									<tr>
										<td><input type='button' value='Transfer Cancel' class='dial-button uibutton' onclick="TransferCancel()"></td>
									</tr>
								</table>
						</fieldset>
					</div>
				</div>
			</li>
			<li id="callDuration" class="bottomTips item-li" title="Call duration"></li>
			<li id="cname" class='bottomTips item-li' title='CName'></li>
			<li id="cli" class='bottomTips item-li' title='CLI'></li>
			<li id="skill" class='bottomTips item-li' title='Skill (DID)'></li>
			<li id="holdTime" class='bottomTips item-li' title='Hold in Queue'></li>
			<li id="lang" class='bottomTips item-li' title='Language'></li>
		</ul>


		<div style='float:right; width:500px;'>
			<ul class='item mini-dashboard'>
				<!--li class='bottomTips item-li' title='<b>Call in queue</b>'>01</li>
				<li class='bottomTips item-li' title='<b>Available agents</b>'>03</li-->
				<li class='item-li'>
					<span id="callsInQ" class='bottomTips' title='Call in queue'>0</span> : 
					<span id="callsInService" class='bottomTips' title='Call in Service'>0</span>
				</li>
			</ul>
			<ul class='item' style="float:right;">
				
				<li class='selfId bottomTips item-li' title='Agent ID'><?php echo UserAuth::getCurrentUser(); ?></li>
				<li id='nick' class='bottomTips item-li' title='Agent'><?php echo UserAuth::getUserNick(); ?></li>
				<li class='seat bottomTips item-li' id='seatId' title='Seat ID'></li>
				
				<li class='item-li'><!--div class='aux busy loadAux' id="btnAux">Busy</div-->
					<div class='aux busy loadAux' id="btnAux">Busy</div>
				</li>
				<!--<li><div class='skill primary loadSkill' id="btnSkill" title="Busy">Skill: </div></li>-->
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
					<!--li><div href="#" class="loadHelp"><img src="image/i-3.png" alt="Icon">Help</div></li-->
					<li><div href="#" id="clearPopupScreen"><!--img src="image/i-3.png" alt="Icon" align='texttop'-->Clear POP-UP Screen</div></li>
					<li><div href="#" id="resetCallSession"><!--img src="image/i-3.png" alt="Icon" align='texttop'-->Reset Call-Sessions</div></li>
					<li><div href="#"><!--img src="image/i-3.png" alt="Icon" align='texttop'-->About</div></li>
					<!--li><div href="#" id="loadAgent"><img src="image/i-4.png" alt="Icon" align='texttop'>Available Agent</div></li>
					<li><div href="#" id="loadIVR"><img src="image/i-4.png" alt="Icon" align='texttop'>IVR</div></li>
					<li><div href="#" id="loadASI"><img src="image/i-4.png" alt="Icon" align='texttop'>AUX + SKILL + IVR (ASI)</div></li-->
					<li><div href="#" onclick="Logout()"><!--img src="image/i-6.png" alt="Icon" align='texttop'-->Sign out</div></li>
				</ul>	
			</div>			
		</div>
		<div class='clear'></div>
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
	<iframe id="purl" class="iframe-url" src="about:blank" style="display:none;"></iframe>
<!--a style="position: fixed; bottom:10px; left:10px;" href="<?php //echo $this->url("task=agents");?>" > Home</a-->
<audio id="chatAudio">
	<source src="ccd/notify.wav" type="audio/wav">
</audio>
<?php

$token = UserAuth::getOCXToken();

if(empty($token)) {
	include('model/MAgent.php'); 
	//AddModel("MAgent");
	$agent_model = new MAgent();
	$token=$agent_model->GenerateToken($user);
}
?>

</body>
</html>

<script type="text/javascript">
var dispositionUrl = "<?php echo $this->url('task=email&act=chat_disposition_mail'); ?>";
var chatTemplateUrl = "<?php echo $this->url('task=email&act=ChatTemplate'); ?>";
</script>

<script  src="ccd/jquery.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="ccd/tipsy/tipsy.css">
<script  src="ccd/tipsy/jquery.tipsy.js" type="text/javascript"></script>
<script src="ccd/gPlexCCWS.js?v=1.0.7" type="text/javascript"></script>
<script src="ccd/shortcut.js" type="text/javascript"></script>
<!--script src="ccd/jquery.cookie.min-1.4.1.js" type="text/javascript"></script-->

<!--link rel="stylesheet" href="ccd/chosen/chosen.css">
<script src="ccd/chosen/chosen.jquery.js" type="text/javascript"></script-->

<script type="text/javascript" src="ccd/autosize.js"></script>
<script type="text/javascript" src="ccd/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="ccd/jquery-ui.css"/>
<link rel="stylesheet" href="ccd/message.css?<?php echo time(); ?>">
<script  src="ccd/message.js?v=1.0.7" type="text/javascript"></script>

<script  src="ccd/select2/select2.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="ccd/select2/select2.min.css">

<link rel="stylesheet" href="ccd/button.css">
<script type="text/javascript" src="ccd/js-serialize.js"></script>

<link href="js/lightbox/colorbox.css" rel="stylesheet" media="screen">
<script src="js/lightbox/jquery.colorbox-min.js" type="text/javascript"></script>

<script type="text/javascript">

var isInCall = false;
var isOutBoundCall = false;
var inCallInfo = null;
var currentSCeq = 100;
var requestTimeoutInterval = 3; //seconds;
var startedSessionThread = false;
var SessionData = new Array();
var obj;
//var selectedAuxId = $.cookie("wsCCSelectedAuxId");
//var selectedSkillId = $.cookie("wsCCSelectedSkillId");
var tempAuxId = "";
var seat_id;
var thisPcMac = "";
var agentNick = "<?php echo UserAuth::getUserNick(); ?>";
var agentId = "<?php echo UserAuth::getCurrentUser();?>";
var userType = "<?php echo UserAuth::getRoleID();?>";
var wsLoggedUrl = "<?php echo $this->url("task=agent&act=add-web-sock-logged-in");?>";
var wsSignInUrl = "<?php echo $this->url("task=logout");?>";
var skilListData = [];
//var agentListData = [];
var agentListData = <?php echo $AgentList;?>;
//$.cookie("wsCCListAgent",JSON.stringify(agentListData));
var ivrListData = [];
var auxListData = [];
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
//var holdedCallId = "";
var unHoldedCallId = "";
//var callsStack = !$.cookie("wsCCCallsStack") ? [] : JSON.parse($.cookie("wsCCCallsStack"));
//console.log("V:"+$.cookie("wsCCCallsStack"));
var transferAgentId = "";
var transferCallId = "";
var missedCall = false;
var tempSkillId = "";
var regResponseSuccess = true;
var lastRegMsgSendAt = new Date();
var isHoldedCall = false;
var webSiteKey = "";
var natPingInterval = null;
var baseUrl = "<?php echo "http://" . $_SERVER['SERVER_NAME']; ?>"+"/cloudcc";
//var agentIsInMonitor = [];
$.cookie("ws_port","<?php echo $account_info->ws_port;?>");
var natPingResponseCount = 0;
var socketAlreadyOpen = false;

var datetime;
var cookieVars = {
	callsStack:"wsCCCallsStack",
	selectedSkillId:"wsCCSelectedSkillId",
	selectedAuxId:"wsCCSelectedAuxId",
	webKey:"wsWebKey",
	webSiteKeyToChat:"wsWebSiteKeyToChat",
	callIdToChat:"wsCallIdToChat",
	customerNameToChat:"wsCustomerNameToChat"
}
var cookieValue = {
	callsStack : !$.cookie(cookieVars.callsStack) ? [] : JSON.parse($.cookie(cookieVars.callsStack)),
	selectedSkillId : !$.cookie(cookieVars.selectedSkillId) ? "" : $.cookie(cookieVars.selectedSkillId),
	selectedAuxId : !$.cookie(cookieVars.selectedAuxId) ? "" : $.cookie(cookieVars.selectedAuxId),
	webKey: !$.cookie(cookieVars.webKey) ? "" : $.cookie(cookieVars.webKey),
	webSiteKeyToChat: !$.cookie(cookieVars.webSiteKeyToChat) ? "" : $.cookie(cookieVars.webSiteKeyToChat),
	callIdToChat: !$.cookie(cookieVars.callIdToChat) ? "" : $.cookie(cookieVars.callIdToChat),
	customerNameToChat: !$.cookie(cookieVars.customerNameToChat) ? "" : $.cookie(cookieVars.customerNameToChat),
}

//console.log($.cookie(cookieVars.selectedAuxId));

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
var $callTransferWindow = $("#callTransferWindow");
var $callTransferCancelWindow = $("#callTransferCancelWindow");


$(function() {
	$skillList.select2();
	$optSkill.select2();
	$ivrList.select2();
	SetMissedCallAckNotification();
	SetReturnCallAckNotification();
	$("#btnAuthenticated").hide();
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
		obj.loadSettings('ws<?php if(UserAuth::getDBSuffix()=="AA") { ?>s<?php }?>://<?php echo $account_info->ws_ip;?>:<?php echo $account_info->ws_port;?>/chat', '<?php echo UserAuth::getCurrentUser();?>', '<?php echo $token;?>');
		obj.setNumLoggedIn(<?php echo UserAuth::numWSLoggedIn();?>);
		obj.Connect();
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

	$("#btnCall").click(function(e) {
		
		if(!isLoggedIn || missedCall) return;
		
		if(typeof skilListData !== 'undefined' && skilListData.length==0) { 
			RecordCurrentSCeq();
			SendGUI("LIST","SKILL");
		} else {
			ShowSkillView(skilListData);
		}
			
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
	
	$("#btnAgentTransferCancel").click(function(){
		//$(".agent-transfer-item").hide();
		$(".dial-view").show();
		$(".dial-button-view").hide();
		TransferCancel();
	});
	
	$(document).on("click",".loadAux",function(){

		if(!isLoggedIn) return;
		
		missedCall = false;
		
		//var said = cookieValue.selectedAuxId;
				
		//if(parseInt(cookieValue.selectedAuxId) != 0 ||  cookieValue.selectedAuxId == "") {
		if($(this).hasClass("busy")) {
			console.log("Aux zero");
			tempAuxId = "0";
			obj.ChangeAUX(tempAuxId, cookieValue.webKey, function(err, data) {});
		} else {
			/*
			console.log("Aux non-zero");
			if(auxListData.length==0) {
				$("#btnAux").removeClass("primary").addClass("active");
				RecordCurrentSCeq();
				SendGUI("LIST","AUX");
			} else {
			*/
				ShowAuxView(auxListData);
			//}
		}
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
	
		if(!isLoggedIn ||  cookieValue.callsStack.length==0 || missedCall) return;
		
		$("#btnTransfer").removeClass("primary").addClass("active");
		$(".ccd .transfer-view").show();
		
		$(".ccd .dial-control .fields").hide();
		$("#btnCall").removeClass("active").addClass("primary");
		
		if(ivrListData.length>0 && skilListData.length>0) {
			
			var listRow = "<option value=''>&nbsp;</option>";
			for(i=0; i<skilListData.length; i++) {
				listRow += "<option value='"+skilListData[i].skill_id+"'>"+skilListData[i].skill_name+"</option>";
			}
	
			$optSkill.find('option')
			.remove()
			.end()
			.append(listRow);
			//.val('whatever');
			$optSkill.trigger("change");
	
			
			var listRow = "<option value=''>&nbsp;</option>";
			for(i=0; i<ivrListData.length; i++) {
				listRow += "<option value='"+ivrListData[i].ivr_id+"'>"+ivrListData[i].ivr_name+"</option>";
			}
	
			$ivrList.find('option')
			.remove()
			.end()
			.append(listRow);
			//.val('whatever');
			$ivrList.trigger("change");
		}		
			
		e.stopPropagation();
	});
	
	$("#clearPopupScreen").click(function(){
		$("#purl").attr('src', "").hide();
	});
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
		var url = "<?php echo $this->url("task=agent&act=assignseat");?>";
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
	
	$("#trns_agent").keypress(function(){
		$("#optSkill").val("");
		$("#ivrList").val("");
		$("#trns_ext_number").val("");
	});
	$("#trns_ext_number").keypress(function(){
		$("#trns_agent").val("");
		$("#optSkill").val("");
		$("#ivrList").val("");
	});
	$("#optSkill").change(function(){
		$("#trns_agent").val("");
		$("#ivrList").val("");
		$("#trns_ext_number").val("");
	});
	$("#ivrList").change(function(){
		$("#trns_agent").val("");
		$("#optSkill").val("");
		$("#trns_ext_number").val("");
	});
	
	$("#dialNo").keypress(function(e){
		var k = e.keyCode==0 ? e.which : e.keyCode;
		if(k==13) {
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
		$.colorbox({iframe:true, href:"<?php echo $this->url("task=agent&act=sendtext");?>", width:500, height:335});
	});
	
	//---------call log------------
	$("#callLog").click(function(){
		if($("#callLogWindow").length==0) {
			//$("#callLogWindow").show();
			var html = "<div class='call-log-window' id='callLogWindow'>\
							<div class='tab-call-log'>\
								<div class='tabCL tabFirst active' rel='inbound'>Incoming Call</div>\
								<div class='tabCL' rel='outbound'>Outgoing Call</div>\
								<div class='btn-close' style='float:right; border: 1px solid #999999; background-color:#ffffff;'>&times;</div>\
								<div class='clear'></div>\
							</div>\
							<div class='call-log-container'>\
								<div class='row-head'><div class='cell'>Number</div><div class='cell'>Time</div><div class='cell'>Duration</div><div class='clear'></div></div>\
								<div id='inboundCallLog' class='log-content inbound'>\
									\
								</div>\
								<div id='outboundCallLog' class='log-content outbound'>\
									\
								</div>\
							</div>\
						</div>";
			$('body').append(html);
		}
		
		
		
		$(".tabCL, .tabFirst").click(function(){
			$(".tabCL").removeClass('active');
			$(this).addClass('active');
			var rel = $(this).attr("rel");
			var it = this;
			//var url = "http://64.5.49.34/cloudcc/index.php?task=get-agent-data&act=agentskillcdr&type="+rel;
			var url = "http://ccportal.gplex.com/index.php?task=get-agent-data&act=agentskillcdr&type="+rel;
			
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
});

//---------call log------------
function populateCalllog(inCalls, it) {
	$("#inboundCallLog, #outboundCallLog").hide();
	var rel = $(it).attr("rel");
	var $container = $("#"+rel+'CallLog');
	$container.show();
	
	var html = "";
	for(i=0; i<inCalls.length; i++) {
		var o = inCalls[i];
		var call_to = rel=='inbound' ? o.cli : o.callto;
		html += "<div class='row'><div class='cell'>"+call_to+"</div><div class='cell'>"+o.start_time+"</div><div class='cell'>"+o.service_time+"</div><div class='clear'></div></div>";
	}
	$container.html(html);
	
	$(".call-log-window .call-log-container .row").unbind("click").bind("click",function(e){
		$(".call-log-window .call-log-container .row").removeClass("active");
		var phone_no = $(this).find(".cell:first").text();
		//console.log(phone_no);
		$(this).addClass("active");
		$("#btnCall").click();
		$("#dialNo").val(phone_no);
	});
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



function LoadCall(cli, cname, callid, skill, lang, did, holdTime, callsInQ, callsInService, callsAuthenticated)
{
	$callTransferWindow.show();
	$callTransferCancelWindow.hide();
	
	$callDuration.html("00:00");
	$cli.html(cli);
	$cname.html(cname);
	if(skill.length>0) {
		var result = $.grep(skilListData, function(e){ return e.skill_id == skill; });
		var o = result[0];
		skill = o.skill_name;
	}
	if(did.length>0) skill += "("+did+")";
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
		"CName":cname,
		"lang":lang,
		"holdTime":holdTime,
		"callTime":curTime,
		"callReceived":false,
		"callHold":false,
		"callsAuthenticated":callsAuthenticated
	});
	
	$.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));
	
	//timerFunction();
}

function CancelCall(callid) 
{
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
	currentCallId = callid;
	datetime = new Date();
	
	var result = $.grep(cookieValue.callsStack, function(e){ return e.callId == callid; });
	if(result.length>0) {
		var o = result[0];
		var index = cookieValue.callsStack.indexOf(o);
		o.callReceived = true;
		cookieValue.callsStack[index] = o;
		
		if(o.callsAuthenticated==0) ShowAuthenticatedButton();
		
		$.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));
	}
	//if (cookieValue.callsStack.length>0) {
	//	$(".dial-view").hide();
	//	$(".dial-button-view").show();
	//} else {
		$holdSection.show();
		console.log("isOutBoundCall(2): " + isOutBoundCall);
		if (!isOutBoundCall) $transferSection.show();
		else $transferSection.hide();
		$(".ccd .fields").hide();
		$("#btnCall").removeClass("active").addClass("primary");
		$("#dialNo").val("");
		//$("#skillList").val("");
	//}
	
	timerFunction();
	
	/*
	result = $.grep(cookieValue.callsStack, function(e){ return e.callHold == true; });
	if(result.length==0) {
		$("#unHoldSection").hide();
	}
	$(".ccd .unhold-view").hide();
	$("#btnHold").removeClass("active").addClass("primary");
	$("#btnUnhold").removeClass("active").addClass("primary");
	*/
}

function HangupCall(callid)
{
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
	$unHoldSection.show();
	
	//$transferSection.show();
	
	//timerFunction();
}

function UnHoldCall(callid)
{
	//datetime = new Date();
	callid = unHoldedCallId;
	unHoldedCallId = "";
	
	var result = $.grep(cookieValue.callsStack, function(e){ return e.callId == callid; });
	var o = result[0];
	var index = cookieValue.callsStack.indexOf(o);
	o.callHold = false;
	cookieValue.callsStack[index] = o;
	$.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));
	
	$holdSection.show();
	
	result = $.grep(cookieValue.callsStack, function(e){ return e.callHold == true; });
	if(result.length==0) {
		$unHoldSection.hide();
	}
	
	//$transferSection.show();
	
	//timerFunction();
}

function RemoveCall(callid)
{
	RemoveCallFromCallsStack(callid);
	clearTimeout(duration_timeout);
	//console.log(cookieValue.callsStack);
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
		} else {
			alert("Object is not created!");
		}
	} catch (ex) {
		alert("Warning3: " + ex.message);
	}
}

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
				//timerFunction();
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
				
				timerFunction();
				
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
			//var agid = $("#trns_agent").val();
			var sqid = $("#optSkill").val();
			var ivid = $("#ivrList").val();
			//var exnum = $("#trns_ext_number").val();
			//if (agid.length > 0) {
			//	transfer_to = "AG"+agid;
			//} else 
			if(sqid.length > 0) {
				transfer_to = "SQ"+sqid;
			} else if(ivid.length > 0) {
				transfer_to = "IV"+ivid;
			}
			//else if(exnum.length > 0) {
			//	transfer_to = "EX"+exnum;
			//}
                   
			if (transfer_to.length > 0) {
				obj.Transfer( currentCallId, transfer_to, cookieValue.webKey);
				$callTransferWindow.hide();
				$callTransferCancelWindow.show();
				//isOutBoundCall = false;
			}
			//obj.Transfer( callid,"dst_callid");
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
		$holdSection.show();
		$transferSection.show();
		
		var result = $.grep(cookieValue.callsStack, function(e){ return e.callHold == true; });
		if(result.length>0) {
			$unHoldSection.show();
		}
		
		var result = $.grep(cookieValue.callsStack, function(e){ return e.callHold == false; });
		if(result.length>0) {
			var o = result[0];
			$cname.html(o.CName);
			$cli.html(o.CLI);
			//$lang.html(o.lang);
			datetime = new Date(o.callTime);
			timerFunction();
		}
	}
}

function SyncCallStack()
{
	if (cookieValue.callsStack.length > 0) {
		for(i=0; i<cookieValue.callsStack.length; i++) {
			var o = cookieValue.callsStack[i];
			obj.CallStatus(o.callId, cookieValue.webKey);
		}
	}
}

function RemoveCallFromCallsStack(callid)
{
	clearTimeout(duration_timeout);
	//console.log("RemoveCallFromCallsStack: " + callid);
	var result = $.grep(cookieValue.callsStack, function(e){ return e.callId == callid; });
	var o = result[0];
	//console.log(o);
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


	var guiMsg = '{"SCeq":'+currentSCeq+',"Method":"'+Method+'","Type":"'+Type+'"}';
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
					$("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("Busy");
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
				ShowAuxView(jsonData.data);
			} else if(type=="SKILL") {
				ShowSkillView(jsonData.data);
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
					$("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("Busy");
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

function ShowSkillView(auxList)
{
	
	var auxRow = '';//"<option value=''>&nbsp;</option>";
	for(i=0; i<auxList.length; i++) {
		auxRow += "<option value='"+auxList[i].skill_id+"'>"+auxList[i].skill_name+"</option>";
	}
	//auxRow += "<option value=''>DID</option>";
	auxRow += "<option value='' selected>&nbsp;</option>";

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
	
	duration_timeout = setTimeout(timerFunction, 1000);
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
function IframeOpen(url)
{
	//var iframe = " <iframe class='iframe-url' src='"+url+"'></iframe>";
	//$("#EventListener").after(iframe);
	
	$("#purl").show().attr('src', url);
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
			
			cookieValue.webSiteKeyToChat = resp.web_key;
			cookieValue.callIdToChat = resp.call_id;
			cookieValue.customerNameToChat = resp.name;
			var email = resp.email;
			var contact = typeof resp.contact=='undefined' ? "" : resp.contact;
			var transfered = typeof resp.auto_answer=='undefined' ? "" : resp.auto_answer;
			
			$.cookie("cChatLanguage",resp.language);
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
					lastRegTime: new Date()
				};
				console.log(options);
				var len = $.map(chatBoxes, function(n, i) { return i; }).length;
				var right = 235 + len*304;
				createChatBox( cookieValue.callIdToChat, cookieValue.customerNameToChat, "", right, 300, 380, maxZIndex, idleTime, lastTypingAt, textTyping, false, options );
			
				if(transfered=="NO") {
					setTimeout(function() {
						var $box = $("#chatbox_"+resp.call_id);
						var $boxBody = $box.find(".gcaw-body");
					
						var msg = {
							from:agentId,
							name:agentNick,
							msg:"My name is " + agentNick,
							from_type: "AGNT",
							timestamp:""
						};
						var row = msgRow(msg);
						$boxBody.append(row);
						
					}, 1500);
					
					setTimeout(function(){
						var $box = $("#chatbox_"+resp.call_id);
						var $boxBody = $box.find(".gcaw-body");
						var msg = {
							from:agentId,//$.cookie(cookieValue.callIdToChat), 
							name:agentNick,
							msg:"How may I help you?",
							from_type: "AGNT",
							timestamp:""
						};
						row = msgRow(msg);
						$boxBody.append(row);
						
					}, 3000);
				}
				
			}
		//});
		
		//if(resp.audo_answer!='NO') {
			//$("#chatAcceptRequest").hide();
			//$("#btnChatAccept").click();
		//}
	},
	EmailChatHistory: function(callid, email) {
		$.ajax({
			type:"POST",
			url:"<?php echo $this->url("task=agent&act=email-chat-history&cid=");?>"+callid+"&mailid="+email.replace(" ",""),
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
		if(response.length>0) {
			$boxBody = $box.find(".gcaw-body");
			var html = "";
			for(i=0; i<response.length; i++) {
				response[i]["msg"] = atob(response[i].msg);
				html += msgRow(response[i]);
			}
			$("._m_r").remove();
			$box.find(".btnShowRecentMsg").after(html);
			$box.find(".btnShowRecentMsg").remove();
			//$boxBody.html(html);
		}
	},
	
	ChatClose: function(callid, is_incoming_close) {
		try {
			_Chat.ConversationClosedNotification(callid);
			if(typeof chatBoxes[callid]!='undefined') chatBoxes[callid].isIdle = false;
			if(typeof chatBoxes[callid]!='undefined') chatBoxes[callid].conversationClosed = true;
			$.cookie("chatBoxes", JSON.stringify(chatBoxes));
			if (!is_incoming_close) obj.ChatClose(callid, cookieValue.webKey);
		} catch (ex) {
			alert("Exception: " + ex.message);
		}
	},
	ConversationClosedNotification: function(callid) {
		$box = $("#chatbox_"+callid);
		var $boxBody = $box.find(".gcaw-body");
		var name = typeof chatBoxes[callid]!='undefined' ? chatBoxes[callid].name : "";
		$boxBody.append("<div id='gcaw-closed-conversation'>Conversation closed by "+name+"</div>");
		if(typeof $boxBody[0]!='undefined') $boxBody.scrollTop($boxBody[0].scrollHeight);
	},
	
	ChatData: function(resp) {
		try {
			var data = JSON.parse(atob(resp.data));
			
			var endTime = new Date();
			
			console.log("ON/OFF");
			
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
				lastRegTime: new Date()
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
  // Let's check if the browser supports notifications
  if (!("Notification" in window)) {
    //alert("This browser does not support desktop notification");
  }

  // Let's check whether notification permissions have already been granted
  else if (Notification.permission === "granted") {
    // If it's okay let's create a notification
	//var title = 'Text from '+from;
	var img = 'ccd/notification.png';
	var notification = new Notification(title, { body: msg, icon: img });
	notification.onclick = function(){
		notification.close();
        //window.open();
        window.focus();
	};
	//setTimeout(notification.close.bind(notification), 500);
	//notification.onclose = function (e) {
	//	console.log("Notification closedtest");
	//	window.opener.focusCallController();
	//};
	//setTimeout(notification.close.bind(notification), 500);
    //var notification = new Notification(msg);
  }

  // Otherwise, we need to ask the user for permission
  else if (Notification.permission !== 'denied') {
    Notification.requestPermission(function (permission) {
      // If the user accepts, let's create a notification
      if (permission === "granted") {
        //var title = 'Text from '+from;
		var img = 'ccd/notification.png';
		var notification = new Notification(title, { body: msg, icon: img });
		notification.onclick = function(){
			notification.close();
			//window.open();
			window.focus();
		};
      }
    });
  }

  // At last, if the user has denied notifications, and you 
  // want to be respectful there is no need to bother them any more.
}
Notification.requestPermission().then(function(result) {
  console.log(result);
});
function spawnNotification(theBody,theIcon,theTitle) {
  var options = {
      body: theBody,
      icon: theIcon }
  var n = new Notification(theTitle,options);
}
</script>

