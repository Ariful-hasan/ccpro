var gPlexCCChatWSApi = (function() {
	var _ws = null;
	_is_ws_ready = false;
	var url = "";

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
			dataType: "json"
		});
	};
	
	function WSReconnect() {
		obj.Connect();
	}
		
	var _connectWS = function() {
		if (_ws == null) {
			try {
				debug("URL: " + uri);
				//uri = 'ws://64.5.49.34:8180/chat';
				_ws = new WebSocket(uri);

				_ws.onopen = function () {
					debug("connected to ... " + uri, 'success'); // we are in! :D
					_is_ws_ready = true;
					noInternetConnection = false;
					//this.Login();
					//debug("Num LoggedIn: " + numLoggedIn);
					//var requestData = {
					//      softlogin: numLoggedIn == 0 ? '0' : '1'
					//};
					CallBackResponse("{\"method\":\"CONNECT\",\"status\":200,\"msg\":\"connected\"}");
					natPingInterval = setTimeout(NatPing,20000);
				};

				_ws.onmessage = function (evt) {
					debug("Received: " + evt.data);
					CallBackResponse(evt.data);
				};

				_ws.onerror = function (evt) {
					console.log(evt);
					let callId = $.cookie("cCustomerCallId");
					if(callId == null){
						callId = $.cookie("demoCallId");
						if(callId == null){							
							callId = Math.floor(Math.random() * 10000000000) + 1;
							$.cookie("demoCallId", callId);
						}
						let data = {callid: callId};
						$.ajax({
							url: baseUrl + "/socket_disconnect_data_receiver.php",
							type: "POST",
							dataType: "json",
							data: data,
							success: function (data) {
								console.log(data);
							}
						});
					}
				};

				_ws.onclose = function (event) {									
					noInternetConnection = true;
					debug("Socket closed!", 'error'); // the socket was closed (this could be an error or simply that there is no server)
					_ws = null;
					_is_ws_ready = false;
					
					$("#wsCCOverlay").show();									
					$("#wsCCOverlay .cell_3").click(function() {
						window.location.reload(true);
					});
					$("#wsCCOverlay .cell_4").click(function() {
						// console.log("chat_leave_text_form");
						$("#wsCCOverlay").hide();
						show_chat_leave_text_form();
					});
					
					clearInterval(try_timeout);
					try_timeout = setInterval(function (){
						console.log(countdown_sec_limit_to_try);
						if(countdown_sec_limit_to_try==0) {
							WSReconnect();
							max_sec_limit_to_try += 10;
							countdown_sec_limit_to_try = max_sec_limit_to_try;
						} else {
							countdown_sec_limit_to_try--;
						}
						console.log(countdown_sec_limit_to_try);
						
						$("#wsCCOverlay .cell_2").html(countdown_sec_limit_to_try+"s");
						//try_timeout = setTimeout(UpdateSecondLimitToRetryConnect,1000);
					},1000);
					
				}
			} catch(evt) {
				// console.log(evt.message);
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
			//      "\r\npass: " + token + "\r\n";

			var reqStr = "WEB " + requestMethod + "\r\n";

			for (var property in requestData) {
				reqStr += property + ": " + requestData[property] + "\r\n";
			}

			//CallBackResponse(requestMethod+"|200|34je884j4k57d8fj43h87d8d|438384834883434-3");

			if (_is_ws_ready) {
				//debug("Sent: " + reqStr);
				reqStr = "ws_type: 101|ws_data: " + reqStr;
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

		// console.log("DATA: |"+msg+"|");

		if(msg=="KA" || msg.lenght<3) {
			return;
		}

		if(typeof(msg) === 'undefined') {
			//alert("No value");
			return;
		}

		//var msg = "200\r\n";

		var resp = JSON.parse(msg);//msg.split("|");
		let log_data = [];

		switch(resp.method) {
			case "CONNECT":
				ChatJoin();
				break;

			case "CHAT_JOIN":

				//16-09-2020
				log_data = [];
				log_data.push({"Type": "CHAT_JOIN"});
				log_data.push({"Name": $.cookie("cCustomerName")});
				log_data.push({"Contact": $.cookie("cCustomerContactNumber")});
				log_data.push(resp);
				addLog(log_data);

				var statusCode = resp.status;
				if(statusCode=="200") {
					clearTimeout(timeout);
					debug("Cookie ==> " + $.cookie("cCustomerCallId") + " " + $.cookie("cCustomerWebKey"));
					if (resp.call_id == $.cookie("cCustomerCallId") && resp.web_key == $.cookie("cCustomerWebKey")) {
						// endpoint_status: Agent Ring = 17, Agent Serving = 18, Queue = 9
						debug("Old Status=" + resp.endpoint_status);
						if (resp.endpoint_status == 18) {
							debug("18 ......");
							conversationClosed = false;											 
							showWebchatHistoryFlag = true;
							welcomeMessageToCustomer("", true);
						} else {
							//EnterChatDetailLog(resp.call_id);
							//newChatStore(resp);
							
							WebChatItemNotFound(resp.call_id);
						}
					} else {                                        
						debug("New ==> " + resp.call_id +  " " + resp.web_key);
						$.cookie("cCustomerWebKey",resp.web_key);
						$.cookie("cCustomerCallId",resp.call_id);
					
						UpdateChatLog(resp.call_id);
						EnterChatDetailLog(resp.call_id);
						//welcomeMessageToCustomer("&nbsp;Finding free agent, Please wait",false);
						newChatStore(resp);
					}
					
					//$.cookie("cCustomerWebKey",resp.web_key);
					//$.cookie("cCustomerCallId",resp.call_id);
					//UpdateChatLog(resp.call_id);
					//EnterChatDetailLog(resp.call_id);
					//welcomeMessageToCustomer("&nbsp;Finding free agent, Please wait",false);
				}else{
					WebChatItemNotFound(resp.call_id);
				}
				break;

			case "CHAT_ACCEPT":

				//16-09-2020
				log_data = [];
				log_data.push({"Type": "CHAT_ACCEPT"});
				log_data.push({"Name": $.cookie("cCustomerName")});
				log_data.push({"Contact": $.cookie("cCustomerContactNumber")});
				log_data.push(resp);
				addLog(log_data);

				conversationClosed = false;
				// console.log(resp);
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
				ChatClose(resp.disc_party);
				break;

		}
	}

	function debug(msg, type) {
		$("#console").append('<p class="' + (type || '') + '">' + msg + '</p>');
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
		//console.log(options);
		requestData = $.extend(requestData,options);

		return _checkParamsAndPerformRequest('CHAT_JOIN', requestData, callback);
	};

	gPlexCCChat.prototype.ChatClose = function(callback) {

		var requestData = {
			web_key: $.cookie("cCustomerWebKey"),
			call_id: $.cookie("cCustomerCallId")
		};
		//console.log('Chat Close');
		//console.log(requestData);
		return _checkParamsAndPerformRequest('CHAT_CLOSE', requestData, callback);
	};

	gPlexCCChat.prototype.SendTestResponse = function(msg) {
		CallBackResponse(msg);
    };

	return gPlexCCChat;

})();
