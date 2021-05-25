<?php

error_reporting(7);

include ('conf.php');
$dbManager = new DBManager($db);

$report_time = '';
if (isset($_REQUEST['day'])) {
	$report_time = strtotime($_REQUEST['day']);
}
if (!$report_time) {
	$report_time = strtotime("-1 day");
}

$model = new MReportUpdate($dbManager);
$report_controller = new ControllerReport($report_time, $model);

$report_controller->actionAgentcall();
$report_controller->actionSkillreport();
$report_controller->actionAgenttime();


class ControllerReport {

	var $report_time;
	var $report_day;
	var $report_year;
	var $cur_year;
	var $start_tstamp;
	var $end_tstamp;
	var $report_model;
	
	function __construct($report_time, $model) {
		$this->report_time = $report_time;
		$this->report_day = date("Y-m-d", $report_time);
		$this->cur_year = date("y");
		$this->report_year = date("y", $report_time);
		$this->start_tstamp = strtotime($this->report_day.' 00:00:00');
		$today = date("Y-m-d");
		if ($this->report_day == $today) {
			$this->end_tstamp = time();
		} else {
			$this->end_tstamp = strtotime($this->report_day.' 23:59:59');
		}
		//echo 'err='.$this->end_tstamp.',';
		$this->report_model = $model;
	}

	function actionAgentcall()
	{
		$report = 'AgentCallInbound';
		
		if (isset($_REQUEST['day'])) {
			$isReportExist = false;
		} else if ($this->cur_year != $this->report_year) {
			$isReportExist = $this->report_model->isTableExist($report, $this->report_year);
		} else {
			$isReportExist = $this->report_model->isReportExist($report, $this->start_tstamp, $this->end_tstamp);
		}
		
		if (!$isReportExist) {

			$this->report_model->removeReport($report, $this->start_tstamp, $this->end_tstamp);
			$agents = $this->report_model->getSessionAgents($this->start_tstamp, $this->end_tstamp);
			$logs_raw = $this->report_model->getAgentCallSourceLog($this->report_day, $this->report_day, '00:00', '23:59');
			$logs = array();
			if (is_array($logs_raw)) {
				foreach ($logs_raw as $log) {
					$key = $log->agent_id . '_' . $log->skill_id . '_' . substr($log->cdate, 5, 2) . '_' . substr($log->cdate, 8, 2) . '_' . $log->hour . '_' . sprintf("%02d", $log->minute);
					$logs[$key] = $log;
				}
			}
			$day = date("d", $this->start_tstamp);
			$month = date("m", $this->start_tstamp);

			foreach ($agents as $agentid) {

				$skills = $this->report_model->getAgentSkills($agentid);
				for ($tstamp=$this->start_tstamp; $tstamp<=$this->end_tstamp; $tstamp+=1800) {
					foreach ($skills as $skillid) {

						$min = date("i", $tstamp);
						$hour = date("H", $tstamp);
						$key = $agentid . '_' . $skillid . '_' . $month . '_' . $day . '_' . $hour . '_' . $min;
						$call = isset($logs[$key]) ? $logs[$key] : null;
						$this->report_model->addAgentCall($agentid, $skillid, $tstamp, $call);

					}
				}
			}
		}

		if ($this->cur_year != $this->report_year) {
			if (!$isReportExist) {
				$this->report_model->backupYearReport($report, $this->report_year);
			}
		}
	}


	function actionSkillreport()
	{
		$report = 'SkillReportInbound';

		if (isset($_REQUEST['day'])) {
			$isReportExist = false;
		} else if ($this->cur_year != $this->report_year) {
			$isReportExist = $this->report_model->isTableExist($report, $this->report_year);
		} else {
			$isReportExist = $this->report_model->isReportExist($report, $this->start_tstamp, $this->end_tstamp);
		}
		
		
		if (!$isReportExist) {

			$this->report_model->removeReport($report, $this->start_tstamp, $this->end_tstamp);
			$skills = $this->report_model->getInboundSkills();
			$logs_raw = $this->report_model->getSkillSourceLog($this->report_day, $this->report_day, '00:00', '23:59');
			$logs = array();
			if (is_array($logs_raw)) {
				foreach ($logs_raw as $log) {
					$key = $log->skill_id . '_' . substr($log->cdate, 5, 2) . '_' . substr($log->cdate, 8, 2) . '_' . $log->hour . '_' . sprintf("%02d", $log->minute);
					$logs[$key] = $log;
				}
			}
			$day = date("d", $this->start_tstamp);
			$month = date("m", $this->start_tstamp);

			foreach ($skills as $skillid) {

				for ($tstamp=$this->start_tstamp; $tstamp<=$this->end_tstamp; $tstamp+=1800) {

					$min = date("i", $tstamp);
					$hour = date("H", $tstamp);
					$key = $skillid . '_' . $month . '_' . $day . '_' . $hour . '_' . $min;
					$call = isset($logs[$key]) ? $logs[$key] : null;
					$this->report_model->addSkillReport($skillid, $tstamp, $call);
				}
			}
		}

		if ($this->cur_year != $this->report_year) {
			if (!$isReportExist) {
				$this->report_model->backupYearReport($report, $this->report_year);
			}
		}
	}

	function actionAgenttime()
	{
		$report = 'AgentTime';
		
		if (isset($_REQUEST['day'])) {
			$isReportExist = false;
		} else if ($this->cur_year != $this->report_year) {
			$isReportExist = $this->report_model->isTableExist($report, $this->report_year);
		} else {
			$isReportExist = $this->report_model->isReportExist($report, $this->start_tstamp, $this->end_tstamp);
		}
		
		
		if (!$isReportExist) {

			$this->report_model->removeReport($report, $this->start_tstamp, $this->end_tstamp);
			
			$agents = $this->report_model->getSessionAgents($this->start_tstamp, $this->end_tstamp);

			$_dateinfo = new stdClass;
			$_dateinfo->sdate = $_dateinfo->edate = date("Y-m-d", $this->start_tstamp);

			foreach ($agents as $agentid) {

				for ($tstamp=$this->start_tstamp; $tstamp<=$this->end_tstamp; $tstamp+=1800) {

					$_dateinfo->stime = date("H:i", $tstamp);
					$_dateinfo->etime = date("H:i", $tstamp+1799);
					$agentinfo = $this->report_model->getAgentSessionInfo($agentid, $_dateinfo);
					
					$this->report_model->addAgentTime($agentid, $tstamp, $agentinfo);
				}

			}
		}

		if ($this->cur_year != $this->report_year) {
			if (!$isReportExist) {
				$this->report_model->backupYearReport($report, $this->report_year);
			}
		}
	}

}



class MReportUpdate
{
	var $_conn = null;

	function __construct($dbmanager) {
		$this->_conn = $dbmanager;
	}

	function getDB()
	{
		return $this->_conn;
	}

	function getAgentSessionInfo($agent_id, $dateinfo)
	{
		$current_time = date("Y-m-d H:i:s");
		
		$report_start_time = empty($dateinfo->stime) ? $dateinfo->sdate . ' 00:00:00' : $dateinfo->sdate . ' ' . $dateinfo->stime . ':00';
		$report_end_time = $dateinfo->edate;
		if (!empty($report_end_time)) {
			$report_end_time = empty($dateinfo->etime) ? $report_end_time . ' 23:59:59' : $report_end_time . ' ' . $dateinfo->etime . ':59';
		}

		if (empty($report_start_time)) $report_start_time = $current_time;
		if (empty($report_end_time)) $report_end_time = substr($report_start_time, 0, 10) . " 23:59:59";//$current_time;
		if ($report_end_time>$current_time) $report_end_time = $current_time;

		$result = $this->getAgentSessionLog($agent_id, $report_start_time, $report_end_time);
		//if (is_array($result)) echo "$report_start_time, $report_end_time";
		//if ($report_start_time == '2012-03-19 14:00:00') echo '$report_end_time' . $report_end_time;
		return $this->calcAgentSessionInfo($agent_id, $result, $report_start_time, $report_end_time);
	}
	
	function getAgentSessionLog($agent_id, $stime='', $etime='', $order = 'tstamp')
	{
		$_year = substr($stime, 2, 2);
		$table = '';//date("y") == $_year ? '' : '_' . $_year;
		$table = 'agent_session_log' . $table;
		if (empty($etime)) {
			$etime = substr($stime, 0, 10) . " 23:59:59";
		}
		$ststamp = strtotime($stime);
		$etstamp = strtotime($etime);
		
		$sql = "SELECT tstamp, agent_id, type, value FROM $table WHERE tstamp BETWEEN $ststamp AND $etstamp";
		$sql .= " AND agent_id='$agent_id' ORDER BY $order";
//if ($stime == '2012-03-19 06:00:00') echo 'sql='.$sql;
		return $this->getDB()->query($sql);
	}

	function getAgentLastStatus($agent_id, $stime)
	{
		//$stime = '2012-03-19 06:00:00';
		$_year = substr($stime, 2, 2);
		$table = '';//date("y") == $_year ? '' : '_' . $_year;
		$table = 'agent_session_log' . $table;
		$ststamp = strtotime($stime);
		
		$sql = "SELECT tstamp, agent_id, type, value FROM $table WHERE tstamp < $ststamp";
		$sql .= " AND agent_id='$agent_id' ORDER BY tstamp DESC LIMIT 1";

		return $this->getDB()->query($sql);
	}

	function calcAgentSessionInfo($agent_id, $result, $stime='', $etime='')
	{
		$agent = null;
		$agent->staffed_time = 0;
		$agent->pause_time = 0;
		$agent->talk_time = 0;
		$agent->pause_count = 0;
		$agent->calls_count = 0;
		$agent->session_count = 0;
		$agent->session_login = 'Continue';
		$agent->session_logout = 'Continue';
		$session_start = "";
		$pause_start = "";
		$pause_type = "";


		$last_session_details = $this->getAgentLastStatus($agent_id, $stime);
		if (is_array($last_session_details)) {
			$session_details = $last_session_details[0];
			if ($session_details->type == 'I' || $session_details->type == 'X') {
				$session_start = $stime;
				$agent->session_count++;
				if ($session_details->type == 'X') {
					$pause_start = $stime;
					$pause_type = $session_details->value;
					if ($pause_type == '99') $agent->calls_count++;
					else $agent->pause_count++;
				}
			}
		}
		
		if (!is_array($result)) {
			//$agent->staffed_time = $this->diff_in_sec($stime, $etime);

			//if ($agent->staffed_time < 0) {
				//echo "$stime, $etime";
				//exit;
			//}
			//$agent->pause_time = 0;
			//$agent->pause_count = 0;
			if ($agent->pause_count > 0) {
				$agent->staffed_time = $this->diff_in_sec($stime, $etime);
				$agent->pause_time = $agent->staffed_time;
				$temp_pause_type = "p" . $pause_type;
				$agent->$temp_pause_type = $agent->staffed_time;
			} else if ($agent->calls_count > 0) {
				$agent->staffed_time = $this->diff_in_sec($stime, $etime);
				$agent->talk_time = $agent->staffed_time;
				//$temp_pause_type = "p" . $pause_type;
				//$agent->$temp_pause_type = $agent->staffed_time;
			}

			return $agent;
		}
		


		$last_event_occured = $stime;
		$last_event_type = '';
		
		$last_event = '';
		$last_time = '';
		$last_type = '';
		
		foreach($result as $session) {

			$last_event = $session->type;
			$last_time = date("Y-m-d H:i:s", $session->tstamp);
			$session->logdate = $last_time;
			$last_type = $session->value;


			//if ($agent_id=='1024') echo "<br />Ag: pt-$agent->pause_time($agent->pause_count), st-$agent->staffed_time; .... ty-$last_event, tm-$last_time";
			//if ($agent_id=='1024') echo "<br />-----------------------";

			if(empty($session_start)) {
				$agent->session_count++;
				if($session->type == 'I') {
					if ($agent->session_count == 1) $agent->session_login = $session->logdate;
					$session_start = $session->logdate;
				} else {
					$session_start = $last_event_occured;
				}
			}
			
			if($session->type=='I') {
				if (empty($session_start)) {
					$agent->session_count++;
					$session_start = $session->logdate;
				}
			} else if($session->type=='O') {
				if (!empty($pause_start)) {
					$temp_pause_time = $this->diff_in_sec($pause_start, $session->logdate);
					$temp_pause_type = "p" . $pause_type;
					if(isset($agent->$temp_pause_type)) {
						$agent->$temp_pause_type += $temp_pause_time;
					} else {
						$agent->$temp_pause_type = $temp_pause_time;
					}
					if ($pause_type == '99') $agent->talk_time += $temp_pause_time;
					else $agent->pause_time += $temp_pause_time;
					$pause_start = "";
					$pause_type = "";
				}
				$agent->staffed_time += $this->diff_in_sec($session_start, $session->logdate);
				//if ($agent_id=='1024') echo "session: $session_start - $session->logdate - Hour=" . $this->diff_in_sec($session_start, $session->logdate)/3600 . "<br>";
				$session_start = "";
			} else if($session->type=='X') {
				if(!empty($pause_start)) {
					$temp_pause_time = $this->diff_in_sec($pause_start, $session->logdate);
					$temp_pause_type = "p" . $pause_type;
					if(isset($agent->$temp_pause_type)) {
						$agent->$temp_pause_type += $temp_pause_time;
					} else {
						$agent->$temp_pause_type = $temp_pause_time;
					}
					//$agent->pause_time += $temp_pause_time;
					if ($pause_type == '99') $agent->talk_time += $temp_pause_time;
					else $agent->pause_time += $temp_pause_time;
				}
				if ($pause_type == '99') $agent->calls_count++;
				else $agent->pause_count++;
				$pause_start = $session->logdate;
				$pause_type = $session->value;
			} else if($session->type=='R') {
				if ($last_event_type != 'R') {
					if(empty($pause_start)) $pause_start = $last_event_occured;

					$temp_pause_time = $this->diff_in_sec($pause_start, $session->logdate);
					$temp_pause_type = "p" . $pause_type;
					if(isset($agent->$temp_pause_type)) {
						$agent->$temp_pause_type += $temp_pause_time;
					} else {
						$agent->$temp_pause_type = $temp_pause_time;
					}
					//$agent->pause_time += $temp_pause_time;
					if ($pause_type == '99') $agent->talk_time += $temp_pause_time;
					else $agent->pause_time += $temp_pause_time;

					$pause_start = "";
					$pause_type = "";
				}
			}
			
			$last_event_occured = $session->logdate;
			$last_event_type = $session->type;
		}
		
		//echo '$last_type='.$last_type;
		//echo '$temp_pause_time='.$temp_pause_time;
		
		if(!empty($session_start)) {
			$agent->staffed_time += $this->diff_in_sec($session_start, $etime);
			//if ($agent_id=='1024') echo "session: $session_start - $etime - Hour=".$this->diff_in_sec($session_start, $etime)/3600 ."<br>";
			if($last_event=='X') {
				$temp_pause_time = $this->diff_in_sec($pause_start, $etime);
				$temp_pause_type = "p" . $last_type;

				if(isset($agent->$temp_pause_type)) {
					$agent->$temp_pause_type += $temp_pause_time;
				} else {
					$agent->$temp_pause_type = $temp_pause_time;
				}
				//$agent->pause_time += $temp_pause_time;
				if ($last_type == '99') $agent->talk_time += $temp_pause_time;
				else $agent->pause_time += $temp_pause_time;

			}
		}
		
		if ($last_event == 'O') {
			$agent->session_logout = $last_time;
		}
		/*if(!empty($id))	return $result[0];*/
		//$agent->pause_time = 202;
		//$agent->p11 = 20;
		//$agent->p13 = 50;
		return $agent;
	}

	function diff_in_sec($stime, $etime)
	{
		return strtotime($etime) - strtotime($stime) + 1;
	}
	
	function getSkillSourceLog($sdate='', $edate='', $stime='', $etime='')
	{

		$date_attributes = DateHelper::get_date_attributes('s.tstamp', $sdate, $edate, $stime, $etime);
		if (empty($date_attributes->yy)) return null;
		//$_year = $date_attributes->yy;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
	//echo $stime . $etime . $date_attributes->condition;
		$table = 'skill_log' . $year;
		$agent_table = 'agent_inbound_log' . $year;

		$where = $date_attributes->condition;
		$where .= " GROUP BY s.skill_id, ";
		if (!empty($sdate) && !empty($edate) && $sdate!=$edate) $where .= "cdate, ";
		$where .= "hour, minute ORDER BY skill_id, hour, minute";
		
		$sql = "SELECT ".
			"s.skill_id, ".
			"FROM_UNIXTIME(s.tstamp, '%Y-%m-%d') AS cdate, ".
			"FROM_UNIXTIME(s.tstamp, '%H') AS hour, ".
			"FLOOR(FROM_UNIXTIME(s.tstamp, '%i')/30)*30 AS minute, ".
			"COUNT(s.tstamp) AS num_calls, ".
			"SUM(IF(status='S', 1, 0)) AS num_ans, ".
			"SUM(IF(hold_in_q<=10 AND status='S', 1, 0)) AS ans_within_10s, ".
			"SUM(IF(hold_in_q<=20 AND status='S', 1, 0)) AS ans_within_20s, ".
			"SUM(IF(status='S', hold_in_q, 0)) AS ring_sec, ".
			"SUM(s.service_time) AS talktime, ".
			"SUM(s.acw_time) AS acw_time, ".
			"SUM(IF(status='S', s.acw_time+hold_in_q, 0)) AS extra_handle_time, ".
			"SUM(IF(status='A', 1, 0)) AS num_abdns, ".
			"SUM(IF(status='A', hold_in_q, 0)) AS abdns_time, ".
			"SUM(IF(status='S', hold_count, 0)) AS hold_count, ".
			"SUM(IF(status='S', hold_time, 0)) AS hold_time, ".
			"SUM(IF(flow_type='I', 1, 0)) AS inflowcalls, ".
			"SUM(IF(flow_type='O', 1, 0)) AS outflowcalls, ".
			"SUM(IF(extn_type!='', 1, 0)) AS extncalls, ".
			"SUM(IF(extn_type!='', a.service_time, 0)) AS extntime, ".
			"SUM(conference_count) AS conference, ".
			"SUM(IF(is_transferred='Y', 1, 0)) AS transfer, ".
			"SUM(IF(status='S', aux_out_calls, 0)) AS aux_out_calls, ".
			"SUM(IF(status='S', aux_out_time, 0)) AS aux_out_time, ".
			"SUM(IF(status='A' AND hold_in_q>5, 1, 0)) AS num_abdns_after_5s";
			//"SUM(IF(event='ED', duration, 0)) AS extn_time, ".
			//"SUM(IF(event='ED', 1, 0)) AS num_extn");
		$sql .= " FROM " . $table . " AS s LEFT JOIN $agent_table AS a ON a.callid=s.callid AND a.is_answer='Y' WHERE " . $where;
		//echo $sql;
		//echo date("y-m-d H:i:s", 1332028800);
		//echo date("y-m-d H:i:s", 1332097259);

		return $this->getDB()->query($sql);
		//$result = $this->db_manager->select($info);

		//return $result;
	}

	function getAgentCallSourceLog($sdate='', $edate='', $stime='', $etime='')
	{

		$date_attributes = DateHelper::get_date_attributes('a.tstamp', $sdate, $edate, $stime, $etime);
		if (empty($date_attributes->yy)) return null;
		//$_year = $date_attributes->yy;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
	//echo $stime . $etime . $date_attributes->condition;
		//$skill_table = 'skill_log' . $year;
		$agent_table = 'agent_inbound_log' . $year;

		$where = $date_attributes->condition;
		$where .= " GROUP BY a.agent_id, a.skill_id, ";
		if (!empty($sdate) && !empty($edate) && $sdate!=$edate) $where .= "cdate, ";
		$where .= "hour, minute ORDER BY agent_id, skill_id, hour, minute";
		
		$sql = "SELECT ".
			"a.agent_id, ".
			"FROM_UNIXTIME(a.tstamp, '%Y-%m-%d') AS cdate, ".
			"FROM_UNIXTIME(a.tstamp, '%H') AS hour, ".
			"FLOOR(FROM_UNIXTIME(a.tstamp, '%i')/30)*30 AS minute, ".
			"a.skill_id AS skill_id, ".
			"COUNT(a.tstamp) AS num_calls, ".
			"SUM(a.ring_time) AS ring_time, ".
			"SUM(IF(is_answer='Y',1, 0)) AS num_ans, ".
			"SUM(IF(is_answer='Y', a.service_time+a.ring_time+a.hold_time, 0)) AS handling_time, ".
			"SUM(a.service_time) AS service_time, ".
			"SUM(a.hold_time) AS hold_time, ".
			"SUM(IF(is_answer='Y', aux_out_calls, 0)) AS aux_out_calls, ".
			"SUM(IF(is_answer='Y', aux_out_time, 0)) AS aux_out_time, ".
			"SUM(a.acw_time) AS acw_time, ".
			"SUM(IF(extn_type!='', 1, 0)) AS extncalls, ".
			"SUM(IF(extn_type!='', a.service_time, 0)) AS extntime, ".
			"SUM(conference_count) AS conference, ".
			"SUM(IF(is_transferred='Y', 1, 0)) AS transfer";
			//"SUM(IF(event='ED', duration, 0)) AS extn_time, ".
			//"SUM(IF(event='ED', 1, 0)) AS num_extn");
		$sql .= " FROM " . $agent_table . " AS a WHERE " . $where;
		//echo $sql;
		//echo date("y-m-d H:i:s", 1332028800);
		//echo date("y-m-d H:i:s", 1332097259);

		return $this->getDB()->query($sql);
		//$result = $this->db_manager->select($info);

		//return $result;
	}

	function addAgentCall($agentid, $skillid, $tstamp, $call)
	{
		if (empty($call)) $call = $this->getEmptyAgentCall();
		
		$sql = "INSERT INTO report_agent_call_inbound SET agent_id='$agentid', tstamp='$tstamp', skill_id='$skillid', ring_calls='$call->num_calls', ".
			"ring_time='$call->ring_time', acd_calls='$call->num_ans', acd_time='$call->service_time', acd_hold_time='$call->hold_time', ".
			"acd_aux_out_calls='$call->aux_out_calls', acd_aux_out_time='$call->aux_out_time', acw_time='$call->acw_time', extn_out_calls='0', ".
			"extn_out_time='0', extn_in_calls='$call->extncalls', extn_in_time='$call->extntime', conference='$call->conference', transferred='$call->transfer'";
		return $this->getDB()->query($sql);
	}
	
	function addSkillReport($skillid, $tstamp, $call)
	{
		if (empty($call)) $call = $this->getEmptySkillReport();
		
		$sql = "INSERT INTO report_skill_inbound SET skill_id='$skillid', tstamp='$tstamp', calls_offered='$call->num_calls', ".
			"calls_answered='$call->num_ans', calls_answered_10='$call->ans_within_10s', calls_answered_20='$call->ans_within_20s', ".
			"avg_answer_speed='$call->num_ans', acd_time='$call->talktime', acd_hold_time='$call->hold_time', ".
			"acd_aux_out_calls='$call->aux_out_calls', acd_aux_out_time='$call->aux_out_time', acw_time='$call->acw_time', ".
			"abn_calls='$call->num_abdns', abn_calls_5='$call->num_abdns_after_5s', abn_time='$call->abdns_time', ".
			"flow_in_calls='$call->inflowcalls', flow_out_calls='$call->outflowcalls', extn_out_calls='0', extn_out_time='0', ".
			"extn_in_calls='$call->extncalls', extn_in_time='$call->extntime', conference='$call->conference', transferred='$call->transfer'";
		return $this->getDB()->query($sql);
	}
	
	function addAgentTime($agentid, $tstamp, $agentinfo)
	{
		if (empty($agentinfo)) return false;
		$avail_time = $agentinfo->staffed_time - $agentinfo->pause_time - $agentinfo->talk_time;
		$sql = "INSERT INTO report_agent_time SET agent_id='$agentid', tstamp='$tstamp', staff_time='$agentinfo->staffed_time', ".
			"aux_time='$agentinfo->pause_time', available_time='$avail_time'";
		for ($i=11; $i<=20; $i++) {
			$key = 'p' . $i;
			$ax_value = isset($agentinfo->$key) ? $agentinfo->$key : 0;
			$sql .= ', aux_' . $i . '=' . "'$ax_value'";
		}
		return $this->getDB()->query($sql);
	}

	function getEmptySkillReport()
	{
		$call = new stdClass();
		$call->num_calls = 0;
		$call->num_ans = 0;
		$call->ans_within_10s = 0;
		$call->ans_within_20s = 0;
		$call->talktime = 0;
		$call->hold_time = 0;
		$call->aux_out_calls = 0;
		$call->aux_out_time = 0;
		$call->acw_time = 0;
		$call->num_abdns = 0;
		$call->num_abdns_after_5s = 0;
		$call->abdns_time = 0;
		$call->inflowcalls = 0;
		$call->outflowcalls = 0;
		$call->extncalls = 0;
		$call->extntime = 0;
		$call->conference = 0;
		$call->transfer = 0;

		return $call;
	}
	
	
	function getEmptyAgentCall()
	{
		$call = new stdClass();
		$call->num_calls = 0;
		$call->ring_time = 0;
		$call->num_ans = 0;
		$call->service_time = 0;
		$call->hold_time = 0;
		$call->aux_out_calls = 0;
		$call->aux_out_time = 0;
		$call->acw_time = 0;
		$call->extncalls = 0;
		$call->extntime = 0;
		$call->conference = 0;
		$call->transfer = 0;

		return $call;
	}
	
	function getAgentSkills($agent_id)
	{
		$skills = array();
		$sql = "SELECT a.skill_id FROM agent_skill AS a, skill AS s WHERE agent_id='$agent_id' AND ".
			"s.skill_id=a.skill_id AND s.active='Y' ORDER BY skill_id";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $row) {
				$skills[] = $row->skill_id;
			}
		}
		return $skills;
	}
	
	function getInboundSkills()
	{
		$skills = array();
		$sql = "SELECT skill_id FROM skill WHERE active='Y' ORDER BY skill_id";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $row) {
				$skills[] = $row->skill_id;
			}
		}
		return $skills;
	}
	
	function getSessionAgents($start_tstamp, $end_tstamp)
	{
		$agents = array();
		$sql = "SELECT DISTINCT s.agent_id FROM agent_session_log AS s, agents AS a WHERE tstamp BETWEEN ".
			"$start_tstamp AND $end_tstamp AND a.agent_id=s.agent_id AND usertype='A' ORDER BY agent_id";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $row) {
				$agents[] = $row->agent_id;
			}
		}
		return $agents;
	}

	function isReportExist($report='', $start_tstamp, $end_tstamp)
	{
		$table = $this->getReportTableName($report);
		
		if (empty($table)) return false;
		
		$start1 = $end_tstamp - 1800;
		if ($start_tstamp > $start1) $start1 = $start_tstamp;
		$sql = "SELECT tstamp FROM $table WHERE tstamp BETWEEN $start1 AND $end_tstamp LIMIT 1";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return true;
		}
		return false;
	}
	
	function removeReport($report='', $start_tstamp, $end_tstamp)
	{
		$table = $this->getReportTableName($report);
		if (empty($table)) return false;
		$sql = "DELETE FROM $table WHERE tstamp BETWEEN $start_tstamp AND $end_tstamp";
		return $this->getDB()->query($sql);
	}

	function isTableExist($report, $yy)
	{
		$table = $this->getReportTableName($report);
		if (empty($table)) return false;
		
		if (!empty($yy)) $table = $table . '_' . $yy;
		$database_name = $this->getDB()->db_s;
		
		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '$database_name' AND table_name = '$table'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return true;
		}
		return false;
	}
	
	function backupYearReport($report, $yy)
	{
		$table = $this->getReportTableName($report);
		if (empty($table)) return false;
		
		$backup_table = $table . '_' . $yy;
		
		$sql = "RENAME TABLE $table TO $backup_table";
		
		if ($this->getDB()->query($sql)) {
			$this->createReportTable($report);
			return true;
		}
		return false;
	}
	
	function getReportTableName($report)
	{
		$table = '';
		if ($report == 'AgentCallInbound') {
			$table = 'report_agent_call_inbound';
		} else if ($report == 'SkillReportInbound') {
			$table = 'report_skill_inbound';
		} else if ($report == 'AgentTime') {
			$table = 'report_agent_time';
		}
		return $table;
	}
	
	function createReportTable($report)
	{
		$table = '';
		if ($report == 'AgentCallInbound') {
			$table = "CREATE TABLE `report_agent_call_inbound` (".
			  "`agent_id` char(4) NOT NULL DEFAULT '',".
			  "`tstamp` int(4) NOT NULL DEFAULT '0',".
			  "`skill_id` char(2) NOT NULL DEFAULT '',".
			  "`ring_calls` decimal(2,0) unsigned NOT NULL DEFAULT '0',".
			  "`ring_time` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`acd_calls` decimal(2,0) unsigned NOT NULL DEFAULT '0',".
			  "`acd_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`acd_hold_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`acd_aux_out_calls` decimal(2,0) unsigned NOT NULL DEFAULT '0',".
			  "`acd_aux_out_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`acw_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`extn_out_calls` decimal(2,0) unsigned NOT NULL DEFAULT '0',".
			  "`extn_out_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`extn_in_calls` decimal(2,0) unsigned NOT NULL DEFAULT '0',".
			  "`extn_in_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`conference` decimal(2,0) unsigned NOT NULL DEFAULT '0',".
			  "`transferred` decimal(2,0) unsigned NOT NULL DEFAULT '0',".
			  "KEY `tstamp` (`tstamp`)".
			")";
		} else if ($report == 'SkillReportInbound') {
			$table = "CREATE TABLE `report_skill_inbound` (".
			  "`skill_id` char(2) NOT NULL DEFAULT '',".
			  "`tstamp` int(4) NOT NULL DEFAULT '0',".
			  "`calls_offered` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`calls_answered` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`calls_answered_10` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`calls_answered_20` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`avg_answer_speed` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`acd_time` decimal(6,0) unsigned NOT NULL DEFAULT '0',".
			  "`acd_hold_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`acd_aux_out_calls` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`acd_aux_out_time` decimal(5,0) unsigned NOT NULL DEFAULT '0',".
			  "`acw_time` decimal(5,0) unsigned NOT NULL DEFAULT '0',".
			  "`abn_calls` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`abn_calls_5` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`abn_time` decimal(6,0) unsigned NOT NULL DEFAULT '0',".
			  "`flow_in_calls` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`flow_out_calls` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`extn_out_calls` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`extn_out_time` decimal(5,0) unsigned NOT NULL DEFAULT '0',".
			  "`extn_in_calls` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "`extn_in_time` decimal(5,0) unsigned NOT NULL DEFAULT '0',".
			  "`conference` decimal(2,0) unsigned NOT NULL DEFAULT '0',".
			  "`transferred` decimal(3,0) unsigned NOT NULL DEFAULT '0',".
			  "KEY `tstamp` (`tstamp`)".
			")";
		} else if ($report == 'AgentTime') {
			$table = "CREATE TABLE `report_agent_time` (".
			  "`agent_id` char(4) NOT NULL DEFAULT '',".
			  "`tstamp` int(4) NOT NULL DEFAULT '0',".
			  "`staff_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`available_time` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_11` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_12` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_13` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_14` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_15` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_16` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_17` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_18` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_19` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "`aux_20` decimal(4,0) unsigned NOT NULL DEFAULT '0',".
			  "KEY `tstamp` (`tstamp`)".
			")";
		}

		if (!empty($table)) {
			$this->getDB()->query($table);
		}
	}

}


class DBManager
{
	var $db_host_s;
	var $db_user_s;
	var $db_pass_s;
	var $db_s;
	var $db_host_u;
	var $db_user_u;
	var $db_pass_u;
	var $db_u;

	var $sel_conn;
	var $upd_conn;
	var $num_rows;
	var $affected_rows;
	var $insert_id;

	var $isSmaeBothConn;

	function __construct($db)
	{
		//echo "<br><br>CONSTRUCTOR<br><br>";
		$this->db_host_s = $db->select_host;
		$this->db_user_s = $db->select_user;
		$this->db_pass_s = $db->select_pass;
		$this->db_s = $db->select_db;
		
		$this->db_host_u = $db->update_host;
		$this->db_user_u = $db->update_user;
		$this->db_pass_u = $db->update_pass;
		$this->db_u = $db->update_db;
		
		if (empty($this->db_pass_u) || $this->db_user_s === $this->db_user_u) {
			$this->isSmaeBothConn = 1;
		} else {
			$this->isSmaeBothConn = 0;
		}
		
		$this->sel_conn = null;
		$this->upd_conn = null;
				
		$this->num_rows = 0;
		$this->affected_rows = 0;
		$this->insert_id = null;
	}
	
	function __destruct()
	{
		//echo "<br><br>DESTRUCTOR<br><br>";
		if($this->sel_conn != null)
			mysql_close($this->sel_conn);
		if($this->upd_conn != null)
			mysql_close($this->upd_conn);
	}
	
	function getSelectConn($isTestConnection=false)
	{
		if($this->sel_conn != null)	return $this->sel_conn;
		
		if($isTestConnection) {
			$this->sel_conn = @mysql_connect($this->db_host_s, $this->db_user_s, $this->db_pass_s);
			if (!$this->sel_conn) {
				$this->sel_conn =  null;
			} else {
				mysql_select_db($this->db_s,$this->sel_conn) or $this->sel_conn =  null;
			}
		} else {
			$this->sel_conn = mysql_connect($this->db_host_s, $this->db_user_s, $this->db_pass_s);
			if (!$this->sel_conn) die ("Could not connect MySQL $this->db_host_s");
			mysql_select_db($this->db_s,$this->sel_conn) or die ("Could not open database");
		}

		return $this->sel_conn;
	}
	
	function getUpdateConn()
	{
		if($this->isSmaeBothConn == 1)
		{
			if($this->sel_conn != null)	return $this->sel_conn;
			
			$connection = $this->getSelectConn();
			
			return $connection;
		}

		if($this->upd_conn != null)	return $this->upd_conn;
		
		$this->upd_conn = mysql_connect($this->db_host_u, $this->db_user_u, $this->db_pass_u);
		if (!$this->upd_conn) die ("Could not connect");
		mysql_select_db($this->db_u,$this->upd_conn) or die ("Could not open database");

		return $this->upd_conn;
	}
	
	function getNumRows()
	{
		return $this->num_rows;
	}

	function getInsertId()
	{
		return $this->insert_id;
	}
	
	function getAffectedRows()
	{
		return $this->affected_rows;
	}

	function dumpResult($sql)
	{
		$is_success = mysql_query($sql, $this->getSelectConn());
		if (!$is_success) {
			$err = mysql_error($this->getSelectConn());
			echo $err . '<br />';
		}
		return $is_success;
	}

	function query($stmt, $debug = false)
	{
		$sql = explode(' ', $stmt, 2);
		$query_type = strtolower($sql[0]);
		//echo($stmt);
		if($query_type == 'select' || $query_type == 'desc') {

			//if($debug)
				//$this->echo_debug_msg($stmt);
	
			$result = mysql_query($stmt, $this->getSelectConn());
			$err = mysql_error($this->getSelectConn());
	
			//if ($debug)
				//$this->echo_debug_msg($err);
	
			if (!empty($err) || mysql_num_rows($result) < 1)
				return null;
	
			$this->num_rows = mysql_num_rows($result);
			//echo 'asd'. $this->num_rows;
			$data = array();
	
			while($row = mysql_fetch_object($result))
			{
				$data[] = $row;
			}
	
			mysql_free_result($result);
			
			return $data;

		} else {

			$result = mysql_query($stmt, $this->getUpdateConn());
			$err = mysql_error($this->getUpdateConn());
			/*
			if ($debug)
			{
				$this->echo_debug_msg("$stmt");
				$this->echo_debug_msg("Error: $err");
				$this->echo_debug_msg("Affected Rows: " . mysql_affected_rows($this->getUpdateConn()));
			}
			*/
			if (!empty($err) || mysql_affected_rows($this->getUpdateConn()) < 1)
			{
				return false;
			}
			else
			{
				$this->affected_rows = mysql_affected_rows($this->getUpdateConn());
				return true;
			}
		}
	}
	
}


class DateHelper
{
	public static function get_input_time_details($allow_empty_date = false, $_sdate='', $_edate='', $_stime='', $_etime='')
	{
		//$sdate = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
		//$stime = isset($_REQUEST['stime']) ? trim($_REQUEST['stime']) : '';
		//$edate = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
		//$etime = isset($_REQUEST['etime']) ? trim($_REQUEST['etime']) : '';
		$err = '';

		//if (isset($_POST['sdate'])) {

			$sdate = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
			$stime = isset($_REQUEST['stime']) ? trim($_REQUEST['stime']) : '';
			$edate = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
			$etime = isset($_REQUEST['etime']) ? trim($_REQUEST['etime']) : '';

			$isValidSDate = false;
			$isValidEDate = false;
			$isValidSTime = false;
			$isValidETime = false;

			if (!empty($sdate)) {
				$sday = substr($sdate, 8, 2);
				$smonth = substr($sdate, 5, 2);
				$syear = substr($sdate, 0, 4);
				if (checkdate($smonth, $sday, $syear)) {
					$isValidSDate = true;
					if (!empty($stime)) {
						$shr = substr($stime, 0, 2);
						$smin = substr($stime, 3, 2);
						if (DateHelper::checktime($shr, $smin)) {
							$isValidSTime = true;
						}
					}
				}
			}
			
			if (!empty($edate)) {
				$eday = substr($edate, 8, 2);
				$emonth = substr($edate, 5, 2);
				$eyear = substr($edate, 0, 4);
				if (checkdate($emonth, $eday, $eyear)) {
					$isValidEDate = true;
					if (!empty($etime)) {
						$ehr = substr($etime, 0, 2);
						$emin = substr($etime, 3, 2);
						if (DateHelper::checktime($ehr, $emin)) {
							$isValidETime = true;
						}
					}
				}
			}
			
			if (!$isValidSDate) $sdate = '';
			if (!$isValidEDate) $edate = '';
			if (!$isValidSTime || !$isValidETime) {
				$stime = '';
				$etime = '';
			}
		//}

		if (empty($sdate) && empty($edate)) {
			if (!empty($_sdate)) {
				$sdate = $_sdate;
			}
			if (!empty($_edate)) {
				$edate = $_edate;
			}
			if (!empty($_stime)) {
				$stime = $_stime;
			}
			if (!empty($_etime)) {
				$etime = $_etime;
			}
		}
		if (empty($sdate) && !$allow_empty_date) {
			$sdate = date("Y-m-d");
		}
		
		//$sdate = empty($stime) ? '' : date('Y-m-d', $stime);
		//$edate = empty($etime) ? '' : date('Y-m-d', $etime);
		
		$sdate_time = empty($sdate) ? '' : strtotime($sdate);
		$edate_time = empty($edate) ? '' : strtotime($edate);
		if (!empty($sdate_time) && !empty($edate_time)) {
			$date_diff = round( abs($edate_time-$sdate_time) / 86400, 0 );
		} else {
			$date_diff = 0;
		}

		if (!empty($sdate_time) && !empty($edate_time) && $etime-$stime < 0) {
			$err = 'Provide positive date range !!';
		} else if ($date_diff > 50) {
			$err = 'Date range is too large !!';
		}

		$ststamp = 0;
		$etstamp = 0;
		if (!empty($sdate)) {
			$_sdate_for_tstamp = $sdate;
			if (!empty($stime)) {
				$_sdate_for_tstamp .= "$stime:00";
			} else {
				$_sdate_for_tstamp .= "00:00:00";
			}
			$ststamp = strtotime($_sdate_for_tstamp);
		}

		if (!empty($edate)) {
			$_edate_for_tstamp = $edate;
			if (!empty($etime)) {
				$_edate_for_tstamp .= "$etime:59";
			} else {
				$_edate_for_tstamp .= "23:59:59";
			}
			$etstamp = strtotime($_edate_for_tstamp);
		}

		$dateinfo = null;
		$dateinfo->stime = $stime;
		$dateinfo->etime = $etime;
		$dateinfo->sdate = $sdate;
		$dateinfo->edate = $edate;
		$dateinfo->ststamp = $ststamp;
		$dateinfo->etstamp = $etstamp;
		
		$dateinfo->errMsg = $err;
		//var_dump($dateinfo);
		return $dateinfo;
	}
	

	public static function get_date_title($dateinfo=null)
	{
		$title = '';
		if (empty($dateinfo)) return $title;
		$sdate = $dateinfo->sdate;
		$edate = $dateinfo->edate;
		$month_name = array('01'=>'JAN','02'=>'FEB','03'=>'MAR','04'=>'APR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AUG','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DEC');
		if (!empty($dateinfo->sdate) && !empty($dateinfo->edate)) {
			$sm = isset($month_name[substr($sdate, 5, 2)]) ? $month_name[substr($sdate, 5, 2)] : '';
			$em = isset($month_name[substr($edate, 5, 2)]) ? $month_name[substr($edate, 5, 2)] : '';
			$title = $sm.' '.substr($sdate, 8, 2).', ' .substr($sdate, 0, 4).' - '.$em.' '.substr($edate, 8, 2).', ' .substr($edate, 0, 4);
		} else if (!empty($dateinfo->sdate)) {
			$sm = isset($month_name[substr($sdate, 5, 2)]) ? $month_name[substr($sdate, 5, 2)] : '';
			$title = $sm.' '.substr($sdate, 8, 2).', ' .substr($sdate, 0, 4);
		} else if (!empty($dateinfo->edate)) {
			$em = isset($month_name[substr($edate, 5, 2)]) ? $month_name[substr($edate, 5, 2)] : '';
			$title = $em.' '.substr($edate, 8, 2).', ' .substr($edate, 0, 4);
		}
		
		return $title;
	}

	public static function get_date_log($dateinfo=null)
	{
		$cond = '';
		if (empty($dateinfo)) return $cond;
		$sdate = $dateinfo->sdate;
		$edate = $dateinfo->edate;
		$stime = $dateinfo->stime;
		$etime = $dateinfo->etime;
		
		if (!empty($sdate) && !empty($edate)) {
			if (empty($stime) || empty($etime)) {
				$cond = "Date between $sdate and $edate";
			} else {
				$_stime = strtotime("$sdate $stime:00");
				$_etime = strtotime("$edate $etime:59");
				$cond = "Time between $_stime and $_etime";
			}
		} else if (!empty($sdate)) {
			$cond = "Date=$sdate";
		} else {
			$cond = "Date=$edate";
		}

		return $cond;
	}

	public static function get_date_attributes($field='', $sdate='', $edate='', $stime='', $etime='')
	{
		$attr = new stdClass();
		$attr->condition = '';
		$attr->yy = '';
		$cond = '';
		$yy = '';
		if (empty($field)) return $attr;
		if (empty($sdate) && empty($edate)) return $attr;
		
		//$date_field_name = $is_field_type_time ? "FROM_UNIXTIME($field,'%Y-%m-%d')" : "$field";
		$date_field_name = "FROM_UNIXTIME($field,'%Y-%m-%d')";
		
		if (!empty($sdate) && !empty($edate)) {
			if (empty($stime) || empty($etime)) {
				//$cond = "$date_field_name BETWEEN '$sdate' AND '$edate'";
				$_stime = strtotime("$sdate 00:00:00");
				$_etime = strtotime("$edate 23:59:59");
				$cond = "$field BETWEEN '$_stime' AND '$_etime'";

			} else {
				$_stime = strtotime("$sdate $stime:00");
				$_etime = strtotime("$edate $etime:59");
				$cond = "$field BETWEEN '$_stime' AND '$_etime'";
			}
			$yy = substr($sdate, 2, 2); 
		} else if (!empty($sdate)) {
			$_stime = strtotime("$sdate 00:00:00");
			$_etime = strtotime("$sdate 23:59:59");
			$cond = "$field BETWEEN '$_stime' AND '$_etime'";
			//$cond = "'$sdate'=$date_field_name";
			$yy = substr($sdate, 2, 2);
		} else {
			$_stime = strtotime("$edate 00:00:00");
			$_etime = strtotime("$edate 23:59:59");
			$cond = "$field BETWEEN '$_stime' AND '$_etime'";
//			$cond = "'$edate'=$date_field_name";
			$yy = substr($edate, 2, 2);
		}
		$attr->condition = $cond;
		$attr->yy = $yy;
		return $attr;
	}

	public static function checktime($hour, $minute)
	{
		if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60) {
			return true;
		}
		return false;
	}
	
	public static function get_formatted_time($string=0, $format='h:m:s')
	{
		if (empty($string)) $string = 0;
		$h = 0;
		$m = 0;
		$s = $string;
	
		$is_minute = strpos($format, 'm');
		$is_hour = strpos($format, 'h');
		$is_minute = $is_minute === false ? false : true;
		$is_hour = $is_hour === false ? false : true;
	
		if ($is_minute || $is_hour) {
			$m = (int)($s/60);
			$s = $s%60;
		}
		if ($is_hour) {
			$h = (int)($m/60);
			$m = $m%60;
		}
	
		$h = sprintf("%02d", $h);
		$m = sprintf("%02d", $m);
		$s = sprintf("%02d", $s);

		$return = $format;
		$return = str_replace("s", $s, $return);
		$return = str_replace("m", $m, $return);
		$return = str_replace("h", $h, $return);

		return $return;
	}
}

