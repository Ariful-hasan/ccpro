<?php

class MCrmIn extends Model
{
	var $_cdr_csv_save_path = null;
	
	function __construct() {
		parent::__construct();
	}

	function getCRMLogByCallID($callid)
	{
		$sql = "SELECT * FROM skill_crm_disposition_log WHERE callid='$callid' LIMIT 1";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function numCrmInLog($tempid, $did, $dateinfo)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT COUNT(callid) AS numrows FROM skill_crm_disposition_log AS dl ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($did)) $cond = $this->getAndCondition($cond, "dl.disposition_id='$did'");
		if (!empty($tempid)) {
			$sql .= "LEFT JOIN skill_crm_disposition_code AS dc ON dc.disposition_id=dl.disposition_id ";
			$cond = $this->getAndCondition($cond, "template_id='$tempid'");
		}

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getCrmInLog($tempid, $did, $dateinfo, $offset=0, $limit=0)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT dc.template_id, dl.disposition_id, agent_id, tstamp, note, callid, account_id, caller_auth_by FROM skill_crm_disposition_log AS dl ".
			"LEFT JOIN skill_crm_disposition_code AS dc ON dc.disposition_id=dl.disposition_id LEFT JOIN skill_crm AS sc ON sc.record_id=dl.record_id ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($did)) $cond = $this->getAndCondition($cond, "dl.disposition_id='$did'");
		if (!empty($tempid)) {
			$cond = $this->getAndCondition($cond, "template_id='$tempid'");
		}

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY tstamp DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function getCrmRecordDetailByCond($cond, $offset=0, $limit=0)
	{
		$sql = "SELECT * FROM skill_crm ";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY first_name, last_name ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function numCrmRecordCount($tempid, $dateinfo)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT COUNT(DISTINCT dl.disposition_id) AS numrows FROM skill_crm_disposition_log AS dl LEFT JOIN skill_crm_disposition_code AS dc ON dc.disposition_id=dl.disposition_id ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($tempid)) $cond = $this->getAndCondition($cond, "template_id='$tempid'");

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getCrmRecordCount($tempid, $dateinfo, $offset=0, $limit=0)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT dl.disposition_id, COUNT(record_id) AS numrecords FROM skill_crm_disposition_log AS dl ".
			"LEFT JOIN skill_crm_disposition_code AS dc ON dc.disposition_id=dl.disposition_id ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($tempid)) $cond = $this->getAndCondition($cond, "template_id='$tempid'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "GROUP BY dl.disposition_id ORDER BY dl.disposition_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getTotalCrmRecordCount($tempid, $dateinfo)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT COUNT(record_id) AS numrows FROM skill_crm_disposition_log AS dl LEFT JOIN skill_crm_disposition_code AS dc ON dc.disposition_id=dl.disposition_id ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($tempid)) $cond = $this->getAndCondition($cond, "template_id='$tempid'");
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
		$sql = "SELECT COUNT(record_id) AS numrows FROM skill_crm_disposition_log WHERE record_id='$rid'";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getCrmDispositions($rid, $offset=0, $limit=0)
	{
		$sql = "SELECT cd.callid, cd.disposition_id, cd.tstamp, cd.agent_id, a.nick, d.title, cd.note, caller_auth_by FROM skill_crm_disposition_log AS cd ".
			"LEFT JOIN agents AS a ON a.agent_id=cd.agent_id LEFT JOIN skill_crm_disposition_code AS d ON d.disposition_id=cd.disposition_id ";
		$sql .= "WHERE cd.record_id='$rid' ORDER BY tstamp DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getCrmIVRPendingServices($account_id, $offset=0, $limit=0)
	{
		$sql = "SELECT r.tstamp, r.caller_id, r.disposition_code, d.service_title, d.service_type FROM ivr_service_request AS r ".
			"LEFT JOIN ivr_service_code AS d ON d.disposition_code=r.disposition_code WHERE account_id='$account_id' ".
			"ORDER BY tstamp DESC LIMIT $offset, $limit";
		return $this->getDB()->query($sql);
	}

	function getCrmIVRServedServices($account_id, $offset=0, $limit=0)
	{
		$sql = "SELECT s.tstamp, s.served_time, s.caller_id, s.disposition_code, s.agent_id, a.nick, d.service_title, d.service_type FROM ivr_service_request_log AS s ".
			"LEFT JOIN ivr_service_code AS d ON d.disposition_code=s.disposition_code LEFT JOIN agents AS a ON a.agent_id=s.agent_id WHERE account_id='$account_id' ".
			"ORDER BY tstamp DESC LIMIT $offset, $limit";
		return $this->getDB()->query($sql);
	}
	
	function numCrmRecords($account_id='', $dcode='', $dateinfo)
	{
	    $cond = '';
		$date_attributes = DateHelper::get_date_attributes('l.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT COUNT(c.record_id) AS numrows FROM skill_crm AS c ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($account_id)) $cond .= "account_id LIKE '$account_id%'";
		if (!empty($dcode)) {
		    $cond = $this->getAndCondition($cond, "disposition_id='$dcode'");
		}
		if (!empty($dcode) || !empty($date_attributes->condition)) {
			$sql .= "LEFT JOIN skill_crm_disposition_log AS l ON l.callid=c.last_callid ";
		}
		//if (!empty($camid)) $cond = $this->getAndCondition($cond, "campaign_id='$camid'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getCRMRecord($record_id)
	{
		$sql = "SELECT account_id FROM skill_crm WHERE record_id='$record_id' LIMIT 1";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getCrmRecords($account_id='', $dcode='', $offset=0, $limit=0, $dateinfo)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('l.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT c.record_id, l.disposition_id, l.tstamp, l.agent_id, l.note, account_id ".
			"FROM skill_crm AS c LEFT JOIN skill_crm_disposition_log AS l ON l.callid=c.last_callid ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($account_id)) $cond .= "account_id LIKE '$account_id%'";
		if (!empty($dcode)) $cond = $this->getAndCondition($cond, "disposition_id='$dcode'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY last_callid DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;

		return $this->getDB()->query($sql);
	}
	
	function prepareCRMFile($account_id, $dcode, $file_name)
	{
		$cond = '';
		$sql = "SELECT account_id, title, first_name, middle_name, last_name, DOB, house_no, street, landmarks, city, state, zip, country, home_phone, office_phone, mobile_phone, other_phone, fax, email, priority_label, last_callid, TPIN, activation_date, status FROM skill_crm AS c ";
		if (!empty($account_id)) $cond .= "account_id LIKE '$account_id%'";
		if (!empty($dcode)) {
			$cond = $this->getAndCondition($cond, "disposition_id='$dcode'");
			$sql .= "LEFT JOIN skill_crm_disposition_log AS l ON l.callid=c.last_callid ";
		}
		//if (!empty($camid)) $cond = $this->getAndCondition($cond, "c.campaign_id='$camid'");
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY first_name, last_name ";

		$sql .= " INTO OUTFILE '$file_name' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' ESCAPED BY '\"' LINES TERMINATED BY '\n'";
		//echo $sql;exit;
		if (file_exists($file_name)) {
			//echo $file_name;
			unlink($file_name);
		}
		
		$is_success = $this->getDB()->dumpResult($sql);

		return $is_success;
	}
	
	function addCRMRecord($db_cols, $record)
	{
		if (is_array($db_cols)) {
			$sql = '';
			$record_id = '';
			$try = 0;
			foreach ($db_cols as $fld=>$i) {
				if (!empty($record[$i])) {
					if (!empty($sql)) $sql .= ',';
					if ($fld != 'DOB') $sql .= "$fld='" . trim($record[$i]) . "'";
					else $sql .= "$fld='" . date("Y-m-d", strtotime(trim($record[$i]))) . "'";
					if ($fld == 'record_id') $record_id = trim($record[$i]);
				}
			}
			
			while (empty($record_id)) {
				$record_id = time() . rand(1000, 9999);
				$try++;
				$sql1 = "SELECT record_id FROM skill_crm WHERE record_id='$record_id' LIMIT 1";
				$result = $this->getDB()->query($sql1);
				if (!empty($result)) {
					$record_id = '';
				} else {
					if (!empty($sql)) $sql .= ',';
					$sql .= "record_id='$record_id'";
				}
				if ($try > 5) return false;
				
			}
			
			if (!empty($sql)) {
				$sql = "INSERT IGNORE INTO skill_crm SET " . $sql;
				return $this->getDB()->query($sql);
			}
		}
		
		return false;
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
	
	function addDispositionLog($record_id, $callid, $agentid='', $disposition='', $note='')
	{
		$time = time();
		$sql = "INSERT INTO skill_crm_disposition_log SET record_id='$record_id', callid='$callid', ".
				"disposition_id='$disposition', tstamp='$time', agent_id='$agentid', note='$note'";
		return $this->getDB()->query($sql);
	}
	
	function svaeDisposition($last_callid, $record_id, $disposition, $note, $status, $agentid)
	{
		$time = time();
		$resp = false;
		
		//$sql = "SELECT callid FROM skill_crm_disposition_log WHERE callid='$last_callid'";
		//$result = $this->getDB()->query($sql);
		
		//if (is_array($result)) {
		$sql = "UPDATE skill_crm_disposition_log SET disposition_id='$disposition', tstamp=UNIX_TIMESTAMP(), ".
			"note='$note', status='$status' WHERE callid='$last_callid' AND agent_id='$agentid'";
		//agent_id='$agentid', 
		if ($this->getDB()->query($sql)) $resp = true;
		//}
		/*
		 else {
			$sql = "INSERT INTO skill_crm_disposition_log SET record_id='$record_id', callid='$last_callid', ".
			"disposition_id='$disposition', tstamp='$time', agent_id='$agentid', note='$note'";
			if ($this->getDB()->query($sql)) $resp = true;
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