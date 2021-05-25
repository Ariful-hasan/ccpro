<?php

class MCrm extends Model
{
	var $_cdr_csv_save_path = null;
	
	function __construct() {
		parent::__construct();
	}

	function numCrmRecordCount($camid, $leadid, $dateinfo)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('last_dial_time', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT COUNT(DISTINCT last_disposition_id) AS numrows FROM crm ";
		if (!empty($camid)) $cond = $this->getAndCondition($cond, "campaign_id='$camid'");
		if (!empty($leadid)) $cond = $this->getAndCondition($cond, "lead_id='$leadid'");
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getCrmRecordCount($camid, $leadid, $dateinfo, $offset=0, $limit=0)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('last_dial_time', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT last_disposition_id, COUNT(record_id) AS numrecords FROM crm ";
		if (!empty($camid)) $cond = $this->getAndCondition($cond, "campaign_id='$camid'");
		if (!empty($leadid)) $cond = $this->getAndCondition($cond, "lead_id='$leadid'");
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "GROUP BY last_disposition_id ORDER BY last_disposition_id LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getTotalCrmRecordCount($camid, $leadid, $dateinfo)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('last_dial_time', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT COUNT(record_id) AS numrows FROM crm ";
		if (!empty($camid)) $cond = $this->getAndCondition($cond, "campaign_id='$camid'");
		if (!empty($leadid)) $cond = $this->getAndCondition($cond, "lead_id='$leadid'");
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}

	function getCdrCsvSavePath()
	{
		if ($this->_cdr_csv_save_path === null) {
			require('./conf.extras.php');
			$this->_cdr_csv_save_path = $extra->cdr_csv_save_path;
		}
		
		return $this->_cdr_csv_save_path;
	}
	
	function numCrmDispositions($rid)
	{
		$sql = "SELECT COUNT(record_id) AS numrows FROM crm_disposition WHERE record_id='$rid'";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getCrmDispositions($rid, $offset=0, $limit=0)
	{
		$sql = "SELECT cd.callid, cd.disposition_id, cd.tstamp, cd.agent_id, a.nick, d.title, cn.note FROM crm_disposition AS cd ".
			"LEFT JOIN agents AS a ON a.agent_id=cd.agent_id LEFT JOIN disposition AS d ON d.disposition_id=cd.disposition_id ".
			"LEFT JOIN crm_note  AS cn ON cn.callid=cd.callid ";
		$sql .= "WHERE cd.record_id='$rid' ORDER BY tstamp DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function numCrmRecords($dial_num='', $camid='', $leadid='', $disp_code='')
	{
		$cond = '';
		$sql = "SELECT COUNT(record_id) AS numrows FROM crm ";
		if (!empty($dial_num)) $cond .= "dial_number LIKE '$dial_num%'";
		if (!empty($camid)) $cond = $this->getAndCondition($cond, "campaign_id='$camid'");
		if (!empty($leadid)) $cond = $this->getAndCondition($cond, "lead_id='$leadid'");
		if (!empty($disp_code)) $cond = $this->getAndCondition($cond, "last_disposition_id='$disp_code'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function churnCrmRecords($dial_num, $camid, $leadid, $disp_code, $num_selected)
	{
		$cond = '';
		$sql = "UPDATE crm SET churn=churn+1, dial_count=0, status='N' ";
		if (!empty($dial_num)) $cond .= "dial_number LIKE '$dial_num%'";
		if (!empty($camid)) $cond = $this->getAndCondition($cond, "campaign_id='$camid'");
		if (!empty($leadid)) $cond = $this->getAndCondition($cond, "lead_id='$leadid'");
		if (!empty($disp_code)) $cond = $this->getAndCondition($cond, "last_disposition_id='$disp_code'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "LIMIT $num_selected";
		return $this->getDB()->query($sql);
	}
		
	function prepareCRMFile($dial_num, $camid, $leadid, $disp_code, $file_name)
	{
		$cond = '';
		$sql = "SELECT IFNULL(cp.title, '') AS cp_title, IFNULL(lp.title, '') AS lp_title, last_dial_time, first_name, last_name, dial_attempted, ".
			"IFNULL(CONCAT('[', last_disposition_id, '] ', dp.title), '') AS dp_title, FROM_UNIXTIME(last_dial_time) AS last_dial_time FROM crm AS c ".
			"LEFT JOIN campaign_profile AS cp ON cp.campaign_id=c.campaign_id LEFT JOIN lead_profile AS lp ON lp.lead_id=c.lead_id ".
			"LEFT JOIN disposition AS dp ON dp.disposition_id=c.last_disposition_id ";
		if (!empty($dial_num)) $cond .= "dial_number LIKE '$dial_num%'";
		if (!empty($camid)) $cond = $this->getAndCondition($cond, "c.campaign_id='$camid'");
		if (!empty($leadid)) $cond = $this->getAndCondition($cond, "c.lead_id='$leadid'");
		if (!empty($disp_code)) $cond = $this->getAndCondition($cond, "last_disposition_id='$disp_code'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY first_name, last_name ";

		$sql .= " INTO OUTFILE '$file_name' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";
		//echo $sql;exit;
		if (file_exists($file_name)) {
			//echo $file_name;
			unlink($file_name);
		}
			
		$is_success = $this->getDB()->dumpResult($sql);

		return $is_success;
	}
	
	function getCrmRecordsForVoice($dial_num='', $camid='', $leadid='', $disp_code='')
	{
		$cond = '';
		$sql = "SELECT record_id, last_callid, cp.title AS cp_title, lp.title AS lp_title, dial_number, last_dial_time";
		$sql .= " FROM crm AS c ".
			"LEFT JOIN campaign_profile AS cp ON cp.campaign_id=c.campaign_id LEFT JOIN lead_profile AS lp ON lp.lead_id=c.lead_id ".
			"LEFT JOIN disposition AS dp ON dp.disposition_id=c.last_disposition_id ";
		if (!empty($dial_num)) $cond .= "dial_number LIKE '$dial_num%'";
		if (!empty($camid)) $cond = $this->getAndCondition($cond, "c.campaign_id='$camid'");
		if (!empty($leadid)) $cond = $this->getAndCondition($cond, "c.lead_id='$leadid'");
		if (!empty($disp_code)) $cond = $this->getAndCondition($cond, "last_disposition_id='$disp_code'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY first_name, last_name ";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getCrmRecords($dial_num='', $camid='', $leadid='', $disp_code='', $offset=0, $limit=0)
	{
		$cond = '';
		$sql = "SELECT record_id, c.campaign_id, cp.title AS cp_title, lp.title AS lp_title, dial_attempted, last_callid, c.status, ".
			"dp.title AS dp_title, c.lead_id, dial_number, first_name, last_name, last_dial_time, last_disposition_id FROM crm AS c ".
			"LEFT JOIN campaign_profile AS cp ON cp.campaign_id=c.campaign_id LEFT JOIN lead_profile AS lp ON lp.lead_id=c.lead_id ".
			"LEFT JOIN disposition AS dp ON dp.disposition_id=c.last_disposition_id ";
		if (!empty($dial_num)) $cond .= "dial_number LIKE '$dial_num%'";
		if (!empty($camid)) $cond = $this->getAndCondition($cond, "c.campaign_id='$camid'");
		if (!empty($leadid)) $cond = $this->getAndCondition($cond, "c.lead_id='$leadid'");
		if (!empty($disp_code)) $cond = $this->getAndCondition($cond, "last_disposition_id='$disp_code'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY first_name, last_name ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;

		return $this->getDB()->query($sql);
	}

	function getCrmRecordsByCond($cond, $offset=0, $limit=0)
	{
		$sql = "SELECT record_id, c.campaign_id, cp.title AS cp_title, lp.title AS lp_title, dial_attempted, c.status, ".
			"dp.title AS dp_title, c.lead_id, dial_number, first_name, last_name, last_dial_time, last_disposition_id FROM crm AS c ".
			"LEFT JOIN campaign_profile AS cp ON cp.campaign_id=c.campaign_id LEFT JOIN lead_profile AS lp ON lp.lead_id=c.lead_id ".
			"LEFT JOIN disposition AS dp ON dp.disposition_id=c.last_disposition_id ";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY first_name, last_name ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		return $this->getDB()->query($sql);
	}

	function getCrmRecordDetailByCond($cond, $offset=0, $limit=0)
	{
		$sql = "SELECT record_id, c.campaign_id, cp.title AS cp_title, lp.title AS lp_title, dial_attempted, ".
			"dp.title AS dp_title, c.lead_id, dial_number, first_name, last_name, middle_name, c.title, DOB, ".
			"house_no, street, landmarks, city, state, zip, country, home_phone, office_phone, mobile_phone, ".
			"other_phone, fax, email, last_dial_time, last_disposition_id, last_callid, churn, dial_count FROM crm AS c ".
			"LEFT JOIN campaign_profile AS cp ON cp.campaign_id=c.campaign_id LEFT JOIN lead_profile AS lp ON lp.lead_id=c.lead_id ".
			"LEFT JOIN disposition AS dp ON dp.disposition_id=c.last_disposition_id ";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY first_name, last_name ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function numCrmRecordsByCond($cond)
	{
		$sql = "SELECT COUNT(record_id) AS numrows FROM crm ";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function svaePersonalProfile($record_id, $pinfo)
	{
		$sql = "UPDATE crm SET title='$pinfo->title', first_name='$pinfo->first_name', ".
			"middle_name='$pinfo->middle_name', last_name='$pinfo->last_name', DOB='$pinfo->DOB' WHERE record_id='$record_id' LIMIT 1";
		return $this->getDB()->query($sql);
	}
	
	function svaePersonalAddress($record_id, $ainfo)
	{
		$sql = "UPDATE crm SET house_no='$ainfo->house_no', street='$ainfo->street', ".
			"landmarks='$ainfo->landmarks', city='$ainfo->city', state='$ainfo->state', zip='$ainfo->zip', ".
			"country='$ainfo->country' WHERE record_id='$record_id' LIMIT 1";
		return $this->getDB()->query($sql);
	}

	function svaeContackDetails($record_id, $cinfo)
	{
		$sql = "UPDATE crm SET home_phone='$cinfo->home_phone', mobile_phone='$cinfo->mobile_phone', ".
			"office_phone='$cinfo->office_phone', other_phone='$cinfo->other_phone', fax='$cinfo->fax', email='$cinfo->email' WHERE record_id='$record_id' LIMIT 1";
		return $this->getDB()->query($sql);
	}
	
	function svaeDisposition($last_callid, $record_id, $disposition, $note, $agentid, $schedule)
	{
		$time = time();
		$resp = false;
		
		$sql = "UPDATE crm SET last_disposition_id='$disposition'";
		if ($schedule > 0) $sql .= ", dial_count=0";
		$sql .= " WHERE record_id='$record_id' LIMIT 1";
		if ($this->getDB()->query($sql)) {
			$resp = true;
		}

		//$ltime = $time - 300;
		//$lagent = '';
		$sql = "UPDATE crm_disposition SET disposition_id='$disposition', tstamp='$time', agent_id='$agentid' WHERE callid='$last_callid'";
		if ($this->getDB()->query($sql)) $resp = true;
		
		if (empty($note)) {
			$sql = "DELETE FROM crm_note WHERE callid='$last_callid'";
			if ($this->getDB()->query($sql)) $resp = true;
		} else {
			$sql = "SELECT * FROM crm_note WHERE callid='$last_callid'";
			$res = $this->getDB()->query($sql);
				
			if (is_array($res)) {
				$sql = "UPDATE crm_note SET note='$note' WHERE callid='$last_callid'";
				if ($this->getDB()->query($sql)) $resp = true;
			} else {
				$sql = "INSERT INTO crm_note SET callid='$last_callid', note='$note'";
				if ($this->getDB()->query($sql)) $resp = true;
			}
		}
			
		/*
		$sql = "SELECT * FROM crm_disposition WHERE record_id='$record_id' AND agent_id='$agentid' AND tstamp>$ltime";
		$result = $this->getDB()->query($sql);
		
		if (is_array($result)) {
			$old_tstamp = $result[0]->tstamp;
			$sql = "UPDATE crm_disposition SET disposition_id='$disposition', tstamp='$time' WHERE record_id='$record_id' AND agent_id='$agentid' AND tstamp='$old_tstamp'";
			if ($this->getDB()->query($sql)) $resp = true;
			
			if (empty($note)) {
				$sql = "DELETE FROM crm_note WHERE record_id='$record_id' AND tstamp='$old_tstamp'";
				if ($this->getDB()->query($sql)) $resp = true;
			} else {
				$sql = "SELECT * FROM crm_note WHERE record_id='$record_id' AND tstamp='$old_tstamp'";
				$res = $this->getDB()->query($sql);
				
				if (is_array($res)) {
					$sql = "UPDATE crm_note SET note='$note', tstamp='$time' WHERE record_id='$record_id' AND tstamp='$old_tstamp'";
					if ($this->getDB()->query($sql)) $resp = true;
				} else {
					$sql = "INSERT INTO crm_note SET record_id='$record_id', tstamp='$time', note='$note'";
					if ($this->getDB()->query($sql)) $resp = true;
				}
			}
		} else {
			$sql = "INSERT INTO crm_disposition SET record_id='$record_id', disposition_id='$disposition', tstamp='$time', agent_id='$agentid'";
			if ($this->getDB()->query($sql)) $resp = true;
			
			if ($resp && !empty($note)) {
				$sql = "INSERT INTO crm_note SET record_id='$record_id', tstamp='$time', note='$note'";
				if ($this->getDB()->query($sql)) $resp = true;
			}
		}
		*/
		
		return $resp;
	}
	
	function svaeScheduleDial($campaign_id, $record_id, $days, $hrs, $min, $sctime, $dial_number, $agent_id)
	{
		$sql = "SELECT * FROM schedule_dial WHERE record_id='$record_id' AND campaign_id='$campaign_id'";
		$result = $this->getDB()->query($sql);
		
		if (!empty($days) || !empty($hrs) || !empty($min)) $time = time() + $days * 86400 + $hrs * 3600 + $min * 60;
		else $time = $sctime;
		
		if (is_array($result)) {
			$sql = "UPDATE schedule_dial SET dial_number='$dial_number', trigger_time='$time', agent_id='$agent_id' WHERE record_id='$record_id' AND campaign_id='$campaign_id'";
			return $this->getDB()->query($sql);
		} else {
			$sql = "INSERT INTO schedule_dial SET campaign_id='$campaign_id', record_id='$record_id', dial_number='$dial_number', trigger_time='$time', agent_id='$agent_id'";
			return $this->getDB()->query($sql);
		}
		
		return false;
	}
	
	function getScheduleDial($campaign_id, $record_id)
	{
		$time = time();
		
		$sql = "SELECT * FROM schedule_dial WHERE record_id='$record_id' AND trigger_time>$time AND campaign_id='$campaign_id' LIMIT 1";
		$result = $this->getDB()->query($sql);
		
		if (is_array($result)) return $result[0];
		
		return null;
	}

	function delScheduleDial($campaign_id, $record_id)
	{
		$sql = "DELETE FROM schedule_dial WHERE record_id='$record_id' AND campaign_id='$campaign_id'";
		return $this->getDB()->query($sql);
	}
}

?>