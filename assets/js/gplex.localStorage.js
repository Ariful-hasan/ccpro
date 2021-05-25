/*============================Local storage=======================*/
function removeHtmlStorage(name) {
	localStorage.removeItem(name);
	localStorage.removeItem(name + '_time');
}

function setHtmlStorage(name, value, expires) {
	if (expires == undefined || expires == 'null') {
		var expires = 3600;
	} // default: 1h
	
	var date = new Date();
	var schedule = Math.round((date.setSeconds(date.getSeconds() + expires)) / 1000);
	localStorage.setItem(name, value);
	localStorage.setItem(name + '_time', schedule);
}

function statusHtmlStorage(name) {
	var date = new Date();
	var current = Math.round(+date / 1000);
	// Get Schedule
	var stored_time = localStorage.getItem(name + '_time');
	if (stored_time == undefined || stored_time == 'null') {
		var stored_time = 0;
	}
	// Expired
	if (stored_time < current) {
		// Remove
		this.removeHtmlStorage(name);
		return 0;
	} else {
		return 1;
	}
}

function getHtmlStorage(name) {
	var value = localStorage.getItem(name);
	var expireTime = parseInt(localStorage.getItem(name + '_time')) * 1000;
	var expireDate = new Date(expireTime);

	if(typeof name != 'undefined' && value != null && expireDate > new Date()){
		return JSON.parse(value);
	}else{
		this.removeHtmlStorage(name);
		this.removeHtmlStorage(name + '_time');
		return JSON.parse('{}');
	}
}

function newChatStore(resp){
	console.log('NEW CHAT');
	console.log(resp);
	console.log(currentCallId);
	
	var callIdToChat = [];
	var chatRequestInfo = {};
	
	try {
		if(!statusHtmlStorage(storageVars.callIdToChat)){
			callIdToChat.push(resp.call_id);
			setHtmlStorage(storageVars.callIdToChat, JSON.stringify(callIdToChat), 24*3600);
		}else{
			callIdToChat = getHtmlStorage(storageVars.callIdToChat);
			
			if(callIdToChat.indexOf(resp.call_id) == -1){			
				callIdToChat.push(resp.call_id);
			}
			setHtmlStorage(storageVars.callIdToChat, JSON.stringify(callIdToChat), 24*3600);
		}
		
		if(!statusHtmlStorage(storageVars.chatRequestInfo)){
			chatRequestInfo[resp.call_id] = resp;
			setHtmlStorage(storageVars.chatRequestInfo, JSON.stringify(chatRequestInfo), 24*3600);
		}else{
			chatRequestInfo = getHtmlStorage(storageVars.chatRequestInfo);
			
			console.log(chatRequestInfo);			
			chatRequestInfo[resp.call_id] = resp;
			console.log(chatRequestInfo);
			
			setHtmlStorage(storageVars.chatRequestInfo, JSON.stringify(chatRequestInfo), 24*3600);
		}
	} catch(ex) {
		console.log("New Chat Store: "+ex.message);
	}
}

function WebChatItemNotFound(callid){
	var callIdToChat = !statusHtmlStorage(storageVars.callIdToChat) ? [] : getHtmlStorage(storageVars.callIdToChat);
	var chatConversation = !statusHtmlStorage(storageVars.chatConversation) ? {} : getHtmlStorage(storageVars.chatConversation);
	var chatRequestInfo = !statusHtmlStorage(storageVars.chatRequestInfo) ? {} : getHtmlStorage(storageVars.chatRequestInfo);
	var chatBoxesInfo = !statusHtmlStorage(storageVars.chatBoxes) ? {} : getHtmlStorage(storageVars.chatBoxes);
	var idx = callIdToChat.indexOf(callid);
	
	console.log(idx);
	console.log(callIdToChat);
	console.log(chatConversation);
	console.log(chatRequestInfo);
	console.log(chatBoxesInfo);
	try{
		callIdToChat.splice(parseInt(idx), 1);
		delete chatConversation[callid];
		delete chatRequestInfo[callid];	
		delete chatBoxesInfo[callid];	
		console.log(callIdToChat);
		console.log(chatConversation);
		console.log(chatRequestInfo);
		console.log(chatBoxesInfo);
		
		setHtmlStorage(storageVars.callIdToChat, JSON.stringify(callIdToChat), 24*3600);
		setHtmlStorage(storageVars.chatConversation, JSON.stringify(chatConversation), 24*3600);
		setHtmlStorage(storageVars.chatRequestInfo, JSON.stringify(chatRequestInfo), 24*3600);
		setHtmlStorage(storageVars.chatBoxesInfo, JSON.stringify(chatBoxesInfo), 24*3600);
	} catch(ex) {
		console.log("Web Chat Item Not Found: "+ex.message);
	}
}

function addMessageInStorage(msg, time){
	var callIdToChat = !statusHtmlStorage(storageVars.callIdToChat) ? [] : getHtmlStorage(storageVars.callIdToChat);
	var chatConversation = !statusHtmlStorage(storageVars.chatConversation) ? {} : getHtmlStorage(storageVars.chatConversation);
	var callid = '';

	try{
		if(msg.from != '' && msg.from.length > 4){
			callid = msg.from;
		}else if(msg.from != '' && msg.from.length == 4){
			callid = msg.callid;
		}

		var chatConversationValue = callid in chatConversation ? chatConversation[callid] : [];

		console.log('addMessageInStorage');
		console.log(msg);
		console.log(time);
		console.log(callIdToChat);
		console.log(chatConversation);
		console.log(callid in chatConversation);
		console.log(chatConversationValue);
		console.log(typeof chatConversationValue);
		
		msg.timestamp = time;
		chatConversationValue.push(msg);
		console.log(chatConversationValue);
		chatConversation[callid] = chatConversationValue;
		console.log(chatConversation);
		setHtmlStorage(storageVars.chatConversation, JSON.stringify(chatConversation), 24*3600);
	} catch(ex) {
		console.log("Web Chat Item Not Found: "+ex.message);
	}
}

function showWebChatHistory(callid){
	var callIdToChat = !statusHtmlStorage(storageVars.callIdToChat) ? [] : getHtmlStorage(storageVars.callIdToChat);
	var chatConversation = !statusHtmlStorage(storageVars.chatConversation) ? [] : getHtmlStorage(storageVars.chatConversation);
	var chatRequestInfo = !statusHtmlStorage(storageVars.chatRequestInfo) ? [] : getHtmlStorage(storageVars.chatRequestInfo);
	var chatConversationValue = callid in chatConversation ? chatConversation[callid] : [];
	
	console.log(chatConversationValue);
	console.log(callid);
	console.log($("#chatbox_"+callid));
	try{
		
		if(chatConversationValue.length > 0){
			chatConversationValue.forEach(function(item){
				console.log(item);
				var row = msgRow(item);		
				var $box = $("#chatbox_"+callid);
				var $boxBody = $box.find(".gcaw-body");		
				$boxBody.find("#gccw-typing").remove();
				$boxBody.append(row);
				$boxBody.scrollTop($boxBody[0].scrollHeight);
				//$box.find("textarea").blur();
				textReceivedArray = new Array();
			});
		}
	} catch(ex) {
		console.log("Web Chat Item Not Found: "+ex.message);
	}
}

function newChatBoxStorage(chatBoxes){
	console.log('NEW CHATBOX');
	console.log(chatBoxes);
	console.log(Object.keys(chatBoxes).length);
	
	try {
		if(Object.keys(chatBoxes).length > 0){
			setHtmlStorage(storageVars.chatBoxes, JSON.stringify(chatBoxes), 24*3600);
		}
	} catch(ex) {
		console.log("New Chatbox Store: "+ex.message);
	}
}




