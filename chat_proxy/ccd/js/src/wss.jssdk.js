var agentId = "1015";
var agentNick = "Mr. X";

var obj = null;
var customerChatBox = {};
var $gchatBtn;
var chat_web_key;
var chat_call_id;

var cCustomerName = null;
var cCustomerEmail = null;
var cCustomerSubject = 'AA';
var cCustomerSubjectText = cob_subject;
var cCustomerLanguage = null;

//var RequestSession = new Array();
//var currentRequestSCeq = 100;
var requestTimeoutInterval = 6; //seconds;
var ChatJoinRequestTime;
var timeout = null;

var chatIdleInterval = 6*60;//seconds
var cahtIdleTime;
var idleTimeout = null;
var textTypingAt = new Date();
var textTyping = false;
var textTypingMineAt = new Date();
var textTypingMine = false;
var conversationClosed = false;
var textBoxfocus = false;
var socket_ip = "";
var socket_port = "";
var available = 0;
//var baseUrl;
var geoLocation = "";
var natPingInterval = null;
var natPingResponseCount = 0;
var logId = '';
var checkQueueInterval = null;

var leave_text_length = 300;
var default_lan = '';
var user_in_queue = false;
var chat_accept = false;
var customerMsgCount = 0;
var showWebchatHistoryFlag = false;
var noInternetConnection = false;
var try_timeout = null;
var max_sec_limit_to_try = 10;
var countdown_sec_limit_to_try = max_sec_limit_to_try;
var chatLandingTime = '';
var hasAgentResponded = false;

/*var officeStatus = 'open';
var offTimeFrom = offtime_from;
var offTimeTo = offtime_to;*/
// var txt_cursor_position_start = 0;
// var txt_cursor_position_end = 0;


//webchat storage var
var storageVars = {
    callIdToChat: user.toLowerCase()+"WsslCallIdToChat",
    chatConversation: user.toLowerCase()+"WsslChatConversation",
    chatRequestInfo: user.toLowerCase()+"WsslChatRequestInfo"
}

var storageValue = {
    callIdToChat: !statusHtmlStorage(storageVars.callIdToChat) ? [] : getHtmlStorage(storageVars.callIdToChat),
    chatConversation: !statusHtmlStorage(storageVars.chatConversation) ? {} : getHtmlStorage(storageVars.chatConversation),
    chatRequestInfo: !statusHtmlStorage(storageVars.chatRequestInfo) ? {} : getHtmlStorage(storageVars.chatRequestInfo),
}
console.log("storage value");
console.log(storageVars);
console.log(storageValue);
function greetingsText(){
	var gre_start_time = JSON.parse(greetings_start_time);
	var gre_end_time = JSON.parse(greetings_end_time);
	var gre_message = JSON.parse(greetings_message);
	var msg = '';

	$.each(gre_start_time, function(idx, item){
		var today = new Date();
		var start_time = item.split(':');
		var end_time = gre_end_time[idx].split(':');
		var start_date = new Date(today.getFullYear(), today.getMonth(), today.getDate(), parseInt(start_time[0]), parseInt(start_time[1]));
		var end_date = new Date(today.getFullYear(), today.getMonth(), today.getDate(), parseInt(end_time[0]), parseInt(59));

		// console.log(today);
		// console.log(start_time);
		// console.log(end_time);
		// console.log(start_date);
		// console.log(end_date);
		if(start_date <= today && end_date >= today){
			msg = gre_message[idx];
			return false;
		}
	});
	return msg;
}

function GetVisitorInfo(available,country,region,city,postal_code,ip,port,language,service) {
	var location = country+"|"+city+"|"+region;
	socket_ip = ip;
	socket_port = port;
	geoLocation = location;
	language = language;
	service = service;
	//console.log("1="+available);
	available = parseInt(available);
	//document.getElementById("gchat-button").setAttribute("data-available", available);
	//document.getElementById("gchat-button").setAttribute("data-location", location);
	//document.getElementById("gchat-button").setAttribute("data-port", port);
	//document.getElementById("gchat-button").setAttribute("data-language", language);

	//$gchatBtn = $(".gcc-chat-button");

	enableChatButton(available,language,service);
}

function UpdateChatLog(callid)
{
    $.ajax({
		type:"POST",
		url:baseUrl+"/chat_agent.php",
		data:{upd_log: "1", user:user, cid: callid, logid: logId},
		dataType:"jsonp",
		success:function(resp) {

		}
	});
}

function EnterChatDetailLog(callid)
{
	$.ajax({
		type:"post",
		url:baseUrl+"/insert_chat_detail_log.php",
		data:{
			callid: callid, 
			cCustomerName: $.cookie("cCustomerName"),
			cCustomerEmail: '', //$.cookie("cCustomerEmail"),
			cCustomerContactNumber: $.cookie("cCustomerContactNumber"),
			cCustomerSubject: $.cookie("cCustomerSubject"),
			cCustomerVerify: $.cookie("cCustomerVerify"),
			page_id: page_id,
			site_key: site_key,
			user: user
		},
		dataType:"text",
		contentType: 'application/x-www-form-urlencoded',
		success:function(resp) {
			var resp_data = JSON.parse(resp);
			//console.log(resp_data);
			welcomeMessageToCustomer("&nbsp;Finding free agent, Please wait",false);
		}
	});
}

function jssdk() {
	$gchatBtn = $(".gcc-chat-button");
	//var page_id = $gchatBtn.data('page_id');
	//console.log(page_id);
	//var available = $gchatBtn.data("available");
	//var site_key = $gchatBtn.data('site_key');
	//var user = $gchatBtn.data('user');
	$.ajax({
		type:"POST",
		url:baseUrl+"/client_info.php",
		data:{page_id:page_id,site_key:site_key,user:user},
		dataType:"jsonp",
		success:function(resp) {
			GetVisitorInfo(resp.available,resp.country,resp.region,resp.city,resp.postal_code,resp.ws_ip,resp.ws_port,resp.language,resp.service);
			if(!ws_token_falg)
				$("#chat_preloader").hide();
		}
	});
	
	window.onbeforeunload = function(e) {
		/*
		var msg = 'Do you want to close conversation?';
		e = e || window.event;

		if(e) {
			obj.Close();
			e.returnValue = msg;
		}*/
		//$("#gcc-action-close").click();
		
		var c_w_k = $.cookie("cCustomerWebKey");
		var c_c_id = $.cookie("cCustomerCallId");
		var cc_ag_name = $.cookie("cChatAgentName");
		
		//if(c_w_k!=null && c_w_k.lenght>0) ChatClose();
		//else if(obj!=null) obj.ChatClose();
		clearCookie();
		$.cookie("customerChatBox","{}");
		$.cookie("cCustomerWebKey", c_w_k);
		$.cookie("cCustomerCallId", c_c_id);
		$.cookie("cChatAgentName", cc_ag_name);
		//return false;
	};
}


function enableChatButton(available,language,service) {
	var gccChatButton = "gCCCustomerChatButton";
	//var layout = $gchatBtn.data('layout');
	$("body").append("<div id='"+gccChatButton+"' class='"+layout+"' data-language='"+language+"' data-service='"+service+"'><span class='gcc-left'>Chat Now</span><span class='gcc-right'>&plus;</span></div>");
	var $chatButton2 = $("#"+gccChatButton);
	$gchatBtn.addClass("gchat-button");//.html("Live Chat");//.attr("id",gccChatButton)
	$gchatBtn.removeClass("btn-disable");
	//var available = $gchatBtn.data("available");
	availabe = parseInt(available);

	//$gchatBtn = $("#"+gccChatButton);
	//console.log(availabe);
	//if(availabe>0) {
		$gchatBtn.removeClass("btn-disable");
		$gchatBtn.click(function(){
			//if(availabe>0)
			if($(".g-chat-customer-window").length==0) {
				$chatButton2.hide();
				joinToChat();
			} else {
				$chatButton2.hide();
				$(".g-chat-customer-window").show();
			}
		});
		$chatButton2.click(function(){
			$chatButton2.hide();
			if($(".g-chat-customer-window").length==0) {
				joinToChat();
			} else {
				$(".g-chat-customer-window").show();
			}
		});

	//} else {
	//  return;
	//}


	enableCookie();
	$.cookie("available_agent",available);
	// existingChatBoxes();
	//console.log("2="+language);
	$chatButton2.click(); //added this line for popup
}


function joinToChat() {

	//$gchatBtn.html("Live Chat");
	if ($("#g-chat-customer-window").length <= 0) {
		var html = getForm();
		var w = $(window).width()-12;
		var h = $(window).height()-12;
		//console.log(w+"|"+h);
		createCustomerChatWindow("", "", w, h, html);
		/*
		window.onresize = function(){
			$("#g-chat-customer-window").width($(window).width()-12);
			$("#g-chat-customer-window").height($(window).height()-12);
		};*/
	}
}

/**
 * Chat leave text form
 */
function chat_leave_text_form(){
	var lng = $("#gCCCustomerChatButton").data("language");//language;//$gchatBtn.data("language");
	var srv = $("#gCCCustomerChatButton").data("service");
	var lng_array = typeof lng!='undefined' && lng.length>0 ? lng.split("|") : new Array();
	var srv_array = typeof srv!='undefined'&& srv.length>0 ? srv.split("|") : new Array();

	var name = !$.cookie("tempCustomerName") ? "" : $.cookie("tempCustomerName");
	var email = ""; //!$.cookie("tempCustomerEmail") ? "" : $.cookie("tempCustomerEmail");
	var contactNumber = !$.cookie("tempCustomerContactNumber") ? "" : $.cookie("tempCustomerContactNumber");

	var html = "";
	html += "<form id='leave_text_form' class='cmxform mb-0'>";
	html += "<div class='form-group row'>";
	html += "<label class='col-md-12 greetings-text'>"+greetingsText()+"!!!</label>";
	// html += "<div class='col-xs-12 control-label'>Dear Customer, all our service executives are busy at this moment but will be available shortly to serve you. Meanwhile you can contact us through <strong>WhatsApp</strong> by saving number <strong>01614000121</strong> in your mobile contact.<br/></div>";
    var available = $.cookie("available_agent");
    if (office_status == 'open' && available == 0) {
        html += "<div id='chat_queue_msg' class='col-md-12'></div>";
    } else if (office_status == 'close') {
        html += "<div class='col-xs-12 control-label'></div>";
    }
	html += "</div>";

	// error msg
	html += "<div id='error_div' class='form-group row hide'>";
	html += "<div class='col-xs-12 mb-10'>";
	html += "<div class='alert alert-danger'></div>";
	html += "</div>";
	html += "</div>";

	// Name field
	html += "<div class='form-group form-row row'>";
	html += "<label class='col-xs-12 control-label' for='cCustomerName'>Name";
	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	html += "<div class='col-xs-12'>";
	html += "<input id='cCustomerName' name='cCustomerName' placeholder='Name' class='form-control input-md' type='text' maxlength='20' value='"+name+"' ";
	html += "data-rule-required='true' data-msg-required='Name is required!' data-rule-maxlength='20' data-msg-maxlength='Please enter no more than 20 characters!' ";
	html += ">";
	html += "</div>";
	html += "</div>";

	// Email field
	// html += "<div class='form-group form-row row'>";
	// html += "<label class='col-xs-12 control-label' for='cCustomerEmail'>Email";
	// html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	// html += "<div class='col-xs-12'>";
	// html += "<input id='cCustomerEmail' name='cCustomerEmail' placeholder='Email' class='form-control input-md' type='text' value='"+email+"' ";	
	// html += "data-rule-required='true' data-msg-required='Email is required!' data-rule-email='true' data-msg-email='Email is not valid!' data-rule-maxlength='35' data-msg-maxlength='Please enter no more than 35 characters!'";
	// html += ">";
	// html += "</div>";
	// html += "</div>";

	// contact field
	html += "<div class='form-group form-row row'>";
	html += "<label class='col-xs-12 control-label' for='cCustomerContactNumber'>Contact Number";
	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	html += "<div class='col-xs-12'>";		
	html += "<input id='cCustomerContactNumber' name='cCustomerContactNumber' placeholder='Contact Number' class='form-control input-md' type='number' value='"+contactNumber+"' ";
	html += "data-rule-required='true' data-msg-required='Contact Number is required!' data-rule-number='true' data-msg-number='This is not valid number!' data-rule-maxlength='11' data-msg-maxlength='Please enter no more than 11 characters!' ";
	html += ">";
	html += "</div>";
	html += "</div>";

	// if(srv_array.length>0) {
	// 	html += "<div class='form-group form-row row' style='display:none;'>";
	// 	html += "<label class='col-xs-12 control-label' for='cCustomerSubject'>Service";
	// 	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	// 	html += "<div class='col-xs-12'>";
	// 	html += "<select id='cCustomerSubject' name='cCustomerSubject' class='form-control' data-rule-required='true' data-msg-required='Service is required!' data-rule-maxlength='2' data-msg-maxlength='Please enter no more than 2 characters!'>\
	// 				<option value=''>---Select---</option>";
	// 				for(i=0; i<srv_array.length; i++) {
	// 					var key_title = srv_array[i].split("=");
	// 					html += "<option selected value='"+key_title[0]+"'>"+key_title[1]+"</option>";
	// 				}
	// 	html += "</select>";
	// 	html += "</div>";
	// 	html += "</div>";
	// } else {
	// 	html += "<div class='form-group form-row row' style='display:none;'>";
	// 	html += "<label class='col-xs-12 control-label' for='cCustomerSubject'>Service";
	// 	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	// 	html += "<div class='col-xs-12'>";
	// 	html += "<select id='cCustomerSubject' name='cCustomerSubject' class='form-control' data-rule-required='true' data-msg-required='Service is required!' data-rule-maxlength='2' data-msg-maxlength='Please enter no more than 2 characters!'>\
	// 				<option value=''>---Select---</oloadption>";
	// 				for(i=0; i<srv_array.length; i++) {
	// 					var key_title = srv_array[i].split("=");
	// 					html += "<option value='"+key_title[0]+"'>"+key_title[1]+"</option>";
	// 				}
	// 	html += "</select>";
	// 	html += "</div>";
	// 	html += "</div>";
	// }

	html += "<div class='form-group form-row row mb-0'>";
	html += "<label class='col-xs-12 control-label' for='leave_text_field'>Message";
	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	html += "<div class='col-xs-12 mb-10'>";
	html += "<textarea id='leave_text_field' name='leave_text_field' placeholder='Please leave your text here' class='form-control leave-text-area' rows='4' maxlength='"+leave_text_length+"' ";
	html += "data-rule-required='true' data-msg-required='This field is required!' data-rule-maxlength='"+leave_text_length+"' data-msg-maxlength='Please enter no more than "+leave_text_length+" characters!'";
	html += "></textarea>";
	html += "<small id='leave_text_field_help_block' class='form-text text-muted'>Characters left: "+leave_text_length+"</small>";
	html += "</div>";
	html += "</div>";

	html += "<div class='form-group form-row row'>";
	html += "<div class='col-xs-12'>";
	html += "<button id='leave_text_btn' name='leave_text_btn' class='btn btn-warning'>Submit</button>";
	html += "</div>";
	html += "</div>";
	html += "</form>";

	return html;
}

/**
 * Chat login form
 */
function chat_login_form(){
	var lng = $("#gCCCustomerChatButton").data("language");//language;//$gchatBtn.data("language");
	var srv = $("#gCCCustomerChatButton").data("service");
	var lng_array = typeof lng!='undefined' && lng.length>0 ? lng.split("|") : new Array();
	var srv_array = typeof srv!='undefined'&& srv.length>0 ? srv.split("|") : new Array();

	var name = !$.cookie("tempCustomerName") ? "" : $.cookie("tempCustomerName");
	var email = ""; //!$.cookie("tempCustomerEmail") ? "" : $.cookie("tempCustomerEmail");
	var contactNumber = !$.cookie("tempCustomerContactNumber") ? "" : $.cookie("tempCustomerContactNumber");

	var html = "";
	html += "<form id='chat_login_form' class='cmxform'>";
	html += "<div class='form-group row'>";
	html += "<label class='col-md-12 greetings-text'>"+greetingsText()+"!!!</label>";
	html += "<div class='col-xs-12 control-label'>Welcome to Robi Sheba. Please fill in the form below to continue</div>";
	html += "</div>";

	// error msg
	html += "<div id='error_div' class='form-group row hide'>";
	html += "<div class='col-xs-12 mb-10'>";
	html += "<div class='alert alert-danger'></div>";
	html += "</div>";
	html += "</div>";

	// Name field
	html += "<div class='form-group form-row row'>";
	html += "<label class='col-xs-12 control-label' for='cCustomerName'>Name";
	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	html += "<div class='col-xs-12'>";
	html += "<input id='cCustomerName' name='cCustomerName' placeholder='Name' class='form-control input-md' type='text' maxlength='20' value='"+name+"' ";
	html += "data-rule-required='true' data-msg-required='Name is required!' data-rule-maxlength='20' data-msg-maxlength='Please enter no more than 20 characters!' ";
	html += ">";
	html += "</div>";
	html += "</div>";

	// Email field
	// html += "<div class='form-group form-row row'>";
	// html += "<label class='col-xs-12 control-label' for='cCustomerEmail'>Email";
	// html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	// html += "<div class='col-xs-12'>";
	// html += "<input id='cCustomerEmail' name='cCustomerEmail' placeholder='Email' class='form-control input-md' type='text' value='"+email+"' ";	
	// html += "data-rule-required='true' data-msg-required='Email is required!' data-rule-email='true' data-msg-email='Email is not valid!' data-rule-maxlength='35' data-msg-maxlength='Please enter no more than 35 characters!'";
	// html += ">";
	// html += "</div>";
	// html += "</div>";

	// contact field
	html += "<div class='form-group form-row row'>";
	html += "<label class='col-xs-12 control-label' for='cCustomerContactNumber'>Contact Number";
	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	html += "<div class='col-xs-12'>";		
	html += "<input id='cCustomerContactNumber' name='cCustomerContactNumber' placeholder='Contact Number' class='form-control input-md' type='number' value='"+contactNumber+"' ";
	html += "data-rule-required='true' data-msg-required='Contact Number is required!' data-rule-number='true' data-msg-number='This is not valid number!' data-rule-maxlength='11' data-msg-maxlength='Please enter no more than 11 characters!' ";
	html += ">";
	html += "</div>";
	html += "</div>";

	// if(srv_array.length>0) {
	// 	html += "<div class='form-group form-row row' style='display:none;'>";
	// 	html += "<label class='col-xs-12 control-label' for='cCustomerSubject'>Service";
	// 	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	// 	html += "<div class='col-xs-12'>";
	// 	html += "<select id='cCustomerSubject' name='cCustomerSubject' class='form-control' data-rule-required='true' data-msg-required='Service is required!' data-rule-maxlength='2' data-msg-maxlength='Please enter no more than 2 characters!'>\
	// 				<option value=''>---Select---</option>";
	// 				for(i=0; i<srv_array.length; i++) {
	// 					var key_title = srv_array[i].split("=");
	// 					html += "<option selected value='"+key_title[0]+"'>"+key_title[1]+"</option>";
	// 				}
	// 	html += "</select>";
	// 	html += "</div>";
	// 	html += "</div>";
	// } else {
	// 	html += "<div class='form-group form-row row' style='display:none;'>";
	// 	html += "<label class='col-xs-12 control-label' for='cCustomerSubject'>Service";
	// 	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	// 	html += "<div class='col-xs-12'>";
	// 	html += "<select id='cCustomerSubject' name='cCustomerSubject' class='form-control' data-rule-required='true' data-msg-required='Service is required!' data-rule-maxlength='2' data-msg-maxlength='Please enter no more than 2 characters!'>\
	// 				<option value=''>---Select---</option>";
	// 				for(i=0; i<srv_array.length; i++) {
	// 					var key_title = srv_array[i].split("=");
	// 					html += "<option value='"+key_title[0]+"'>"+key_title[1]+"</option>";
	// 				}
	// 	html += "</select>";
	// 	html += "</div>";
	// 	html += "</div>";
	// }

	if(lng_array.length>1) {

		html += "<div class='form-group form-row row'>";
		html += "<label class='col-xs-12 control-label' for='cCustomerLanguage'>Language";
		html += "&nbsp;<span class='chat-form-error'>*</span></label>";
		html += "<div class='col-xs-12'>";
		html += "<select id='cCustomerLanguage' name='cCustomerLanguage' class='form-control'>\
				<option value=''>---Select---</option>";
					for(i=0; i<lng_array.length; i++) {
						var key_title = lng_array[i].split("=");
						html += "<option value='"+key_title[0]+"'>"+key_title[1]+"</option>";
					}
		html += "</select>";
		html += "</div>";
		html += "</div>";

	} else {

		html += "<div class='form-group form-row row' style='display:none;'>";
		html += "<label class='col-xs-12 control-label' for='cCustomerLanguage'>Language";
		html += "&nbsp;<span class='chat-form-error'>*</span></label>";
		html += "<div class='col-xs-12'>";
		html += "<select id='cCustomerLanguage' name='cCustomerLanguage' class='form-control'>";
					for(i=0; i<lng_array.length; i++) {
						var key_title = lng_array[i].split("=");
						html += "<option value='"+key_title[0]+"'>"+key_title[1]+"</option>";
					}
		html += "</select>";
		html += "</div>";
		html += "</div>";

	}

	html += "<div id='verify_user' class='form-group form-row row'>";
	html += "<label class='col-xs-12 control-label'>Are you Robi registered customer?";
	html += "&nbsp;<span class='chat-form-error'>*</span></label>";
	html += "<div class='checkbox-inline'>";
  	html += "<input id='verify_user_yes' name='verify_user' type='radio' class='login-verify-user' value='Y'  />";
  	html += "<label class='mr-10' for='verify_user_yes'>Yes</label>";
  	html += "<input id='verify_user_no' name='verify_user' type='radio' class='login-verify-user' value='N' checked='checked' />";
  	html += "<label class='mr-10' for='verify_user_no'>No</label>";
	html += "</div>"
	html += "</div>";

	html += "<div class='form-group form-row row'>";
	html += "<label class='col-xs-12 control-label' for='cCustomerInfoSend'></label>";
	html += "<div class='col-xs-12'>";
	html += "<button id='cCustomerInfoSend' name='cCustomerInfoSend' class='btn btn-warning'>OK</button>";
	html += "</div>";
	html += "</div>";
	html += "</form>";

    return html;
}
// checkServerTime();
function getForm() {
	var c_w_k = $.cookie("cCustomerWebKey");

	//if(c_w_k!=null && c_w_k.length>0) {
	//  return "";
	//}

	var lng = $("#gCCCustomerChatButton").data("language");//language;//$gchatBtn.data("language");
	var srv = $("#gCCCustomerChatButton").data("service");
	var lng_array = typeof lng!='undefined' && lng.length>0 ? lng.split("|") : new Array();
	var srv_array = typeof srv!='undefined'&& srv.length>0 ? srv.split("|") : new Array();


	var name = !$.cookie("tempCustomerName") ? "" : $.cookie("tempCustomerName");
	var email = ""; //!$.cookie("tempCustomerEmail") ? "" : $.cookie("tempCustomerEmail");
	var contactNumber = !$.cookie("tempCustomerContactNumber") ? "" : $.cookie("tempCustomerContactNumber");

	var available = $.cookie("available_agent");
	/*if(available==0) {
		return chat_leave_text_form();
	}*/
   /* var officeStatus = 'open';

    console.log("officeStatus:"+officeStatus);*/

    // console.log("officeStatus:"+office_status);
    if (office_status == 'close' || available==0) {
        return chat_leave_text_form();
    }
	return chat_login_form();

}

/**
 * Close button 
 *
 * If click on close button then show confirmation msg. 
 * 1. If click on No button then stay on that chat session
 * 2. If click on Yes button then 
 *    a. Close conversation
 *    b. Clear cookie data
 *    c. Show rating option
 */
function closeChatSessionFromWss(){
	var c_w_k = $.cookie("cCustomerWebKey");
	console.log(c_w_k);
	console.log(obj);
	console.log( $.cookie("cCustomerCallId"));
	console.log(obj!=null);

	if(c_w_k!=obj && c_w_k != null && c_w_k.lenght>0) {
		ChatClose("");
	} else if(obj!=null) {
		obj.ChatClose();
	}

	$gchatBtn.show();
	$.cookie("customerChatBox","{}");
	var cid = $.cookie("cCustomerCallId");
	
	if( cid!=null && cid!='null' && cid.length!=0) {
		clearCookie();							
		window.clearInterval(checkQueueInterval);
		if(user_in_queue){
			updateChatQueue(cid);							
			user_in_queue =  false;
			// window.opener.document.location.href = return_url;
			// window.opener.location.reload(false);
			// window.opener.location = return_url;
			//setTimeout("window.close()",2000);
		}else{	
			  
			var currentTime = new Date();
			var chatDurationForIce = (currentTime.getTime() - chatLandingTime.getTime()) / 1000;
			var agentResponse = ($.cookie("hasAgentResponded") == 'true');
			var numCustomerMsg = Number($.cookie("customerMsgCount"));
			if (numCustomerMsg >= 2 && chatDurationForIce >= 120 && agentResponse == true) {
				$.cookie("hasAgentResponded", null);
				$.cookie("customerMsgCount", null);
				showRatingView(cid);
			} else {
				$.cookie("hasAgentResponded", null);
				$.cookie("customerMsgCount", null);
				showThankYou();
	 
		 
									  
			}
			// setTimeout("window.close()",2000);
		}
		
	} else {
		clearCookie();
		// window.opener.location.reload(false);
		// window.opener.document.location.href = return_url;
		// window.opener.location = return_url;
		//window.close();
		setTimeout("window.close()",2000);
	}
}
function closeChatSession(){
	$("#btnChatClose").click(function(){
		bootbox.confirm({
		    message: "Do you want to close this conversation?",
		    buttons: {
		        confirm: {
		            label: 'Yes',
		            className: 'btn-success'
		        },
		        cancel: {
		            label: 'No',
		            className: 'btn-danger'
		        }
		    },
		    callback: function (result) {
		        if(result){
					closeChatSessionFromWss();
		        }
		    }
		});
	});
}

function updateChatQueue(cid){
	$.ajax({
		type:"post",
		url:baseUrl+"/update_chat_queue.php",
		data:{
			cCustomerCallId: cid,
			page_id: page_id,
			site_key: site_key,
			user: user,
			close: true,
		},
		dataType:"text",
		contentType: 'application/x-www-form-urlencoded',
		success:function(resp) {
			var resp_data = JSON.parse(resp);
			// console.log(resp_data);
		}
	});
}
/**
 * Return button 
 *
 * If click on return button then goto mainwebsite. 
 * 1. If there are not open any chat session then directly going to main website
 * 2. If there are open any chat session then show confirmation msg
 *    a. If click on No button then stay on that chat session
 *    b. If click on Yes button then 
 *         i. Close conversation
 *        ii. Clear cookie data
 *       iii. Show rating option
 */
function returnChatSession(){
	$("#btnChatReturn").click(function(){
		var cid = $.cookie("cCustomerCallId");
		var c_w_k = $.cookie("cCustomerWebKey");

		if((cid!=null && cid!='null' && cid.length!=0) || (c_w_k!=null && c_w_k.lenght>0) || obj!=null) {
			bootbox.confirm({
			    message: "Do you want to go Home page?",
			    buttons: {
			        confirm: {
			            label: 'Yes',
			            className: 'btn-success'
			        },
			        cancel: {
			            label: 'No',
			            className: 'btn-danger'
			        }
			    },
			    callback: function (result) {
			        if(result){
						if(c_w_k!=obj && c_w_k != null && c_w_k.lenght>0)
							ChatClose("");
						else if(obj!=null) 
							obj.ChatClose();

						$gchatBtn.show();
						$.cookie("customerChatBox","{}");
						
						if( cid!=null && cid!='null' && cid.length!=0) {
							clearCookie();
							window.clearInterval(checkQueueInterval);
							if(user_in_queue){
								updateChatQueue(cid);							
								user_in_queue =  false;
								// window.opener.document.location.href = return_url;
                                // window.opener.location.reload(false);
                                window.opener.location = return_url;
								setTimeout("window.close()",2000);
							}
							// else{							
								// showRatingView(cid);
							// }
						} else {
							clearCookie();
						}
						// window.opener.document.location.href = return_url;
                        // window.opener.location.reload(false);
                        window.opener.location = return_url;
						window.close();
			        }
			    }
			});
		}else{
			clearCookie();
			// window.opener.document.location.href = return_url;
            // window.opener.location.reload(false);
            window.opener.location = return_url;
			window.close();
		}				
	});
}

/**
 * Set language type when user start chat
 * 
 * By deafault set bangla language. When press Ctrl+m then change language
 */
function setLanguageText(){
    if(document.getElementById("language_en").checked){
        $.fn.bnKb.lang_change('e');
        default_lan = 'e';
    }else if(document.getElementById("language_bn").checked){
        $.fn.bnKb.lang_change('b');
        $("#chatTextInputBox").bnKb({
            'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
            'driver': phonetic,
            'writingMode': 'b'
        });
        default_lan = 'b';
    }	
}

function setDefaultLanguage(){
	if(default_lan == ''){
	    // $("#chatTextInputBox").bnKb({
	    //     'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
	    //     'driver': phonetic,
			// 'writingMode': 'b'
	    // });
	    default_lan = 'e';
	}
}

/**
 * Submit customer leave text data
 * 
 */
function submitLeaveText(){
	$("#leave_text_field").keyup(function(){
		if((leave_text_length - $(this).val().length) >= 0 )
	  		$("#leave_text_field_help_block").html("Characters left: " + (leave_text_length - $(this).val().length));
	});
	$("#leave_text_form").validate();  
    $("#leave_text_form").on('submit', function(e) {
		$("#error_div").addClass('hide');
		$("#error_div").find("div.alert-danger").html("");

		var cCustomerName = $("#cCustomerName").val();
		var lt = /</g,
		   gt = />/g,
		   ap = /'/g,
		   ic = /"/g;
		cCustomerName = cCustomerName.toString().replace(lt, "").replace(gt, "").replace(ap, "").replace(ic, "");
		
		var cCustomerEmail = "";//$("#cCustomerEmail").val();
		var cCustomerContactNumber = $("#cCustomerContactNumber").val();
		var cCustomerSubject = ''; //$("#cCustomerSubject").val();
		var leave_text_field = $("#leave_text_field").val();
		
		var isvalid = $("#leave_text_form").valid();
        if (isvalid) {
            e.preventDefault();            
    		$("#leave_text_form").addClass('hide');
			$("#leave_text_form").after(loading_html());

            // submit customer message
			$.ajax({
				type:"post",
				url:baseUrl+"/save_chat_msg.php",
				data:{
					cCustomerName: cCustomerName, 
					cCustomerEmail: cCustomerEmail,
					cCustomerContactNumber: cCustomerContactNumber,
					leave_text_field: leave_text_field,
					cCustomerSubject: cCustomerSubject,
					page_id: page_id,
					site_key: site_key,
					user: user
				},
				dataType:"text",
				contentType: 'application/x-www-form-urlencoded',
				success:function(resp) {											
					$("#chat_body_preloader").remove();
					var resp_data = JSON.parse(resp);

					if(resp_data.result==true){
						$(".gccw-body").html('<div class="col-xs-12 control-label">'+resp_data.message+'</div>');
						customerMsgCount = 0;
						closeChatSessionFromWss();
					}else{
						if(typeof resp_data.data != 'undefined' && resp_data.data.length > 0){
							$.each(resp_data.data, function(key, error_items){
								$.each(error_items.msg, function(idx, msg_item){
									$("#"+error_items.field).addClass('error');
									$("#"+error_items.field).parent('div').find("label#"+error_items.field+"-error").remove();
									$("#"+error_items.field).after('<label id="'+error_items.field+'-error" class="error" for="'+error_items.field+'">'+msg_item+'</label>')
								});
							});						
						}
						$("#error_div").removeClass('hide');
						$("#error_div").find("div.alert-danger").html(resp_data.message);
						$("#leave_text_form").removeClass('hide');
					}
				}
			});
        }
    });
}

function chat_opt_form(){
	var html = '';
	html += "<form id='chat_opt_form' class='cmxform'>";
	html += "<input type='hidden' id='auth_code' name='auth_code' value=''>";
	html += "<div class='form-group row'>";
	// html += "<label class='col-md-12 greetings-text'>"+greetingsText()+"!!!</label>";
	html += "<div class='col-xs-12 control-label'>Please submit your One Time Password (OTP)</div>";
	html += "</div>";

	// error msg
	html += "<div id='error_div' class='form-group row hide'>";
	html += "<div class='col-xs-12 mb-10'>";
	html += "<div class='alert alert-danger'></div>";
	html += "</div>";
	html += "</div>";

	// sent type field
	html += "<div class='form-group form-row row'>";
	html += "<div class='col-xs-12'>";
	html += "<input id='opt_number' name='opt_number' class='form-control input-md' type='password' ";
	html += "data-rule-required='true' data-msg-required='This field is required!' ";
	html += ">";
	html += "</div>";
	html += "</div>";

	html += "</div>";
	html += "</div>";

	html += "<div class='form-group form-row row'>";
	html += "<div class='col-xs-4'>&nbsp;</div>";
	html += "<div class='col-xs-8'>";
	html += "<button id='opt_ok' name='opt_ok' class='btn btn-warning'>OK</button>";
	html += "&nbsp;&nbsp;<a id='opt_back' name='opt_back' class='btn btn-danger chat-back-button'>Back</a>";
	html += "</div>";
	html += "</div>";
	html += "</form>";

	return html;
}

/*
function chat_login_form_initialize(){
	$("#chat_login_form").validate();  
    $("#chat_login_form").on('submit', function(e) {    	
		$("#error_div").addClass('hide');
		$("#error_div").find("div.alert-danger").html("");

		cCustomerName = $("#cCustomerName").val();
		var lt = /</g,
		   gt = />/g,
		   ap = /'/g,
		   ic = /"/g;
		cCustomerName = cCustomerName.toString().replace(lt, "").replace(gt, "").replace(ap, "").replace(ic, "");
		//console.log(cCustomerName);
		
		cCustomerEmail = "";//$("#cCustomerEmail").val();
		cCustomerContact = $("#cCustomerContactNumber").val();
		cCustomerSubject = 'AA'; //$("#cCustomerSubject").val();
		cCustomerSubjectText = cob_subject; //$("#cCustomerSubject option:selected").text();
		cCustomerLanguage = $("#cCustomerLanguage").val();
		cCustomerLanguageText = $("#cCustomerLanguage option:selected").text();
		cCustomerVerify = $("input[name='verify_user']:checked").val();

		$.cookie("tempCustomerName", cCustomerName);
		// $.cookie("tempCustomerEmail", cCustomerEmail);
		$.cookie("tempCustomerContactNumber", cCustomerContact);
		$.cookie("tempCustomerSubject", cCustomerSubject);
		$.cookie("tempCustomerLanguage", cCustomerLanguage);
		$.cookie("tempcCustomerVerify", cCustomerVerify);

		var isvalid = $("#chat_login_form").valid();
		var verify_user = true;

		if(!cCustomerVerify){
			$("#chat_login_form").find('#verify_user .checkbox-inline').append('<label id="verify_user-error" class="error" for="verify_user">This field is required!</label>');
			verify_user = false;
		}

        if (isvalid && verify_user) {
        	// if(cCustomerVerify)
            e.preventDefault();
    		$("#chat_login_form").addClass('hide');
			$("#chat_login_form").after(loading_html());
            
			$("#g-chat-customer-window .gccw-header .gccw-name").html("&nbsp;Connecting...");

			$.cookie("cCustomerName", cCustomerName);
			// $.cookie("cCustomerEmail", cCustomerEmail);
			$.cookie("cCustomerContactNumber", cCustomerContact);
			$.cookie("cCustomerSubject", cCustomerSubject);
			$.cookie("cCustomerSubjectText", cCustomerSubjectText);
			$.cookie("cCustomerLanguage", 'EN');
			$.cookie("cCustomerLanguageText", cCustomerLanguageText);
			$.cookie("cCustomerVerify", cCustomerVerify);

			// console.log(cCustomerVerify);
			// console.log($.cookie("cCustomerVerify"));

			if(cCustomerVerify == 'Y'){
	            $.ajax({
					type:"post",
					url:baseUrl+"/send_chat_code.php",
					data:{
						otp_sent_type: 'MO', 
						cCustomerName: cCustomerName, 
						cCustomerEmail: cCustomerEmail,
						cCustomerContactNumber: cCustomerContact,
						cCustomerSubject: cCustomerSubject,				
						cCustomerVerify: cCustomerVerify,				
						page_id: page_id,
						site_key: site_key,
						user: user
					},
					dataType:"text",
					contentType: 'application/x-www-form-urlencoded',
					success:function(resp) {
						$("#chat_body_preloader").remove();
						var resp_data = JSON.parse(resp);

						if(resp_data.result==true){
							$("#chat_body_preloader").remove();
							$("#chat_login_form").remove();
							chat_opt_submit_form();
							$("#auth_code").val(resp_data.data.auth_code);
						}else{
							$("#error_div").removeClass('hide');
							$("#error_div").find("div.alert-danger").html(resp_data.message);
							$("#chat_login_form").removeClass('hide');
						}
					}
				});
			}else{
				$("#chat_body_preloader").remove();
				$("#chat_login_form").remove();
				console.log(obj);

				if(obj != null) {
					ChatJoin();
				} else {
					try {
						obj = new gPlexCCChatWSApi();
						//var wshref = (window.location.protocol === 'https') ? 'wss' : 'ws';
						//wshref += '://' + window.location.host;
						//var port = $gchatBtn.data("port");
						//obj.loadSettings('ws://'+socket_ip+':'+socket_port+'/chat');
						obj.loadSettings('wss://'+srv_name+':'+socket_port+'/chat?token='+srv_token);
						//obj.setNumLoggedIn(0);
						obj.Connect();

						//close button show if aggent is available
						// $("#btnChatClose").removeClass('hide');
					} catch (ex) {
						alert(ex.message);
					}
				}
			}

			// // otp code request
			// $.ajax({
			// 	type:"post",
			// 	url:baseUrl+"/chat/chat_authentication.php",
			// 	data:{
			// 		cCustomerName: cCustomerName, 
			// 		cCustomerEmail: cCustomerEmail,
			// 		cCustomerContactNumber: cCustomerContact,
			// 		cCustomerSubject: cCustomerSubject,
			// 		page_id: page_id,
			// 		site_key: site_key,
			// 		user: user
			// 	},
			// 	dataType:"text",
			// 	contentType: 'application/x-www-form-urlencoded',
			// 	success:function(resp) {
			// 		$("#chat_body_preloader").remove();
			// 		var resp_data = JSON.parse(resp);

			// 		if(resp_data.result==true){
			// 			$("#chat_body_preloader").remove();
			// 			$("#chat_login_form").remove();
			// 			chat_sent_type_selection();	
			// 			$("#auth_code").val(resp_data.data.auth_code);									
			// 		}else{
			// 			if(typeof resp_data.data != 'undefined' && resp_data.data.length > 0){
			// 				$.each(resp_data.data, function(key, error_items){
			// 					$.each(error_items.msg, function(idx, msg_item){
			// 						$("#"+error_items.field).addClass('error');
			// 						$("#"+error_items.field).parent('div').find("label#"+error_items.field+"-error").remove();
			// 						$("#"+error_items.field).after('<label id="'+error_items.field+'-error" class="error" for="'+error_items.field+'">'+msg_item+'</label>')
			// 					});
			// 				});
			// 			}
			// 			$("#error_div").removeClass('hide');
			// 			$("#error_div").find("div.alert-danger").html(resp_data.message);
			// 			$("#chat_login_form").removeClass('hide');
			// 		}
			// 	}
			// });
		}else{
			return false;
		}

    });
}
*/

function back_chat_login_form(){
	$('.chat-back-button').on('click', function(){
		var html = chat_login_form();
		$("#g-chat-customer-window .gccw-body").html(html);
		// chat_login_form_initialize();
	});	
}

/*
function chat_opt_submit_form(){
	var html = chat_opt_form();
	$("#g-chat-customer-window .gccw-body").html(html);
	back_chat_login_form();
	$("#chat_opt_form").validate(); 
	$("#chat_opt_form").bind("submit").on('submit', function(e) {    	
		$("#error_div").addClass('hide');
		$("#error_div").find("div.alert-danger").html("");

		var isvalid = $("#chat_opt_form").valid();
        if (isvalid) {
            e.preventDefault();
    		$("#chat_opt_form").addClass('hide');
			$("#chat_opt_form").after(loading_html());

            $.ajax({
				type:"post",
				url:baseUrl+"/checked_otp_code.php",
				data:{
					code: $('#opt_number').val(), 
					auth_code: $('#auth_code').val(),					
					page_id: page_id,
					site_key: site_key,
					user: user
				},
				dataType:"text",
				contentType: 'application/x-www-form-urlencoded',
				success:function(resp) {
					$("#chat_body_preloader").remove();
					var resp_data = JSON.parse(resp);

					if(resp_data.result==true){
						$("#chat_body_preloader").remove();
						$("#chat_opt_form").remove();

						if(obj != null) {
							ChatJoin();
						} else {
							try {
								obj = new gPlexCCChatWSApi();
								//var wshref = (window.location.protocol === 'https') ? 'wss' : 'ws';
								//wshref += '://' + window.location.host;
								//var port = $gchatBtn.data("port");
								//obj.loadSettings('ws://'+socket_ip+':'+socket_port+'/chat');
								obj.loadSettings('wss://'+srv_name+':'+socket_port+'/chat?token='+srv_token);
								//obj.setNumLoggedIn(0);
								obj.Connect();

								//close button show if aggent is available
								// $("#btnChatClose").removeClass('hide');
							} catch (ex) {
								alert(ex.message);
							}
						}

					}else{
						$("#error_div").removeClass('hide');
						$("#error_div").find("div.alert-danger").html(resp_data.message);
						$("#chat_opt_form").removeClass('hide');
					}
				}
			});
        }
    }); 
}
*/

function chat_sent_type_form(){
	var cCustomerEmail = "";//$.cookie("cCustomerEmail");
	var cCustomerContact = $.cookie("cCustomerContactNumber");

	var html = '';
	html += "<form id='chat_sent_type_form' class='cmxform'>";
	html += "<input type='hidden' id='auth_code' name='auth_code' value=''>";
	html += "<div class='form-group row'>";
	// html += "<label class='col-md-12 greetings-text'>"+greetingsText()+"!!!</label>";
	html += "<div class='col-xs-12 control-label'>Please select your preferred channel to receive your One Time Password (OTP) for completing your authentication.</div>";
	html += "</div>";

	// error msg
	html += "<div id='error_div' class='form-group row hide'>";
	html += "<div class='col-xs-12 mb-10'>";
	html += "<div class='alert alert-danger'></div>";
	html += "</div>";
	html += "</div>";

	// sent type field
	html += "<div class='form-group form-row row'>";
	html += "<div class='col-xs-2'>&nbsp;</div>";
	html += "<div class='col-xs-10'>";

	html += "<label class='radio-container'>"+cCustomerEmail;
  	html += "<input name='otp_sent_type' type='radio' class='radio-sent-type' value='EM' data-rule-required='true' data-msg-required='Please select one option!' />";
  	html += "<span class='checkmark'></span>";
  	html += "</label>";

  	html += "<label class='radio-container'>"+cCustomerContact;
  	html += "<input name='otp_sent_type' type='radio' class='radio-sent-type' value='MO' />";
  	html += "<span class='checkmark'></span>";
	html += "</label>"

	html += "</div>";
	html += "</div>";

	html += "<div class='form-group form-row row'>";
	html += "<div class='col-xs-4'>&nbsp;</div>";
	html += "<div class='col-xs-8'>";
	html += "<button id='sent_type_ok' name='sent_type_ok' class='btn btn-warning'>OK</button>";
	html += "</div>";
	html += "</div>";
	html += "</form>";

	return html;
}

/*
function chat_sent_type_selection(){
	var html = chat_sent_type_form();
	$("#g-chat-customer-window .gccw-body").html(html);
	$("#chat_sent_type_form").validate();  

    $("#chat_sent_type_form").bind("submit").on('submit', function(e) {    	
		$("#error_div").addClass('hide');
		$("#error_div").find("div.alert-danger").html("");

		otp_sent_type = $(".radio-sent-type:checked").val();
		$.cookie("cCustomerOtpSentType", otp_sent_type);

		var isvalid = $("#chat_sent_type_form").valid();
        if (isvalid) {
            e.preventDefault();
    		$("#chat_sent_type_form").addClass('hide');
			$("#chat_sent_type_form").after(loading_html());

            $.ajax({
				type:"post",
				url:baseUrl+"/send_chat_code.php",
				data:{
					otp_sent_type: otp_sent_type, 
					auth_code: $('#auth_code').val(),					
					page_id: page_id,
					site_key: site_key,
					user: user
				},
				dataType:"text",
				contentType: 'application/x-www-form-urlencoded',
				success:function(resp) {
					$("#chat_body_preloader").remove();
					var resp_data = JSON.parse(resp);

					if(resp_data.result==true){
						$("#chat_body_preloader").remove();
						$("#chat_sent_type_form").remove();
						chat_opt_submit_form();
						$("#auth_code").val(resp_data.data.auth_code);
					}else{
						$("#error_div").removeClass('hide');
						$("#error_div").find("div.alert-danger").html(resp_data.message);
						$("#chat_sent_type_form").removeClass('hide');
					}
				}
			});
        }
    });
}
*/

function auto_connect_chat(){
	$("#chat_preloader").hide();
	cCustomerName = c_name;
	var lt = /</g,
	   gt = />/g,
	   ap = /'/g,
	   ic = /"/g;
	cCustomerName = cCustomerName.toString().replace(lt, "").replace(gt, "").replace(ap, "").replace(ic, "");
	//console.log(cCustomerName);
	
	cCustomerEmail = "";
	cCustomerContact = c_number;
	cCustomerSubject = 'AA';
	cCustomerSubjectText = cob_subject;
	cCustomerLanguage = 'EN';
	cCustomerLanguageText = 'English';
	cCustomerVerify = c_verify;

	$.cookie("tempCustomerName", cCustomerName);
	// $.cookie("tempCustomerEmail", cCustomerEmail);
	$.cookie("tempCustomerContactNumber", cCustomerContact);
	$.cookie("tempCustomerSubject", cCustomerSubject);
	$.cookie("tempCustomerLanguage", cCustomerLanguage);
	$.cookie("tempcCustomerVerify", cCustomerVerify);

	$.cookie("cCustomerName", cCustomerName);
	// $.cookie("cCustomerEmail", cCustomerEmail);
	$.cookie("cCustomerContactNumber", cCustomerContact);
	$.cookie("cCustomerSubject", cCustomerSubject);
	$.cookie("cCustomerSubjectText", cCustomerSubjectText);
	$.cookie("cCustomerLanguage", cCustomerLanguage);
	$.cookie("cCustomerLanguageText", cCustomerLanguageText);
	$.cookie("cCustomerVerify", cCustomerVerify);

	$("#chat_body_preloader").remove();
	$("#chat_login_form").remove();
	console.log(obj);

	if(obj != null) {
		// ChatJoin("auto_connect_chat");
		obj.loadSettings('wss://'+srv_name+':'+socket_port+'/chat?token='+srv_token);
		obj.Connect();
	} else {
		try {
			obj = new gPlexCCChatWSApi();
			//var wshref = (window.location.protocol === 'https') ? 'wss' : 'ws';
			//wshref += '://' + window.location.host;
			//var port = $gchatBtn.data("port");
			//obj.loadSettings('ws://'+socket_ip+':'+socket_port+'/chat');
			obj.loadSettings('wss://'+srv_name+':'+socket_port+'/chat?token='+srv_token);
			//obj.setNumLoggedIn(0);
			obj.Connect();

			//close button show if aggent is available
			// $("#btnChatClose").removeClass('hide');
		} catch (ex) {
			alert(ex.message);
		}
	}
}

function lsChatJoinRequest(item, callId, webkey){
	$("#chat_preloader").hide();
	
	$.cookie("cCustomerName", $.cookie("tempCustomerName"));
	// $.cookie("cCustomerEmail", cCustomerEmail);
	$.cookie("cCustomerContactNumber", $.cookie("tempCustomerContactNumber"));
	$.cookie("cCustomerSubject", $.cookie("tempCustomerSubject"));
	$.cookie("cCustomerSubjectText", cob_subject);
	$.cookie("cCustomerLanguage", 'EN');
	$.cookie("cCustomerLanguageText", $.cookie("tempCustomerLanguageText"));
	$.cookie("cCustomerVerify", $.cookie("tempcCustomerVerify"));
	
	
	$("#chat_body_preloader").remove();
	$("#chat_login_form").remove();
	console.log(obj);

	try {
		obj = new gPlexCCChatWSApi();
		//var wshref = (window.location.protocol === 'https') ? 'wss' : 'ws';
		//wshref += '://' + window.location.host;
		//var port = $gchatBtn.data("port");
		//obj.loadSettings('ws://'+socket_ip+':'+socket_port+'/chat');
		obj.loadSettings('wss://'+srv_name+':'+socket_port+'/chat?token='+srv_token);
		//obj.setNumLoggedIn(0);
		obj.Connect();
		
		// setTimeout(function() {
		// 	ChatJoin("lsChatJoinRequest");
		// }, 500);
		
	} catch (ex) {
		alert(ex.message);
	}
}

function createCustomerChatWindow(left, top, width, height, html) {
	var selectedTheme = layout;
	var available = $.cookie("available_agent");
	var customerWindow = "<div id='g-chat-customer-window' class='container-fluid g-chat-customer-window gccw-"+selectedTheme+"'>";
	
	// header
	customerWindow += "<div class='row gccw-header'>";
	customerWindow += "<div class='col-xs-12 pl-10 pr-10'>";
	customerWindow += "<img class='img-thumbnail chat-logo' alt='logo' src='"+baseUrl+"/ccd/images/robi_logo.svg'>";
	customerWindow += "<label class='welcome-text'>Welcome to Robi Sheba</label>";
	// customerWindow += "<a class='btn btn-primary btn-xs pull-right hide' id='btnChatClose'><i class='glyphicon glyphicon-remove'></i></a>";
	customerWindow += "<div class='g-chat-notification-wrapper'>";
	customerWindow += "<div class='g-chat-notification-text'>End chat Here &amp; Give Feedback on Agent Service</div>";
	customerWindow += "<a class='btn btn-primary btn-xs' id='btnChatClose'><i class='glyphicon glyphicon-remove'></i></a>";
	customerWindow += "</div>";
	// customerWindow += "<a class='btn btn-return btn-xs pull-right' id='btnChatReturn'>Return</a>";
	// customerWindow += "<label class='greetings-text'>"+greetingsText()+"</label>";
	customerWindow += "</div>";
	// customerWindow += "<div class='col-xs-5'>";
	// customerWindow += "<a class='btn btn-primary btn-xs pull-right' id='btnChatClose'>Close</a>";
	// customerWindow += "<a class='btn btn-return btn-xs pull-right mr-10' id='btnChatReturn'>Return</a>";
	// customerWindow += "</div>";
	customerWindow += "</div>";

	// body
	customerWindow += "<div class='gccw-body'>";
	customerWindow += html.length>0 ? html : "";
	customerWindow += "</div>";

	// footer
	customerWindow += "<div class='gccw-footer'>";
	customerWindow += "<div class='checkbox-inline language-check'>";
  	customerWindow += "<input id='language_en' name='language_rd' type='radio' class='radio-language' value='EN' checked='checked' onclick='setLanguageText()' />";
  	customerWindow += "<label class='mr-10' for='language_en'>English</label>";
  	customerWindow += "<input id='language_bn' name='language_rd' type='radio' class='radio-language' value='BN' onclick='setLanguageText()' />";
  	customerWindow += "<label for='language_bn'>Bangla</label>";  
	customerWindow += "</div>"
	customerWindow += "<div><textarea id='chatTextInputBox' class='bangla' onfocus='setDefaultLanguage()'></textarea></div>";
	customerWindow += "<input id='btnSend' type='button' class='btn btn-default btn-send' value='Send'></div>";
	customerWindow += "</div>";

	
	customerWindow += "</div>";

	$("body").append(customerWindow);

	autosize(document.querySelectorAll('textarea'));
	//$(".g-chat").hide();

	//hide chat arae
	$(".gccw-footer").hide();	

	// close button
	closeChatSession();
	// return button
	returnChatSession();
	
	// if(available==0){
	// 	// leave text form
	// 	submitLeaveText();
	// 	return;
	// }
	if (office_status == 'close' || available==0) {
        submitLeaveText();
        return;
    }
	
	var subject = !$.cookie("tempCustomerSubject") ? "" : $.cookie("tempCustomerSubject");
	var language = !$.cookie("tempCustomerLanguage") ? "" : $.cookie("tempCustomerLanguage");
	$("#cCustomerSubject").val(subject);
	$("#cCustomerLanguage").val(language);

	var $box = $(".g-chat-customer-window");

	//dragable window
	$box.css({
		//"left":left+"px",
		//"top":top+"px",
		//"width":width,
		//"height":height
	})/*.draggable({
		containment:$('body'),
		handle:'.gccw-header',
		cursor: 'move',
		opacity:0.5,
		stop: handleDragStop
	}).resizable('destroy').resizable({
		minWidth: 300,
		minHeight: 66,
		maxWidth: 900,
		maxHeight: 700,
		stop: handleResizeStop
	});*/

	customerChatBox = {left:left, top:top, width:width, height:height};

	$.cookie("customerChatBox", JSON.stringify(customerChatBox));

	$(".g-chat-customer-window").click(function(){
		textBoxfocus = false;
		$box.find(".gccw-header").removeClass("highlight");
	});

	$(".g-chat-customer-window .gccw-footer textarea").keypress(function(e){
		if(conversationClosed) return false;

		textBoxfocus = false;
		$box.find(".gccw-header").removeClass("highlight");

		var k = e.keyCode ? e.which : e.keyCode;
		var it = this;
		
		//if(!textTypingMine) {
		var textTypingMineAt = new Date();
		//$.cookie("textTypingMineAt",textTypingMineAt);
		textTypingMine = true;
		
		var $typing = $("#gccw-typing");
		var $boxBody = $box.find(".gccw-body");
		sendChunkText($boxBody, $typing, ".");				
		//return;
		//}
		
		if(k==13) {
			KeyPressTrigger();
			return false;
		}
		
		return;
	});

	$("#btnSend").click(function(){
		KeyPressTrigger();
	});

    $(document).on("keypress", "#chatTextInputBox", function (e) {
        if (e.keyCode == 13 && e.shiftKey == 0) {
            KeyPressTrigger();
        }
    });

	
	$("#gcc-action-minimize").click(function(){
		$(".g-chat-customer-window").hide();
		$("#gCCCustomerChatButton").show();
	});

	$('.login-verify-user').on('click', function(){
		if($(this).is(':checked')){
			$('#verify_user-error').remove();
		}
	});
	// chat_login_form_initialize();
	
	var callIdToChat = !statusHtmlStorage(storageVars.callIdToChat) ? [] : getHtmlStorage(storageVars.callIdToChat);
	var chatConversation = !statusHtmlStorage(storageVars.chatConversation) ? {} : getHtmlStorage(storageVars.chatConversation);
	var chatRequestInfo = !statusHtmlStorage(storageVars.chatRequestInfo) ? {} : getHtmlStorage(storageVars.chatRequestInfo);
	var anyConnectFlag = false;
	
	if(callIdToChat.length > 0){
		callIdToChat.forEach(function(item){
			console.log(item);
			console.log(chatRequestInfo[item].obj);
			console.log(chatRequestInfo);
			console.log(chatRequestInfo[item]);
			if (item == $.cookie("cCustomerCallId") && chatRequestInfo[item].web_key == $.cookie("cCustomerWebKey")) {
				console.log("not WebChatItemNotFound");
				lsChatJoinRequest(item, $.cookie("cCustomerCallId"), $.cookie("cCustomerWebKey"));
				anyConnectFlag = true;
			}else{
				console.log("WebChatItemNotFound");
				WebChatItemNotFound(item);
			}
		});
	}else{
		callIdToChat = [];
		chatConversation = {};
		chatRequestInfo = {};
	
		setHtmlStorage(storageVars.callIdToChat, JSON.stringify(callIdToChat), 24*3600);
		setHtmlStorage(storageVars.chatConversation, JSON.stringify(chatConversation), 24*3600);
		setHtmlStorage(storageVars.chatRequestInfo, JSON.stringify(chatRequestInfo), 24*3600);
		
		if(ws_token_falg)
			auto_connect_chat();
	}
	
	if(!anyConnectFlag){
		callIdToChat = [];
		chatConversation = {};
		chatRequestInfo = {};
	
		setHtmlStorage(storageVars.callIdToChat, JSON.stringify(callIdToChat), 24*3600);
		setHtmlStorage(storageVars.chatConversation, JSON.stringify(chatConversation), 24*3600);
		setHtmlStorage(storageVars.chatRequestInfo, JSON.stringify(chatRequestInfo), 24*3600);
		
		if(ws_token_falg)
			auto_connect_chat();
	}
}


// loading area
function loading_html(){
	var html = "";
	html += '<div id="chat_body_preloader" class="chat-preloader text-center">';
	html += '<label><small>Please wait...<small></label>';
    html += '<img class="img-fluid" src="'+assetUrl+'images/5_34.gif" alt="preloader">';
    html += '</div>';

    return html;
}

function KeyPressTrigger() {
	var $inputBox = $("#chatTextInputBox");
	var message = $inputBox.val();
	message = $.trim(message);
	message = nl2br(message);
	var lt = /</g,
	   gt = />/g,
	   ap = /'/g,
	   ic = /"/g;
	message = message.toString().replace(lt, "").replace(gt, "").replace(ap, "").replace(ic, "");
	//console.log(message);
	
	if(message.length==0) return false;
	//if(unescape(encodeURIComponent(btoa(message))).length>128) return false;
	//if(message.length>256) return false;
	
	$inputBox.css('height','47px');
	$inputBox.val('').focus();
	
	var $box = $(".g-chat-customer-window");
	var $boxBody = $box.find(".gccw-body");
	var $typing = $("#gccw-typing");
	
	sendChunkText($boxBody, $typing, message);
	return;
	/*
	$inputBox.val("");
	//SendMsg(receiverId, message);
	$inputBox.css('height','47px');
	$inputBox.val('').focus();

	var msg = {
		from: $.cookie("cCustomerName"),
		name: $.cookie("cCustomerName"),
		msg:message,
		receiverId: agentId,
		receiverName: agentNick
	};
	var row = msgRow(msg,"L");

	var $boxBody = $box.find(".gccw-body");
	var $typing = $("#gccw-typing");
	if($typing.length<=0) $boxBody.append(row);
	else $typing.before(row);

	$boxBody.scrollTop($boxBody[0].scrollHeight);

	ChatText(btoa(message));

	return false;
	*/
}

function sendChunkText($boxBody, $typing, message) {
	
	//SendMsg(receiverId, message);
	//console.log(message);
	var msg = {
		from: $.cookie("cCustomerName"),
		name: $.cookie("cCustomerName"),
		msg:message,
		receiverId: agentId,
		receiverName: agentNick
	};
	var row = msgRow(msg,"L");

	if(message!=".") {
		if ($.cookie("customerMsgCount") != null) {
			customerMsgCount = $.cookie("customerMsgCount");
		}
		customerMsgCount++;
		$.cookie("customerMsgCount", customerMsgCount);
		if($typing.length<=0) $boxBody.append(row);
		else $typing.before(row);
	}
	

	$boxBody.scrollTop($boxBody[0].scrollHeight);
	
	
	
	message = b64e(message);
	var msgArray = message.match(/.{1,250}/g);
	var length = msgArray.length - 1;
	
	var timeStamp = Math.floor(Date.now() / 1000);
	
	
	for(i=0; i<=length; i++) {
		//sendText(receiverId, msgArray[i]+"|"+length+"|"+i);
		// console.log("Sending text: "+timeStamp+"|"+msgArray[i]+"|"+length+"|"+i);
		ChatText(b64e(timeStamp+"|"+msgArray[i]+"|"+length+"|"+i));
	}

	//ChatText(btoa(message));
}



function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function ChatJoin(reqFrom = '') {
    try {

			//currentRequestSCeq++;
			//var curTime = new Date();
			//RequestSession.push({"SCeq":currentRequestSCeq,"CHAT_JOIN":"","Time":curTime});

			ChatJoinRequestTime = new Date();
			//checkSession();

			$.ajax({
				type:"POST",
				url:baseUrl+"/chat_agent.php",
				data:{
					page_id:page_id,
					site_key:site_key,
					user:user,
					service:$.cookie("cCustomerSubject"),
				    name: $.cookie("cCustomerName"), 
				    email: '',
				    //$.cookie("cCustomerEmail"), 
				    contact: $.cookie("cCustomerContactNumber"),
				    subject: $.cookie("cCustomerSubjectText"), 
				    service_id: $.cookie("cCustomerSubject"), 
				    language: $.cookie("cCustomerLanguage"),
				    domain: domain, 
				    www_ip: www_ip,
				    url: web_site_url,
				    duration: user_arival_duration,
				    location: geoLocation
				    // , verify_user: $.cookie("cCustomerVerify")
				},
				dataType:"jsonp",
				success: function(resp) {
				console.log('resp.available');
				resp.available = parseInt(resp.available);
				//console.log(resp.available);
				if (resp.available>0) {

					//16-09-2020
					let log_data = [];
					log_data.push({"Request From": reqFrom});
					log_data.push({"Type": "BEFORE_CHAT_JOIN"});
					log_data.push({"Name": $.cookie("cCustomerName")});
					log_data.push({"Contact": $.cookie("cCustomerContactNumber")});
					log_data.push({"WebKey": $.cookie("cCustomerWebKey")});
					log_data.push({"CallId": $.cookie("cCustomerCallId")});
					log_data.push(resp);
					addLog(log_data);

					conversationClosed = false;
					logId = resp.logid;
					obj.ChatJoin({
						name: $.cookie("cCustomerName"),
						email: '',
						//$.cookie("cCustomerEmail"),
						contact: $.cookie("cCustomerContactNumber"),
						subject: $.cookie("cCustomerSubjectText"),
						service_id: $.cookie("cCustomerSubject"),
						language: $.cookie("cCustomerLanguage"),
						domain: domain,//$gchatBtn.data("domain"),
						page_id: page_id,//$gchatBtn.data("page_id"),
						site_key: site_key,//$gchatBtn.data("site_key"),
						www_ip: www_ip,//$gchatBtn.data("www_ip"),
						url: web_site_url,
						duration: user_arival_duration,
						web_key: !$.cookie("cCustomerWebKey") ? "" : $.cookie("cCustomerWebKey"),
                        call_id: !$.cookie("cCustomerCallId") ? "" : $.cookie("cCustomerCallId"),
						// verify_user: $.cookie("cCustomerVerify"),
						location: geoLocation//+"*"+web_site_url+"*"+user_arival_duration//$gchatBtn.data("location")
						//SCeq: currentRequestSCeq,
					});

				} else {
					$.cookie("available_agent", 0);
					//getForm();
					//joinToChat();
					//existingChatBoxes();
					var html = getForm();
					$("#g-chat-customer-window .gccw-body").html(html);
					//createCustomerChatWindow( 0, 0, 0, 0, html );
					//$("body").html(html);
					return;
				}
		}
	});
/*
		obj.ChatJoin({
			name: $.cookie("cCustomerName"),
			email: $.cookie("cCustomerEmail"),
			subject: $.cookie("cCustomerSubjectText"),
			service_id: $.cookie("cCustomerSubject"),
			language: $.cookie("cCustomerLanguage"),
			domain: domain,//$gchatBtn.data("domain"),
			page_id: page_id,//$gchatBtn.data("page_id"),
			site_key: site_key,//$gchatBtn.data("site_key"),
			www_ip: www_ip,//$gchatBtn.data("www_ip"),
			location: geoLocation+"*"+web_site_url+"*"+user_arival_duration//$gchatBtn.data("location")
			//SCeq: currentRequestSCeq,
		});
*/
	} catch (ex) {
		alert("Excpetion: " + ex.message);
	}
}

function checkSession() {
/*
	//for( i=0; i<RequestSession.length; i++ ) {
		//if(RequestSession[i] !== null && typeof RequestSession[i] === 'object') {
			var startTime = ChatJoinRequestTime;
			var endTime = new Date();
			var seconds = get_time_diff(startTime, endTime);

			if(seconds>requestTimeoutInterval) {

				//alert("Request Timeout");
				obj.Close();
				clearTimeout(timeout);
				return;
			}
		//}
	//}
	timeout = setTimeout(checkSession, 1000);*/
}

function checkIdleTime() {

	var startTime = cahtIdleTime;
	var endTime = new Date();
	var seconds = get_time_diff(startTime, endTime);

	if(seconds>chatIdleInterval) {

		//alert("Request Timeout");
		obj.ChatClose();
		clearTimeout(idleTimeout);
		return;
	}

	timeout = setTimeout(checkIdleTime, 1000);
}

function get_time_diff(startTime, endTime)
{

	var seconds = (endTime.getTime() - startTime.getTime())/1000;

	return seconds;

}

function ChatText(msg) {
	try {

		obj.ChatText( msg );

	} catch (ex) {
		alert("Exception: " + ex.message);
	}
}

function ChatClose(disc_party) {
	try {
		conversationClosedNotification(disc_party);
		obj.ChatClose();

	} catch (ex) {
		alert("Exception: " + ex.message);
	}
}

function clearCookie() {
	$.cookie("cCustomerWebKey",null);
	$.cookie("cCustomerCallId",null);
	$.cookie("cCustomerName",null);
	$.cookie("cCustomerEmail",null);
	$.cookie("cCustomerSubject",null);
	$.cookie("cCustomerLanguage",null);
	$.cookie("cCustomerVerify",null);
}

function welcomeMessageToCustomer(msg, isWecomeMsg) {
	isChatInIdle = true;
	cahtIdleTime = new Date();

	var $box = $(".g-chat-customer-window");
	$("#g-chat-customer-window .gccw-header .gccw-name").html(msg);
	console.log(isWecomeMsg);

	if(isWecomeMsg) {		
		window.clearInterval(checkQueueInterval);
		checkQueueInterval = null;
		$("#chat_queue_msg").remove();
		$("#queue_body_preloader").remove();
		user_in_queue = false;
		chat_accept = true;
		
		$("a#btnChatClose").removeClass('hide');
		$("a#btnChatReturn").addClass('hide');
				
		// submit customer in queue
		$.ajax({
			type:"post",
			url:baseUrl+"/update_chat_queue.php",
			data:{
				cCustomerCallId: $.cookie("cCustomerCallId"),
				cCustomerContactNumber: $.cookie("cCustomerContactNumber"),
				cCustomerVerify: $.cookie("cCustomerVerify"),
				page_id: page_id,
				site_key: site_key,
				user: user
			},
			dataType:"text",
			contentType: 'application/x-www-form-urlencoded',
			success:function(resp) {
				var resp_data = JSON.parse(resp);
				// console.log(resp_data);
			}
		});
		
		$(".gccw-footer").show();
		var $boxBody = $box.find(".gccw-body");
		greeting_html = '';
		greeting_html += "<div class='row'>";
		greeting_html += "<div class='col-md-12'>";
		// greeting_html += "<label class='greetings-text'>"+greetingsText()+"!!!</label>";
		greeting_html += "</div>";
		greeting_html += "</div>";
		$boxBody.html(greeting_html);

		console.log('history: ');
		console.log(showWebchatHistoryFlag);
		if(showWebchatHistoryFlag){
			chatLandingTime = $.cookie("chatLandingTime");
			chatLandingTime = new Date(chatLandingTime);
			showWebChatHistory($.cookie("cCustomerCallId"));
		}else{
			chatLandingTime = new Date();
			$.cookie("chatLandingTime", chatLandingTime);
			setTimeout(function() {
				var msg = {
					from: agentId,
					name: agentNick,
					msg:"Welcome to Robi Live Chat Service. To ensure you receive the best service, our customer service agents are now available 24 hours! ",
					//msg:"আমার নাম " + $.cookie("cChatAgentName"),
					receiverId: $.cookie("cCustomerName"),
					receiverName: $.cookie("cCustomerName")
				};

				var row = msgRow(msg,"R");
				var $boxBody = $box.find(".gccw-body");
				$boxBody.append(row);
				$boxBody.scrollTop($boxBody[0].scrollHeight);
			}, 1500);

			setTimeout(function() {
				var msg = {
					from: agentId,
					name: agentNick,
					msg: "This is "+ $.cookie("cChatAgentName") +" from Robi Livechat, How may I help you? ",
					//msg:"আমার নাম " + $.cookie("cChatAgentName"),
					receiverId: $.cookie("cCustomerName"),
					receiverName: $.cookie("cCustomerName")
				};

				var row = msgRow(msg,"R");
				var $boxBody = $box.find(".gccw-body");
				$boxBody.append(row);
				$boxBody.scrollTop($boxBody[0].scrollHeight);
			}, 3000);
		}
	} else {
		// There are submit to queue code
		// submit customer in queue
		$.ajax({
			type:"post",
			url:baseUrl+"/insert_chat_queue.php",
			data:{
				cCustomerCallId: $.cookie("cCustomerCallId"), 
				cCustomerName: $.cookie("cCustomerName"),
				cCustomerEmail: '',
				//$.cookie("cCustomerEmail"),
				cCustomerContactNumber: $.cookie("cCustomerContactNumber"),
				cCustomerSubject: $.cookie("cCustomerSubject"),
				cCustomerVerify: $.cookie("cCustomerVerify"),
				page_id: page_id,
				site_key: site_key,
				user: user
			},
			dataType:"text",
			contentType: 'application/x-www-form-urlencoded',
			success:function(resp) {
				$("a#btnChatClose").removeClass('hide');
				$("a#btnChatReturn").addClass('hide');

				//console.log(resp);
				var resp_data = JSON.parse(resp);
				// console.log(resp_data);

				if(chat_accept==false){
					user_in_queue = true;
					var $boxBody = $box.find(".gccw-body");
					var body_html = '';
					body_html += "<div class='col-md-12'>";
					// body_html += "<label class='greetings-text'>"+greetingsText()+"!!!</label>";
					body_html += "</div>";

					body_html += "<div id='chat_queue_msg' class='col-md-12' >"+greetingsText()+"!<br>"+
									chat_queue_text +" </div>";


					body_html += "<div class='col-lg-1 col-md-2 col-sm-6 col-xs-6'>";
					body_html += "<a href='"+ playstore_link +"' target='_blank'>";
					body_html += "<img class='img-thumbnail' src='"+baseUrl+"/ccd/images/android.svg' alt='preloader'>";
					body_html += "</a>";
					body_html += "</div>";

					body_html += "<div class='col-lg-1 col-md-2 col-sm-6 col-xs-6'>";
					body_html += "<a href='"+ appstore_link +"' target='_blank'>";
					body_html += "<img class='img-thumbnail' src='"+baseUrl+"/ccd/images/apple.svg' alt='IOS'>";
					body_html += "</a>";
					body_html += "</div>";

					body_html += "<div id='queue_body_preloader' class='chat-preloader text-center' style='margin-top:235px;min-height:20px; height: 80px;' >";
					body_html += "<label style='margin-top:5px;'><small>Please wait...<small></label>";
				    body_html += "<img class='img-fluid' src='"+assetUrl+"images/30_gray.gif' alt='preloader'>";
				    body_html += "</div>";

					$boxBody.html(body_html);
					checkQueueInterval = setInterval(checkQueue,10000);
				}
			}
		});
	}

}

(function() {
	setTimeout(jssdk,2000);
})();


var textReceivedArray = new Array();
var currentMsgTimestamp=0;
function msgReceived(resp) {
	//console.log(resp);
	var msg = b64d(resp.message);//message|total_chunk|chunk_number
	//console.log(msg);
	var msgAry = msg.split("|");
	if(currentMsgTimestamp != msgAry[0]) {
		textReceivedArray = new Array();
	}
	currentMsgTimestamp = msgAry[0];
	var chunk_number = parseInt(msgAry[3]);
	textReceivedArray[chunk_number] = msgAry[1];//chunk_number
	var length = textReceivedArray.length-1;
	if( parseInt(msgAry[2]) == parseInt(msgAry[3]) && length == parseInt(msgAry[3]) ) {
		var msg = textReceivedArray.join('');
		msgShowInWindow(resp, b64d(msg));
		hasAgentResponded = true;
		$.cookie("hasAgentResponded", hasAgentResponded);
	}
	//console.log(textReceivedArray);
}

function msgShowInWindow(resp, message) {
	
	var senderId = resp.web_key;
	var senderNick = "Agent";//$.cookie("cCustomerName");
	//$("#g-chat-customer-window .gccw-header .gccw-name").html("&nbsp;"+senderNick);
	
	if(message==".") {
		textTypingAt = new Date();
		$.cookie("textTypingAt",textTypingAt);
		chatWriting();
		var $box = $("#g-chat-customer-window");
		$boxBody = $box.find(".gccw-body");
		$boxBody.scrollTop($boxBody[0].scrollHeight);
		return;
	}

	textBoxfocus = true;
	if ($("#g-chat-customer-window").length <= 0) {

		//createChatBox( senderId, senderNick, "", "", 300, 380, maxZIndex );
		createCustomerChatWindow("", "", 300, 378, "");
	}

	if(!$("#g-chat-customer-window textarea").is(':focus')) {
		$('#chatAudio')[0].play();
	}

	/*
	if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
		$("#chatbox_"+chatboxtitle).css('display','block');
		restructureChatBoxes();
	}
	*/
	//newMessages[chatboxtitle] = true;
	//newMessagesWin[chatboxtitle] = true;
	//nameArray[chatboxtitle] = item.name;
	var item = { from:senderId, name:senderNick, msg:message };
	var row = msgRow(item,"R");
	$("#gccw-typing").remove();



	var $box = $("#g-chat-customer-window");
	$boxBody = $box.find(".gccw-body");
	$boxBody.append(row);
	$boxBody.scrollTop($boxBody[0].scrollHeight);
	//$box.find("textarea").blur();
	
	textReceivedArray = new Array();
}

function msgRow(msg, direction) {

	cahtIdleTime = new Date();

	var time = new Date();
	var HH = parseInt(time.getHours());
	var ampm = HH>12 ? "pm" : "am";
	var hh = HH>12 ? HH - 12 : HH;

	var timestmp = ("0" + hh).slice(-2) + ":" + ("0" + time.getMinutes()).slice(-2) + ":" + ("0" + time.getSeconds()).slice(-2) + " " + ampm;
	var row = "";

	console.log(typeof msg.timestamp);
	if(typeof msg.timestamp == 'undefined')
		addMessageInStorage(msg, timestmp, direction);

	if(direction=="L") {
		row = "<div class='msg-row'>"+
				"<div class='caption-left'>"+msg.name+" --- <span class='timestmp'>"+timestmp+"</span></div>"+
				// "<div class='caption-left'>"+msg.name+"</div>"+
				"<span class='pull-left'><img src='"+baseUrl+"/ccd/image/chat_customer.png' class='img-circle' alt='agent' width='30' height='30'> </span>"+
				"<span class='msg-item left' style='margin-left:5px;'>"+urlify(msg.msg)+"</span>"+
				"<div class='clear'></div>"+
			"</div>";
	} else {
		msg.msg = String(msg.msg).replace(/<space>/g, '&lt;space&gt;');
		row = "<div class='msg-row'>"+
				// "<div class='caption-right'><span class='timestmp'>"+timestmp+"</span> --- "+$.cookie("cChatAgentName")+"</div>"+
				"<div class='caption-right'><span class='timestmp'>"+timestmp+"</span> </div>"+
				"<span class='pull-right' style='background: red;border-radius: 50%;padding: 5px;'><img src='"+baseUrl+"/ccd/images/5G.svg' class='img-circle' alt='agent' width='30' height='30'> </span>"+
				"<span class='msg-item right' style='margin-right:5px;'>"+urlify(msg.msg)+"</span>"+
				"<div class='clear'></div>"+
			"</div>";
	}
	return row;
}
/*
function SendMsg(from, msg) {
	try {
		if (obj) {
			obj.SendMessage(from, msg);
		} else {
			alert("Object is not created!");
		}
	} catch (ex) {
		alert("Warning12: " + ex.message);
	}
}
*/
function loadjscssfile(filename, filetype){
    if (filetype=="js"){ //if filename is a external JavaScript file
        var fileref=document.createElement('script')
        fileref.setAttribute("type","text/javascript")
        fileref.setAttribute("src", filename)
    }
    else if (filetype=="css"){ //if filename is an external CSS file
        var fileref=document.createElement("link")
        fileref.setAttribute("rel", "stylesheet")
        fileref.setAttribute("type", "text/css")
        fileref.setAttribute("href", filename)
    }
    if (typeof fileref!="undefined")
        document.getElementsByTagName("head")[0].appendChild(fileref)
}

function handleDragStop( event, ui ) {
	var $box = $('#g-chat-customer-window');

	var offsetXPos = parseInt( ui.offset.left );
	var offsetYPos = parseInt( ui.offset.top );

	customerChatBox.left = offsetXPos;
	customerChatBox.top = offsetYPos;

	$.cookie("customerChatBox", JSON.stringify(customerChatBox));

}

function handleResizeStop( event, ui ) {
	var $box = $('#g-chat-customer-window');

	customerChatBox.width = parseInt( ui.size.width );
	customerChatBox.height = parseInt( ui.size.height );

	$.cookie("customerChatBox", JSON.stringify(customerChatBox));
}

function existingChatBoxes()
{
	//var ccb = $.cookie("customerChatBox");
	customerChatBox = JSON.parse($.cookie("customerChatBox"));
	if(customerChatBox!=null && parseInt(customerChatBox.width)>0) {
		var html = getForm();
		createCustomerChatWindow( customerChatBox.left, customerChatBox.top, customerChatBox.width, customerChatBox.height, html );
	}
	countUpTextTypingTime();
}

function isValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
}

function chatWriting() {
	$typingIndicator = $("#gccw-typing");
	if($typingIndicator.length<=0) {
		var $box = $("#g-chat-customer-window .gccw-body");
		row = "<div class='msg-row'>\
				<div class='caption-right'><span class='timestmp'> &nbsp; </div>\
				<span class='msg-item right'><img src='ccd/typing.gif' height='8' style='margin:3px;'></span>\
				<div class='clear'></div>\
			</div>";
		$box.append("<div id='gccw-typing'>"+row+"</div>");
	}
}

function conversationClosedNotification(disc_party) {
	conversationClosed = true;
	$indicator = $("#gccw-closed-conversation");
	if($indicator.length<=0) {
		var $box = $("#g-chat-customer-window .gccw-body");
		if(disc_party=='S')
			$box.append("<div id='gccw-closed-conversation'>Conversation closed by System</div>");
		else {
			$box.append("<div id='gccw-closed-conversation'>Conversation closed by Agent</div>");
			$box.scrollTop($box[0].scrollHeight);
			setTimeout("closeChatSessionFromWss()",2000);
		}
		
		$box.scrollTop($box[0].scrollHeight);
	}
}

function countUpTextTypingTime() {
	//return;
	//for(user_id in chatBoxes) {

		var endTime = new Date();

		var $box = $(".g-chat-customer-window");
		//$box.find(".gcaw-idle").html(" (Idle: "+minutes+":"+seconds+")");

		var textTypingAt = new Date($.cookie("textTypingAt"));
		if(textTypingAt!=null)
		{
			seconds = get_time_diff(textTypingAt, endTime);
			var $typingIndicator = $("#gccw-typing");
			if(seconds>7 && $typingIndicator.length>0) {
				$typingIndicator.remove();
				textTypingAt = new Date();
				textTyping = false;
				$.cookie("textTypingAt",textTypingAt);
			}
		}
		
		//var textTypingMineAt = new Date($.cookie("textTypingMineAt"));
		if(textTypingMineAt!=null) {
			seconds = get_time_diff(textTypingMineAt, endTime);
			if(seconds>5) {
				textTypingMineAt = new Date();
				textTypingMine = false;
			}
		}

		if(textBoxfocus) {
			$box.find(".gccw-header").toggleClass("highlight");
		}
	//}


	setTimeout("countUpTextTypingTime()",1000);
}


function NatPing()
{
	console.log("Pinging...");
	var data = b64e(JSON.stringify({isTyping:true}));
	obj.Reg(data);
	natPingInterval = setTimeout(NatPing,20000);
}

function checkQueue(){
	//console.log("Queue...");
    
    if(chat_accept){
    	window.clearInterval(checkQueueInterval);
		checkQueueInterval = null;
		$("#chat_queue_msg").remove();
		$("#queue_body_preloader").remove();
		user_in_queue = false;

		return;
    }

	// check queue number
	$.ajax({
		type:"post",
		url:baseUrl+"/check_chat_queue.php",
		data:{
			cCustomerCallId: $.cookie("cCustomerCallId"),
			page_id: page_id,
			site_key: site_key,
			user: user
		},
		dataType:"text",
		contentType: 'application/x-www-form-urlencoded',
		success:function(resp) {
			var resp_data = JSON.parse(resp);
			// console.log(resp_data);

			var $boxBody = $("#g-chat-customer-window").find(".gccw-body");
			body_html = '';
			body_html += "<div class='col-md-12'>";
			// body_html += "<label class='greetings-text'>"+greetingsText()+"!!!</label>";
			body_html += "</div>";
			
            body_html += "<div id='chat_queue_msg' class='col-md-12' >"+greetingsText()+"!<br>"+
							chat_queue_text +" </div>";


			body_html += "<div class='col-lg-1 col-md-2 col-sm-6 col-xs-6'>";
			body_html += "<a href='"+ playstore_link +"' target='_blank'>";
			body_html += "<img class='img-thumbnail' src='"+baseUrl+"/ccd/images/android.svg' alt='preloader'>";
			body_html += "</a>";
			body_html += "</div>";

			body_html += "<div class='col-lg-1 col-md-2 col-sm-6 col-xs-6'>";
			body_html += "<a href='"+ appstore_link +"' target='_blank'>";
			body_html += "<img class='img-thumbnail' src='"+baseUrl+"/ccd/images/apple.svg' alt='IOS'>";
			body_html += "</a>";
			body_html += "</div>";

			body_html += "<div id='queue_body_preloader' class='chat-preloader text-center' style='margin-top:235px;min-height:20px; height: 80px;' >";
			body_html += "<label style='margin-top:5px;'><small>Please wait...<small></label>";
		    body_html += "<img class='img-fluid' src='"+assetUrl+"images/30_gray.gif' alt='preloader'>";
		    body_html += "</div>";

			$boxBody.html(body_html);
		}
	});
}

function enableCookie()
{
	jQuery.cookie = function(name, value, options) {
		if (typeof value != 'undefined') { // name and value given, set cookie
			options = options || {};
			if (value === null) {
				value = '';
				options.expires = -1;
			}
			var expires = '';
			if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
				var date;
				if (typeof options.expires == 'number') {
					date = new Date();
					date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
				} else {
					date = options.expires;
				}
				expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
			}
			// CAUTION: Needed to parenthesize options.path and options.domain
			// in the following expressions, otherwise they evaluate to undefined
			// in the packed version for some reason...
			var path = options.path ? '; path=' + (options.path) : '';
			var domain = options.domain ? '; domain=' + (options.domain) : '';
			var secure = options.secure ? '; secure' : '';
			document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
		} else { // only name given, get cookie
			var cookieValue = null;
			if (document.cookie && document.cookie != '') {
				var cookies = document.cookie.split(';');
				for (var i = 0; i < cookies.length; i++) {
					var cookie = jQuery.trim(cookies[i]);
					// Does this cookie string begin with the name we want?
					if (cookie.substring(0, name.length + 1) == (name + '=')) {
						cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
						break;
					}
				}
			}
			return cookieValue;
		}
	};
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function urlify(text) {
    var urlRegex = /(https?:\/\/[^\s]+)/g;
    //console.log(urlRegex);
    var url_data = text.replace(urlRegex, function(url) {
        return '<a href="' + url + '" target="_blank">' + url + '</a>';
    })
    //console.log(url_data);

    if(url_data){
    	return url_data;
    }else{
    	urlRegex = /(http?:\/\/[^\s]+)/g;
    	return url_data = text.replace(urlRegex, function(url) {
        	return '<a href="' + url + '" target="_blank">' + url + '</a>';
    	})
    }
    // or alternatively
    // return text.replace(urlRegex, '<a href="$1">$1</a>')
}

function showRatingView(cid)
{
	$("a#btnChatClose").addClass('hide');
	$("a#btnChatReturn").removeClass('hide');

	var ratingWindow = "";
	ratingWindow += "<div id='g-chat-customer-window' class='container-fluid g-chat-customer-window gccw-"+layout+"'>";
	
	// header
	ratingWindow += "<div class='row gccw-header'>";
	ratingWindow += "<div class='col-xs-12 pl-10 pr-10'>";
	ratingWindow += "<img class='img-thumbnail chat-logo' alt='logo' src='"+baseUrl+"/ccd/images/robi_logo.svg'>";
	ratingWindow += "<label class='welcome-text'>Welcome to Robi Sheba</label>";
	// ratingWindow += "<a class='btn btn-return btn-xs pull-right mr-10' id='btnChatReturn'>Return</a>";
	ratingWindow += "</div>";
	// ratingWindow += "<div class='col-xs-5'>";
	// ratingWindow += "<a class='btn btn-return btn-xs pull-right mr-10' id='btnChatReturn' onclick='returnChatSession()'>Return</a>";
	// ratingWindow += "</div>";
	ratingWindow += "</div>";

	// body
	ratingWindow += "<div class='gccw-body'>";
	ratingWindow += "<div class='row' align='center'>";
	// ratingWindow += "<label class='col-md-12 greetings-text'>"+greetingsText()+"!!!</label>";

    if(document.getElementById("language_en").checked){
        //ratingWindow += "<div class='col-md-12' id='rateYo_caption'>Are you satisfied with the live chat service given by the agent?</div>";
        ratingWindow += "<div class='col-md-12' id='rateYo_caption'>প্রিয় গ্রাহক, রবি Webchat থেকে পাওয়া এজেন্ট এর সেবাটি ভালো লাগলে Yes, ভালো না লাগলে No সিলেক্ট করুন</div>";
        ratingWindow += "<div id='rating' class='col-md-12' style='height:65px; font-weight:bold; color:#bbbbbb; margin-top: 5px;'>";
        ratingWindow += "<div class='col-xs-6'><img id='user_happy' class='img-circle pull-right' src='"+baseUrl+"/ccd/images/happy1.png' height='36' width='36'></div>";
        ratingWindow += "<div class='col-xs-6'><img id='user_sad' class='img-circle pull-left' src='"+baseUrl+"/ccd/images/sad_no_tear.png' height='36' width='36'></div>";
        ratingWindow += "<div class='col-xs-6'><input id='satisfied_user_yes' name='satisfied_user' type='radio' class='login-verify-user' value='Y' checked='checked' ><label class='mr-10 pull-right satisfied-user-yes' for='satisfied_user_yes' style='color: black' >Yes</label></div>";
        ratingWindow += "<div class='col-xs-6'><input id='satisfied_user_no' name='satisfied_user' type='radio' class='login-verify-user' value='N'><label class='mr-10 pull-left satisfied-user-no' for='satisfied_user_no' style='color: black' >No</label></div>";
        ratingWindow += "</div>";
    }else if(document.getElementById("language_bn").checked){
        ratingWindow += "<div class='col-md-12' id='rateYo_caption'>প্রিয় গ্রাহক, রবি Webchat থেকে পাওয়া এজেন্ট এর সেবাটি ভালো লাগলে Yes, ভালো না লাগলে No সিলেক্ট করুন</div>";
        ratingWindow += "<div id='rating' class='col-md-12' style='height:65px; font-weight:bold; color:#bbbbbb; margin-top: 5px;'>";
        ratingWindow += "<div class='col-xs-6'><img id='user_happy' class='img-circle pull-right' src='"+baseUrl+"/ccd/images/happy1.png' height='36' width='36'></div>";
        ratingWindow += "<div class='col-xs-6'><img id='user_sad' class='img-circle pull-left' src='"+baseUrl+"/ccd/images/sad_no_tear.png' height='36' width='36'></div>";
        ratingWindow += "<div class='col-xs-6'><input id='satisfied_user_yes' name='satisfied_user' type='radio' class='login-verify-user' value='Y' checked='checked' ><label class='mr-10 pull-right satisfied-user-yes' for='satisfied_user_yes' style='color: black' >হ্যা</label></div>";
        ratingWindow += "<div class='col-xs-6'><input id='satisfied_user_no' name='satisfied_user' type='radio' class='login-verify-user' value='N'><label class='mr-10 pull-left satisfied-user-no' for='satisfied_user_no' style='color: black' >না</label></div>";
        ratingWindow += "</div>";
    }

	// ratingWindow += "<div class='col-md-12 center'><div id='rateYo'></div></div>";
	// ratingWindow += "<div id='rating' class='col-md-12' style='height:36px; font-weight:bold; color:#bbbbbb; margin-top: 5px;'>&nbsp;</div>";

    ratingWindow += "<div class='row' >";
    ratingWindow += "<div class='col-md-12' >";

	ratingWindow += "<div id='rateYo_button' style='padding-top:45px; padding-bottom:30px; padding-left: 13px'>";
    ratingWindow += "<a class='btn btn-success' id='sendFeedback' >OK</a> &nbsp;&nbsp;";
	// ratingWindow += "<a class='btn btn-danger' id='cancelFeedback' onclick='window.close()'>Cancel</a>";
	// ratingWindow += "</div>";
	ratingWindow += "</div>";
	ratingWindow += "</div>";

	ratingWindow += "</div>";
    ratingWindow += "</div>";
    ratingWindow += "</div>";
	
	$('body').html(ratingWindow);

	/*var loadYoRating = function() {
		$("#rateYo").rateYo({
			rating: 0,
			fullStar: true,
			multiColor: true,
			onSet: function (rating, rateYoInstance) {
				$("#rateYo_caption").html("Thank you for rating.");
				$("#rateYo").html("");
				$("#rateYo_button").html("");
				$("#rating").html("&nbsp;");
				CloseChatWindow(rating, cid);
			}
		}).on("rateyo.change", function (e, data) {
			var rating = data.rating;
			if (rating == 1) rating = "Very Bad";
			else if (rating == 2) rating = "Bad";
			else if (rating == 3) rating = "Normal";
			else if (rating == 4) rating = "Good";
			else if (rating == 5) rating = "Very Good";
			else rating = "";
			$("#rating").text(rating);
		});
	};*/
    var loadYoRating = function() {
        $("#sendFeedback").click(function(){
            $("#rateYo_caption").html(ice_feedback_msg);
            $("#user_happy,#user_sad,#satisfied_user_yes,#satisfied_user_no,#sendFeedback,#cancelFeedback").hide();

            $("input").hide();
            $("label").hide();

            var rating = $("input[name='satisfied_user']:checked").val();
            if (rating) {
                if (rating == 'Y') {
                    rating = 5;
                }
                else if (rating == 'N') {
                    rating = 0;
                }
                CloseChatWindow(rating, cid);
            }
        });

        $("a.sendFeedback-by-link").click(function(){
            $("#rateYo_caption").html(ice_feedback_msg);
            $("#user_happy,#user_sad,#satisfied_user_yes,#satisfied_user_no,#sendFeedback,#cancelFeedback").hide();

            $("input").hide();
            $("label").hide();

            var rating = $(this).attr('data-rating');
            if (rating) {
                if (rating == 'Y') {
                    rating = 5;
                } else if (rating == 'N') {
                    rating = 0;
                }else{
                	rating = '';
                }
                CloseChatWindow(rating, cid);
            }
        });
    }
	 setTimeout(loadYoRating, 200);
}

function CloseChatWindow(rating, cid)
{

	/*
	type:"POST",
		url:baseUrl+"/update_chat_feedback.php",
		data:{
			chat_feedback:rating,
			call_id:cid,
            page_id: page_id,
            site_key: site_key,
            user: user
		},
	 */

	$.ajax({
		type:"GET",
		url:baseUrl+"/chat.php",
		data:{chat_rating:rating,call_id:cid,user:user},
		dataType:"jsonp",
		success:function(resp){
			
		}
	});

	setTimeout("window.close()",2000);
}

function b64e(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode('0x' + p1);
    }));
}

function b64d(str) {
    return decodeURIComponent(Array.prototype.map.call(atob(str), function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
}

/*
function checkServerTime() {
    $.ajax({
        type: "GET",
        url: baseUrl + "/server_time.php",
        data: {
            page_id: page_id,
            site_key: site_key,
            user: user,
			offtime_from: offTimeFrom,
			offtime_to: offTimeTo
        },
        dataType: "json",
        success: function (resp) {
            console.log("status:" + resp.message);
            // console.log(window.opener.parent)
            setOfficeStatus(resp.message)
        }
    });
}

function setOfficeStatus(status) {
    officeStatus = status;
}*/
function showThankYou() {
	var ratingWindow = "";
	ratingWindow += "<div id='g-chat-customer-window' class='container-fluid g-chat-customer-window gccw-"+layout+"'>";
	// header
	ratingWindow += "<div class='row gccw-header'>";
	ratingWindow += "<div class='col-xs-12 pl-10 pr-10'>";
	ratingWindow += "<img class='img-thumbnail chat-logo' alt='logo' src='"+baseUrl+"/ccd/images/robi_logo.svg'>";

	ratingWindow += "<label class='welcome-text'>Welcome to Robi Sheba</label>";
	ratingWindow += "</div>";
	ratingWindow += "</div>";

	// body
	ratingWindow += "<div class='gccw-body'>";
	ratingWindow += "<div class='row' align='center'>";
	ratingWindow += "<div class='col-md-12' id='rateYo_caption'></div>";


	ratingWindow += "<div class='row' >";
	ratingWindow += "<div class='col-md-12' >";

	ratingWindow += "<div id='rateYo_button' style='padding-top:45px; padding-bottom:30px; padding-left: 13px'>";
	ratingWindow += "</div>";
	ratingWindow += "</div>";

	ratingWindow += "</div>";
	ratingWindow += "</div>";
	ratingWindow += "</div>";

	$('body').html(ratingWindow);
	$("#rateYo_caption").html(ice_feedback_msg_for_blank_webchat);
	$("input").hide();
	$("label").hide();
	CloseChatWindow(null , null);
}

function show_chat_leave_text_form(){
	//console.log("show_chat_leave_text_form");
	try_timeout = null;
	clearInterval(try_timeout);
	$("#chat_preloader").show();
	$("#g-chat-customer-window").remove();
	WebChatItemNotFound($.cookie("cCustomerCallId"));
	
	var html = chat_leave_text_form();	
	var selectedTheme = layout;
	var available = $.cookie("available_agent");
	var customerWindow = "<div id='g-chat-customer-window' class='container-fluid g-chat-customer-window gccw-"+selectedTheme+"'>";
	
	// header
	customerWindow += "<div class='row gccw-header'>";
	customerWindow += "<div class='col-xs-12 pl-10 pr-10'>";
	customerWindow += "<img class='img-thumbnail chat-logo' alt='logo' src='"+baseUrl+"/ccd/images/robi_logo.svg'>";
	customerWindow += "<label class='welcome-text'>Welcome to Robi Sheba</label>";
	customerWindow += "<a class='btn btn-primary btn-xs pull-right hide' id='btnChatClose'><i class='glyphicon glyphicon-remove'></i></a>";
	// customerWindow += "<a class='btn btn-return btn-xs pull-right' id='btnChatReturn'>Return</a>";
	// customerWindow += "<label class='greetings-text'>"+greetingsText()+"</label>";
	customerWindow += "</div>";
	// customerWindow += "<div class='col-xs-5'>";
	// customerWindow += "<a class='btn btn-primary btn-xs pull-right' id='btnChatClose'>Close</a>";
	// customerWindow += "<a class='btn btn-return btn-xs pull-right mr-10' id='btnChatReturn'>Return</a>";
	// customerWindow += "</div>";
	customerWindow += "</div>";

	// body
	customerWindow += "<div class='gccw-body'>";
	customerWindow += html.length>0 ? html : "";
	customerWindow += "</div>";

	// footer
	customerWindow += "<div class='gccw-footer'>";
	customerWindow += "<div class='checkbox-inline language-check'>";
  	customerWindow += "<input id='language_en' name='language_rd' type='radio' class='radio-language' value='EN' checked='checked' onclick='setLanguageText()' />";
  	customerWindow += "<label class='mr-10' for='language_en'>English</label>";
  	customerWindow += "<input id='language_bn' name='language_rd' type='radio' class='radio-language' value='BN' onclick='setLanguageText()' />";
  	customerWindow += "<label for='language_bn'>Bangla</label>";  
	customerWindow += "</div>"
	customerWindow += "<div><textarea id='chatTextInputBox' class='bangla' onfocus='setDefaultLanguage()'></textarea></div>";
	customerWindow += "<input id='btnSend' type='button' class='btn btn-default btn-send' value='Send'></div>";
	customerWindow += "</div>";
	
	customerWindow += "</div>";

	$("body").append(customerWindow);
	autosize(document.querySelectorAll('textarea'));
	//$(".g-chat").hide();

	//hide chat arae
	$(".gccw-footer").hide();	
	// close button
	closeChatSession();
	// return button
	returnChatSession();
	
	submitLeaveText();
	$("#chat_preloader").hide();
	
	return;
}

/*
	 * log chat data
	 * 16-09-2020
	 */
var addLog = async (...args) => {
	args = JSON.stringify(args);
	await $.ajax({
		type: "POST",
		url:baseUrl+"/accept_join_log.php",
		data: {args: args},
		dataType: "json",
		success:function(resp) {}
	});
};

