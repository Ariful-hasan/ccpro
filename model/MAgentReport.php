<?php

class MAgentReport extends Model
{
	var $_condition;
	
	function __construct() {
		parent::__construct();
		$this->_condition = '';
	}

	function getDayReportForAgent($agent_id, $dateinfo)
	{
		$date_attributes = DateHelper::get_date_attributes('tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (empty($date_attributes->yy)) return null;
		$year = '';//$date_attributes->yy == date('y') ? '' : '_' . $date_attributes->yy;
		$cond = $date_attributes->condition;

		$table = 'agent_inbound_log' . $year;
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
		$sql .= " agent_id='$agent_id'";
		$sql .= " GROUP BY agent_id";
		//if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		$logs = $this->getDB()->query($sql);
		
		if (is_array($logs)) {
			return $logs[0];
		} else {
			$null_value = new stdClass();
			$null_value->agent_id = $agent_id;
			$null_value->talk_time = 0;
			$null_value->hold_time = 0;
			$null_value->acw_time = 0;
			$null_value->alarm = 0;
			$null_value->aring_time = 0;
			$null_value->answered_calls = 0;
			
			return $null_value;
		}
		return null;
	}
	
	function numSkillCDRs($type, $skills, $cli, $did, $aid, $dateinfo)
	{
		$date_attributes = DateHelper::get_date_attributes('cl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		//var_dump($date_attributes);
		if (empty($type) || empty($date_attributes->yy)) return 0;
		//echo 'deb';
		$this->_condition = '';
		$callid_count = 'cl.callid';
		if ($type == 'outbound') {
			$callid_count = 'cl.callid';
		}
		$sql = "SELECT COUNT($callid_count) AS numrows FROM " . $this->getSkillCDRSqlPart($type, $skills, $cli, $did, $aid, $date_attributes->yy, $dateinfo);
//        echo $sql;die;
        $result = $this->getDB()->query($sql);
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}
		
		return 0;
	}
	
	function getSkillCDRs($type, $skills, $cli, $did, $aid, $dateinfo, $offset=0, $rowsPerPage=0, $sms_enabled=false){

		$acdr = 'sl';
		
		if ($type == 'outbound') {

			$sql = "SELECT cl.callid AS callid, '-' AS trunkid, cl.callto, cl.skill_id, cl.agent_id, ".
				"is_reached, FROM_UNIXTIME(cl.start_time) AS start_time, cl.talk_time, cl.callerid, ".
				"cl.service_time AS service_time, FROM_UNIXTIME(cl.tstamp) AS stop_time, '-' AS disc_cause";
			/*
			$sql = "SELECT cl.callid AS callid, cl.trunkid, cl.callto, cl.call_from, skill_id, agent_id, ".
				"status, FROM_UNIXTIME(cl.start_time) AS start_time, cl.duration, ".
				"al.service_time AS service_time, FROM_UNIXTIME(cl.tstamp) AS stop_time, cl.disc_cause";
			*/
		} else {
			$sql = "SELECT sl.callid AS callid, cl.cli, cl.did, FROM_UNIXTIME(il.enter_time) AS ivr_enter_time, time_in_ivr, ".
				"il.language AS ivr_language, FROM_UNIXTIME(sl.enter_time) AS skill_enter_time,  sl.skill_id, hold_in_q, sl.status AS skill_status, ".
				"FROM_UNIXTIME(cl.start_time) AS start_time, il.ivr_id, sl.service_time AS service_time, sl.agent_id, ".
				"FROM_UNIXTIME(sl.tstamp) AS stop_time, FROM_UNIXTIME(cl.tstamp) AS cdr_stop_time, sl.alarm, cl.disc_cause, trunkid";
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

		$sql .= " FROM " . $this->getSkillCDRSqlPart($type, $skills, $cli, $did, $aid, $date_attributes->yy, $dateinfo, false, $sms_enabled) . " ORDER BY cl.tstamp DESC LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getSkillCDRSqlPart($type, $skills, $cli, $did, $aid, $yy, $dateinfo, $is_disposition=false, $sms_enabled=false){
		//if (!empty($this->_condition)) return $this->_condition;
		
		$sql = '';
		$cond = '';
		$skill_cond = '';
		$year = '';//$yy == date('y') ? '' : '_' . $yy;
		
		/*if ($type=='omanual') {*/
		$scdr = 'cl';
		
		if (is_array($skills)) {
			foreach ($skills as $row) {
				if (!empty($skill_cond)) $skill_cond .= ",";
				$skill_cond .= "'$row->skill_id'";
			}
		}
		
		if (empty($skill_cond)) $skill_cond = "''";
		
		if ($type=='outbound') {
			//$scdr = 'al';
			$skill_table = 'log_agent_outbound_manual' . $year . ' AS cl';
			//$skill_table = 'skill_log' . $year . ' AS al';
			$cdr_table = 'cdrout_log' . $year;

			$sql .= "$skill_table";
            if ($sms_enabled){
                $sql .= " LEFT JOIN inbound_sms_log AS sms ON sms.callid=cl.callid ";
            }
		
			//$sql .= " LEFT JOIN $skill_table ON cl.callid=al.callid";
			$sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        	$edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        	$date_attributes = new stdClass;
        	$date_attributes->condition = "$scdr.start_time BETWEEN '".$sdate."' AND '".$edate."' ";
        	$date_attributes->yy = substr(date('Y', $dateinfo->ststamp), 2, 2);
			// $date_attributes = DateHelper::get_date_attributes("$scdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		
			//$cond = "((al.skill_id IN ($skill_cond) AND ) OR al.agent_id='$aid')";
			$cond = "cl.agent_id='$aid'";

			//if (!empty($cli)) $cond = $this->getAndCondition($cond, "");
            if (!empty($cli)) $cond = $this->getAndCondition($cond, "cl.callto LIKE '$cli%'");
			if (!empty($did)) $cond = $this->getAndCondition($cond, "callto LIKE '$did%'");

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
			$disp_log_table = 'skill_crm_disposition_log' . $year;

			$sql .= "$skill_table";

			$sql .= " LEFT JOIN $cdr_table cl ON cl.callid=sl.callid_cti LEFT JOIN $ivr_table il ON il.callid_cti=cl.callid";
            if ($sms_enabled){
                $sql .= " LEFT JOIN inbound_sms_log AS sms ON sms.callid=sl.callid ";
            }
			if ($is_disposition) $sql .= " LEFT JOIN $disp_log_table dl ON dl.callid=sl.callid";

			$date_attributes = DateHelper::get_date_attributes("$scdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		
			//$cond = "((sl.skill_id IN ($skill_cond) AND sl.status!='S') OR sl.agent_id='$aid')";
			$cond = "sl.agent_id='$aid'";

			if (!empty($cli)) $cond = $this->getAndCondition($cond, "cl.cli LIKE '$cli%'");
			if (!empty($did)) $cond = $this->getAndCondition($cond, "cl.did LIKE '$did%'");
			//if (!empty($aid)) $cond = $this->getAndCondition($cond, "");

			if (!empty($date_attributes->condition) && !empty($cond)) {
				$cond = $date_attributes->condition . " AND $cond";
			} else if (!empty($date_attributes->condition)) {
				$cond = $date_attributes->condition;
			}
		}
		if (!empty($cond)) $sql .= " WHERE $cond";

		$this->_condition = $sql;
		return $sql;
	}
	
	function listenVMFile($cid, $ts, $vm, $agentid, $vm_path, $isSkill)
	{
		if (!empty($vm)) {
                        //echo $vm->status . '<';exit;
                        if ($vm->status != 'S') {
                                $this->updateVoiceLogStatus($cid, $ts, 'R', $agentid, $isSkill);
                        }
                        $this->addToAuditLog('Voice Mail', "V", "", "Listen voice file");
                        $yyyy = date("Y", $ts);
                        $yyyy_mm_dd = date("Y_m_d", $ts);
                        $sound_file = $vm_path . "VM/$yyyy/$yyyy_mm_dd/" . $cid . ".wav";
                        if (!file_exists($sound_file)) {
                                $sound_file = $vm_path . "VM/$yyyy/$yyyy_mm_dd/" . $cid . ".mp3";
                                if (!file_exists($sound_file)) {
                                        $sound_file = '';
                                }
                        }
                        if (!empty($sound_file)) {
                                $fp = fopen($sound_file, "rb");
                                header("Content-type: application/octet-stream");
                                header('Content-disposition: attachment; filename="vm-voice-'.$ts.'.mp3"');
                                header("Content-transfer-encoding: binary");
                                header("Content-length: ".filesize($sound_file)."    ");
                                fpassthru($fp);
                                fclose($fp);
                        	
				return true;
			}
        	}
		return false;
	}
	
	function getVMLogSqlPart($aid, $skills, $cli, $did, $agent_id, $yy, $dateinfo, $status='')
        {
                if (!empty($this->_condition)) return $this->_condition;

                $sql = '';
                $cond = '';
                $skill_cond = '';
                $year = '';//$yy == date('y') ? '' : '_' . $yy;

                $scdr = 'cl';

                if (!UserAuth::hasRole('admin')) {
                        if (is_array($skills)) {
                                foreach ($skills as $row) {
                                        if (!empty($skill_cond)) $skill_cond .= ",";
                                        $skill_cond .= "'$row->skill_id'";
                                }
                        }

                        if (empty($skill_cond)) $skill_cond = "''";
                        $cond = "(cl.skill_id IN ($skill_cond) OR cl.agent_id='$aid')";
                }
                
		$skill_table = 'skill_vm_log' . $year . ' AS cl';
                
		$sql .= "$skill_table";
                $date_attributes = DateHelper::get_date_attributes("$scdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);

                //$cond = "(cl.skill_id IN ($skill_cond) OR cl.agent_id='$aid')";

                if (strlen($cli)>=3) $cond = $this->getAndCondition($cond, "cl.cli LIKE '%$cli%'");
                //if (strlen($did)>=3) $cond = $this->getAndCondition($cond, "cl.did LIKE '%$did%'");
                if (!empty($agent_id)) $cond = $this->getAndCondition($cond, "cl.agent_id='$agent_id'");
                //if (!empty($aid)) $cond = $this->getAndCondition($cond, "");
		if (!empty($status)) {
			if ($status == 'N') {
				$cond = $this->getAndCondition($cond, "cl.status IN ('N', 'R')");
			} else {
				$cond = $this->getAndCondition($cond, "cl.status='$status'");
			}
		}


                if (!empty($date_attributes->condition) && !empty($cond)) {
                        $cond = $date_attributes->condition . " AND $cond";
                } else if (!empty($date_attributes->condition)) {
                        $cond = $date_attributes->condition;
                }
                
		if (!empty($cond)) $sql .= " WHERE $cond";

                $this->_condition = $sql;
                return $sql;
        }

	function numVMLogs($aid, $skills, $cli, $did, $agent_id, $dateinfo, $status)
        {
                $date_attributes = DateHelper::get_date_attributes('cl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
                //var_dump($date_attributes);
                if (empty($date_attributes->yy)) return 0;
                //echo 'deb';
                $this->_condition = '';
                $callid_count = 'cl.callid';
                $sql = "SELECT COUNT($callid_count) AS numrows FROM " . $this->getVMLogSqlPart($aid, $skills, $cli, $did, $agent_id, $date_attributes->yy, $dateinfo, $status);
                $result = $this->getDB()->query($sql);
                //echo $sql;
                if($this->getDB()->getNumRows() == 1) {
                        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
                }

                return 0;
        }

	function getVMLogs($aid, $skills, $cli, $did, $agent_id, $dateinfo, $status, $offset=0, $rowsPerPage=0)
        {

                $acdr = 'cl';

                $sql = "SELECT callid, tstamp, FROM_UNIXTIME(tstamp) AS stop_time, cli, agent_id, status, ".
                                "skill_id, duration, FROM_UNIXTIME(cl.served_tstamp) AS served_time";
                $date_attributes = DateHelper::get_date_attributes("cl.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
                if (empty($date_attributes->yy)) return null;

                $sql .= " FROM " . $this->getVMLogSqlPart($aid, $skills, $cli, $did, $agent_id, $date_attributes->yy, $dateinfo, $statys) . " ORDER BY cl.tstamp DESC LIMIT $offset, $rowsPerPage";
                //echo $sql;
                return $this->getDB()->query($sql);
        }

	function getAgentVMLogSqlPart($aid, $cli, $did, $yy, $dateinfo, $status='')
        {
                if (!empty($this->_condition)) return $this->_condition;

                $sql = '';
                $cond = '';
                $skill_cond = '';
                $year = '';//$yy == date('y') ? '' : '_' . $yy;

                $scdr = 'cl';

		$skill_table = 'vm_log' . $year . ' AS cl';
                
		$sql .= "$skill_table";
                $date_attributes = DateHelper::get_date_attributes("$scdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);

                if (strlen($cli)>=3) $cond = $this->getAndCondition($cond, "cl.cli LIKE '%$cli%'");
		if (!empty($status)) {
			if ($status == 'N') {
				$cond = $this->getAndCondition($cond, "cl.status IN ('N', 'R')");
			} else {
				$cond = $this->getAndCondition($cond, "cl.status='$status'");
			}
		}


                if (!empty($date_attributes->condition) && !empty($cond)) {
                        $cond = $date_attributes->condition . " AND $cond";
                } else if (!empty($date_attributes->condition)) {
                        $cond = $date_attributes->condition;
                }
                
                if (ctype_digit($aid) && strlen($aid) <= 4) $cond = "mail_box_id='$aid' AND " . $cond;
                else $cond = "mail_box_id='' AND " . $cond;
                
		if (!empty($cond)) $sql .= " WHERE $cond";

                $this->_condition = $sql;
                return $sql;
        }


	function numAgentVMLogs($aid, $cli, $did, $dateinfo, $status)
        {
                $date_attributes = DateHelper::get_date_attributes('cl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
                //var_dump($date_attributes);
                if (empty($date_attributes->yy)) return 0;
                //echo 'deb';
                $this->_condition = '';
                $callid_count = 'cl.callid';
                $sql = "SELECT COUNT($callid_count) AS numrows FROM " . $this->getAgentVMLogSqlPart($aid, $cli, $did, $date_attributes->yy, $dateinfo, $status);
                $result = $this->getDB()->query($sql);
                //echo $sql;
                if($this->getDB()->getNumRows() == 1) {
                        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
                }

                return 0;
        }

	function getAgentVMLogs($aid, $cli, $did, $dateinfo, $status, $offset=0, $rowsPerPage=0)
        {

                $acdr = 'cl';

                $sql = "SELECT callid, tstamp, FROM_UNIXTIME(tstamp) AS stop_time, cli, mail_box_id AS agent_id, status, ".
                                "duration";
                $date_attributes = DateHelper::get_date_attributes("cl.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
                if (empty($date_attributes->yy)) return null;

                $sql .= " FROM " . $this->getAgentVMLogSqlPart($aid, $cli, $did, $date_attributes->yy, $dateinfo, $statys) . " ORDER BY cl.tstamp DESC LIMIT $offset, $rowsPerPage";
                //echo $sql;
                return $this->getDB()->query($sql);
        }
        
        function updateVoiceLogStatus($cid, $tstamp, $status, $agentid, $isSkill=true)
        {
                if ($status == 'R' || $status == 'S') {
                        $ts = time();
                        if ($isSkill) {
                                $sql = "UPDATE skill_vm_log SET status='$status', served_tstamp='$ts', agent_id='$agentid' WHERE callid='$cid' AND tstamp='$tstamp' AND ".
                                        "status IN ('N', 'R')";
                        } else {
                                $sql = "UPDATE vm_log SET status='$status' WHERE callid='$cid' AND tstamp='$tstamp' AND status IN ('N', 'R')";
                        }
                        return $this->getDB()->query($sql);
                }
                
                return false;
        }
        
        function getVoiceLogByCallID($cid, $tstamp='', $isSkill=true)
        {
                $table = $isSkill ? 'skill_vm_log' : 'vm_log';
                $sql = "SELECT * FROM $table WHERE callid='$cid' ";
                if (!empty($tstamp)) {
                        $sql .= "AND tstamp='$tstamp' ";
                }
                $sql .= "LIMIT 1";
                //echo $sql;
                $result = $this->getDB()->query($sql);
                if (is_array($result)) {
                        return $result[0];
                }
                return null;
        }


        //////  Updated from numSkillCDRs  ///////
    function numSkillCDRsNew($type, $skills, $callid_cti, $did, $aid, $dateinfo) {
        $date_attributes = DateHelper::get_date_attributes('cl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);

        if (empty($type) || empty($date_attributes->yy)) return 0;
        $this->_condition = '';
        /*$callid_count = 'cl.callid';
        if ($type == 'outbound') {
            $callid_count = 'cl.callid';
        }*/
        $sql = "SELECT COUNT(lai.callid_cti) as numrows FROM log_agent_inbound as lai ";
        $sql .= " LEFT JOIN cdrin_log AS ci ON ci.callid = lai.callid_cti ";
        $sql .= $this->getSkillCDRSqlPartNew($type, $skills, $callid_cti, $did, $aid, $date_attributes->yy, $dateinfo);;
        $result = $this->getDB()->query($sql);
//        GPrint($sql);die;
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }
    function getSkillCDRSqlPartNew($type, $skills, $callid_cti, $did, $aid, $yy, $dateinfo, $is_disposition=false, $sms_enabled=false){
        $sql = '';
        $cond = '';
        $skill_cond = '';
        $year = '';//$yy == date('y') ? '' : '_' . $yy;
        $scdr = 'cl';

        if ($type=='outbound') {
            /*$skill_table = 'agent_outbound_manual_log' . $year . ' AS cl';
            $cdr_table = 'cdrout_log' . $year;
            $sql .= "$skill_table";
            if ($sms_enabled){
                $sql .= " LEFT JOIN inbound_sms_log AS sms ON sms.callid=cl.callid ";
            }
            $date_attributes = DateHelper::get_date_attributes("$scdr.tstamp", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
            $cond = "cl.agent_id='$aid'";

            if (!empty($cli)) $cond = $this->getAndCondition($cond, "cl.callto LIKE '$cli%'");
            if (!empty($did)) $cond = $this->getAndCondition($cond, "callto LIKE '$did%'");

            if (!empty($date_attributes->condition) && !empty($cond)) {
                $cond = $date_attributes->condition . " AND $cond";
            } else if (!empty($date_attributes->condition)) {
                $cond = $date_attributes->condition;
            }*/
        } else {

            //$sql =!empty($did) ? " LEFT JOIN cdrin_log AS ci ON ci.callid = lai.callid_cti " : "";
            $sql = "";

            $cond = " lai.agent_id='$aid' ";
            $cond .= !empty($did) ? " AND ci.did='$did'" : "";
            $cond .= !empty($callid_cti) ? " AND lai.callid_cti='$callid_cti'" : " ";
            //$date_attributes = DateHelper::get_date_attributes("lai.start_time", $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
            //$cond .= " AND $date_attributes->condition ";
            $cond .= !empty($dateinfo) ? " AND lai.start_time BETWEEN '".$dateinfo->sdate." ".$dateinfo->stime."' AND '"." $dateinfo->edate"." ".$dateinfo->etime."'" : "";
        }
        if (!empty($cond)) $sql .= " WHERE $cond";

        $this->_condition = $sql;
        return $sql;
    }

    function getSkillCDRsNew($type, $skills, $callid_cti, $did, $aid, $dateinfo, $offset=0, $rowsPerPage=0, $sms_enabled=false){

        $acdr = 'sl';

        if ($type == 'outbound') {
            $sql = "SELECT cl.callid AS callid, '-' AS trunkid, cl.callto, cl.skill_id, cl.agent_id, ".
                "is_reached, FROM_UNIXTIME(cl.start_time) AS start_time, cl.talk_time, cl.callerid, ".
                "cl.service_time AS service_time, FROM_UNIXTIME(cl.tstamp) AS stop_time, '-' AS disc_cause";
        } else {
            $sql = "SELECT lai.start_time, lai.callid_cti, lai.is_answer, lai.ring_time, lai.service_time, lai.wrap_up_time, lai.hold_time, lai.agent_id, FROM_UNIXTIME(ci.tstamp) AS stop_time, ci.cli, ci.did, ci.disc_party, skl.skill_name";
            $sql .= " FROM log_agent_inbound AS lai ";
            $sql .= " LEFT JOIN cdrin_log AS ci ON ci.callid = lai.callid_cti ";
            $sql .= " LEFT JOIN skill AS skl ON skl.skill_id = lai.skill_id ";
            $sql .= " LEFT JOIN skill_crm_disposition_code AS scdc ON scdc.disposition_id = lai.disposition_id ";
        }

        $sql .= $this->getSkillCDRSqlPartNew($type, $skills, $callid_cti, $did, $aid, '', $dateinfo, false, $sms_enabled) . " ORDER BY lai.start_time DESC LIMIT $offset, $rowsPerPage";
//        echo $sql;die;
        return $this->getDB()->query($sql);
    }
        
}

?>