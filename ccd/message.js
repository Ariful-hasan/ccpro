/*
var agentListData = [{"agent_id":"1001","nick":"Md. zubayer Ahmed"},
				{"agent_id":"1002","nick":"Mr. Abdul Ahad"},
				{"agent_id":"1003","nick":"Mr. Z"},
				{"agent_id":"1004","nick":"Mr. P"},
				{"agent_id":"2200","nick":"Mr. Q"},
				{"agent_id":"2000","nick":"Mr. R"},
				{"agent_id":"1007","nick":"Mr. S"}];
*/

var maxZIndex = 50;
var chatBoxes = {};
var transfer_agent_id = "";
var transfer_agent_name = "";
var conferance_call_id = "";
var isActive;
var default_lan = [];

$( init ); 
function init() {

	$("#msgButton, #chatMenu").click(function(){
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
			rows += "<li href=\"javascript:void(0)\" class=\"new-window\" aid='"+agentListData[i].agent_id+"' nick='"+agentListData[i].nick+"'><div class='info'><span>"+agentListData[i].agent_id+"</span><span> - "+agentListData[i].nick+"</span></div></li>";
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
					//console.log(searchText, agent_id , nick, agent_id.indexOf(searchText), nick.indexOf(searchText));
					rows += "<li href=\"javascript:void(0)\" class=\"new-window\" aid='"+agentListData[i].agent_id+"' nick='"+agentListData[i].nick+"'><div class='info'><span>"+agentListData[i].agent_id+"</span><span> - "+agentListData[i].nick+"</span></div></li>";
				}
			}
			$("#agentList").find("ul").html(rows);
		});
		
	});
	

	//$.cookie("chatBoxes","{}")
	//agentList();
	
	
	var cb = JSON.parse($.cookie("chatBoxes"));
	chatBoxes = cb==null || cb.length==0  ? {} : JSON.parse($.cookie("chatBoxes"));
	
	//existingChatBoxes(chatBoxes);
	//$('.g-chat-agent-window').resizable();
	$(document).on("mouseenter", '.g-chat-agent-window', function(e){
		var item = $(this); 
		if (!item.is('.ui-draggable')) {
			item.draggable({
				containment:$('body'),
				handle:'.gcaw-header',
				opacity:0.5,
				cursor: 'move',
				stop: handleDragStop
			});
			
		}
		
	}).on("mousedown", '.g-chat-agent-window', function(){
		$cbbox = $('.g-chat-agent-window');
		maxZIndex++;
		//$cbbox.css({"z-index":50});
		$(this).css({"z-index":maxZIndex});
	});
	
	$(document).on("click",".new-window",function(){
		var user_id = $(this).attr('aid');
		var user_name = $(this).attr('nick');
		openChatWindow(user_id, user_name);
		$("#agentList").remove();
	});
	
	$(document).on("click",".gcaw-close",function(e){
		var $box = $(this).parent().parent().parent();
		var user_id = $box.attr("id").replace("chatbox_","");
		
		try {
			if(user_id.length>4) {
				//obj.ChatClose(cookieValue.callIdToChat, cookieValue.webKey);
				var $menu = $(this).parent().find(".ccaw-menu");
				if($menu.length>0) {
					$menu.remove();
				} else {
					var isSupervisor = chatBoxes[user_id].isSupervisor;
					var ul;
					if(isSupervisor) {
						ul = "<ul class='ccaw-menu'><li>Pick Up</li><li class='close'>Close</li></ul>";
					} else {
						ul = "<ul class='ccaw-menu'><li>Transfer</li><li class='email'>Email Chat History</li><li class='close'>Close</li></ul>";
						//<li class='colorbox'>Disposition</li>
						//<li class='templatebox'>Template</li>
					}
					$(this).parent().append(ul);
					$menu = $(this).parent().find(".ccaw-menu");
					//$(".colorbox").colorbox({iframe:true, href:dispositionUrl, width:950, height:750});
					$menu.find("li").click(function(){
						$menu.remove();
						
						if($(this).hasClass("close")) {
							//alert("Chat close");
							//if(isSupervisor) {
								//_Supervisor.ChatMonitorClose(user_id);
								//obj.ChatClose(user_id, cookieValue.webKey);
								//_Chat.ChatClose(user_id);
							//} else {
								//obj.ChatClose(cookieValue.callIdToChat, cookieValue.webKey);
								//obj.ChatClose(user_id, cookieValue.webKey);
								//_Chat.ChatClose(user_id);
							//}
							//$box.remove();
							//delete chatBoxes[user_id];
							//$.cookie("chatBoxes", JSON.stringify(chatBoxes));
							var bx = chatBoxes[user_id];
							//if(dispositionUrl.indexOf('uad')<0) {
							
							var url = typeof bx.web_site_url =='undefined' ? "" : bx.web_site_url;
							var dur = typeof bx.user_arival_duration =='undefined' ? 0 : bx.user_arival_duration;
							
							var tempDispositionUrl = dispositionUrl+"&url="+url+"&uad="+dur+"&cid="+user_id+"&email="+bx.email+"&contact="+bx.contact+"&service_id="+bx.service_id; 
							//}
							$.ajax({
								type:"POST",
								url:tempDispositionUrl,
								success:function(resp) {
									if(resp!="N") {
										$.colorbox({iframe:true, href:tempDispositionUrl, width:700, height:500, opacity:0 });
									} else {
										closeChatBox(user_id);
									}
								}
							});
							//$.colorbox({iframe:true, href:tempDispositionUrl, width:700, height:500, opacity:0 });
						} else if($(this).hasClass("email")) {
							var email = chatBoxes[user_id].email;
							var skill_id = chatBoxes[user_id].skill_id;
							var emailView = "<div id='chatEmailView' class='chat-transfer-view'>\
									<div class='ctv-header'><b>Email Chat history</b></div>\
									<div class='ctv-body'>\
										<table>\
											<tr><td><input type='radio' name='emailto[]' id='et' value='emailTo' checked></td><td> <label id='etLabel' for='et'>To: "+email+"</label></td></tr>\
											<tr><td><input type='radio' name='emailto[]' id='etc' value='emailToCustom'></td><td> <label for='etc'><input type='text' id='emailToCustom' name='emailToCustom' placeholder='Email address' style='width:200px;'></label></td></tr>\
											</table>\
									</div>\
									<div class='ctv-footer' align='center'><table align='center'><tr><td><a id='btnChatEmailCancel' class='uibutton'>Cancel</a></td><td><a id='btnChatEmail' class='uibutton special'>Email</a></td></tr></table></div>\
									</div>";
								var $boxBody = $box.find(".gcaw-body");
								$boxBody.append(emailView);
								var $chatEmailView = $box.find("#chatEmailView");
								
								var top = $chatEmailView.css("top");
								top = top.replace("px","");
								top = parseInt(top) + $boxBody.scrollTop();
								$chatEmailView.css("top",top);
								
								$("#emailToCustom").attr("disabled", "disabled");
								
								var mailOption = "et";
								$("#et").change(function() {
									if($(this).is(":checked")) {
										mailOption = "et";
										$("#etLabel").css({"color":"#000000"});
										$("#emailToCustom").attr("disabled", "disabled");
									} else {
										mailOption = "etc"
										$("#emailToCustom").removeAttr("disabled");
										$("#etLabel").css({"color":"#bbbbbb"});
									}
								});
								$("#etc").change(function() {
									if($(this).is(":checked")) {
										mailOption = "etc"
										$("#emailToCustom").removeAttr("disabled");
										$("#etLabel").css({"color":"#bbbbbb"});
									} else {
										mailOption = "et"
										$("#etLabel").css({"color":"#000000"});
										$("#emailToCustom").attr("disabled", "disabled");
									}
								});
									
								$chatEmailView.find("#btnChatEmail").click(function(){
									//var email_to = "";
									//var email_to_custom = $("#emailToCustom").val();
									
									if(mailOption=='et') {
										email_to = email;
									} else {
										email_to = $("#emailToCustom").val();
									}
									email_to = $.trim(email_to);
									//console.log(email_to);
									if(email_to.length>0) {
										if(!isValidEmailAddress(email_to)) {
											alert("Invalid email address");
											return;
										}
									} else {
										alert("Email address empty");
										return;
									}
									_Chat.EmailChatHistory(user_id, email_to, skill_id);
									$chatEmailView.remove();
								});
								
								$chatEmailView.find("#btnChatEmailCancel").click(function(){
									$chatEmailView.remove();
								});
						
							//if(confirm('Do you want to email chat history')) {
								//_Chat.EmailChatHistory(user_id, email);
							//}
						}/* else if($(this).hasClass("colorbox")) {
							var bx = chatBoxes[user_id];
							if(dispositionUrl.indexOf('uad')<0) {
								dispositionUrl += "&url="+encodeURIComponent(bx.web_site_url)+"&uad="+bx.user_arival_duration+"&cid="+user_id; 
							}
							$.colorbox({iframe:true, href:dispositionUrl, width:950, height:750, escKey:false, overlayClose:false, onClosed:function(){ closeChatBox(user_id); }, onLoad: function() { $('#cboxClose').remove(); }});
						} else if($(this).hasClass("templatebox")) { 
						
							if(chatTemplateUrl.indexOf('user_id')<0) 
								chatTemplateUrl += "&user_id="+user_id;
						
							$.colorbox({iframe:true, href:chatTemplateUrl, width:950, height:750});
						
						}*/ else {
						
							if(isSupervisor) {
								_Supervisor.ChatPickUp(user_id);
							} else {
						
								var agentRow = "<option value=''>&nbsp;</option>";
								for(i=0; i<agentListData.length; i++) {
									agentRow += "<option value='"+agentListData[i].agent_id+"'>"+agentListData[i].agent_id+" "+agentListData[i].nick+"</option>";
								}

								var skillRow = "<option value=''>&nbsp;</option>";
								for(i=0; i<skilListData.length; i++) {
									skillRow += "<option value='"+skilListData[i].skill_id+"'>"+skilListData[i].skill_name+"</option>";
								}
								
								var xferView = "<div id='chatTransferView' class='chat-transfer-view'>\
									<div class='ctv-header'><b>Transfer to:</b></div>\
									<div class='ctv-body'>\
										<table><tr><td>Agent:</td><td></select> <select id='chatTransferAgent'>"+agentRow+"</select></td></tr>\
										<tr><td>Skill:</td><td> <select id='chatTransferSkill'>"+skillRow+"</select></td></tr></table>\
									</div>\
									<div class='ctv-footer' align='center'><table align='center'><tr><td><a id='btnChatTransferCancel' class='uibutton'>Cancel</a></td><td><a id='btnChatTransfer' class='uibutton special'>Transfer</a></td></tr></table></div>\
									</div>";
								var $boxBody = $box.find(".gcaw-body");
								$boxBody.append(xferView);
								
								var $transferView = $box.find("#chatTransferView");
								
								var top = $transferView.css("top");
								top = top.replace("px","");
								top = parseInt(top) + $boxBody.scrollTop();
								$transferView.css("top",top);
								
								$("#chatTransferAgent").select2();
								$("#chatTransferSkill").select2();
								
								
								
								$transferView.find("#btnChatTransfer").click(function(){
									var transfer_to = '';
									var agent_id = $("#chatTransferAgent").val();
									var agent_name = $("#chatTransferAgent option:selected").text();
									var sqid = $("#chatTransferSkill").val();
									
									if(agent_id.length==0 && sqid.length==0) return;
									
									if(agent_id.length > 0) {
										transfer_to = "AG"+agent_id;
										var transferButton = "<span class='uibutton-group'>\
												<span class='transfer uibutton special'>Transfer</span>\
												<span class='cancel uibutton confirm'>x</span>\
											</span>";
										$gcawTransfer = $box.find(".gcaw-transfer");
										$gcawTransfer.append(transferButton);
										$gcawTransfer.find(".transfer").click(function(){ 
											//console.log(user_id,transfer_to);
											_Chat.ChatTransferConfirm(user_id, transfer_to); 
											
											$("#chatbox_"+agent_id).remove();
											delete chatBoxes[agent_id];
											$.cookie("chatBoxes", JSON.stringify(chatBoxes));
											newChatBoxStorage(chatBoxes);
											
											$box.remove();
											delete chatBoxes[user_id];
											$.cookie("chatBoxes", JSON.stringify(chatBoxes));
											newChatBoxStorage(chatBoxes);
											
											WebChatItemNotFound(user_id);
										});
										$gcawTransfer.find(".cancel").click(function(){ 
											//$gcawTransfer.html("");
											$("#chatbox_"+user_id).find(".gcaw-transfer").html("");
											$("#chatbox_"+agent_id).remove();
											delete chatBoxes[agent_id];
											$.cookie("chatBoxes", JSON.stringify(chatBoxes));
											newChatBoxStorage(chatBoxes);
											
											WebChatItemNotFound(user_id);
										});
										transfer_agent_id = agent_id;
										transfer_agent_name = agent_name;
										conferance_call_id = user_id;
										//openChatWindow(agent_id, agent_name);
										
									} else if(sqid.length > 0) {
										transfer_to = "SQ"+sqid;
										
										//$box.remove();
										//delete chatBoxes[user_id];
										//$.cookie("chatBoxes", JSON.stringify(chatBoxes));
									}								
									_Chat.ChatTransfer(user_id, transfer_to);
									$transferView.remove();
								});
								$transferView.find("#btnChatTransferCancel").click(function(){
									$transferView.remove();
								});
							
							}
						}
					});
					e.stopPropagation();
				}
			} else {
				$box.remove();
				delete chatBoxes[user_id];
				$.cookie("chatBoxes", JSON.stringify(chatBoxes));
				newChatBoxStorage(chatBoxes);
				
				WebChatItemNotFound(user_id);
			}
		} catch(ex) {
			alert("Chat send warning: "+ex.message);
		}
	});
	/*
	$(document).on("focus", ".gcaw-input", function(e){
		var $box = $(this).parents(".g-chat-agent-window");
		var receiverId = $box.attr("id").replace("chatbox_","");
		chatBoxes[receiverId].focus = false;
		$.cookie("chatBoxes", JSON.stringify(chatBoxes));
		$box.find(".gcaw-header").removeClass("highlight");
	});
	*/
	$(document).on("click", ".gcaw-size", function(e){
		var $it = $(this);
		var $box = $it.parent().parent().parent();;
		var receiverId = $box.attr("id").replace("chatbox_","");
		chatBoxes[receiverId].isMinimize = true;
		$.cookie("chatBoxes", JSON.stringify(chatBoxes));
		newChatBoxStorage(chatBoxes);
		
		if($it.hasClass("minimize")) {
			$it.html("&plus;").removeClass("minimize").addClass("normal");
			$box.find(".gcaw-footer").hide();
			$box.animate({height:31},500);
		} else {
			$it.html("&minus;").removeClass("normal").addClass("minimize");
			var height = chatBoxes[receiverId].height;
			$box.find(".gcaw-footer").show();
			$box.animate({height:height},500);
		}
	});
	$(document).on("click", ".gcaw-maxsize", function(e){
		var $it = $(this);
		var $box = $it.parent().parent().parent();;
		var receiverId = $box.attr("id").replace("chatbox_","");
		//chatBoxes[receiverId].isMinimize = true;
		$.cookie("chatBoxes", JSON.stringify(chatBoxes));
		newChatBoxStorage(chatBoxes);
		
		if($it.hasClass("maximize")) {
			$it.html("&boxbox;").removeClass("maximize").addClass("normal");
			//$box.find(".gcaw-footer").hide();
			var w = window.innerWidth-100;
			var h = window.innerHeight-100;
			$box.animate({height:h,width:w,left:50,top:50,right:50,bottom:50},500);
		} else {
			$it.html("&square;").removeClass("normal").addClass("maximize");
			var height = chatBoxes[receiverId].height;
			var width = chatBoxes[receiverId].width;
			var top = chatBoxes[receiverId].top;
			var right = chatBoxes[receiverId].right;
			//$box.find(".gcaw-footer").show();
			$box.animate({height:height,width:width,top:top,right:right},500);
		}
	});
	$(document).on("click", ".g-chat-agent-window", function(e){
		var $box = $(this);//.parents(".g-chat-agent-window");
		var receiverId = $box.attr("id").replace("chatbox_","");
		chatBoxes[receiverId].focus = false;
		$.cookie("chatBoxes", JSON.stringify(chatBoxes));
		newChatBoxStorage(chatBoxes);
		var $menu = $box.find(".ccaw-menu");
		if($menu.length>0) $menu.remove();
		$box.find(".gcaw-header").removeClass("highlight");
	});
	
	$(document).on("click", ".gcaw-template", function(e){
		var $box = $(this).parents(".g-chat-agent-window");
		var receiverId = $box.attr("id").replace("chatbox_","");
		
		//if(chatTemplateUrl.indexOf('user_id')<0) 
		var tempChatTemplateUrl = chatTemplateUrl+"&user_id="+receiverId;
						
		$.colorbox({iframe:true, href:tempChatTemplateUrl, width:700, height:500});
	});

	$(document).on("click", ".gcaw-co-browser-link", function(e){
		var $box = $(this).parents(".g-chat-agent-window");
		var receiverId = $box.attr("id").replace("chatbox_","");
		var user_name = $box.find(".gcaw-name").text(); 
		
		//if(chatTemplateUrl.indexOf('user_id')<0) 
		var tempCoBrowserUrl = coBrowserUrl+"&user_id="+receiverId+"&user_name="+user_name.trim();
						
		$.colorbox({iframe:true, href:tempCoBrowserUrl, width:700, height:500});
	});
	
	var msg_receiver_list = new Array();
	
	$(document).on("keypress", ".gcaw-input", function(e){
		
		var $box = $(this).parents(".g-chat-agent-window");
		var receiverId = $box.attr("id").replace("chatbox_","");
		
		var conversationClosed = chatBoxes[receiverId].conversationClosed;
		if(conversationClosed) return false;

		var textTypingMine = chatBoxes[receiverId].textTypingMine;
		if(!textTypingMine && $(this).val().length==0) {
			//_Chat.ChatWriting(receiverId);
			sendChunkText(this, receiverId, ".");
			chatBoxes[receiverId].lastTypingMineAt = new Date();
			chatBoxes[receiverId].textTypingMine = true;
			$.cookie("chatBoxes", JSON.stringify(chatBoxes));
			newChatBoxStorage(chatBoxes);
		}
		
		chatBoxes[receiverId].focus = false; //
		$.cookie("chatBoxes", JSON.stringify(chatBoxes)); //
		newChatBoxStorage(chatBoxes);
		
		if(e.keyCode == 13 && e.shiftKey == 0)  {
			if (msg_receiver_list.indexOf(receiverId) == -1) {
                msg_receiver_list.push(receiverId);
                var first_response_data = {
                    call_id: receiverId,
                };
                $.ajax({
                    type: "POST",
                    url: baseUrl + "?task=agent&act=save-agent-first-response",
                    data: first_response_data,
                    dataType: "json",
                    success: function (resp) {
                        console.log("Agent First Response Time Saved.");
                    }
                });
            }
			
			var message = $(this).val();
			if(message.length==0) return false;
			//if(message.length>256) return false;//256
			//if(message.length>256) {
			//if(message.length>10) {
				chatBoxes[receiverId].lastTypingMineAt = new Date();
				chatBoxes[receiverId].textTypingMine = false;
				$.cookie("chatBoxes", JSON.stringify(chatBoxes));
				newChatBoxStorage(chatBoxes);
				sendChunkText(this, receiverId, message);
				e.preventDefault();
				return;
			//}
			//if(unescape(encodeURIComponent(btoa(message))).length>128) return false;
			
			/*
			var isSupervisor = chatBoxes[receiverId].isSupervisor;
			
			var receiverNick = $box.find(".gcaw-name").text();
			message = $.trim(message);
			message = nl2br(message);
			
			$(this).css('height','46px');
			$(this).val('').focus();
			
			chatBoxes[receiverId].focus = false;
		
			chatBoxes[receiverId]["idleTime"] = new Date();
			$.cookie("chatBoxes", JSON.stringify(chatBoxes));
			
			try {
				if(receiverId.length>4) {
					
										
					var msg = {
						from:receiverId,//$.cookie(cookieValue.callIdToChat), 
						name:agentNick,
						msg:message,
						from_type: "AGNT",
						timestamp:""
					};
					
					var row = msgRow(msg);
					var $boxBody = $box.find(".gcaw-body");
					$boxBody.append(row);
					$boxBody.scrollTop($boxBody[0].scrollHeight);
					
					_Chat.ChatText(receiverId, btoa(message));
					
					return false;
				}
			} catch(ex) {
				alert("Chat send warning: "+ex.message);
			}
			
			
			var msg = { from:agentId, name:agentNick, msg:message, from_type:"AGNT", timestamp:"", pic:"ccd/image/pic.png", receiverId:receiverId, receiverName:receiverNick };
			var row = msgRow(msg);
			
			msg = {"from":agentId, "name":agentNick, "msg":message, "pic":"ccd/image/pic.png", "receiverId":receiverId, "receiverName":receiverNick};
			msg = btoa(JSON.stringify(msg));
			
			try {
				if(receiverId.length==4) {
					SendMsg(receiverId, msg);
				}
			} catch(ex) {
				alert("Chat send warning: "+ex.message);
			}
			
			var $boxBody = $box.find(".gcaw-body");
			$boxBody.append(row);
			$boxBody.scrollTop($boxBody[0].scrollHeight);
			
			return false;
			*/
		}
		
	});
	
	
	
	/*
	$(document).on("click",".gcaw-minimize",function(){
		var $box = $(this).parent().parent().parent();//.attr("id").replace("chatbox_","");
		var left = $box.offset().left;
		var top =  $box.offset().top;
	});*/
}

function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function openChatWindow(user_id, user_name)
{
	maxZIndex++;
	var idleTime = new Date();
	var lastTypingAt = idleTime;
	var textTyping = false;
	//var location = "";
	var options = { 
		email:"", 
		contact:"",
		transfered:"",
		isIdle:true,
		conversationClosed:false,
		isMinimize:false,
		web_site_url:"",
		user_arival_duration:0,
		service_id:"",
		location:"",
		lastRegTime: new Date(),
		skill_id:"",
		lastTypingMineAt:new Date(),
		textTypingMine: false
	};
	//var left = 380 - chatBoxes.length*30;
	var len = $.map(chatBoxes, function(n, i) { return i; }).length;
	var right = 235 + len*304;
	createChatBox( user_id, user_name, "", right, 300, 380, maxZIndex, idleTime, lastTypingAt, textTyping, false, options );
}

/**
 * Set language type when user start chat
 * 
 * By deafault set bangla language. When press Ctrl+m then change language
 */
function setLanguageText(event){
	var call_id = event.dataset.callid;
	console.log(call_id);

    if(document.getElementById("language_en_"+call_id).checked){
        $.fn.bnKb.lang_change('e');
        default_lan[call_id] = 'e';
    }else if(document.getElementById("language_bn_"+call_id).checked){
        $.fn.bnKb.lang_change('b');

        $(".bangla_" + call_id).bnKb({
            'switchkey': {"webkit": "k", "mozilla": "y", "safari": "k", "chrome": "k", "msie": "y"},
            'driver': phonetic,
            'writingMode': 'b'
        });
        default_lan[call_id] = 'b';
    }	
}

function setDefaultLanguage(event){
	var call_id = event.dataset.callid;
	console.log(call_id);
    console.log(default_lan);
    console.log(default_lan[call_id]);

    default_lan[call_id] = 'e';
	// if(default_lan[call_id] == ''){
	//     $(".bangla_"+call_id).bnKb({
	//         'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
	//         'driver': phonetic,
	// 		'writingMode': 'e'
	//     });
    //
	//     default_lan[call_id] = 'e';
	// }else{
	// 	console.log(default_lan);
	// 	console.log(default_lan[call_id]);
	// 	$(".bangla_"+call_id).bnKb({
	//         'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
	//         'driver': phonetic,
	// 		'writingMode': default_lan[call_id]
	//     });
	// }
}

function genRandStr(length = 5) {
	var text = "";
	var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  
	for (var i = 0; i < length; i++)
	  text += possible.charAt(Math.floor(Math.random() * possible.length));
  
	return text;
}
  
function genCobrowseLink(user_id,user_name, sahre_id, web_url,web_prefix, phone_number){
	var user_name = user_name.trim();
	var coBaseUrl = web_url;
	var agentCobrowseLink = coBaseUrl+"#&togetherjs="+sahre_id+"&role=agent&username="+agentNick+"&participant="+user_name;
	window.open(agentCobrowseLink, 'cobrowseWindow',"width="+screen.availWidth+",height="+screen.availHeight);
	var chat_str_arr = user_id.split("-");
	var logData = {
		session_id: sahre_id,
		page_id: web_prefix,
		initiator: "A",
		phone_number: phone_number,
		agent_id: agentId,
		customer_name: user_name,
		web_chat_callid: chat_str_arr["0"]
	};
	$.ajax({
		type:"POST",
		url:baseUrl+"?task=agent&act=save-cobrowse-log",
		data:logData,
		dataType:"jsonp",
		success:function(resp){
			console.log("cobrowse log data save successfull");
		}
	});
}

function createChatBox( user_id, user_name, top, right, width, height, zIndex, idleTime, lastTypingAt, textTyping, isSupervisor, options )
{
	console.log("user_id= "+user_id);
	console.log("user_name= "+user_name);
	console.log(options);

	var location = options.location;
	var transfered = options.transfered;
	var location_ary = [];
	if (typeof location != 'undefined') location_ary = location.split("|");
	// console.log(location_ary);

	location = "";
	if(user_id.length>4) {
		location += "<div class='msg-row'>Name: <b>"+user_name+"</b></div>";
		// if(location_ary.length > 0) {
		// 	if(typeof location_ary[0] != 'undefined' && location_ary[0].length>0) {
		// 		location += "<div class='msg-row'>Country: <b>"+location_ary[0]+"</b></div>";
		// 	}
		// 	if(typeof location_ary[1] != 'undefined' && location_ary[1].length>0) {
		// 		location += "<div class='msg-row'>City: <b>"+location_ary[1]+"</b></div>";
		// 	}
		// 	if(typeof location_ary[2] != 'undefined' && location_ary[2].length>0) {
		// 		location += "<div class='msg-row'>Region: <b>"+location_ary[2]+"</b></div>";
		// 	}
		// }
		
		//$.cookie("cChatLanguage");
		//$.cookie("cChatSkill");
		//$.cookie("cChatSubject");
		
		//if(typeof location_ary[3] != 'undefined' && location_ary[3].length>0) {
			// location += "<div class='msg-row'>Language: <b>"+$.cookie("cChatLanguage")+"</b></div>";
		//}
		
		location += "<div id='verify_user' class='msg-row'>Verify User:</div>";

		// var skillid = $.cookie("cChatSkill");
		var skillid = options.skill_id;
		if(typeof skillid != 'undefined' && skillid.length>0) {
			var result = $.grep(skilListData, function(e){ return e.skill_id == skillid; });
			if(result.length>0) {
				var skill = result[0].skill_name;
				location += "<div class='msg-row'>Skill: <b>"+skill+"</b></div>";
			}
		}
		//if(typeof location_ary[5] != 'undefined' && location_ary[5].length>0) {
		var hasCobrowse = false;	
		if(typeof options.service_name != 'undefined' && options.service_name.length > 0) {
			location += "<div class='msg-row hide'>Service: <b>"+options.service_name+"</b></div>";
		}else if(typeof options.subject != 'undefined' && options.subject.length > 0) {
			var subStr = $.trim(options.subject);
			if(subStr.substr(0, 3) == 'CO-'){
				hasCobrowse = true;
				var subName = subStr;
				var subNameArr = subName.split("-");
				var webAndShareId = subNameArr[subNameArr.length-1];
				var webAndShareArr = webAndShareId.split("@");
				var shareId = webAndShareArr[webAndShareArr.length-1];
				var webPrefix = webAndShareArr["0"];
				var webName = cobrowseLinkData[webPrefix].title;
				var webUrl = cobrowseLinkData[webPrefix].link_name;
			}else{
				location += "<div class='msg-row hide'>Service: <b>"+options.subject+"</b></div>";
			}
			
		}
		location += "<div class='msg-row hide'>Email: <b>"+options.email+"</b></div>";
		location += "<div class='msg-row'>Contact No.: <b>"+options.contact+"</b></div>";
		if(hasCobrowse == true) { 
			location += '<button type="button" onclick="genCobrowseLink(\''+user_id+'\',\''+user_name+'\',\''+shareId+'\',\''+webUrl+'\',\''+webPrefix+'\',\''+options.contact+'\')">Co-browsing('+webName+')</button>';
		}
		
		
	}

	//"+location+"
	// console.log(location);
	if($("#chatbox_"+user_id).length>0) return;
	
	var closeButtonText = user_id.length>4 ? "&#9776;" : "&times;";//&#9660;
	var showRecentMessage = user_id.length>4 && transfered!="NO" ? "<a href='javascript://' class='btnShowRecentMsg'>Show recent message</a>" : "";
	var chat_box_init = new Date();
	var html = "<div id='chatbox_"+user_id+"' class='g-chat-agent-window gcaw-blue'>";
	html += "<div class='gcaw-header'>";
	html += "<span class='gcaw-status online'></span>";
	html += "<span class='gcaw-name'>"+user_name+"</span>";
	// html += "<span class='gcaw-idle'></span>";
	html += "<span data-duration='"+chat_box_init+"' class='gcaw-chat-duration'></span>";
	html += "<span class='gcaw-transfer'></span>";
	html += "<span class='gcaw-action'>";
	html += "<span class='gcaw-size minimize'>&minus;</span>";
	html += "<span class='gcaw-maxsize maximize'>&square;</span>";
	html += "<span class='gcaw-close'>"+closeButtonText+"</span>";
	html += "</span>";
	html += "</div>";
	html += "<div class='gcaw-body'>"+location+showRecentMessage+"</div>";
	html += "<div class='gcaw-footer'>";
	html += "<div class='checkbox-inline language-check'>";
  	html += "<input id='language_en_"+user_id+"' name='language_rd_"+user_id+"' type='radio' class='radio-language' value='EN' data-callid='"+user_id+"' checked='checked' onclick='setLanguageText(this)'/>";
  	html += "<label class='mr-10' for='language_en_"+user_id+"'>English</label>";
  	html += "<input id='language_bn_"+user_id+"' name='language_rd_"+user_id+"' type='radio' class='radio-language' value='BN' data-callid='"+user_id+"' onclick='setLanguageText(this)'/>";
  	html += "<label for='language_bn_"+user_id+"'>Bangla</label>";  
	html += "</div>"
	html += "<span class='gcaw-co-browser-link'>C</span>";
	html += "<span class='gcaw-template'>T</span>";
	html += "<textarea id='chatTextarea_"+user_id+"' class='gcaw-input bangla_"+user_id+"' data-callid='"+user_id+"' onfocus='setDefaultLanguage(this)'></textarea><br class='clear' /></div>";
	html += "</div>";
	
	/*
	<div class='msg-row'>\
		<div class='caption-left'>Me --- <span class='timestmp'>05:29 am</span></div>\
		<span class='msg-item left'>This is a test message from sender</span>\
		<div class='clear'></div>\
	</div>\
	<div class='msg-row'>\
		<div class='caption-right'><span class='timestmp'>05:29 am</span> --- "+user_name+"</div>\
		<span class='msg-item right'>This is another test message from receipient</span>\
		<div class='clear'></div>\
	</div>\
	<div class='msg-row'>\
		<div class='caption-left'>Me --- <span class='timestmp'>05:29 am</span></div>\
		<span class='msg-item left'>We are showing this message to present UI</span>\
		<div class='clear'></div>\
	</div>\
	<div class='msg-row'>\
		<div class='caption-right'><span class='timestmp'>05:29 am</span> --- "+user_name+"</div>\
		<span class='msg-item right'>After getting messaging functional we will remove test message.</span>\
		<div class='clear'></div>\
	</div>\
	*/
	
	//<span class='gcaw-minimize'>&nbsp;</span>
	
	$("body").append(html);

	var $box = $("#chatbox_"+user_id);
	//$box.find(".gcaw-body").append(location);

	$box.css({
		"right":right+"px",
		"top":top+"px",
		"z-index":zIndex,
		"width":width+"px",
		"height":height+"px"
	});
	//if(user_id.length>4) {
		$box.find(".btnShowRecentMsg").click(function(){
			var user_id_ary = user_id.split("-");
			$.ajax({
				type:"POST",
				url:baseUrl+"/index.php?task=cdr&act=chat-log&cid="+user_id_ary[0],
				dataType:"json",
				success:function(response) {
					_Chat.LoadMoreChat($box, response);
				}
			});
		});
	//}
	
	var user_name = $box.find(".gcaw-name").text();

	chatBoxes[user_id] = {
		name: user_name,
		right: right,
		top: top,
		width:width,
		height:height,
		dragged: false,
		zIndex: zIndex,
		idleTime:idleTime,
		isIdle: options.isIdle,
		conversationClosed: options.conversationClosed,
		focus: false,
		lastTypingAt: lastTypingAt,
		textTyping: textTyping,
		isSupervisor: isSupervisor,
		location: options.location,
		email: options.email,
		contact: options.contact,
		transfered:options.transfered,
		isMinimize: options.isMinimize,
		web_site_url:options.web_site_url,
		user_arival_duration:options.user_arival_duration,
		service_id:options.service_id,
		lastRegTime:options.lastRegTime,
		skill_id:options.skill_id,
		lastTypingMineAt:options.lastTypingMineAt,
		textTypingMine:options.textTypingMine
	};
	
	$.cookie("chatBoxes", JSON.stringify(chatBoxes));
	newChatBoxStorage(chatBoxes);
	autosize(document.querySelectorAll('textarea'));
	
	$(".g-chat-agent-window").resizable('destroy').resizable({
		minWidth: 300,
		minHeight: 80,
		maxWidth: 900,
		maxHeight: 700,
		stop: handleResizeStop
	});

	// console.log(chatBoxes);
	// console.log('chatBoxes_length'+Object.keys(chatBoxes).length);
	if(Object.keys(chatBoxes).length == 1)
		chatDuration();
}

function chatDuration(){
	// console.log('Start chat duration: ');
	// console.log(chatBoxes);
	// console.log('chatBoxes_length'+Object.keys(chatBoxes).length);
	if(Object.keys(chatBoxes).length > 0){
		for(user_id in chatBoxes) {
			if(!chatBoxes[user_id].conversationClosed){
				// console.log(user_id);
				var data_duration = $("#chatbox_"+user_id+" .gcaw-chat-duration").attr("data-duration");
				// console.log(data_duration);
				var startTime = new Date(data_duration);
				// console.log(startTime);		
				var endTime = new Date();	
				// console.log(endTime);	
				var seconds = Math.floor(get_time_diff(startTime, endTime));
				// console.log(seconds);
				// data_duration = parseInt(data_duration);
				// data_duration = data_duration + 1;
				// console.log(data_duration);

				var minutes = Math.floor(seconds / 60);
				// console.log(minutes);
				seconds = parseInt(seconds - minutes * 60);
				// console.log(seconds);
				minutes = minutes<10 ? "0"+minutes : minutes;
				seconds = seconds<10 ? "0"+seconds : seconds;
				//console.log(" (Idle: "+minutes+":"+seconds+")");

				// $("#chatbox_"+user_id+" .gcaw-chat-duration").attr("data-duration", data_duration);
				$("#chatbox_"+user_id+" .gcaw-chat-duration").html(" (Duration: "+minutes+":"+seconds+")");
			}
		}
		setTimeout(chatDuration,1000);
	}
}


var textReceivedArray = new Array();
var currentMsgTimestamp=0;
function msgReceived(resp) {
	
	var msg = b64d(resp);//message|total_chunk|chunk_number
	console.log("M2: "+msg);
	var item = jQuery.parseJSON(msg);
	msg = item.msg;
	
	//msg = b64d(msg);
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
		//item["msg"] = b64d(msg);
		var lt = /</g,
                    gt = />/g,
                    ap = /'/g,
                    ic = /"/g;
                var value = b64d(msg);
                value = value.toString().replace(lt, "").replace(gt, "").replace(ap, "").replace(ic, "");
                item["msg"] = value;
		msgShowInAgentWindow(item);
	}
	
	/*
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
	}*/
}

function msgShowInAgentWindow(item) {

	var senderId = item.from;
	senderId.replace("-","");
	var senderNick = item.name;
	
	if ($("#chatbox_"+senderId).length <= 0) {
		maxZIndex++;
		var idleTime = new Date();
		var lastTypingAt = idleTime;
		var textTyping = false;
		var options = {
			location:"", 
			email:"", 
			contact:"",
			transfered:"",
			isIdle:true,
			conversationClosed:false,
			isMinimize:false,
			web_site_url:"",
			user_arival_duration:0,
			service_id:"",
			lastRegTime: new Date(),
			skill_id:"",
			lastTypingMineAt: new Date(),
			textTypingMine: false
		};
		//var left = 380 - chatBoxes.length*30;
		createChatBox( senderId, senderNick, "", "", 300, 380, maxZIndex, idleTime, lastTypingAt, textTyping, false, options );
	}
	
	console.log("FOCUS: "+ $("#chatbox_"+senderId+" textarea").is(':focus'));
	
	chatBoxes[senderId].focus = true;
	newChatBoxStorage(chatBoxes);
	
	item["from_type"] = "CUST";
	item["timestamp"] = "";
	
	var row = msgRow(item);
	var $box = $("#chatbox_"+senderId);
	var $boxBody = $box.find(".gcaw-body");
	$boxBody.append(row);
	$boxBody.scrollTop($boxBody[0].scrollHeight);


}

function chatRequest(resp) {
	if(!isActive) {
		try{
			notifyMe("Chat Request", "Chat request from "+resp.name);
		}catch(e){console.log(e);}
	}
	_Chat.ChatRequestAccept(resp);
}

function openTransferedChatBox() {
	if(transfer_agent_id.length==0 || transfer_agent_name.length==0) return;
	
	openChatWindow(transfer_agent_id, transfer_agent_name);
	
	/*
	var $box = $("#chatbox_"+transfer_agent_id);
	var transferButton = "<span class='uibutton-group'>\
							<span class='conference uibutton special'>Conference</span>\
						</span>";
	$gcawTransfer = $box.find(".gcaw-transfer");
	$gcawTransfer.append(transferButton);
	$gcawTransfer.find(".conference").click(function(){ 
			_Chat.ChatConferance(transfer_agent_id, conferance_call_id); 
			
			$box.remove();
			delete chatBoxes[transfer_agent_id];
			$.cookie("chatBoxes", JSON.stringify(chatBoxes));
			
		});
	*/
}


var textReceivedArray = new Array();
var currentMsgTimestamp=0;
function chatTextReceived(resp) {
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
		var lt = /</g,
                    gt = />/g,
                    ap = /'/g,
                    ic = /"/g;
                var value = b64d(msg);
                value = value.toString().replace(lt, "").replace(gt, "").replace(ap, "").replace(ic, "");
		msgShowInWindow(resp, value);
	}
}

function msgShowInWindow(resp, message) {
	//resp = atob(resp);
	//var item = jQuery.parseJSON(resp);
	
	//$('#chatAudio')[0].play();
	
	var senderId = resp.call_id;
	var senderNick = resp.from;//cookieValue.customerNameToChat;
	var from_type = resp.from_type;
	//var textAlign = from_type=="CUST" ? "R" : "L";
	console.log(senderId+", "+message);
	if(message==".") {
		console.log(senderId+", "+message);
		chatBoxes[senderId].lastTypingAt = new Date();
		//chatBoxes[senderId].textTyping = true;
		$.cookie("chatBoxes", JSON.stringify(chatBoxes));
		newChatBoxStorage(chatBoxes);
		chatWriting(senderId);
		return;
	}
	
	if ($("#chatbox_"+senderId).length <= 0) {
		maxZIndex++;
		var idleTime = new Date();
		var lastTypingAt = idleTime;
		var textTyping = false;
		var options = {
			location:"", 
			email:"", 
			contact:"",
			transfered:"",
			isIdle:true,
			conversationClosed:false,
			isMinimize:false,
			web_site_url:"",
			user_arival_duration:0,
			service_id:"",
			lastRegTime: new Date(),
			skill_id:"",
			lastTypingMineAt: new Date(),
			textTypingMine:false
		};
		//var left = 380 - chatBoxes.length*30;
		var len = $.map(chatBoxes, function(n, i) { return i; }).length;
		var right = 5 + len*304;
		createChatBox( senderId, senderNick, "", right, 300, 380, maxZIndex, idleTime, lastTypingAt, textTyping, false, options );
	}
	
	if(!$("#chatbox_"+senderId+" textarea").is(':focus')) {
		$('#chatAudio')[0].play();
		if(!isActive) {
			notifyMe("Text from "+resp.from, message);
		}//setTimeout(function(){window.focus();},2000);
		//window.opener.focusCallController();
	}
	
	var item = { from:senderId, name:senderNick, msg:message, from_type:from_type, timestamp:"" };
	
	chatBoxes[senderId].focus = true;
	
	/*
	var row = msgRow(item);
	var $box = $("#chatbox_"+senderId);
	var $boxBody = $box.find(".gcaw-body");
	$boxBody.append(row);
	$box.scrollTop($box[0].scrollHeight);
	$box.find("textarea").blur();
	*/
	var row = msgRow(item);
	var $box = $("#chatbox_"+senderId);
	var $boxBody = $box.find(".gcaw-body");
	$boxBody.find("#gccw-typing").remove();
	$boxBody.append(row);
	$boxBody.scrollTop($boxBody[0].scrollHeight);
	//$box.find("textarea").blur();
	textReceivedArray = new Array();
}

function chatWriting(boxid) {
	var $box = $("#chatbox_"+boxid);
	var $boxBody = $box.find(".gcaw-body");
	//$boxBody.append(row);
	
	
	$typingIndicator = $box.find("#gccw-typing");
	if($typingIndicator.length<=0) {
		var row = "<div class='msg-row'>\
				<div class='caption-right'><span class='timestmp'> &nbsp; </div>\
				<span class='msg-item right'><img src='ccd/typing.gif' height='8' style='margin:3px 0 0 0;'></span>\
				<div class='clear'></div>\
			</div>";
		$boxBody.append("<div id='gccw-typing'>"+row+"</div>");
		
		$boxBody.scrollTop($boxBody[0].scrollHeight);
	}
}

function msgRow(msg) {
	
	//var HH = parseInt(time.getHours());
	//var ampm = HH>12 ? "pm" : "am";
	//var hh = HH>12 ? HH - 12 : HH;
	var timestamp = msg.timestamp;
	if(timestamp.length==0) {
		var d = new Date();
		var yyyy = d.getFullYear();
		var mm = d.getMonth()+1;
		var dd = d.getDate();
		var hh = d.getHours();
		var ampm = hh >= 12 ? 'pm' : 'am';
		hh = hh % 12;
		hh = hh ? hh : 12; // the hour '0' should be '12'
		var ii = d.getMinutes();
		var ss = d.getSeconds();
		//timestamp = yyyy+"-"+mm+"-"+dd+" "+hh+":"+ii+":"+ss+" "+ampm;
		timestamp = hh+":"+ii+":"+ss+" "+ampm;
		
		//timestamp = Math.floor(date.getTime()/1000) //date to 10 digits timestamp
		//var date = new Date(unix_timestamp*1000); //10 digits timestamp to date
		
		/*
		timestamp = [d.getMonth()+1,
				   d.getDate(),
				   d.getFullYear()].join('/')+' '+
				  [d.getHours(),
				   d.getMinutes(),
				   d.getSeconds()].join(':');
		*/
		//var timestmp = ("0" + hh).slice(-2) + ":" + ("0" + time.getMinutes()).slice(-2) + ":" + ("0" + time.getSeconds()).slice(-2) + " " + ampm;
		addMessageInStorage(msg, timestamp);
	}
	
	var row = "";
	if(msg.from_type=="AGNT") {
		row = "<div class='msg-row _m_r'>\
				<div class='caption-left'>"+msg.name+" --- <span class='timestmp'>"+timestamp+"</span></div>\
				<span class='pull-left'><img src='assets/images/chat_agent_logo.png' style='width:auto;' class='img-circle' alt='agent' width='30' height='30'> </span>\
				<span class='msg-item left' style='margin-left:5px;'>"+urlify(msg.msg)+"</span>\
				<div class='clear'></div>\
			</div>";
	} else {
		row = "<div class='msg-row _m_r'>\
				<div class='caption-right'><span class='timestmp'>"+timestamp+"</span> --- "+msg.name+"</div>\
				<span class='pull-right'><img src='assets/images/chat_customer.png' style='width:auto;' class='img-circle' alt='agent' width='30' height='30'> </span>\
				<span class='msg-item right' style='margin-right:5px;'>"+urlify(msg.msg)+"</span>\
				<div class='clear'></div>\
			</div>";
	}
	return row;
}

function handleDragStop( event, ui ) {
	var $box = $(event.target).parents(".g-chat-agent-window");
	if(typeof $box.attr("id")=='undefined') return;
	
	$box.find(".gcaw-footer").show();
	$box.find(".gcaw-size").removeClass("maximize").addClass("minimize");
	
	var user_id = $box.attr("id").replace("chatbox_","");
	
	var offsetXPos = parseInt( ui.offset.left );
	var offsetYPos = parseInt( ui.offset.top );
	var width = $box.width();
	var zIndex = $box.css("z-index");
	
	chatBoxes[user_id]["right"] = screen.width - (offsetXPos + width);
	chatBoxes[user_id]["top"] = offsetYPos;
	chatBoxes[user_id]["zIndex"] = zIndex;
	
	$.cookie("chatBoxes", JSON.stringify(chatBoxes));
	newChatBoxStorage(chatBoxes);
}

function handleResizeStop( event, ui ) {
	var $box = $(event.target).parents(".g-chat-agent-window");
	if(typeof $box.attr("id")=='undefined') return;
	
	var user_id = $box.attr("id").replace("chatbox_","");
	
	
	
	//var width = parseInt( ui.size.width );
	//var offsetYPos = parseInt( ui.offset.top );
	var zIndex = $box.css("z-index");
	
	chatBoxes[user_id]["width"] = parseInt( ui.size.width );
	chatBoxes[user_id]["height"] = parseInt( ui.size.height );
	chatBoxes[user_id]["zIndex"] = zIndex;
	
	$.cookie("chatBoxes", JSON.stringify(chatBoxes));
	newChatBoxStorage(chatBoxes);
}

function existingChatBoxes(chatBoxes)
{
	for(user_id in chatBoxes) {

		var zIndex = chatBoxes[user_id].zIndex;
		maxZIndex = maxZIndex<zIndex ? zIndex : maxZIndex;
	}
	
	for(user_id in chatBoxes) {
		var user_name = chatBoxes[user_id].name;
		var top = chatBoxes[user_id].top;
		var right = chatBoxes[user_id].right;
		var width = chatBoxes[user_id].width;
		var height = chatBoxes[user_id].height;
		var zIndex = chatBoxes[user_id].zIndex;
		var idleTime = chatBoxes[user_id].idleTime;
		var lastTypingAt = chatBoxes[user_id].lastTypingAt;
		var textTyping = chatBoxes[user_id].textTyping;
		var isSupervisor = chatBoxes[user_id].isSupervisor;
		var isMinimize = chatBoxes[user_id].isMinimize;
		var options = {
			location:chatBoxes[user_id].location, 
			email:chatBoxes[user_id].email,
			contact:chatBoxes[user_id].contact, 
			transfered:chatBoxes[user_id].transfered,
			isIdle:chatBoxes[user_id].isIdle,
			conversationClosed:chatBoxes[user_id].conversationClosed,
			isMinimize:isMinimize,
			web_site_url:chatBoxes[user_id].web_site_url,
			user_arival_duration:chatBoxes[user_id].user_arival_duration,
			service_id:chatBoxes[user_id].service_id,
			lastRegTime:chatBoxes[user_id].lastRegTime,
			skill_id:chatBoxes[user_id].skill_id,
			lastTypingMineAt:chatBoxes[user_id].lastTypingMineAt,
			textTypingMine:chatBoxes[user_id].textTypingMine
		};
		
		createChatBox( user_id, user_name, top, right, width, height, zIndex, idleTime, lastTypingAt, textTyping, isSupervisor, options );
	}
	countUpChatIdleTime();
}

function countUpChatIdleTime() {
	//return;
	for(user_id in chatBoxes) {
		if(!chatBoxes[user_id].isIdle) return;
		var startTime = new Date(chatBoxes[user_id].idleTime);
		if(startTime==null) continue;
		
		var endTime = new Date();
		
		var seconds = get_time_diff(startTime, endTime);
		
		var minutes = Math.floor(seconds / 60);
		var seconds = parseInt(seconds - minutes * 60);
		minutes = minutes<10 ? "0"+minutes : minutes;
		seconds = seconds<10 ? "0"+seconds : seconds;
		//console.log(" (Idle: "+minutes+":"+seconds+")");
		$box = $("#chatbox_"+user_id);
		$box.find(".gcaw-idle").html(" (Idle: "+minutes+":"+seconds+")");
		
		
		var lastTypingAt = new Date(chatBoxes[user_id].lastTypingAt);
		seconds = get_time_diff(lastTypingAt, endTime);
		var textTyping = chatBoxes[user_id].textTyping;
		//console.log(seconds);
		var $typingIndicator = $box.find("#gccw-typing");
		if(seconds>7 && $typingIndicator.length>0) {
			$typingIndicator.remove();
			chatBoxes[user_id].textTyping = false;
			chatBoxes[user_id].lastTypingAt = new Date();
			$.cookie("chatBoxes", JSON.stringify(chatBoxes));
			newChatBoxStorage(chatBoxes);
		}
		
		var lastTypingMineAt = new Date(chatBoxes[user_id].lastTypingMineAt);
		seconds = get_time_diff(lastTypingMineAt, endTime);
		var textTypingMine = chatBoxes[user_id].textTypingMine;
		//console.log(seconds + " " + textTypingMine);
		if(seconds>5 && textTypingMine) {
			chatBoxes[user_id].textTypingMine = false;
			chatBoxes[user_id].lastTypingMineAt = new Date();
			$.cookie("chatBoxes", JSON.stringify(chatBoxes));
			newChatBoxStorage(chatBoxes);
		}
			
		if(chatBoxes[user_id].focus) {
			$box.find(".gcaw-header").toggleClass("highlight");
			//document.title = document.title == "Call Control" ? "New Message" : "Call Control";
		} else {
			//document.title = document.title == "Call Control";
		}
		
		
		//code block for checking online/offline
		startTime = new Date(chatBoxes[user_id].lastRegTime);
		seconds = get_time_diff(startTime, endTime);
		var maxTime = 10*60;
		
		if(seconds <= maxTime) onOffclass = "online";
		else onOffclass = "offline";
		
		$box = $("#chatbox_"+user_id);
		$box.find(".gcaw-status").removeClass("online offline").addClass(onOffclass);

		//if(seconds <= maxTime) chatBoxes[user_id].lastRegTime = endTime;
		//else chatBoxes[user_id].lastRegTime = endTime;
		
		//$.cookie("chatBoxes", JSON.stringify(chatBoxes));
		
	}
	
	
	// setTimeout("countUpChatIdleTime()",1000);
}

function closeTemplateBox(user_id,msg) {
	$("#chatbox_"+user_id).find('textarea').val(msg);
	$.colorbox.close();
}

function closeCoBrowserLinkBox(user_id, user_name, msg) {
	var requestData = getHtmlStorage(storageVars.chatRequestInfo);
	var cli = requestData[user_id].contact;
	console.log(requestData);
	var randStr = genRandStr(10); 
	var coBaseUrl = msg;
	var cobrowseLink = coBaseUrl+"#&togetherjs="+randStr+"&username="+user_name;
	var agentCobrowseLink = coBaseUrl+"#&togetherjs="+randStr+"&role=agent&username="+agentNick+"&participant="+user_name;
	var txtAreaId = "chatTextarea_"+user_id;
	var logData = {
		customerName: user_name,
		customerNumber: cli,
		callId: user_id,
		requestType: "A", // requested by Agent
		customerUrl: cobrowseLink,
		agentUrl: agentCobrowseLink
	}
	$.ajax({
		type: "POST",
		url: chat_co_browse_log_url,
		data: logData,
		success: function (resp) {
			console.log(resp);
		}
	});
	$.colorbox.close();
	$("#chatbox_"+user_id).find('textarea').val(cobrowseLink);
	window.open(agentCobrowseLink, 'cobrowseWindow',"width="+screen.availWidth+",height="+screen.availHeight);
	
	//$("#chatbox_"+user_id).find('textarea').val(msg);
}

function closeChatBox(user_id) {
	_Chat.ChatClose(user_id);
	$box = $("#chatbox_"+user_id);
	$box.remove();
	delete chatBoxes[user_id];
	$.cookie("chatBoxes", JSON.stringify(chatBoxes));
	newChatBoxStorage(chatBoxes);
	
	WebChatItemNotFound(user_id);
}

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

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function isValidEmailAddress(emailAddress) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	return regex.test(emailAddress);
}

function urlify(text) {
    var urlRegex = /(https?:\/\/[^\s]+)/g;
    return text.replace(urlRegex, function(url) {
        return '<a href="' + url + '" target="_blank">' + url + '</a>';
    })
    // or alternatively
    // return text.replace(urlRegex, '<a href="$1">$1</a>')
}

window.onfocus = function () { 
  isActive = true; 
}; 

window.onblur = function () { 
  isActive = false; 
};

function sendChunkText(it, receiverId, message) {
	//if(message==".") return;
	var $box = $(it).parents(".g-chat-agent-window");
	
	message = $.trim(message);
	message = nl2br(message);
	
	
	
	chatBoxes[receiverId].focus = false;
	chatBoxes[receiverId]["idleTime"] = new Date();
	$.cookie("chatBoxes", JSON.stringify(chatBoxes));
	newChatBoxStorage(chatBoxes);
	
	if(receiverId.length>4) {				
	
		var msg = {
			from:receiverId,//$.cookie(cookieValue.callIdToChat), 
			name:agentNick,
			msg:message,
			from_type: "AGNT",
			timestamp:""
		};
		
	} else {
	
		var receiverNick = $box.find(".gcaw-name").text();
		var msg = { 
			from:agentId, 
			name:agentNick, 
			msg:message, 
			from_type:"AGNT", 
			timestamp:"", 
			pic:"ccd/image/pic.png", 
			receiverId:receiverId, 
			receiverName:receiverNick 
		};
	}
	
	if(message!=".") {
		$(it).css('height','46px');
		$(it).val('').focus();
		var row = msgRow(msg);
		var $boxBody = $box.find(".gcaw-body");
		$boxBody.append(row);
		$boxBody.scrollTop($($boxBody[0]).prop("scrollHeight"));
	}
	//$boxBody.scrollTop($boxBody[0].scrollHeight);
	
	message = b64e(message);
	var msgArray = message.match(/.{1,250}/g);
	var length = msgArray.length - 1;
	var timeStamp = Math.floor(Date.now() / 1000);
	for(i=0; i<=length; i++) {
	        /*
		if (i>0) {
			ts_sleep(1000).then(() => {
				sendText(receiverId, timeStamp+"|"+msgArray[i]+"|"+length+"|"+i);
			});
		} else {
			sendText(receiverId, timeStamp+"|"+msgArray[i]+"|"+length+"|"+i);
		}
		*/
		sendText(receiverId, timeStamp+"|"+msgArray[i]+"|"+length+"|"+i, $box);
		var ts_start = new Date().getTime();
                while (new Date().getTime() < ts_start + 50);
	}
}

function ts_sleep (time) {
  return new Promise((resolve) => setTimeout(resolve, time));
}

function sendText(receiverId, message, $box='') {

	var isSupervisor = chatBoxes[receiverId].isSupervisor;

	var receiverNick = $box.find(".gcaw-name").text();

	console.log(message);
	try {
		if(receiverId.length>4) {
			
			_Chat.ChatText(receiverId, b64e(message));
			
			return false;
		}
	} catch(ex) {
		alert("Chat send warning: "+ex.message);
	}

	msg = {"from":agentId, "name":agentNick, "msg":message, "pic":"ccd/image/pic.png", "receiverId":receiverId, "receiverName":receiverNick};
	msg = b64e(JSON.stringify(msg));
	
	try {
		if(receiverId.length==4) {
			SendMsg(receiverId, msg);
		}
	} catch(ex) {
		alert("Chat send warning: "+ex.message);
	}
	
	
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