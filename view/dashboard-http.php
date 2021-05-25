<html>
<head>
<title><?php echo $pageTitle;?></title>

<script type="text/JavaScript" src="js/jquery.min.js"></script>

<script type="text/javascript" src="js/jquery-ui.min.js"></script>

<script src="js/contextMenu/jquery.ui.position.js" type="text/javascript"></script>
<script src="js/contextMenu/jquery.contextMenu.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/contextMenu/jquery.contextMenu.css">

<script type="text/JavaScript">
$(document).ready(function(){

	$(document).bind("contextmenu",function(e){
        e.preventDefault();
    });
	$(document).disableSelection();

	var updateMsg = function() {
		$.ajax({type: "POST", url: "dashboard_data.php", data: "dlg=2", dataType: "json", success: function(json) {
			if (json.reload == 'Y') {window.location.reload();return;}
			
			/*
			if (typeof json.calls != 'undefined') {
				$("#sum_calls").html(json.calls + ':' + json.inq);
				if (json.mht == '0') {
					$("#tr-mht").hide();
				} else {
					$("#tr-mht").show();
					$("#sum_mht").html(' ' + json.mht);
				}
				$("#sum_staff").html(json.staffin + ':' + json.staffout);
			}
			*/
			var staffin = 0;
			var staffout = 0;
			var allcalls = 0;
			var inq = 0;
			//$('.seat').removeClass("active idle ring dial hold hold-out").addClass("empty");
			<?php if ($is_expanded_db != 'Y'):?>
			$('.seat').removeClass("active activeo idle ring dial hold hold-out show").addClass("empty hide");
			<?php else:?>
			$('.seat').removeClass("active activeo idle ring dial hold hold-out").addClass("empty");
			<?php endif;?>
			$('.seat div.agent').empty();
			$('.seat div.status').empty();
			//$('.seat div.ageventinfo').hide(); --- remove
			$('.agentpic').removeClass("serving");
			
			//$trigger.data('whisperDisabled', false);

			for (prop in json.seat) {

				if (prop.length > 0) {

					var _sObj = json.seat[prop];
					var _seat = _sObj.cid;
					
					<?php if ($is_expanded_db != 'Y'):?>
					if (typeof available_agents[_sObj.aid]!='undefined') $('#seat_'+_seat).removeClass("hide").addClass("show");
					<?php endif;?>

					var _status = _sObj.stat;
					var _txtActivity = '';
					var _skill_name = '';
					if (null != _sObj.srv) {
						if(_sObj.srv.length == 0) {
							_skill_name = '';
						} else {
							_skill_name = (typeof available_services[_sObj.srv]=='undefined') ? _sObj.srv : available_services[_sObj.srv];
						}
					} else {
						_skill_name = '';
					}
						
					if(_status == 'B') {
						$('#seat_'+_seat).addClass("active");
						_txtActivity = _skill_name.length > 0 ? _skill_name + ' (' + _sObj.lap  + ')' : _sObj.lap;
						$('#seat_'+_seat+' div.status').html(_txtActivity);
						$('#seat_'+_seat+' div.agentpic').addClass("serving");
						
					} else if(_status == 'S') {
						var srvtype = !isNaN(_sObj.srv) ? 'activeo' : 'active';
						$('#seat_'+_seat).addClass(srvtype);
						_txtActivity = _skill_name.length > 0 ? _skill_name + ' (' + _sObj.lap  + ')' : _sObj.lap;
						$('#seat_'+_seat+' div.status').html(_txtActivity);
						$('#seat_'+_seat+' div.agentpic').addClass("serving");
					} else if(_status == 'A') {
						$('#seat_'+_seat).addClass("idle");
						_txtActivity = _skill_name.length > 0 ? _skill_name + ' (' + _sObj.lap  + ')' : _sObj.lap;
						if(_sObj.lap.length > 0)
							$('#seat_'+_seat+' div.status').html('Idle: '+ _txtActivity);
						else
							$('#seat_'+_seat+' div.status').html('');
					} else if(_status == 'R') {
						$('#seat_'+_seat).addClass("ring");
						_txtActivity = _skill_name + ' (' + _sObj.lap  + ')';
						$('#seat_'+_seat+' div.status').html('Ringing '+_txtActivity);
					} else if(_status == 'D') {
						$('#seat_'+_seat).addClass("dial");
						_txtActivity = _skill_name + ' (' + _sObj.lap  + ')';
						$('#seat_'+_seat+' div.status').html('Dialing '+_txtActivity);
					} else if(_status == 'XI') {
						$('#seat_'+_seat).addClass("hold");
						_txtPause = (typeof available_aux_msgs[_sObj.aux]=='undefined') ? 'Busy' : available_aux_msgs[_sObj.aux];
						$('#seat_'+_seat+' div.status').html(_txtPause + ' ('+_sObj.lap+')');
					} else if(_status == 'XO') {
						<?php if ($is_expanded_db != 'Y'):?>
						if ($('#seat_'+_seat).hasClass('show')) staffout++;
						<?php else:?>
						staffout++;
						<?php endif;?>
						$('#seat_'+_seat).addClass("hold-out");
						_txtPause = (typeof available_aux_msgs[_sObj.aux]=='undefined') ? 'Busy' : available_aux_msgs[_sObj.aux];
						$('#seat_'+_seat+' div.status').html(_txtPause + ' ('+_sObj.lap+')');
					} else { //or status F

					}

//alert(prop);
					if (_sObj.aid.length > 0) {
						<?php if ($is_expanded_db != 'Y'):?>
						if ($('#seat_'+_seat).hasClass('show')) staffin++;
						<?php else:?>
						staffin++;
						<?php endif;?>
						if($('#seat_'+_seat+' div.agent div#a_'+_sObj.aid).html() == null) {
							$('#seat_'+_seat+' div.agent').attr('agent', _sObj.aid);
							var agent_name = (typeof available_agents[_sObj.aid]=='undefined') ? _sObj.aid : available_agents[_sObj.aid];
							if(agent_name.length == 0) agent_name = _sObj.aid;
							$('#seat_'+_seat+' div.agent').append('<div id="a_'+_sObj.aid+'">'+agent_name+'</div>');
						}
						//$('#seat_'+_seat+' div.ageventinfo').attr('agent', _sObj.aid); --remove
						//$('#seat_'+_seat+' div.ageventinfo').show(); -- remove
						$('#seat_'+_seat+' div.agentpic').attr('agent', _sObj.aid);
						

					}
				
				}
			}

			var dimvalue = '<span class="dim-value">0</span>';
			
			for (prop in json.q) {
				if (prop.length > 0) {
					var _qObj = json.q[prop];
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
			}

			/*
			if (typeof json.num_vm != 'undefined') {
				if (json.num_vm == '0') json.num_vm = dimvalue;
				$('#cntVM0').html(' ' + json.num_vm);
			}
			*/
			
			if (typeof json.ivr != 'undefined') {
				if (json.ivr.length > 0) {
					var ivr1 = json.ivr[0];

					if (ivr1.cnt == '0') ivr1.cnt = dimvalue;
					if (ivr1.at == '0' || ivr1.mt == null) ivr1.at = dimvalue;
					if (ivr1.mt == '0' || ivr1.mt == null) ivr1.mt = dimvalue;
					
					$('#cntI0').html(' ' + ivr1.cnt);
					$('#atI0').html(' ' + ivr1.at);
					$('#mtI0').html(' ' + ivr1.mt);
				}
			}

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
			$("#sum_staff").html(staffin + ':' + staffout);



		}});
		setTimeout(updateMsg, 1000);
	}

	updateMsg();

	var tstamp = new Date().getTime();
	var is_agentinfo_visible = false;

	$(".seat div.agent").hover(
		function (event) {

			var tPosX = event.pageX - 5;
			var tPosY = event.pageY + 20;
			//alert(tPosX + '.' + tPosY);
			//alert($(this).attr('agent'));
			$('#agent_queue_info').css({top: tPosY, left: tPosX}); 
			agent_queue_info_display = $(this).attr('agent');
			tstamp++;
			
			if (!is_agentinfo_visible) {
				is_agentinfo_visible = true;
				$('#agent_queue_info').html('<div class="loading">Loading....</div>');
				$('#agent_queue_info').css('display','block');

			$.ajax({
				type: 'POST',
				url: "get_agent_q_mem_info.php",
				data: "agent_id="+agent_queue_info_display+"&time="+tstamp,
				dataType: "json",
				success: function (jresponse) {
					//$('#div_id').html(response);
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



				}
			});
			
			}

		}, function () {
			$('#agent_queue_info').css('display','none');
			is_agentinfo_visible = false;
		}
	);

	/*
	$('div.agentpic').click(function(){
		var agent_id = $(this).attr('agent');
		var hasClass = $(this).hasClass('serving');
		if (hasClass) {
			$.post('cc_web_agi.php', { 'page':'dashboard', 'agent_id':dashboarduser, 'option':agent_id },
				function(data, status){
					if(status == 'success') {
						if(data.length > 0)
							if(data != 'Y')
								alert(data);
					} else {
						alert('Failed to commuinicate!!');
					}
				}
			);
		}
	});
	*/


    $.contextMenu({
        selector: '.agentpic', 
        callback: function(key, options) {
			var menu_aid = options.$trigger.attr('agent');
			menu_aid = key == 'W' ? menu_aid + 'W' : menu_aid;
			$.post('cc_web_agi.php', { 'page':'dashboard', 'agent_id':dashboarduser, 'option':menu_aid },
				function(data, status){
					if(status == 'success') {
						if(data.length > 0)
							if(data != 'Y')
								alert(data);
					} else {
						alert('Failed to commuinicate!!');
					}
				}
			);
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
    });

	//$trigger = $('.agentpic');
});

var max_calls_in_q = 6;
var agent_queue_info_display = null;
var queue_detail_info_display = null;
var ivr_detail_info_display = null;
var available_services = new Array();
var available_agents = new Array();
var available_aux_msgs = new Array();

var dashboarduser = '<?php echo UserAuth::getCurrentUser();?>';
<?php if (is_array($skills)):?>
<?php foreach ($skills as $skill):?>
available_services['<?php echo $skill->skill_id;?>'] = '<?php echo $skill->skill_name;?>';
<?php endforeach;?>
<?php endif;?>

<?php if (is_array($agents)):?>
<?php foreach ($agents as $agent):?>
available_agents['<?php echo $agent->agent_id;?>'] = '<?php echo $agent->nick;?>';
<?php endforeach;?>
<?php endif;?>

<?php if (is_array($aux_messages)):?>
<?php foreach ($aux_messages as $amsg):?>
<?php $msg = $amsg->aux_type == 'I' ? $amsg->message . ' [I]' : $amsg->message;?>
available_aux_msgs['<?php echo $amsg->aux_code;?>'] = '<?php echo $msg;?>';
<?php endforeach;?>
<?php endif;?>

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
	height: 47px;
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
	height: 48px;
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
<tr><td><div id="inq<?php echo $skill->skill_id;?>">&nbsp;</div></td><td class="qbox-col-left-border"><div id="srv<?php echo $skill->skill_id;?>">&nbsp;</div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo"><?php echo $skill->type == 'out' ? 'ATT' : 'AHT';?></div></td><td class="qbox-col-left-border"><div class="qinfo"><?php echo $skill->type == 'out' ? 'MTT' : 'MHT';?></div></td></tr>
<tr><td><div id="at<?php echo $skill->skill_id;?>">&nbsp;</div></td><td class="qbox-col-left-border"><div id="mt<?php echo $skill->skill_id;?>">&nbsp;</div></td></tr>
</table>
</div>
<?php endforeach;?>
<?php endif;?>

<div class="bxIvr">
<table class="infoQueue" bgcolor="#ffffff" cellspacing="0">
<tr><td colspan="2" class="qhead-td"><div class="qhead">IVR</div></td></tr>

<tr class="qinfo-tr"><td colspan="2"><div class="qinfo">IVR Count</div></td></tr>
<tr><td colspan="2"><div id="cntI0">&nbsp;</div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo">Avg Time</div></td><td class="qbox-col-left-border"><div class="qinfo">Max Time</div></td></tr>
<tr><td><div id="atI0">&nbsp;</div></td><td class="qbox-col-left-border"><div id="mtI0">&nbsp;</div></td></tr>
</table>
</div>

<div class="bxOutbound">
<table class="infoQueue" bgcolor="#ffffff" cellspacing="0">
<tr><td colspan="2" class="qhead-td"><div class="qhead">Outbound</div></td></tr>

<tr class="qinfo-tr"><td colspan="2"><div class="qinfo">Total Calls</div></td></tr>
<tr><td colspan="2"><div id="cntO">&nbsp;</div></td></tr>
<tr class="qinfo-tr"><td><div class="qinfo">Avg Time</div></td><td class="qbox-col-left-border"><div class="qinfo">Max Time</div></td></tr>
<tr><td><div id="atO">&nbsp;</div></td><td class="qbox-col-left-border"><div id="mtO">&nbsp;</div></td></tr>
</table>
</div>


</div>


        </td>

    </tr>


</table>

<div style="text-align:center; position: relative; height: 24px; line-height: 24px;">
<?php 
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
}
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