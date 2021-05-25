var gPlexCCChatWSApi = (function() {	
	var _ws = null;
	_is_ws_ready = false;
	var url = "";
	var _connectWS = function() {
	
		if (_ws == null) {
		
			try {
			
				debug("URL: " + uri);
				//uri = 'ws://64.5.49.34:8180/chat';
				_ws = new WebSocket(uri);
				
				_ws.onopen = function () {
					
					debug("connected to ... " + uri, 'success'); // we are in! :D
					_is_ws_ready = true;
					//this.Login();
					//debug("Num LoggedIn: " + numLoggedIn);
					//var requestData = {
					//	softlogin: numLoggedIn == 0 ? '0' : '1'
					//};
					CallBackResponse("{\"method\":\"CONNECT\",\"status\":200,\"msg\":\"connected\"}");
					natPingInterval = setTimeout(NatPing,20000);
				};

				_ws.onmessage = function (evt) {
					debug("Received: " + evt.data);
					CallBackResponse(evt.data);
				};

				_ws.onclose = function (event) {};
				
			} catch(evt) {
				console.log(evt.message);
			}
		}
	};
	
	var _closeWS = function() {
	
		if (_ws != null) {
			_ws.close();
		}
	}
	
	var _performRequest = function(requestMethod, requestData, callback) {

		var promiseFunction = function(resolve, reject) {

			_connectWS();
			
			
			function success(data) {
				if (resolve) {
					resolve(data);
				}
				if (callback) {
					callback(null, data);
				}
			}

			function failure() {
				if (reject) {
					reject(req);
				}
				if (callback) {
					callback(req, null);
				}
			}
			
			//var reqStr = "WEB " + requestMethod + "\r\nuser: " + agentId + 
			//	"\r\npass: " + token + "\r\n";
			
			var reqStr = "WEB " + requestMethod + "\r\n";
			
			for (var property in requestData) {
				reqStr += property + ": " + requestData[property] + "\r\n";
			}
			
			//CallBackResponse(requestMethod+"|200|34je884j4k57d8fj43h87d8d|438384834883434-3");
			
			if (_is_ws_ready) {
				debug("Sent: " + reqStr);
				_ws.send(reqStr);
			} else {
				debug("WS not ready");
				if (_ws == null) {
					_connectWS();
				}
			}

		}

		promiseFunction();
		return null;
	}
	
	var _checkParamsAndPerformRequest = function(requestMethod, requestData, options, callback) {

		var opt = {};
		var cb = null;

		if (typeof options === 'object') {
			opt = options;
			cb = callback;
		} else if (typeof options === 'function') {
			cb = options;
		}

		//requestData.params = _extend(requestData.params, opt);
		
		return _performRequest(requestMethod, requestData, cb);
	};
	
	function CallBackResponse(msg) {
		
		msg = msg.replace(/^\s+|\s+$/g, '');
		
		console.log("DATA: |"+msg+"|");
		
		if(msg=="KA" || msg.lenght<3) {
			return;
		}
		
		if(typeof(msg) === 'undefined') {
			//alert("No value");
			return;
		}
		
		//var msg = "200\r\n";

		var resp = JSON.parse(msg);//msg.split("|");

		switch(resp.method) {
			case "CONNECT":
				ChatJoin();
				break;
			
			case "CHAT_JOIN":
				var statusCode = resp.status;
				if(statusCode=="200") {
					clearTimeout(timeout);
					$.cookie("cCustomerWebKey",resp.web_key);
					$.cookie("cCustomerCallId",resp.call_id);
					welcomeMessageToCustomer("&nbsp;Finding free agent, Please wait",false);
				}
				break;
				
			case "CHAT_ACCEPT":
					conversationClosed = false;
					console.log(resp);
					var agent_name = typeof resp.agent_name!='undefined' ? resp.agent_name : "";
					$.cookie("cChatAgentName",agent_name);
					welcomeMessageToCustomer("&nbsp;Welcome to "+$.cookie("cCustomerSubjectText"),true);
				break;
				
			case "CHAT_TEXT":
					msgReceived(resp);
				break;
				
			case "CHAT_WRITE":
					textTypingAt = new Date();
					$.cookie("textTypingAt",textTypingAt);
					chatWriting();
				break;
				
			case "CHAT_CLOSE":
				conversationClosed = true;
				ChatClose();
				break;
				
		}
	}
	
	function debug(msg, type) {
		//$("#console").append('<p class="' + (type || '') + '">' + msg + '</p>');
		var d = new Date();
		console.log(d+": "+msg);
	};
	
	var gPlexCCChat = function() {};

	gPlexCCChat.prototype = {
		constructor: gPlexCCChatWSApi
	};
	//gPlexCCChat.prototype.loadSettings = function(url, ag, pass) {
	gPlexCCChat.prototype.loadSettings = function(url) {
		uri = url;
		//agentId = ag;
		//token = pass;
	};
	
	gPlexCCChat.prototype.Connect = function() {
		return _connectWS();
	};
	
	gPlexCCChat.prototype.Reg = function(data, callback) {
		/*
		if(natPingResponseCount>=3) {
			alert("Network connection error!");
			natPingResponseCount=0;
			return;
		}
		natPingResponseCount++;
		*/
		
		var requestData = {
			web_key: $.cookie("cCustomerWebKey"),
			call_id: $.cookie("cCustomerCallId"),
			data:data
		};
		
		return _checkParamsAndPerformRequest('CHAT_DATA', requestData, callback);
	};
	
	gPlexCCChat.prototype.Close = function() {
		return _closeWS();
	};
	
	gPlexCCChat.prototype.ChatText = function(message, callback) {
		
		var requestData = {
			web_key: $.cookie("cCustomerWebKey"),
			call_id: $.cookie("cCustomerCallId"),
			user: $.cookie("cCustomerName"),
			message:message
		};
		
		return _checkParamsAndPerformRequest('CHAT_TEXT', requestData, callback);
	};
	
	gPlexCCChat.prototype.ChatJoin = function(options, callback) {
		var requestData = {};
		console.log(options);
		requestData = $.extend(requestData,options);
		
		return _checkParamsAndPerformRequest('CHAT_JOIN', requestData, callback);
	};
	
	gPlexCCChat.prototype.ChatClose = function(callback) {
		
		var requestData = {
			web_key: $.cookie("cCustomerWebKey"),
			call_id: $.cookie("cCustomerCallId")
		};
		
		return _checkParamsAndPerformRequest('CHAT_CLOSE', requestData, callback);
	};
	
	gPlexCCChat.prototype.SendTestResponse = function(msg) {
		CallBackResponse(msg);
    };
	
	return gPlexCCChat;
	
})();