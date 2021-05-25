<?php

class MReportUpdate extends Model
{
	function __construct() {
		parent::__construct();
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
		
		$sql = "SELECT tstamp FROM $table WHERE tstamp BETWEEN $start_tstamp AND $end_tstamp LIMIT 1";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return true;
		}
		return false;
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
			  "`skill_id` char(1) NOT NULL DEFAULT '',".
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
			  "`skill_id` char(1) NOT NULL DEFAULT '',".
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

?>