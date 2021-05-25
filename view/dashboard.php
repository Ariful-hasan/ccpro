<html>
<head>
<title><?php echo $pageTitle;?></title>

<script type="text/JavaScript" src="js/jquery.min.js"></script>

<script type="text/javascript" src="js/jquery-ui.min.js"></script>

<script src="js/contextMenu/jquery.ui.position.js" type="text/javascript"></script>
<script src="js/contextMenu/jquery.contextMenu.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/contextMenu/jquery.contextMenu.css">

<script src="ccd/gPlexCCDashboardWS.js" type="text/javascript"></script>

<?php $is_expanded_db = "Y";

	$isInfoAllow = true;
	
	if(UserAuth::getDBSuffix()=="AA" || UserAuth::getDBSuffix()=="AC") {
		$isInfoAllow = true;
	}
?>
<script type="text/JavaScript">



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

//-----Dduplicate window will not open -- start------//
var dt = new Date();
var cts = Math.floor(dt.getTime()/1000);
$.cookie("isDashBoardAlreadyOpen",cts);
	
window.setInterval(function(){
	var dt = new Date();
	var cts = Math.floor(dt.getTime()/1000);
	$.cookie("isDashBoardAlreadyOpen",cts);
}, 15000);
/*
window.addEventListener("beforeunload", function (e) {
    var dt = new Date();
	var cts = Math.floor(dt.getTime()/1000);
	var cts = 15+5;
	$.cookie("isDashBoardAlreadyOpen",cts);
	//var confirmationMessage = 'If you leave this page, you dialer session will be lost.';

    //(e || window.event).returnValue = confirmationMessage; //Gecko + IE
    return true;//confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
});
*/
//-----Dduplicate window will not open -- end------//
	
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

<?php
/*
$languages = array();


$o = new stdClass();
$o->lang_key = "HI";
$o->lang_title = "Hindi";
$languages[] = $o;

$o = new stdClass();
$o->lang_key = "BN";
$o->lang_title = "Bangla";
$languages[] = $o;

$o = new stdClass();
$o->lang_key = "EN";
$o->lang_title = "English";
$languages[] = $o;
*/
?>

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
var objLanguages = [];
var colorSequence = ["blue","purple","green","red"];
objLanguages = jQuery.parseJSON('<?php echo json_encode($languages);?>');

var lang_n_color = {};
var classes = "";
var classes2 = "";
for(i=0; i<objLanguages.length; i++) {
	var l = objLanguages[i];
	var color = typeof colorSequence[i]!='undefined' ? colorSequence[i] : "";
	lang_n_color[l.lang_key] = color;
	classes += "."+color+",";
	classes2 += color+" ";
}

var dashboarduser = '<?php echo UserAuth::getCurrentUser();?>';

<?php
$colorSeq = array("blue","purple","green","red");
$language_n_color = array(); 
foreach($languages as $key=>$lang) {
	$language_n_color[$lang->lang_key] = $colorSeq[$key];
}

?>

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

objSeats = jQuery.parseJSON('<?php echo json_encode($seats);?>');

/*
var agentStacks = [];
for(i=0; i<objAgents.length; i++) {
	var agent = objAgents[i];
	var obj = {"agent_id":
}
*/
try {
	obj = new gPlexCCDashboardWSApi();
	//173.192.129.82
	//obj.loadSettings("ws://<?php echo $account_info->ws_ip;?>:<?php echo $account_info->ws_port;?>/chat", dashboarduser, $.cookie("wsCCSeatId"), $.cookie("wsWebKey"));
	obj.loadSettings("wss://rnd126.gtalkpbx.net:<?php echo $account_info->ws_port;?>/chat", dashboarduser, $.cookie("wsCCSeatId"), $.cookie("wsWebKey"));
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
	PopulateIvr: function(resp) {
		//var dimvalue = '<span class="dim-value">0</span>';
		//var end_ts = resp[0];
		var did = resp[2];
		var cli = resp[3];
		var call_id = resp[4];
		var lap = resp[5];
		var ivr_id = resp[6];
		
		var call_obj = {
			call_stat  : 'ivr',
			did        : did,
			cli        : cli,
			call_id    : call_id,
			//start_ts   : start_ts,
			lap	       : lap,
			skill_id   : '',
			ivr_id     : ivr_id,
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
			
			if(typeof o.seat_id!='undefined') {
				var result = $.grep(objSeats, function(e){ return e.seat_id == o.seat_id; });
				if(result.length>0) {
					//var date = new Date();
					//var start_ts = Math.floor(date.getTime()/1000);
					
					var o = result[0];
					var index = objSeats.indexOf(o);
					o.call_stat = '';
					o.call_id = '';
					//o.start_ts = start_ts;
					//o.end_ts = end_ts;
					o.lap = lap;
					o.status = 'Idle';
					o.css_class = 'idle';
					
					objSeats[index] = o;
				}
			}
			
		} else {
			//callsStack.push(call_obj);
		}
		callsStack.push(call_obj);
	
	},
	PopulateInQueue: function(resp) {
		//var end_ts = resp[0];
		var did = resp[2];
		var cli = resp[3];
		var call_id = resp[4];
		var lap = resp[5];
		var skill_id = resp[6];
		var seat_id = resp[7];
		var agent_id = resp[8];
		var status = resp[9];
		var language = resp.length>10 ? $.trim(resp[10]) : "";
		
		if(seat_id!=-1) {
			var result = $.grep(callsStack, function(e){ return e.seat_id == seat_id; });
			if(result.length>0) {
				for(i=0; i<result.length; i++) {
					var o = result[i];
					var index = callsStack.indexOf(o);
					callsStack.splice( index, 1 );
				}
			}
		}
		
		
		var call_obj = {
			call_stat  : 'inq',
			did        : did,
			cli        : cli,
			call_id    : call_id,
			//start_ts   : start_ts,
			//end_ts	   : end_ts,
			lap        : lap,
			skill_id   : skill_id,
			ivr_id     : '',
			agent_id   : agent_id,
			seat_id    : seat_id,
			language   : language
		};
		
		if(seat_id==-1 && status=='NO_RESPONSE') {
			var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
			if(result.length>0) {
				//for(j=0; j<result.length; j++) {
					var o = result[0];
					var index = callsStack.indexOf(o);
					callsStack.splice( index, 1 );
				//}
				
				var result = $.grep(objSeats, function(e){ return e.call_id == call_id; });
				if(result.length>0) {
					var o = result[0];
					var index = objSeats.indexOf(o);
					
					//o.aux_status = '';
					//o.start_ts = start_ts;
					//o.end_ts = end_ts;
					o.lap = lap;
					o.status = 'Idle';
					o.css_class = 'idle';
					o.call_stat = '';
					o.call_id = '';
					o.skill_name = '';
					o.language = '';
					
					objSeats[index] = o;
				}
			}
			return;
		}
		var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
		if(result.length>0) {
			var o = result[0];
			var index = callsStack.indexOf(o);
			o = $.extend(o, call_obj);
			callsStack[index] = o;
		} else {
			call_obj.lap = lap;
			callsStack.push(call_obj);
		}
		
		var result = $.grep(objSkills, function(e){ return e.skill_id == skill_id; });
		skill_name = "";
		if(result.length>0) {
			var o = result[0];
			skill_name = o.skill_name;
		}
		
		if(status=='RINGING' || status=='CALLING') { status='Ringing'; cssClass = 'ring'; }
		else if(status=='CONNECTED') { status = 'In Service'; cssClass = 'active'; }
		else if(status=='MISS_CALL') { status = 'Missed Call'; cssClass = 'hold-out'; }
		else if(status=='IN_PROGRESS') { status = 'Ringing'; cssClass = 'active'; }
		else { status='Idle'; cssClass = 'idle'; }
		
		
		//console.log(agent_id);
		//console.log(objAgents);
		
		var result = $.grep(objSeats, function(e){ return e.seat_id == seat_id; });
		if(result.length>0) {
			var o = result[0];
			var index = objSeats.indexOf(o);
			o.seat_id
			//o.aux_status = '';
			//o.start_ts = start_ts;
			//o.end_ts = end_ts;
			o.lap = lap;
			o.status = status;
			o.css_class = cssClass;
			o.call_stat = 'inq';
			o.call_id = call_id;
			o.skill_name = skill_name;
			
			objSeats[index] = o;
		}
		
	},
	PopulateInService: function(resp) {
		//var end_ts = resp[0];
		var did = resp[2];
		var cli = resp[3];
		var call_id = resp[4];
		var status = resp[9];
		var skill_id = resp[10];
		//var start_ts = resp[5];
		var lap = resp[5];
		var agent_id = resp[6];
		var seat_id = resp[7];
		
		var result = $.grep(callsStack, function(e){ return e.seat_id == seat_id; });
		if(result.length>0) {
			for(i=0; i<result.length; i++) {
				var o = result[i];
				var index = callsStack.indexOf(o);
				callsStack.splice( index, 1 );
			}
		}
		
		var call_obj = {
			call_stat  : 'ins',
			did        : did,
			cli        : cli,
			call_id    : call_id,
			//start_ts   : start_ts,
			lap	       : lap,
			skill_id   : skill_id,
			ivr_id     : '',
			agent_id   : agent_id,
			seat_id    : seat_id,
			lng        : status,
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
		
		//var status = lng;
		if(status=='RINGING') { status='Ringing'; cssClass = 'ring'; }
		else if(status=='CONNECTED') { status='In Service'; cssClass = 'active'; }
		else if(status=='MISS_CALL') { status='Missed Call'; cssClass = 'hold-out'; }
		else { cssClass = 'idle'; }
		
		var result = $.grep(objSkills, function(e){ return e.skill_id == skill_id; });
		skill_name = "";
		if(result.length>0) {
			var o = result[0];
			skill_name = o.skill_name;
		}
		
		var result = $.grep(objSeats, function(e){ return e.seat_id == seat_id; });
		if(result.length>0) {
			var o = result[0];
			var index = objSeats.indexOf(o);
			o.seat_id = seat_id;
			o.aux_status = '';
			//o.start_ts = start_ts;
			o.lap = lap;
			o.status = status;
			o.css_class = cssClass;
			o.call_stat = 'ins';
			o.call_id = call_id;
			o.skill_name = skill_name;
			
			objSeats[index] = o;
		}
				
		//$('#seat_'+seatid).removeClass("ring active idle").addClass(cssClass);
		
	},
	PopulateBye: function(resp) {
		
		var lap = resp[0];
		var call_id = resp[2];
		var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
		
		//var date = new Date();
		//var start_ts = Math.floor(date.getTime()/1000);
				
		if(result.length>0) {
			var obj = result[0];
			var index = callsStack.indexOf(obj);
			callsStack.splice( index, 1 );
			
			if(obj.call_stat=='inq') {
				
				var result = $.grep(objSeats, function(e){ return e.seat_id == obj.seat_id; });
				if(result.length>0) {
					var o = result[0];
					var index = objSeats.indexOf(o);
					o.call_id = '';
					//o.start_ts = start_ts;
					//o.end_ts = start_ts;
					o.lap = lap;
					o.status = 'Idle';
					o.css_class = 'idle';
					o.skill_name = '';
					
					objSeats[index] = o;
				}
			}
			else if(obj.call_stat=='ins') {
				
				var result = $.grep(objSeats, function(e){ return e.seat_id == obj.seat_id; });
				if(result.length>0) {
					var o = result[0];
					var index = objSeats.indexOf(o);
					o.call_id = '';
					//o.start_ts = start_ts;
					o.lap = lap;
					o.status = 'Idle';
					o.css_class = 'idle';
					o.skill_name = '';
					
					objSeats[index] = o;
				}
				
				//if(typeof o.seat_id!='undefined') $("#seat_"+o.seat_id).removeClass("hold-out idle ring active activeo").addClass("idle");
			}
			else if(obj.call_stat=='ivr') {
				//var skill_id = o.skill_id;
				
				//$("#cntI0").html(dimvalue);
			} else if(obj.call_stat=='inc') {
				var result = $.grep(objSeats, function(e){ return e.seat_id == obj.df_seat_id; });
				if(result.length>0) {
					var o = result[0];		
					var index = objSeats.indexOf(o);
					if(typeof o.agent_id!='undefined' && o.agent_id.length>0) {
						cssClass = 'idle';
						status = 'Idle';
					} else {
						cssClass = 'enable';
						status = '';
						lap = 0;
					}
					o.call_id = '';
					o.lap = lap;
					o.status = status;
					o.css_class = cssClass;
					o.skill_name = '';
					o.dial_from  = '';
					o.df_seat_id = '';
					o.dial_to    = '';
					o.dt_seat_id = '';
					objSeats[index] = o;
				}
				var result = $.grep(objSeats, function(e){ return e.seat_id == obj.dt_seat_id; });
				if(result.length>0) {
					var o = result[0];		
					var index = objSeats.indexOf(o);
					if(typeof o.agent_id!='undefined' && o.agent_id.length>0) {
						cssClass = 'idle';
						status = 'Idle';
					} else {
						cssClass = 'enable';
						status = '';
						lap = 0;
					}
					o.call_id = '';
					o.lap = lap;
					o.status = status;
					o.css_class = cssClass;
					o.skill_name = '';
					o.dial_from  = '';
					o.df_seat_id = '';
					o.dial_to    = '';
					o.dt_seat_id = '';
					objSeats[index] = o;
				}
			}
			
			
		} 
	},
	PopulateSeat: function(resp) {
		var status = "";
		var cssClass = "enable";
		
		//var end_ts = $.trim(resp[0]);
		var seat_id = $.trim(resp[2]);
		var agent_id = $.trim(resp[3]);
		var call_id = $.trim(resp[4]);
		var busy_status = $.trim(resp[5]);
		var aux_status = $.trim(resp[6]);
		var aux_type = $.trim(resp[7]);
		var aux_code = $.trim(resp[8]);
		var lap = parseInt($.trim(resp[9]));
		var language = resp.length>10 ? $.trim(resp[10]) : "";
		language = language.split(',');
		//var date = new Date();
		//var end_ts = Math.floor(date.getTime()/1000);
		
		/*
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
		}*/
		var agent_name = "";
		if(agent_id.length>0) {
			var result = $.grep(objAgents, function(e){ return e.agent_id == agent_id; });
			if(result.length>0) {
				var o = result[0];
				agent_name = o.nick;
			
				status = 'Idle';
				cssClass = 'idle';
				if(aux_status=="B" || busy_status=="O") {
					if(aux_code==0) {
						status = "Busy";
					} else {
						var result = $.grep(objAuxs, function(e){ return e.aux_code == aux_code; });
						if(result.length>0) { var o = result[0]; status = o.message; }
					}
					//status += aux_status=="O" ? "[OUT]" : "[IN]"; 
					cssClass = "hold-out";
				}
				/*
				if(busy_status=='I' || busy_status=='O') { status = "Idle"; cssClass = "idle"; }
				else if(busy_status=='R') { status = "Ringing"; cssClass = "ring"; }
				else if(busy_status=='S') { status = "In Service"; cssClass = "active"; }
				else if(busy_status=='B') { 
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
				*/
				cssClass = agent_id.length!=0 ? cssClass : 'enable';

			}
		}
		
		var result = $.grep(objSeats, function(e){ return e.seat_id == seat_id; });
		if(result.length>0) {
			var o = result[0];
			
			var index = objSeats.indexOf(o);
			o.seat_id = seat_id;
			//o.call_id = call_id;
			//o.busy_status = busy_status;
			o.aux_status = aux_status;
			o.aux_type = aux_type;
			o.busy_status = busy_status;
			o.aux_code = aux_code;
			//o.start_ts = start_ts;
			o.lap = lap;
			o.status = status;
			o.css_class = cssClass;
			o.agent_id = agent_id;
			o.agent_name = agent_name;
			o.skill_name = '';
			o.language = language;
			o.seat_registered = typeof agent_id!='undefined' && agent_id.length>0 ? true : false;
			
			objSeats[index] = o;
		}
		

		
		
	},
	PopulateAux: function(resp) {
		var status = '';
		var cssClass = '';
		var lap = resp[0];
		var agentid = $.trim(resp[2]);
		var seat_id = $.trim(resp[3]);
		var aux_type = $.trim(resp[4]);
		var aux_status = $.trim(resp[5]);
		var busy_status = $.trim(resp[6]);
		var aux_code = $.trim(resp[7]);
		//var date = new Date();
		//var start_ts = end_ts;//Math.floor(date.getTime()/1000);

		var result = $.grep(objAuxs, function(e){ return e.aux_code == aux_code; });
		if(result.length>0) { var o = result[0]; status = o.message; }
		
		if(aux_status!='B') {
			//staffing++;
			if(busy_status=='I') { status = 'Idle'; cssClass='idle'; }
			else if(busy_status=='R') { status = 'Ringing'; cssClass='ring'; }
			else if(busy_status=='S') { status = "In Service"; cssClass='active'; }
			else if(busy_status=='B') { status = 'Busy'; cssClass='hold-out'; }
			else { cssClass='idle'; }
		} else {
			if(aux_type=="O") { cssClass='hold-out'; }
			else if(aux_type="I") { cssClass='hold-out'; }
		}

		result = $.grep(objSeats, function(e){ return e.seat_id == seat_id; });
		var agent_name = "";
		if(result.length>0) {
			var o = result[0];		
			var index = objSeats.indexOf(o);
			o.aux_type = aux_type;
			o.busy_status = busy_status;
			o.aux_status = aux_status;
			o.aux_code = aux_code;
			//o.start_ts = start_ts;
			o.lap = lap;
			o.status = status;
			o.css_class = cssClass;
			o.skill_name = '';
			
			objSeats[index] = o;
		}
		
		//var staffin = staffing - staffout;
		//$("#sum_staff").html(staffing + ':' + staffin);
	},
	PopulateOutgoingCall: function(resp) {
		
		var dt_seat_id = resp[5];
		var dial_to = resp[4];
		if(dial_to.length==5) return;
		
		//var end_ts = resp[0];
		
		var df_seat_id = resp[3];
		var call_id = resp[6];
		var lap = resp[7];
		var status = ucfirst(resp[8]);
		
		var result = $.grep(callsStack, function(e){ return e.seat_id == dt_seat_id; });
		if(result.length>0) {
			for(i=0; i<result.length; i++) {
				var o = result[i];
				var index = callsStack.indexOf(o);
				callsStack.splice( index, 1 );
			}
		}
		
		var result = $.grep(callsStack, function(e){ return e.seat_id == df_seat_id; });
		if(result.length>0) {
			for(i=0; i<result.length; i++) {
				var o = result[i];
				var index = callsStack.indexOf(o);
				callsStack.splice( index, 1 );
			}
		}
		
		var call_obj = {
			call_stat  : "inc",
			dial_from  : resp[2],
			df_seat_id : df_seat_id,
			dial_to    : dial_to,
			dt_seat_id : dt_seat_id,
			call_id    : call_id,
			//start_ts   : start_ts,
			lap        : lap,
			dir        : resp[8],
			agent_id   : ''
			//inc_status : ucfirst(resp[9]),
			//inc_class  : 'activeo'
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
		
		//if(status=="Calling" || status=="Ringing") { status="Dialing"; cssClass = "ring"; }
		//if(status=="Connected") { status="In Service"; cssClass = "activeo"; }
				
		var result = $.grep(objSeats, function(e){ return e.seat_id == df_seat_id; });
		if(result.length>0) {
			var o = result[0];
			var index = objSeats.indexOf(o);
			if(typeof o.agent_id!='undefined' && o.agent_id.length>0) {
				if(status=="Calling" || status=="Ringing") { status="Dialing"; cssClass = "ring"; }
				if(status=="Connected") { status="In Service"; cssClass = "activeo"; }
			} else {
				if(status=="Calling" || status=="Ringing") { status="Dialing"; cssClass = "ring"; }
				if(status=="Connected") { status='In Service'; cssClass = "headphone"; }
			}
			o.call_stat = 'inc';
			o.call_id = call_id;
			//o.start_ts = start_ts;
			o.lap = lap;
			o.status = status;
			o.css_class = cssClass;
			
			objSeats[index] = o;
		}
		
		//if(dt_seat_id=='0000') return;
		
		var result = $.grep(objSeats, function(e){ return e.seat_id == dt_seat_id; });
		if(result.length>0) {
		
			var o = result[0];
			var index = objSeats.indexOf(o);
			
			if(typeof o.agent_id!='undefined' && o.agent_id.length>0) {
				if(status=="Calling" || status=="Ringing") { status="Ringing"; cssClass = "ring"; }
				if(status=="Connected") { status="In Service"; cssClass = "activeo"; }
			} else {
				if(status=="Calling" || status=="Ringing") { status="Ringing"; cssClass = "ring"; }
				if(status=="Connected") { status='In Service'; cssClass = "headphone"; }
			}
			
			o.call_stat = 'inc';
			o.call_id = call_id;
			//o.start_ts = start_ts;
			o.lap = lap;
			o.status = status;
			o.css_class = cssClass;
			objSeats[index] = o;
			
		}
		
	},
	Close: function() {
		obj.Close();
		alert("Dashboard already opened in another location");
		window.close();
	}
}


function getTimeDifference(diff) {
	//var diff = endtime - starttime;

	/*
	if(diff < 0 ||diff > 20000) return "00:00";
	//diff = Math.floor(diff / 1000);
	
	var secs_diff = diff % 60;
	secs_diff = secs_diff<10 ? '0'+secs_diff : secs_diff;
	
	diff = Math.floor(diff / 60);
	
	var mins_diff = diff % 60;
	mins_diff = mins_diff<10 ? '0'+mins_diff : mins_diff;
	
	diff = Math.floor(diff / 60*60);
	
	var lap = mins_diff+':'+secs_diff;
	*/
	
	var date = new Date(diff * 1000);
	var hh = date.getUTCHours();
	var mm = date.getUTCMinutes();
	var ss = date.getSeconds();
	// If you were building a timestamp instead of a duration, you would uncomment the following line to get 12-hour (not 24) time
	// if (hh > 12) {hh = hh % 12;}
	// These lines ensure you have two-digits
	//if (hh < 10) {hh = "0"+hh;}
	if (mm < 10) {mm = "0"+mm;}
	if (ss < 10) {ss = "0"+ss;}
	// This formats your string to HH:MM:SS
	var lap;
	if(hh=='00') lap = mm+":"+ss;
	else lap = hh+":"+mm+":"+ss;
	//console.log(lap);
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
	//var mins_diff = diff % 60;
	mins_diff = diff;
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

var refreshTimer = 0;
var maxRefreshTimer = 30*60;
function secondInterval() {

	//$(".lang").removeClass("english spanish hindi bengali");
	$(".lang").removeClass(classes2);
	
	var staffing = 0;
	var staffin = 0;
	var staffout = 0;
	
	refreshTimer++;
	
	if(refreshTimer >= maxRefreshTimer) {
		refreshTimer=0;
		window.location.href=window.location.href;
		return;
	}
	
	for(i=0; i<objSeats.length; i++) {
		var o = objSeats[i];
		var lap = typeof o.lap!='undefined' && o.lap.length!=0 ? o.lap : 0;
		o.lap = parseInt(lap)+1;
		objSeats[i] = o;
		
		var seat_id = o.seat_id;
		var status = typeof o.status!='undefined' ? o.status : '';
		var agent_id = typeof o.agent_id=='undefined' ? '' : o.agent_id;
		var agent_name = typeof o.agent_name!='undefined' ? o.agent_name+"<br>" : '';
		var css_class = typeof o.css_class!='undefined' ? o.css_class : '';
		var language = typeof o.language!='undefined' ? o.language : '';
		/*
		if(css_class=='ring' && o.lap>60) {
			o.status = agent_id.length>0 ? 'Idle' : '';
			o.css_class= agent_id.length>0 ? 'idle' : 'enable';
			o.skill_name='';
			o.call_id='';
			o.lap = 0;
			objSeats[i] = o;
			continue;
		}*/
		//var start_ts = o.start_ts;
		//var end_ts = o.end_ts;
		
		var skill_name = typeof o.skill_name!='undefined' ? o.skill_name : '';
		var call_id = typeof o.skill_name!='undefined' ? o.call_id : 0;
		var busy_status = typeof o.busy_status=='undefined' ? '' : o.busy_status;
		
		var aux_status = typeof o.aux_status!='undefined' ? o.aux_status : '';
		var seat_registered = typeof o.seat_registered!='undefined' ? o.seat_registered : false;
		var aux_type = typeof o.aux_type!='undefined' ? o.aux_type : '';
		
		if(seat_registered) {
			staffing++;
			if(agent_id.length>0) {
				if(aux_status=="B") {
					if(aux_type=="O") staffout++;
				}
			} //else staffout++;
			
		}
		

		var staffin = staffing - staffout;
		$("#sum_staff").html(staffing + ':' + staffin);
		
		/*
		var result = $.grep(callsStack, function(e){ return e.call_id == call_id; });
		if(result.length>0) {
			var ob = result[0];
			if(ob.call_stat=='inc') {
				status = ob.inc_status;
				css_class = ob.inc_class;
			}
		}*/
		//console.log(start_ts);
		//console.log(end_ts);
		//console.log("--------");
		var lap = ((typeof o.agent_id!='undefined' && o.agent_id.length>0) || (typeof o.call_id!='undefined' && o.call_id.length>0)) && typeof o.lap!='undefined' && !isNaN(o.lap) && o.lap>=0 ? '<br>('+getTimeDifference(lap)+')' : '';
		
		skill_name = (typeof skill_name!='undefined' && skill_name.length>0) ? skill_name+"<br>" : '';
		
		$('#seat_'+seat_id).removeClass("hold-out idle ring active activeo headphone").addClass("show "+css_class);
		$('#seat_'+seat_id+' div.status').html(agent_name+skill_name+status+lap);
		//var $s = $('#seat_'+seat_id+' div.language div.lang');
		for(j=0; j<language.length; j++) {
			var lang_key = language[j];
			//var css_class = "";
			//if(lng=='EN') css_class="english";
			//if(lng=='ES') css_class="spanish";
			//if(lng=='HI') css_class="hindi";
			//if(lng=='BN') css_class="bengali";
			try {
				if(lang_key.length>0) {
					if($('#seat_'+seat_id+' div.language .lang .'+lang_n_color[lang_key]).length==0) 
						$('#seat_'+seat_id+' div.language .lang'+j).addClass(lang_n_color[lang_key]);
				}
			} catch(e){}
			
		}
		//staffing++;
	}
	
	
	/*
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
			var lap = getTimeDifference(start_ts, end_ts)
			
			skill_name = (typeof skill_name!='undefined' && skill_name.length>0) ? skill_name+"<br>" : '';
			
			$('#seat_'+seat_id).removeClass("hold-out idle ring active activeo").addClass("show "+css_class);
			$('#seat_'+seat_id+' div.status').html(agent_name +'<br>'+skill_name+status+'<br>('+lap+')');
			staffing++;
		}
	}
	var staffin = staffing - staffout;
	$("#sum_staff").html(staffing + ':' + staffin);
	*/
	
	
	//calls info update
	var ivrCount = 0;
	var incCount = 0;
	var ivrMaxTime = 0;
	var incMaxTime = 0;
	var totalIvrTime = 0;
	var totalIncTime = 0;
	var ivrAvgTime = 0;
	var incAvgTime = 0;
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
		o.lap = parseInt(o.lap)+1;
		callsStack[i] = o;
		
		if(o.call_stat=='ivr') {
			ivrCount++;
			var ivrSeconds = o.lap;//getTimeDifferenceInSeconds(o.start_ts, o.end_ts);
			if(ivrSeconds>ivrMaxTime) ivrMaxTime = ivrSeconds;
			totalIvrTime += ivrSeconds;
		} else if(o.call_stat=='inq') {
			var skill_id = o.skill_id;
			if(o.language.length>0) {
				
				//var css_class="";
				//if(o.language=='EN') css_class="english";
				//else if(o.language=='ES') css_class="spanish";
				//else if(o.language=='HI') css_class="hindi";
				//else if(o.language=='BN') css_class="bengali";
				try{
					//$($("#inqLang"+skill_id+" .lang:not(.english,.spanish,.hindi,.bengali)")[0]).addClass(lang_n_color[o.language]); 
					$($("#inqLang"+skill_id+" .lang:not("+classes+")")[0]).addClass(lang_n_color[o.language]); 
					
				} catch(e){}
				/*if(!$ln.hasClass('english') && !$ln.hasClass('spanish') && !$ln.hasClass('hindi') && !$ln.hasClass('bengali')) {
						$ln[0].addClass(css_class);
						
				}*/					
				//console.log($ln);
				/*for(j=0; j<$ln.length; j++) {
					$item = $($ln[j]);
					//console.log($item);
					if(!$item.hasClass('english') && !$item.hasClass('spanish') && !$item.hasClass('hindi') && !$item.hasClass('bengali')) {
						$item.addClass(css_class);
						break;
					}
				}*/
			}
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
			//incCount++;
			//var incSeconds = o.lap;
			//if(ivrSeconds>ivrMaxTime) ivrMaxTime = ivrSeconds;
			//totalIvrTime += ivrSeconds;
			
			if(o.dial_to.length>4) {
				outboundCount++;
				var incSeconds = o.lap;
				if(incSeconds>incMaxTime) incMaxTime = incSeconds;
				totalIncTime += incSeconds;
			}
			//$('#seat_'+o.df_seat_id).removeClass("hold-out idle ring active activeo").addClass("activeo");
			//$('#seat_'+o.dt_seat_id).removeClass("hold-out idle ring active activeo").addClass("activeo");
		}
	}
	
	ivrCount = ivrCount>0 ? ivrCount : 0;
	ivrAvgTime = ivrCount>0 ? secondsToMMSS(Math.ceil(totalIvrTime/ivrCount)) : dimvalue;
	ivrMaxTime = ivrMaxTime>0 ? secondsToMMSS(ivrMaxTime) : dimvalue;
	
	ivrCount = ivrCount>0 ? ivrCount : dimvalue;
	
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
	
	var inqStartEndTimestamps = {};
	var inqResult = $.grep(callsStack, function(e){ return e.call_stat == 'inq'; });
	for(i=0; i<inqResult.length; i++) {
		var o = inqResult[i];
		var sec = o.lap;//getTimeDifferenceInSeconds(o.start_ts, o.end_ts);
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
	
	outboundCount = outboundCount>0 ? outboundCount : 0;
	incAvgTime = outboundCount>0 ? secondsToMMSS(Math.ceil(totalIncTime/outboundCount)) : dimvalue;
	incMaxTime = incMaxTime>0 ? secondsToMMSS(incMaxTime) : dimvalue;
	
	outboundCount = outboundCount==0 ? dimvalue : outboundCount;
	$("#cntOB").html(outboundCount);
	$("#atOB").html(incAvgTime);
	$("#mtOB").html(incMaxTime);
	
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
	height: 52px;
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
#seats .seat.headphone{
	background-image:url(image/head_phone.gif) !important;
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
	height:136px;
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



/*--------------language color box---------------*/
.clear { clear:both; width:0; height:0; }
/*
#queues .language, #seats .seat .language{
	border-collapse: separate;
	border-spacing: 2px;
	display: table;
	width: 100%;
}
#queues .language .lang, #seats .seat .language .lang, .note {
	border: 1px solid transparent;
	display: table-cell;
	height: 6px;
	text-align: center;
}*/

#queues .language, #seats .seat .language {
  border-collapse: separate;
  border-spacing: 1px;
  display: table;
  
  position: absolute;
  right: 5px;
  
  width: 109%;
}

#queues .language {
	top: 32px;
	width: 109%;
	left: -3px;
}

#seats .seat .language {
	top: 66px;
	width: 104%;
	left: -2px;
}

#queues .language .lang, #seats .seat .language .lang, .note {
  border: 1px solid transparent;
  display: table-cell;
  height: 5px;
  text-align: center;
  background-color:#b4b4b4;
}
.note { width:35px; height:7px; border:none; }

/*#queues .language .lang.none, #seats .seat .language .lang.none { background-color:#b4b4b4; }*/
#queues .language .lang.english, #seats .seat .language .lang.english, .note.english, #infoContainer .lng.english, { background-color:#6F69FF; }
#queues .language .lang.blue, #seats .seat .language .lang.blue, .note.blue, #infoContainer .lng.blue { background-color:#6F69FF; }

#queues .language .lang.bengali, #seats .seat .language .lang.bengali, .note.bengali, .note.bangla, #infoContainer .lng.bengali  { background-color:#E663C7; }
#queues .language .lang.purple, #seats .seat .language .lang.purple, .note.purple, #infoContainer .lng.purple  { background-color:#E663C7; }

#queues .language .lang.spanish, #seats .seat .language .lang.spanish, .note.spanish, #infoContainer .lng.spanish { background-color:#24E322;/*green;*/ }
#queues .language .lang.green, #seats .seat .language .lang.green, .note.green, #infoContainer .lng.green { background-color:#24E322;/*green;*/ }

#queues .language .lang.hindi, #seats .seat .language .lang.hindi, .note.hindi, #infoContainer .lng.hindi { background-color:#F29F93;/*blue;*/ }
#queues .language .lang.red, #seats .seat .language .lang.red, .note.red, #infoContainer .lng.red { background-color:#F29F93;/*blue;*/ }

.note.english,.note.spanish,.note.hindi, .note.bengali, .note.bangla { display:block !important; }
.note.blue,.note.green,.note.red, .note.purple { display:block !important; }

#infoContainer { position:absolute; display:none; background-color:#FFFFFF; width:620px; border:5px solid #75A5B3; z-index:1; border-radius:5px; }
#infoContainer table { border-collapse: collapse; }
#infoContainer table th { background-color:#E5F1F4; text-align:left; text-indent:20px; padding:3px; }
#infoContainer table td { border:1px solid #75A5B3; padding:5px; }
#infoContainer .lng { width:50px; height:5px; display:block; margin:2px; }
.tooltips { position:absolute; background-color:#FFFFFF; padding:5px; border:3px solid #75A5B3; border-radius:3px; z-index:1; font-size:11px; display:none;  }
.tooltips b { font-size:15px; }
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
	} else {
	    $image_src = 'image/blank_logo.png';
	}
	
	$infoArray = array(
		array(
			array("img"=>"seat_enable.gif","desc"=>"Agent not logged in"),
			array("img"=>"agent_idle.gif","desc"=>"Agent idle")
		),
		array(
			array("img"=>"seat_empty.gif","desc"=>"Unregistered Seat"),
			array("img"=>"agent_talking.gif","desc"=>"Agent attending inbound call")
		),
		array(
			array("img"=>"head_phone.gif","desc"=>"Agent talking without logging in"),
			array("img"=>"agent_talking_out.gif","desc"=>"Agent attending outbound call"),
		),
		array(
			array("img"=>"ringing.png","desc"=>"Call ringing"),
			array("img"=>"agent_st_hold_out.gif","desc"=>"Agent busy (work/short break)")
		)
	);
	


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
        	<table width="225"><tr>
				<td width="60" style="font-size:20px;" class='note-tips'>
				Calls:
				<?php if($isInfoAllow) { ?>
				<div class='tooltips'>
				<strong>Calls</strong><br>
				Total Running Calls <b>:</b> Total Calls in Queue <b>:</b> Total Calls in Service
				</div>
				<?php } ?>
				</td>
				<td class="qheadinfo" align="left"><span id="sum_calls"></span> &nbsp;</td></tr></table>
         	</td><td id="tr-mht">
            <table width="225"><tr>
			<td width="60" style="font-size:20px;" class='note-tips'>
				MHT:
				<?php if($isInfoAllow) { ?>
				<div class='tooltips'>
				<strong>Maximum Hold Time</strong><br>
				Minutes <b>:</b> Seconds
				</div>
				<?php } ?>
				</td><td class="qheadinfo" align="left"><span id="sum_mht"></span> &nbsp;</td></tr></table>
			</td><td>
         	<table width="225"><tr><td width="100" style="font-size:20px;" class='note-tips'>Staffing:
			<?php if($isInfoAllow) { ?>
				<div class='tooltips'>
					<strong>Staffing</strong><br>
				No of agents logged in <b>:</b> No of agent in seat
				</div>
				<?php } ?>
			</td><td class="qheadinfo" align="left"><span id="sum_staff"></span></td></tr></table>
			</td></tr></table>
        
        </td>
        <td style="background:url(<?php echo $image_src; ?>) no-repeat right bottom; width:220px; height:46px;">
			
			<table height='100%'>
				<?php 
				if(count($languages)>1) {
				foreach ($languages as $language): ?>
				<tr>
					<td><div class='note <?php echo strtolower($language_n_color[$language->lang_key]);?>'></div></td>
					<td><?php echo $language->lang_title;?></td>
				</tr>
				<?php endforeach;
				}
				?>
			</table>
		</td>
		<td width='60'>
		
			<?php if($isInfoAllow) { ?>
			
			<img id='btnInfo' src='image/info.png'></a>
			<div id="infoContainer"><table width="100%">
				<tr>
					<th colspan='4'>Legend</th>
				</tr>
				<tr>
					<td width='80' align='center'><b>Figure</b></td>
					<td><b>Description</b></td><td width='80' align='center'><b>Figure</b></td>
					<td><b>Description</b></td>
				</tr>
				<?php
				foreach($infoArray as $array) {
				?>
					<tr>
						<td align='center'><img src='image/<?php echo $array[0]["img"]; ?>'></td>
						<td><?php echo $array[0]["desc"]; ?></td>
						<td align='center'><img src='image/<?php echo $array[1]["img"]; ?>'></td>
						<td><?php echo $array[1]["desc"]; ?></td>
					</tr>
				<?php
				}
				?>
				<tr height='60'><td align='center'>
				<div class="language">

		
					<?php 
					if(count($languages)>1) {
					$k = 0;
					foreach ($languages as $language):?>
					<span class="lng <?php echo $colorSeq[$k]; ?>"></span>
					<?php 
					$k++;
					endforeach;
					}
					?>
					
					<div class='clear'></div>
				</div>
				</td><td>Language indicator</td></tr>
				</table>
			</div>
			<?php
			}
			?>
		</td>
		<td align='right' class='note-tips'><img src='image/logout.png' height='28' id='LogoutBtn' style='cursor:pointer;'>
				<div class='tooltips' style='margin:5px 10px 0 0;'>
				<strong style='color:#ff0000'>Logout</strong>
				</div>
			</td>
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
<tr>
	<td style='position:relative;'><div id="inq<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div>
	<div id='inqLang<?php echo $skill->skill_id;?>' class="language">
		
		<?php 
		if(count($languages)>1) {
		foreach ($languages as $language):?>
		<span class="lang"></span>
		<?php endforeach;
		}
		?>
		
		<div class='clear'></div>
	</div>
</td>
<td class="qbox-col-left-border"><div id="srv<?php echo $skill->skill_id;?>" class='valbox'><span class="dim-value">0</span></div><div class="language"></div></td></tr>
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
<tr><td colspan="2"><div id="cntOB"><span class="dim-value">0</span></div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo">Avg Time</div></td><td class="qbox-col-left-border"><div class="qinfo">Max Time</div></td></tr>
<tr><td><div id="atOB"><span class="dim-value">0</span></div></td><td class="qbox-col-left-border"><div id="mtOB"><span class="dim-value">0</span></div></td></tr>
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

<div id="seat_<?php echo $seat->seat_id;?>" class="seat empty" style='position:relative;'>
	<div class="agentpic" agent="">&nbsp;</div>
	<div class="seat-text">
        <div class="agent"></div>
        <div class="status"></div>
        <div class="seatinfo"><?php echo $seat->label;?></div>
    </div>
<?php /*
	<div class="ageventinfo" style="display:none; padding-top:5px;" agent="">
    	&nbsp; &nbsp; <a href="#" class="listen" title="Listen"><img src="image/agent_listen.gif" border="0"></a> 
		&nbsp; &nbsp; &nbsp; <a href="#" class="whisper" title="Whisper"><img src="image/agent_whisper.gif" border="0"></a> 
 //        &nbsp; <a href="#" class="dial" title="Dial"><img src="image/agent_dial.gif" border="0"></a> ;
	</div> */ ?>
    <div style="display:none;" class="seat_ip"><?php echo $seat->ip;?></div>
	<div class="language">

		
		<?php 
		if(count($languages)>1) {
		$k = 0;
		foreach ($languages as $language):?>
		<span class="lang lang<?php echo $k; ?>"></span>
		<?php 
		$k++;
		endforeach;
		}
		?>
		
		<div class='clear'></div>
	</div>
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
	$("#btnInfo").mouseover(function(){
		var left = $(this).position().left;
		var top = $(this).position().top;
		var width = $(this).width();
		var height = $(this).height();
		$("#infoContainer").css({"left":left+width-600+'px',"top":top+height+10});
		$("#infoContainer").show();
	}).mouseout(function(){
		$("#infoContainer").hide();
	});
	$(".note-tips").mouseover(function(){
		$(this).find(".tooltips").show();
	}).mouseout(function(){
		$(this).find(".tooltips").hide();
	});
</script>