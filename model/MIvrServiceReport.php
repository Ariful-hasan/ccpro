<?php

class MIvrServiceReport extends Model
{
	function __construct() {
		parent::__construct();
	}
	
	function numServiceSummary($dateinfo, $dcode)
	{
		$cond = $this->getServiceSummaryCond($dateinfo, $dcode);
		$sql = "SELECT COUNT(DISTINCT log_date, disposition_code) AS numrows FROM ivr_service_request_log ";
		if (!empty($cond)) $sql .= "WHERE $cond";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getServiceSummary($dateinfo, $dcode, $offset=0, $limit=0)
	{
		$cond = $this->getServiceSummaryCond($dateinfo, $dcode);
		$sql = "SELECT log_date, disposition_code, COUNT(log_date) AS num_services FROM ivr_service_request_log ";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "GROUP BY log_date, disposition_code ORDER BY log_date DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getServiceSummaryCond($dateinfo, $dcode)
	{
		$cond = DateHelper::get_date_condition('log_date', $dateinfo->sdate, $dateinfo->edate);
		//$cond = $datecond->condition;
		if (!empty($dcode)) $cond = $this->getAndCondition($cond, "disposition_code='$dcode'");
		
		return $cond;
	}
	
	function numServiceLog($dateinfo, $dcode, $clid, $alid, $skills=array())
	{
	   // $current_agent = UserAuth::getCurrentUser();
		$cond = $this->getServiceLogCond($dateinfo, $dcode, $clid, $alid);
		
		$sql = "SELECT COUNT(*) AS numrows FROM ivr_service_request_log AS isrl";
        $sql .= " INNER JOIN ivr ON isrl.ivr_id = ivr.ivr_id ";
        $sql .= "  WHERE caller_id != '' ";
		$sql .= !empty($cond) ? " AND $cond " : "";

		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getServiceLog($dateinfo, $dcode, $clid, $alid, $offset=0, $limit=0, $skills=array())
	{
	    //$current_agent = UserAuth::getCurrentUser();
		$cond = $this->getServiceLogCond($dateinfo, $dcode, $clid, $alid);

		$sql = "SELECT isrl.*, ivr.ivr_name FROM ivr_service_request_log AS isrl ";
		$sql .= " INNER JOIN ivr ON isrl.ivr_id = ivr.ivr_id ";
		$sql .= "  WHERE caller_id != '' ";
		$sql .= !empty($cond) ? " AND $cond " : "";
		$sql .= " ORDER BY tstamp DESC ";
		$sql .= $limit > 0 ?  " LIMIT $offset, $limit" : "";

		return $this->getDB()->query($sql);
	}
	
	function getServiceLogCond($dateinfo, $dcode, $clid, $alid)
	{
		$datecond = DateHelper::get_date_attributes('served_time', $dateinfo->sdate, $dateinfo->edate,$dateinfo->stime, $dateinfo->etime);
		$cond = $datecond->condition;
		if (!empty($dcode)) $cond = $this->getAndCondition($cond, "isrl.disposition_code='$dcode'");
		if (!empty($clid)) $cond = $this->getAndCondition($cond, "isrl.caller_id LIKE '%$clid%'");
		if (!empty($alid) &&  $alid != '*') $cond = $this->getAndCondition($cond, "isrl.ivr_id ='$alid'");
		
		return $cond;
	}

	function numSurviceRequest($dateinfo, $dcode, $skills=array())
	{
	    $skill_cond = $this->skillWiseCondition($skills);
		$cond = $this->getServiceRequestCond($dateinfo, $dcode);
		
		$sql = "SELECT COUNT(disposition_code) AS numrows FROM ivr_service_request AS isr ";
		$sql .= "LEFT JOIN skill as skl ON skl.skill_id=isr.skill_id ";
		if (!empty($cond)) $sql .= "WHERE $cond";
		
		if (!empty($skill_cond)){
		    if (!empty($cond)) $sql .= " AND $skill_cond ";
		    else $sql .= "WHERE $skill_cond ";
		}
		
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getServiceRequest($dateinfo, $dcode, $offset, $limit, $skills=array())
	{
	    $skill_cond = $this->skillWiseCondition($skills);
		$cond = $this->getServiceRequestCond($dateinfo, $dcode);
		
		$sql = "SELECT isr.*, skl.skill_name FROM ivr_service_request AS isr ";
		$sql .= "LEFT JOIN skill as skl ON skl.skill_id=isr.skill_id ";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		
		if (!empty($skill_cond)){
		    if (!empty($cond)) $sql .= " AND $skill_cond ";
		    else $sql .= "WHERE $skill_cond ";
		}
		
		$sql .= "ORDER BY tstamp DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function getServiceRequestCond($dateinfo, $dcode)
	{
		$datecond = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate);
		$cond = $datecond->condition;
		if (!empty($dcode)) $cond = $this->getAndCondition($cond, "isr.disposition_code='$dcode'");
		
		return $cond;
	}
	
	function markServices($agentid, $sregs)
	{
		$c = 0;
		
		if (is_array($sregs)) {
			foreach ($sregs as $req) {
				//$sql = "INSERT IGNORE INTO rate SET rate_id='$rid', route_id='$routeid', rate='0'";
				$tstamp = substr($req, 0, 10);
				$cli = substr($req, 10);
				$sql = "INSERT INTO ivr_service_request_log (log_date, tstamp, served_time, agent_id, caller_id, account_id, disposition_code, status, skill_id, ivr_id) ".
					"SELECT CURDATE(), tstamp, UNIX_TIMESTAMP(), '$agentid', caller_id, account_id, disposition_code, 'S', skill_id, ivr_id FROM ivr_service_request WHERE ".
					"tstamp='$tstamp' AND caller_id='$cli' LIMIT 1";
				if ($this->getDB()->query($sql)) {
					$sql = "DELETE FROM ivr_service_request WHERE tstamp='$tstamp' AND caller_id='$cli' LIMIT 1";
					$this->getDB()->query($sql);
					$c++;
				}
			}
		}
		
		if ($c > 0) {
			$audit_text = $c == 1 ? $c . ' request served' : $c . ' requests served';
			$this->addToAuditLog('IVR Service Request', 'U', '', $audit_text);
		}
		
		return $c;
	}
	
	function skillWiseCondition($skills = array()){
	    $skill_cond = '';
	    if (UserAuth::hasRole('supervisor') || UserAuth::hasRole('agent')) {
    	    if (is_array($skills) && count($skills) > 0) {
    	        foreach ($skills as $row) {
    	            if (!empty($skill_cond)) $skill_cond .= ",";
    	            $skill_cond .= "'$row->skill_id'";
    	        }
    	    }
    	    if (!empty($skill_cond)){
    	        $skill_cond = " skl.skill_id IN ($skill_cond) ";
    	    }else {
    	        $skill_cond = " skl.skill_id IN ('') ";
    	    }
	    }
	    
	    return $skill_cond;
	}
	
	function AMAX_get_insurance_name($prefix)
	{
	    $sql = "SELECT company_name FROM ct_insurance_company AS c, ct_policy_prefix p WHERE p.prefix='$prefix' AND c.company_id=p.company_id LIMIT 1";
	    $result = $this->getDB()->query($sql);
	    return is_array($result) ? $result[0]->company_name : $prefix;
	}
	
	function AMAX_get_sales_center_name($zip)
	{
	    $sql = "SELECT name FROM ct_sales_center WHERE zip='$zip' LIMIT 1";
            $result = $this->getDB()->query($sql);
            return is_array($result) ? $result[0]->name : $zip;
	}
}

?>