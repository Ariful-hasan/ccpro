<?php

class MSkill extends Model
{
	function __construct() {
		parent::__construct();
	}

	function syncVoice($sync_type, $sid, $file='')
	{
		$db_suff = UserAuth::getDBSuffix();
		$sql = "INSERT INTO cc_master.sync_web_uploads SET db_suffix='$db_suff', ".
			"update_type='$sync_type', update_id='$sid', upload_path='$file', update_time=NOW()";
		$this->getDB()->query($sql);
	}
	
	function getEmails($skillid='', $format='sql')
	{
		if (empty($skillid)) {
			return $format == 'string' ? '' : null;
		}
		
		$sql = "SELECT email FROM e_mail2skill WHERE skill_id='$skillid'";
		$result = $this->getDB()->query($sql);
		if ($format == 'string') {
			if (is_array($result)) {
				$str = '';
				foreach ($result as $sk) {
					if (!empty($str)) $str .= ', ';
					$str .= $sk->email;
				}
				return $str;
			} else {
				return '';
			}
		} else if ($format == 'array') {
			if (is_array($result)) {
				$emails = array();
				foreach ($result as $sk) {
					$emails[] = $sk->email;
				}
				return $emails;
			}
		}
		return $result;
	}
	
	function removeSkillAgents($skillid, $agents0, $agents1, $agents2, $agents3,$agents4, $agents5, $agents6, $agents7,$agents8, $agents9, $skilldetails)
	{
		$del_cond = "";
		$del_cond0 = "";
		$del_cond1 = "";
		$del_cond2 = "";
        $del_cond3 = "";
		$del_cond4 = "";
		$del_cond5 = "";
		$del_cond6 = "";
		$del_cond7 = "";
		$del_cond8 = "";
		$del_cond9 = "";


		if (count($agents0) > 0) { $del_cond0 .= implode("','", $agents0); }
		if (count($agents1) > 0) { $del_cond1 .= implode("','", $agents1); }
		if (count($agents2) > 0) $del_cond2 .= implode("','", $agents2);
		if (count($agents3) > 0) $del_cond3 .= implode("','", $agents3);
		if (count($agents4) > 0) $del_cond4 .= implode("','", $agents4);
		if (count($agents5) > 0) $del_cond5 .= implode("','", $agents5);
		if (count($agents6) > 0) $del_cond6 .= implode("','", $agents6);
		if (count($agents7) > 0) $del_cond7 .= implode("','", $agents7);
		if (count($agents8) > 0) $del_cond8 .= implode("','", $agents8);
		if (count($agents9) > 0) $del_cond9 .= implode("','", $agents9);

		if (!empty($del_cond1)) $del_cond .= "'" . $del_cond1 . "'";
		if (!empty($del_cond2)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond2 . "'";
		}
		if (!empty($del_cond3)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond3 . "'";
		}
		if (!empty($del_cond4)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond4 . "'";
		}
		if (!empty($del_cond5)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond5 . "'";
		}
		if (!empty($del_cond6)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond6 . "'";
		}
		if (!empty($del_cond7)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond7 . "'";
		}
		if (!empty($del_cond8)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond8 . "'";
		}
		if (!empty($del_cond9)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond9 . "'";
		}
		if (!empty($del_cond0)) {
			if (!empty($del_cond)) $del_cond .= ",";
			$del_cond .= "'" . $del_cond0 . "'";
		}
		
		if (empty($del_cond)) $del_cond = "''";
		
		$sql = "DELETE FROM agent_skill WHERE skill_id='$skillid' AND agent_id NOT IN ($del_cond)";
		$resp = $this->getDB()->query($sql);
		if ($resp) {
			$num = $this->getDB()->getAffectedRows();
			$audit_text = $num == 1 ? $num . ' agent ' : $num . ' agents ';
			$audit_text .= ' removed from skill';
			$this->addToAuditLog('Skill', 'D', "Skill=".$skilldetails->skill_name, $audit_text);
		}
		return $resp;
	}
	
	function addSkillAgents($skillid, $priority, $agents, $skilldetails)
	{
		$is_update = false;
		$count = 0;
		//$del_cond = "'";
		if (is_array($agents)) {
			//$del_cond .= implode("','", $agents);
			foreach ($agents as $ag) {
				$sql = "INSERT INTO agent_skill SET skill_id='$skillid', agent_id='$ag', priority='$priority' ON DUPLICATE KEY UPDATE priority='$priority'";
				if ($this->getDB()->query($sql)) {
					$is_update = true;
					$count++;
				}
			}
		}
		//$del_cond .= "'";
		
		//$sql = "DELETE FROM agent_skill WHERE skill_id='$skillid' AND agent_id NOT IN ($del_cond)";
		//if ($this->getDB()->query($sql)) $is_update = true;
		if ($count > 0) {
			$audit_text = $count == 1 ? $count . ' agent ' : $count . ' agents ';
			$audit_text .= ' added in the skill';
			$this->addToAuditLog('Skill', 'A', "Skill=".$skilldetails->skill_name, $audit_text);
		}
		return $is_update;
	}
	
	function isAllowedSkill($agentid, $skillid)
	{
		$resp = false;
		$sql = "SELECT skill_id FROM agent_skill WHERE skill_id='$skillid' AND agent_id='$agentid'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			$resp = true;
		}
		return $resp;
	}
	
	function getEmailSkillOptions($showSelectOption=false)
	{		
		if($showSelectOption){
		    $options = array('*'=>'Select');
		}else{
		    $options = array();
		}
		$sql = "SELECT skill_id, skill_name FROM skill WHERE (qtype='E' || qtype='C') AND active='Y' ORDER BY skill_id ";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $skill) {
				$options[$skill->skill_id] = $skill->skill_name;
			}
		}
		return $options;
	}

	function getDuplicateSkillEmail($oldskill, $skill)
	{
		if ($oldskill->emails != $skill->emails) {
			$emails = explode(",", $skill->emails);
			if (is_array($emails)) {
				foreach ($emails as $email) {
					$email = trim($email);
					$email = str_replace(array(" "),"", $email);
					if (!empty($email)) {
						$sql = "SELECT * FROM e_mail2skill WHERE email='$email' AND skill_id!='$oldskill->skill_id'";
						$result = $this->getDB()->query($sql);
						if (is_array($result)) return $email;
					}
				}
			}
		}
		return '';
	}
	
	function updateEmailSkill($oldskill, $skill, $value_options)
	{
		if (empty($oldskill->skill_id)) return false;
		$is_update = false;
		$fields_array = array(
			'skill_name' => 'skill_name',
			'agent_ring_timeout' => 'agent_ring_timeout',
			'delay_between_calls' => 'delay_between_calls',
			'max_calls_in_queue' => 'max_calls_in_queue',
			'acd_mode' => 'acd_mode',
			'popup_url' => 'popup_url',
			'find_last_agent' => 'find_last_agent',
			'skill_type' => 'skill_type'
		);
		$changed_fields = $this->getSqlOfChangedFields($oldskill, $skill, $fields_array);

		if (!empty($changed_fields)) {
			$sql = "UPDATE skill SET $changed_fields WHERE skill_id='$oldskill->skill_id'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($oldskill->emails != $skill->emails) {
			$sql = "DELETE FROM e_mail2skill WHERE skill_id='$oldskill->skill_id'";
			$this->getDB()->query($sql);
			$emails = explode(",", $skill->emails);
			if (is_array($emails)) {
				foreach ($emails as $email) {
					$email = trim($email);
					$email = str_replace(array(" "),"", $email);
					if (!empty($email)) {
						$sql = "INSERT INTO e_mail2skill SET skill_id='$oldskill->skill_id', email='$email'";
						$this->getDB()->query($sql);
					}
				}
			}
			$is_update = true;
		}
		
		if ($is_update) {
			$field_names = array(
				'skill_name' => 'Skill',
				'agent_ring_timeout' => 'Agent ring timeout',
				'delay_between_calls' => 'Delay between calls',
				'max_calls_in_queue' => 'Max calls in queue',
				'acd_mode' => 'Call routing',
				'popup_url' => 'Popup URL',
				'emails' => 'Email',
				'find_last_agent' => 'Find last agent',
				'skill_type' => 'Skill Type'
			);
			$fields_array['emails'] = 'emails';
			$audit_text = $this->getAuditText($oldskill, $skill, $fields_array, $field_names, $value_options);
			$audit_text = rtrim($audit_text, ";");
			$this->addToAuditLog('Skill', 'U', "Skill=".$oldskill->skill_name, $audit_text);
		}
		return $is_update;
	}
	
	function numSkills($status='')
	{
		$cond = '';
		$sql = "SELECT COUNT(skill_id) AS numrows FROM skill ";
		if (!empty($status)) $cond = "active='$status'";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}

	function getSkillOptions()
	{
		$options = array();
		$sql = "SELECT skill_id, skill_name FROM skill WHERE qtype!='E' AND active='Y' ORDER BY skill_id ";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $skill) {
				$options[$skill->skill_id] = $skill->skill_name;
			}
		}
		return $options;
	}
	
	function getVoiceSkillOptions()
	{
		$options = array();
		$sql = "SELECT skill_id, skill_name FROM skill WHERE qtype IN ('V', 'P') AND active='Y' ORDER BY skill_id ";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $skill) {
				$options[$skill->skill_id] = $skill->skill_name;
			}
		}
		return $options;
	}

	function getDialerSkillOptions()
	{
		$options = array();
                $sql = "SELECT skill_id, skill_name FROM skill WHERE qtype='P' ORDER BY skill_name";
                $result = $this->getDB()->query($sql);
                if (is_array($result)) {
                        foreach ($result as $skill) {
                                $options[$skill->skill_id] = $skill->skill_name;
                        }
                }
                return $options;
	}

	function getOutSkillOptions($status='')
	{
		$options[''] = 'Select';
		$sql = "SELECT skill_id, skill_name FROM skill WHERE qtype='V' ";
		if (!empty($status)) $sql .= "AND active='$status' ";
		$sql .= "ORDER BY skill_name ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if (is_array($result)) {
			foreach ($result as $skill) {
				$options[$skill->skill_id] = $skill->skill_name;
			}
		}
		return $options;
	}

	function getSkills($status='', $type='', $offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT skill_id, qtype, popup_url, caller_priority, skill_name, acd_mode, ".
			"record_all_calls, find_last_agent, active, auto_answer FROM skill ";
		if (!empty($status)) $cond = "active='$status'";
		if (!empty($type)) {
			if (!empty($cond)) $cond .= ' AND ';
			$cond = "qtype='$type'";
		}
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY skill_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	function getSkillsNamesArray($qtype='')
	{
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

		$returnArray=array();
		$sql = "SELECT skill_id, skill_name FROM skill ";
		if (!empty($qtype) && strlen($qtype) == 1)
        {
            $sql .= "WHERE  qtype='{$qtype}' ";
        }
        if (!empty($partition_id))
        {
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND " : " WHERE ";
            $sql .= " skill_id IN(SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' AND type = 'SQ' )";
        }
        $sql .= " ORDER BY skill_name ";

		$result= $this->getDB()->query($sql);
		if($result && count($result)){
			foreach ($result as $data){
				$returnArray[$data->skill_id]=$data->skill_name;
			}
		}
		return $returnArray;
	}
	
	function getAllowedSkills($agentid, $type, $offset=0, $limit=0)
	{
		$sql = "SELECT s.skill_id, qtype, popup_url, caller_priority, skill_name, acd_mode, ".
			"record_all_calls, find_last_agent, active FROM skill s LEFT JOIN agent_skill a ON a.skill_id=s.skill_id ";
		$sql .= "WHERE a.agent_id='$agentid' ";
		if (!empty($type)) {
			$sql .= "AND qtype='$type' ";
		}
		$sql .= "ORDER BY skill_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function getSkillsForAgent($agentId='', $skillType='', $priority=0)
	{
	    $response = array();
	    if (empty($agentId) && !ctype_digit($agentId)) return $response;
	    
	    $sql = "SELECT s.skill_id, s.skill_name FROM agent_skill a LEFT JOIN skill s ON a.skill_id=s.skill_id WHERE a.agent_id='$agentId' ";
	    if (!empty($skillType) && strlen($skillType) <= 2) {
	        $sql .= "AND s.qtype='$skillType' ";
	    }
	    
	    if (strlen($priority) == 1 && $priority > 0) {
	        $sql .= "AND a.priority='$priority' ";
	    }
	    
	    $sql .= "ORDER BY skill_id ";
	    //echo $sql;
	    return $this->getDB()->query($sql);
	}
	

	function getAllSkillOptions($status='', $format = 'sql')
	{
		$sql1 = "SELECT skill_id, skill_name, 'in' AS type FROM skill WHERE qtype!='E' ";
		$sql2 = "SELECT skill_id, skill_name, 'out' AS type FROM skill_out ";
		if (!empty($status)) $cond = "active='$status'";
		if (!empty($cond)) {
			$sql1 .= "AND $cond ";
			$sql2 .= "WHERE $cond ";
		}
		//$sql = $sql1 . " UNION (" . $sql2 . ")";
		$sql = $sql1;
		//echo $sql;
		$result = $this->getDB()->query($sql);
		
		if ($format == 'array') {
			$options = array();
			if (is_array($result)) {
				foreach ($result as $skill) {
					$options[$skill->skill_id] = $skill->skill_name;
				}
			}
			return $options;
		}
		return $result;
	}

	function getAllSkillOptionsShort($status='', $format = 'sql')
        {
                $sql1 = "SELECT skill_id, short_name, 'in' AS type FROM skill WHERE qtype!='E' ";
                $sql2 = "SELECT skill_id, short_name, 'out' AS type FROM skill_out ";
                if (!empty($status)) $cond = "active='$status'";
                if (!empty($cond)) {
                        $sql1 .= "AND $cond ";
                        $sql2 .= "WHERE $cond ";
                }
                //$sql = $sql1 . " UNION (" . $sql2 . ")";
                $sql = $sql1;
                //echo $sql;
                $result = $this->getDB()->query($sql);

                if ($format == 'array') {
                        $options = array();
                        if (is_array($result)) {
                                foreach ($result as $skill) {
                                        $options[$skill->skill_id] = $skill->skill_name;
                                }
                        }
                        return $options;
                }
                return $result;
        }

	function getAllAgentSkillOptions($is_email_module, $status='', $format = 'sql')
	{
		/*
		$sql1 = "SELECT skill_id, skill_name, 'in' AS type FROM skill ";
		$sql2 = "SELECT skill_id, skill_name, 'out' AS type FROM skill_out ";
		$cond = '';
		$cond1 = '';
		if (!empty($status)) $cond = "active='$status'";
		if (!$is_email_module) {
			$cond1 = " qtype!='E' ";
		}
		if (!empty($cond) || !empty($cond1)) {
			$sql1 .= "WHERE ";
			if (!empty($cond1)) $sql1 .= "$cond1 ";
			if (!empty($cond)) {
				if (!empty($cond1)) $sql1 .= "AND ";
				$sql2 .= "$cond ";
				$sql2 .= "WHERE $cond ";
			}
		}
		$sql = $sql1 . " UNION (" . $sql2 . ")";
		*/
		$sql1 = "SELECT skill_id, skill_name FROM skill ";
		$cond = '';
		$cond1 = '';
		if (!empty($status)) $cond = "active='$status'";
		if (!$is_email_module) {
			$cond1 = " qtype!='E' ";
		}
		if (!empty($cond) || !empty($cond1)) {
			$sql1 .= "WHERE ";
			if (!empty($cond1)) $sql1 .= "$cond1 ";
			if (!empty($cond)) {
				if (!empty($cond1)) $sql1 .= "AND ";
				$sql1 .= "$cond ";
			}
		}
		//echo $sql1;
		$result = $this->getDB()->query($sql1);
		
		if ($format == 'array') {
			$options = array();
			if (is_array($result)) {
				foreach ($result as $skill) {
					$options[$skill->skill_id] = $skill->skill_name;
				}
			}
			return $options;
		}
		return $result;
	}
	
	function getOutSkills($status='', $offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT skill_id, popup_url, skill_name, max_out_calls_per_agent, auto_answer, ".
			"record_all_calls, service_level, active FROM skill_out ";
		if (!empty($status)) $cond = "active='$status'";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY skill_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function getSkillById($skillid, $type='')
	{
		$table = $type == 'out' ? 'skill_out' : 'skill';
		$sql = "SELECT * FROM $table WHERE skill_id='$skillid'";
		//echo $sql;
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getSkillField($fieldName='', $disposid='')
	{
		if (empty($fieldName) || empty($fieldName)) return "";
		$sql = "SELECT ".$fieldName." FROM email_disposition_code WHERE disposition_id='$disposid' LIMIT 1";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0]->$fieldName : "";
	}

	function updateSkill($oldskill, $skill, $extra_audit_text, $value_options, $isTTS, $isTTSUpdate)
	{
		if (empty($oldskill->skill_id)) return false;
		$is_update = false;
		
		
		$fields_array = array(
			'skill_name' => 'skill_name',
			'short_name' => 'short_name',
			//'music_directory' => 'music_directory',
			'welcome_voice' => 'welcome_voice',
			'agent_ring_timeout' => 'agent_ring_timeout',
			'delay_between_calls' => 'delay_between_calls',
			'max_calls_in_queue' => 'max_calls_in_queue',
			'max_wait_time' => 'max_wait_time',
			'acd_mode' => 'acd_mode',
			'popup_type' => 'popup_type',
			'popup_url' => 'popup_url',
			'find_last_agent' => 'find_last_agent',
			'record_all_calls' => 'record_all_calls',
			'callback_type' => 'callback_type',
			//'caller_priority' => 'caller_priority',
			//'unattended_calls_action' => 'unattended_calls_action',
			'auto_answer' => 'auto_answer',
			'service_level' => 'service_level',
			'start_filler_after' => 'start_filler_after',
			'moh_filler_after' => 'moh_filler_after',
			'skill_type' => 'skill_type'
		);
		
		if ($isTTS) {
			$fields_array['TTS'] = 'TTS';
		}
		
		if ($isTTSUpdate) {
			$fields_array['TTS_update'] = 'TTS_update';
			$skill->TTS_update = 'Y';
		}
		
		$changed_fields = $this->getSqlOfChangedFields($oldskill, $skill, $fields_array);

		if (!empty($changed_fields)) {
			$sql = "UPDATE skill SET $changed_fields WHERE skill_id='$oldskill->skill_id'";
			$is_update = $this->getDB()->query($sql);
		}
		if ($is_update || !empty($extra_audit_text)) {
			$field_names = array(
				'skill_name' => 'Skill',
				'short_name' => 'Short name',
				'music_directory' => 'Music directory',
				'welcome_voice' => 'Welcome voice',
				'agent_ring_timeout' => 'Agent ring timeout',
				'delay_between_calls' => 'Delay between calls',
				'max_calls_in_queue' => 'Max calls in queue',
				'max_wait_time' => 'Max wait time',
				'acd_mode' => 'Call routing',
				'popup_type' => 'Popup type',
				'popup_url' => 'Popup URL',
				'find_last_agent' => 'Find last agent',
				'record_all_calls' => 'Record all calls',
				'callback_type' => 'Callback type',
				'caller_priority' => 'Caller priority',
				'unattended_calls_action' => 'Unattended call action',
				'auto_answer' => 'Auto answer',
				'TTS' => 'Enable TTS',
				'service_level' => 'Service Level',
				'moh_filler_after' => 'Start MOH Filler after',
				'skill_type' => 'Skill Type'
			);

			//$notify_txt = "WWW\r\nType: UPD_SKILL\r\nskill_id: $oldskill->skill_id\r\n";
			//$this->notifyUpdate($notify_txt);
			require_once('lib/DBNotify.php');
			DBNotify::NotifySkillPropUpdate(UserAuth::getDBSuffix(), $oldskill->skill_id, $this->getDB());
			//$this->syncVoice('UPD_SKILL', $oldskill->skill_id);
			
			
			$audit_text = $extra_audit_text . $this->getAuditText($oldskill, $skill, $fields_array, $field_names, $value_options);
			if (isset($skill->TTS_update)) $audit_text .= 'TTS Text changed';
			$audit_text = rtrim($audit_text, ";");
			$this->addToAuditLog('Skill', 'U', "Skill=".$oldskill->skill_name, $audit_text);
		}
		return $is_update;
	}

	function notifyVoiceFileUpdate($skill_id, $file)
	{
		//$notify_txt = "WWW\r\nType: UPD_SKILL_VOICE\r\nskill_id: $skill_id\r\nvoice: $file\r\n";
		//$this->notifyUpdate($notify_txt);
		include_once('lib/DBNotify.php');
		DBNotify::NotifySkillUpdate(UserAuth::getDBSuffix(), $skill_id, $file, $this->getDB());
		//$this->syncVoice('UPD_SKILL_VOICE', $skill_id, $file);
	}
	
	function notifySkillAgentUpdate($skill_id)
	{
		//$notify_txt = "WWW\r\nType: UPD_SKILL_AG\r\nskill_id: $skill_id\r\n";
		//$this->notifyUpdate($notify_txt);
		include_once('lib/DBNotify.php');
		DBNotify::NotifySkillAgentUpdate(UserAuth::getDBSuffix(), $skill_id, $this->getDB());
		//$this->syncVoice('UPD_SKILL_AG', $skill_id);
	}
	
	function getOutSkillFieldsArray()
	{
		return array(
			'skill_name' => 'skill_name',
			'welcome_voice' => 'welcome_voice',
			'dial_type' => 'dial_type',
			'max_out_calls_per_agent' => 'max_out_calls_per_agent',
			'anouncement_timeouot' => 'anouncement_timeouot',
			'auto_answer' => 'auto_answer',
			'popup_url' => 'popup_url',
			'record_all_calls' => 'record_all_calls',
			'service_level' => 'service_level'
		);
	}
	
	function updateOutSkill($oldskill, $skill, $extra_audit_text)
	{
		if (empty($oldskill->skill_id)) return false;
		$is_update = false;
		$changed_fields = $this->getSqlOfChangedFields($oldskill, $skill, $this->getOutSkillFieldsArray());

		if (!empty($changed_fields)) {
			$sql = "UPDATE skill_out SET $changed_fields WHERE skill_id='$oldskill->skill_id'";
			//echo $sql;
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update || !empty($extra_audit_text)) {
			/*
				Insert audit log ..
			*/
			$field_names = array(
				'skill_name' => 'Skill',
				'welcome_voice' => 'Welcome voice',
				'dial_type' => 'Dial Type',	//M, P
				'max_out_calls_per_agent' => 'Max calls per agent',
				'anouncement_timeouot' => 'Anouncement timeout',
				'auto_answer' => 'Auto ans.',
				'popup_url' => 'Popup URL',
				'record_all_calls' => 'Record all calls',
				'service_level' => 'Service level'
			);
			$field_values = array(
				'welcome_voice' => array('Y'=>'Enable', 'N'=>'Disable'),
				'auto_answer' => array('Y'=>'Enable', 'N'=>'Disable'),
				'dial_type' => '',	//M, P
				'popup_url' => '',
				'record_all_calls' => array('Y'=>'Enable', 'N'=>'Disable')
			);
			$audit_text = $extra_audit_text . $this->getAuditText($oldskill, $skill, $this->getOutSkillFieldsArray(), $field_names, $field_values);
			$audit_text = rtrim($audit_text, ";");
			$this->addToAuditLog('Outbound Skill', 'U', "Outbound Skill=".$oldskill->skill_name, $audit_text);
		}
		return $is_update;
	}

	function updateSkillStatus($skillid, $type, $status='')
	{
		if (empty($skillid)) return false;
		if ($status=='Y' || $status=='N') {
			if ($type == 'out') {
				$table =  'skill_out';
				$skill_title = 'Outbound Skill';
			} else {
				$table =  'skill';
				$skill_title = 'Skill';
			}
			
			$sql = "UPDATE $table SET active='$status' WHERE skill_id='$skillid'";
			if ($this->getDB()->query($sql)) {
				$skillinfo = $this->getSkillById($skillid, $type);
				$skill_name = empty($skillinfo) ? $skillid : $skillinfo->skill_name;
				$ltxt = $status=='Y' ? 'Inactive to Active' : 'Active to Inactive';
				$this->addToAuditLog($skill_title, 'U', $skill_title . "=$skill_name", "Status=".$ltxt);
				return true;
			}
		}
		return false;
	}

	function getAgentSkills() {
		$sql = "SELECT skill_id, agent_id, priority FROM agent_skill";
		$result = $this->getDB()->query($sql);
		
		$options = array();
		if (is_array($result)) {
			foreach ($result as $skill) {
				$options[$skill->agent_id] = array(
													"skill_id"=>$skill->skill_id,
													"priority"=>$skill->priority
												);
			}
		}
		return $options;
	}

	function addSendSMSLog($callid='', $destNum='', $smsTxt='', $smsTplate='',$agent_id='',$skill_id='')
	{
		if (!empty($callid) && !empty($destNum) && !empty($smsTxt) && !empty($agent_id)){
			$sql = "INSERT INTO inbound_sms_log SET tstamp=UNIX_TIMESTAMP(),agent_id='{$agent_id}',skill_id='{$skill_id}', callid='$callid', dest_number='$destNum', sms_text='$smsTxt', template_id='$smsTplate'";
			if ($this->getDB()->query($sql)) {
				return true;
			}
		}
		return false;
	}

    function numInboundSMSLog($dateinfo, $mob_number='')
    {
        $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        if (empty($date_attributes->yy)) return 0;
        $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
        $table = 'agent_session_log' . $year;
        $cond = $date_attributes->condition;
        $sql = "SELECT COUNT(DISTINCT(agent_id)) AS numrows FROM $table";
        if (!empty($cond)) $sql .= " WHERE $cond";

        $result = $this->getDB()->query($sql);
        //echo $sql;
        if ($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function getInboundSMSLog($dateinfo, $mob_number='', $offset=0, $rowsPerPage=0)
    {
        $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        if (empty($date_attributes->yy)) return null;
        $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
        $table = 'agent_session_log' . $year;
        $cond = $date_attributes->condition;

        $sql = "SELECT DISTINCT agent_id";

        $sql .= " FROM $table";
        if (!empty($cond)) $sql .= " WHERE $cond";
        $sql .= " ORDER BY agent_id";
        if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
        //echo $sql;
        return $this->getDB()->query($sql);
    }

    function getAgentAndSkill($login_id, $skill_type){
    	$sql = "SELECT skill.skill_id, agent_skill.agent_id from skill LEFT JOIN agent_skill ON agent_skill.skill_id = skill.skill_id
    			WHERE skill.qtype='".$skill_type."' AND agent_skill.agent_id = '".$login_id."'";

    	return $this->getDB()->query($sql);
    }

    public function getActiveSkillAsKeyValue()
    {
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

        $returnArray = [];
        $sql = "SELECT skill_id, skill_name FROM skill WHERE active='Y' ";
        if (!empty($partition_id))
        {
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND " : " WHERE ";
            $sql .= " skill_id IN(SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' AND type = 'SQ' )";
        }
        $sql .= " ORDER BY skill_name ";

        $result= $this->getDB()->query($sql);
        if($result && count($result)){
            foreach ($result as $data){
                $returnArray[$data->skill_id]=$data->skill_name;
            }
        }
        return $returnArray;
    }

    function getSkillsTypeArray()
	{
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

		$returnArray=array();
		$sql = "SELECT skill_id, skill_name, qtype FROM skill ";
		
        if (!empty($partition_id))
        {
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND " : " WHERE ";
            $sql .= " skill_id IN(SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' AND type = 'SQ' )";
        }
        $sql .= " ORDER BY skill_name ";

		$result= $this->getDB()->query($sql);
		if($result && count($result)){
			foreach ($result as $data){
				$returnArray[$data->qtype][]=$data->skill_id;
			}
		}
		return $returnArray;
	}

	function getAllSkillsTypeArray()
	{
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

		$returnArray=array();
		$sql = "SELECT skill_id, skill_name, qtype, skill_type FROM skill ";
		
        if (!empty($partition_id)){
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND " : " WHERE ";
            $sql .= " skill_id IN(SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' AND type = 'SQ' )";
        }
        $sql .= " ORDER BY skill_name ";

		$result= $this->getDB()->query($sql);
		if($result && count($result)){
			foreach ($result as $data){
				$returnArray[$data->skill_type][]=$data->skill_id;
			}
		}
		
		return $returnArray;
	}

	function getSkillsTypeWithNameArray()
	{
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

		$returnArray=array();
		$sql = "SELECT skill_id, skill_name, qtype, skill_type FROM skill ";
		
        if (!empty($partition_id))
        {
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND " : " WHERE ";
            $sql .= " skill_id IN(SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' AND type = 'SQ' )";
        }
        $sql .= " ORDER BY skill_name ";

		$result= $this->getDB()->query($sql);
		if($result && count($result)){
			foreach ($result as $data){
				$returnArray[$data->skill_type][$data->skill_id]=$data->skill_name;
			}
		}
		return $returnArray;
	}

    public function getChatSkills()
    {
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

        $returnArray = array();
        $returnArray['*'] = "All";

        $sql = "SELECT skill_id, skill_name FROM skill WHERE qtype='C'";

        if (!empty($partition_id)) {
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND " : " WHERE ";
            $sql .= " skill_id IN(SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' AND type = 'SQ' )";
        }
        $sql .= " ORDER BY skill_name ";

        $result = $this->getDB()->query($sql);
        if ($result && count($result)) {
            foreach ($result as $data) {
                $returnArray[$data->skill_id] = $data->skill_name;
            }
        }
        return $returnArray;
    }

    public function getSkillsFromType($qtype)
    {
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

        $returnArray = array();
        $returnArray['*'] = "All";

        $sql = "SELECT skill_id, skill_name FROM skill WHERE qtype='$qtype'";

        if (!empty($partition_id)) {
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND " : " WHERE ";
            $sql .= " skill_id IN(SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' AND type = 'SQ' )";
        }
        $sql .= " ORDER BY skill_name ";

        $result = $this->getDB()->query($sql);
        if ($result && count($result)) {
            foreach ($result as $data) {
                $returnArray[$data->skill_id] = $data->skill_name;
            }
        }
        return $returnArray;
    }
	public function getSkillIdByName($skill_name)
    {
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT skill_id, skill_name FROM skill WHERE skill_name='$skill_name' LIMIT 1";

        if (!empty($partition_id)) {
            $sql .= stripos($sql, "WHERE") !== FALSE ? " AND " : " WHERE ";
            $sql .= " skill_id IN(SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' AND type = 'SQ' )";
        }

        $result = $this->getDB()->query($sql);
        if ($result && count($result)) {
            return $result[0]->skill_id;
        }
        return null;
    }


    function getVoiceSkillIDs()
    {
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT skill_id FROM skill WHERE qtype = 'V' ";

        if (!empty($partition_id))
        {
            $sql .= " AND skill_id IN(SELECT record_id FROM partition_record WHERE partition_id = '{$partition_id}' AND type = 'SQ' )";
        }
        $sql .= " ORDER BY skill_id ";

        return $this->getDB()->query($sql);

    }

    public function getDailySnapShotData()
    {
        $today = date('Y-m-d'); // '2018-09-30'

        $select = " (SUM(ans_lte_30_count)/(SUM(calls_offered)-SUM(abd_lte_30_count)) * 100) AS service_level,
         (SUM(calls_abandoned)/SUM(calls_offered) * 100) AS abandoned_ratio, 
         SUM(hold_time_in_queue)/SUM(calls_offered) AS awt,
         (SUM(ring_time)+SUM(service_duration)+SUM(wrap_up_time))/SUM(calls_answerd) as aht,
         (SUM(wrap_up_call_count)/SUM(calls_answerd) * 100) AS word_code,
         ((SUM(calls_offered)-SUM(repeat_cli_1_count))/SUM(calls_offered)) * 100 AS unique_call_per,
         SUM(calls_offered)/SUM(rgb_call_count) AS cpc, 
         (SUM(repeat_1_count)/SUM(calls_offered)) * 100 AS repeat_call_per ";

        $sql = "SELECT $select FROM  rt_skill_call_summary  AS scs INNER JOIN skill AS s ";
        $sql .= "ON  scs.skill_id = s.skill_id ";
        $sql .= " WHERE scs.sdate='{$today}' AND  s.qtype = 'V' ";

        return $this->getDB()->query($sql);
    }

    function getSkillByAgentId($skillid, $type='')
	{
		$table = $type == 'out' ? 'skill_out' : 'skill';
		$sql = "SELECT * FROM $table WHERE skill_id='$skillid'";
		//echo $sql;
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function getSkillNames($qtype='', $status='', $offset=0, $limit=0, $format='sql')
	{
		$cond = '';
		$sql = "SELECT skill_id, skill_name, qtype FROM skill ";
		if (!empty($qtype)) $cond .= "qtype='$qtype'";
		if (!empty($status)) $cond = $this->getAndCondition($cond, "active='$status'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY skill_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		// echo $sql;
		
		$result = $this->getDB()->query($sql);
		// if ($format == 'array') {
			$names = array();
			if (is_array($result)) {
				foreach ($result as $row) {
					$names[$row->skill_id]['name'] = !empty($row->skill_name) ? $row->skill_name : $row->skill_id;
					$names[$row->skill_id]['qtype'] = $row->qtype;
					// $names[$row->skill_id] = !empty($row->skill_name) ? $row->skill_name : $row->skill_id;
				}
			}
			return $names;
		// }
		// return $result;
	}
}