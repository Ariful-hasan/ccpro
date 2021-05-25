<html>
<head>
<title><?php echo $pageTitle;?></title>

<script type="text/JavaScript" src="js/jquery.min.js"></script>

<script type="text/javascript" src="js/jquery-ui.min.js"></script>

<script src="js/contextMenu/jquery.ui.position.js" type="text/javascript"></script>
<script src="js/contextMenu/jquery.contextMenu.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/contextMenu/jquery.contextMenu.css">

<script src="ccd/gPlexCCDashboardWS.js" type="text/javascript"></script>

<?php $is_expanded_db = "Y"; ?>
<script type="text/JavaScript">


var staffin = 0;
var staffout = 0;
var allcalls = 0;
var inq = 0;
var natPingTimeout = null;
var isDBLoggedIn = false;
var dbLoggedUrl = "<?php echo $this->url("task=agent&act=add-dashboard-logged-in");?>";

//var ivrCallsStack = [];
//var inQueueCallsStack = [];
//var inServiceCallsStack = [];

var callsStack = [];
var outCallsStack = [];
var dimvalue = '<span class="dim-value">0</span>';

var obj = null;
			
$(document).ready(function(){

	$(document).bind("contextmenu",function(e){
        e.preventDefault();
    });
	$(document).disableSelection();

	var tstamp = new Date().getTime();
	var is_agentinfo_visible = false;

	$(".seat div.agent").hover(
		function (event) {

			var tPosX = event.pageX - 5;
			var tPosY = event.pageY + 20;
			$('#agent_queue_info').css({top: tPosY, left: tPosX}); 
			agent_queue_info_display = $(this).attr('agent');
			tstamp++;
			
			if (!is_agentinfo_visible) {
				is_agentinfo_visible = true;
				$('#agent_queue_info').html('<div class="loading">Loading....</div>');
				$('#agent_queue_info').css('display','block');
				
				obj.MemberInfo(agent_queue_info_display, tstamp);
			}

		}, function () {
			$('#agent_queue_info').css('display','none');
			is_agentinfo_visible = false;
		}
	);

	/*
    $.contextMenu({
        selector: '.agentpic', 
        callback: function(key, options) {
			var menu_aid = options.$trigger.attr('agent');
			menu_aid = key == 'W' ? menu_aid + 'W' : menu_aid;
			
			obj.Listen('dashboard', dashboarduser, menu_aid);
			
        },
        items: {
            "L": {
				name: "Listen", 
				icon: "listen", 
				disabled: function(key, opt) { 
					return !opt.$trigger.hasClass('serving');
                }
			},
			"sep1": "---------",
            "W": {name: "Whisper", icon: "whisper", disabled: function(key, opt) { 
					return !opt.$trigger.hasClass('serving');
			}}
        }
    });*/
});

var max_calls_in_q = 6;
var agent_queue_info_display = null;
var queue_detail_info_display = null;
var ivr_detail_info_display = null;
var available_services = new Array();
var available_agents = new Array();
var available_aux_msgs = new Array();
var objSkills = [];
var objAgents = [];
var objAuxs = [];

var dashboarduser = '<?php echo UserAuth::getCurrentUser();?>';
<?php if (is_array($skills)):?>
objSkills = jQuery.parseJSON('<?php echo json_encode($skills);?>');
<?php foreach ($skills as $skill):?>
available_services['<?php echo $skill->skill_id;?>'] = '<?php echo $skill->skill_name;?>';
<?php endforeach;?>
<?php endif;?>

<?php if (is_array($agents)):?>
objAgents = jQuery.parseJSON('<?php echo json_encode($agents);?>');
<?php foreach ($agents as $agent):?>
available_agents['<?php echo $agent->agent_id;?>'] = '<?php echo $agent->nick;?>';
<?php endforeach;?>
<?php endif;?>

<?php if (is_array($aux_messages)):?>
<?php foreach ($aux_messages as $amsg):?>
<?php $msg = $amsg->aux_type == 'I' ? $amsg->message . ' [I]' : $amsg->message;?>
available_aux_msgs['<?php echo $amsg->aux_code;?>'] = '<?php echo $msg;?>';
objAuxs = jQuery.parseJSON('<?php echo json_encode($aux_messages);?>');
<?php endforeach;?>
<?php endif;?>

/*
var agentStacks = [];
for(i=0; i<objAgents.length; i++) {
	var agent = objAgents[i];
	var obj = {"agent_id":
}
*/
try {
	obj = new gPlexCCDashboardWSApi();
	obj.loadSettings("ws://64.5.49.34:<?php echo $account_info->ws_port;?>/chat", dashboarduser, $.cookie("wsCCSeatId"), $.cookie("wsWebKey"));
	obj.setNumLoggedIn(<?php echo UserAuth::numDBLoggedIn();?>);
	obj.Connect();
} catch (ex) {
	alert("Warning1: " + ex.message);
}


var _DashBoard = {
	Login: function() {
		obj.Login(dashboarduser);
	},
	Load: function() {
		
		obj.Load();
		
	},
	/*
	LoadResponse: function(json) {
		
			<?php if ($is_expanded_db != 'Y'):?>
			$('.seat').removeClass("active activeo idle ring dial hold hold-out show").addClass("empty hide");
			<?php else:?>
			$('.seat').removeClass("active activeo idle ring dial hold hold-out").addClass("empty");
			<?php endif;?>
			$('.seat div.agent').empty();
			$('.seat div.status').empty();
			$('.agentpic').removeClass("serving");
			
			_DashBoard.PopulateSeat(json.seat);
			_DashBoard.PopulateQueue(json.q);
			
			if (typeof json.ivr != 'undefined') {
				if (json.ivr.length > 0) {
					var ivr1 = json.ivr[0];
					_DashBoard.PopulateIvr(ivr1);
				}
			}

			var dimvalue = '<span class="dim-value">0</span>';
			if (typeof json.outbound != 'undefined') {
				//if (json.ivr.length > 0) {
					var outbound = json.outbound;

					if (outbound.cnt == '0') outbound.cnt = dimvalue;
					if (outbound.at == '0' || outbound.mt == null) outbound.at = dimvalue;
					if (outbound.mt == '0' || outbound.mt == null) outbound.mt = dimvalue;
					
					$('#cntO').html(' ' + outbound.cnt);
					$('#atO').html(' ' + outbound.at);
					$('#mtO').html(' ' + outbound.mt);
				//}
			}

			var srving = allcalls-inq > 0 ? allcalls-inq : 0;
			$("#sum_calls").html(allcalls + ':' + inq + ':' + srving);
			<?php if (UserAuth::hasRole('admin')):?>
			if (typeof json.mht != 'undefined') {
				if (json.mht == '0') {
					$("#tr-mht").hide();
				} else {
					$("#tr-mht").show();
					$("#sum_mht").html(' ' + json.mht);
				}
			}
			<?php else:?>
			$("#tr-mht").hide();
			<?php endif;?>
			
	},
	MemberInfo: function(jresponse) {
		var agent_name = (typeof available_agents[agent_queue_info_display]=='undefined') ? agent_queue_info_display : available_agents[agent_queue_info_display];
		$('#agent_queue_info').html('<span class="popup-user-name">'+agent_name+' ('+agent_queue_info_display+')</span>');
						
		if (jresponse.length > 0) {
			for (j=0; j<jresponse.length; j++) {
				var _skill = jresponse[j];
				var _idle = (_skill.idle.length == 0) ? 0 : _skill.idle;
				var _calls = (_skill.call.length == 0) ? 0 : _skill.call;
				$('#agent_queue_info').append('<div class="agqinfo"><span><b>Skill: '+available_services[_skill.qid]+' </b></span><span><b>Priority:</b> '+_skill.p+'</span><span><b>Calls:</b> '+_calls+' </span><span><b>Idle:</b> '+_idle+'</span></div>');
			}
		}
	},
	AuxChange: function(resp) {
		$('#seat_'+resp.seat_id).removeClass("hold-out idle ring active activeo");
		
		<?php if ($is_expanded_db != 'Y'):?>
		if ($('#seat_'+resp.seat_id).hasClass('show')) staffout++;
		<?php else:?>
		staffout++;
		<?php endif;?>
		$('#seat_'+resp.seat_id).addClass("hold-out");
		_txtPause = (typeof available_aux_msgs[resp.aux_id]=='undefined') ? 'Busy' : available_aux_msgs[resp.aux_id];
		$('#seat_'+resp.seat_id+' div.status').html(_txtPause + ' ('+resp.lap+')');
	},
	Ring: function(resp) {
		var _skill_name = _DashBoard.SkillName(resp);
		
		$('#seat_'+resp.seat_id).removeClass("hold-out idle ring active activeo");
		
		$('#seat_'+resp.seat_id).addClass("ring");
		_txtActivity = _skill_name + ' (' + resp.lap  + ')';
		$('#seat_'+resp.seat_id+' div.status').html('Ringing '+_txtActivity);
	},
	Answer: function(resp) {
		var _skill_name = _DashBoard.SkillName(resp);
		
		$('#seat_'+resp.seat_id).removeClass("hold-out idle ring active activeo");
		
		var srvtype = !isNaN(resp.skill_id) ? 'activeo' : 'active';
		$('#seat_'+resp.seat_id).addClass(srvtype);
		_txtActivity = _skill_name.length > 0 ? _skill_name + ' (' + resp.lap  + ')' : resp.lap;
		$('#seat_'+resp.seat_id+' div.status').html(_txtActivity);
		$('#seat_'+resp.seat_id+' div.agentpic').addClass("serving");
	},
	HangUp: function(resp) {
		var _skill_name = _DashBoard.SkillName(resp);
		
		$('#seat_'+resp.seat_id).removeClass("hold-out idle ring active activeo");
		
		$('#seat_'+resp.seat_id).addClass("idle");
		_txtActivity = _skill_name.length > 0 ? _skill_name + ' (' + resp.lap  + ')' : resp.lap;
		if(resp.lap.length > 0)
			$('#seat_'+resp.seat_id+' div.status').html('Idle: '+ _txtActivity);
		else
			$('#seat_'+resp.seat_id+' div.status').html('');
	},
	ListenResponse: function(resp){
		var data = resp.data;
		var status = resp.status;
		
		if(status == 'success') {
			if(data.length > 0)
				if(data != 'Y')
					alert(data);
		} else {
			alert('Failed to commuinicate!!');
		}
	},
	SkillName: function(resp) {
		var _skill_name = '';
		if (null != resp.skill_id) {
			if(resp.skill_id.length == 0) {
				_skill_name = '';
			} else {
				_skill_name = (typeof available_services[resp.skill_id]=='undefined') ? resp.skill_id : available_services[resp.skill_id];
			}
		} else {
			_skill_name = '';
		}
		return _skill_name;
	},*/
	PopulateIvr: function(resp) {
		//var dimvalue = '<span class="dim-value">0</span>';
		var end_ts = resp[0];
		var call_id = resp[4];
		var start_ts = resp[5];
		var call_obj = {
			call_stat  : 'ivr',
			did        : resp[2],
			cli        : resp[3],
			call_id    : call_id,
			start_ts   : start_ts,
			end_ts	   : end_ts,
			skill_id   : '',
			ivr_id     : resp[6],
			agent_id   : '',
			seat_id    : '',
			lng        : '',
			skill_name : ''
		};
		
		var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
		if(result.length>0) {
			var o = result[0];
			var index = callsStack.indexOf(o);
			callsStack.splice( index, 1 );
			
			if(typeof o.agent_id!='undefined') {
				var result = $.grep(objAgents, function(e){ return e.agent_id == o.agent_id; });
				if(result.length>0) {
					//var date = new Date();
					//var start_ts = Math.floor(date.getTime()/1000);
					
					var o = result[0];
					var index = objAgents.indexOf(o);
					o.call_stat = 'ivr';
					o.call_id = '';
					o.start_ts = start_ts;
					o.end_ts = end_ts;
					o.status = 'Idle';
					o.css_class = 'idle';
					
					objAgents[index] = o;
				}
			}
			
		} else {
			//callsStack.push(call_obj);
		}
		callsStack.push(call_obj);
	
	},
	PopulateInQueue: function(resp) {
		var end_ts = resp[0];
		var call_id = resp[4];
		var start_ts = resp[5];
		var agent_id = resp[7];
		var status = resp[8];
		var skill_id = resp[6];
		var call_obj = {
			call_stat  : 'inq',
			did        : resp[2],
			cli        : resp[3],
			call_id    : call_id,
			start_ts   : start_ts,
			end_ts	   : end_ts,
			skill_id   : skill_id,
			ivr_id     : '',
			agent_id   : agent_id,
			seat_id    : '',
			lng        : '',
			skill_name : ''
		};
		
		var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
		if(result.length>0) {
			var o = result[0];
			var index = callsStack.indexOf(o);
			o = $.extend(o, call_obj);
			callsStack[index] = o;
		} else {
			callsStack.push(call_obj);
		}
		
		
		if(status=='RINGING' || status=='CALLING') { status='Ringing'; cssClass = 'ring'; }
		else if(status=='CONNECTED') { status = 'In Service'; cssClass = 'active'; }
		else if(status=='MISS_CALL') { status = 'Missed Call'; cssClass = 'hold-out'; }
		else if(status=='IN_PROGRESS') { status = 'Ringing'; cssClass = 'active'; }
		else { cssClass = 'idle'; }
		
		var result = $.grep(objSkills, function(e){ return e.skill_id == skill_id; });
		skill_name = "";
		if(result.length>0) {
			var o = result[0];
			skill_name = o.skill_name;
		}
		//console.log(agent_id);
		//console.log(objAgents);
		var result = $.grep(objAgents, function(e){ return e.agent_id == agent_id; });
		if(result.length>0) {
			var o = result[0];
			var index = objAgents.indexOf(o);
			o.aux_status = '';
			o.start_ts = start_ts;
			o.end_ts = end_ts;
			o.status = status;
			o.css_class = cssClass;
			o.call_stat = 'inq';
			o.call_id = call_id;
			o.skill_name = skill_name;
			
			objAgents[index] = o;
		}
		
	},
	PopulateInService: function(resp) {
		var end_ts = resp[0];
		var call_id = resp[4];
		var lng = resp[9];
		var skill_id = resp[10];
		var start_ts = resp[5];
		var agent_id = resp[6];
		var call_obj = {
			call_stat  : 'ins',
			did        : resp[2],
			cli        : resp[3],
			call_id    : call_id,
			start_ts   : start_ts,
			end_ts	   : end_ts,
			skill_id   : skill_id,
			ivr_id     : '',
			agent_id   : agent_id,
			seat_id    : resp[7],
			lng        : lng,
			skill_name : ''
		};
		
		var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
		if(result.length>0) {
			var o = result[0];
			var index = callsStack.indexOf(o);
			o = $.extend(o, call_obj);
			callsStack[index] = o;
		} else {
			callsStack.push(call_obj);
		}
		
		/*
		if(lng=='RINGING') {
		
			var result = $.grep(objSkills, function(e){ return e.skill_id == skillid; });
			var o = result[0];
			skill_name = o.skill_name;
		
			//var callid = resp[3];
			var result = $.grep(ivrCallsStack, function(e){ return e.CallID == callid; });
			var o = result[0];
			var index = ivrCallsStack.indexOf(o);
			ivrCallsStack.splice( index, 1 );
			
			var ivrCallsCount = ivrCallsStack.length==0 ? '<span class="dim-value">0</span>' : ivrCallsStack.length;
			$('#cntI0').html(' ' + ivrCallsCount);
			
			inServiceCallsStack.push({
				"DID":did,
				"CLI":cli,
				"CallID":callid,
				"start_ts":start_ts,
				"Agent_ID":agentid,
				"Seat_ID":seatid,
				"LNG":lng,
				"Skill_ID":skillid,
				"Skill_Name":skill_name
			});
			
			var result = $.grep(inServiceCallsStack, function(e){ return e.Skill_ID == skillid; });
			var count = result.length==0 ? '<span class="dim-value">0</span>' : result.length;
			$('#srv'+skillid).html(' ' + count);
		}
		*/
		
		
		var status = lng;
		if(lng=='RINGING') { cssClass = 'ring'; }
		else if(lng=='CONNECTED') { status='In Service'; cssClass = 'active'; }
		else if(lng=='MISS_CALL') { status='Missed Call'; cssClass = 'hold-out'; }
		else { cssClass = 'idle'; }
		
		var result = $.grep(objSkills, function(e){ return e.skill_id == skill_id; });
		skill_name = "";
		if(result.length>0) {
			var o = result[0];
			skill_name = o.skill_name;
		}
		
		var result = $.grep(objAgents, function(e){ return e.agent_id == agent_id; });
		if(result.length>0) {
			var o = result[0];
			var index = objAgents.indexOf(o);
			o.aux_status = '';
			o.start_ts = start_ts;
			o.end_ts = end_ts;
			o.status = status;
			o.css_class = cssClass;
			o.call_stat = 'ins';
			o.call_id = call_id;
			o.skill_name = skill_name;
			
			objAgents[index] = o;
		}
				
		//$('#seat_'+seatid).removeClass("ring active idle").addClass(cssClass);
		
		
		
		/*
		var date = new Date();
		var endtime = Math.floor(date.getTime()/1000);
				
		
		var lap = getTimeDifference(start_ts, endtime);
		
		var _txtActivity = skill_name + ' (' + lap  + ')';
		if(lng=='RINGING') {
			$('#seat_'+seatid+' div.status').html(lng+' '+_txtActivity);
		} else {
			$('#seat_'+seatid+' div.status').html(_txtActivity);
		}
		//$.cookie(cookieVars.callsStack,JSON.stringify(cookieValue.callsStack));
		*/
	},
	PopulateBye: function(resp) {
		
		var start_ts = resp[0];
		var call_id = resp[2];
		var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
		
		//var date = new Date();
		//var start_ts = Math.floor(date.getTime()/1000);
				
		if(result.length>0) {
			var o = result[0];
			var index = callsStack.indexOf(o);
			callsStack.splice( index, 1 );
			
			if(o.call_stat=='inq') {
				//var skill_id = o.skill_id;
				//$("#inq"+skill_id).html(dimvalue);
				//$("#at"+skill_id).html(dimvalue);
				//$("#mt"+skill_id).html(dimvalue);
				
				var result = $.grep(objAgents, function(e){ return e.agent_id == o.agent_id; });
				if(result.length>0) {
					var o = result[0];
					var index = objAgents.indexOf(o);
					o.call_id = '';
					o.start_ts = start_ts;
					o.end_ts = start_ts;
					o.status = 'Idle';
					o.css_class = 'idle';
					o.skill_name = '';
					
					objAgents[index] = o;
				}
			}
			else if(o.call_stat=='ins') {
				//var skill_id = o.skill_id;
				//$("#srv"+skill_id).html(dimvalue);
				//$("#at"+skill_id).html(dimvalue);
				//$("#mt"+skill_id).html(dimvalue);
				
				
				
				var result = $.grep(objAgents, function(e){ return e.agent_id == o.agent_id; });
				if(result.length>0) {
					var o = result[0];
					var index = objAgents.indexOf(o);
					o.call_id = '';
					o.start_ts = start_ts;
					o.end_ts = start_ts;
					o.status = 'Idle';
					o.css_class = 'idle';
					o.skill_name = '';
					
					objAgents[index] = o;
				}
				
				//if(typeof o.seat_id!='undefined') $("#seat_"+o.seat_id).removeClass("hold-out idle ring active activeo").addClass("idle");
			}
			else if(o.call_stat=='ivr') {
				//var skill_id = o.skill_id;
				
				//$("#cntI0").html(dimvalue);
			} else if(o.call_stat=='inc') {
				//var skill_id = o.skill_id;
				
				//$("#cntI0").html(dimvalue);
				var result = $.grep(objAgents, function(e){ return e.seat_id == o.df_seat_id; });
				if(result.length>0) {
					var o = result[0];		
					var index = objAgents.indexOf(o);
					o.call_id = '';
					o.start_ts = start_ts;
					o.end_ts = start_ts;
					o.status = 'Idle';
					o.css_class = 'idle';
					o.skill_name = '';
					
					objAgents[index] = o;
				}
				
				var result = $.grep(objAgents, function(e){ return e.seat_id == o.dt_seat_id; });
				if(result.length>0) {
					var o = result[0];		
					var index = objAgents.indexOf(o);
					o.call_id = '';
					o.start_ts = start_ts;
					o.end_ts = start_ts;
					o.status = 'Idle';
					o.css_class = 'idle';
					o.skill_name = '';
					
					objAgents[index] = o;
				}
			}
			
			/*
			var count = inServiceCallsStack.length==0 ? '<span class="dim-value">0</span>' : inServiceCallsStack.length;
			$('#srv'+o.Skill_ID).html(' ' + count);
			
			var agentid = o.Agent_ID;
			var result = $.grep(objAgents, function(e){ return e.agent_id == agentid; });
			if(result.length>0) {
				var o = result[0];		
				var index = objAgents.indexOf(o);
				//o.call_id = call_id;
				//o.busy_status = busy_status;
				o.aux_status = '';
				
				var date = new Date();
				var start_tm = Math.floor(date.getTime()/1000);
				o.start_ts = start_tm;
				o.status = 'Idle';
				o.css_class = 'idle';
				
				objAgents[index] = o;
			}*/
		} 
		
		/*
		else {
			result = $.grep(ivrCallsStack, function(e){ return e.CallID == callid; });
			if(result.length>0) {
				o = result[0];
				index = ivrCallsStack.indexOf(o);
				ivrCallsStack.splice( index, 1 );
			} else {
				result = $.grep(inQueueCallsStack, function(e){ return e.CallID == callid; });
				if(result.length>0) {
					o = result[0];
					index = inQueueCallsStack.indexOf(o);
					inQueueCallsStack.splice( index, 1 );
				} else {
					return;
				}
			}
		}
		
		var skillid = o.Skill_ID;
		//var date = new Date();
		//var endtime = Math.floor(date.getTime()/1000);
		//var lap = getTimeDifference(endtime, endtime);
		
		var result = $.grep(inServiceCallsStack, function(e){ return e.Skill_ID == skillid; });
		var count = result.length==0 ? '<span class="dim-value">0</span>' : result.length;
		$('#srv'+o.Skill_ID).html(' ' + count);
		
		count = ivrCallsStack.length==0 ? '<span class="dim-value">0</span>' : ivrCallsStack.length;
		$('#cntI0').html(' ' + count);
		*/
		
		
		//$('#seat_'+o.Seat_ID).removeClass("ring active idle").addClass('idle');
		//$('#seat_'+o.Seat_ID+' div.status').html('Idle: '+lap);
	},
	PopulateSeat: function(resp) {
		var status = "";
		var cssClass = "";
		
		var end_ts = $.trim(resp[0]);
		var seat_id = $.trim(resp[2]);
		var agent_id = $.trim(resp[3]);
		var call_id = $.trim(resp[4]);
		var busy_status = $.trim(resp[5]);
		var aux_status = $.trim(resp[6]);
		var aux_type = $.trim(resp[7]);
		var aux_code = $.trim(resp[8]);
		//var date = new Date();
		//var end_ts = Math.floor(date.getTime()/1000);
		var start_ts = $.trim(resp[9])
		
		
		if(agent_id.length==0) {
			$("#seat_"+seat_id).removeClass("hold-out idle ring active activeo").addClass("enable");
			$('#seat_'+seat_id+' div.status').html("");
			var result = $.grep(objAgents, function(e){ return e.seat_id == seat_id; });
			if(result.length>0) {
				var o = result[0];
				var newO = { agent_id:o.agent_id, nick:o.nick }
				var index = objAgents.indexOf(o);
				objAgents[index] = newO;
			}
			//objAgents.splice( index, 1 );
			return;
		}
		
		
		if(busy_status=='I') { status = "Idle"; cssClass = "idle"; }
		else if(busy_status=='R') { status = "Ringing"; cssClass = "ring"; }
		else if(busy_status=='S') { status = "In Service"; cssClass = "active"; }
		else if(busy_status=='B') { 
			
			//if(aux_code==0) 
			if(aux_code==0) {
				status = "Busy";
			} else {
				var result = $.grep(objAuxs, function(e){ return e.aux_code == aux_code; });
				if(result.length>0) { var o = result[0]; status = o.message; }
			}
			//status += aux_status=="O" ? "[OUT]" : "[IN]"; 
			cssClass = "hold-out";
		}
		else if(busy_status=='M') { status = "Missed Call"; cssClass = "hold-out"; } 
		else if(busy_status=='') { status = "Idle"; cssClass = "idle"; }

		var result = $.grep(objAgents, function(e){ return e.agent_id == agent_id; });
		
		var agent_name = "";
		if(result.length>0) {
			var o = result[0];
			agent_name = o.nick;
			
			var index = objAgents.indexOf(o);
			
			o.seat_id = seat_id;
			//o.call_id = call_id;
			//o.busy_status = busy_status;
			o.aux_status = aux_status;
			o.aux_type = aux_type;
			o.aux_code = aux_code;
			o.start_ts = start_ts;
			o.end_ts = end_ts;
			o.status = status;
			o.css_class = cssClass;
			
			objAgents[index] = o;
		}
	},
	PopulateAux: function(resp) {
		var status = '';
		var cssClass = '';
		var end_ts = resp[0];
		var agentid = $.trim(resp[2]);
		var seat_id = $.trim(resp[3]);
		var aux_type = $.trim(resp[4]);
		var aux_status = $.trim(resp[5]);
		var busy_status = $.trim(resp[6]);
		var aux_code = $.trim(resp[7]);
		var date = new Date();
		var start_ts = Math.floor(date.getTime()/1000);
		
		//var aux_name = '';
		var result = $.grep(objAuxs, function(e){ return e.aux_code == aux_code; });
		if(result.length>0) { var o = result[0]; status = o.message; }
		
		
		
		if(aux_status!='B') {
			if(busy_status=='I') { status = 'Idle'; cssClass='idle'; }
			else if(busy_status=='R') { status = 'Ringing'; cssClass='ring'; }
			else if(busy_status=='S') { status = "In Service"; cssClass='active'; }
			else if(busy_status=='B') { status = 'Busy'; cssClass='hold-out'; }
			else { cssClass='idle'; }
		} else {
			if(aux_type=="O") { cssClass='hold-out'; }
			else if(aux_type="I") { cssClass='hold-out'; }
		}
		
		
		
		//if(aux_type=='O') { cssClass='hold-out'; }
		//else { status='Idle'; cssClass='idle';}
		
		result = $.grep(objAgents, function(e){ return e.agent_id == agentid; });
		var agent_name = "";
		if(result.length>0) {
			var o = result[0];		
			var index = objAgents.indexOf(o);
			o.aux_type = aux_type;
			o.aux_status = aux_status;
			o.aux_code = aux_code;
			o.start_ts = start_ts;
			o.end_ts = end_ts;
			o.status = status;
			o.css_class = cssClass;
			
			objAgents[index] = o;
		}
		/*
		//var date = new Date();
		//var starttime = Math.floor(date.getTime()/1000);
		var lap = '00:00';
		var status = '';
		if(busy_status=='B') status = 'Busy ';
		else status = 'Idle ';
		
		$('#seat_'+seat_id+' div.status').html(status + '('+lap+')');
		*/
	},
	PopulateQueue: function(queues) {
	
		/*
		var dimvalue = '<span class="dim-value">0</span>';
			
		for (prop in queues) {
			if (prop.length > 0) {
				var _qObj = queues[prop];
				var _queue = _qObj.qid;
				if (available_services[_queue]) {
					//vat ttl = _qObj.inq + _qObj.srv;
					//var total = parseInt(_qObj.inq) + parseInt(_qObj.srv);
					allcalls += parseInt(_qObj.srv);
					inq += parseInt(_qObj.inq);
					
					if (_qObj.inq == '0') _qObj.inq = dimvalue;
					//if (total == 0) total = dimvalue;
					if (_qObj.srv == '0') _qObj.srv = dimvalue;
					if (_qObj.at == '0') _qObj.at = dimvalue;
					if (_qObj.mt == '0') _qObj.mt = dimvalue;
					//if (_qObj.wa == '0') _qObj.wa = dimvalue;
					
					$('#inq'+_queue).html(' ' + _qObj.inq);
					$('#srv'+_queue).html(' ' + _qObj.srv);
					//$('#tc'+_queue).html(' ' + total);
					$('#at'+_queue).html(' ' + _qObj.at);
					$('#mt'+_queue).html(' ' + _qObj.mt);
					//$('#wa'+_queue).html(' ' + _qObj.wa);
				}
			}
		}*/
	},
	PopulateOutgoingCall: function(resp) {
		
		var dt_seat_id = resp[5];
		var dial_to = resp[4];
		if(dial_to.length==5) return;
		
		var end_ts = resp[0];
		var start_ts = resp[7];
		var df_seat_id = resp[3];
		var call_id = resp[6];
		var call_obj = {
			call_stat  : "inc",
			dial_from  : resp[2],
			df_seat_id : df_seat_id,
			dial_to    : dial_to,
			dt_seat_id : dt_seat_id,
			call_id    : call_id,
			start_ts   : start_ts,
			end_ts     : end_ts,
			dir        : resp[8],
			inc_status : ucfirst(resp[9]),
			inc_class  : 'activeo'
		};
		
		var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
		if(result.length>0) {
			var o = result[0];
			var index = callsStack.indexOf(o);
			o = $.extend(o, call_obj);
			callsStack[index] = o;
		} else {
			callsStack.push(call_obj);
		}		
		
		var result = $.grep(objAgents, function(e){ return e.seat_id == df_seat_id; });
		if(result.length>0) {
			var o = result[0];
			var index = objAgents.indexOf(o);
			o.call_stat = 'inc';
			o.call_id = call_id;
			o.start_ts = start_ts;
			o.end_ts = end_ts;
			
			objAgents[index] = o;
		}
		
		if(dt_seat_id=='0000') return;
		
		var result = $.grep(objAgents, function(e){ return e.seat_id == dt_seat_id; });
		if(result.length>0) {
			var o = result[0];
			var index = objAgents.indexOf(o);
			o.call_stat = 'inc';
			o.call_id = call_id;
			o.start_ts = start_ts;
			o.end_ts = start_ts;
			
			objAgents[index] = o;
		}
		
	}
}


function getTimeDifference(starttime, endtime) {
	var diff = endtime - starttime;
	if(diff < 0 ||diff > 20000) return "00:00";
	//diff = Math.floor(diff / 1000);
	var secs_diff = diff % 60;
	secs_diff = secs_diff<10 ? '0'+secs_diff : secs_diff;
	diff = Math.floor(diff / 60);
	var mins_diff = diff % 60;
	mins_diff = mins_diff<10 ? '0'+mins_diff : mins_diff;
	var lap = mins_diff+':'+secs_diff;
	
	return lap;
}

function getTimeDifferenceInSeconds(starttime, endtime) {
	var diff = endtime - starttime;
	if(diff < 0 ||diff > 20000) diff = 0;
	//diff = Math.floor(diff / 1000);
	//var secs_diff = diff % 60;
	
	return diff;
}

function secondsToMMSS(diff) {
	//diff = diff*60;
	if(diff == 0 || diff > 20000) return 0;
	
	var secs_diff = diff % 60;
	secs_diff = secs_diff<10 ? '0'+secs_diff : secs_diff;
	diff = Math.floor(diff / 60);
	var mins_diff = diff % 60;
	mins_diff = mins_diff<10 ? '0'+mins_diff : mins_diff;
	var lap = mins_diff+':'+secs_diff;
	
	return lap;
}

function ucfirst(str) {
  str += '';
  str = str.toLowerCase();
  var f = str.charAt(0).toUpperCase();
  return f + str.substr(1);
}


function NatPing()
{
	obj.Reg();
	natPingTimeout = setTimeout(NatPing,20000);
}

function secondInterval() {
	var staffing = 0;
	var staffin = 0;
	var staffout = 0;
	//var date = new Date();
	//var end_ts = Math.floor(date.getTime()/1000);
	
	for(i=0; i<objAgents.length; i++) {
		var o = objAgents[i];
		
		if(typeof o.start_ts!='undefined') {
			//var date = new Date(parseInt(o.end_ts));
			//date.setSeconds(date.getSeconds() + 1);
			o.end_ts = parseInt(o.end_ts)+1;//Math.floor(date.getTime());
			//console.log(o.end_ts);
			objAgents[i] = o;
		
			var seat_id = o.seat_id;
			var status = o.status;
			var agent_name = o.nick;
			var css_class = o.css_class;
			var start_ts = o.start_ts;
			var end_ts = o.end_ts;
			var skill_name = o.skill_name;
			if(o.aux_status=="B") {
				if(o.aux_type=="O") staffout++;
			}
			var call_id = o.call_id;

			var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
			if(result.length>0) {
				var ob = result[0];
				if(ob.call_stat=='inc') {
					status = ob.inc_status;
					css_class = ob.inc_class;
				}
			}
			
			//var date = new Date();
			//var end_ts = Math.floor(date.getTime()/1000);
			//console.log("START:"+start_ts);
			//console.log("END:"+end_ts);
			var lap = getTimeDifference(start_ts, end_ts)
			
			skill_name = (typeof skill_name!='undefined' && skill_name.length>0) ? skill_name+"<br>" : '';
			
			$('#seat_'+seat_id).removeClass("hold-out idle ring active activeo").addClass("show "+css_class);
			$('#seat_'+seat_id+' div.status').html(agent_name +'<br>'+skill_name+status+'<br>('+lap+')');
			staffing++;
		}
	}
	var staffin = staffing - staffout;
	$("#sum_staff").html(staffing + ':' + staffin);
	
	/*
	var mht = 0;
	var totalSecs = 0;
	
	for(i=0; i<ivrCallsStack.length; i++) {
		var o = ivrCallsStack[i];
		var start_ts = o.start_ts;
		
		var secs = getTimeDifferenceInSeconds(start_ts, end_ts);
		totalSecs += secs;
		if(secs>mht) mht = secs;
	
	}
	var mht = mht==0 ? '<span class="dim-value">0</span>' : secondsToMMSS(mht);
	$('#mtI0').html(mht);
	
	totalSecs = ivrCallsStack.length>0 ? Math.floor(totalSecs / ivrCallsStack.length) :  totalSecs;
	totalSecs = totalSecs==0 ? '<span class="dim-value">0</span>' : secondsToMMSS(totalSecs);
	$('#atI0').html(totalSecs);
	
	//skill wise In Queue and In Service call
	for(i=0; i<objSkills.length; i++) {
		var o = objSkills[i];
		var skillid = o.skill_id;
		var result = $.grep(inServiceCallsStack, function(e){ return e.Skill_ID == skillid; });
		
		mht = 0;
		totalSecs = 0;
		for(j=0; j<result.length; j++) {
			var o = result[j];
			var start_ts = o.start_ts;
			
			var secs = getTimeDifferenceInSeconds(start_ts, end_ts);
			totalSecs += secs;
			if(secs>mht) mht = secs;
		
		}
		mht = mht==0 ? '<span class="dim-value">0</span>' : secondsToMMSS(mht);
		$('#mt'+skillid).html(mht);
		
		totalSecs = result.length>0 ? Math.floor(totalSecs / result.length) :  totalSecs;
		totalSecs = totalSecs==0 ? '<span class="dim-value">0</span>' : secondsToMMSS(totalSecs);
		$('#at'+skillid).html(totalSecs);
	}
	
	//calls count
	var srv = inServiceCallsStack.length;
	var inq = inQueueCallsStack.length;
	var allCalls = srv + inq;
	$("#sum_calls").html(allCalls + ':' + inq + ':' + srv);
	*/
	
	/*
	for(i=0; i<callsStack.length; i++) {
		var o = callsStack[i];
		var date = new Date(parseInt(o.end_ts));
		date.setSeconds(date.getSeconds() + 1);
		o.end_ts = Math.floor(date.getTime());
		callsStack[i] = o;
	}*/
	
	//calls info update
	var ivrCount = 0;
	var ivrMaxTime = 0;
	var totalIvrTime = 0;
	var ivrAvgTime = 0;
	var inqCount = {};
	var insCount = {};
	//var skillTimestamps = {};
	var outboundCount = 0;
	//var insTimestamps = {};
	
	//var date = new Date();
	//var end_ts = Math.floor(date.getTime()/1000);
	$(".valbox").html(dimvalue);
	
	for(i=0; i<callsStack.length; i++) {
		var o = callsStack[i];
		
		//var date = new Date(parseInt(o.end_ts));
		//date.setSeconds(date.getSeconds() + 1);
		//o.end_ts = Math.floor(date.getTime());
		o.end_ts = parseInt(o.end_ts)+1;
		callsStack[i] = o;
		
		if(o.call_stat=='ivr') {
			ivrCount++;
			var ivrSeconds = getTimeDifferenceInSeconds(o.start_ts, o.end_ts);
			if(ivrSeconds>ivrMaxTime) ivrMaxTime = ivrSeconds;
			totalIvrTime += ivrSeconds;
		} else if(o.call_stat=='inq') {
			var skill_id = o.skill_id;
			if(typeof inqCount[skill_id]=='undefined') inqCount[skill_id] = 0;
			inqCount[skill_id] += 1;
			//if(typeof skillTimestamps[skill_id]=='undefined') skillTimestamps[skill_id] = new Array();
			//skillTimestamps[skill_id].push(o.start_ts);
		} else if(o.call_stat=='ins') {
			var skill_id = o.skill_id;
			if(typeof insCount[skill_id]=='undefined') insCount[skill_id] = 0;
			insCount[skill_id] += 1;
			//if(typeof skillTimestamps[skill_id]=='undefined') skillTimestamps[skill_id] = new Array();
			//skillTimestamps[skill_id].push(o.start_ts);
		} else if(o.call_stat=='inc') {
			if(o.dial_to.length>4) outboundCount++;
			$('#seat_'+o.df_seat_id).removeClass("hold-out idle ring active activeo").addClass("activeo");
			$('#seat_'+o.dt_seat_id).removeClass("hold-out idle ring active activeo").addClass("activeo");
		}
	}
	
	ivrCount = ivrCount>0 ? ivrCount : dimvalue;
	ivrAvgTime = ivrCount>0 ? secondsToMMSS(Math.ceil(totalIvrTime/ivrCount)) : dimvalue;
	ivrMaxTime = ivrMaxTime>0 ? secondsToMMSS(ivrMaxTime) : dimvalue;
	
	$("#cntI0").html(ivrCount);
	$("#atI0").html(ivrAvgTime);
	$("#mtI0").html(ivrMaxTime);
	
	for(skill_id in inqCount) {
		var count = inqCount[skill_id];
		count = count>0 ? count : dimvalue;
		$("#inq"+skill_id).html(count);
	}

	for(skill_id in insCount) {
		var count = insCount[skill_id];
		count = count>0 ? count : dimvalue;
		$("#srv"+skill_id).html(count);
	}
	
	/*
	for(skill_id in skillTimestamps) {
		var mht = 0;
		var total_ts = 0;
		var ts_ary = skillTimestamps[skill_id];
		for(j=0; j<ts_ary.length; j++) {
			var ivrSeconds = getTimeDifferenceInSeconds(ts_ary[j], end_ts);
			if(ivrSeconds>mht) mht = ivrSeconds;
			total_ts += ivrSeconds;
		}
		
		mht = mht==0 ? dimvalue : secondsToMMSS(mht);
		$("#mt"+skill_id).html(mht);
		
		total_ts = ts_ary.length>0 ? Math.ceil(total_ts/ts_ary.length) : total_ts;
		total_ts = total_ts==0 ? dimvalue : secondsToMMSS(total_ts);
		$("#at"+skill_id).html(total_ts);
	}*/
	
	var inqStartEndTimestamps = {};
	var inqResult = $.grep(callsStack, function(e){ return e.call_stat == 'inq'; });
	for(i=0; i<inqResult.length; i++) {
		var o = inqResult[i];
		var sec = getTimeDifferenceInSeconds(o.start_ts, o.end_ts);
		if(typeof inqStartEndTimestamps[o.skill_id]=='undefined') inqStartEndTimestamps[o.skill_id] = {"total":0,"max":0, "count":0};
		inqStartEndTimestamps[o.skill_id]["total"] += sec;
		inqStartEndTimestamps[o.skill_id]["count"] += 1;
		if(sec > inqStartEndTimestamps[o.skill_id]["max"]) inqStartEndTimestamps[o.skill_id]["max"] = sec;
	}
	
	var total_t = 0;
	var max_mht = 0;
	for(index in inqStartEndTimestamps) {
		var o = inqStartEndTimestamps[index];
		var total = o.total;
		var max = o.max;
		max_mht = max>max_mht ? max : max_mht;
		var count = o.count;
		total = count>0 ? Math.floor(total/count) : total;	
		var aht = total==0 ? dimvalue : secondsToMMSS(total);
		var mht = max==0 ? dimvalue : secondsToMMSS(max);
		$("#at"+index).html(aht);
		$("#mt"+index).html(mht);
	}
	
	max_mht = max_mht==0 ? "00:00" : secondsToMMSS(max_mht);
	$("#sum_mht").html(max_mht);

	//var result = $.grep(callsStack, function(e){ return e.call_stat == 'inq'; });
	var inq = inqResult.length;
	var result = $.grep(callsStack, function(e){ return e.call_stat == 'ins'; });
	var ins = result.length;
	var allCalls = inq + ins;
	
	outboundCount = outboundCount==0 ? dimvalue : outboundCount;
	$("#cntO").html(outboundCount);
	
	$("#sum_calls").html(allCalls + ':' + inq + ':' + ins);
	
	setTimeout("secondInterval()",1000);
}
secondInterval();

</script>


<style type="text/css">
*{
	padding: 0px;
	margin: 0px;
}
.agent{
	padding-left:20px;
	font-weight:bold;
	cursor:pointer;
}
#seats {
	clear: left;
}
.number{
	/*padding-left: 20px;*/
	/*background:url(images/call.png) no-repeat 10% 0%;*/
}
#seats .seat{
	/*background:url(images/user.png) no-repeat top center;*/
	color: #222;
	background-repeat: no-repeat;
	background-position: 3 6;
	/*background-color: #eee;*/
	margin: 5px;
	padding: 2px 2px 10px 2px;
	border-color:#e1e1e1 #bbbbbb #bbbbbb #e1e1e1;
	border-style:solid;
	border-width:1px;
	background-color: #c7d6e9;/*#eaeaea;*/

	/*height: 40px;*/

	width: 128px;
	float:left;
	font-size:9px;
	display:block;
}
.agentpic {
	float:left;
	width: 45px;
	height: 50px;
}
.agentpic.serving {
	/*cursor: pointer;*/
}
.seat-text{
	float:left;
	padding: 0px;
	margin:0px;
	/*margin: 2px 0px 0px 22px;*/
	margin-top: 2px;
	width: 74px;
	height: 60px;
	border-left: 1px solid #c7d6e9;/*2px dashed #eaeaea;*/
	border-right: 1px solid #c7d6e9;/*2px dashed #eaeaea;*/
	font-family:verdana;
	overflow: hidden;
}

html>body .seat-text{
	/*margin: 2px 0px 0px 50px;*/
}
html>body #seats .seat{
	/*height: 53px;*/
	height: 60px;
}
#seats .seat .agent{
	padding: 0px;
	margin: 0px;
}
#seats .seat .status{
	min-height: 12px;
}

#seats .seat .idle{
}
#seats div.seat.dial{
	background-image:url(image/agent_st_dial.gif);
}
#seats .seat.empty{
	background-image:url(image/seat_empty.gif);
}
#seats .seat.enable{
	background-image:url(image/seat_enable.gif);
}
#seats .seat.active{
	background-image:url(image/agent_talking.gif);
}
#seats .seat.activeo{
	background-image:url(image/agent_talking_out.gif);
}

#seats .seat.idle{
	background-image:url(image/agent_idle.gif);
}
#seats .seat.ring{
	background-image:url(image/agent_ringing.gif);
}
#seats .seat.hold{
	background-image:url(image/agent_st_hold.gif);
}
#seats .seat.hold-out{
	background-image:url(image/agent_st_hold_out.gif);
}

#seats .seat.hide{
	/*background-image:url(images/agent_st_hold.gif);*/
	display: none;
}

#seats .seat.show{
	/*background-image:url(images/agent_st_hold.gif);*/
	display: block;
}


.loading {

}
.queue_calls_count{
	font-weight: bold;
}
.numbers{
	padding-top: 6px;
}
.queueinfo{
	font-family:verdana;
	font-size: 11px;
	color:white;font-weight:bold;text-align:center;
}
#queues .queue, #queues .ivr{
	height: 150px;
	width: 179px;
	margin: 1px;
	float:left;
	font-size:9px;
}

#queues{
	height: 130px;
	/*width: 100%;*/
	/*float:left;*/
	text-align:center;
	border: 0px solid red;
}

#ivrs .ivr{
	height: 100px;
	width: 90px;
	margin: 1px;
	padding: 22px 2px 2px 2px;
	border: 1px solid gray;
	float:left;
	font-size:9px;
}

#ivrs{
	height: 200px;
	width: 350px;
	float:left;
	margin-left:20px;
	border: 0px solid gray;
	clear:right;
}
.heading{
	font-weight:bold;
	font-size:15px;
	clear:both;
	border-top: 3px dotted #EEAAAA;
	border-bottom: 3px dotted #EEAAAA;
	text-align:center;
	padding: 10px 0px 10px 0px;
	color: #FF2323;
}
.seatinfo{
	text-align: center;
	padding: 1px;
	margin: 0px;
	font-weight:bold;
}
#agent_queue_info, #queue_detail_info{
	/*position:fixed;*/
	position:absolute;
	background: #E0ECFF;
	border: 5px solid #C3D9FF;
	font-size: 11px;
	font-family: verdana;
}
.popup-user-name, .popup-queue-name{
	display:block;
	text-align:left;
	font-weight:bold;
	margin: 1px;
	padding: 1px;
	padding-left: 18px;

}
.popup-user-name{
	background: #ABCABC url(images/user.png) no-repeat left center;
}
.popup-queue-name{
	background: #ABCABC url(images/queue.png) no-repeat left center;
}
.queuemoreinfo{
	text-align: right;
	padding: 0px;
	cursor: pointer;
	font-size:9px;
	/*
	background:url(images/queue_menu_top.gif) no-repeat 0 0;
	*/
}
.queuemoreinfo a {
	text-decoration: none;
}
.agqinfo {
	border: 0px dotted blue;
	background: #ABCDAB;
	padding:2px;
	margin: 1px;
}
.agqinfo span{
	text-align:left;
	display:block;
}
.qnumberinfo {
	background: #DDDDDD;
	margin: 1px;
	padding: 1px;
	text-align:left;
}
.ageventinfo{
	width: 28px;
	float: right;
}
.seat_ip{
}
.ageventinfo a{

}
.ageventinfo a img{
	float: right;
}
#wrapper{
width: 99%;
/*background-color:#F1E0CE;*/
margin: 0 auto;
margin-top:5px;
margin-bottom: 5px;
text-align: center;
/*background: #F1E0CE;*/
border:1px solid #000000;
padding-bottom: 25px;
height:100%;
vertical-align:middle;
text-align:center;
}
#uptime {
	font-weight: bold;
}
#container-uptime {
	/*vertical-align:text-bottom;*/
	/*padding-left: 300px;*/
	float: left;
}

.bxIvr, .bxOutbound {
	width: 150px;
	border: 1px solid #BCBCBC;
	padding: 1px;
	float:left;
	/*margin-right: 10px;*/
}
.bxQueue {
	width: 150px;
	border: 1px solid #BCBCBC;
	padding: 0px;
	float:left;
	margin-right: 10px;
}
.bxSum {
	width: 300px;
	border: 1px solid #BCBCBC;
	padding: 0px;
	float:left;
	margin-right: 10px;
}
.infoQueue, .infoSum {
	width: 100%;
}
.infoQueue td, .infoSum td {
	font-size: 30px;
	border: 1px solid #ffffff;
	text-align:center;
	font-family:"Times New Roman", Times, serif;
}
.infoSum td {
	font-size: 30px;
	background-color:#ffffff;
}
.infoQueue .qhead {
	font-size: 11px;
	font-weight: bold;
	text-align:center;
}
.infoQueue .qinfo, .infoSum .qinfo {
	font-size: 9px;
	text-align: center;
	font-family:Verdana, Arial, Helvetica, sans-serif;
}
td.qhead-td {
	/*background-color:#F96B0F;*/
	/*
	background:linear-gradient(to bottom, #4A98AF 44%, #328AA4 45%) repeat scroll 0 0 rgba(0, 0, 0, 0);
	color: #ffffff;
	padding: 1px;
	*/
	
	background: #4a98af; /* Old browsers */
/* IE9 SVG, needs conditional override of 'filter' to 'none' */
background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSI0NCUiIHN0b3AtY29sb3I9IiM0YTk4YWYiIHN0b3Atb3BhY2l0eT0iMSIvPgogICAgPHN0b3Agb2Zmc2V0PSI0NSUiIHN0b3AtY29sb3I9IiMzMjhhYTQiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
background: -moz-linear-gradient(top,  #4a98af 44%, #328aa4 45%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(44%,#4a98af), color-stop(45%,#328aa4)); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top,  #4a98af 44%,#328aa4 45%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top,  #4a98af 44%,#328aa4 45%); /* Opera 11.10+ */
background: -ms-linear-gradient(top,  #4a98af 44%,#328aa4 45%); /* IE10+ */
background: linear-gradient(to bottom,  #4a98af 44%,#328aa4 45%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#4a98af', endColorstr='#328aa4',GradientType=0 ); /* IE6-8 */
color: #ffffff;
padding: 1px;
}
.qhead-td div.qhead{
	font-size: 14px;
}

.qinfo-tr {
	background-color:#D8D8D8;
}
.qinfo-tr td {
	padding: 2px;
}
td.qbox-col-left-border {
	border-left: 1px solid #BFBEBE;
}
.dim-value{
	color:#DDDDDD;
}



table.dbinfo {
	display:inline;
	border: 1px solid white;
}
td.qheadinfo span{
	padding:0;
	margin:0;
}
td.qheadinfo {
	margin:0px;
	padding:0px;
	font-size: 35px;
	font-weight: bolder;
	/*color:#F96B0F;*/
}
table.q-panel, table.ag-panel {
	border-collapse: collapse;
    border-radius: 0.417em 0.417em 0.417em 0.417em;
    box-shadow: 0 0 5px #091B21;
    margin: 5px;
    width: 98%;
}
</style>

<!--[if lt IE 9]>
<style type="text/css">
table.q-panel, table.ag-panel {
border :0.1em solid #BDBBB0 !important;
}
</style>
<![endif]-->

<!--[if gte IE 9]>
  <style type="text/css">
    .gradient {
       filter: none;
    }
  </style>
<![endif]-->

</head>
<?php
	$image_src = 'agents_picture/ccowner_logo.png';
    if (file_exists($image_src)) {
		$image_src = 'agents_picture/ccowner_logo.png?t=' . time();
	}else {
	    $image_src = 'image/blank_logo.png';
	}
?>
<body style="text-align:center;">

<!--
<div id="wrapper">
-->
<table cellpadding="0" cellspacing="0" id="wrapper">
	<tr>
    	<td valign="top";>

<center>

<table cellpadding="0" cellspacing="0"  width="98%">
	<tr>
    	<td style="background:url(image/dashboard_image.png) no-repeat left top;" width="225" height="48"></td>
		<td align="center" style="font-size:18px; font-family:Verdana, Arial, Helvetica, sans-serif; color:#F26522;">

        	<table align="center"><tr><td>
        	<table width="225"><tr><td width="60" style="font-size:20px;">Calls:</td><td class="qheadinfo" align="left"><span id="sum_calls"></span> &nbsp;</td></tr></table>
         	</td><td id="tr-mht">
            <table width="225"><tr><td width="60" style="font-size:20px;">MHT:</td><td class="qheadinfo" align="left"><span id="sum_mht"></span> &nbsp;</td></tr></table>
			</td><td>
         	<table width="225"><tr><td width="100" style="font-size:20px;">Staffing:</td><td class="qheadinfo" align="left"><span id="sum_staff"></span></td></tr></table>
			</td></tr></table>
        
        </td>
        <td width="180" valign="top" align="right" style="background:url(<?php echo $image_src; ?>) no-repeat right bottom; width:180px; height:46px;"> </td>
		<td><a href='#' id='LogoutBtn'>Logout</a></td>
	</tr>
</table>


<table class="q-panel" style="" cellpadding="0" border="0" cellspacing="0" width="98%" height="150" align="center">
    <tr>
        <td style="vertical-align:top;background: none repeat scroll 0 0 #E5F1F4; padding:5px;">


<div id="queues">

<?php if (is_array($skills)):?>
<?php foreach ($skills as $skill):?>
<div class="bxQueue">
<table class="infoQueue" bgcolor="#ffffff" cellspacing="0">
<tr><td colspan="2" class="qhead-td"><div class="qhead"><?php echo $skill->skill_name; if ($skill->type == 'out') echo ' [out]';?></div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo"><?php echo $skill->type == 'out' ? 'In Progress' : 'In Queue';?></div></td><td class="qbox-col-left-border"><div class="qinfo">In Service</div></td></tr>
<tr><td><div id="inq<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div></td><td class="qbox-col-left-border"><div id="srv<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo"><?php echo $skill->type == 'out' ? 'ATT' : 'AHT';?></div></td><td class="qbox-col-left-border"><div class="qinfo"><?php echo $skill->type == 'out' ? 'MTT' : 'MHT';?></div></td></tr>
<tr><td><div id="at<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div></td><td class="qbox-col-left-border"><div id="mt<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div></td></tr>
</table>
</div>
<?php endforeach;?>
<?php endif;?>

<div class="bxIvr">
<table class="infoQueue" bgcolor="#ffffff" cellspacing="0">
<tr><td colspan="2" class="qhead-td"><div class="qhead">IVR</div></td></tr>

<tr class="qinfo-tr"><td colspan="2"><div class="qinfo">IVR Count</div></td></tr>
<tr><td colspan="2"><div id="cntI0"><span class="dim-value">0</span></div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo">Avg Time</div></td><td class="qbox-col-left-border"><div class="qinfo">Max Time</div></td></tr>
<tr><td><div id="atI0"><span class="dim-value">0</span></div></td><td class="qbox-col-left-border"><div id="mtI0"><span class="dim-value">0</span></div></td></tr>
</table>
</div>

<div class="bxOutbound">
<table class="infoQueue" bgcolor="#ffffff" cellspacing="0">
<tr><td colspan="2" class="qhead-td"><div class="qhead">Outbound</div></td></tr>

<tr class="qinfo-tr"><td colspan="2"><div class="qinfo">Total Calls</div></td></tr>
<tr><td colspan="2"><div id="cntO"><span class="dim-value">0</span></div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo">Avg Time</div></td><td class="qbox-col-left-border"><div class="qinfo">Max Time</div></td></tr>
<tr><td><div id="atO"><span class="dim-value">0</span></div></td><td class="qbox-col-left-border"><div id="mtO"><span class="dim-value">0</span></div></td></tr>
</table>
</div>


</div>


        </td>

    </tr>


</table>

<div style="text-align:center; position: relative; height: 24px; line-height: 24px;">
<?php 
/*
if (UserAuth::hasRole('admin') || UserAuth::isPageLoggedIn()) {
?>
<?php
} else {
	if ($is_expanded_db != 'Y') {
?>
<a style="margin-top:2px;" href="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&expand=Y");?>"><img src="image/maximize.jpg" border="0"></a>
<?php
	} else {
?>
<a style="margin-top:2px;" href="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&expand=N");?>"><img src="image/minimize.jpg" border="0"></a>
<?php
	}
}*/
?>
    <div id="poweredBy" style="position: absolute; right: 18px; top: 0;"><img alt="gPlex Contact Center" src="image/gplex_poweredby.png"></div>
</div>

<table class="ag-panel" cellpadding="0" border="0" cellspacing="0" width="98%" height="320" align="center">
    <tr>
        <td style="text-align:center; vertical-align:top;background: none repeat scroll 0 0 #E5F1F4; padding:5px;">


<div id="seats">
<?php if (is_array($seats)):?>
<?php foreach ($seats as $seat):?>

<div id="seat_<?php echo $seat->seat_id;?>" class="seat empty">
	<div class="agentpic" agent="">&nbsp;</div>
	<div class="seat-text">
        <div class="agent"></div>
        <div class="status"></div>
        <div class="seatinfo">Seat: <?php echo $seat->label;?></div>
    </div>
<?php /*
	<div class="ageventinfo" style="display:none; padding-top:5px;" agent="">
    	&nbsp; &nbsp; <a href="#" class="listen" title="Listen"><img src="image/agent_listen.gif" border="0"></a> 
		&nbsp; &nbsp; &nbsp; <a href="#" class="whisper" title="Whisper"><img src="image/agent_whisper.gif" border="0"></a> 
 //        &nbsp; <a href="#" class="dial" title="Dial"><img src="image/agent_dial.gif" border="0"></a> ;
	</div> */ ?>
    <div style="display:none;" class="seat_ip"><?php echo $seat->ip;?></div>
</div>

<?php endforeach;?>
<?php endif;?>

</div>
        </td>
    </tr>
</table>

</center>

</td>
</tr>
</table>

<div id="agent_queue_info" style="display:none;	width: 160px;"></div>

<div id="queue_detail_info" style="display:none; width:270px;"></div>
<!--
</div>
-->
</body>
</html>
<script>
	$("#LogoutBtn").click(function(){
		obj.Logout();
		$.ajax({
			type: "POST",
			url: "<?php echo $this->url("task=agent&act=add-dashboard-logged-out");?>"
		}).done(function (data) {
		   window.close();
		});
	});
</script>