var gPlexCCDashboardWSApi = (function() {	
	var uri = '';
	var agentId = '';
	var seatId = '';
	var webKey = '';
	var numLoggedIn = 0;
	var _ws = null;
	var _tag = '';
	var _is_ws_ready = false;
	var _do_not_retry = false;
	var _connectWS = function() {
                        	
		if (_ws == null) {
		
			try {
				//uri = 'ws://64.5.49.34:8180/chat';
				_ws = new WebSocket(uri);
				
				_ws.onopen = function () {
					
					debug("connected to ... " + uri, 'success'); // we are in! :D
					_is_ws_ready = true;
					//this.Login();
					
					//CallBackResponse("{\"method\":\"CONNECT\",\"status\":200,\"msg\":\"connectled\"}");
					CallBackResponse("CONNECT|200|connected");
					debug("Num LoggedIn: " + numLoggedIn);
					var exTag = $.cookie("dTag");
					
					if (exTag != null && exTag.length > 0) {
					    var requestData = {
					        tag: exTag
					    };
					} else {
                                            var requestData = {};
					}
					_checkParamsAndPerformRequest('DB_LOGIN', requestData, function(err, data){});
				};

				_ws.onmessage = function (evt) {
					debug("Received: " + evt.data);
					CallBackResponse(evt.data);
				};
				
				_ws.onerror = function (error) {
				    _ws = null;
				    //natPingTimes += 4;
				    //NatPing();
				};

				_ws.onclose = function (event) {
				    _ws = null;
				    _is_ws_ready = false;
				    console.log("Close Called 3");
				    if (natPingTimeout != null) {
				        clearTimeout(natPingTimeout);
				        natPingTimeout = null;
                                    }
				    if (!_do_not_retry) {
				        WebSockConnectError();
				        console.log("Close Called 4");
                                    }
                                    console.log("Close Called 5");
				    //_tag = '';
				    //$.cookie("dTag", "");
				};
				
			} catch(evt) {
				console.log(evt.message);
			}
		}
            
            //CallBackResponse('[{"S102":{"seat_id":"102","agent_id":"1001","status":"B","aux_code":0,"language_0":"EN","language_1":"b8","srv_id":"","srv_language":"","event_change_ts":1476490244}}]');
	};
	
	var _closeWS = function() {
	
		if (_ws != null && _ws.readyState === WebSocket.OPEN) {
		        console.log("Close Called 2");
		        _do_not_retry = true;
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
			
			var reqStr;
			if(requestMethod=="DB_LOGIN") {
			        reqStr = requestMethod + "\r\nid: " + agentId + "\r\n";
                        } else {
				reqStr = requestMethod + "\r\nid: " + agentId + "\r\n";
				if (_tag.length > 0) {
				    reqStr += "tag: " + _tag + "\r\n";
				}
			}
			//var reqStr = "WEB " + requestMethod + "\r\n";
			
			for (var property in requestData) {
				reqStr += property + ": " + requestData[property] + "\r\n";
			}
			
			//CallBackResponse(requestMethod+"|200|34je884j4k57d8fj43h87d8d|438384834883434-3");
			
			if (_is_ws_ready) {
			        reqStr = "ws_type: 102|ws_data: " + reqStr;
				debug("Sent: " + reqStr);
				_ws.send(reqStr);
				if(requestMethod=="DB_LOGOUT") {
					clearTimeout(natPingTimeout);
				}
			} else {
				debug("WS not ready");
				if (_ws == null) {
				    debug("Trying to connect WS");
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
		
		try {
		    var jsonData = JSON.parse(msg);
		    var jkey = '';
		    var rMsg = '';
		    var rId = '';
		    var rObj = null;
		    for (var _i=0;_i<jsonData.length;_i++) {
		        //console.log(jsonData[_i]);
		        for (jkey in jsonData[_i]) {
		            rMsg = jkey.substring(0, 1);
		            rId = jkey.substring(1);
		            rObj = jsonData[_i][jkey];
		            switch (rMsg) {
		                case "C":
		                    console.log(rObj.type);
		                    if (rObj.type == "DB_LOGIN") {
		                        if (rObj.status == 480) {
                                                _DashBoard.Close();
                                                return;
                                        } else if(rObj.status == 405) {
                                            $.cookie("dTag", "");
                                            alert("Maximum number of users already logged in dashboard.");
                                            _DashBoard.Close();
                                            return;
                                        } else if(rObj.status == 404) {
                                            //console.log("404 received");
                                            $.cookie("dTag", "");
                                            alert("You have logged in from another browser.");
                                            _DashBoard.Close();
                                            return;
                                            /*
                                            var exTag = $.cookie("dTag");
                                            //console.log(exTag);
                                            if (exTag != null && exTag.length > 0) {
                                                $.cookie("dTag", "");
                                                 var requestData = {};
                                                 //console.log("404 login");
                                                _checkParamsAndPerformRequest('DB_LOGIN', requestData, function(err, data){});
                                            } else {
                                                alert("Transaction does not exists.");
                                                _DashBoard.Close();
                                            }
                                            */
                                        } else if(rObj.status == 200) {
                                                _tag = rObj.tag;
                                                $.cookie("dTag", rObj.tag);
                                                natPingTimeout = setTimeout(NatPing, 20000);
                                                isDBLoggedIn=true;
                                                //rObj.ts = 1477068607;
                                                if (rObj.ts) _DashBoard.UpdateDBTime(rObj.ts);
                                        } else {
                                                alert("Transaction does not exists.");
                                                _DashBoard.Close();
                                                return;
                                        }
                                        if(isDBLoggedIn) numLoggedIn++;
                                        $.ajax({
                                                type: "POST",
                                                url: dbLoggedUrl
                                        });
		                    } else if (rObj.type == "DB_PONG") {
		                        _DashBoard.UpdateDBTime(rObj.ts);
		                    } else if (rObj.type == "RELOAD") {
		                        window.location.href=window.location.href;
		                    }
		                    break;
                                case "I":
                                    _DashBoard.PopulateIvr(rObj);
                                    break;
                                case "O":
                                    _DashBoard.PopulateOBSkill(rObj);
                                    break;
                                case "Q":
                                    _DashBoard.PopulateSkill(rId, rObj);
                                    break;
                                case "S":
                                    //console.log(rId);
                                    _DashBoard.PopulateSeat(rId, rObj);
                                    break;
		            }
		        }
                    }
                    return;
		} catch (e) { }
		
	}
	
	function debug(msg, type) {
		//$("#console").append('<p class="' + (type || '') + '">' + msg + '</p>');
		var d = new Date();
		console.log(d+": "+msg);
	};
	
	var gPlexCCDashboard = function() {};

	gPlexCCDashboard.prototype = {
		constructor: gPlexCCDashboardWSApi
	};
	
	gPlexCCDashboard.prototype.loadSettings = function(url, ag, seat, wk) {
		uri = url;
		agentId = ag;
		seatId = seat;
		webKey: $.cookie("wsWebKey");
	};
	
	gPlexCCDashboard.prototype.setNumLoggedIn = function(count) {
		console.log(count);
		numLoggedIn = count;
	};
	
	gPlexCCDashboard.prototype.isRetryNeeded = function() {
	    return _do_not_retry ? false : true;
	};
	
	gPlexCCDashboard.prototype.Ping = function(callback) {
		var requestData = {
			//db_ses_index: $.cookie("db_ses_index")
		};
		return _checkParamsAndPerformRequest('DB_PING', requestData, callback);
	};
	
	gPlexCCDashboard.prototype.Connect = function() {
		return _connectWS();
	};
	
	gPlexCCDashboard.prototype.Close = function() {
	        console.log("Close Called");
		return _closeWS();
	};
	
	/*
	gPlexCCDashboard.prototype.IsConnected = function(callback) {
	    return _ws != null;
	};
	*/
	
	gPlexCCDashboard.prototype.Login = function(callback) {
		var requestData = {
		
		};
		return _checkParamsAndPerformRequest('DB_LOGIN', requestData, callback);
	};
	
	gPlexCCDashboard.prototype.Logout = function(callback) {
	        var exTag = $.cookie("dTag");
	        $.cookie("dTag", "");
	        _tag = '';
		var requestData = {
			tag: exTag
		};
		return _checkParamsAndPerformRequest('DB_LOGOUT', requestData, callback);
	};
	
	gPlexCCDashboard.prototype.Load = function(callback) {
		var requestData = {
			dlg:2
		};
		return _checkParamsAndPerformRequest('DB_LOAD', requestData, callback);
	};
	gPlexCCDashboard.prototype.MemberInfo = function(agent_id, time, callback) {
		var requestData = {
			agent_id: agent_id,
			time: time
		};
		return _checkParamsAndPerformRequest('DB_INFO', requestData, callback);
	};
	gPlexCCDashboard.prototype.Listen = function(page, agent_id, menu_aid, callback) {
		var requestData = {
			page:page, 
			agent_id:agent_id, 
			option:menu_aid
		};
		return _checkParamsAndPerformRequest('DB_LISTEN', requestData, callback);
	};
	gPlexCCDashboard.prototype.TestResponse = function(msg) {
		CallBackResponse(msg);
    };
	
	return gPlexCCDashboard;
	
})();

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
