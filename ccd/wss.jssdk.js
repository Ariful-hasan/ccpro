var agentId = "1015";
var agentNick = "Mr. X";

var obj = null;
var customerChatBox = {};
var $gchatBtn;
var chat_web_key;
var chat_call_id;

var cCustomerName = null;
var cCustomerEmail = null;
var cCustomerSubject = null;
var cCustomerSubjectText = null;
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

/*
//var account = document.getElementById("gchat-button").getAttribute("data-user");
if(is_rnd==1) {
    baseUrl = "http://72.48.199.126/cloudcc/";
    //baseUrl = "http://192.168.10.67/cc/";
} else {
    baseUrl = "http://ccportal.gplex.com/";
}*/

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
		if(c_w_k!=null && c_w_k.lenght>0) ChatClose();
		else if(obj!=null) obj.ChatClose();
		clearCookie();
		$.cookie("customerChatBox","{}");

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
	existingChatBoxes();
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
	var email = !$.cookie("tempCustomerEmail") ? "" : $.cookie("tempCustomerEmail");
	var contactNumber = !$.cookie("tempCustomerContactNumber") ? "" : $.cookie("tempCustomerContactNumber");

	var available = $.cookie("available_agent");
	if(available==0) {
		var html = "<div class='form-group'>";
		html += "<div class='col-md-12 control-label' for='cCustomerName'>No agent available to chat right now. <br>Please call to customer care.</div>";
		html += "</div>";

		return html;
	}

	var html = "<div class='form-group'>";
	html += "<div class='col-md-12 control-label' for='cCustomerName'>Welcome to our LiveChat! Please fill in the form below to chat</div>";
	html += "</div>";
	html += "<div class='form-group'>";
	html += "<div class='col-md-12 control-label' for='cCustomerName' id='gccRequiredFieldErr'></div>";
	html += "</div>";
	html += "<div class='form-group form-row'>";
	html += "<label class='col-md-3 control-label' for='cCustomerName'>Name</label>";
	html += "<div class='col-md-9'>";
	html += "<input id='cCustomerName' name='cCustomerName' placeholder='Name' class='form-control input-md' type='text' value='"+name+"'>";
	html += "</div>";
	html += "</div>";
	html += "<div class='form-group form-row'>";
	html += "<label class='col-md-3 control-label' for='cCustomerEmail'>Email</label>";
	html += "<div class='col-md-9'>";
	html += "<input id='cCustomerEmail' name='cCustomerEmail' placeholder='Email' class='form-control input-md' type='text' value='"+email+"'>";
	html += "</div>";
	html += "</div>";
	html += "<div class='form-group form-row'>";
	html += "<label class='col-md-3 control-label' for='cCustomerEmail'>Contact Number</label>";
	html += "<div class='col-md-9'>";
	html += "<input id='cCustomerContactNumber' name='cCustomerContactNumber' placeholder='Contact Number' class='form-control input-md' type='text' value='"+contactNumber+"'>";
	html += "</div>";
	html += "</div>";

	if(srv_array.length>0) {
		html += "<div class='form-group form-row'>";
		html += "<label class='col-md-3 control-label' for='cCustomerSubject'>Service</label>";
		html += "<div class='col-md-9'>";
		html += "<select id='cCustomerSubject' name='cCustomerSubject' class='form-control'>\
					<option value=''>---Select---</option>";
					for(i=0; i<srv_array.length; i++) {
						var key_title = srv_array[i].split("=");
						html += "<option value='"+key_title[0]+"'>"+key_title[1]+"</option>";
					}
		html += "</select>";
		html += "</div>";
		html += "</div>";
	} else {
		html += "<div class='form-group form-row' style='display:none;'>";
		html += "<label class='col-md-3 control-label' for='cCustomerSubject'>Service</label>";
		html += "<div class='col-md-9'>";
		html += "<select id='cCustomerSubject' name='cCustomerSubject' class='form-control'>\
					<option value=''>---Select---</option>";
					for(i=0; i<srv_array.length; i++) {
						var key_title = srv_array[i].split("=");
						html += "<option value='"+key_title[0]+"'>"+key_title[1]+"</option>";
					}
		html += "</select>";
		html += "</div>";
		html += "</div>";
	}

	if(lng_array.length>1) {

		html += "<div class='form-group form-row'>";
		html += "<label class='col-md-3 control-label' for='cCustomerLanguage'>Language</label>";
		html += "<div class='col-md-9'>";
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

		html += "<div class='form-group form-row' style='display:none;'>";
		html += "<label class='col-md-3 control-label' for='cCustomerLanguage'>Language</label>";
		html += "<div class='col-md-9'>";
		html += "<select id='cCustomerLanguage' name='cCustomerLanguage' class='form-control'>";
					for(i=0; i<lng_array.length; i++) {
						var key_title = lng_array[i].split("=");
						html += "<option value='"+key_title[0]+"'>"+key_title[1]+"</option>";
					}
		html += "</select>";
		html += "</div>";
		html += "</div>";

	}

	html += "<div class='form-group form-row'>";
	html += "<label class='col-md-3 control-label' for='cCustomerInfoSend'></label>";
	html += "<div class='col-md-9'>";
	html += "<button id='cCustomerInfoSend' name='cCustomerInfoSend' class='btn btn-warning'>OK</button>";
	html += "</div>";
	html += "</div>";

    return html;
}

function createCustomerChatWindow(left, top, width, height, html) {

	//console.log("Called");

	var selectedTheme = layout;//$gchatBtn.data("layout");

	var customerWindow = "<div id='g-chat-customer-window' class='container-fluid g-chat-customer-window gccw-"+selectedTheme+"'>";
	customerWindow += "<legend class='gccw-header'>Welcome to Live Chat <span id='btnChatClose'>Close</span></legend>";
	customerWindow += "<div class='gccw-body'>";

	customerWindow += html.length>0 ? html : "";

	customerWindow += "</div>";
	customerWindow += "<div class='gccw-footer'><textarea id='chatTextInputBox'></textarea>";
	customerWindow += "<input id='btnSend' type='button' class='btn btn-default btn-send' value='Send'></div>";
	customerWindow += "</div>";

	/*
	var customerWindow = "<div id='g-chat-customer-window' class='g-chat-customer-window gccw-"+selectedTheme+"'>";
	customerWindow += "<div class='gccw-header'><span class='gccw-name'>&nbsp;Welcome to Live Chat</span><span class='gccw-action'> <span id='gcc-action-minimize'>&minus;</span> <span id='gcc-action-close'>&times;</span> </span></div>";
	customerWindow += "<div class='gccw-body'>";
	customerWindow += html.length>0 ? html : "";
	customerWindow += "</div>";
	customerWindow += "<div class='gccw-footer'><textarea></textarea></div>";
	customerWindow += "</div>";
	*/
	/*
	var customerWindow = "<div id='g-chat-customer-window' class='container g-chat-customer-window gccw-"+selectedTheme+"'>";
	customerWindow += "<div class='row'>";
	customerWindow += "<div class='col-xs-12 col-sm-12 col-xl-12'>";
	customerWindow += "<div class='row gccw-header'><span class='gccw-name'>&nbsp;Welcome to Live Chat</span><span class='gccw-action'> <span id='gcc-action-minimize'>&minus;</span> <span id='gcc-action-close'>&times;</span> </span></div>";
	customerWindow += "<div class='row gccw-body'>";
	customerWindow += html.length>0 ? html : "";
	customerWindow += "</div>";
	customerWindow += "<div class='row gccw-footer'><textarea></textarea></div>";
	customerWindow += "</div>";
	customerWindow += "</div>";
	customerWindow += "</div>";
	*/

	$("body").append(customerWindow);



	autosize(document.querySelectorAll('textarea'));
	//$(".g-chat").hide();
	$(".gccw-footer").hide();
	var available = $.cookie("available_agent");

	$("#btnChatClose").click(function(){

		if(!confirm("Do you want to close conversation!")) return;
		
		//window.location.href="http://192.168.10.18/cloudcc/chat_rating.php";

		
		//if(confirm("Do you want to close conversation?")) {
			var c_w_k = $.cookie("cCustomerWebKey");
			if(c_w_k!=null && c_w_k.lenght>0) ChatClose();
			else if(obj!=null) obj.ChatClose();
			
			//clearCookie();
			$(".g-chat-customer-window").remove();
			$gchatBtn.show();
			$.cookie("customerChatBox","{}");
			//$("#gCCCustomerChatButton").show();
			//window.close();
		//}
		
		var cid = $.cookie("cCustomerCallId");
		
		if( cid!=null && cid!='null' && cid.length!=0) {
			clearCookie();
			showRatingView(cid);
		} else {
			clearCookie();
			window.close();
		}
		
	});
	if(available==0) return;
	
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

	$(".g-chat-customer-window .gccw-footer textarea")
		/*
		.focus(function(e){
			textBoxfocus = false;
			$box.find(".gccw-header").removeClass("highlight");
		})*/
		.keypress(function(e){

			if(conversationClosed) return false;

			textBoxfocus = false;
			$box.find(".gccw-header").removeClass("highlight");

			var k = e.keyCode ? e.which : e.keyCode;
			var it = this;
			
			if(!textTypingMine) {
				var textTypingMineAt = new Date();
				//$.cookie("textTypingMineAt",textTypingMineAt);
				textTypingMine = true;
				
				var $typing = $("#gccw-typing");
				var $boxBody = $box.find(".gccw-body");
				sendChunkText($boxBody, $typing, ".");
				
				return;
			}
			
			if(k==13) {
				KeyPressTrigger();
				/*
				var message = $(this).val();
				if(message.length==0) return false;
				//if(unescape(encodeURIComponent(btoa(message))).length>128) return false;
				if(message.length>256) return false;

				$(this).val("");
				//SendMsg(receiverId, message);
				$(this).css('height','36px');
				$(this).val('').focus();

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
				*/

				return false;
			}
		});

	$("#btnSend").click(function(){
		/*
		console.log("Event triggered");
		var press = jQuery.Event("keypress");
		press.ctrlKey = false;
		press.which = 13;
		$("#chatTextInputBox").trigger(press);
		*/
		KeyPressTrigger();
	});

	
	$("#gcc-action-minimize").click(function(){
		$(".g-chat-customer-window").hide();
		$("#gCCCustomerChatButton").show();
	});
	$("#cCustomerInfoSend").unbind("click").on("click", function(){

		cCustomerName = $("#cCustomerName").val();
		cCustomerEmail = $("#cCustomerEmail").val();
		cCustomerContact = $("#cCustomerContactNumber").val();
		cCustomerSubject = $("#cCustomerSubject").val();
		cCustomerSubjectText = $("#cCustomerSubject option:selected").text();
		cCustomerLanguage = $("#cCustomerLanguage").val();
		cCustomerLanguageText = $("#cCustomerLanguage option:selected").text();

		$.cookie("tempCustomerName", cCustomerName);
		$.cookie("tempCustomerEmail", cCustomerEmail);
		$.cookie("tempCustomerContactNumber", cCustomerContact);
		$.cookie("tempCustomerSubject", cCustomerSubject);
		$.cookie("tempCustomerLanguage", cCustomerLanguage);


		if(cCustomerName.length!=0 && cCustomerEmail.length!=0 && cCustomerSubject.length!=0 && cCustomerLanguage.length!=0) {

			if(!isValidEmailAddress(cCustomerEmail)) {
				$("#gccRequiredFieldErr").html("Email Address Invalid");
				return;
			}

			$("#g-chat-customer-window .gccw-body").html("");
			$("#g-chat-customer-window .gccw-header .gccw-name").html("&nbsp;Connecting...");

			$.cookie("cCustomerName", cCustomerName);
			$.cookie("cCustomerEmail", cCustomerEmail);
			$.cookie("cCustomerContactNumber", cCustomerContact);
			$.cookie("cCustomerSubject", cCustomerSubject);
			$.cookie("cCustomerSubjectText", cCustomerSubjectText);
			$.cookie("cCustomerLanguage", cCustomerLanguage);
			$.cookie("cCustomerLanguageText", cCustomerLanguageText);

			if(obj != null) {

				ChatJoin();

			} else {

				try {
					obj = new gPlexCCChatWSApi();
					//var wshref = (window.location.protocol === 'https') ? 'wss' : 'ws';
					//wshref += '://' + window.location.host;
					//var port = $gchatBtn.data("port");
					//obj.loadSettings('ws://'+socket_ip+':'+socket_port+'/chat');
					obj.loadSettings('wss://'+srv_name+':'+socket_port+'/chat');
					//obj.setNumLoggedIn(0);
					obj.Connect();
				} catch (ex) {
					alert(ex.message);
				}

			}

		} else {
			//alert("All field required!");
			if(cCustomerName.length==0) {
				$("#gccRequiredFieldErr").html("Name required!");
			} else if(cCustomerEmail.length==0) {
				$("#gccRequiredFieldErr").html("Email required!");
			} else if(cCustomerSubject.length==0) {
				$("#gccRequiredFieldErr").html("Please choose service!");
			} else if(cCustomerLanguage.length==0) {
				$("#gccRequiredFieldErr").html("Language required!");
			}
		}
	});
}


function KeyPressTrigger() {
	
	var $inputBox = $("#chatTextInputBox");
	
	

	var message = $inputBox.val();
	message = $.trim(message);
	message = nl2br(message);
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
	var msg = {
		from: $.cookie("cCustomerName"),
		name: $.cookie("cCustomerName"),
		msg:message,
		receiverId: agentId,
		receiverName: agentNick
	};
	var row = msgRow(msg,"L");

	if(message!=".") {
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
		console.log("Sending text: "+timeStamp+"|"+msgArray[i]+"|"+length+"|"+i);
		ChatText(b64e(timeStamp+"|"+msgArray[i]+"|"+length+"|"+i));
	}

	//ChatText(btoa(message));
}



function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function ChatJoin() {
    try {

			//currentRequestSCeq++;
			//var curTime = new Date();
			//RequestSession.push({"SCeq":currentRequestSCeq,"CHAT_JOIN":"","Time":curTime});

			ChatJoinRequestTime = new Date();
			checkSession();

			$.ajax({
				type:"POST",
				url:baseUrl+"/chat_agent.php",
				data:{page_id:page_id,site_key:site_key,user:user,service:$.cookie("cCustomerSubject"),
				        name: $.cookie("cCustomerName"), email: $.cookie("cCustomerEmail"), contact: $.cookie("cCustomerContactNumber"),
				        subject: $.cookie("cCustomerSubjectText"), service_id: $.cookie("cCustomerSubject"), language: $.cookie("cCustomerLanguage"),
				        domain: domain, www_ip: www_ip,url: web_site_url,duration: user_arival_duration,location: geoLocation
				},
				dataType:"jsonp",
				success: function(resp) {
				console.log('resp.available');
				resp.available = parseInt(resp.available);
				//console.log(resp.available);
				if (resp.available>0) {
					conversationClosed = false;
					logId = resp.logid;
					obj.ChatJoin({
						name: $.cookie("cCustomerName"),
						email: $.cookie("cCustomerEmail"),
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
	timeout = setTimeout(checkSession, 1000);
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

function ChatClose() {
	try {
		conversationClosedNotification();
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
}

function welcomeMessageToCustomer(msg, isWecomeMsg) {
	isChatInIdle = true;
	cahtIdleTime = new Date();

	var $box = $(".g-chat-customer-window");
	$("#g-chat-customer-window .gccw-header .gccw-name").html(msg);

	if(isWecomeMsg) {
		$(".gccw-footer").show();
		setTimeout(function() {

			var msg = {
				from: agentId,
				name: agentNick,
				msg:"My name is " + $.cookie("cChatAgentName"),
				receiverId: $.cookie("cCustomerName"),
				receiverName: $.cookie("cCustomerName")
			};

			var row = msgRow(msg,"R");
			var $boxBody = $box.find(".gccw-body");
			$boxBody.html(row);
			$boxBody.scrollTop($boxBody[0].scrollHeight);
		}, 1500);

		setTimeout(function(){
			var msg = {
				from: agentId,
				name: agentNick,
				msg: "How may I help you?",
				receiverId: $.cookie("cCustomerName"),
				receiverName: $.cookie("cCustomerName")
			};
			var row = msgRow(msg,"R");


			var $boxBody = $box.find(".gccw-body");
			$boxBody.append(row);
			$boxBody.scrollTop($boxBody[0].scrollHeight);
		}, 3000);
	} else {
		var $boxBody = $box.find(".gccw-body");
		$boxBody.html("You are in queue, please wait a while... ");
	}

}

//var baseUrl = "http://64.5.49.34/cloudcc/";
//var baseUrl = "http://ccportal.gplex.com/";
//var baseUrl = "http://192.168.10.67/";
(function() {
	//var t = new Date().toString().split(" ")[4];
	if(!window.jQuery) {
		loadjscssfile(baseUrl+"/ccd/jquery.min.js", "js");
	}
	loadjscssfile(baseUrl+"/ccd/wss.gPlexCCChatWS.js", "js");
	loadjscssfile(baseUrl+"/ccd/gchat.css?v=1.1.0", "css");
	if (typeof customChatTheme !== 'undefined' && customChatTheme.length > 0) {
                loadjscssfile(baseUrl+"/ccd/gchat."+customChatTheme+".css?v=1.1.0", "css");
        }
	loadjscssfile(baseUrl+"/ccd/autosize.js", "js");
	loadjscssfile(baseUrl+"/ccd/jquery-ui.min.js", "js");
	loadjscssfile(baseUrl+"/ccd/jquery-ui.css", "css");
	loadjscssfile(baseUrl+"/ccd/bootstrap.min.css", "css");
	
	loadjscssfile(baseUrl+"/ccd/rateyo/jquery.rateyo.min.css?v=1.0", "css");
	loadjscssfile(baseUrl+"/ccd/rateyo/jquery.rateyo.min.js?v=1.0", "js");

	setTimeout(jssdk,2000);


    /*
    if (window.addEventListener) {
        // Standard
        window.addEventListener('load', jQueryCheck, false);
    }
    else if (window.attachEvent) {
        // Microsoft
        window.attachEvent('onload', jQueryCheck);
    }
    function jQueryCheck() {
        if (typeof jQuery === "undefined") {
            //alert("No one's loaded it; either load it or do without");
			loadjscssfile(baseUrl+"ccd/jquery.min.js", "js");
			loadjscssfile(baseUrl+"ccd/gPlexCCChatWS.js", "js");
			loadjscssfile(baseUrl+"ccd/gchat.css?sdks", "css");
			loadjscssfile(baseUrl+"ccd/autosize.js", "js");
			loadjscssfile(baseUrl+"ccd/jquery-ui.min.js", "js");
			loadjscssfile(baseUrl+"ccd/jquery-ui.css", "css");

			setTimeout(jssdk,5000);
        }
    }
    */
})();


var textReceivedArray = new Array();
var currentMsgTimestamp=0;
function msgReceived(resp) {
	var msg = b64d(resp.message);//message|total_chunk|chunk_number
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
	if(direction=="L") {
		row = "<div class='msg-row'>\
				<div class='caption-left'>"+msg.name+" --- <span class='timestmp'>"+timestmp+"</span></div>\
				<span class='msg-item left'>"+urlify(msg.msg)+"</span>\
				<div class='clear'></div>\
			</div>";
	} else {
		row = "<div class='msg-row'>\
				<div class='caption-right'><span class='timestmp'>"+timestmp+"</span> --- "+$.cookie("cChatAgentName")+"</div>\
				<span class='msg-item right'>"+urlify(msg.msg)+"</span>\
				<div class='clear'></div>\
			</div>";
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

function conversationClosedNotification() {
	conversationClosed = true;
	$indicator = $("#gccw-closed-conversation");
	if($indicator.length<=0) {
		var $box = $("#g-chat-customer-window .gccw-body");
		$box.append("<div id='gccw-closed-conversation'>Conversation closed by Agent</div>");
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
    return text.replace(urlRegex, function(url) {
        return '<a href="' + url + '" target="_blank">' + url + '</a>';
    })
    // or alternatively
    // return text.replace(urlRegex, '<a href="$1">$1</a>')
}

function showRatingView(cid)
{
	var ratingWindow = "";
	ratingWindow += "<div id='g-chat-customer-window' class='container-fluid g-chat-customer-window gccw-"+layout+"'>";
	ratingWindow += "<legend class='gccw-header'>";
	ratingWindow += "Welcome to Live Chat ";//<span id='btnChatClose'>Close</span>
	ratingWindow += "</legend>";
	ratingWindow += "<div class='gccw-body'>";

	ratingWindow += "<div class='row' align='center'>";
	ratingWindow += "<div class='col-md-12' id='rateYo_caption'>How do you rate our service?</div>";
	ratingWindow += "<div class='col-md-12 center'><div id='rateYo'></div></div>";
	ratingWindow += "<div id='rating' class='col-md-12' style='height:36px; font-weight:bold; color:#bbbbbb; margin-top: 5px;'>&nbsp;</div>";
	ratingWindow += "<div id='rateYo_button' style='padding-top:30px; padding-bottom:30px;'><a class='btn btn-warning' onclick='window.close()'>Not Now</a></div>";
	ratingWindow += "</div>";

	ratingWindow += "</div>";
	ratingWindow += "</div>";
	
	$('body').html(ratingWindow);

	var loadYoRating = function() {
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
	};

	setTimeout(loadYoRating, 200);
}

function CloseChatWindow(rating, cid)
{

	$.ajax({
		type:"GET",
		url:baseUrl+"/chat.php",
		data:{chat_rating:rating,call_id:cid,user:user},
		dataType:"jsonp",
		success:function(resp){
			
		}
	  });

	setTimeout("window.close()",3000);
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