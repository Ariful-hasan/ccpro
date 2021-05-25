var gPlexCCWSApi = (function() {

	var uri = '';
	var agentId = '';
	var token = '';
	var numLoggedIn = 0;
	var isCrm = false;

	var _ws = null;
	var _is_ws_ready = false;
	var _promiseImplementation = null;
	var _is_ws_closed = false;

	function WSReconnect() {
		obj.Connect();
	}

	var _connectWS = function() {

		if (_ws == null) {

			try {

				_ws = new WebSocket(uri);

				_ws.onopen = function () {
					debug("connected ... ", 'success'); // we are in! :D
					_is_ws_ready = true;
					//this.Login();
					debug("Num LoggedIn: " + numLoggedIn);
					var requestData;
					if(numLoggedIn == 0) {
						requestData = {
							softlogin: numLoggedIn == 0 ? '0' : '1'
						};
					} else {
						requestData = {
							softlogin: numLoggedIn == 0 ? '0' : '1',
							web_key: cookieValue.webKey
						};
					}
					_checkParamsAndPerformRequest('LOGIN', requestData, function(err, data){});

					$("#wsCCOverlay").remove();
					clearInterval(try_timeout);

					noInternetConnection = false;

					//setTimeout(NatPing,55000);

					//console.log(jQuery.parseJSON($.cookie("wsCCListData")));
				};

				_ws.onmessage = function (evt) {
					//debug(evt.data, 'response'); // we got some data - show it omg!!
					//console.log(evt);
					debug("Received: " + evt.data);
					//success(evt.data);
					CallBackResponse(evt.data);
				};

				_ws.onclose = function (event) {
					noInternetConnection = true;
					debug("Socket closed!", 'error'); // the socket was closed (this could be an error or simply that there is no server)
					_ws = null;
					_is_ws_ready = false;
					_is_ws_closed = true;


					//alert(JSON.stringify(event));
					var reason;
					// See http://tools.ietf.org/html/rfc6455#section-7.4.1
					if (event.code == 1000)
						reason = "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
					else if(event.code == 1001)
						reason = "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
					else if(event.code == 1002)
						reason = "An endpoint is terminating the connection due to a protocol error";
					else if(event.code == 1003)
						reason = "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
					else if(event.code == 1004)
						reason = "Reserved. The specific meaning might be defined in the future.";
					else if(event.code == 1005)
						reason = "No status code was actually present.";
					else if(event.code == 1006)
						reason = "The connection was closed abnormally, e.g., without sending or receiving a Close control frame";
					else if(event.code == 1007)
						reason = "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [http://tools.ietf.org/html/rfc3629] data within a text message).";
					else if(event.code == 1008)
						reason = "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
					else if(event.code == 1009)
						reason = "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
					else if(event.code == 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
						reason = "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake. <br /> Specifically, the extensions that are needed are: " + event.reason;
					else if(event.code == 1011)
						reason = "A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.";
					else if(event.code == 1015)
						reason = "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
					else
						reason = "Unknown reason";

					//if($("#wsCCOverlay").length==0 && numLoggedIn>0) {
					if($("#wsCCOverlay").length==0) {
						$("<div/>").attr("id","wsCCOverlay")
							.appendTo("body")
							.html("<span class='cell_1'>Not connected.</span> Connecting in <span class='cell_2'>10s</span> <a href='javascript://' class='cell_3''>Try Now</a>.");
					}

					$("#wsCCOverlay .cell_3").click(function() {
						WSReconnect();
					});
					//UpdateSecondLimitToRetryConnect();
					clearInterval(try_timeout);
					try_timeout = setInterval(function (){
						if(countdown_sec_limit_to_try==0) {
							WSReconnect();
							max_sec_limit_to_try += 10;
							countdown_sec_limit_to_try = max_sec_limit_to_try;
						} else {
							countdown_sec_limit_to_try--;
						}

						$("#wsCCOverlay .cell_2").html(countdown_sec_limit_to_try+"s");
						//try_timeout = setTimeout(UpdateSecondLimitToRetryConnect,1000);
					},1000);
				};
			} catch(evt) {
				//console.log(evt.message);
			}
		}


	};

	var _promiseProvider = function(promiseFunction, onAbort) {
		return null;
	};

	var _performRequest = function(requestMethod, requestData, callback) {

		/*
		try {
			ws.send(str);
		} catch (err) {
			debug(err, 'error');
		}
		*/
		var promiseFunction = function(resolve, reject) {

			//console.log(settings.url);
			//if (_ws == null) _ws = new WebSocket(this.uri);
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

			var reqStr = "WEB " + requestMethod + "\r\nuser: " + agentId +
				"\r\npass: " + token + "\r\n";

			for (var property in requestData) {
				reqStr += property + ": " + requestData[property] + "\r\n";
			}

			if (_is_ws_ready) {
				reqStr = "ws_type: 101|ws_data: " + reqStr;
				debug("Sent: " + reqStr);
				_ws.send(reqStr);
			} else {
				debug("WS not ready");
				if (_ws == null) {
					//_ws = new WebSocket(this.uri);
					_connectWS();
				}
			}

		}
		/*
		if (callback) {
			promiseFunction();
			return null;
		} else {
			return _promiseProvider(promiseFunction, function() {
				req.abort();
			});
		}
		*/
		promiseFunction();
		return null;
	}

	var _checkParamsAndPerformRequest = function(requestMethod, requestData, options, callback) {

		//debug('_checkParamsAndPerformRequest: ' + requestMethod);

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

	var _extend = function() {
		var args = Array.prototype.slice.call(arguments);
		var target = args[0];
		var objects = args.slice(1);
		target = target || {};
		objects.forEach(function(object) {
			for (var j in object) {
				if (object.hasOwnProperty(j)) {
					target[j] = object[j];
				}
			}
		});
		return target;
	};

	function debug(msg, type) {
		//$("#console").append('<p class="' + (type || '') + '">' + msg + '</p>');
		var d = new Date();
		console.log(d+": "+msg);
	};

	function PinPreviewToCallFromIPPhone(pin_to_call) {
		$(" <div />" ).attr("id","pinPreview").attr("class","pin-preview")
			.html('Call to <span>'+pin_to_call+'</span> from Agent Phone.')
			.appendTo($( "body" ));
	}



	function CallBackResponse(msg) {

		msg = msg.replace(/^\s+|\s+$/g, '');

		if(msg=="KA") {
			return;
		}

		if(typeof(msg) === 'undefined') {
			//alert("No value");
			return;
		}

		var method;
		if(msg.indexOf("method")!==-1) {
			var resp = JSON.parse(msg);
			method = resp.method;
		} else {
			var resp_ary = msg.split("|");
			method = resp_ary[0];
		}

		//msg = trimChar(msg, "|");

		switch(method) {

			case "REG":
				natPingResponseCount = 0;
				if(resp_ary[3]=="200") {
					regResponseSuccess = true;
					if(resp_ary[2]=="1") {
						$("#seatStatusAck").remove();
						$("#pinPreview").remove();
						$ccdBody.removeClass("disabled").addClass("enabled");
						$dispositionButton.removeClass('hide').addClass("enabled");
						isLoggedIn = true;
						$.cookie("wsCCPin","");
					} else {
						SeatStatus(resp_ary[3],"Not Available");
					}
				} else if(resp_ary[3]=="481") {
					regResponseSuccess = false;
					var requestData = {
						softlogin: numLoggedIn == 0 ? '0' : '0'
					};
					_checkParamsAndPerformRequest('LOGIN', requestData, function(err, data){});
				} else {
					regResponseSuccess = false;
				}
				break;

			case "CALL":

				if (!isInCall){
					currentCallId = resp_ary[6];
				}

				var cWay = resp_ary[1];
				var cDid = resp_ary[2];
				var callerid = resp_ary[3];
				var cSkill = resp_ary[8];
				var cLang = resp_ary[9];
				var cHoldTime = resp_ary.length>10 ? resp_ary[11] : "";
				var callsInQ = resp_ary.length>11 ? resp_ary[12] : 0;
				var callsInService = resp_ary.length>12 ? resp_ary[13] : 0;
				var callsAuthenticated = resp_ary.length>13 ? resp_ary[14] : 0;
				var additionalArgs = resp_ary.length>14 ? resp_ary[15] : "";
				var footprint = resp_ary.length>15 ? resp_ary[16] : "";

				if(cWay==="IN") {
					try{
						notifyMe("Calling...", "You have a call from "+callerid);
					}catch(e) {
						console.log(e);
					}

				}

				LoadCall(resp_ary[5], resp_ary[4], resp_ary[6], cSkill, cLang, cDid, cHoldTime, callsInQ, callsInService, callsAuthenticated);

				try {
					fixNameCliForInboundAndOutbound(cWay);
				}catch (e) {
					debug(e.message)
				}

				if(resp_ary.length>9) {

					var url = resp_ary[10];
					if(!url) {
						url = "index.php?task=crm_in&act=details&param=AAA";
					}
					var skill = "";

					if (typeof url !== 'undefined' && url.length > 0) {

						if(url.indexOf("&param=") >=0 ) {
							try {
								var templateArr = [];
								templateArr["callid"] = resp_ary[6];

								var url_ary = url.split("&param=");
								templateArr["template_id"] = url_ary[1];

								if(cSkill.length>0) {
									var result = $.grep(skilListData, function(e){ return e.skill_id === cSkill; });
									var o = result[0];
									if (typeof o !== 'undefined') {
										templateArr["skill_name"] = typeof o.skill_name !== 'undefined' ? o.skill_name : "";
									} else {
										templateArr["skill_name"] = cSkill;
									}
								}
								templateArr["callerid"] = callerid;
								templateArr["altid"] = agentId;
								templateArr["agent_id"] = agentId;
								templateArr["language"] = cLang;
								templateArr["did"] = cDid;
								templateArr["caller_auth_by"] = parseInt(callsAuthenticated) === 1 ? "I" : "";

								additionalArgs = additionalArgs.replace(/(^;)|(;$)/g, "");// trim semicolon(;)
								var aArgsAry = [];
								aArgsAry = additionalArgs.split(";");
								if(aArgsAry.length > 0) {
									for(var i=0; i < aArgsAry.length; i++) {
										var arg = aArgsAry[i];
										var arg_ary = arg.split("=");
										var val_ary = typeof arg_ary[1] !== 'undefined' ? arg_ary[1].split(":") : [];
										if(typeof val_ary[0] !== 'undefined' && typeof val_ary[1] !== 'undefined') {
											var k = val_ary[0];
											templateArr[k] = val_ary[1];
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
							catch (e) {
								debug(e.message);
							}
						}
						console.log("PopUp URL: " + url);

						cookieValue.popup = {"cid":resp_ary[6], "cli":callerid, "url":url};
						$.cookie(cookieVars.popup, JSON.stringify(cookieValue.popup));

						try {
							IframeOpen(resp_ary[6], callerid, url, cLang, cSkill, cWay, cDid);
						}catch (e) {
							debug(e.message);
						}
					}
				}

				//if (inCallInfo) AnswerCall(inCallInfo.callId);
				console.log('footprint='+footprint);
				if (typeof footprint != 'undefined' && footprint.length>0) {
					LoadFootprint(currentCallId);
				} else {
					$("#footprintTitle").hide();
				}

				////Customer-journey
				if (typeof callerid != 'undefined' && callerid.length>0) {
					//LoadCustomerJourney(callerid);
					LoadCustomerJourney(resp_ary[6], callerid, cSkill);
				} else {
					$("#customer_journey").hide();
				}
				/////

				isInCall = false;
				CallSuccess();
				//}
				break;
			case "IN_SRV_CALL":
				if (resp_ary[1] == '200') {
					LoadCall(resp_ary[4], resp_ary[3], resp_ary[5], "", "", "", "", "", "","");
					isInCall = true;
					CallSuccess();
				}
				else
				{
					alert(msg);
				}
				break;
			case "RNG":
				currentCallId = resp_ary[2];
				//LoadCall(resp_ary[2]);
				break;

			case "CAN":
				//currentCallId = resp_ary[5];
				CancelCall(resp_ary[2]);
				//MissedCallAcknowledge();
				break;

			case "HANGUP":
				RemoveCall(resp_ary[2]);
				break;

			case "ANS":

				if (typeof resp_ary[2] !== 'undefined' && resp_ary[2].length>0) {
					SwCallStatusID = resp_ary[2];
					currentCallId = resp_ary[2];
					AnswerCall(resp_ary[2]);
				}
				break;

			case "BYE":
				//currentCallId = resp_ary[5];
				//HangupCall(resp_ary[2]);
				if (typeof resp_ary[2] !== 'undefined' && resp_ary[2].length>0) {
					SwCallStatusID = '';
					RemoveCall(resp_ary[2]);
				}
				break;

			case "HOLD":
				//currentCallId = resp_ary[5];
				isHoldedCall = true;
				var cid = resp_ary[1];
				if(cid==404) return;
				HoldCall(cid);
				break;

			case "UHOLD":
				//currentCallId = resp_ary[5];
				isHoldedCall = false;
				var cid = resp_ary[1];
				if(cid==404) return;
				UnHoldCall(cid);
				break;

			case "MIS":
				missedCall = true;
				//MissedCallAcknowledge();
				break;

			case "RETURN":
				ReturnCallAcknowledge();
				break;

			case "BSY":
			case "XFER_CAN":
				TransferCancel();
				break;

			case "SEAT_STATUS":
				SeatStatus(resp_ary[2],resp_ary[3]);
				break;

			case "LOGIN":
				//IframeOpen("http://www.genuitysystems.com");
				if(resp_ary[1] == 401) {

					clearTimeout(natPingInterval);

					$(".ccd").hide();
					var rary = resp_ary.length>2 ? resp_ary[2] : "";
					var text = resp_ary[1]+" "+rary;
					alert(text+", closing window...");
					Logout();

					//opener.location.reload();
					//window.location.href = wsSignInUrl;
					//window.close();

					/*
					var html = "Authentication failed please <span><a href='"+wsSignInUrl+"'>Try Again</a></span";
					$("#pinPreview").remove();
					$(" <div />" ).attr("id","pinPreview").attr("class","pin-preview")
					  .html(html)
					  .appendTo($( "body" ));
					 */

				} else if(resp_ary[1] == 200) {

					lastRegMsgSendAt = new Date();
					natPingInterval = setTimeout(NatPing,15000);//55000

					var pin_to_call = resp_ary[3];
					var pd_login_status = resp_ary[4];
					//$(".seat").html(seat_id);
					//thisPcMac = resp_ary[4];

					//resp_ary[6] = callid
					if (typeof resp_ary[6] !== 'undefined' && resp_ary[6].length>0) {
						SwCallStatusID = resp_ary[6];
					}

					if(typeof pin_to_call !== 'undefined' && pin_to_call.length>0)  {

						$.cookie("wsCCPin",pin_to_call);

						PinPreviewToCallFromIPPhone(pin_to_call);

						//RecordCurrentSCeq();
						//SendGUI("LIST","ASI");
						//cookieValue.selectedAuxId = "";
						//$.cookie(cookieVars.selectedAuxId,cookieValue.selectedAuxId);
						$("#btnAux").addClass("busy").removeClass("primary active").html("<span class='caption'>Busy</span><span class='drop'></span><div class='clear'></div>");
					} else {
						var pin_to_call = $.cookie("wsCCPin");

						if(pin_to_call!=null && pin_to_call.length>0) {
							PinPreviewToCallFromIPPhone(pin_to_call);
							//cookieValue.selectedAuxId = "";
							//$.cookie(cookieVars.selectedAuxId,cookieValue.selectedAuxId);
							$("#btnAux").addClass("busy").removeClass("primary active").html("<span class='caption'>Busy</span><span class='drop'></span><div class='clear'></div>");
							return;
						} else {
							isLoggedIn = true;
							$ccdBody.removeClass("disabled").addClass("enabled");
							$dispositionButton.removeClass('hide').addClass("enabled");
							loginSuccessCallback();

							ShowHoldUnholdTransferButton();
						}
						$("#seatId").html($.cookie("wsCCSeatId"));

						//var listData = jQuery.parseJSON($.cookie("wsCCListData"));
						//var listAgent = jQuery.parseJSON($.cookie("wsCCListAgent"));


						//auxListData = listData.AUX;
						//skilListData = listData.SKILL;
						//ivrListData = listData.IVR;
						//agentListData = listAgent;


					}

					SetAuxCode(resp_ary[7]);
					/*
if (typeof resp_ary[7] !== 'undefined' && resp_ary[7].length>0) {
        if (resp_ary[7] != '0') {
                var txtAUX = "Busy";
                try {
                var resultAUX = $.grep(auxListData, function(e){ return e.aux_code == resp_ary[7]; });
                var oAUX = resultAUX[0];
                txtAUX = oAUX.message;
            } catch (exception) {}

            $("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("<span class='caption'>"+txtAUX+"</span><span class='drop'></span><div class='clear'></div>");
    } else {
            $("#btnAux").addClass("primary").removeClass("busy active").html("<span class='caption'>Ready</span><span class='drop'></span><div class='clear'></div>");
    }
}
*/

					if(typeof pd_login_status !== 'undefined' && pd_login_status == '1')  {
						PDLoggedIn();
					}

					if(numLoggedIn==0) {
						RecordCurrentSCeq();
						SendGUI("LIST","ASI");
					}

					$(".ccd").show();
					if(isLoggedIn) numLoggedIn++;
					$.ajax({
						type: "POST",
						url: wsLoggedUrl
					});

					SyncCallStack();


				} else {

					clearTimeout(natPingInterval);

					$(".ccd").hide();

					var rary = resp_ary.length>2 ? resp_ary[2] : "";
					var text = resp_ary[1]+" "+rary;
					alert(text+", closing window...");
					Logout();


					//opener.location.reload();
					//window.location.href = wsSignInUrl;
					//window.close();
					/*
					var html = "Authentication failed please <span><a href='"+wsSignInUrl+"'>Try Again</a></span>";
					$("#pinPreview").remove();
					$(" <div />" ).attr("id","pinPreview").attr("class","pin-preview")
					  .html(html)
					  .appendTo($( "body" ));
					*/

				}
				break;

			case "LOGOUT":
				if(resp_ary[1]=="200") {
					clearTimeout(natPingInterval);
					Logout();
				}
				break;

			case "GUI":

				var data = atob(resp_ary[2]);

				var jsonData = jQuery.parseJSON(data);

				if( typeof (jsonData.Mothod) != undefined && jsonData.Mothod=="AUTH_SEAT") {
					isLoggedIn = true;

					$("#seatId").html(jsonData.Seat);
					$.cookie("wsCCSeatId",jsonData.Seat);
					$.cookie("wsCCPin","");
					$("#pinPreview").remove();
					cookieValue.webKey = jsonData.web_key;
					//cookieValue.webSiteKey = webSiteKey;
					$.cookie(cookieVars.webKey, cookieValue.webKey);
					RemoveCookie();

					if (jsonData.skillout.length > 0) {
						$.cookie(cookieVars.selectedSkillId, jsonData.skillout);
					}

					$ccdBody.removeClass("disabled").addClass("enabled");
					$dispositionButton.removeClass('hide').addClass("enabled");

					RecordCurrentSCeq();
					SendGUI("LIST","ASI");

					return;
				}


				var res_code = jsonData.ResCode;
				var sceq = jsonData.SCeq;

				var result = $.grep(SessionData, function(e){ return e.SCeq == sceq; });
				var type = result[0].Type;

				SessionData = jQuery.removeFromArray(result[0], SessionData);

				if(type=="ASI") {
					//$.cookie("wsCCListData",JSON.stringify(jsonData.data));
					/*
					auxListData = jsonData.data.AUX;
					//ShowAuxView(auxListData);
					skilListData = jsonData.data.SKILL;
					ivrListData = jsonData.data.IVR;
					*/
					//debug(">>>>>>>>>>>" + jsonData.data.web_key);
					RecordCurrentSCeq();
					//SendGUI("LIST","AGENT");

				} else if(type=="AGENT") {
					//agentListData = jsonData.data;
					//$.cookie("wsCCListAgent",JSON.stringify(jsonData.data));
				} else if(type=="SKILL") {
					skilListData = jsonData.data;
					ShowSkillView(jsonData.data);
				}
				break;
			case "DIAL":
				//alert("Call ID: "+resp_ary[2]);
				if(resp_ary[1]=="200") {
					currentCallId = resp_ary[2];
					//LoadCall(resp_ary[4], resp_ary[3], resp_ary[5], "", "", "", "", "", "");
					LoadCall("", "", currentCallId, "", "", "", "", "", "","");
				} else {
					alert(resp_ary[2]);
				}
				break;
			case "CHANGE_AUX":
				if(resp_ary[1]=="200 OK") {
					cookieValue.selectedAuxId = resp_ary[2];//tempAuxId;
					$.cookie(cookieVars.selectedAuxId,cookieValue.selectedAuxId);
					//logAgentAux(resp_ary[2], cookieValue.webKey);////log agent aux
					if(resp_ary[2] != "0") {
						if(resp_ary[2] == "21") {
							$("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("<span class='caption'>Bounced Call</span>");
						} else {
							var txt = "Busy";
							try {
								var result = $.grep(auxListData, function(e){ return e.aux_code == resp_ary[2]; });
								var o = result[0];
								txt = o.message;
							} catch (exception) {}

							$("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("<span class='caption'>"+txt+"</span><span class='drop'></span><div class='clear'></div>");

						}
					} else {
						$("#btnAux").addClass("primary").removeClass("busy active").html("<span class='caption'>Ready</span><span class='drop'></span><div class='clear'></div>");
					}
				}
				else if(resp_ary[1].substring(0,3)=="483") {
					//alert("Call in progress");
					$("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("Pending");
				}
				else if(resp_ary[1].substring(0,3)=="404") {
					alert("Failed to change AUX");
				}
				break;
			case "CHANGE_SKILL":
				if(resp_ary[1]=="200 OK") {
					cookieValue.selectedSkillId = tempSkillId;
					$.cookie(cookieVars.selectedSkillId,cookieValue.selectedSkillId);


					//if(selectedAuxId != "0") {
					//$("#btnAux").addClass("busy").removeClass("primary").removeClass("active").html("Busy");
					//} else {
					//$("#btnAux").addClass("primary").removeClass("busy active").html("<span class='caption'>Ready</span><span class='drop'></span><div class='clear'></div>");
					//}
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
			case "CALL_STATUS":
				//debug("Call Status: " + resp_ary[1]);
				if (resp_ary[1] == '404') {
					//RemoveCallFromCallsStack(resp_ary[3]);
					HangupCall(resp_ary[3]);
				} else if (resp_ary[1] == '200') {
					SwCallStatusID = resp_ary[3];
					//$.cookie(cookieVars.popup, {"cid":resp_ary[6],"cli":callerid,"url":url});
					if (cookieValue.popup.cid == SwCallStatusID) {
						IframeOpen(cookieValue.popup.cid, cookieValue.popup.cli, cookieValue.popup.url);
					}
				}
				break;
			case "CHAT_JOIN":
				console.log(resp);
				chatRequest(resp);				
				newChatStore(resp);
				break;
			case "CHAT_TEXT":
				if(resp.from_type === 'TEXT'){ // SMS service
					try{
                        var sender_id = resp.sender_id;
                        var session_id = sender_id.substring(5);
                        appendReceivedSMStoChatWindow(session_id, decodeURIComponent(escape(window.atob(resp.message))));
					}catch(e){
						console.log(resp);
					}
				}else{
                    chatTextReceived(resp);
				}
				break;
			case "CHAT_TRANSFER":
				if (resp_ary[1] == '200') {
					openTransferedChatBox();
				}
				break;
			case "CHAT_MONITOR":
				// console.log(resp);
				_Supervisor.chatMonitorResponse(resp);
				break;
			case "CHAT_CLOSE":
				_Chat.ChatClose(resp.call_id, true, resp.disc_party);
				break;

			case "TRANSFER":
				if (resp_ary[1] == '200') {
					CallTransferSuccess();
					RemoveCall(resp_ary[3]);
				} else {
					alert(resp_ary[2]);
				}
				break;
			case "CHAT_DATA":
				_Chat.ChatData(resp);
				break;
			case "PD_JOIN":
				if (resp_ary[1] == '200') {
					//PDStatus = 1;
					PDLoggedIn();
				}
				break;
			case "PD_EXIT":
				if (resp_ary[1] == '200') {
					//PDStatus = 0;
					PDLoggedOut();
				}
				break;
			case "PD_STATUS":
				PDStatusUpdate(resp_ary[3]);
				try {
					if (resp_ary[3].startsWith('100-Trying')){
						$("#callDuration, #cname, #cli, #skill, #holdTime, #lang").empty();
					}
				}catch(e){
					console.log(e);
				}

				var url = resp_ary[4];
				if (resp_ary[2] == '4' && url.length > 4) {
					if(Object.keys(pd_engines).length > 0 && pd_engines[resp_ary[1]] == 'PV')
					{
						pdPreviewDialStatus = 1;
						pdPreviewSkillId = resp_ary[1];
						OpenPdPreviewPrompt();
					}else {
						pdPreviewDialStatus = 0;
					}
					//IframeOpen(resp_ary[6], resp_ary[5], url);
					//OpenTargetUrl(url, resp_ary[6], resp_ary[1], resp_ary[5], agentId, "", "", "", "", isCrm);
				}
				break;
			case "EMAIL_JOIN":
                var type = resp.url;
                var url = type.substring(5);
                if(type.substring(0,4) == "TEXT"){
                    createSmsWindow(url, resp.call_id, resp.contact, resp.location);
                }else{
					var email_id = resp.url;
                	email_id = email_id.replace('MAIL/','');
                    IframeOpen(resp.call_id, "Email::"+email_id, baseUrl+'index.php?task=email&act=details&tid='+resp.url+'&callid='+resp.call_id+'&agent_id='+agentId);
                }

				break;
				
			case "NVC_STATUS":
				console.log("NVC_STATUS");
				console.log(resp);
				console.log(resp_ary);
				console.log(chatBoxes);
				console.log(Object.keys(chatBoxes).length);
				
				var chatBoxes_item = !statusHtmlStorage(storageVars.chatBoxes) ? {} : getHtmlStorage(storageVars.chatBoxes);				
				console.log(chatBoxes_item);
				if(resp_ary[3] in chatBoxes_item)
					existingChatBoxes(chatBoxes_item);
				else{
					delete chatBoxes_item[resp_ary[3]];
					setHtmlStorage(storageVars.chatBoxes, JSON.stringify(chatBoxes_item), 24*3600);
				}
				
				var nv_item = !statusHtmlStorage(storageVars.callIdToChat) ? [] : getHtmlStorage(storageVars.callIdToChat);
				var nv_item_resp = !statusHtmlStorage(storageVars.chatRequestInfo) ? [] : getHtmlStorage(storageVars.chatRequestInfo);
				var nv_item_idx = nv_item.indexOf(resp_ary[3]);				
				console.log(nv_item_resp[resp_ary[3]]);
				
				if (resp_ary[1] == '404') {
					console.log("NVC_STATUS= 404");
					console.log(cookieValue.callIdToChat == resp_ary[3]);
					
					if(cookieValue.callIdToChat == resp_ary[3])
						setTimeout(showWebChatHistory(resp_ary[3], resp), 3000);
					else
						setTimeout(WebChatItemNotFound(resp_ary[3]), 3000);
					
					setTimeout(_Chat.ChatClose(resp_ary[3], true), 3500);
				} else if (resp_ary[1] == '200') {
					console.log("NVC_STATUS= 200");
					
					if(nv_item.length > 0 && nv_item_idx > -1) {
						setTimeout(showWebChatHistory(resp_ary[3], resp), 3000);
					} else {
						console.log("NVC_STATUS= not found call id");
						setTimeout(showWebChatHistory(resp_ary[3], resp), 3000);
						setTimeout(_Chat.ChatClose(resp_ary[3], true), 3500);
					}
				}
				break;

			default:
				//alert("Nothing");
				break;
		}
	}



	var gPlexCC = function() {};

	gPlexCC.prototype = {
		constructor: gPlexCCWSApi
	};

	gPlexCC.prototype.loadSettings = function(url, ag, pass, curSuffix) {
		uri = url;
		agentId = ag;
		token = pass;
		if (curSuffix == 'AB') isCrm = false;
	};

	gPlexCC.prototype.setNumLoggedIn = function(count) {
		numLoggedIn = count;
	};

	gPlexCC.prototype.Dial = function(number, webKey, skill_id, callback) {
		var requestData = {
			dial_number: number,
			web_key: webKey,
			skill_id:skill_id
		};
		return _checkParamsAndPerformRequest('DIAL', requestData, callback);
	};

	gPlexCC.prototype.Call = function(number, callid, webKey, callback) {
		var requestData = {
			call_to: number,
			call_id: callid,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('CALL', requestData, callback);
	};

	gPlexCC.prototype.Authenticated = function(callid, webKey, callback) {
		var requestData = {
			call_id: callid,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('CUST_AUTH', requestData, callback);
	};

	gPlexCC.prototype.InServiceCall = function(number, callid, webKey, callback) {
		var requestData = {
			call_to: number,
			call_id: callid,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('IN_SRV_CALL', requestData, callback);
	};

	gPlexCC.prototype.Connect = function() {
		return _connectWS();
	};

	gPlexCC.prototype.Login = function(callback) {
		var requestData = {};
		return _checkParamsAndPerformRequest('LOGIN', requestData, callback);
	};

	gPlexCC.prototype.Logout = function(webKey, callback) {//seatId, callback
		var requestData = {
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('LOGOUT', requestData, callback);
	};

	gPlexCC.prototype.PD_Action = function(status, skillId, webKey, callback) {
		if (status == 0) {
			var action = 'PD_JOIN';
			var requestData = {
				skill_id: skillId,
				web_key: webKey
			}
		} else {
			var action = 'PD_EXIT';
			var requestData = {
				web_key: webKey
			}
		}
		return _checkParamsAndPerformRequest(action, requestData, callback);
	};

	gPlexCC.prototype.Hangup = function(callId, webKey, callback) {
		var requestData = {
			call_id: callId,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('HANGUP', requestData, callback);
	};

	gPlexCC.prototype.Answer = function(callId, webKey, callback) {
		var requestData = {
			call_id: callId,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('ANSWER', requestData, callback);
	};

	gPlexCC.prototype.ChangeAUX = function(auxId, webKey, callback) {
		var flagAux = checkChangeAUXTime(auxId, webKey);
		console.log("ChangeAUX: "+flagAux);
		
		if(flagAux){
			var requestData = {
				aux_type: auxId,
				web_key: webKey
			};
			
			return _checkParamsAndPerformRequest('CHANGE_AUX', requestData, callback);
		}else{
			$.cookie("ccAuxModeDateTime", new Date());
			setTimeout(function(){
				var requestData = {
					aux_type: auxId,
					web_key: webKey
				};
				
				return _checkParamsAndPerformRequest('CHANGE_AUX', requestData, callback);
			}, 2000);
		}
	};

	/*
	gPlexCC.prototype.ChangeSkill = function(skillId, webKey, callback) {
		var requestData = {
			skill_id: skillId,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('CHANGE_SKILL', requestData, callback);
	};*/

	gPlexCC.prototype.ChangeSkill = function(skillId, webKey, callback) {
		var requestData = {
			skill: skillId
		};
		return _checkParamsAndPerformRequest('OB_SKILL', requestData, callback);
	};

	gPlexCC.prototype.LoadSkill = function(webKey, callback) {
		var requestData = {
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('LOAD_SKILL', requestData, callback);
	};

	gPlexCC.prototype.LoadIVR = function(webKey, callback) {
		var requestData = {
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('LOAD_IVR', requestData, callback);
	};

	gPlexCC.prototype.LoadAUX = function(webKey, callback) {
		var requestData = {
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('LOAD_AUX', requestData, callback);
	};

	gPlexCC.prototype.SendGUI = function(data, webKey, callback) {
		var requestData = {
			message: data,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('GUI', requestData, callback);
	};

	gPlexCC.prototype.Transfer = function(callId, destCallId, webKey, callback) {
		var requestData = {
			transfer_to: destCallId,
			call_id: callId,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('TRANSFER', requestData, callback);
	};

	gPlexCC.prototype.TransferCancel = function(callId, webKey, callback) {
		var requestData = {
			call_id: callId,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('XFER_CANCEL', requestData, callback);
	};

	gPlexCC.prototype.Hold = function(callId, seatId, webKey, callback) {
		var requestData = {
			call_id: callId,
			seat: seatId,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('HOLD', requestData, callback);
	};

	gPlexCC.prototype.UnHold = function(callId, seatId, webKey, callback) {
		var requestData = {
			call_id: callId,
			seat: seatId,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('UHOLD', requestData, callback);
	};


	gPlexCC.prototype.SendMessage = function(toUser, message, webKey, callback) {
		var requestData = {
			to_user: toUser,
			message: message,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('SENDMSG', requestData, callback);
	};

	gPlexCC.prototype.Reg = function(webKey, callback) {

		var ts = Math.round((new Date()).getTime() / 1000);

		if (ts < (lastNatPingTime+17)) {
			return false;
		}

		if(!isLoggedIn) {
			if (_is_ws_ready) {
				var reqStr = "ws_type: 103|ws_data: WS_PING";
				debug("Sent: " + reqStr);
				_ws.send(reqStr);
			}
			return;
		}

		if(natPingResponseCount > 10) {
			window.location.reload();
			/*alert("Network connection error!");
			natPingResponseCount=0;
			return;*/
		}
		natPingResponseCount++;

		var requestData = {
			web_key: webKey
		};
		lastNatPingTime = ts;
		return _checkParamsAndPerformRequest('REG', requestData, callback);
	};

	gPlexCC.prototype.MissAck = function(webKey, callback) {
		var requestData = {
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('MIS_ACK', requestData, callback);
	};

	gPlexCC.prototype.CallStatus = function(callId, webKey, callback) {
		var requestData = {
			call_id: callId,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest('CALL_STATUS', requestData, callback);
	};

	gPlexCC.prototype.ChatAccept = function(webKeyToChat, callIdToChat, callback) {
		var requestData = {
			web_key: webKeyToChat,
			call_id: callIdToChat
		};
		return _checkParamsAndPerformRequest('CHAT_ACCEPT', requestData, callback);
	};

	gPlexCC.prototype.ChatText = function(callIdToChat, webKeyToChat, message, callback) {
		var requestData = {
			call_id: callIdToChat,
			web_key: webKeyToChat,
			message: message
		};
		return _checkParamsAndPerformRequest('CHAT_TEXT', requestData, callback);
	};

	gPlexCC.prototype.SendChatData = function(callIdToChat, webKeyToChat, message, callback) {

		var requestData = {
			call_id: callIdToChat,
			web_key: webKeyToChat,
			data: message
		};
		return _checkParamsAndPerformRequest('CHAT_DATA', requestData, callback);
	};

	gPlexCC.prototype.ChatTransfer = function(callIdToChat, webKeyToChat, transferTo, callback) {
		var requestData = {
			call_id: callIdToChat,
			web_key: webKeyToChat,
			transfer_to: transferTo
		};
		return _checkParamsAndPerformRequest('CHAT_TRANSFER', requestData, callback);
	};

	gPlexCC.prototype.ChatTransferConfirm = function(callIdToChat, webKeyToChat, transferTo, callback) {
		var requestData = {
			call_id: callIdToChat,
			web_key: webKeyToChat,
			transfer_to: transferTo
		};
		return _checkParamsAndPerformRequest('CHAT_XFER', requestData, callback);
	};

	gPlexCC.prototype.ChatConferance = function(conf_user, webKeyToChat, call_id, callback) {
		var requestData = {
			call_id: call_id,
			web_key: webKeyToChat,
			conf_user: conf_user
		};
		return _checkParamsAndPerformRequest('CHAT_CONF', requestData, callback);
	};

	gPlexCC.prototype.ChatWriting = function(webKeyToChat, callIdToChat, callback) {
		var requestData = {
			web_key: webKeyToChat,
			call_id: callIdToChat
		};
		return _checkParamsAndPerformRequest('CHAT_WRITE', requestData, callback);
	};

	gPlexCC.prototype.ChatMonitor = function(mon_agent, webKeyToChat, callback) {
		var requestData = {
			mon_agent: mon_agent,
			web_key: webKeyToChat
		};
		return _checkParamsAndPerformRequest('CHAT_MONITOR', requestData, callback);
	};

	gPlexCC.prototype.ChatPickUp = function(callIdToChat, webKeyToChat, callback) {
		var requestData = {
			call_id: callIdToChat,
			web_key: webKeyToChat
		};
		return _checkParamsAndPerformRequest('CHAT_PICKUP', requestData, callback);
	};

	gPlexCC.prototype.ChatMonitorClose = function(callIdToChat, webKeyToChat, callback) {
		var requestData = {
			call_id: callIdToChat,
			web_key: webKeyToChat
		};
		return _checkParamsAndPerformRequest('CHAT_MONCLOSE', requestData, callback);
	};

	gPlexCC.prototype.ChatClose = function(callIdToChat, webKeyToChat, callback) {

		var requestData = {
			web_key: webKeyToChat,
			call_id: callIdToChat
		};

		return _checkParamsAndPerformRequest('CHAT_CLOSE', requestData, callback);
	};

	gPlexCC.prototype.SendChatJoin = function(msg) {
		CallBackResponse(msg);
	};

	gPlexCC.prototype.PD_Number_Dial = function(dial_number, skillId, webKey, callback) {
		var action = dial_number == true ? 'PD_DIAL' : 'PD_SKIP';
		var requestData = {
			skill_id: skillId,
			web_key: webKey
		};
		return _checkParamsAndPerformRequest(action, requestData, callback);
	};

	gPlexCC.prototype.NVCStatus = function(callId, webKey, callback) {
		var requestData = {
			call_id: callId,
			web_key: webKey
		};
		console.log("prototype of NVCStatus");
		console.log(requestData);
		return _checkParamsAndPerformRequest('NVC_STATUS', requestData, callback);
	};

	return gPlexCC;

})();