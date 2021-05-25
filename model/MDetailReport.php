<?php
class MDetailReport extends Model
{
	public function __construct() {
		parent::__construct();
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
				$returnArray[$data->skill_id] = $data->skill_name;
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
	public function get_disposition_all_value()
    {
        $dispositions = [];
        $query = "SELECT disposition_id, title, disposition_type, responsible_party FROM skill_crm_disposition_code ORDER BY title";

        $result = $this->getDB()->query($query);
        if (empty($result)){
            return $dispositions;
        }

        foreach ($result as $disposition){
            $dispositions[$disposition->disposition_id]['disposition_id'] = $disposition->disposition_id;
            $dispositions[$disposition->disposition_id]['title'] = $disposition->title;
            $dispositions[$disposition->disposition_id]['type'] = $disposition->disposition_type;
            $dispositions[$disposition->disposition_id]['responsible_party'] = $disposition->responsible_party;
        }

        return $dispositions;
	}
	function numCategoriesDetailsReport($dateinfo, $skill_ids = [], $report_type = REPORT_DAILY, $skill_type, $hangup_initiator, $disposition_id)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_skill_inbound';
        $group_by = '';

        $sql = "SELECT COUNT({$table}.call_start_time) AS total_record FROM {$table} ";
        // $sql .= "left join skill ON skill.skill_id={$table}.skill_id ";
        $sql .= " WHERE {$table}.call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

        // $sql .= (!empty($qtype )) ? " AND skill.qtype='{$qtype}' " : "";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND {$table}.skill_id IN('{$skill_ids}') " : "";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND {$table}.call_type='{$skill_type}' " : "";
        $sql .= (!empty($hangup_initiator) && $hangup_initiator!='*') ? " AND {$table}.disc_party='{$hangup_initiator}' " : "";
        $sql .= (!empty($disposition_id) && $disposition_id!='*') ? " AND {$table}.disposition_id='{$disposition_id}' " : "";
        $sql .= $group_by;
        // echo $sql;

        $record = $this->getDB()->query($sql);

        return $record[0]->total_record;
	}
	public function saveReportAuditRequest($report_name, $request_param){
        $log_txt = [];
        foreach ($request_param as $key => $value) {
            $log_txt[] =$key.'='.$value;
        }
        $log_txt = implode(';', $log_txt);
        $this->addToAuditLog($report_name, 'R', $log_txt, '');
	}
	function getCategoriesDetailsReport($dateinfo,$skill_ids = [], $report_type = REPORT_DAILY, $skill_type, $hangup_initiator, $disposition_id, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isSum=false, $multiple_wrap_up = false)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_skill_inbound';   
        $group_by = '';        
        $order_by = " ORDER BY lsi.call_start_time ASC, skill_id ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
             
        $sql = "SELECT lsi.call_start_time, lsi.cli, lsi.skill_id, lsi.status, lsi.ring_time, lsi.service_time, lsi.agent_hold_time, lsi.repeated_call, lsi.ice_feedback, lsi.disc_party, lsi.hold_in_q, lsi.wrap_up_time, lsi.disposition_count, lsi.agent_id, lsi.callid, lsi.transfer_tag ";
        if(!$multiple_wrap_up){
            // $sql .= ', scdc.title, scdc.disposition_type, scdc.disposition_id ';
            $sql .= ', lsi.disposition_id ';
        }else{
            $sql .= ', scdl.disposition_id ';
        }


        if($isSum){
            $sql = "SELECT SUM(lsi.ring_time) as ring_time, SUM(lsi.service_time) as service_time, SUM(lsi.agent_hold_time) as agent_hold_time, SUM(lsi.repeated_call) as repeated_call, SUM(lsi.hold_in_q) as hold_in_q, SUM(lsi.wrap_up_time) as wrap_up_time, SUM(lsi.disposition_count) as disposition_count, SUM(IF(lsi.status='S', 1,0)) as call_ans, SUM(IF(lsi.transfer_tag !='', 1,0)) as transfer_tag ";
        }

        $sql .= "FROM {$table} as lsi ";
        if($multiple_wrap_up){
            $scdl_sdate = strtotime("-1 hour", $dateinfo->ststamp);
            $scdl_edate = strtotime("+1 hour", $dateinfo->etstamp);
            // var_dump(date('Y-m-d H:i:s', $scdl_sdate));
            // var_dump(date('Y-m-d H:i:s', $scdl_edate));
            // var_dump($scdl_sdate);
            // var_dump($scdl_edate);
            $sql .= "LEFT JOIN skill_crm_disposition_log as scdl ON scdl.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND lsi.callid=scdl.callid ";
        }
        // else{
            // $sql .= "LEFT JOIN skill_crm_disposition_code as scdc ON scdc.disposition_id=lsi.disposition_id ";
        // }
        $sql .= " WHERE lsi.call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

        // if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            // $sql .= " AND HOUR(enter_time) BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND lsi.skill_id IN('{$skill_ids}') " : "";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND lsi.call_type='{$skill_type}' " : "";
        $sql .= (!empty($hangup_initiator) && $hangup_initiator!='*') ? " AND lsi.disc_party='{$hangup_initiator}' " : "";
        $sql .= (!empty($disposition_id) && $disposition_id!='*') ? " AND lsi.disposition_id='{$disposition_id}' " : "";
        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        //echo $sql;
        // die();
        
        return $this->getDB()->query($sql);
	}
	function getMultipleDisposition($callIds){
        $table = "skill_crm_disposition_log";
        $sql = "SELECT * FROM {$table} ";

        $sql .= !empty($callIds) ? " WHERE callid IN('".$callIds."') " : '';
        // var_dump($sql);
        $record = $this->getDB()->query($sql);

        return $record;
    }




    /*
     * Agent perfomance report
     */
    public function getAgentsFullName()
    {
        $sql = "SELECT agent_id, nick, name FROM agents ";
        $sql .= "ORDER BY agent_id ";

        $result = $this->getDB()->query($sql);

        $names = array();
        if (is_array($result)) {
            foreach ($result as $row) {
                $names[$row->agent_id] = !empty($row->name) ? $row->name : $row->agent_id;
            }
        }
        return $names;
    }

    public function numAgentPerformanceSummary($dateinfo, $type="")
    {
        $scdl_sdate = date('Y-m-d H:i:s', strtotime("-2 hour", strtotime($dateinfo->sdate)));
        $scdl_edate = date('Y-m-d H:i:s', strtotime("+2 hour", strtotime($dateinfo->edate)));

        $skill_type_cond = " NOT IN('O', 'E', 'C') ";
        if (!empty($type) && $type=="E")
            $skill_type_cond = " IN('E') ";
        elseif(!empty($type) && $type=="C")
            $skill_type_cond = " IN('C') ";

        $sql = "SELECT COUNT(las.tstamp) AS aggregate ";
        $sql .= "FROM log_agent_session as las ";
        $sql .= "LEFT JOIN agent_skill as ask ON ask.agent_id=las.agent_id ";
        $sql .= "LEFT JOIN skill as s ON s.skill_id=ask.skill_id ";
        $sql .= "WHERE las.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND s.skill_type ".$skill_type_cond;
        $sql .= "GROUP BY las.agent_id ";
        $result = $this->getDB()->query($sql);
//        echo $sql;
//        die();
        return !empty($result) ? count($result): 0;
    }

    public function getAgentPerformanceSummary($dateinfo, $offset=0, $limit=20, $type="")
    {
        $scdl_sdate = date('Y-m-d H:i:s', strtotime("-2 hour", strtotime($dateinfo->sdate)));
        $scdl_edate = date('Y-m-d H:i:s', strtotime("+2 hour", strtotime($dateinfo->edate)));

        $skill_type_cond = " NOT IN('O', 'E', 'C') ";
        if (!empty($type) && $type=="E")
            $skill_type_cond = " IN('E') ";
        elseif(!empty($type) && $type=="C")
            $skill_type_cond = " IN('C') ";

        $sql = "SELECT las.agent_id ";
        $sql .= "FROM log_agent_session as las ";
        $sql .= "LEFT JOIN agent_skill as ask ON ask.agent_id=las.agent_id ";
        $sql .= "LEFT JOIN skill as s ON s.skill_id=ask.skill_id ";
        $sql .= "WHERE las.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND s.skill_type ".$skill_type_cond;
        $sql .= "GROUP BY las.agent_id ";
        $sql .= "ORDER BY las.agent_id LIMIT $offset, $limit ";

        return $this->getDB()->query($sql);
    }

    public function getAgentSkillSetAsString2($agent_id='')
    {
        $skill_sets = [];

        $sql = "SELECT agent_id, skill_name FROM agent_skill ";
        $sql .= " INNER JOIN  skill ON agent_skill.skill_id = skill.skill_id";
        $sql .= !empty($agent_id) ? " WHERE agent_id = '{$agent_id}' " : "";
        $sql .= " order by agent_skill.agent_id, agent_skill.priority, skill.skill_name ";
        $response = $this->getDB()->query($sql);

        if (is_array($response)){
            foreach ($response as $record){
                if (empty($skill_sets[$record->agent_id])){
                    $skill_sets[$record->agent_id] = new stdClass();
                    $skill_sets[$record->agent_id]->agent_id = $record->agent_id;
                    $skill_sets[$record->agent_id]->skill_set = "";
                }
                $skill_sets[$record->agent_id]->skill_set .= !empty( $skill_sets[$record->agent_id]->skill_set) ? ", " : "";
                $skill_sets[$record->agent_id]->skill_set .= $record->skill_name;
            }
        }

        return $skill_sets;
    }

    public function getAgentCallInformation($dateinfo, $agent_id){
        $sql = "SELECT lai.agent_id, SUM(CASE WHEN lai.is_answer='Y' THEN 1 ELSE 0 END ) AS calls_answered, ";
        $sql .= "SUM(lai.ring_time) AS ring_time, ";
        $sql .= "SUM(lai.wrap_up_time) AS wrap_up_time, SUM(lai.service_time) AS service_time, ";
        $sql .= "SUM(lai.hold_time) AS hold_time, SUM(lai.hold_in_q) AS hold_time_in_queue,  ";
        $sql .= "SUM(CASE WHEN (lai.is_answer='Y' AND  lai.disc_party='A') THEN 1 ELSE 0 END)  AS agent_hangup, ";
        $sql .= "SUM(CASE WHEN (lai.is_answer='N' AND  lai.disc_party='A') THEN 1 ELSE 0 END) AS agent_reject_calls, ";
        $sql .= "SUM(CASE WHEN lai.disc_party='A' THEN 1 ELSE 0 END ) AS agent_disc_calls, ";
        $sql .= "SUM(CASE WHEN lai.service_time < 10 THEN 1 ELSE 0 END ) AS short_call, ";
        $sql .= "SUM(CASE WHEN lai.repeated_call = 'Y' THEN 1 ELSE 0 END ) AS repeated_call, ";
        $sql .= "SUM(CASE WHEN lai.fcr_call = 'Y' THEN 1 ELSE 0 END ) AS fcr_call, ";
        $sql .= "SUM(lai.disposition_count) AS workcode_count ";
        $sql .= "FROM log_agent_inbound AS lai ";
        $sql .= "WHERE lai.call_start_time BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' AND lai.agent_id = '{$agent_id}' ";
        $sql .= "AND call_type NOT IN('O', 'E') ";
        // echo $sql;
        // die();
        $result = $this->getDB()->query($sql);

        return (!empty($result)) ? array_shift($result) : [];
    }

    function getAgentSessionInfo($agent_id, $dateinfo)
    {
        $report_start_time = $dateinfo->sdate;
        $report_end_time = $dateinfo->edate;

        $result = $this->getAgentSessionLog($agent_id, $report_start_time, $report_end_time);
        return $this->calcAgentSessionInfo($agent_id, $result, $report_start_time, $report_end_time);
    }

    function getAgentSessionLog($agent_id, $stime='', $etime='', $order = 'tstamp')
    {
        $sql = "SELECT * FROM log_agent_session WHERE tstamp BETWEEN '{$stime}' AND '{$etime}' ";
        $sql .= " AND agent_id='$agent_id' ORDER BY $order";
        // echo $sql;

        return $this->getDB()->query($sql);
    }

    function getAgentLastStatus($agent_id, $stime)
    {
        $sql = "SELECT * FROM log_agent_session WHERE tstamp < '{$stime}'";
        $sql .= " AND agent_id='$agent_id' ORDER BY tstamp DESC LIMIT 1";
        // echo $sql;

        return $this->getDB()->query($sql);
    }

    function getAgentPreviousLoginStatus($agent_id, $stime)
    {
        $sql = "SELECT * FROM log_agent_session WHERE   tstamp < '{$stime}' ";
        $sql .= " AND agent_id='$agent_id' and type='I' ORDER BY tstamp DESC LIMIT 1 ";

        return $this->getDB()->query($sql);
    }

    function diff_in_sec($stime, $etime)
    {
        return strtotime($etime) - strtotime($stime);
    }

    function getAgentNextStatus($agent_id, $etime)
    {
        $sql = "SELECT * FROM log_agent_session WHERE tstamp > '{$etime}'";
        $sql .= " AND agent_id='$agent_id' ORDER BY tstamp ASC LIMIT 1";

        return $this->getDB()->query($sql);
    }

    function getAgentNextLogoutStatus($agent_id, $etime)
    {
        $sql = "SELECT * FROM log_agent_session WHERE  tstamp > '{$etime}' ";
        $sql .= " AND agent_id='$agent_id' and type='O' ORDER BY tstamp ASC LIMIT 1 ";

        return $this->getDB()->query($sql);
    }

    function calcAgentSessionInfo($agent_id, $result, $stime='', $etime='')
    {
        $agent = new stdClass();
        $agent->staffed_time = 0;
        $agent->idle_time = 0;
        $agent->logout_time = '';
        $agent->not_ready_time = 0;
        $agent->available_time = 0;
        $agent->not_ready_count = 0;
        $agent->first_login = '';

        $last_session_details = $this->getAgentLastStatus($agent_id, $stime);
        if (!empty($last_session_details) && $last_session_details[0]->type == 'I'){
            $previous_login_info = $last_session_details;
        }else{
            $previous_login_info = $this->getAgentPreviousLoginStatus($agent_id, $stime);
        }

        if (empty($result)){

            if (is_array($last_session_details)){
                $last_event = $last_session_details[0]->type;

                $agent->first_login = !empty($previous_login_info) ? $previous_login_info[0]->tstamp : $agent->first_login;

                if ($last_event == 'I'){ // Last event is login
                    $agent->staffed_time += $this->diff_in_sec($stime, $etime);
                    $agent->first_login = !empty($agent->first_login) ? $agent->first_login : $last_session_details[0]->tstamp;
                }elseif ($last_event == 'X'){ //Agent last event is busy
                    $agent->not_ready_count += 1;
                    $agent->staffed_time += $this->diff_in_sec($stime, $etime);
                    $agent->not_ready_time = $this->diff_in_sec($stime, $etime);
                }elseif ($last_event == 'R'){
                    $agent->staffed_time += $this->diff_in_sec($stime, $etime);
                    $agent->idle_time += $this->diff_in_sec($stime, $etime);
                }
            }

            return $agent;
        }

        $last_time = !empty($last_session_details[0]->tstamp) ? ($stime > $last_session_details[0]->tstamp  ? $stime : $last_session_details[0]->tstamp) : 0;
        $last_event = !empty($last_session_details[0]->type) ? $last_session_details[0]->type : '';

        if (!empty($previous_login_info)){
            $login = array_shift($previous_login_info);
            $agent->first_login = $login->tstamp;
        }

        if ($result[0]->type == 'I'){
            $agent->first_login = $result[0]->tstamp;
        }

        $login_duration_arr = [];
        $logout_duration_arr = [];

        foreach($result as $index => $session) {
            if($index == 0 && $session->type == 'I') {
                $agent->first_login = !empty($agent->first_login) ? $agent->first_login : $session->tstamp;

            } else if($session->type=='O') {
                $agent->logout_time = $session->tstamp;

                if ($last_event == 'I'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }elseif ($last_event == 'R'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->idle_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }elseif ($last_event == 'X'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }
            } else if($session->type=='X') {
                $agent->not_ready_count += 1;
                $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;

                if ($last_event == 'I'){
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }elseif ($last_event == 'R'){
                    $agent->idle_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }elseif ($last_event == 'X'){
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }
            } else if($session->type=='R') {
                if ($last_event == 'I'){
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->staffed_time += $this->diff_in_sec($last_time, $session->tstamp);
                }elseif ($last_event == 'X'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }
            }

            // Gprint($last_event);
            // Gprint($session);
            $last_event = $session->type;
            $last_time = $session->tstamp;
            // Gprint($last_event);

            // calculate login duration
            $l_arr_c = count($login_duration_arr);
            $lo_arr_c = count($logout_duration_arr);

            if($session->type == 'I') {
                $login_duration_arr[$l_arr_c] = $session->tstamp;
            }elseif($session->type == 'O'){
                $logout_duration_arr[$lo_arr_c] = $session->tstamp;
            }

            if($index==0 && !isset($login_duration_arr[$l_arr_c])){
                $login_duration_arr[$l_arr_c] = $stime;
            }

            if($index==(count($result)-1) && !isset($logout_duration_arr[$lo_arr_c])){
                $logout_duration_arr[$lo_arr_c] = $etime;
            }
        }

        $total_record = count($result);
        $index = $total_record - 1;

        if ($result[$index]->type != 'O'){

            $agent_next_status = $this->getAgentNextStatus($agent_id, $etime);
            $agent_next_status = !empty($agent_next_status) ? array_shift($agent_next_status) : $agent_next_status;
            // GPrint($agent_next_status);

            if (!empty($agent_next_status)){
                if($agent_next_status->type=='R'){
                    if ($last_event == 'I'){
                        $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    }elseif ($last_event == 'X'){
                        $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                        $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    }
                }elseif ($agent_next_status->type=='X'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    if ($last_event == 'X'){
                        $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    }
                }else{
                    // var_dump($result[$index]->type);
                    if(in_array($result[$index]->type, array('X', 'I', 'R')) && $agent_next_status->tstamp > $etime){
                        $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    }

                    if($result[$index]->type=='X'){
                        $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    }
                }
            }else{
                $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;

                if (in_array($last_event, array('X','I'))){
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                }
            }

        }

        if ($result[$index]->type != 'O'){
            $next_logout_info = $this->getAgentNextLogoutStatus($agent_id, $last_time);

            $logout = !empty($next_logout_info) ? array_shift($next_logout_info) : $next_logout_info;
            $agent->logout_time = !empty($logout->tstamp) ? $logout->tstamp : '';
        }

        $login_duration = 0;
        foreach ($login_duration_arr as $key => $item) {
            $login_duration +=$this->diff_in_sec($login_duration_arr[$key], $logout_duration_arr[$key]);
        }
        $agent->login_duration = $login_duration;

        return $agent;
    }
}    
?>