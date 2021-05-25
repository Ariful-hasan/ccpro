<?php

class MConference extends Model
{
	function __construct() {
		parent::__construct();
	}
	
	function numConferences()
	{
		$sql = "SELECT COUNT(conf_id) AS numrows FROM conf_bridge";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}

	function getConferences()
	{
		$sql = "SELECT * FROM conf_bridge ORDER BY conf_id";
		return $this->getDB()->query($sql);
	}
	
	function getAgentNames($usertype='', $status='', $offset=0, $limit=0, $format='sql')
	{
		$cond = '';
		$sql = "SELECT agent_id, nick FROM agents ";
		if (!empty($usertype)) $cond .= "usertype='$usertype'";
		if (!empty($status)) $cond = $this->getAndCondition($cond, "active='$status'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY agent_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		
		$result = $this->getDB()->query($sql);
		if ($format == 'array') {
			$names = array();
			if (is_array($result)) {
				foreach ($result as $row) {
					$names[$row->agent_id] = $row->nick;
				}
			}
			return $names;
		}
		return $result;
	}

	function getConferenceById($confid)
	{
		$sql = "SELECT * FROM conf_bridge WHERE conf_id='$confid'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getSupervisedAgents($agentid)
	{
		$sql = "SELECT DISTINCT s.agent_id, a.nick FROM agent_skill s LEFT JOIN agents a ".
			"ON a.agent_id=s.agent_id WHERE skill_id IN (SELECT skill_id FROM agent_skill ".
			"WHERE agent_id='$agentid')";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getAssignedSkills($agentid)
	{
		$sql = "SELECT skill_id FROM agent_skill WHERE agent_id='$agentid'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			$skills = '';
			foreach ($result as $row) {
				if (!empty($skills)) $skills .= ",";
				$skills .= "'$row->skill_id'";
			}
			if (!empty($skills)) {

				$sql1 = "SELECT skill_id, skill_name, 'in' AS type FROM skill ";
				$sql2 = "SELECT skill_id, skill_name, 'out' AS type FROM skill_out ";
				$cond = "skill_id IN ($skills) AND active='Y'";
				$sql1 .= "WHERE $cond ";
				$sql2 .= "WHERE $cond ";
				$sql = $sql1 . " UNION (" . $sql2 . ")";
				//echo $sql;
				return $this->getDB()->query($sql);
			}
		}
		return null;
	}
	
	function getConferenceExtNumber($conf_id)
	{
		$sql = "SELECT ext_number, status FROM conf_ext_number WHERE conf_id='$conf_id' ORDER BY ext_number";
		return $this->getDB()->query($sql);
	}
	
	function getConferenceAgent($conf_id='', $agent_id='')
	{
		$cond = '';
		$sql = "SELECT agent_id, status FROM conf_agent ";
		if (!empty($conf_id)) $cond = "conf_id='$conf_id'";
		if (!empty($agent_id)) $cond = $this->getAndCondition($cond, "agent_id='$agent_id'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY agent_id ";
		return $this->getDB()->query($sql);
	}

	function addConference($conference, $agents, $ext_numbers)
	{
		if (empty($conference->bridge_number)) return false;
		
		$confid = time();
		
		$sql = "INSERT INTO conf_bridge SET conf_id='$confid', bridge_number='#50$conference->bridge_number', ".
			"title='$conference->title', delay_ext_dial='$conference->delay_ext_dial'";
		
		if ($this->getDB()->query($sql)) {
			
			
			$this->addConferenceAgents($confid, $agents);
			$this->addConferenceExtNumbers($confid, $ext_numbers);


			//$ltxt = $agent->active=='Y' ? 'Active' : 'Inactive';
			//$ltxt = "Nick=".$agent->nick.";Pass=*;Status=".$ltxt;
			//$skill_text = $this->getLoggedServices($services, $skill_options);
			//if (!empty($skill_text)) $ltxt .= ";$skill_text";
			$ltxt = '';
			$this->addToAuditLog('Conference', 'A', "Bridge number=".$conference->bridge_number, $ltxt);

			return true;
		}
		
		return false;
	}
	
	function updateConference($oldconf, $conference, $agents, $ext_numbers, $task_type)
	{
		if (empty($oldconf->conf_id)) return false;
		$is_update = false;
		$changed_fields = '';
		$ltext = '';
		
		if ($conference->title != $oldconf->title) {
			$changed_fields .= "title='$conference->title'";
			$ltext = "Title=$oldconf->title to $conference->title";
		}
		if ($conference->delay_ext_dial != $oldconf->delay_ext_dial) {
			if (!empty($changed_fields)) $changed_fields .= ', ';
			$changed_fields .= "delay_ext_dial='$conference->delay_ext_dial'";
			$ltext = $this->addAuditText($ltext, "Delay dial=$oldconf->delay_ext_dial to $conference->delay_ext_dial");
		}

		if (!empty($task_type)) {
			
			if ($task_type == 'S') {
				if (!empty($changed_fields)) $changed_fields .= ', ';
				$stime = time();
				$etime = $stime + 1800;
				$changed_fields .= "active='Y', start_time='$stime', end_time='$etime'";
			} else if ($task_type == 'E') {
				if (!empty($changed_fields)) $changed_fields .= ', ';
				$changed_fields .= "active='N'";
			}
		}

		if (!empty($changed_fields)) {
			$sql = "UPDATE conf_bridge SET $changed_fields WHERE conf_id='$oldconf->conf_id'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($conference->agents != $oldconf->agents) {
			$agents_cond = "'" . implode("','", $agents) . "'";
			$sql = "DELETE FROM conf_agent WHERE conf_id='$oldconf->conf_id' AND agent_id NOT IN ($agents_cond)";
			if ($this->getDB()->query($sql)) $is_update = true;
			if ($this->addConferenceAgents($oldconf->conf_id, $agents)) {
				$is_update = true;
				$ltext = $this->addAuditText($ltext, "Agent list updated");
			}
		}
		
		if ($conference->ext_numbers != $oldconf->ext_numbers) {
			$agents_cond = "'" . implode("','", $ext_numbers) . "'";
			$sql = "DELETE FROM conf_ext_number WHERE conf_id='$oldconf->conf_id' AND ext_number NOT IN ($agents_cond)";
			if ($this->getDB()->query($sql)) $is_update = true;
			if ($this->addConferenceExtNumbers($oldconf->conf_id, $ext_numbers)) {
				$is_update = true;
				$ltext = $this->addAuditText($ltext, "External number list updated");
			}
		}
		
		
		if ($is_update) {
			$this->addToAuditLog('Conference', 'U', "Bridge number=".$oldconf->bridge_number, $ltext);
		}
		
		return $is_update;
	}
	
	function deleteConference($confid)
	{
		$confinfo = $this->getConferenceById($confid); 
		if (!empty($confinfo)) {
			$sql = "DELETE FROM conf_bridge WHERE conf_id='$confid'";
			if ($this->getDB()->query($sql)) {
				$sql = "DELETE FROM conf_agent WHERE conf_id='$confid'";
				$this->getDB()->query($sql);

				$sql = "DELETE FROM conf_ext_number WHERE conf_id='$confid'";
				$this->getDB()->query($sql);
				
				$this->addToAuditLog('Conference', 'D', "Bridge number=$confinfo->bridge_number", "Title=".$confinfo->title);
				return true;
			}
		}
		return false;
	}
	
	function getLoggedServices($services, $skill_options)
	{
		$ltext = '';

		$skills = array();
		if (is_array($skill_options)) {
			foreach ($skill_options as $skill) $skills[$skill->skill_id] = $skill->skill_name;
		}
		if (is_array($services)) {
			foreach ($services as $srv) {
				if (!empty($ltext)) $ltext .= ',';
				$ltext .= isset($skills[$srv]) ? $skills[$srv] : $srv;
			}
		}
		
		return empty($ltext) ? $ltext : 'Skills=' . $ltext;
	}
	
	function addConferenceExtNumbers($conf_id, $ext_numbers)
	{
		$is_update = false;
		if (is_array($ext_numbers)) {
			foreach ($ext_numbers as $srv) {
				$sql = "INSERT IGNORE INTO conf_ext_number SET conf_id='$conf_id', ext_number='$srv'";
				if ($this->getDB()->query($sql)) $is_update = true;
			}
		}
		return $is_update;
	}
	
	function addConferenceAgents($conf_id, $agents)
	{
		$is_update = false;
		if (is_array($agents)) {
			foreach ($agents as $srv) {
				$sql = "INSERT IGNORE INTO conf_agent SET conf_id='$conf_id', agent_id='$srv'";
				if ($this->getDB()->query($sql)) $is_update = true;
			}
		}
		return $is_update;
	}

	function updateAgentStatus($agentid, $status='', $utype='A', $agent_title='')
	{
		if (empty($agentid)) return false;
		if ($status=='Y' || $status=='N') {
			$sql = "UPDATE agents SET active='$status' WHERE agent_id='$agentid' AND usertype='$utype'";
			if ($this->getDB()->query($sql)) {

				$ltxt = $status=='Y' ? 'Inactive to Active' : 'Active to Inactive';
				$this->addToAuditLog($agent_title, 'U', $agent_title . "=$agentid", "Status=".$ltxt);
				return true;
			}
		}
		return false;
	}

	function updateAgentTBankStatus($agentid, $status='', $utype='A', $agent_title='')
	{
		if (empty($agentid)) return false;
		if ($status=='Y' || $status=='N') {
			$sql = "UPDATE agents SET web_password_priv='$status' WHERE agent_id='$agentid' AND usertype='$utype'";
			if ($this->getDB()->query($sql)) {

				$ltxt = $status=='Y' ? 'Inactive to Active' : 'Active to Inactive';
				$this->addToAuditLog($agent_title, 'U', $agent_title . "=$agentid", "TeleBank Status=".$ltxt);
				return true;
			}
		}
		return false;
	}
	
	

	

	function setAgentPassword($uid, $pass)
	{
		if (empty($uid)) return false;
		$sql = "UPDATE agents SET password='$pass' WHERE agent_id='$uid'";
		return $this->getDB()->query($sql);
	}

}

?>