<?php

class MCrmIn extends Model
{
	var $_cdr_csv_save_path = null;
	
	function __construct() {
		parent::__construct();
	}

	function getCRMLogByCallID($callid, $record_id='', $agent_id='', $auth_by = '')
	{
	    $callid_a = explode('-', $callid);
	    $callid = $callid_a[0];
		$sql = "SELECT * FROM skill_crm_disposition_log WHERE callid='$callid' LIMIT 1";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
		    return $result[0];
		}elseif (!empty($callid)) {
			$sql = "INSERT INTO skill_crm_disposition_log SET callid='$callid', tstamp=UNIX_TIMESTAMP(), record_id='$record_id', ".
				"agent_id='$agent_id', caller_auth_by='$auth_by'";
			$this->getDB()->query($sql);
				
			return $this->getCRMLogByCallID($callid);
		}
		return null;
	}
	
	function numCrmInLog($tempid, $did, $dateinfo, $contactNo='', $accountNo='')
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('dl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT COUNT(dl.callid) AS numrows FROM skill_crm_disposition_log AS dl ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($did)) {
		    $cond = $this->getAndCondition($cond, "dl.disposition_id='$did'");
		}
		if (!empty($tempid)) {
			$sql .= "LEFT JOIN skill_crm_disposition_code AS dc ON dc.disposition_id=dl.disposition_id ";
			$cond = $this->getAndCondition($cond, "template_id='$tempid'");
		}
		if (!empty($contactNo)) {
		    $sql .= "LEFT JOIN cdrin_log as cl ON cl.callid=dl.callid ";
		    $cond = $this->getAndCondition($cond, "cl.cli LIKE '$contactNo%'");
		}
		if (!empty($accountNo)) {
		    $sql .= "LEFT JOIN skill_crm AS sc ON sc.record_id=dl.record_id ";
		    $cond = $this->getAndCondition($cond, "account_id LIKE '$accountNo%'");
		}

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getCrmInLog($tempid, $did, $dateinfo, $offset=0, $limit=0, $contactNo='', $accountNo='')
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('dl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sql = "SELECT dc.template_id, dl.disposition_id, agent_id, dl.tstamp, note, dl.callid, dl.served_account as account_id, caller_auth_by, cl.cli AS contact_no FROM skill_crm_disposition_log AS dl ".
			"LEFT JOIN skill_crm_disposition_code AS dc ON dc.disposition_id=dl.disposition_id LEFT JOIN skill_crm AS sc ON sc.record_id=dl.record_id LEFT JOIN cdrin_log as cl ON cl.callid=dl.callid ";
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($did)) {
		    $cond = $this->getAndCondition($cond, "dl.disposition_id='$did'");
		}
		if (!empty($tempid)) {
			$cond = $this->getAndCondition($cond, "template_id='$tempid'");
		}
		if (!empty($contactNo)) {
		    $cond = $this->getAndCondition($cond, "cl.cli LIKE '$contactNo%'");
		}
		if (!empty($accountNo)) {
		    $cond = $this->getAndCondition($cond, "account_id LIKE '$accountNo%'");
		}

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "ORDER BY dl.tstamp DESC ";
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
		$sql = "SELECT c.record_id, c.first_name, c.last_name, l.disposition_id, l.tstamp, l.agent_id, l.note, account_id ".
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
			$account_id = '';
			$try = 0;
			foreach ($db_cols as $fld=>$i) {
				if (!empty($record[$i])) {
				        
				        $record_i = $this->getDB()->escapeString($record[$i]);
				        
					if (!empty($sql)) $sql .= ',';
					if ($fld != 'DOB') {
					    if ($fld == 'account_id') {
					        $account_id = trim($record_i);
							if (empty($account_id)) $account_id = time() . rand(10000, 99999);
					        if (strlen($account_id) == 10 && ctype_digit($account_id) && substr($account_id, 0, 1) != '1') $account_id = '1' . $account_id;
					        $sql .= "$fld='" . $account_id . "'";
					    } else {
					        $sql .= "$fld='" . trim($record_i) . "'";
					        if ($fld == 'record_id') $record_id = trim($record_i);
					    }
					} else {
						if (empty($record_i) || $record_i == '0000-00-00'){
							$sql .= "$fld='0000-00-00'";
						}else{
							$sql .= "$fld='" . date("Y-m-d", strtotime(trim($record_i))) . "'";
						}
					}
				}else{
					if ($fld == 'account_id') {
						$account_id = time() . rand(10000, 99999);
						if (strlen($account_id) == 10 && ctype_digit($account_id) && substr($account_id, 0, 1) != '1') $account_id = '1' . $account_id;
						$sql .= "$fld='" . $account_id . "'";
					}
				}
			}
			
			if (!empty($account_id)) {
			    if (strlen($account_id) == 10 && ctype_digit($account_id) && substr($account_id, 0, 1) != '1') $account_id = '1' . $account_id;
			    $sql1 = "SELECT record_id FROM skill_crm WHERE account_id='$account_id' LIMIT 1";
                            $result = $this->getDB()->query($sql1);
                            if (!empty($result)) {
                                //return false;
								if (!empty($sql)) {//echo 'insert';die;
									$sql = "update skill_crm SET ".$sql." WHERE record_id = ".$result[0]->record_id;
									return $this->getDB()->query($sql);
								}
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
			
			if (!empty($sql)) {//echo 'insert';die;
				$sql = "INSERT IGNORE INTO skill_crm SET " . $sql;
				return $this->getDB()->query($sql);
			}
		}
		
		return false;
	}

	function addEditSkillCrm($db_cols_vals, $record_id='', $caller_id='')
	{
	    if (is_array($db_cols_vals)) {
	        $sql = '';
	        $try = 0;
	        $isCLIExist1 = false;
	        $isCLIExist2 = false;
	        $dbFields = array('account_id', 'title', 'first_name', 'middle_name', 'last_name', 'DOB', 'house_no', 'street', 'landmarks', 'city', 'state', 'zip', 'country', 'home_phone', 'office_phone', 'mobile_phone', 'other_phone', 'fax', 'email', 'status');
	        foreach ($db_cols_vals as $colName=>$colValue) {
	            if (!empty($colName) && in_array($colName, $dbFields)){
	                if ($colName == 'mobile_phone'){
	                    $isCLIExist1 = true;
	                }
	                if ($colName == 'other_phone'){
	                    $isCLIExist2 = true;
	                }
	                if (!empty($sql)){
	                    $sql .= ',';
	                }
	                $sql .= "$colName='" . $colValue . "'";
	            }
	        }
	        	
	        if (!empty($record_id)){
	            if (!empty($sql)) {
	                $sql = "UPDATE skill_crm SET " . $sql . " WHERE record_id='$record_id' LIMIT 1";
	                return $this->getDB()->query($sql);
	            }
	        }elseif (!empty($caller_id) && !empty($sql)){
	            $sql_query = "SELECT record_id FROM skill_crm where home_phone LIKE '%$caller_id%' OR ";
	            $sql_query .= "office_phone LIKE '%$caller_id%' OR mobile_phone LIKE '%$caller_id%' OR ";
	            $sql_query .= "other_phone LIKE '%$caller_id%' LIMIT 1";
	            $result = $this->getDB()->query($sql_query);
	            if ($result != null) {
	                $record_id = $result[0]->record_id;
	                $sql = "UPDATE skill_crm SET " . $sql . " WHERE record_id='$record_id' LIMIT 1";
	                return $this->getDB()->query($sql);
	            }else {
	                $record_id = '';
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
	                    if ($isCLIExist1 && $isCLIExist2){
	                        $sql = "INSERT IGNORE INTO skill_crm SET " .$sql;
	                    }elseif ($isCLIExist1 && !$isCLIExist2){
	                        $sql = "INSERT IGNORE INTO skill_crm SET " .$sql. ", other_phone='" . $caller_id . "'";
	                    }else {
	                        $sql = "INSERT IGNORE INTO skill_crm SET " .$sql. ", mobile_phone='" . $caller_id . "'";
	                    }
	                    return $this->getDB()->query($sql);
	                }
	            }
	        }else {
	            $record_id = '';
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
	        $callid_a = explode('-', $callid);
                $callid = $callid_a[0];
		$time = time();
		//echo "From 2 $callid";exit;
		$sql = "INSERT INTO skill_crm_disposition_log SET record_id='$record_id', callid='$callid', ".
				"disposition_id='$disposition', tstamp='$time', agent_id='$agentid', note='$note'";
		return $this->getDB()->query($sql);
	}
	
	function getCallSessionVars($callid)
	{
	    $sql = "SELECT * FROM tb_mpf WHERE MPF1='$callid' AND MPF6='ASV' LIMIT 1";
	    $result = $this->getDB()->query($sql);
	    
	    $vars = array();
	    
	    if (is_array($result)) {
	        if (!empty($result[0]->MPF3)) $vars[] = $result[0]->MPF3;
	        if (!empty($result[0]->MPF4)) $vars[] = $result[0]->MPF4;
	        if (!empty($result[0]->MPF5)) $vars[] = $result[0]->MPF5;
	    }
	    
	    return $vars;
	}
	
	function svaeCallSessionVar($callid, $key, $value)
	{
	    $MPF6 = 'ASV'; //Additional Session Variable
	    
	    $newMPF = $key . '|' . $value;
	    
	    $sql = "SELECT * FROM tb_mpf WHERE MPF1='$callid' AND MPF6='$MPF6' LIMIT 1";
	    $result = $this->getDB()->query($sql);
	    
	    if (is_array($result)) {
	        $keyLen = strlen($key);
	        $keyFound = false;
	        $emptyIndex = 0;
	        for ($i=2; $i<6; $i++) {
	            if (substr($result[0]->{'MPF' . $i}, 0, $keyLen) == $key) {
	                $keyFound = true;
	                $result[0]->{'MPF' . $i} = $newMPF;
	                break;
	            }
	            if (empty($result[0]->{'MPF' . $i})) {
	                $emptyIndex = $i;
	            }
	        }
	        
	        if (!$keyFound) {
	            if ($emptyIndex == 0) {
	                $emptyIndex = 3;
	            }
	            $result[0]->{'MPF' . $emptyIndex} = $newMPF;
	        }
	        $sql = "UPDATE tb_mpf SET MPF1='$callid', MPF2=UNIX_TIMESTAMP(), MPF3='".$result[0]->MPF3."', MPF4='".$result[0]->MPF4."', MPF5='".$result[0]->MPF5."', MPF6='$MPF6'";
	    } else {
	        $sql = "INSERT INTO tb_mpf SET MPF1='$callid', MPF2=UNIX_TIMESTAMP(), MPF3='".$newMPF."', MPF4='', MPF5='', MPF6='$MPF6'";
	    }
	    
	    return $this->getDB()->query($sql);;
	}
	
	function svaeDisposition($last_callid, $record_id, $disposition, $note, $status, $agentid, $msg, $served_account='')
	{
		$time = time();
		$resp = false;
		
		//$sql = "SELECT callid FROM skill_crm_disposition_log WHERE callid='$last_callid'";
		//$result = $this->getDB()->query($sql);
		
		//if (is_array($result)) {
		
		$callid_a = explode('-', $last_callid);
        $last_callid = $callid_a[0];
                
		$sql = "UPDATE skill_crm_disposition_log SET disposition_id='$disposition', tstamp=UNIX_TIMESTAMP(), agent_id='$agentid', ".
			"note='$note', served_account='{$served_account}' WHERE callid='$last_callid'";// AND agent_id='$agentid'";
                //, status='$status'
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

	function saveCallMeBack($dial_time='',$agent_id='',$skill_id,$number,$disposition)
    {
        $response = FALSE;
        $schedule_time_update = FALSE;

        $query = "SELECT COUNT(*) AS call_back_number,id FROM leads WHERE skill_id='{$skill_id}' AND (number_1 = '{$number}' || number_2 = '{$number}' || number_3 = '{$number}') ";
        $exist_call_back_number = $this->getDB()->query($query);
        if ($exist_call_back_number[0]->call_back_number > 0)
        {
            $query = "SELECT COUNT(*) AS exist_schedule_dial FROM schedule_dial WHERE id='{$exist_call_back_number[0]->id}' ";
            $result = $this->getDB()->query($query);
            if ($result[0]->exist_schedule_dial > 0)
            {
                $query = "UPDATE schedule_dial SET dial_time='{$dial_time}' WHERE id='{$exist_call_back_number[0]->id}'";
                $schedule_time_update = $this->getDB()->query($query) ? TRUE : FALSE;
            }
            else
            {
                $query = "INSERT INTO schedule_dial(id,dial_time) VALUES ('{$exist_call_back_number[0]->id}','{$dial_time}')";
                $schedule_time_update = $this->getDB()->query($query) ? TRUE : FALSE;
            }

            $query = "UPDATE leads SET agent_id='{$agent_id}', dial_status='T', retry_count='0' WHERE id='{$exist_call_back_number[0]->id}' ";
            $response = $this->getDB()->query($query) ? TRUE : FALSE;
        }

        return $response || $schedule_time_update;
	}

    private function random_digits($num_digits)
    {
        if ($num_digits <= 0) {
            return '';
        }
        return mt_rand(1, 9) . $this->random_digits($num_digits - 1);
    }

    private function generateTicketID($length=7) {
        $id = '';
        $max_try = 50;
        $i = 0;
        while ($i<=$max_try)
        {
            $id = $this->random_digits(10);
            $sql = "SELECT ticket_id FROM t_ticket_info WHERE ticket_id='$id'";
            $result = $this->getDB()->query($sql);
            if (empty($result)) break;
            $i++;
        }

        return !empty($id) ? $id : FALSE;
    }

	function openCrmTicket($skill_id,$number,$disposition,$ticket_body,$last_name,$account_id,$category_id, $status = "O", $source = "C") {
        $response = FALSE;
        $agent_id = UserAuth::getUserID();


        $query = "SELECT COUNT(*) AS crm_ticket FROM t_ticket_info WHERE skill_id='{$skill_id}' AND disposition_id = '{$disposition}' AND status='{$status}' AND created_by='{$agent_id}' AND account_id='{$account_id}' ";
        $crm_ticket_counter = $this->getDB()->query($query);
        $crm_ticket_counter = $crm_ticket_counter[0]->crm_ticket;
        $ticket_id = $this->generateTicketID(10);

        if ($crm_ticket_counter > 0 || !$ticket_id)
        {
            return $response;
        }

        $current_time = time();

        $query = "INSERT INTO t_ticket_info SET ticket_id='{$ticket_id}', created_by='{$agent_id}', created_for='{$number}',   account_id='{$account_id}', `name`='{$last_name}', status_updated_by='$agent_id', skill_id='{$skill_id}',  ";
        $query .= "  category_id='{$category_id}', disposition_id='{$disposition}', status='{$status}', num_tickets='1', last_update_time='{$current_time}', create_time='{$current_time}', source ='{$source}' ";

        //START
        if ($this->getDB()->query($query)){
            $this->saveTicketMessage($ticket_id,$ticket_body,$skill_id,$number,$current_time,$agent_id,$status);
            $this->addToAuditLog('Manual Ticket Create', 'A', "ticket_id", "$ticket_id");

            $sql = "INSERT INTO t_ticket_activity SET ticket_id='$ticket_id', agent_id='$agent_id', activity='M', ".
                "activity_details='001', activity_time='$current_time'";
            $this->getDB()->query($sql);
            if (!empty($disposition)) {
                $sql = "INSERT INTO t_ticket_activity SET ticket_id='$ticket_id', agent_id='$agent_id', activity='D', ".
                    "activity_details='$disposition', activity_time='$current_time'";
                $this->getDB()->query($sql);
            }

            return true;
        }
        return false;
        //END  Update by Rif
    }

    public function saveTicketMessage($ticket_id,$ticket_body,$skill_id,$number,$current_time,$agent_id,$status = "O"){
        $msgBody = base64_encode(nl2br($ticket_body));
        $sql = "SELECT skill_name FROM skill WHERE skill_id = '$skill_id'";
        $result = $this->getDB()->query($sql);
        $skill_name = count($result > 0) ? $result[0]->skill_name : "";

        $sql = "INSERT INTO ticket_messages SET ".
            "ticket_id='$ticket_id', ".
            "ticket_sl='001', ".

            "from_name='$skill_name', ".
            "ticket_to='$number', ".

            "ticket_body='$msgBody', ".
            "has_attachment='N', ".
            "agent_id='$agent_id', ".
            "status='$status', ".
            "tstamp='".$current_time."' ";
        return $this->getDB()->query($sql);
    }

	public function deleteCallMeBack($id,$skill_id,$number,$position)
    {
        if (empty($id) || empty($skill_id) || empty($number) || empty($position))
        {
            return FALSE;
        }
        $sql = "UPDATE leads SET number_{$position}='' WHERE id='{$id}' LIMIT 1";
        $response = $this->getDB()->query($sql);
        $sql = "SELECT number_1,number_2,number_3 FROM leads WHERE id='{$id}' LIMIT 1";
        $lead = $this->getDB()->query($sql);
        if (empty($lead[0]->number_1) && empty($lead[0]->number_2) && empty($lead[0]->number_3))
        {
            $query = "DELETE FROM leads WHERE id='{$id}' LIMIT 1";
            $sql = "DELETE FROM schedule_dial WHERE id='{$id}' LIMIT 1";
            if ($this->getDB()->query($query))
            {
                $this->getDB()->query($sql);
                return TRUE;
            }
        }

        return $response;
    }

    public function getAutoDialNumbers($number)
    {
        $crm_in = new MCrmIn();
        $query = "SELECT id FROM leads WHERE number_1='{$number}' || number_2='{$number}' || number_3='{$number}' LIMIT 1";
        $response = $crm_in->getDB()->query($query);
        if ($response[0]->id)
        {
           $query = "SELECT id,skill_id, number_1,number_2,number_3 FROM leads WHERE id = '{$response[0]->id}'";
           return $crm_in->getDB()->query($query);
        }

        return [];
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
	
	function numCrmSkillRecords($account_id='', $first_name='', $last_name='', $mobile_no='', $home_phone='', $emailAddr='', $office_phone='', $status='')
	{
	    $cond = '';
	    $sql = "SELECT COUNT(record_id) AS numrows FROM skill_crm ";
	    if (!empty($account_id)) $cond .= "account_id LIKE '$account_id%'";
	    if (!empty($first_name)) {
	        $cond = $this->getAndCondition($cond, "first_name LIKE '$first_name%'");
	    }
	    if (!empty($last_name)) {
	        $cond = $this->getAndCondition($cond, "last_name LIKE '$last_name%'");
	    }
	    if (!empty($mobile_no)) {
	        $cond = $this->getAndCondition($cond, "mobile_phone LIKE '%$mobile_no%'");
	    }
        if (!empty($home_phone)) {
            $cond = $this->getAndCondition($cond, "home_phone LIKE '%$home_phone%'");
        }
        if (!empty($emailAddr)) {
            $cond = $this->getAndCondition($cond, "email LIKE '%$emailAddr%'");
        }
        if (!empty($office_phone)) {
            $cond = $this->getAndCondition($cond, "office_phone LIKE '%$office_phone%'");
        }
        if (!empty($status)) {
            $cond = $this->getAndCondition($cond, "status='$status'");
        }

	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $result = $this->getDB()->query($sql);
	
	    if($this->getDB()->getNumRows() == 1) {
	        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
	    }
	
	    return 0;
	}
	
	function getCrmSkillRecords($account_id='', $first_name='', $last_name='', $mobile_no='', $home_phone='', $emailAddr='', $office_phone='', $status='', $offset=0, $limit=0)
	{
	    $cond = '';
	    $sql = "SELECT * FROM skill_crm ";
	    if (!empty($account_id)) $cond .= "account_id LIKE '$account_id%'";
	    if (!empty($first_name)) {
	        $cond = $this->getAndCondition($cond, "first_name LIKE '$first_name%'");
	    }
	    if (!empty($last_name)) {
	        $cond = $this->getAndCondition($cond, "last_name LIKE '$last_name%'");
	    }
	    if (!empty($mobile_no)) {
	        $cond = $this->getAndCondition($cond, "mobile_phone LIKE '%$mobile_no%'");
	    }
        if (!empty($home_phone)) {
            $cond = $this->getAndCondition($cond, "home_phone LIKE '%$home_phone%'");
        }
        if (!empty($emailAddr)) {
            $cond = $this->getAndCondition($cond, "email LIKE '%$emailAddr%'");
        }
        if (!empty($office_phone)) {
            $cond = $this->getAndCondition($cond, "office_phone LIKE '%$office_phone%'");
        }
        if (!empty($status)) {
            $cond = $this->getAndCondition($cond, "status='$status'");
        }
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $sql .= "ORDER BY last_callid DESC ";
	    if ($limit > 0) $sql .= "LIMIT $offset, $limit";
	    //echo $sql;
	
	    return $this->getDB()->query($sql);
	}

    function downloadCrmInRecordFile($file_name, $account_id='', $first_name='', $last_name='', $mobile_no='', $home_phone='', $emailAddr='', $office_phone='', $status='')
    {
        $cond = "";
        $sql = "SELECT account_id, IF(first_name!='', REPLACE(first_name, ',', ' '), '-') AS first_name, IF(last_name!='', REPLACE(last_name, ',', ' '), '-') AS last_name, IF(DOB!='', DOB, '-') AS DOB, ";
        $sql .= "IF(email!='', REPLACE(email, ',', ' '), '-') AS email, IF(house_no!='', REPLACE(house_no, ',', ' '), '-') AS house_no, IF(street!='', REPLACE(street, ',', ' '), '-') AS street, IF(landmarks!='', REPLACE(landmarks, ',', ' '), '-') AS landmarks, ";
        $sql .= "IF(city!='', REPLACE(city, ',', ' '), '-') AS city, IF(state!='', REPLACE(state, ',', ' '), '-') AS state, IF(zip!='', REPLACE(zip, ',', ' '), '-') AS zip, IF(country!='', REPLACE(country, ',', ' '), '-') AS country, ";
        $sql .= "IF(mobile_phone!='', mobile_phone, '-') AS mobile_phone, IF(home_phone!='', home_phone, '-') AS home_phone, IF(office_phone!='', office_phone, '-') AS office_phone, ";
        $sql .= "IF(other_phone!='', other_phone, '-') AS other_phone, IF(fax!='', REPLACE(fax, ',', ' '), '-') AS fax, IF(priority_label!='', priority_label, '-') AS priority_label, ";
        $sql .= "CASE status WHEN 'A' THEN 'Active' WHEN 'I' THEN 'Inactive' WHEN 'L' THEN 'Locked' ELSE '-' END AS Status FROM skill_crm ";

        if (!empty($account_id)) {
            $cond .= "account_id LIKE '$account_id%'";
        }
        if (!empty($first_name)) {
            $cond = $this->getAndCondition($cond, "first_name LIKE '$first_name%'");
        }
        if (!empty($last_name)) {
            $cond = $this->getAndCondition($cond, "last_name LIKE '$last_name%'");
        }
        if (!empty($mobile_no)) {
            $cond = $this->getAndCondition($cond, "mobile_phone LIKE '%$mobile_no%'");
        }
        if (!empty($home_phone)) {
            $cond = $this->getAndCondition($cond, "home_phone LIKE '%$home_phone%'");
        }
        if (!empty($emailAddr)) {
            $cond = $this->getAndCondition($cond, "email LIKE '%$emailAddr%'");
        }
        if (!empty($office_phone)) {
            $cond = $this->getAndCondition($cond, "office_phone LIKE '%$office_phone%'");
        }
        if (!empty($status)) {
            $cond = $this->getAndCondition($cond, "status='$status'");
        }

        if (!empty($cond)) $sql .= " WHERE $cond ";

        $sql .= "ORDER BY first_name ASC ";
        $sql .= "INTO OUTFILE '$file_name' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";

        if (file_exists($file_name)) {
            unlink($file_name);
        }

        $is_success = $this->getDB()->dumpResult($sql);

        return $is_success;
    }

    function verifyMobileImei($accountId='', $mobileImei='', $agentId=''){
        $sql = "UPDATE user_mobile_imei SET verified_by='$agentId', activation_code='', activation_time=UNIX_TIMESTAMP() WHERE account_id='$accountId' AND mobile_IMEI='$mobileImei' LIMIT 1";
        return $this->getDB()->query($sql);
    }
	
	function deleteSkillCrm($record_id){
	    if (empty($record_id)) return false;
	    
	    $sql = "DELETE FROM skill_crm WHERE record_id='$record_id' LIMIT 1";
	    return $this->getDB()->query($sql);
	}

    function updateSkillCrmLog($callid, $record_id='', $disposition='', $note='', $caller_auth_by='', $agentid='')
    {
        if (empty($callid)) return false;

        $resp = false;
        $callid_a = explode('-', $callid);
        $last_callid = $callid_a[0];

        $sql = "UPDATE skill_crm_disposition_log SET record_id='$record_id', caller_auth_by='$caller_auth_by', ";
        if (!empty($disposition)) $sql .= "disposition_id='$disposition', ";
        if (!empty($agentid)) $sql .= "agent_id='$agentid', ";
        if (!empty($note)) $sql .= "note='$note', ";

        $sql .= "tstamp=UNIX_TIMESTAMP() WHERE callid='$last_callid'";

        if ($this->getDB()->query($sql)) $resp = true;

        return $resp;
    }

    public function getDispositionIdByAlternativeDisposition($template_id='', $alternative_disposition='')
    {

        $sql = "SELECT disposition_id FROM skill_crm_disposition_code WHERE template_id='{$template_id}' AND altr_disposition_id= '{$alternative_disposition}' LIMIT 1";
        $result = $this->getDB()->query($sql);
        return !empty($result[0]->disposition_id) ? $result[0]->disposition_id : NULL;
    }
}

?>