<?php

class MReport extends Model
{
	var $_cdr_csv_save_path = null;
	
	function __construct() {
		parent::__construct();
	}

	function getCdrCsvSavePath()
	{
		if ($this->_cdr_csv_save_path === null) {
			require('./conf.extras.php');
			$this->_cdr_csv_save_path = $extra->cdr_csv_save_path;
		}
		
		return $this->_cdr_csv_save_path;
	}
	
	function getAutodialCDRSqlPart($type, $option, $stext, $date_attributes)
	{
		$sql = '';
		$cond = '';
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		
		$sql .= 'log_cdr_autodial' . $year . ' AS cdr';
		if (!empty($stext)) {
			if ($option == 'cli') $cond = "call_from LIKE '%$stext%' ";
			else if ($option == 'did') $cond = "dial_number LIKE '%$stext%' ";
			//else if ($option == 'agent') $cond = "(aal.agent_id='$stext' OR aml.agent_id='$stext')";
		}


		if (!empty($date_attributes->condition) && !empty($cond)) {
			$cond = $date_attributes->condition . " AND $cond";
		} else if (!empty($date_attributes->condition)) {
			$cond = $date_attributes->condition;
		}

		if (!empty($cond)) $sql .= " WHERE $cond";

		return $sql;
	}

	function getCDRSqlPart($type, $option, $stext, $date_attributes)
	{
		$sql = '';
		$cond = '';
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		
		if($type=='outbound') {
			/*
			$sql .= 'cdrout_log' . $year . ' AS cdr LEFT JOIN '.
				'agent_outbound_autodial_log' . $year . ' AS aal ON aal.callid=cdr.callid LEFT JOIN '.
				'agent_outbound_manual_log' . $year . ' AS aml ON aml.callid=cdr.callid';
				*/
			$sql .= 'cdrout_log' . $year . ' AS cdr';
			if (!empty($stext)) {
				if ($option == 'cli') $cond = "call_from LIKE '%$stext%' ";
				else if ($option == 'did') $cond = "callto LIKE '%$stext%' ";
				//else if ($option == 'agent') $cond = "(aal.agent_id='$stext' OR aml.agent_id='$stext')";
			}

		} else if ($type=='pabx') {
			$sql .= 'pbx_log' . $year . ' AS cdr';
			if (!empty($stext)) {
				if ($option == 'cli') $cond = "fr_agent='$stext' ";
				else if ($option == 'did') $cond = "to_agent='$stext' ";
			}
		} else {
			$sql .= 'cdrin_log' . $year . ' AS cdr';
			if (!empty($stext)) {
				if ($option == 'cli') $cond = "cli LIKE '$stext%' ";
				else if ($option == 'did') $cond = "did LIKE '$stext%' ";
			}
		}

		if (!empty($date_attributes->condition) && !empty($cond)) {
			$cond = $date_attributes->condition . " AND $cond";
		} else if (!empty($date_attributes->condition)) {
			$cond = $date_attributes->condition;
		}

		if (!empty($cond)) $sql .= " WHERE $cond";

		return $sql;
	}
	
	function getAgentCDRSqlPart($type, $agentid, $callid, $yy, $dateinfo, $callerid = '', $answered = '', $callto = '')
	{
		$sql = '';
		$cond = '';
		$year = '';//$yy == date('y') ? '' : '_' . $yy;

		if ($type=='omanual') {
			$acdr = 'aoml';
			/*
			$sql .= 'agent_outbound_manual_log' . $year . ' AS aoml LEFT JOIN '.
				'cdrout_log' . $year . ' AS cdr ON cdr.callid=aoml.callid';
			*/
			$sql .= 'log_agent_outbound_manual' . $year . ' AS aoml';

		} else if ($type=='oauto') {
			$acdr = 'aoal';
			/*
			$sql .= 'agent_outbound_autodial_log' . $year . ' AS aoal LEFT JOIN '.
				'cdrout_log' . $year . ' AS cdr ON cdr.callid=aoal.callid';
			*/
			$sql .= 'agent_outbound_autodial_log' . $year . ' AS aoal';
		} else {
			$acdr = 'ail';
			/*
			$sql .= 'agent_inbound_log' . $year . ' AS ail LEFT JOIN '.
				'cdrin_log' . $year . ' AS cdr ON cdr.callid=ail.callid';
			*/
			$sql .= 'agent_inbound_log' . $year . ' AS ail LEFT JOIN cdrin_log' . $year . ' AS cdr ON cdr.callid=ail.callid_cti';

		}

		if ($type=='omanual') {
			$sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        	$edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        	$date_attributes = new stdClass;
        	$date_attributes->condition = "$acdr.start_time BETWEEN '".$sdate."' AND '".$edate."' ";
        	$date_attributes->yy = substr(date('Y', $dateinfo->ststamp), 2, 2);
		}else{
			$date_attributes = DateHelper::get_date_attributes("$acdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		}

		if (!empty($agentid)) $cond = "$acdr.agent_id='$agentid'";

		if (!empty($date_attributes->condition) && !empty($cond)) {
			$cond = $date_attributes->condition . " AND $cond";
		} else if (!empty($date_attributes->condition)) {
			$cond = $date_attributes->condition;
		}

		if (!empty($callid)) {
			if (!empty($cond)) $cond .= " AND ";
			if ($type=='omanual'){
				$cond .= "$acdr.callid='$callid'";
			}else {
				$cond .= "$acdr.callid_cti='$callid'";
			}
		}
		if (!empty($callerid)){
			if (!empty($cond)) $cond .= " AND ";
			$cond .= "cdr.cli='$callerid'";
		}
		if (!empty($answered)){
			if (!empty($cond)) $cond .= " AND ";
			if ($type=='omanual'){
				$cond .= "$acdr.is_reached='$answered'";
			}else {
				$cond .= "$acdr.is_answer='$answered'";
			}
		}
		if (!empty($callto)){
			if (!empty($cond)) $cond .= " AND ";
			$cond .= "$acdr.callto='$callto'";
		}

		if (!empty($cond)) $sql .= " WHERE $cond";

		return $sql;
	}

	function getSkillCDRSqlPart($type, $skillid, $cli, $did, $aid, $_callid, $status, $ivr_id, $yy, $dateinfo, $sms_enabled=false)
	{
		$sql = '';
		$cond = '';
		$year = '';//$yy == date('y') ? '' : '_' . $yy;
		
		/*if ($type=='omanual') {*/
		$scdr = 'cl';
		
		if ($type=='outbound') {
			$skill_table = 'log_agent_outbound_manual' . $year . ' AS al';
			//$skill_table = 'skill_log' . $year . ' AS al';
			$cdr_table = 'cdrout_log' . $year;
			$scdr = 'al';
			//$sql .= "$cdr_table AS cl";
		
			//$sql .= " LEFT JOIN $skill_table ON cl.callid=al.callid";
			$sql .= "$skill_table";
			if ($sms_enabled){
				$sql .= " LEFT JOIN inbound_sms_log AS sms ON sms.callid=al.callid ";
			}
			$sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        	$edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        	$date_attributes = new stdClass;
        	$date_attributes->condition = "$scdr.start_time BETWEEN '".$sdate."' AND '".$edate."' ";
        	$date_attributes->yy = substr(date('Y', $dateinfo->ststamp), 2, 2);
			
			// $date_attributes = DateHelper::get_date_attributes("$scdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
			
			if (!empty($skillid)) $cond = "al.skill_id='$skillid'";
			else $cond = "al.skill_id!=''";

			if (!empty($cli)) $cond = $this->getAndCondition($cond, "al.agent_id LIKE '$cli%'");
			if (!empty($did)) $cond = $this->getAndCondition($cond, "al.callto LIKE '%$did%'");
			if (!empty($_callid)) $cond = $this->getAndCondition($cond, "$scdr.callid='$_callid'");

			if (!empty($date_attributes->condition) && !empty($cond)) {
				$cond = $date_attributes->condition . " AND $cond";
			} else if (!empty($date_attributes->condition)) {
				$cond = $date_attributes->condition;
			}
		} else {
			//$sql .= 'skill_log' . $year . ' AS '.$scdr;
			$skill_table = 'skill_log' . $year . ' AS sl';
		
			$cdr_table = 'cdrin_log' . $year;
			$ivr_table = 'ivr_log' . $year;

			/*
			$sql .= "$cdr_table AS cl";
		
			$sql .= " LEFT JOIN $skill_table ON cl.callid=sl.callid_cti LEFT JOIN $ivr_table il ON il.callid_cti=cl.callid";
			if ($sms_enabled){
				$sql .= " LEFT JOIN inbound_sms_log AS sms ON sms.callid=cl.callid ";
			}
			*/
			if ($type=='ivr-inbound') {
				$sql .= "$ivr_table as il";
				$sql .= " LEFT JOIN $cdr_table  AS cl ON cl.callid=il.callid_cti LEFT JOIN $skill_table ON sl.callid=il.callid";
			} else {
				$sql .= $skill_table;
				$sql .= " LEFT JOIN $cdr_table  AS cl ON cl.callid=sl.callid_cti LEFT JOIN $ivr_table il ON il.callid=sl.callid";
			}
			if ($sms_enabled){
				$sql .= " LEFT JOIN inbound_sms_log AS sms ON sms.callid=cl.callid ";
			}
			
			
			$date_attributes = DateHelper::get_date_attributes("$scdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		
			if (!empty($skillid)) $cond = "sl.skill_id='$skillid'";

			if (!empty($cli)) $cond = $this->getAndCondition($cond, "cl.cli LIKE '%$cli%'");
			if (!empty($did)) $cond = $this->getAndCondition($cond, "cl.did LIKE '%$did%'");
			if (!empty($aid)) $cond = $this->getAndCondition($cond, "sl.agent_id='$aid'");
			if (!empty($_callid)) $cond = $this->getAndCondition($cond, "$scdr.callid='$_callid'");
			if (!empty($status)) $cond = $this->getAndCondition($cond, "sl.status='$status'");
			if (!empty($ivr_id)) $cond = $this->getAndCondition($cond, "il.ivr_id='$ivr_id'");

			if (!empty($date_attributes->condition) && !empty($cond)) {
				$cond = $date_attributes->condition . " AND $cond";
			} else if (!empty($date_attributes->condition)) {
				$cond = $date_attributes->condition;
			}
		}
		if (!empty($cond)) $sql .= " WHERE $cond";
//echo $sql;
		return $sql;
	}


	function numDLoadOutboundCDR($dateinfo)
	{
	    // $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, '', '');
	    // Gprint($dateinfo);
	    // Gprint($date_attributes);

	    $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
    	$edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
    	$date_attributes = new stdClass;
    	$date_attributes->condition = "start_time BETWEEN '".$sdate."' AND '".$edate."' ";
    	$date_attributes->yy = substr(date('Y', $dateinfo->ststamp), 2, 2);
	    // Gprint($date_attributes);

	    if (empty($date_attributes->yy)) return 0;
	    $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
	    $table = 'log_agent_outbound_manual' . $year;
	    $cond = $date_attributes->condition;

	    // if (!empty($dateinfo->stime) && !empty($dateinfo->etime)) {
	    //     if ($dateinfo->stime == $dateinfo->etime) {
	    //         $cond .= " AND DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') = '$dateinfo->stime' ";
	    //     } elseif ($dateinfo->stime < $dateinfo->etime) {
     //            $cond .= " AND DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') BETWEEN '$dateinfo->stime' AND '$dateinfo->etime' ";
     //        } else {
     //            $cond .= " AND (DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') BETWEEN '$dateinfo->stime' AND '23:59' OR ".
     //                "DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') BETWEEN '00:00' AND '$dateinfo->etime')";
     //        }
     //    }
	    
	    $sql = "SELECT COUNT(start_time) AS numrows FROM $table ";
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $result = $this->getDB()->query($sql);
		// echo $sql;	die();    

	    if($this->getDB()->getNumRows() == 1) {
	        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
            }
            
            return 0;
	}
	
	function getDLoadOutboundCDR($dateinfo, $offset=0, $rowsPerPage=0)
	{
	    if ($rowsPerPage > 500) {
	        $rowsPerPage = 500;
	    }
	    if ($rowsPerPage == 0) {
	        $rowsPerPage = 20;
	    }
	    
	    // $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, '', '');
	    $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
    	$edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
    	$date_attributes = new stdClass;
    	$date_attributes->condition = "start_time BETWEEN '".$sdate."' AND '".$edate."' ";
    	$date_attributes->yy = substr(date('Y', $dateinfo->ststamp), 2, 2);
        if (empty($date_attributes->yy)) return null;

        $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
        $table = 'log_agent_outbound_manual' . $year;
        $skill_log_table = 'skill_log' . $year;
        $cond = $date_attributes->condition;
        
        $cond = $date_attributes->condition;

    	// if (!empty($dateinfo->stime) && !empty($dateinfo->etime)) {
     //    	if ($dateinfo->stime == $dateinfo->etime) {
     //        		$cond .= " AND DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') = '$dateinfo->stime' ";
     //    	} elseif ($dateinfo->stime < $dateinfo->etime) {
     //        		$cond .= " AND DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') BETWEEN '$dateinfo->stime' AND '$dateinfo->etime' ";
     //    	} else {
     //        		$cond .= " AND (DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') BETWEEN '$dateinfo->stime' AND '23:59' OR ".
     //            		"DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') BETWEEN '00:00' AND '$dateinfo->etime')";
     //    	}
    	// }
        /*
        $having_cond = false;
        
        
        if (!empty($dateinfo->stime) && !empty($dateinfo->etime)) {
            if ($dateinfo->stime == $dateinfo->etime) {
                $cond .= " HAVING hhmm='$dateinfo->stime' ";
                $having_cond = true;
            } elseif ($dateinfo->stime < $dateinfo->etime) {
                $cond .= " HAVING hhmm BETWEEN '$dateinfo->stime' AND '$dateinfo->etime' ";
                $having_cond = true;
            } else {
                $cond .= " HAVING hhmm BETWEEN '$dateinfo->stime' AND '23:59' OR hhmm BETWEEN '00:00' AND '$dateinfo->etime' ";
                $having_cond = true;
            }
        }
        */
        
        $sql = "SELECT start_time, ".
            "callerid, callto, talk_time, service_time FROM $table ";
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY start_time DESC";
        if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
        //print_r($date_attributes);
        // echo '<pre>'.$sql.'</pre>';     die();           
        //echo "$dateinfo->edate, $dateinfo->stime, $sql";
        
        return $this->getDB()->query($sql);
	}
	
	function numDialingCampaigns()
	{
		$sql = "SELECT COUNT(DISTINCT campaign_id) AS numrows FROM campaign_dial_list";
		$result = $this->getDB()->query($sql);
	        if ($this->getDB()->getNumRows() == 1) {
                	return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
            	}

            	return 0;
	}
	
	function getCampaignRecordStatus($offset=0, $rowsPerPage=0)
	{
		//if () return null;
		$sql = "SELECT campaign_id, COUNT(*) AS num_records, SUM(IF(disposition='', 1, 0)) AS num_available, SUM(IF(disposition='100' OR disposition='400', 1, 0)) AS num_progress ".
			"FROM campaign_dial_list GROUP BY campaign_id ORDER BY campaign_id LIMIT $offset, $rowsPerPage";
		return $this->getDB()->query($sql);
	}
	
	function numDLoadCDR($dateinfo)
	{
	    $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, '', '');
	    if (empty($date_attributes->yy)) return 0;
	    $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
	    $table = 'cdrin_log' . $year;
	    $cond = $date_attributes->condition;
	    
	    if (!empty($dateinfo->stime) && !empty($dateinfo->etime)) {
	        if ($dateinfo->stime == $dateinfo->etime) {
	            $cond .= " AND DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') = '$dateinfo->stime' ";
	        } elseif ($dateinfo->stime < $dateinfo->etime) {
                    $cond .= " AND DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') BETWEEN '$dateinfo->stime' AND '$dateinfo->etime' ";
                } else {
                    $cond .= " AND (DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') BETWEEN '$dateinfo->stime' AND '23:59' OR ".
                        "DATE_FORMAT(FROM_UNIXTIME(tstamp), '%H:%i') BETWEEN '00:00' AND '$dateinfo->etime')";
                }
            }
	    
	    $sql = "SELECT COUNT(tstamp) AS numrows FROM $table ";
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	    $result = $this->getDB()->query($sql);
//echo $sql;	    
	    if($this->getDB()->getNumRows() == 1) {
	        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
            }
            
            return 0;
	}
	
	function getDLoadCDR($dateinfo, $offset=0, $rowsPerPage=0)
	{
	        if ($rowsPerPage > 500) {
	            $rowsPerPage = 500;
	        }
	        if ($rowsPerPage == 0) {
	            $rowsPerPage = 20;
	        }
	        
	        $date_attributes = DateHelper::get_date_attributes('c.tstamp', $dateinfo->sdate, $dateinfo->edate, '', '');
                if (empty($date_attributes->yy)) return null;

                $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
                $table = 'cdrin_log' . $year;
                $skill_log_table = 'skill_log' . $year;
                $cond = $date_attributes->condition;
                
                $having_cond = false;
                
                if (!empty($dateinfo->stime) && !empty($dateinfo->etime)) {
                    if ($dateinfo->stime == $dateinfo->etime) {
                        $cond .= " HAVING hhmm='$dateinfo->stime' ";
                        $having_cond = true;
                    } elseif ($dateinfo->stime < $dateinfo->etime) {
                        $cond .= " HAVING hhmm BETWEEN '$dateinfo->stime' AND '$dateinfo->etime' ";
                        $having_cond = true;
                    } else {
                        $cond .= " HAVING hhmm BETWEEN '$dateinfo->stime' AND '23:59' OR hhmm BETWEEN '00:00' AND '$dateinfo->etime' ";
                        $having_cond = true;
                    }
                }
                
                $sql = "SELECT FROM_UNIXTIME(c.tstamp) AS tstamp, FROM_UNIXTIME(c.start_time) AS start_time, FROM_UNIXTIME(c.answer_time) AS answer_time, ".
                    "c.cli, c.did, c.duration, s.service_time";
                if ($having_cond) $sql .= ", DATE_FORMAT(FROM_UNIXTIME(c.tstamp), '%H:%i') AS hhmm";
                $sql .= " FROM $table c LEFT JOIN $skill_log_table s ON s.callid=c.callid ";
                if (!empty($cond)) $sql .= " WHERE $cond ";                
                $sql .= "ORDER BY c.tstamp DESC";
                if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
                //print_r($date_attributes);
                //echo '<pre>'.$sql.'</pre>';                
                //echo "$dateinfo->edate, $dateinfo->stime, $sql";
                
                return $this->getDB()->query($sql);
	}
	
	function numAuditRecords($user='', $dateinfo)
	{
  		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'audit_log' . $year;
		$cond = $date_attributes->condition;
		if (!empty($user)) $cond .= " AND agent_id='$user'";

		$sql = "SELECT COUNT(tstamp) AS numrows FROM $table ";
		if (!empty($cond)) $sql .= "WHERE $cond ";
		//echo $sql;
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getAuditRecords($user='', $dateinfo, $offset=0, $rowsPerPage=0)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'audit_log' . $year;
		$cond = $date_attributes->condition;
		if (!empty($user)) $cond .= " AND agent_id='$user'";

		$sql = "SELECT * FROM $table ";
		if (!empty($cond)) $sql .= "WHERE $cond ";

		$sql .= "ORDER BY tstamp DESC";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";

		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function getLoginRecords($user='', $dateinfo, $offset=0, $rowsPerPage=0)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		
		$endDate = $dateinfo->edate;
		if (empty($endDate)) $endDate = $dateinfo->sdate;
		$curYear = date("Y");
		$startYear = date("Y", strtotime($dateinfo->sdate));
		$endYear = date("Y", strtotime($endDate));
		$whereIn = "";
		$agentIdArr = array();
		if (!empty($startYear)){
			for ($i = $startYear; $i <= $endYear; $i++) {
				//if ($curYear == $i){ $table = 'audit_log'; }
				//else { $table = 'audit_log' . $i; }
				$table = 'audit_log';
				$sTimeStamp = strtotime($i."-01-01 00:00:00");
				$eTimeStamp = strtotime($i."-12-31 23:59:59");
				if ($i == $startYear) $sTimeStamp = strtotime($dateinfo->sdate." 00:00:00");
				if ($i == $endYear) $eTimeStamp = strtotime($endDate." 23:59:59");
				$isTabExist = 1;//$this->getDB()->query("SHOW TABLES LIKE '".$table."'");
				if (!empty($isTabExist)&& count($isTabExist) > 0){
					$cond = " tstamp BETWEEN '".$sTimeStamp."' AND '".$eTimeStamp."'";
					if (!empty($user)) $cond .= " AND agent_id='$user'";
					$sql = "SELECT agent_id FROM $table ";
					$sql .= "WHERE (type='I' OR type='V') AND $cond GROUP BY agent_id";
					//echo $sql."<br>";
					$result = $this->getDB()->query($sql);		
					if (is_array($result) && count($result) > 0){					
						foreach ($result as $agentId) {
							if (!in_array($agentId, $agentIdArr)){
								$agentIdArr[] = $agentId;
								$whereIn .= "'".$agentId->agent_id."', ";
							}							
						}
					}
				}
			}
		}
		if (!empty($whereIn)){
			$whereIn = substr($whereIn, 0, -2);
			$sql = "SELECT agent_id, nick, name, usertype, active FROM agents ";
			$sql .= " WHERE agent_id NOT IN (".$whereIn.")";
			$sql .= " ORDER BY agent_id ASC";
			if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
			//echo $sql."<br>";
			return $this->getDB()->query($sql);
		}else {
			$sql = "SELECT agent_id, nick, name, usertype, active FROM agents ";
			$sql .= " ORDER BY agent_id ASC";
			if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
			//echo $sql."<br>";
			return $this->getDB()->query($sql);
		}
	}

	
	function numVMRecords($status='')
	{
		//$year = $date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		if (empty($status)) $status = 'I';
		$table = 'skill_vm_log';//$status == 'S' ? 'vm_log_' . date("y") : 'vm_log';

		$sql = "SELECT COUNT(callid) AS numrows FROM $table ";
		if ($status == 'I') $sql .= "WHERE tstamp>'0' ";
		//echo $sql;
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getVMRecords($status='', $offset=0, $limit=0)
	{
		if (empty($status)) $status = 'I';
		$table = 'skill_vm_log';//$status == 'S' ? 'vm_log_' . date("y") : 'vm_log';
		$field = $status == 'S' ? ', attended_time' : '';
		$sql = "SELECT tstamp, callid, enter_time, cli, skill_id, record_time$field FROM $table ";
		if ($status == 'I') $sql .= "WHERE tstamp>'0' ";
		//if (!empty($status)) $sql .= "WHERE status='$status'";
		$sql .= "ORDER BY tstamp DESC ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function updateVMStatus($callid, $status)
	{
		$sql = "SELECT * FROM vm_log WHERE callid='$callid' AND tstamp > '0'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			$vmlog = $result[0];
			$yy = '';//'_' . date("y", $vmlog->tstamp);
			$sql = "UPDATE vm_log SET tstamp='0' WHERE callid='$callid'";
			if ($this->getDB()->query($sql)) {
				$sql = "INSERT INTO vm_log$yy SET tstamp='$vmlog->tstamp', callid='$vmlog->callid', enter_time='$vmlog->enter_time', ".
					"cli='$vmlog->cli', skill_id='$vmlog->skill_id', record_time='$vmlog->record_time', attended_time=UNIX_TIMESTAMP()";
				//echo $sql;exit;
				$this->getDB()->query($sql);
				$dt = date("Y-m-d H:i:s", $vmlog->tstamp);
				
				$sql = "SELECT skill_name FROM skill WHERE skill_id='$vmlog->skill_id'";
				$result = $this->getDB()->query($sql);
				
				$skillname = is_array($result) ? $result[0]->skill_name : $vmlog->skill_id;
				
				$this->addToAuditLog('Voice Box', 'U', "Date time=".$dt, "Caller ID=$vmlog->cli;Skill name=$skillname;Duration=$vmlog->record_time;Status=Served");
				
				return true;
			}
		}
		return false;
	}

	function getChannelUseByDay($day, $trunkid='')
	{
		if (empty($trunkid)) {
			$sql = "SELECT shour, MAX(ch_count) AS ch_count FROM ch_count WHERE sdate='$day' GROUP BY shour";
		} else {
			$sql = "SELECT shour, MAX(ch_count) AS ch_count FROM ch_count WHERE sdate='$day' AND trunkid='$trunkid' GROUP BY shour";
		}
		return $this->getDB()->query($sql);
	}

	function getChannelUseByMonth($year, $month, $trunkid='')
	{
		$sql = "SELECT sdate, MAX(ch_count) AS ch_count FROM ch_count WHERE ".
			"'$month'=MONTH(sdate) AND '$year'=YEAR(sdate) ";
		if (!empty($trunkid)) $sql .= "AND trunkid='$trunkid' ";
		$sql .= "GROUP BY sdate";

		return $this->getDB()->query($sql);
	}

	function numAgentTime($dateinfo)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return 0;
		
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'report_agent_time' . $year;
		$cond = $date_attributes->condition;
		$sql = "SELECT COUNT(tstamp) AS numrows FROM $table";
		if (!empty($cond)) $sql .= " WHERE $cond";

		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}

	function getAgentTime($dateinfo, $offset=0, $rowsPerPage=0)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'report_agent_time' . $year;
		$cond = $date_attributes->condition;
		$sql = "SELECT l.*, a.nick FROM $table AS l, agents AS a WHERE $cond AND l.agent_id=a.agent_id ";
		$sql .= "ORDER BY tstamp";//staff_time
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function numAutodialCDRs($type, $option, $stext, $dateinfo)
	{
		//var_dump($type);var_dump($option);var_dump($stext);var_dump($dateinfo);
		$date_attributes = DateHelper::get_date_attributes('cdr.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime, false);
		//var_dump($date_attributes);
		if (empty($type) || empty($date_attributes->yy)) return 0;
		//echo 'deb';
		$sql = "SELECT COUNT(cdr.callid) AS numrows FROM " . $this->getAutodialCDRSqlPart($type, $option, $stext, $date_attributes);
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}
	
	function getAutodialCDRs($type, $option, $stext, $dateinfo, $offset=0, $rowsPerPage=0)
	{
		$date_attributes = DateHelper::get_date_attributes('cdr.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime, false);
		if (empty($type) || empty($date_attributes->yy)) return null;
		$sql = "SELECT cdr.start_time AS start_time, cdr.answer_time AS answer_time, cdr.tstamp AS stop_time, callid, dial_index, dial_count, status, " . 
			"cdr.pdd, cdr.ring_time, cdr.customer_id, call_from, dial_number, disc_cause, disc_party, talk_time, disposition ";
		$sql .= " FROM " . $this->getAutodialCDRSqlPart($type, $option, $stext, $date_attributes) . " ORDER BY cdr.tstamp DESC LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function numCDRs($type, $option, $stext, $dateinfo)
	{
		//var_dump($type);var_dump($option);var_dump($stext);var_dump($dateinfo);
		$date_attributes = DateHelper::get_date_attributes('cdr.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		//var_dump($date_attributes);
		if (empty($type) || empty($date_attributes->yy)) return 0;
		//echo 'deb';
		$sql = "SELECT COUNT(cdr.callid) AS numrows FROM " . $this->getCDRSqlPart($type, $option, $stext, $date_attributes);
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}

	function numAgentCDRs($type, $agentid, $_callid, $dateinfo, $callerid = '', $answered = '', $callto = '')
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		//var_dump($date_attributes);
		if (empty($type) || empty($date_attributes->yy)) return 0;
		//echo 'deb';
		if ($type == 'inbound') {
			$callid = 'ail.callid_cti';
		} else {
			$callid = 'callid';
		}

		$sql = "SELECT COUNT($callid) AS numrows FROM " . $this->getAgentCDRSqlPart($type, $agentid, $_callid, $date_attributes->yy, $dateinfo, $callerid, $answered, $callto);
		$result = $this->getDB()->query($sql);
		// echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}

	function numSkillCDRs($type, $skillid, $cli, $did, $aid, $_callid, $dateinfo, $status, $ivr_id)
	{
		$date_attributes = DateHelper::get_date_attributes('cl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		//var_dump($date_attributes);
		if (empty($type) || empty($date_attributes->yy)) return 0;
		//echo 'deb';
		if ($type == 'outbound') $callid = 'al.callid';
		else $callid = 'cl.callid';
		$sql = "SELECT COUNT($callid) AS numrows FROM " . $this->getSkillCDRSqlPart($type, $skillid, $cli, $did, $aid, $_callid, $status, $ivr_id, $date_attributes->yy, $dateinfo);
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}

	function prepareTelcoCDRFile($type, $option, $stext, $dateinfo, $file_name)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$cond = '';
		$sql = '';
		
		//$file_name = $this->_cdr_csv_save_path . 'cdr_' . $type . '.csv';
		if ($type=='outbound') {

			$table = 'cdrout_log' . $year;
			$sql = "SELECT FROM_UNIXTIME(start_time) AS start_time, FROM_UNIXTIME(answer_time) AS answer_time, ".
				"FROM_UNIXTIME(tstamp) AS stop_time, call_from, callto, SEC_TO_TIME(duration) AS duration, ".
				"SEC_TO_TIME(talk_time) AS talk_time, trunk_name, disc_cause, CASE WHEN disc_party = '1' then 'A-Party' ".
				"WHEN disc_party = '2' then 'B-Party' ELSE '' END FROM $table LEFT JOIN trunk ON ".
				"trunk.trunkid=$table.trunkid";
				
			if (!empty($stext)) {
				if ($option == 'cli') $cond = "call_from LIKE '$stext%' ";
				else if ($option == 'did') $cond = "callto LIKE '$stext%' ";
			}

		} else if ($type=='pabx') {
			exit;
		} else {
			$table = 'cdrin_log' . $year;
			$sql = "SELECT FROM_UNIXTIME(start_time) AS start_time, FROM_UNIXTIME(answer_time) AS answer_time, ".
				"FROM_UNIXTIME(tstamp) AS stop_time, cli, did, SEC_TO_TIME(duration) AS duration, ".
				"SEC_TO_TIME(talk_time) AS talk_time, trunk_name, disc_cause, CASE WHEN disc_party = '1' then 'A-Party' ".
				"WHEN disc_party = '2' then 'B-Party' ELSE '' END FROM $table LEFT JOIN trunk ON ".
				"trunk.trunkid=$table.trunkid";
				
			if (!empty($stext)) {
				if ($option == 'cli') $cond = "cli LIKE '$stext%' ";
				else if ($option == 'did') $cond = "did LIKE '$stext%' ";
			}
		}

		if (!empty($date_attributes->condition) && !empty($cond)) {
			$cond = $date_attributes->condition . " AND $cond";
		} else if (!empty($date_attributes->condition)) {
			$cond = $date_attributes->condition;
		}

		if (!empty($cond)) $sql .= " WHERE $cond";
		
		$sql .= " INTO OUTFILE '$file_name' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";

		if (file_exists($file_name)) {
			//echo $file_name;
			unlink($file_name);
		}
		
		$is_success = $this->getDB()->dumpResult($sql);
		
		return $is_success;
	}

	function prepareAgentCDRFile($type, $agentid, $dateinfo, $file_name, $skill_options)
	{
		// $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
    	$edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
    	$date_attributes = new stdClass;
    	$date_attributes->condition = "start_time BETWEEN '".$sdate."' AND '".$edate."' ";
    	$date_attributes->yy = substr(date('Y', $dateinfo->ststamp), 2, 2);

		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$cond = '';
		$sql = '';
		
		//$file_name = $this->_cdr_csv_save_path . 'cdr_' . $type . '.csv';
		if ($type=='omanual') {

			$table = 'log_agent_outbound_manual' . $year;
			
			$skill_cond = '';
			if (is_array($skill_options)) {
				foreach ($skill_options as $sk_id=>$sk_name) {
					$skill_cond .= "WHEN '$sk_id' THEN '$sk_name' ";
				}
			}
			if (!empty($skill_cond)) {
				$skill_cond = "CASE skill_id $skill_cond END AS skill_name, ";
			}
			
			$sql = "SELECT start_time, $table.agent_id, agents.nick, $skill_cond".
				"callto, IF(is_reached='Y','Yes','No'), SEC_TO_TIME(talk_time) AS talk_time, ".
				"SEC_TO_TIME(service_time) AS service_time, SEC_TO_TIME(hold_time) AS hold_time, ".
				"hold_count, SEC_TO_TIME(acw_time) AS acw_time FROM $table LEFT JOIN agents ON ".
				"agents.agent_id=$table.agent_id ";
				
		} else if ($type=='oauto') {
			exit;
		} else {
			$table = 'agent_inbound_log' . $year;
			$sql = "SELECT FROM_UNIXTIME(tstamp) AS stop_time, $table.agent_id, agents.nick, IF(is_answer='Y','Yes','No'), ".
				"SEC_TO_TIME(ring_time) AS ring_time, SEC_TO_TIME(service_time) AS service_time, ".
				"SEC_TO_TIME(hold_time) AS hold_time, hold_count, SEC_TO_TIME(acw_time) AS acw_time FROM $table LEFT JOIN agents ON ".
				"agents.agent_id=$table.agent_id ";
		}

		if (!empty($agentid)) {
			$cond = "$table.agent_id='$agentid'";
		}

		if (!empty($date_attributes->condition) && !empty($cond)) {
			$cond = $date_attributes->condition . " AND $cond";
		} else if (!empty($date_attributes->condition)) {
			$cond = $date_attributes->condition;
		}

		if (!empty($cond)) $sql .= " WHERE $cond";
			
		$sql .= " INTO OUTFILE '$file_name' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";
		//echo $sql;exit;
		if (file_exists($file_name)) {
			//echo $file_name;
			unlink($file_name);
		}
			
		$is_success = $this->getDB()->dumpResult($sql);

		return $is_success;
	}
		
	function getCDRs($type, $option, $stext, $dateinfo, $offset=0, $rowsPerPage=0)
	{
		$date_attributes = DateHelper::get_date_attributes('cdr.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($type) || empty($date_attributes->yy)) return null;
		$sql = "SELECT cdr.start_time AS start_time, cdr.answer_time AS answer_time, cdr.tstamp AS stop_time, ";
		if ($type=='outbound') {
			/*
				Match columns
			*/
			$sql .= "trunkid, call_from AS cli, cdr.callto AS did, duration, cdr.talk_time, cdr.callid, disc_cause, disc_party";
		} else if ($type=='pabx') {
			$sql .= "fr_agent AS cli, to_agent AS did, duration, talk_time, callid, disc_cause";
		} else {
			$sql .= "trunkid, cli, did, duration, talk_time, callid, disc_cause, disc_party";
		}
		$sql .= " FROM " . $this->getCDRSqlPart($type, $option, $stext, $date_attributes) . " ORDER BY cdr.tstamp DESC LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function getAgentCDRs($type, $agentid, $callid, $dateinfo, $offset=0, $rowsPerPage=0, $callerid = '', $answered = '', $callto = '')
	{

		$sql = "SELECT FROM_UNIXTIME(cdr.start_time) AS start_time, FROM_UNIXTIME(cdr.answer_time) AS answer_time, FROM_UNIXTIME(cdr.tstamp) AS stop_time, ";
		$fields = '';
		$order_by= '';

		if ($type=='omanual') {
			$acdr = 'aoml';
			$fields .= "skill_id, callerid AS cli, callto, is_reached AS is_answer, talk_time";
		} else if ($type=='oauto') {
			$acdr = 'aoal';
			$fields .= "is_reached AS is_answer";
		} else {
			$acdr = 'ail';
			$fields .= "is_answer, ring_time, cli, did, disc_party";
		}

		if ($type=='omanual') {
			$sql = "SELECT $acdr.callid, $acdr.start_time AS start_time, agent_id, service_time AS  duration, hold_time, hold_count, acw_time, $fields";
			$order_by = $acdr.'.start_time';
		}else{
			$sql = "SELECT $acdr.callid, FROM_UNIXTIME($acdr.start_time) AS start_time, agent_id, service_time AS  duration, hold_time, hold_count, acw_time, $fields";
			$order_by = $acdr.'.tstamp';
		}

		$date_attributes = DateHelper::get_date_attributes("$acdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($type) || empty($date_attributes->yy)) return null;

		$sql .= " FROM " . $this->getAgentCDRSqlPart($type, $agentid, $callid, $date_attributes->yy, $dateinfo, $callerid, $answered, $callto) . " ORDER BY $order_by DESC LIMIT $offset, $rowsPerPage";
		// echo $sql;
		return $this->getDB()->query($sql);
	}

	function numAgentCallSourceLog($dateinfo)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return 0;
		
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'report_agent_call_inbound' . $year;
		$cond = $date_attributes->condition;
		$sql = "SELECT COUNT(tstamp) AS numrows FROM $table";
		if (!empty($cond)) $sql .= " WHERE $cond";

		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}

	function getAgentCallSourceLog($dateinfo, $offset=0, $rowsPerPage=0)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'report_agent_call_inbound' . $year;
		$cond = $date_attributes->condition;
		$sql = "SELECT l.* FROM $table AS l WHERE $cond ";
		$sql .= "ORDER BY tstamp";//staff_time
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function numSkillSourceLog($dateinfo)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return 0;
		
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'report_skill_inbound' . $year;
		$cond = $date_attributes->condition;
		$sql = "SELECT COUNT(tstamp) AS numrows FROM $table";
		if (!empty($cond)) $sql .= " WHERE $cond";

		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}

	function getSkillSourceLog($dateinfo, $offset=0, $rowsPerPage=0)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'report_skill_inbound' . $year;
		$cond = $date_attributes->condition;
		$sql = "SELECT l.* FROM $table AS l WHERE $cond ";
		$sql .= "ORDER BY tstamp";//staff_time
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
/*
	function getSkillSourceLog($sdate='', $edate='', $stime='', $etime='')
	{

		$date_attributes = DateHelper::get_date_attributes('s.tstamp', $sdate, $edate, $stime, $etime);
		if (empty($date_attributes->yy)) return null;
		//$_year = $date_attributes->yy;
		$year = $date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
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
			"FROM_UNIXTIME(s.tstamp, '%h') AS hour, ".
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
		$sql .= " FROM " . $table . " AS s LEFT JOIN $agent_table AS a ON a.callid=s.callid WHERE " . $where;
		//echo $sql;
		//echo date("y-m-d H:i:s", 1332028800);
		//echo date("y-m-d H:i:s", 1332097259);

		return $this->getDB()->query($sql);
		//$result = $this->db_manager->select($info);

		//return $result;
	}
	
	*/
	

	function getSkillCDRs($type, $skillid, $cli, $did, $aid, $_callid, $status, $ivr_id, $dateinfo, $offset=0, $rowsPerPage=0, $sms_enabled=false)
	{
		$acdr = 'sl';
		
		$order_by = 'cl.tstamp';
		if ($type == 'outbound') {
		    $order_by = 'al.start_time';
			$sql = "SELECT DISTINCT(al.callid) AS callid, al.callid AS callid_sl, '-' AS trunkid, al.callto, al.agent_id, al.skill_id, ".
				"is_reached, al.start_time AS start_time, al.talk_time, al.callerid, ".
				"al.service_time AS service_time, '-' AS disc_cause, '-' AS disc_party";
			/*
			$sql = "SELECT cl.callid AS callid, cl.trunkid, cl.callto, cl.call_from, skill_id, agent_id, ".
				"status, FROM_UNIXTIME(cl.start_time) AS start_time, cl.duration, ".
				"al.service_time AS service_time, FROM_UNIXTIME(cl.tstamp) AS stop_time, cl.disc_cause";
			*/
		} else {
			/*
			$sql = "SELECT cl.callid AS callid, sl.callid AS callid_sl, il.callid AS callid_ivr, cl.cli, cl.did, FROM_UNIXTIME(il.enter_time) AS ivr_enter_time, time_in_ivr, cl.duration, cl.talk_time, ".
				"il.language AS ivr_language, FROM_UNIXTIME(sl.enter_time) AS skill_enter_time,  sl.skill_id, hold_in_q, sl.status AS skill_status, ".
				"FROM_UNIXTIME(cl.start_time) AS start_time, il.ivr_id, sl.service_time AS service_time, sl.agent_id, ".
				"FROM_UNIXTIME(sl.tstamp) AS stop_time, FROM_UNIXTIME(cl.tstamp) AS cdr_stop_time, sl.alarm, cl.disc_cause, cl.disc_party, trunkid";
			*/
			$sql = "SELECT DISTINCT(cl.callid) AS callid, sl.callid AS callid_sl, il.callid AS callid_ivr, cl.cli, cl.did, ".
				"FROM_UNIXTIME(il.enter_time) AS ivr_enter_time, time_in_ivr, cl.duration, cl.talk_time, ".
				"il.language AS ivr_language, FROM_UNIXTIME(sl.enter_time) AS skill_enter_time,  sl.skill_id, hold_in_q, sl.status AS skill_status, ".
				"FROM_UNIXTIME(cl.start_time) AS start_time, il.ivr_id, sl.service_time AS service_time, sl.agent_id, ".
				"FROM_UNIXTIME(sl.tstamp) AS stop_time, FROM_UNIXTIME(cl.tstamp) AS cdr_stop_time, sl.alarm, cl.disc_cause, cl.disc_party, trunkid";
		}
		if ($sms_enabled){
			$sql .= ", sms.callid AS sms_callid ";
		}
		/*
		//if ($type=='omanual') {
		$acdr = 'sl';
		$fields .= "skill_id, callerid AS cli, callto, is_reached AS is_answer, talk_time";

		} else if ($type=='oauto') {
			$acdr = 'aoal';
			$fields .= "is_reached AS is_answer";
		} else {
			$acdr = 'ail';
			$fields .= "is_answer, ring_time";
		}
		*/
		//$sql = "SELECT callid, FROM_UNIXTIME($acdr.start_time) AS start_time, agent_id, service_time AS  duration, hold_time, hold_count, acw_time, $fields";
		$date_attributes = DateHelper::get_date_attributes("cl.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($type) || empty($date_attributes->yy)) return null;
		
		$sql .= " FROM " . $this->getSkillCDRSqlPart($type, $skillid, $cli, $did, $aid, $_callid, $status, $ivr_id, $date_attributes->yy, $dateinfo, $sms_enabled);
		$sql .= " ORDER BY $order_by DESC LIMIT $offset, $rowsPerPage";
		// echo $sql; die();
		return $this->getDB()->query($sql);
	}
	
	function getSkillCDRsForVoice($type, $skillid, $cli, $did, $aid, $callid, $dateinfo, $offset=0, $rowsPerPage=0)
	{
	        $status = '';
		$sql = "SELECT sl.callid AS callid, sl.tstamp, cl.cli, cl.did, sl.agent_id";
		$date_attributes = DateHelper::get_date_attributes("cl.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($type) || empty($date_attributes->yy)) return null;

		$sql .= " FROM " . $this->getSkillCDRSqlPart($type, $skillid, $cli, $did, $aid, $callid, $status, $date_attributes->yy, $dateinfo) . " ORDER BY cl.tstamp DESC";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function prepareSkillCDRFile($type, $skillid, $cli, $did, $aid, $callid, $dateinfo, $file_name, $ivr_options, $skill_options, $trunk_options)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$cond = '';
		$sql = '';
                $status = '';
		$skill_cond = '';
		if (is_array($skill_options)) {
			foreach ($skill_options as $sk_id=>$sk_name) {
				$skill_cond .= "WHEN '$sk_id' THEN '$sk_name' ";
			}
		}
		if (!empty($skill_cond)) {
			$skill_cond .= "ELSE '-' ";
			$skill_cond = "CASE skill_id $skill_cond END AS skill_name, ";
		}

		$trunk_cond = '';
		if (is_array($trunk_options)) {
			foreach ($trunk_options as $sk_id=>$sk_name) {
				$trunk_cond .= "WHEN '$sk_id' THEN '$sk_name' ";
			}
		}
		if (!empty($trunk_cond)) {
			$trunk_cond .= "ELSE '-' ";
			$trunk_cond = ", CASE trunkid $trunk_cond END AS trunk_name";
		}
		
		//$file_name = $this->_cdr_csv_save_path . 'cdr_' . $type . '.csv';
		if ($type=='outbound') {

			$sql = "SELECT FROM_UNIXTIME(cl.start_time) AS start_time,  FROM_UNIXTIME(cl.tstamp) AS stop_time, COALESCE(al.agent_id, ''), COALESCE(al.callerid, ''), cl.callto, ".
				$skill_cond . "IF(is_reached='Y', 'Answered', 'N/A') AS is_reached, IF(cl.talk_time>0, SEC_TO_TIME(cl.talk_time), '00:00:00') AS talk_time, ".
				"IF(al.service_time>0, SEC_TO_TIME(al.service_time), '00:00:00') AS service_time, cl.disc_cause, CASE WHEN cl.disc_party = '1' then 'A-Party' ".
				"WHEN cl.disc_party = '2' then 'B-Party' ELSE '' END" . $trunk_cond;

/*
			$sql = "SELECT FROM_UNIXTIME(cl.start_time) AS start_time,  FROM_UNIXTIME(cl.tstamp) AS stop_time, cl.call_from, '-', cl.callto, ".
				$skill_cond . "IF(status='A', 'Answered', 'N/A') AS is_reached, IF(al.service_time>0, SEC_TO_TIME(al.service_time), '00:00:00') AS service_time, ".
				"IF(cl.duration>0, SEC_TO_TIME(cl.duration), '00:00:00') AS duration, cl.disc_cause" . $trunk_cond;
*/
		} else {

			//$table = 'agent_inbound_log' . $year;

			$ivr_cond = '';
			if (is_array($ivr_options)) {
				foreach ($ivr_options as $ivr_id=>$ivr_name) {
					$ivr_cond .= "WHEN '$ivr_id' THEN '$ivr_name' ";
				}
			}
			if (!empty($ivr_cond)) {
				$ivr_cond .= "ELSE '-' ";
				$ivr_cond = "CASE ivr_id $ivr_cond END AS ivr_name, ";
			}

			$sql = "SELECT FROM_UNIXTIME(cl.start_time) AS start_time, IF(sl.tstamp != 0, FROM_UNIXTIME(sl.tstamp), FROM_UNIXTIME(cl.tstamp)) AS stop_time, cl.cli, ".
				"cl.did, IF(il.enter_time != 0, FROM_UNIXTIME(il.enter_time), '-') AS ivr_enter_time, " . $ivr_cond . "IF(time_in_ivr>0, ".
				"SEC_TO_TIME(time_in_ivr), '00:00:00') AS time_in_ivr, CASE il.language WHEN 'B' THEN 'BAN' WHEN 'E' THEN 'ENG' ELSE '-' ".
				"END AS ivr_language, IF(sl.enter_time != 0, FROM_UNIXTIME(sl.enter_time), '-') AS skill_enter_time, ".
				$skill_cond . "IF(hold_in_q>0, hold_in_q, 0) AS hold_in_q, IF(sl.status!='', CASE sl.status WHEN 'S' THEN 'Served' WHEN 'A' THEN 'Abandoned' ".
				"ELSE '-' END, IF(skill_id is null, '-', 'Droped')) AS skill_status, IF(cl.talk_time>0, COALESCE(SEC_TO_TIME(sl.service_time),''), '00:00:00') AS service_time, ".
				"IF(sl.agent_id!='', sl.agent_id, '--') AS agent_id, IF(sl.alarm>0, sl.alarm, 0) AS alarm, cl.disc_cause, CASE WHEN cl.disc_party = '1' then 'A-Party' ".
				"WHEN cl.disc_party = '2' then 'B-Party' ELSE '' END".
				$trunk_cond;
		}

		$date_attributes = DateHelper::get_date_attributes("cl.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($type) || empty($date_attributes->yy)) return null;

		$sql .= " FROM " . $this->getSkillCDRSqlPart($type, $skillid, $cli, $did, $aid, $callid, $status, $date_attributes->yy, $dateinfo) . " ORDER BY cl.tstamp DESC";

		$sql .= " INTO OUTFILE '$file_name' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";
		//echo $sql;exit;
		if (file_exists($file_name)) {
			//echo $file_name;
			unlink($file_name);
		}
			
		$is_success = $this->getDB()->dumpResult($sql);

		return $is_success;
	}

	function getSkillLogPerDay($dateinfo,$offset=0,$limit=20)
	{
        $start_date = strtotime($dateinfo->sdate." 00:00:00");
        $end_date = strtotime($dateinfo->edate." 23:59:59");
        $cond = " tstamp BETWEEN {$start_date} AND {$end_date} ";

		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'skill_log' . $year;
		//$cond = $date_attributes->condition;
		$sql = "SELECT ".
			"COUNT(callid) AS num_calls, ".
			"SUM(IF(hold_in_q<=20, 1, 0)) AS num_within_th, ".
			"SUM(IF(hold_in_q<=que.service_level, 1, 0)) AS num_within_sl, ".
			"SUM(IF(hold_in_q<=que.service_level AND status='S', 1, 0)) AS num_ans_within_sl, ".
			"FROM_UNIXTIME(tstamp, '%Y-%m-%d') AS cdate, ".
			"SUM(service_time) AS talktime, ".
			"SUM(IF(ql.status='A', hold_in_q, 0)) AS abdns_time, ".
			"0 AS extn_time, ".	//"SUM(IF(status='E', duration, 0)) AS extn_time, ".
			"0 AS num_extn, ". //"SUM(IF(status='E', 1, 0)) AS num_extn, ".
			"ql.skill_id, ".
			"que.skill_name, ".
			"que.service_level, ".
			"SUM(IF(ql.status='A' AND hold_in_q>20, 1, 0)) AS num_abdns_after_th, ".
			"SUM(IF(ql.status='A', 1, 0)) AS num_abdns, ".
			"SUM(IF(ql.status='S', hold_in_q, 0)) AS ring_sec, ".
			"SUM(IF(ql.status='S', 1, 0)) AS num_ans, ".
			"SUM(IF(hold_in_q<=que.service_level AND ql.status='S', 1, 0)) AS ans_within_sl"
		;
		$sql .= " FROM $table AS ql LEFT JOIN skill AS que ON ql.skill_id=que.skill_id ";
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$sql .= " GROUP BY cdate, skill_id ORDER BY cdate, skill_id ";
		$sql .= $limit > 0 ?  " LIMIT $limit OFFSET $offset " : "";
		return $this->getDB()->query($sql);
	}

	function getSkillLogPerDayTotal($dateinfo)
	{
        $start_date = strtotime($dateinfo->sdate." 00:00:00");
        $end_date = strtotime($dateinfo->edate." 23:59:59");
        $cond = " tstamp BETWEEN {$start_date} AND {$end_date} ";

		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'skill_log' . $year;

		$sql = "SELECT COUNT(callid) AS num_calls, SUM(que.service_level) AS service_level, ".
               "SUM(IF(ql.status='S', 1, 0)) AS num_ans, ".
               "SUM(IF(ql.status='A' AND hold_in_q>20, 1, 0)) AS num_abdns_after_th, ".
			   "SUM(IF(hold_in_q<=que.service_level AND ql.status='S', 1, 0)) AS ans_within_sl";
		$sql .= " FROM $table AS ql LEFT JOIN skill AS que ON ql.skill_id=que.skill_id ";

		$sql .= !empty($cond) ? " WHERE $cond " : "";

		return $this->getDB()->query($sql);
	}

    function numSkillLogPerDay($dateinfo)
    {
        $start_date = strtotime($dateinfo->sdate." 00:00:00");
        $end_date = strtotime($dateinfo->edate." 23:59:59");
        $cond = " tstamp BETWEEN {$start_date} AND {$end_date} ";
        $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        if (empty($date_attributes->yy)) return null;
        $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
        $table = 'skill_log' . $year;
        //$cond = $date_attributes->condition;
        $sql = "SELECT ".
            "COUNT(callid) AS num_calls, ".
            "SUM(IF(hold_in_q<=20, 1, 0)) AS num_within_th, ".
            "SUM(IF(hold_in_q<=que.service_level, 1, 0)) AS num_within_sl, ".
            "SUM(IF(hold_in_q<=que.service_level AND status='S', 1, 0)) AS num_ans_within_sl, ".
            "FROM_UNIXTIME(tstamp, '%Y-%m-%d') AS cdate, ".
            "SUM(service_time) AS talktime, ".
            "SUM(IF(ql.status='A', hold_in_q, 0)) AS abdns_time, ".
            "0 AS extn_time, ".	//"SUM(IF(status='E', duration, 0)) AS extn_time, ".
            "0 AS num_extn, ". //"SUM(IF(status='E', 1, 0)) AS num_extn, ".
            "ql.skill_id, ".
            "que.skill_name, ".
            "que.service_level, ".
            "SUM(IF(ql.status='A' AND hold_in_q>20, 1, 0)) AS num_abdns_after_th, ".
            "SUM(IF(ql.status='A', 1, 0)) AS num_abdns, ".
            "SUM(IF(ql.status='S', hold_in_q, 0)) AS ring_sec, ".
            "SUM(IF(ql.status='S', 1, 0)) AS num_ans, ".
            "SUM(IF(hold_in_q<=que.service_level AND ql.status='S', 1, 0)) AS ans_within_sl ";
        $sql .= " FROM $table AS ql LEFT JOIN skill AS que ON ql.skill_id=que.skill_id ";
        if (!empty($cond)) $sql .= " WHERE $cond ";
        $sql .= " GROUP BY cdate, skill_id ORDER BY cdate, skill_id ";
        $response =  $this->getDB()->query($sql);
        return count($response);
    }


	function getSkillLogByInterval($dateinfo='', $skill='')
	{
	        //echo 'asd';
	        //print_r($dateinfo);
		$date_attributes = DateHelper::get_date_attributes_new('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		//print_r($date_attributes);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'skill_log' . $year;
		$cond = $date_attributes->condition;
		if (!empty($skill)) $cond .= " AND l.skill_id='$skill'";

		$sql = "SELECT ".
		        "FROM_UNIXTIME(tstamp, '%Y-%m-%d') AS day, ".
			"FROM_UNIXTIME(tstamp, '%H') AS hour, ".
			"FLOOR(FROM_UNIXTIME(tstamp, '%i')/30)*30 AS minute, ".
			"COUNT(callid) AS num_calls, ".
			"SUM(IF(hold_in_q<=20, 1, 0)) AS num_within_th, ".
			"SUM(IF(hold_in_q<=q.service_level, 1, 0)) AS num_within_sl, ".
			"FROM_UNIXTIME(tstamp, '%Y-%m-%d') AS cdate, ".
			"SUM(service_time) AS talktime, ".
			"SUM(IF(status='A', hold_in_q, 0)) AS abdns_time, ".
			"0 AS extn_time, ".
			"0 AS num_extn, ".
			"SUM(IF(status='A' AND hold_in_q>20, 1, 0)) AS num_abdns_after_th, ".
			"SUM(IF(status='A', 1, 0)) AS num_abdns, ".
			"SUM(IF(status='S', hold_in_q, 0)) AS ring_sec, ".
			"SUM(IF(status='S', 1, 0)) AS num_ans, ".
			"SUM(IF(hold_in_q<=q.service_level AND status='S', 1, 0)) AS ans_within_sl"
		;
		$sql .= " FROM $table AS l LEFT JOIN skill AS q ON q.skill_id=l.skill_id";
		if (!empty($cond)) $sql .= " WHERE $cond";
		$sql .= " GROUP BY day, hour, minute ORDER BY day, hour, minute";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function getSpectrumLogBySkill($skillid, $dateinfo)
	{
		//if (empty($skillid)) return null;
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'skill_log' . $year;
		$cond = $date_attributes->condition;
		if (!empty($skillid)) $cond .= " AND l.skill_id='$skillid'";

		/*$sql = "SELECT ".
			"FROM_UNIXTIME(tstamp, '%Y-%m-%d') AS cdate, ".
			"SUM(if(hold_in_q<5 AND status='S',1,0)) AS answered_l5, ".
			"SUM(if(hold_in_q>=5 AND hold_in_q<10 AND status='S',1,0)) AS answered_l10, ".
			"SUM(if(hold_in_q>=10 AND hold_in_q<20 AND status='S',1,0)) AS answered_l20, ".
			"SUM(if(hold_in_q>=20 AND hold_in_q<30 AND status='S',1,0)) AS answered_l30, ".
			"SUM(if(hold_in_q>=30 AND hold_in_q<45 AND status='S',1,0)) AS answered_l45, ".
			"SUM(if(hold_in_q>=45 AND hold_in_q<60 AND status='S',1,0)) AS answered_l60, ".
			"SUM(if(hold_in_q>=60 AND status='S',1,0)) AS answered_g60, ".
			"SUM(if(hold_in_q<5 AND status='A',1,0)) AS abandoned_l5, ".
			"SUM(if(hold_in_q>=5 AND hold_in_q<10 AND status='A',1,0)) AS abandoned_l10, ".
			"SUM(if(hold_in_q>=10 AND hold_in_q<20 AND status='A',1,0)) AS abandoned_l20, ".
			"SUM(if(hold_in_q>=20 AND hold_in_q<30 AND status='A',1,0)) AS abandoned_l30, ".
			"SUM(if(hold_in_q>=30 AND hold_in_q<45 AND status='A',1,0)) AS abandoned_l45, ".
			"SUM(if(hold_in_q>=45 AND hold_in_q<60 AND status='A',1,0)) AS abandoned_l60, ".
			"SUM(if(hold_in_q>=60 AND status='A',1,0)) AS abandoned_g60"
		;*/
        $sql = "SELECT ".
            "FROM_UNIXTIME(tstamp, '%Y-%m-%d') AS cdate, ".
            "SUM(if(hold_in_q<5 AND status='S',1,0)) AS answered_l5, ".
            "SUM(if(hold_in_q>=5 AND hold_in_q<=10 AND status='S',1,0)) AS answered_l10, ".
            "SUM(if(hold_in_q>=11 AND hold_in_q<=20 AND status='S',1,0)) AS answered_l20, ".
            "SUM(if(hold_in_q>=21 AND hold_in_q<=30 AND status='S',1,0)) AS answered_l30, ".
            "SUM(if(hold_in_q>=31 AND hold_in_q<=45 AND status='S',1,0)) AS answered_l45, ".
            "SUM(if(hold_in_q>=46 AND hold_in_q<=59 AND status='S',1,0)) AS answered_l60, ".

            "SUM(if(hold_in_q>=60 AND status='S',1,0)) AS answered_g60, ".
            "SUM(if(hold_in_q<5 AND status='A',1,0)) AS abandoned_l5, ".

            "SUM(if(hold_in_q>=5 AND hold_in_q<=10 AND status='A',1,0)) AS abandoned_l10, ".
            "SUM(if(hold_in_q>=11 AND hold_in_q<=20 AND status='A',1,0)) AS abandoned_l20, ".
            "SUM(if(hold_in_q>=21 AND hold_in_q<=30 AND status='A',1,0)) AS abandoned_l30, ".
            "SUM(if(hold_in_q>=31 AND hold_in_q<=45 AND status='A',1,0)) AS abandoned_l45, ".
            "SUM(if(hold_in_q>=46 AND hold_in_q<=59 AND status='A',1,0)) AS abandoned_l60, ".
            "SUM(if(hold_in_q>=60 AND status='A',1,0)) AS abandoned_g60";

		$sql .= " FROM $table AS l";
		if (!empty($cond)) $sql .= " WHERE $cond";
		$sql .= " GROUP BY cdate";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function getServiceDelaySpectrum($skillid, $dateinfo, $status)
	{
		//if (empty($skillid)) return null;
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'skill_log' . $year;
		$cond = $date_attributes->condition;
		if (!empty($skillid)) $cond .= " AND l.skill_id='$skillid'";

		$sql = "SELECT ".
			"skill_id, ".
			"COUNT(callid) AS calls_offered, ".
			"FROM_UNIXTIME(tstamp, '%Y-%m-%d') AS cdate, ".
			"SUM(if(hold_in_q<5 AND status='$status',1,0)) AS a_l5, ".
			"SUM(if(hold_in_q>=5 AND hold_in_q<10 AND status='$status',1,0)) AS a_5to10, ".
			"SUM(if(hold_in_q>=10 AND hold_in_q<20 AND status='$status',1,0)) AS a_10to20, ".
			"SUM(if(hold_in_q>=20 AND hold_in_q<30 AND status='$status',1,0)) AS a_20to30, ".
			"SUM(if(hold_in_q>=30 AND hold_in_q<40 AND status='$status',1,0)) AS a_30to40, ".
			"SUM(if(hold_in_q>=40 AND hold_in_q<50 AND status='$status',1,0)) AS a_40to50, ".
			"SUM(if(hold_in_q>=50 AND hold_in_q<60 AND status='$status',1,0)) AS a_50to60, ".
			"SUM(if(hold_in_q>=60 AND hold_in_q<70 AND status='$status',1,0)) AS a_60to70, ".
			"SUM(if(hold_in_q>=70 AND hold_in_q<80 AND status='$status',1,0)) AS a_70to80, ".
			"SUM(if(hold_in_q>=80 AND hold_in_q<90 AND status='$status',1,0)) AS a_80to90, ".
			"SUM(if(hold_in_q>=90 AND hold_in_q<120 AND status='$status',1,0)) AS a_90to120, ".
			"SUM(if(hold_in_q>=120 AND status='$status',1,0)) AS a_g120"
		;
		$sql .= " FROM $table AS l";
		if (!empty($cond)) $sql .= " WHERE $cond";
		$sql .= " GROUP BY cdate, skill_id";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function getServiceAnsDelaySpectrum($skillid, $dateinfo)
	{
		return $this->getServiceDelaySpectrum($skillid, $dateinfo, 'S');
	}
	
	function getServiceAbdnDelaySpectrum($skillid, $dateinfo)
	{
		return $this->getServiceDelaySpectrum($skillid, $dateinfo, 'A');
	}

	function getAgentInboundLogByCallId($callid='')
	{
		$tstamp = substr($callid, 0, 10);
		$yy = date('y', $tstamp);
		if (strlen($yy) == 2 && $yy != '70') {
			$year = '';//$yy == date('y') ? '' : '_' . $yy;
			$table = 'agent_inbound_log' . $year;
			
			$sql = "SELECT tstamp, start_time, l.agent_id, a.nick, skill_id, is_answer, ".
				"ring_time, service_time, hold_time, hold_count, acw_time FROM $table AS l LEFT JOIN ".
				"agents AS a ON a.agent_id=l.agent_id WHERE l.callid='$callid' ORDER BY tstamp";
			return $this->getDB()->query($sql);
		}
		
		return null;
	}
	
	function getSkillFromCallid($callid, $direction='')
	{
	    $skill_id = '';
	    if ($direction == 'O') {
	        $skill_id = '';
	    } else {
	        $sql = "SELECT skill_id FROM agent_inbound_log WHERE callid='$callid' LIMIT 1";
	        $result = $this->getDB()->query($sql);
	        if (is_array($result)){
	            $skill_id=$result[0]->skill_id;
	        }
	    }
	    $response = new stdClass();
	    $response->qtype = 'V';
	    if (!empty($skill_id)) {
	        $sql = "SELECT skill_id, short_name, qtype FROM skill WHERE skill_id='$skill_id' LIMIT 1";
	        $result = $this->getDB()->query($sql);
	        if (is_array($result)){
	            $response = $result[0];
	        }
	    }
	    return $response;
	}
	
	function numAgentPerfInbound($dateinfo)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return 0;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'agent_inbound_log' . $year;
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
	
	/*
	function getSessionAgentsByDay($date)
	{
	}
	*/
	
	function numAgentLogInbound($dateinfo)
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
	
	function numDIDLogInbound($dateinfo)
	{
	    $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
                if (empty($date_attributes->yy)) return 0;
                $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
                $table = 'cdrin_log' . $year;
                $cond = $date_attributes->condition;
                $sql = "SELECT COUNT(DISTINCT(did)) AS numrows FROM $table";
                if (!empty($cond)) $sql .= " WHERE $cond";

                $result = $this->getDB()->query($sql);
                //echo $sql;
                if ($this->getDB()->getNumRows() == 1) {
                        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
                }
                return 0;
	}
	
	
	function numAgentSessionSummary($dateinfo)
	{
        	$date_attributes = DateHelper::get_date_attributes('stop_tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
                if (empty($date_attributes->yy)) return 0;
                $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
                $table = 'agent_session_summary';// . $year;
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
	
	
	function getAgentPerfInbound($dateinfo, $offset=0, $rowsPerPage=0)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'agent_inbound_log' . $year;
		$cond = $date_attributes->condition;

		$sql = "SELECT ".
				"agent_id, ".
				"SUM(ring_time) AS ring_time, ".
				"SUM(IF(is_answer='Y',ring_time,0)) AS aring_time, ".
				"SUM(service_time) AS talk_time, ".
				"SUM(acw_time) AS acw_time, ".
				"MAX(ring_time) AS longest_ring, ".
				"MAX(service_time) AS longest_call, ".
				"MIN(service_time) AS shortest_call, ".
				"COUNT(callid) AS calls_offered, ".
				"SUM(IF(is_answer='Y',1,0)) AS answered_calls, ".
				"SUM(IF(service_time>0 AND service_time<10,1,0)) AS answered_lt10, ".
				"SUM(IF(service_time>=10 AND service_time<20,1,0)) AS answered_10to20, ".
				"SUM(IF(service_time>=20 AND service_time<30,1,0)) AS answered_20to30, ".
				"SUM(IF(service_time>0 AND service_time<30,1,0)) AS answered_lt30, ".
				"SUM(IF(service_time>=30 AND service_time<60,1,0)) AS answered_30to60, ".
				"SUM(IF(service_time>=60 AND service_time<90,1,0)) AS answered_60to90, ".
				"SUM(IF(service_time>=90 AND service_time<120,1,0)) AS answered_90to120, ".
				"SUM(IF(service_time>=120,1,0)) AS answered_gt120, ".
				"SUM(hold_time) AS hold_time, ".
				"SUM(hold_count) AS hold_count"
		;

		$sql .= " FROM $table";
		if (!empty($cond)) $sql .= " WHERE $cond";
		$sql .= " GROUP BY agent_id ORDER BY agent_id";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function getAgentInboundLog($dateinfo, $offset=0, $rowsPerPage=0, $group_by='agent_id', $order_by='agent_id')
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$cond = $date_attributes->condition;

		$sql_agent = "SELECT DISTINCT(agent_id) AS agent_id FROM agent_session_log$year ";
		if (!empty($cond)) $sql_agent .= " WHERE $cond";
		$sql_agent .= " ORDER BY $order_by";
		if ($rowsPerPage > 0) $sql_agent .= " LIMIT $offset, $rowsPerPage";
		$result = $this->getDB()->query($sql_agent);

		if (is_array($result)) {
			$agents = '';
			foreach ($result as $row) {
				if (!empty($agents)) $agents .= ",";
				$agents .= "'$row->agent_id'";
			}

			if (empty($agents)) return null;
			
			$table = 'agent_inbound_log' . $year;
			//$cond = $date_attributes->condition;

			$sql = "SELECT ".
					"agent_id, ".
					"SUM(service_time) AS talk_time, ".
					"SUM(hold_time) AS hold_time, ".
					"SUM(acw_time) AS acw_time, ".
					"SUM(IF(is_answer!='Y',1,0)) AS alarm, ".
					"SUM(IF(is_answer='Y',ring_time,0)) AS aring_time, ".
					"SUM(IF(is_answer='Y',1,0)) AS answered_calls"
			;

			$sql .= " FROM $table WHERE";
			if (!empty($cond)) $sql .= " $cond AND";
			$sql .= " agent_id IN ($agents)";
			$sql .= " GROUP BY $group_by ORDER BY $order_by";
			//if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
			//echo $sql;
			
			$logs = $this->getDB()->query($sql);
			$data = array();
			
			if (is_array($logs)) {
				foreach ($logs as $log) {
					$data[$log->agent_id] = $log;
				}
			}
			
			$return = array();
			foreach ($result as $row) {
				if (isset($data[$row->agent_id])) {
					$return[] = $data[$row->agent_id];
				} else {
					$null_value = new stdClass();
					$null_value->agent_id = $row->agent_id;
					$null_value->talk_time = 0;
					$null_value->hold_time = 0;
					$null_value->acw_time = 0;
					$null_value->alarm = 0;
					$null_value->aring_time = 0;
					$null_value->answered_calls = 0;
					
					$return[] = $null_value;
				}
			}
			
			return $return;
		}
		
		return null;
	}

	function getDIDInboundLog($dateinfo, $offset=0, $rowsPerPage=0, $group_by='did', $order_by='did')
	{
		$date_attributes = DateHelper::get_date_attributes_new('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$cond = $date_attributes->condition;

                $sql = "SELECT did, COUNT(cli) AS num_calls, SUM(duration) AS call_duration, SUM(IF(duration>0, 1, 0)) AS num_ans FROM cdrin_log WHERE " . $cond . " GROUP BY did ORDER BY $order_by";
                if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
                //echo $sql;
                return $this->getDB()->query($sql);

			$sql .= " FROM $table WHERE";
			if (!empty($cond)) $sql .= " $cond AND";
			$sql .= " agent_id IN ($agents)";
			$sql .= " GROUP BY $group_by ORDER BY $order_by";
			//if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
			//echo $sql;
			
			$logs = $this->getDB()->query($sql);
			$data = array();
			
			if (is_array($logs)) {
				foreach ($logs as $log) {
					$data[$log->agent_id] = $log;
				}
			}
			
			$return = array();
			foreach ($result as $row) {
				if (isset($data[$row->agent_id])) {
					$return[] = $data[$row->agent_id];
				} else {
					$null_value = new stdClass();
					$null_value->agent_id = $row->agent_id;
					$null_value->talk_time = 0;
					$null_value->hold_time = 0;
					$null_value->acw_time = 0;
					$null_value->alarm = 0;
					$null_value->aring_time = 0;
					$null_value->answered_calls = 0;
					
					$return[] = $null_value;
				}
			}
			
			return $return;
		
		
		return null;
	}


	function getAgentSessionSummary($dateinfo, $offset=0, $rowsPerPage=0, $group_by='agent_id', $order_by='agent_id')
	{
		$date_attributes = DateHelper::get_date_attributes_new('stop_tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$cond = $date_attributes->condition;

                $sql = "SELECT agent_id, COUNT(*) num_sessions, SUM(staff_time) staff_time, SUM(ring_in_time) ring_in_time, SUM(calls_in_ans) calls_in_ans, ".
                    "SUM(calls_in_time) calls_in_time, SUM(calls_out_attempt) calls_out_attempt, SUM(calls_out_reached) calls_out_reached, ".
                    "SUM(calls_out_time) calls_out_time, SUM(hold_count) hold_count, SUM(hold_time) hold_time, SUM(aux_11_count) acw_count, ".
                    "SUM(aux_11_time) acw_time, SUM(aux_21_count) bounce_count, SUM(aux_21_time) bounce_time, ".
                    "SUM(aux_12_count) aux_12_count, SUM(aux_12_time) aux_12_time, SUM(aux_13_count) aux_13_count, SUM(aux_13_time) aux_13_time, ".
                    "SUM(aux_14_count) aux_14_count, SUM(aux_14_time) aux_14_time, SUM(aux_15_count) aux_15_count, SUM(aux_15_time) aux_15_time, ".
                    "SUM(aux_16_count) aux_16_count, SUM(aux_16_time) aux_16_time, SUM(aux_17_count) aux_17_count, SUM(aux_17_time) aux_17_time, ".
                    "SUM(aux_18_count) aux_18_count, SUM(aux_18_time) aux_18_time, SUM(aux_19_count) aux_19_count, SUM(aux_19_time) aux_19_time, ".
                    "SUM(aux_20_count) aux_20_count, SUM(aux_20_time) aux_20_time FROM agent_session_summary WHERE " . $cond . " GROUP BY agent_id ORDER BY $order_by";
                if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
                //echo $sql;
                
                return $this->getDB()->query($sql);
                
                $result = $this->getDB()->query($sql);
                
                if (is_array($result)) {
                    foreach ($result as &$row) {
                        $row->calls_offered = $row->calls_in_ans+$row->bounce_count;
                        $row->ans_percent = $row->calls_in_ans+$row->bounce_count > 0 ? sprintf("%02d", $row->calls_in_ans/($row->calls_in_ans+$row->bounce_count) * 100) : '-';
                        $row->avg_talk_time = $row->calls_in_ans > 0 ? round($row->calls_in_time/$row->calls_in_ans) : 0;
                        $row->avg_talk_time = gmdate("H:i:s", $row->avg_talk_time);
                        $row->avg_hold_time = $row->hold_count > 0 ? round($row->hold_time/$row->hold_count) : 0;
                        $row->avg_hold_time = gmdate("H:i:s", $row->avg_hold_time);
                        
                        $total_calls = $row->calls_in_ans+$row->calls_out_reached;
                        
                        $row->avg_acw_time = $row->acw_count > 0 ? round($row->acw_time/$row->acw_count) : 0;
                        $row->avg_acw_time = gmdate("H:i:s", $row->avg_acw_time);
                        
                        $row->total_handling_time = $row->calls_in_time+$row->calls_out_time+$row->acw_time+$row->ring_in_time;
                        $row->avg_handling_time = $total_calls > 0 ? round(($row->total_handling_time)/$total_calls) : 0;
                        $row->avg_handling_time = gmdate("H:i:s", $row->avg_handling_time);
                        
                        $row->total_handling_time = gmdate("H:i:s", $row->total_handling_time);
                        //$row->msg = $row->staff_time . ' ' . $row->calls_in_time . ' ' . $row->calls_out_time;
                        
                        $total_aux_time = $row->aux_12_time+$row->aux_13_time+$row->aux_14_time+$row->aux_15_time+$row->aux_16_time+$row->aux_17_time+$row->aux_18_time+$row->aux_19_time+$row->aux_20_time;
                        $total_active_time = $row->ring_in_time+$row->bounce_time+$row->acw_time+$total_aux_time+$row->calls_in_time+$row->calls_out_time;
                        $row->avail_time = gmdate("H:i:s", $row->staff_time-$total_active_time);
                        $row->staff_time = gmdate("H:i:s", $row->staff_time);
                        $row->bounce_time = gmdate("H:i:s", $row->bounce_time);
                        $row->acw_time = gmdate("H:i:s", $row->acw_time);
                        $row->aux_time = gmdate("H:i:s", $total_aux_time);
                        
                        
                        for ($i=11; $i<=20; $i++) {
                            $row->{'aux_'.$i.'_time'} = gmdate("H:i:s", $row->{'aux_'.$i.'_time'});
                        }
                    }
                }
                
                return $result;
	}

	function getAgentSkillInboundLog($dateinfo, $offset=0, $rowsPerPage=0, $group_by='agent_id', $order_by='agent_id')
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$cond = $date_attributes->condition;
	
		$sql_agent = "SELECT DISTINCT(agent_id) AS agent_id FROM agent_session_log$year ";
		if (!empty($cond)) $sql_agent .= " WHERE $cond";
		$sql_agent .= " ORDER BY $order_by";
		if ($rowsPerPage > 0) $sql_agent .= " LIMIT $offset, $rowsPerPage";
		$result = $this->getDB()->query($sql_agent);
	
		if (is_array($result)) {
			$agents = '';
			foreach ($result as $row) {
				if (!empty($agents)) $agents .= ",";
				$agents .= "'$row->agent_id'";
			}
	
			if (empty($agents)) return null;
				
			$table = 'agent_inbound_log' . $year;
			//$cond = $date_attributes->condition;
	
			$sql = "SELECT ".
					"agent_id, ".
					"skill_id, ".
					"SUM(service_time) AS talk_time, ".
					"SUM(IF(is_answer!='Y',1,0)) AS alarm, ".
					"SUM(IF(is_answer='Y',1,0)) AS answered_calls";

			$sql .= " FROM $table WHERE";
			if (!empty($cond)) $sql .= " $cond AND";
			$sql .= " agent_id IN ($agents)";
			$sql .= " GROUP BY $group_by, skill_id ORDER BY $order_by";
			//echo $sql;
			$logs = $this->getDB()->query($sql);
			$data = array();
			
								
			if (is_array($logs)) {
				foreach ($logs as $log) {
					$data[$log->agent_id][] = $log;
				}
			}
								
			$return = array();
			foreach ($result as $row) {
				if (isset($data[$row->agent_id])) {
					foreach ($data[$row->agent_id] as $log) {
					    $return[] = $log;
					}
				} else {
					$null_value = new stdClass();
					$null_value->agent_id = $row->agent_id;
					$null_value->talk_time = 0;
					$null_value->alarm = 0;
					$null_value->answered_calls = 0;							
					$return[] = $null_value;
				}
			}
								
			return $return;
		}
	
		return null;
	}
	
	function getAgentInboundLogByAgent($agentid, $sdate='')
	{
		//$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		//if (empty($date_attributes->yy)) return null;
		$yy = substr($sdate, 2, 2);
		$year = '';//$yy == date('y') ? '' : '_' . $yy;
		$table = 'agent_inbound_log' . $year;
		$stime = strtotime($sdate . ' 00:00:00');
		$etime = strtotime($sdate . ' 23:59:59');
		$cond = "tstamp BETWEEN $stime AND $etime AND agent_id='$agentid'";

		$sql = "SELECT ".
				"agent_id, ".
				"SUM(service_time) AS talk_time, ".
				"SUM(IF(is_answer!='Y',1,0)) AS alarm, ".
				"SUM(IF(is_answer='Y',1,0)) AS answered_calls"
		;

		$sql .= " FROM $table";
		if (!empty($cond)) $sql .= " WHERE $cond";
		$sql .= " GROUP BY agent_id";
		//if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		$result = $this->getDB()->query($sql);
		
		$log = new stdClass();
		if (is_array($result)) {
			$tmp_result = $result[0];
			$log->answered_calls = $tmp_result->answered_calls;
			$log->talk_time = $tmp_result->talk_time;
			$log->alarm = $tmp_result->alarm;
		} else {
			$log->answered_calls = 0;
			$log->talk_time = 0;
			$log->alarm = 0;
		}
		
		return $log;
	}

	function numAgentPerfOutBoundManual($dateinfo)
	{
		// $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
    	$edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
    	$date_attributes = new stdClass;
    	$date_attributes->condition = "start_time BETWEEN '".$sdate."' AND '".$edate."' ";
    	$date_attributes->yy = substr(date('Y', $dateinfo->ststamp), 2, 2);

		if (empty($date_attributes->yy)) return 0;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'log_agent_outbound_manual' . $year;
		$cond = $date_attributes->condition;
		$sql = "SELECT COUNT(DISTINCT(agent_id)) AS numrows FROM $table";
		if (!empty($cond)) $sql .= " WHERE $cond";

		$result = $this->getDB()->query($sql);
		// echo $sql;
		if ($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}
	
	function getAgentPerfOutBoundManual($dateinfo, $offset=0, $rowsPerPage=0)
	{
		// $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		$sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
    	$edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
    	$date_attributes = new stdClass;
    	$date_attributes->condition = "start_time BETWEEN '".$sdate."' AND '".$edate."' ";
    	$date_attributes->yy = substr(date('Y', $dateinfo->ststamp), 2, 2);

		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'log_agent_outbound_manual' . $year;
		$cond = $date_attributes->condition;
		
		$sql = "SELECT ".
                        "agent_id, ".
				"COUNT(callid) AS attempted_calls, ".
				"SUM(IF(is_reached='Y',1,0)) AS reached_calls, ".
				"SUM(talk_time) AS talk_time, ".
				"SUM(service_time) AS service_time, ".
				"SUM(acw_time) AS acw_time";
				//"SUM(ring_time) AS connection_time, ".
				//"SUM(IF(is_reached='Y',ring_time,0)) AS rconnection_time"
		;
		
		$sql .= " FROM $table";
		if (!empty($cond)) $sql .= " WHERE $cond";
		$sql .= " GROUP BY agent_id ORDER BY agent_id";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}


	function numAgentPerfOutBoundAutoDial($dateinfo)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return 0;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'agent_outbound_autodial_log' . $year;
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
	
	function getAgentPerfOutBoundAutoDial($dateinfo, $offset=0, $rowsPerPage=0)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$table = 'agent_outbound_autodial_log' . $year;
		$cond = $date_attributes->condition;

		$sql = "SELECT ".
				"agent_id, ".
				"COUNT(callid) AS attempted_calls, ".
				"SUM(IF(is_reached='Y',1,0)) AS reached_calls, ".
				"SUM(service_time) AS talk_time, ".
				"SUM(acw_time) AS acw_time, ".
				"0 AS connection_time, ".
				"0 AS rconnection_time"
		;

		$sql .= " FROM $table";
		if (!empty($cond)) $sql .= " WHERE $cond";
		$sql .= " GROUP BY agent_id ORDER BY agent_id";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}

	function numAgentsLoggedIn($dateinfo)
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

	function getAgentsLoggedIn($dateinfo, $offset=0, $rowsPerPage=0)
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
	
	function getAgentSessionDates($agent_id, $stime='', $etime='')
	{
		$_year = substr($stime, 2, 2);
		$table = '';//date("y") == $_year ? '' : '_' . $_year;
		$table = 'agent_session_log' . $table;
		if (empty($etime)) {
			$etime = substr($stime, 0, 10) . " 23:59:59";
		}
		$ststamp = strtotime($stime);
		$etstamp = strtotime($etime);
		
		$sql = "SELECT agent_id, FROM_UNIXTIME(tstamp,'%Y-%m-%d') AS sdate FROM $table WHERE tstamp BETWEEN $ststamp AND $etstamp";
		$sql .= " AND agent_id='$agent_id' GROUP BY sdate ORDER BY sdate";

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
	
	function getAgentNextStatus($agent_id, $stime)
	{
		$_year = substr($stime, 2, 2);
		$table = '';//date("y") == $_year ? '' : '_' . $_year;
		$table = 'agent_session_log' . $table;
		$ststamp = strtotime($stime);
		
		$sql = "SELECT tstamp, agent_id, type, value FROM $table WHERE tstamp > $ststamp";
		$sql .= " AND agent_id='$agent_id' ORDER BY tstamp LIMIT 1";

		return $this->getDB()->query($sql);
	}
	
	function calcAgentSessionInfo($agent_id, $result, $stime='', $etime='')
	{
	        $debug_agent_id = '';
		$agent = new stdClass();
		$agent->staffed_time = 0;
		$agent->pause_time = 0;
		$agent->talk_time = 0;
		$agent->pause_count = 0;
		$agent->pause_count_array = array('11'=>0, '12'=>0, '13'=>0, '14'=>0, '15'=>0, '16'=>0, '17'=>0, '18'=>0, '19'=>0, '21'=>0);
		$agent->calls_count = 0;
		$agent->session_count = 0;
		$agent->acw_time = 0;
		$agent->acw_count = 0;
		$agent->missed_call_count = 0;
		$agent->missed_call_time = 0;
		$agent->session_login = 'Continue';
		$agent->session_logout = 'Continue';
		$session_start = "";
		$pause_start = "";
		$pause_type = "";
		$last_event_type = '';

		$first_session_event = '';
		if (is_array($result)) {
		    $first_session_event = $result[0]->type;
		}
		
		//if ($agent_id==$debug_agent_id) echo "First session event: $first_session_event;\n";
		
		if ($first_session_event != 'I') {
		$last_session_details = $this->getAgentLastStatus($agent_id, $stime);
		if (is_array($last_session_details)) {
			$session_details = $last_session_details[0];
			if ($session_details->tstamp+21600 > time()) { //if last event occurs within 6 hours
			$last_event_type = $session_details->type;
			if ($session_details->type == 'I' || $session_details->type == 'X') {
				$session_start = $stime;
				$agent->session_count++;
				if ($session_details->type == 'X') {
					$pause_start = $stime;
					$pause_type = $session_details->value;
					if ($pause_type == '99') $agent->calls_count++;
					else $agent->pause_count++;
					$agent->pause_count_array[$pause_type]++;
				}
			}
			}
		}
		}
		
		//if ($agent_id==$debug_agent_id) print_r($agent);
		
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
		


		$last_event_occured = 0;
		
		//if ($agent_id==$debug_agent_id) echo "Last session info: $last_event_type;\n";
		
		if (!empty($last_event_type) && $last_event_type != 'O') {
		    $last_event_occured = $stime;
                }
		
		$last_event = '';
		$last_time = '';
		$last_type = '';
		
		foreach($result as $session) {

			$last_event = $session->type;
			$last_time = date("Y-m-d H:i:s", $session->tstamp);
			$session->logdate = $last_time;
			$last_type = $session->value;


			//if ($agent_id==$debug_agent_id) echo "<br />Ag: PauseTime-$agent->pause_time($agent->pause_count), StaffTime-$agent->staffed_time; .... EVNT-$last_event($last_type), Time-$last_time";
			//if ($agent_id==$debug_agent_id) echo "<br />-----------------------";

			if(empty($session_start)) {
				$agent->session_count++;
				if($session->type == 'I') {
					if ($agent->session_count == 1) $agent->session_login = $session->logdate;
					$session_start = $session->logdate;
				} else {
					if (empty($last_event_occured)) continue;
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
				
				$agent->pause_count_array[$session->value]++;
				
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
			
			//if ($agent_id==$debug_agent_id) print_r($agent);
		}
		
		//echo '$last_type='.$last_type;
		//echo '$temp_pause_time='.$temp_pause_time;
		
		if(!empty($session_start)) {
			$agent->staffed_time += $this->diff_in_sec($session_start, $etime);
			//if ($agent_id==$debug_agent_id) echo "session: $session_start - $etime - Hour=".$this->diff_in_sec($session_start, $etime)/3600 ."<br>";
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
		if (isset($agent->p21)) {
		    $agent->missed_call_time = $agent->p21;
		    $agent->missed_call_count = $agent->pause_count_array['21'];
		    
		    $agent->pause_time -= $agent->missed_call_time;
		    $agent->pause_count -= $agent->missed_call_count;
		}

		if (isset($agent->p11)) {
		    $agent->acw_time = $agent->p11;
		    $agent->acw_count = $agent->pause_count_array['11'];
		    
		    $agent->pause_time -= $agent->acw_time;
		    $agent->pause_count -= $agent->acw_count;
		}
		
		//if ($agent_id==$debug_agent_id) print_r($agent);
		return $agent;
	}

	function diff_in_sec($stime, $etime)
	{
		return strtotime($etime) - strtotime($stime) + 1;
	}

	function prepareActivityRptFile($user='', $dateinfo, $file_name)
	{
	    $date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
	    if (empty($date_attributes->yy)) return null;
	    $year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
	    $table = 'audit_log' . $year;
	    $cond = $date_attributes->condition;
	    if (!empty($user)) $cond .= " AND agent_id='$user'";
	     
	    $sql = "SELECT (@cnt := @cnt + 1) AS rowNum, FROM_UNIXTIME(al.tstamp) AS log_time, IF(a.nick IS NULL, al.agent_id, a.nick) AS username, ";
	    $sql .= "CASE al.type WHEN 'A' THEN 'Add' WHEN 'U' THEN 'Update' WHEN 'D' THEN 'Delete' WHEN 'V' THEN 'Visit' ";
	    $sql .= "WHEN 'L' THEN 'Download' WHEN 'I' THEN 'Login' WHEN 'M' THEN 'Misslogin' end AS logtype, ";
	    $sql .= "al.page, al.ip, al.log_text ";
	     
	    $sql .= "FROM $table AS al LEFT JOIN agents AS a ON a.agent_id=al.agent_id CROSS JOIN (SELECT @cnt := 0) AS dummy ";
	    if (!empty($cond)) $sql .= "WHERE $cond ";
	     
	    $sql .= "ORDER BY tstamp DESC";
	    $sql .= " INTO OUTFILE '$file_name' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";
	    if (file_exists($file_name)) {
	        unlink($file_name);
	    }
	
	    $is_success = $this->getDB()->dumpResult($sql);
	
	    return $is_success;
	}


	function numSkillCallReportDaily($dateinfo, $skill='', $language=''){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}
		
		if (!empty($skill)) $cond = $this->getAndCondition($cond, "skill_id='$skill'");
		if (!empty($language)) $cond = $this->getAndCondition($cond, "language='$language'");

		$sql = "SELECT COUNT(DISTINCT skill_id, language, sdate) AS numrows FROM skill_call_summary";
		if (!empty($cond)) $sql .= " WHERE $cond";
		
		//$sql .= " GROUP BY skill_id, language";
		
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if ($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}

	function getSkillCallReportDaily($dateinfo, $skill='', $language='', $offset=0, $rowsPerPage=0){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}
		
		if (!empty($skill)) $cond = $this->getAndCondition($cond, "skill_id='$skill'");
		if (!empty($language)) $cond = $this->getAndCondition($cond, "language='$language'");

		$sql = "SELECT sdate, skill_id, language, SUM(service_duration) service_duration, SUM(hold_time_in_queue) hold_time_in_queue, ".
			"MAX(max_hold_time_in_queue) max_hold_time_in_queue, SUM(calls_offered) calls_offered, SUM(calls_answerd) calls_answerd, ".
			"SUM(answerd_within_service_level) answerd_within_service_level, SUM(calls_abandoned) calls_abandoned, ".
			"SUM(abandon_duration) abandon_duration, SUM(abandoned_after_threshold) abandoned_after_threshold FROM skill_call_summary";
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$sql .= "GROUP BY sdate, skill_id, language ORDER BY sdate, skill_id, language";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		
		return $this->getDB()->query($sql);
	}

	function numSkillCallReportSummary($dateinfo, $skill='', $language=''){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}
		
		if (!empty($skill)) $cond = $this->getAndCondition($cond, "skill_id='$skill'");
		if (!empty($language)) $cond = $this->getAndCondition($cond, "language='$language'");

		$sql = "SELECT COUNT(DISTINCT skill_id, language) AS numrows FROM skill_call_summary";
		if (!empty($cond)) $sql .= " WHERE $cond";
		
		//$sql .= " GROUP BY skill_id, language";
		
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if ($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}

	function getSkillCallReportSummary($dateinfo, $skill='', $language='', $offset=0, $rowsPerPage=0){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}
		
		if (!empty($skill)) $cond = $this->getAndCondition($cond, "skill_id='$skill'");
		if (!empty($language)) $cond = $this->getAndCondition($cond, "language='$language'");

		$sql = "SELECT skill_id, language, SUM(service_duration) service_duration, SUM(hold_time_in_queue) hold_time_in_queue, ".
			"MAX(max_hold_time_in_queue) max_hold_time_in_queue, SUM(calls_offered) calls_offered, SUM(calls_answerd) calls_answerd, ".
			"SUM(answerd_within_service_level) answerd_within_service_level, SUM(calls_abandoned) calls_abandoned, ".
			"SUM(abandon_duration) abandon_duration, SUM(abandoned_after_threshold) abandoned_after_threshold FROM skill_call_summary";
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$sql .= "GROUP BY skill_id, language ORDER BY skill_id, language";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		
		return $this->getDB()->query($sql);
	}
	
	function numSkillCallSummary($dateinfo, $skill='', $language=''){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}
		
		if (!empty($skill)) $cond = $this->getAndCondition($cond, "skill_id='$skill'");
		if (!empty($language)) $cond = $this->getAndCondition($cond, "language='$language'");

		$sql = "SELECT COUNT(sdate) AS numrows FROM skill_call_summary";
		if (!empty($cond)) $sql .= " WHERE $cond";
		
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if ($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}
	
	function getSkillCallSummary($dateinfo, $skill='', $language='', $offset=0, $rowsPerPage=0){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':',sminute) BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}
		
		if (!empty($skill)) $cond = $this->getAndCondition($cond, "skill_id='$skill'");
		if (!empty($language)) $cond = $this->getAndCondition($cond, "language='$language'");

		$sql = "SELECT * FROM skill_call_summary";
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$sql .= "ORDER BY sdate, shour, sminute";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		
		return $this->getDB()->query($sql);
	}
	
	function numDidCallSummary($dateinfo){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':00') BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':00') BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}
	
		$sql = "SELECT COUNT(DISTINCT did) AS numrows FROM did_call_summary";
		if (!empty($cond)) $sql .= " WHERE $cond";
	
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if ($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}
	
	function getDidCallSummary($dateinfo, $offset=0, $rowsPerPage=0){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':00') BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':00') BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}

		$sql = "SELECT did, SUM(calls_count) AS calls_count, SUM(total_duration) AS tduration, MAX(max_duration) AS mxduration FROM did_call_summary";
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$sql .= "GROUP BY did ORDER BY tduration DESC";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
	
		return $this->getDB()->query($sql);
	}
	
	
	function numIvrServiceSummary($dateinfo){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':00') BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':00') BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}
	
		$sql = "SELECT COUNT(DISTINCT disposition_code) AS numrows FROM ivr_service_summary";
		if (!empty($cond)) $sql .= " WHERE $cond";
	
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if ($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		return 0;
	}
	
	function getIvrServiceSummary($dateinfo, $offset=0, $rowsPerPage=0){
		$cond = "";
		if (!empty($dateinfo->sdate) && empty($dateinfo->edate) && !empty($dateinfo->stime) && $dateinfo->stime == '00:00') {
			$cond = "CONCAT(sdate,' ',shour,':00') BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->sdate." 23:59'";
		}elseif (!empty($dateinfo->sdate) && !empty($dateinfo->edate) && !empty($dateinfo->stime) && !empty($dateinfo->etime)){
			$cond = "CONCAT(sdate,' ',shour,':00') BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '".$dateinfo->edate." ".$dateinfo->etime."'";
		}

		$sql = "SELECT s.disposition_code, SUM(service_count) AS service_count, d.service_title FROM ivr_service_summary s LEFT JOIN ivr_service_code d ON d.disposition_code=s.disposition_code";
		if (!empty($cond)) $sql .= " WHERE $cond ";
		$sql .= "GROUP BY s.disposition_code ORDER BY s.disposition_code DESC";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
	
		return $this->getDB()->query($sql);
	}
	
	function downloadSkillCDRFile($file_name, $type, $skillid, $cli, $did, $aid, $_callid, $status, $dateinfo)
	{
		include('lib/DateHelper.php');
		$sql = '';
		$cond = '';
		$year = '';
		$scdr = 'cl';
	
		if ($type=='outbound') {
			$skill_table = 'log_agent_outbound_manual AS al';
			$cdr_table = 'cdrout_log';
			$scdr = 'al';
				
			$sql = "SELECT al.start_time, ";
			$sql .= "IF(al.agent_id!='', al.agent_id, '--') AS Agent_ID, IF(agt.nick is null, '-', agt.nick) AS Nick_name, ";
			$sql .= "al.callerid, al.callto, skl.skill_name AS Skill, ";
			$sql .= "CASE is_reached WHEN 'Y' THEN 'Answered' WHEN 'N' THEN 'N/A' ELSE '-' END AS Status, ";
			$sql .= "IF(al.talk_time != 0, SEC_TO_TIME(al.talk_time), '00:00:00') AS Talk_time, ";
			$sql .= "IF(al.service_time != 0, SEC_TO_TIME(al.service_time), '00:00:00') AS Service_time, ";
			$sql .= "'-' AS disc_cause, '-' AS disc_party, '-' AS trunkid, IF(al.callid!='', al.callid, '-') AS callid ";
			$sql .= "FROM $skill_table ";
			$sql .= "LEFT JOIN skill AS skl ON skl.skill_id=al.skill_id ";
			$sql .= "LEFT JOIN agents AS agt ON agt.agent_id=al.agent_id ";
	
			// $date_attributes = DateHelper::get_date_attributes("$scdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
			$sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
	    	$edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
	    	$date_attributes = new stdClass;
	    	$date_attributes->condition = "$scdr.start_time BETWEEN '".$sdate."' AND '".$edate."' ";
	    	$date_attributes->yy = substr(date('Y', $dateinfo->ststamp), 2, 2);
	
			if (!empty($skillid)) $cond = "al.skill_id='$skillid'";
			else $cond = "al.skill_id!=''";
	
			if (!empty($cli)) $cond = $this->getAndCondition($cond, "al.agent_id LIKE '$cli%'");
			if (!empty($did)) $cond = $this->getAndCondition($cond, "al.callto LIKE '$did%'");
			if (!empty($_callid)) $cond = $this->getAndCondition($cond, "$scdr.callid='$_callid'");
	
			if (!empty($date_attributes->condition) && !empty($cond)) {
				$cond = $date_attributes->condition . " AND $cond";
			} else if (!empty($date_attributes->condition)) {
				$cond = $date_attributes->condition;
			}
			if (!empty($cond)) $sql .= " WHERE $cond ";
	
			$sql .= "ORDER BY al.start_time DESC ";
		} else {
			$skill_table = 'skill_log AS sl';
			$cdr_table = 'cdrin_log AS cl';
			$ivr_table = 'ivr_log AS il';
				
			$sql = "SELECT FROM_UNIXTIME(cl.start_time) AS Start_time, IF(sl.tstamp != 0, FROM_UNIXTIME(sl.tstamp), FROM_UNIXTIME(cl.tstamp)) AS Stop_time, cl.cli AS Caller_ID, cl.did AS DID, IF(il.enter_time != 0, FROM_UNIXTIME(il.enter_time), '-') AS IVR_enter_time, ";
			$sql .= "IF(ivr.ivr_name is null, '-', ivr.ivr_name) AS IVR, IF(time_in_ivr != 0, FROM_UNIXTIME(time_in_ivr), '00:00:00') AS Time_in_IVR, CASE il.language WHEN 'BN' THEN 'BAN' WHEN 'EN' THEN 'ENG' ELSE '-' END AS ivr_language, ";
			$sql .= "IF(sl.enter_time != 0, FROM_UNIXTIME(sl.enter_time), '-') AS Skill_enter_time, IF(skl.skill_name is null, '-', skl.skill_name) AS Skill, IF(hold_in_q != 0, SEC_TO_TIME(hold_in_q), '00:00:00') AS Hold_in_queue, ";
			$sql .= "IF(sl.agent_id!='', sl.agent_id, '--') AS Agent_ID, IF(agt.nick is null, '-', agt.nick) AS Nick_name, IF(sl.status!='', CASE sl.status WHEN 'S' THEN 'Served' WHEN 'A' THEN 'Abandoned' ELSE '-' END, IF(sl.skill_id is null, '-', 'Droped')) AS Status, ";
			$sql .= "IF(cl.talk_time>0, COALESCE(SEC_TO_TIME(sl.service_time),''), '00:00:00') AS Service_time, SEC_TO_TIME(cl.duration) AS Total_time, ";
			$sql .= "IF(sl.alarm>0, sl.alarm, 0) AS Missed_call, CASE WHEN cl.disc_party = '1' then 'A-Party' WHEN cl.disc_party = '2' then 'B-Party' ELSE cl.disc_party END AS Disc_party, IF(sl.callid!='', sl.callid, '-') AS Call_ID ";
			$sql .= "FROM $skill_table ";
			$sql .= "LEFT JOIN $cdr_table ON cl.callid=sl.callid_cti ";
			$sql .= "LEFT JOIN $ivr_table ON il.callid=sl.callid ";
			$sql .= "LEFT JOIN ivr ON ivr.ivr_id=il.ivr_id ";
			$sql .= "LEFT JOIN skill AS skl ON skl.skill_id=sl.skill_id ";
			$sql .= "LEFT JOIN agents AS agt ON agt.agent_id=sl.agent_id ";
	
			$date_attributes = DateHelper::get_date_attributes("$scdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
	
			if (!empty($skillid)) $cond = "sl.skill_id='$skillid'";
			if (!empty($cli)) $cond = $this->getAndCondition($cond, "cl.cli LIKE '$cli%'");
			if (!empty($did)) $cond = $this->getAndCondition($cond, "cl.did LIKE '$did%'");
			if (!empty($aid)) $cond = $this->getAndCondition($cond, "sl.agent_id='$aid'");
			if (!empty($_callid)) $cond = $this->getAndCondition($cond, "$scdr.callid='$_callid'");
			if (!empty($status)) $cond = $this->getAndCondition($cond, "sl.status='$status'");
	
			if (!empty($date_attributes->condition) && !empty($cond)) {
				$cond = $date_attributes->condition . " AND $cond";
			} else if (!empty($date_attributes->condition)) {
				$cond = $date_attributes->condition;
			}
				
			if (!empty($cond)) $sql .= " WHERE $cond ";
				
			$sql .= "ORDER BY cl.tstamp DESC ";
		}

		$sql .= " INTO OUTFILE '$file_name' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'";

		if (file_exists($file_name)) {
			unlink($file_name);
		}
						
		$is_success = $this->getDB()->dumpResult($sql);

		return $is_success;
	}

	function numIvrTraceLog($dateinfo){
		$table = 'ivr_log';
        $group_by = 'GROUP BY callid';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table} LEFT JOIN log_ivr_trace ON log_ivr_trace.callid_cti = ivr_log.callid_cti";
        $sdate_time = $dateinfo->sdate;
        $sdate_time .= !empty($dateinfo->stime) ? ' '.$dateinfo->stime.':00' : '00:00';
        $edate_time = $dateinfo->edate;
        $edate_time .= !empty($dateinfo->etime) ? ' '.$dateinfo->stime.':59' : '23:59';        

        $sql .= " WHERE DATE_FORMAT(FROM_UNIXTIME(tstamp), '%Y-%m-%d %H:%i') BETWEEN '{$sdate_time}' AND '{$edate_time}' ";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        // Gprint($record[0]->total_record);
        return (empty($record[0]->total_record)) ? 0 : $record[0]->total_record;
	}
	function getIvrTraceLog($dateinfo, $offset=0, $rowsPerPage=20)
    {
        $table = 'ivr_log';
        $group_by = 'GROUP BY callid ';

        $sql = "SELECT *, ";
        $sql .= "GROUP_CONCAT(DISTINCT log_ivr_trace.branch ORDER BY log_ivr_trace.branch ASC SEPARATOR '|') as traversal, ";
        $sql .= "GROUP_CONCAT(DISTINCT log_ivr_trace.enter_time ORDER BY log_ivr_trace.enter_time ASC SEPARATOR '|' ) as traversal_enter_time, ";
        $sql .= "GROUP_CONCAT(DISTINCT log_ivr_trace.time_in_ivr ORDER BY log_ivr_trace.time_in_ivr ASC SEPARATOR '|') as traversal_sec, ";
        $sql .= "SUM(log_ivr_trace.time_in_ivr) as sum_time_in_ivr ";
        $sql .= "FROM ivr_log ";
        $sql .= "LEFT JOIN log_ivr_trace ON log_ivr_trace.callid_cti = ivr_log.callid_cti ";
        $sdate_time = $dateinfo->sdate;
        $sdate_time .= !empty($dateinfo->stime) ? ' '.$dateinfo->stime.':00' : '00:00';
        $edate_time = $dateinfo->edate;
        $edate_time .= !empty($dateinfo->etime) ? ' '.$dateinfo->stime.':59' : '23:59';        

        $sql .= "WHERE DATE_FORMAT(FROM_UNIXTIME(tstamp), '%Y-%m-%d %H:%i') BETWEEN '{$sdate_time}' AND '{$edate_time}' ";

        $sql .= $group_by;
        $sql .= ($rowsPerPage > 0) ? "ORDER BY tstamp ASC LIMIT $rowsPerPage OFFSET $offset " : "";
        // Gprint($sql);

        return $this->getDB()->query($sql);
    }
    function numIvrTraceSummary($dateinfo, $trace_id){
		$table = 'log_ivr_trace';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table} ";
        $sql .= "LEFT JOIN ivr_log ON ivr_log.callid_cti = log_ivr_trace.callid_cti";
        $sdate_time = $dateinfo->sdate;
        // $sdate_time .= !empty($dateinfo->stime) ? ' '.$dateinfo->stime.':00' : '00:00';
        $edate_time = $dateinfo->edate;
        // $edate_time .= !empty($dateinfo->etime) ? ' '.$dateinfo->stime.':59' : '23:59';        

        $sql .= " WHERE log_ivr_trace.enter_time BETWEEN '{$sdate_time}' AND '{$edate_time}' ";
        $sql .= (!empty($trace_id) && $trace_id != '*') ? "AND log_ivr_trace.trace_id = '{$trace_id}' " : '';
        $sql .= 'GROUP BY log_ivr_trace.trace_id, log_ivr_trace.callid_cti ';
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        // Gprint($record);

        return (empty($record)) ? 0 : count($record);
	}
	function getIvrTraceSummary($dateinfo, $trace_id, $offset=0, $rowsPerPage=20)
    {
        $sql = "SELECT log_ivr_trace.trace_id, log_ivr_trace.callid_cti, log_ivr_trace.enter_time, ivr_log.callid, ";
        $sql .= "GROUP_CONCAT(DISTINCT log_ivr_trace.branch ORDER BY log_ivr_trace.branch ASC SEPARATOR '|') as traversal, ";
        $sql .= "GROUP_CONCAT(DISTINCT log_ivr_trace.enter_time ORDER BY log_ivr_trace.enter_time ASC SEPARATOR '|' ) as traversal_enter_time, ";
        $sql .= "GROUP_CONCAT(DISTINCT log_ivr_trace.time_in_ivr ORDER BY log_ivr_trace.time_in_ivr ASC SEPARATOR '|') as traversal_sec, ";
        $sql .= "SUM(log_ivr_trace.time_in_ivr) as sum_time_in_ivr ";
        $sql .= "FROM log_ivr_trace ";
        $sql .= "LEFT JOIN ivr_log ON ivr_log.callid_cti = log_ivr_trace.callid_cti ";
        $sdate_time = $dateinfo->sdate;
        // $sdate_time .= !empty($dateinfo->stime) ? ' '.$dateinfo->stime.':00' : '00:00';
        $edate_time = $dateinfo->edate;
        // $edate_time .= !empty($dateinfo->etime) ? ' '.$dateinfo->stime.':59' : '23:59';        

        $sql .= "WHERE log_ivr_trace.enter_time BETWEEN '{$sdate_time}' AND '{$edate_time}' ";
        $sql .= (!empty($trace_id) && $trace_id != '*') ? "AND log_ivr_trace.trace_id = '{$trace_id}' " : '';
        $sql .= 'GROUP BY log_ivr_trace.trace_id, log_ivr_trace.callid_cti ';
        $sql .= ($rowsPerPage > 0) ? "ORDER BY log_ivr_trace.enter_time ASC LIMIT $rowsPerPage OFFSET $offset " : "";
        // Gprint($sql);

        return $this->getDB()->query($sql);
    }

    function numIvrCallSummary($dateinfo,$ivr_ids, $report_type = REPORT_HOURLY) {
        $table = 'rt_ivr_call_summary';
        $group_by = '';
        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";

         if($report_type == REPORT_YEARLY){
             $sql = "SELECT YEAR(sdate) as syear, COUNT(*) AS total_record FROM {$table}";
             $group_by = " GROUP BY syear, ivr_id ";
         }elseif($report_type == REPORT_QUARTERLY ){
             $sql = "SELECT QUARTER(sdate) as quarter_no, COUNT(*) AS total_record FROM {$table}";
             $group_by = " GROUP BY quarter_no , ivr_id";
         }elseif($report_type == REPORT_MONTHLY ){
             $sql = "SELECT MONTHNAME(sdate) as smonth, COUNT(*) AS total_record FROM {$table}";
             $group_by = " GROUP BY smonth, ivr_id";
         }elseif($report_type == REPORT_DAILY ){
             $group_by = " GROUP BY sdate, ivr_id ";
         }elseif($report_type == REPORT_HOURLY ){
             $group_by = " GROUP BY sdate, shour, ivr_id ";
         }elseif($report_type == REPORT_HALF_HOURLY){
             $group_by = " GROUP BY sdate, shour ";
         }

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($ivr_ids) && $ivr_ids!='*') ? " AND ivr_id IN('{$ivr_ids}') " : "";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
//        Gprint($sql);die;
//        if(empty($report_type) || $report_type == REPORT_HOURLY)
//            return $record[0]->total_record;
        return (!empty($record)) ? count($record) : 0;
//        return $record[0]->total_record;
    }

	function getCallSummary($dateinfo, $ivr_ids, $report_type = REPORT_HOURLY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE)
    {
        $table = 'rt_ivr_call_summary';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";
        if ($report_type == REPORT_HOURLY ){
            $sql = "SELECT sdate, shour, calls_count, duration, ivr_id ";
        } else {
            $sql = "SELECT sdate,  SUM(calls_count) AS calls_count, duration, ivr_id ";
        }

         if($report_type == REPORT_YEARLY){
             $selected_report_type = ", YEAR(sdate) as syear ";
             $group_by = " GROUP BY syear, ivr_id ";
             $order_by = " ORDER BY syear ASC ";
             $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
         }elseif($report_type == REPORT_QUARTERLY ){
             $selected_report_type = ", QUARTER(sdate) as quarter_no ";
             $group_by = " GROUP BY quarter_no, ivr_id ";
             $order_by = " ORDER BY quarter_no ASC ";
             $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
         }elseif($report_type == REPORT_MONTHLY ){
             $selected_report_type = ", MONTHNAME(sdate) as smonth ";
             $group_by = " GROUP BY smonth, ivr_id ";
             $order_by = " ORDER BY smonth ASC ";
             $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
         }elseif($report_type == REPORT_DAILY ){
             $group_by = " GROUP BY sdate,ivr_id ";
             $order_by = " ORDER BY sdate ASC ";
             $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
         }elseif($report_type == REPORT_HOURLY ) {
             $group_by = " GROUP BY sdate, shour, ivr_id";
             $order_by = " ORDER BY sdate ASC, shour ASC, ivr_id ASC ";
             $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
         }
        // elseif($report_type == REPORT_HALF_HOURLY ){
        //     $group_by = " GROUP BY sdate, shour, sminute ";
        //     $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC ";
        //     $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        // }else{
        //     // $sql = "SELECT * FROM $table";
        //     $group_by = " GROUP BY sdate, shour, sminute ";
        //     $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC ";
        //     $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        // }


        if(!empty($selected_report_type))
            $sql .= $selected_report_type;
        $sql .= "FROM {$table} ";

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $sql .= (!empty($ivr_ids) && $ivr_ids!='*') ? " AND ivr_id IN('{$ivr_ids}') " : "";

        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
//         echo $sql;die;

        return $this->getDB()->query($sql);
    }

    function getAgentShiftSummary($aid, $date=null) {
        $response = array();
	    $table = "rt_agent_shift_summary";
	    $select = "rtass.*, sp.label AS shift_name";
	    $sql = "SELECT  $select  FROM  $table  AS rtass ";
	    $sql .= empty($date) ? " LEFT JOIN agents AS agn ON agn.session_id = rtass.session_id " : "";
	    $sql .=" LEFT JOIN shift_profile AS sp ON sp.shift_code = rtass.shift_code ";
	    $sql .=" WHERE ";
	    $sql .= empty($date) ? " agn.agent_id = '{$aid}'" : "";
	    $sql .= !empty($date) ? " agent_id='{$aid}' AND rtass.sdate='{$date}' " : "";
//	    dd($sql);
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            $response = $result[0];
        }
        return $response;
    }

    function getAgentCurrentSessionInfo($aid){
        $response = array();
        $table = "rt_agent_shift_summary";
        $select = "session_id";
        $sql = "SELECT $select FROM $table WHERE agent_id='{$aid}'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            $response = $result[0];
        }
        return $response;
    }

    function getIceFeedBackByAgentId($dateInfo=null, $agentId) {
	    if (empty($agentId) || empty($dateInfo) || $dateInfo->sdate > $dateInfo->edate)
	        return null;

        $response = [];
	    $sql = "SELECT SUM(IF (ice_feedback='Y', 1, 0)) AS positive_ice, SUM(IF (ice_feedback='N', 1, 0)) AS negative_ice ";
	    $sql .= " FROM log_skill_inbound ";
	    $sql .= " WHERE call_start_time BETWEEN '{$dateInfo->sdate}' AND '{$dateInfo->edate}' ";
	    $sql .= " AND agent_id='{$agentId}' ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)){
            $response = $result[0];
        }
        return $response;
    }
}

?>