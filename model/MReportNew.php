<?php

class MReportNew extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_ivr_footprint($callid)
    {
        $sql = "SELECT l.*, d.service_title FROM log_ivr_footprint l LEFT JOIN ivr_service_code d ON d.disposition_code=l.disposition_id WHERE callid='$callid' ORDER BY log_time DESC LIMIT 50";
        return $this->getDB()->query($sql);
    }

    public function get_session_by_id($session_id,$agent_id,$shift_code,$shift_date)
    {
        $query = "SELECT first_login,last_logout FROM rt_agent_shift_summary WHERE session_id='{$session_id}' AND agent_id='{$agent_id}' AND shift_code='{$shift_code}' AND sdate='{$shift_date}' LIMIT 1";
        $response = $this->getDB()->query($query);
        return $response[0];
    }

    public function update_session_by_id($session_id,$agent_id,$shift_code,$shift_date,$first_login,$last_logout)
    {
        $query = "UPDATE rt_agent_shift_summary SET first_login='{$first_login}',last_logout='{$last_logout}' WHERE session_id='{$session_id}' AND agent_id='{$agent_id}' AND shift_code='{$shift_code}' AND sdate='{$shift_date}' LIMIT 1";
        return $this->getDB()->query($query);

    }

    function numAgentStatus($dateinfo, $agent_id, $shift_code='', $report_type=null)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $group_by = '';
        if ($report_type == REPORT_YEARLY) {
            $group_by =  'YEAR(sdate)' ;
        } elseif ($report_type == REPORT_QUARTERLY) {
            $group_by =  'QUARTER(sdate)' ;
        } elseif ($report_type == REPORT_MONTHLY) {
            $group_by =  'MONTH(sdate)' ;
        } else {
            $group_by =  'sdate' ;
        }

        $table = 'rt_agent_shift_summary';
        $sql = "SELECT COUNT(sdate) AS total_record FROM {$table}";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        $sql .= !empty($agent_id) ? " AND agent_id ='{$agent_id}' " : "";
        $sql .= !empty($shift_code) ? " AND shift_code ='{$shift_code}' " : "";
        $sql .= " GROUP BY ".(!empty($group_by) ? $group_by.', ' : '')."agent_id,shift_code ";
        // echo $sql;

        $record = $this->getDB()->query($sql);
        // var_dump($record);
        return count($record);
    }

    function getAgentStatus($dateinfo, $agent_id='', $shift_code='',$offset=0, $rowsPerPage=20, $report_type=null, $isGroupBy=true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";
        $columns = '';
        $group_by = '';
        $table = 'rt_agent_shift_summary';

        if ($report_type == REPORT_YEARLY) {
            $columns .= "YEAR(sdate) as syear, ";
            $group_by =  'YEAR(sdate)' ;
        } elseif ($report_type == REPORT_QUARTERLY) {
            $columns .= "QUARTER(sdate) as quarter_no, ";
            $group_by =  'QUARTER(sdate)' ;
        } elseif ($report_type == REPORT_MONTHLY) {
            $columns .= "MONTHNAME(sdate) as smonth, ";
            $group_by =  'MONTH(sdate)' ;
        } else {
            $columns = "sdate, ";
            $group_by =  'sdate' ;
        }

        $columns .= "agent_id, shift_code, first_login, last_logout, SUM(available_time) as available_time, SUM(calls_in_ans) as calls_in_ans, SUM(ring_in_time) as ring_in_time, SUM(calls_in_time) as calls_in_time, SUM(total_aux_in_time) as total_aux_in_time, SUM(total_aux_out_time) as total_aux_out_time, SUM(total_break_time) as total_break_time, SUM(staff_time) as staff_time, SUM(hold_time) as hold_time, SUM(aux_11_time) as aux_11_time, SUM(hangup_acd_calls) as hangup_acd_calls, SUM(drop_acd_calls) as drop_acd_calls, SUM(xfer_ivr_count) as xfer_ivr_count, SUM(xfer_queue_count) as xfer_queue_count, SUM(wrap_up_time) as wrap_up_time, SUM(hold_time_in_queue) as hold_time_in_queue, SUM(short_call_count) as short_call_count, SUM(aux_11_count) as aux_11_count, SUM(aux_12_count) as aux_12_count, SUM(aux_13_count) as aux_13_count, SUM(aux_14_count) as aux_14_count, SUM(aux_15_count) as aux_15_count, SUM(aux_16_count) as aux_16_count, SUM(aux_17_count) as aux_17_count, SUM(aux_18_count) as aux_18_count, SUM(aux_19_count) as aux_19_count, SUM(aux_20_count) as aux_20_count, SUM(aux_21_count) as aux_21_count, SUM(wrap_up_count) as wrap_up_count, SUM(calls_out_time) as calls_out_time, SUM(ring_6_to_10_count) as ring_6_to_10_count, SUM(ring_gt_11_count) as ring_gt_11_count, SUM(fcr_call_count) as fcr_call_count, SUM(repeat_call_count) as repeat_call_count ";

        $sql = "SELECT $columns FROM $table";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        $sql .= !empty($agent_id) ? " AND agent_id ='{$agent_id}' " : "";
        $sql .= !empty($shift_code) ? " AND shift_code ='{$shift_code}' " : "";
        if($isGroupBy)
            $sql .= " GROUP BY ".(!empty($group_by) ? $group_by.', ' : '')."agent_id,shift_code ";

        $sql .= ($rowsPerPage > 0) ? " ORDER BY sdate ASC,agent_id LIMIT $rowsPerPage OFFSET $offset" : "";
        // echo $sql;

        return $this->getDB()->query($sql);
    }


    function numAgentOutboundCallStatus($dateinfo,$agent_id,$shift_code='')
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_agent_shift_summary';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        $sql .= !empty($agent_id) ? " AND agent_id ='{$agent_id}' " : "";
        $sql .= !empty($shift_code) ? " AND shift_code ='{$shift_code}' " : "";
        $sql .= " AND calls_out_attempt > 0";
        $sql .= " GROUP BY sdate,agent_id,shift_code ";

        $record = $this->getDB()->query($sql);

        return count($record);

    }

    function getAgentOutboundCallStatus($dateinfo,$agent_id='', $shift_code='',$offset=0, $rowsPerPage=20)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_agent_shift_summary';


        $sql = "SELECT * FROM $table";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        $sql .= !empty($agent_id) ? " AND agent_id ='{$agent_id}' " : "";
        $sql .= !empty($shift_code) ? " AND shift_code ='{$shift_code}' " : "";
        $sql .= " AND calls_out_attempt > 0";
        $sql .= " GROUP BY sdate,agent_id,shift_code ";

        $sql .= ($rowsPerPage > 0) ? " ORDER BY sdate ASC,agent_id LIMIT $rowsPerPage OFFSET $offset" : "";

        return $this->getDB()->query($sql);
    }


    function numAgentProductivityStatus($dateinfo,$agent_id,$shift_code='',$sum_date = "N")
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_agent_shift_summary';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        if (!empty($agent_id))
        {
            $sql .= " AND agent_id ='{$agent_id}' ";
        }
        if (!empty($shift_code))
        {
            $sql .= " AND shift_code ='{$shift_code}' ";
        }

        $sql .= strtoupper($sum_date) == "Y" ? "GROUP BY agent_id" : "";

        $record = $this->getDB()->query($sql);

        return strtoupper($sum_date) == "Y" ? count($record) : array_shift($record)->total_record;
    }

    function getAgentProductivityStatus($dateinfo,$agent_id='', $shift_code='',$sum_date = "N", $offset=0, $rowsPerPage=20)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_agent_shift_summary';
        $group_by = strtoupper($sum_date) == "Y" ? "GROUP BY agent_id " : "";

        $select = $sum_date == "Y" ? "agent_id, shift_code, SUM(calls_in_time) as calls_in_time,SUM(calls_in_ans) AS calls_in_ans, SUM(hold_time) AS hold_time, SUM(aux_11_time) AS aux_11_time" : "*";


        $sql  = "SELECT {$select} FROM $table";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        $sql .= !empty($agent_id) ? " AND agent_id ='{$agent_id}' " : "";
        $sql .= !empty($shift_code) ? " AND shift_code ='{$shift_code}' " : "";
        $sql .= $group_by;
        $sql .= ($rowsPerPage > 0) ? " ORDER BY sdate ASC,agent_id LIMIT $rowsPerPage OFFSET $offset" : "";

        return $this->getDB()->query($sql);
    }

    function numDispositionCall($dateinfo,$disposition_id)
    {
        $disposition_list = [];
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        if (!empty($disposition_id) && $disposition_id != "*")
        {
            $dispostions = $this->getDispositionsIDByParent($disposition_id);
            if (count($dispostions) > 0){
                $disposition_list = $this->getDispositionIDs($dispostions);
            }
            $disposition_list[] = $disposition_id;
        }

        $query = "SELECT COUNT(*) AS total_record FROM skill_crm_disposition_log ";
        $query .= " WHERE log_date BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $query .= count($disposition_list) ?  " AND disposition_id IN ('".implode("', '",$disposition_list)."') " : " ";
        $query .=  !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id = '{$partition_id}') " : "";
        $query .= " GROUP BY log_date, disposition_id ";
        $record = $this->getDB()->query($query);
        return count($record);
    }

    private function getDispositionsIDByParent($parent_disposition){

        $sql = "SELECT lft,rgt FROM skill_crm_disposition_code WHERE disposition_id='{$parent_disposition}'";
        $result = $this->getDB()->query($sql);

        $lft = $result[0]->lft; $rgt = $result[0]->rgt;

        $query  = "SELECT disposition_id FROM skill_crm_disposition_code WHERE lft > {$lft} AND rgt < {$rgt} ";

        return $this->getDB()->query($query);
    }

    private function getDispositionIDs(array $disposition_array_of_object)
    {
        $disposition_array = [];
        foreach ($disposition_array_of_object as $dispostion)
        {
            $disposition_array[] = $dispostion->disposition_id;
        }

        return $disposition_array;
    }

    function getDispositionCall($dateinfo,$disposition_id,$offset=0,$limit=20)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $disposition_list = [];
        $response = new stdClass();
        $response->total_record = 0;
        $response->data = [];

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        if (!empty($disposition_id) && $disposition_id != "*")
        {
            $dispostions = $this->getDispositionsIDByParent($disposition_id);
            if (count($dispostions) > 0){
                $disposition_list = $this->getDispositionIDs($dispostions);
            }
            $disposition_list[] = $disposition_id;
        }

        $query = "SELECT log_date,disposition_id, COUNT(callid) as call_count  FROM skill_crm_disposition_log";
        $query .= " WHERE log_date BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $query .= count($disposition_list) ?  " AND disposition_id IN ('".implode("', '",$disposition_list)."') " : " ";
        $query .=  !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        $query .= " GROUP BY log_date,disposition_id order by call_count DESC";
        if ($limit > 0) $query .= " LIMIT {$limit} OFFSET {$offset}";
        $response->data =  $this->getDB()->query($query);

        $sql = "SELECT COUNT(callid) AS total_record FROM skill_crm_disposition_log ";
        $sql .= " WHERE log_date BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= count($disposition_list) ?  " AND disposition_id IN ('".implode("', '",$disposition_list)."') " : " ";
        $sql .=  !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        $total_record =  $this->getDB()->query($sql);
        $response->total_record = $total_record[0]->total_record;

        return $response;
    }


    function numDispositionDetails($tempid, $did, $dateinfo, $contactNo='', $agent_id='')
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $cond = '';
        $date_attributes = DateHelper::get_date_attributes('dl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);

        $sql = "SELECT COUNT(*) AS numrows FROM skill_crm_disposition_log AS dl ";
        $sql .= "LEFT JOIN skill_crm_disposition_code AS dc ON dc.disposition_id=dl.disposition_id ";

        $cond = !empty($date_attributes->condition) ? $this->getAndCondition($cond, $date_attributes->condition) : $cond;
        $cond = !empty($did) ? $this->getAndCondition($cond, "dl.disposition_id='$did'") : $cond;
        $cond = !empty($agent_id) ? $this->getAndCondition($cond,"dl.agent_id='{$agent_id}' ") : $cond;
        $cond = !empty($tempid) ? $this->getAndCondition($cond, "template_id='$tempid'") : $cond;
        $cond = !empty($contactNo) ? $this->getAndCondition($cond, "dl.cli LIKE '$contactNo%'") : $cond;
        $cond = !empty($partition_id) ? $this->getAndCondition($cond, "dl.agent_id IN(Select agent_id from agents where partition_id = '{$partition_id}') ") : $cond;

        $sql .= !empty($cond) ? "WHERE $cond " : "";
        $result = $this->getDB()->query($sql);

        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
    }

    function getDispositionDetails($tempid, $did, $dateinfo, $offset=0, $limit=0, $contactNo='', $agent_id='')
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $cond = '';
        $date_attributes = DateHelper::get_date_attributes('dl.tstamp', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);


        $sql  = "SELECT dc.template_id, dl.disposition_id, dl.agent_id, dl.tstamp, dl.note, dl.callid, dl.caller_auth_by, dl.cli AS contact_no ";
        $sql .= "FROM skill_crm_disposition_log AS dl ";
        $sql .= "LEFT JOIN skill_crm_disposition_code AS dc ON dc.disposition_id=dl.disposition_id ";

        $cond = !empty($date_attributes->condition) ? $this->getAndCondition($cond, $date_attributes->condition) : $cond;
        $cond = !empty($did) ? $this->getAndCondition($cond, "dl.disposition_id='$did'") :$cond;
        $cond = !empty($tempid) ? $this->getAndCondition($cond, "template_id='$tempid'") : $cond;
        $cond = !empty($contactNo) ? $this->getAndCondition($cond, "dl.cli LIKE '$contactNo%'") : $cond;
        $cond = !empty($agent_id) ? $this->getAndCondition($cond, "dl.agent_id='{$agent_id}' ") : $cond;
        $cond = !empty($partition_id) ? $this->getAndCondition($cond, "dl.agent_id IN(Select agent_id from agents where partition_id = '{$partition_id}') ") : $cond;

        $sql .= !empty($cond) ? "WHERE $cond " : "";
        $sql .= "ORDER BY dl.tstamp DESC ";
        $sql .= $limit > 0 ? "LIMIT $offset, $limit" : "";

        return $this->getDB()->query($sql);
    }

    function numCallbackRequests($dateinfo)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT COUNT(*) as total_callback_requests FROM schedule_dial ";
        $sql .= "LEFT JOIN leads ON schedule_dial.id = leads.id ";
        $sql .= " WHERE schedule_dial.dial_time BETWEEN '{$dateinfo->sdate} {$dateinfo->stime}' AND '{$dateinfo->edate} {$dateinfo->etime}' ";
        $sql .= !empty($partition_id) ? " AND leads.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";


        $result =  $this->getDB()->query($sql);
        return $result[0]->total_callback_requests;
    }

    function getCallbackRequests($dateinfo,$offset=0,$limit=20)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT * FROM schedule_dial ";
        $sql .= "LEFT JOIN leads ON schedule_dial.id = leads.id ";
        $sql .= " WHERE schedule_dial.dial_time BETWEEN '{$dateinfo->sdate} {$dateinfo->stime}' AND '{$dateinfo->edate} {$dateinfo->etime}' ";
        $sql .= !empty($partition_id) ? " AND leads.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= " ORDER BY schedule_dial.dial_time ASC";
        if ($limit > 0) $sql .= " LIMIT {$limit} OFFSET {$offset}";

        return $this->getDB()->query($sql);
    }

    /*  function numHourlyCallStatus($dateinfo)
      {
          $sql  = "SELECT  count(*) as total_row ";
          $sql .= " FROM rt_skill_call_summary WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
          $sql .= " GROUP BY sdate, shour";
          $response = $this->getDB()->query($sql);
          return $response[0]->total_row * 3;
      } */
    function getHourlyCallStatus($dateinfo,$skill_id,$offset=0,$limit=20)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $date1 = date_create($dateinfo->sdate);
        $date2 = date_create($dateinfo->edate);
        $interval =  $interval = date_diff($date1, $date2);
        $interval = $interval->format('%a') + 1;

        $sql  = "SELECT  sdate,shour, (SUM(calls_offered) / {$interval}) as call_offered, ";
        $sql .= " (SUM(calls_answerd) / {$interval}) as call_answered, (SUM(calls_abandoned)  / {$interval} ) as call_abandoned ";
        $sql .= " FROM rt_skill_call_summary ";
        $sql .= !empty($dateinfo) ? " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' " : "";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id = '{$skill_id}' " : "";
        $sql .= " GROUP BY sdate, shour ORDER BY sdate";

        return $this->getDB()->query($sql);
    }

    function getHourlyCallStatusChart($dateinfo,$skill_id)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $date1 = date_create($dateinfo->sdate);
        $date2 = date_create($dateinfo->edate);
        $interval =  $interval = date_diff($date1, $date2);
        $interval = $interval->format('%a') + 1;

        $sql  = "SELECT  shour, (SUM(calls_offered) / {$interval}) as call_offered, ";
        $sql .= " (SUM(calls_answerd) / {$interval}) as call_answered, (SUM(calls_abandoned)  / {$interval} ) as call_abandoned ";
        $sql .= " FROM rt_skill_call_summary ";
        $sql .= !empty($dateinfo) ? " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' " : "";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id = '{$skill_id}' " : "";
        $sql .= " GROUP BY shour ORDER BY shour";

        return $this->getDB()->query($sql);
    }

    function numHourlyServiceLevelStatus($dateinfo, $skill_id)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT COUNT(DISTINCT(sdate)) AS total FROM rt_skill_call_summary ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($dateinfo->hour) && $dateinfo->hour != "*" ? " AND shour = {$dateinfo->hour} " : "";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id = '{$skill_id}' " : "";
        $sql .=  "  group by sdate ";
        $result =  $this->getDB()->query($sql);

        return !empty($result[0]->total) ? $result[0]->total : 0;
    }

    function getHourlyServiceLevelStatus($dateinfo,$skill_id, $offset=0,$limit=20)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT sdate, shour, SUM(calls_offered) AS calls_offered, SUM(calls_answerd) AS calls_answerd, ".
            "SUM(abandoned_after_threshold) AS abandoned_after_threshold, SUM(answerd_within_service_level) AS answerd_within_service_level FROM rt_skill_call_summary ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($dateinfo->hour) && $dateinfo->hour != "*" ? " AND shour = {$dateinfo->hour} " : "";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id = '{$skill_id}' " : "";
        $sql .=  "  group by sdate,shour ";
        //if ($limit > 0) $sql .= " LIMIT {$limit} OFFSET {$offset}";

        return $this->getDB()->query($sql);
    }

    function getDailySkillSummary($start_date,$skill_id)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_daily_skill_summary';

        $sql = "SELECT * FROM {$table} ";
        $sql .= " WHERE sdate = '{$start_date}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= $skill_id != "*" ?  " AND skill_id ='{$skill_id}' " : "";

        return $this->getDB()->query($sql);
    }

    function getAutoDialCallsPerDispositionChart($dateinfo)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_autodial_disposition_summary';

        $sql = "SELECT * FROM {$table} ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($dateinfo->skill_id) && $dateinfo->skill_id != "*" ? " AND skill_id = '{$dateinfo->skill_id}' " : " ";

        $response =  $this->getDB()->query($sql);
        return $response;
    }


    public function get_shift_key_value($key='')
    {
        $shift_list = [];
        $sql = "SELECT shift_code,label FROM shift_profile WHERE day_overlap = 0";
        if (!empty($key))
        {
            $sql .= " WHERE shift_code = '{$key}' ";
        }
        $shifts = $this->getDB()->query($sql);
        if (empty($shifts))
        {
            return [];
        }

        foreach ($shifts as $shift)
        {
            $shift_list[$shift->shift_code] = $shift->label;
        }

        return $shift_list;
    }

    function numAutoDialDispositionCall($dateinfo,$skill_id)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_autodial_disposition_summary';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) ?  " AND skill_id ='{$skill_id}' " : "";

        $record = $this->getDB()->query($sql);
        return $record[0]->total_record;
    }

    function getAutoDialDispositionCall($dateinfo,$skill_id,$offset=0,$limit=20)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_autodial_disposition_summary';

        $sql = "SELECT * FROM {$table}";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) ?  " AND skill_id ='{$skill_id}' " : "";
        if ($limit > 0) $sql .= " LIMIT $limit OFFSET $offset";

        return $this->getDB()->query($sql);
    }

    public function getDailyPDCallSuccessFailurSummary($start_date,$end_date)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";
        $table = 'log_cdr_autodial';

        /*
                $sql = "select DATE_FORMAT(tstamp, '%Y-%m-%d') AS call_date, ";
                $sql .= " SUM(IF(disposition = 200, 1,0)) AS success_call, ";
                $sql .= " SUM(IF(disposition != 200, 1,0)) AS fail_call ";
                $sql .= " FROM {$table} ";
                $sql .= " WHERE DATE(start_time) BETWEEN '{$start_date}' AND '{$end_date}' ";
                $sql .= " GROUP BY DATE_FORMAT(tstamp, '%Y-%m-%d')";
        */
        $table = 'rt_autodial_disposition_summary';
        $sql = "SELECT sdate AS call_date, SUM(call_count) AS attempted_call, SUM(IF(disposition_id = 200, call_count, 0)) AS success_call FROM {$table} ";
        $sql .= " WHERE sdate BETWEEN '{$start_date}' AND '{$end_date}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= " GROUP BY sdate";
        return $this->getDB()->query($sql);
    }

    public function numQueueWaitTime($dateinfo,$skill_id)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";
        $table = 'rt_daily_skill_summary';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) ?  " AND skill_id ='{$skill_id}' " : "";
        //GPrint($sql);die;
        $record = $this->getDB()->query($sql);
        return $record[0]->total_record;
    }
    public function getQueueWaitTime($dateinfo,$skill_id,$offset=0,$limit=20)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";
        $table = 'rt_daily_skill_summary';

        $search_item = " sdate,skill_id, MAX(max_hold_time_in_queue) AS max_hold_time_in_queue ,MIN(IF(min_hold_time_in_queue = '0', NULL, min_hold_time_in_queue)) AS min_hold_time_in_queue , SUM(hold_time_in_queue)/SUM(calls_offered) as avg_queue_wait_time ";

        $sql = "SELECT ". $search_item ." FROM {$table}";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) ? " AND skill_id ='{$skill_id}' " : "";
        $sql .= "GROUP BY sdate,skill_id ORDER BY sdate";
        if ($limit > 0) $sql .= " LIMIT $limit OFFSET $offset";
        //GPrint($sql);die;
        return $this->getDB()->query($sql);
    }

    public function getHourlyQueueWaitTime($dateinfo,$skill_id,$avg_condition,$offset=0,$limit=20)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";
        $table = 'rt_skill_call_summary';

        if (!empty($avg_condition) && $avg_condition == "CO"){
            $search_item = " sdate,skill_id, shour,SUM(hold_time_in_queue) AS hold_time_in_queue, SUM(hold_time_in_queue)/SUM(calls_offered) AS avg_time ";
        }else{
            //$search_item = " sdate,skill_id, shour,SUM(hold_time_in_queue) AS hold_time_in_queue, SUM(hold_time_in_queue)/SUM(calls_answerd) AS avg_time ";
            $search_item = " sdate,skill_id, shour,SUM(hold_time_in_queue) AS hold_time_in_queue, (SUM(hold_time_in_queue)-SUM(abandon_duration))/SUM(calls_answerd) AS avg_time ";
        }

        $sql = "SELECT ". $search_item ." FROM {$table}";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        //$sql .= !empty($shour) && $shour != "*" ?  " AND shour ='{$shour}' " : "";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id && $skill_id != "*") ?" AND skill_id='{$skill_id}' ": "";

        $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        //$sql .= " AND language = 'BN' ";

        $sql .= " GROUP BY sdate,skill_id,shour";
        //if ($limit > 0) $sql .= " LIMIT $limit OFFSET $offset";
        //GPrint($sql);die;
        return $this->getDB()->query($sql);
    }

    public function numCallVolume($dateinfo,$skill_id)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_daily_skill_summary';

        $query = "SELECT COUNT(*) AS total_record FROM {$table} ";
        $query .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $query .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id='{$skill_id}' " : "";
        $query .= " GROUP BY sdate,skill_id ";

        $response = $this->getDB()->query($query);
        return count($response);
        //return $response[0]->total_record;
    }

    public function getCallVolume($dateinfo,$skill_id, $offset = 0, $limit = 0)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_daily_skill_summary';

        $query = "SELECT * FROM {$table} ";
        $query .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $query .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id='{$skill_id}' " : "";
        $query .= " GROUP BY sdate, skill_id ";

        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";
        return $this->getDB()->query($query);
    }

    public function getCallVolumeChart($paremeters)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $query = "SELECT skill_id, SUM(calls_offered) AS calls_offered, SUM(calls_repeated) AS calls_repeated, ";
        $query .= " SUM(calls_answerd) AS calls_answerd, SUM(answerd_within_service_level) AS answerd_within_service_level ";
        $query .= " FROM rt_daily_skill_summary ";
        $query .= " WHERE sdate BETWEEN '{$paremeters->sdate}' AND '{$paremeters->edate}' ";
        $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $query .= !empty($paremeters->skill_id) && $paremeters->skill_id != "*" ? " AND skill_id='{$paremeters->skill_id}' " : "";
        $query .= " GROUP BY skill_id ";

        return $this->getDB()->query($query);
    }

    public function numAgentOutboundActivity($dateinfo,$agent_id)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_agent_shift_summary';

        $query = "SELECT COUNT(*) AS total_record FROM {$table} ";
        $query .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $query .= !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id = '{$partition_id}') " : "";
        $query .= !empty($agent_id) && $agent_id != "*" ? " AND agent_id = '{$agent_id}' " : "";
        $query .= " GROUP BY sdate ";

        $response = $this->getDB()->query($query);
        return $response[0]->total_record;
    }

    public function getAgentOutboundActivity($dateinfo,$agent_id,$offset = 0, $limit = 0)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_agent_shift_summary';

        $query = "SELECT sdate, SUM(CASE WHEN calls_out_attempt > 0 THEN 1 ELSE 0 END) AS agent_worked, SUM(calls_out_attempt) AS total_initiated, ";
        $query .= " SUM(calls_out_reached) AS total_reached FROM {$table} ";
        $query .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $query .= !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id = '{$partition_id}') " : "";
        $query .= !empty($agent_id) && $agent_id != "*" ? " AND agent_id = '{$agent_id}' " : "";
        $query .= " GROUP BY sdate ";

        return $this->getDB()->query($query);
    }

    function getAbandonedCallsChart($dateinfo)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_daily_skill_summary ';

        $sql = "SELECT sdate,SUM(calls_offered) AS calls_offered, SUM(calls_abandoned) AS calls_abandoned ";
        $sql .= " FROM {$table} ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($dateinfo->skill_id) && $dateinfo->skill_id != "*" ? " AND skill_id = '{$dateinfo->skill_id}' " : " ";
        $sql .= " GROUP BY sdate";

        $response =  $this->getDB()->query($sql);
        return $response;
    }

    function numAbandonedCallReport($sum_date="N",$skill_id='', $dateinfo)
    {
        if ($dateinfo->ststamp > $dateinfo->etstamp) {
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = "rt_skill_call_summary";

        $group_by = "";
        $group_by .= $sum_date == "N" ? "sdate,{$table}.skill_id" : "{$table}.skill_id";


        $sql = "SELECT COUNT(*) AS total_record FROM {$table} ";
        $sql .= " WHERE {$table}.sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= " AND {$table}.shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= !empty($partition_id) ? " AND {$table}.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) ? " AND {$table}.skill_id = '$skill_id' ":"  ";
        $sql .= " GROUP BY {$group_by}";

        $response =  $this->getDB()->query($sql);
        return !empty($response) ? count($response) : 0;
    }



    function getAbandonedCallReport($sum_date="N",$skill_id='', $dateinfo, $offset=0, $limit=20)
    {
        if ($dateinfo->ststamp > $dateinfo->etstamp) {
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";


        $table = "rt_skill_call_summary";

        $select_extra = "";
        $select_extra .= $sum_date == "N" ? "sdate," : "";

        $group_by = "";
        $group_by .= $sum_date == "N" ? "sdate,{$table}.skill_id,shour" : "{$table}.skill_id,shour";



        $select = " shour,skill.skill_id,skill_name,SUM(calls_offered) AS calls_offered, SUM(calls_abandoned) AS calls_abandoned, ((calls_abandoned/calls_offered)*100) AS abandoned_percentage ";

        $select = !empty($select_extra) ? $select_extra.$select : $select;

        $sql = "SELECT {$select} FROM {$table}";
        $sql .= " left JOIN skill ON {$table}.skill_id =  skill.skill_id ";
        $sql .= " WHERE {$table}.sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= " AND {$table}.shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= !empty($partition_id) ? " AND {$table}.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id)?" AND {$table}.skill_id = '$skill_id' " : "";
        $sql .= " GROUP BY {$group_by} "; //"LIMIT $limit OFFSET $offset";

        return $this->getDB()->query($sql);
    }



    function numDailyAbandonedCallReport($skill_id='', $dateinfo)
    {
        if ($dateinfo->ststamp > $dateinfo->etstamp) {
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $select = " sdate,skill_name,SUM(calls_offered) AS calls_offered, SUM(calls_abandoned) AS calls_abandoned, ((calls_abandoned/calls_offered)*100) AS abandoned_percentage ";

        $sql = "SELECT $select";
        $sql .= " FROM rt_daily_skill_summary ";
        $sql .= " LEFT JOIN skill ON skill.skill_id = rt_daily_skill_summary.skill_id ";
        $sql .= !empty($partition_id) ? " AND rt_daily_skill_summary.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id)?" WHERE rt_daily_skill_summary.skill_id = '$skill_id' AND ":" WHERE ";
        $sql .= " rt_daily_skill_summary.sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= " GROUP BY sdate,rt_daily_skill_summary.skill_id ";

        return count($this->getDB()->query($sql));
    }



    function getDailyAbandonedCallReport($skill_id='', $dateinfo, $offset=0, $limit=20)
    {
        if ($dateinfo->ststamp > $dateinfo->etstamp) {
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $select = " sdate,skill_name,SUM(calls_offered) AS calls_offered, SUM(calls_abandoned) AS calls_abandoned, ((calls_abandoned/calls_offered)*100) AS abandoned_percentage ";
        $sql = "SELECT $select";
        $sql .= " FROM rt_daily_skill_summary ";
        $sql .= " INNER JOIN skill ON skill.skill_id = rt_daily_skill_summary.skill_id ";
        $sql .= !empty($partition_id) ? " AND rt_daily_skill_summary.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id)?" WHERE rt_daily_skill_summary.skill_id = '$skill_id' AND ":" WHERE ";
        $sql .= " rt_daily_skill_summary.sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= " GROUP BY sdate,rt_daily_skill_summary.skill_id LIMIT $limit OFFSET $offset";
        return $this->getDB()->query($sql);
    }



    function getHourlyAbandonedCallReport($skill_id='', $dateinfo)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";


        $sql = "SELECT shour,SUM(calls_abandoned) AS calls_abandoned,SUM(calls_offered) AS calls_offered";
        $sql .= " FROM rt_skill_call_summary ";
        $sql .= " WHERE rt_skill_call_summary.sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND rt_skill_call_summary.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) && $skill_id != "*" ? " AND rt_skill_call_summary.skill_id = '$skill_id' ":"";
        $sql .= " GROUP BY shour";

        return $this->getDB()->query($sql);
    }

    function getQueueWaitTimeForChart($dateinfo,$skill_id){
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_skill_call_summary';
        $search_item = " skill_id, MAX(max_hold_time_in_queue) AS max_hold_time_in_queue ";

        $sql = "SELECT ". $search_item ." FROM {$table}";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id='{$skill_id}' ": "";
        $sql .= " GROUP BY skill_id";
        return $this->getDB()->query($sql);
    }

    function getHourlyQueueWaitTimeForChart($dateinfo,$skill_id){
        /*$sql = " SELECT  skill_id, shour,SUM(hold_time_in_queue) AS hold_time_in_queue, SUM(hold_time_in_queue)/SUM(calls_offered) AS avg_time ";
        $sql .= " FROM rt_skill_call_summary ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($skill_id) ? " AND  skill_id = '{$skill_id}' " : "";
        $sql .= " GROUP BY skill_id,shour ";
        //GPrint($sql);die;
        return $this->getDB()->query($sql);*/

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";
        $table = 'rt_skill_call_summary';
        $search_item = " skill_name, shour,SUM(hold_time_in_queue) AS hold_time_in_queue, SUM(hold_time_in_queue)/SUM(calls_offered) AS avg_time ";
        $sql = "SELECT ". $search_item ." FROM {$table}";

        $sql .= " LEFT JOIN skill ON skill.skill_id = rt_skill_call_summary.skill_id ";

        $sql .= " WHERE ".$table.".sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND ".$table.".skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) ?" AND ".$table.".skill_id='{$skill_id}' ": "";
        $sql .= " GROUP BY ".$table.".skill_id, ".$table.".shour";
//        GPrint($sql);die;
        return $this->getDB()->query($sql);
    }



    function numAverageHandlingTime($skill_id='', $dateinfo)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT COUNT(*) AS total_record ";
        $sql .= " FROM rt_daily_skill_summary ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id = '{$skill_id}' " : " ";
        $record = $this->getDB()->query($sql);
        return $record[0]->total_record;
    }

    function getAverageHandlingTime($skill_id='', $dateinfo,$offset=0, $limit=20)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT sdate,skill_id,SUM(service_duration) AS service_duration,SUM(hold_time) AS hold_time,SUM(acw_time) AS acw_time,SUM(calls_answerd) AS calls_answerd FROM rt_daily_skill_summary ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id = '{$skill_id}' " : " ";
        $sql .= " GROUP BY sdate, skill_id ";
        $sql .= $limit > 0 ? " LIMIT $limit OFFSET $offset " : "";

        return $this->getDB()->query($sql);
    }

    function getDailyServiceLevelChart($dateinfo)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = 'rt_skill_call_summary ';

        $sql = "SELECT skill_id,shour, SUM(calls_offered) AS calls_offered, SUM(calls_answerd) AS calls_answerd, SUM(answerd_within_service_level) AS answerd_within_service_level, ";
        $sql .= " SUM(calls_abandoned) AS calls_abandoned, SUM(abandoned_after_threshold) AS abandoned_after_threshold FROM {$table} ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($dateinfo->skill_id) && $dateinfo->skill_id != "*" ? " AND skill_id = '{$dateinfo->skill_id}' " : " ";
        $sql .= " GROUP BY sdate,skill_id,shour";

        $response =  $this->getDB()->query($sql);
        return $response;
    }


    function getDispositionTreeOptions($root='')
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $left = 0;
        $rgt = 0;

        if (empty($root)) {
            $left = 1;
            $sql = 'SELECT MAX(rgt) AS max_rgt FROM skill_crm_disposition_code ';
            $sql .= !empty($partition_id) ? " WHERE partition_id='{$partition_id}' " : "";

            $result = $this->getDB()->query($sql);
            if (is_array($result)) $rgt = $result[0]->max_rgt;
        } else {
            $sql = "SELECT lft, rgt FROM skill_crm_disposition_code WHERE disposition_id='$root' ";
            $sql .= !empty($partition_id) ? " AND partition_id='{$partition_id}' " : "";

            $result = $this->getDB()->query($sql);
            if (is_array($result)) {
                $left = $result[0]->lft;
                $rgt = $result[0]->rgt;
            }
        }

        $right = array();
        $options = array();
        $sql = "SELECT disposition_id, title, lft, rgt FROM skill_crm_disposition_code WHERE ";
        $sql .= !empty($partition_id) ? " partition_id='{$partition_id}' AND " : " ";
        $sql .= " lft BETWEEN '$left' AND '$rgt' ORDER BY lft ASC";
        $result = $this->getDB()->query($sql);
        //echo $sql;

        if (is_array($result)) {
            foreach ($result as $row) {
                if (count($right) > 0) {
                    while ($right[count($right)-1]<$row->rgt) {
                        array_pop($right);
                        if (count($right) == 0) break;
                    }
                }

                $path = $this->getDispositionPath($row->disposition_id);
                $options[$row->disposition_id] = !empty($path) ? $path." -> ".$row->title : $row->title;

                $right[] = $row->rgt;
            }
        }

        return $options;
    }


    function getDispositionPathArray($child)
    {
        $path = array();
        if (empty($child)) return $path;

        $result_d = $this->getDB()->query("SELECT lft, rgt, title FROM skill_crm_disposition_code WHERE disposition_id='$child'");
        if (is_array($result_d)) {
            $left = $result_d[0]->lft;
            $rgt = $result_d[0]->rgt;
            $sql = "SELECT disposition_id, title FROM skill_crm_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) {
                //$path = '';
                foreach ($result as $row) {
                    //if (!empty($path)) $path .= ' -> ';
                    //$path .= $row->title;
                    //array_unshift($path, array($row->disposition_id, $row->title));
                    $path[] = array($row->disposition_id, $row->title);
                }
                //return $path;
            }
            $path[] = array($child, $result_d[0]->title);
        }

        return $path;
    }

    function getDispositionPath($child)
    {
        if (empty($child)) return 'Not Selected';

        $result = $this->getDB()->query("SELECT lft, rgt FROM skill_crm_disposition_code WHERE disposition_id='$child'");
        if (is_array($result)) {
            $left = $result[0]->lft;
            $rgt = $result[0]->rgt;
            $sql = "SELECT title FROM skill_crm_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
            $result = $this->getDB()->query($sql);
            $path = '';
            if (is_array($result)) {
                foreach ($result as $row) {
                    if (!empty($path)) $path .= ' -> ';
                    $path .= $row->title;
                }
                return $path;
            }
            return $path;
        }

        return 'Invalid Parent';
    }

    public function getServiceLevel($dateInfo,$date_average="N",$hour_average="N",$skill_id='*')
    {
        $group_by = "";
        $order_by = "";

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";


        if ($date_average == "N" && $hour_average == "N"){
            $group_by = "GROUP BY sdate, shour ";
            $order_by = "ORDER BY sdate, shour ";
        }elseif ($date_average == "Y" && $hour_average == "N"){
            $group_by = "GROUP BY shour";
            $order_by = "ORDER BY shour";;
        }elseif ($date_average == "N" && $hour_average == "Y"){
            $group_by = "GROUP BY sdate";
            $order_by = "ORDER BY sdate";
        }else{
            $group_by = "";
            $order_by = "";
        }


        $sql  = "SELECT SUM(calls_offered) AS calls_offered, SUM(calls_answerd) AS calls_answerd, ";
        $sql .= "SUM(answerd_within_service_level) AS answerd_within_service_level, ";
        $sql .= "SUM(abandoned_after_threshold) AS abandoned_after_threshold ";
        $sql .= $date_average == "N" ? ",sdate " : "";
        $sql .= $hour_average == "N" ? ",shour " : "";
        $sql .= " FROM rt_skill_call_summary ";
        $sql .= " WHERE sdate BETWEEN '{$dateInfo->sdate}' AND '{$dateInfo->edate}' ";
        $sql.= " AND shour BETWEEN '{$dateInfo->stime}' AND '{$dateInfo->etime}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id ='{$skill_id}' " : "";
        $sql .= "{$group_by} {$order_by}";

        return $this->getDB()->query($sql);
    }

    public function agentIdleTime($agent_id, $dateInfo)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT sdate, SUM(available_time) AS available_time, SUM(calls_in_time) AS calls_in_time, ";
        $sql .= " SUM(calls_out_time) AS calls_out_time ";
        $sql .= " FROM rt_agent_shift_summary ";
        $sql .= " WHERE sdate BETWEEN '{$dateInfo->sdate}' AND '{$dateInfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND agent_id IN( Select agent_id from agents where partition_id = '{$partition_id}' ) " : "";
        $sql .= !empty($agent_id) && $agent_id != "*" ? " AND agent_id ='{$agent_id}' " : "";
        $sql .= " GROUP BY sdate";

        return $this->getDB()->query($sql);
    }


    function numAgentCallHistory($agent_id='', $dateinfo)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT COUNT(*) AS total_record ";
        $sql .= " FROM rt_agent_shift_summary ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($agent_id) && $agent_id != "*" ? " AND agent_id = '{$agent_id}' " : " ";
        $record = $this->getDB()->query($sql);
        return $record[0]->total_record;
    }


    function getAgentCallHistory($agent_id='', $dateinfo)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql  = "SELECT sdate,agent_id, shift_code, calls_in_ans,calls_out_attempt, calls_out_reached, ring_lt_6_count, ring_6_to_10_count, ring_gt_11_count ";
        $sql .= "  FROM rt_agent_shift_summary ";
        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= !empty($agent_id) && $agent_id != "*" ? " AND agent_id = '{$agent_id}' " : " ";
        $sql .= "ORDER BY sdate DESC";
        return $this->getDB()->query($sql);
    }

    function numCategorySummaryReport($dateinfo, $skill_ids, $report_type = REPORT_DAILY)
    {
        $table = 'rt_skill_call_summary';
        $group_by = '';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";

        if($report_type == REPORT_YEARLY){
            $sql = "SELECT YEAR(sdate) as syear, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY syear, skill_id ";
        }elseif($report_type == REPORT_QUARTERLY ){
            $sql = "SELECT QUARTER(sdate) as quarter_no, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY quarter_no, skill_id ";
        }elseif($report_type == REPORT_MONTHLY ){
            $sql = "SELECT MONTHNAME(sdate) as smonth, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY smonth, skill_id ";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
        }elseif($report_type == REPORT_HALF_HOURLY){
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
        }

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        if(empty($report_type) || $report_type == REPORT_15_MIN_INV)
            return $record[0]->total_record;
        // Gprint($record);

        return (!empty($record)) ? count($record) : 0;

    }

    function getCategorySummaryReport($dateinfo, $category_skill_ids, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE)
    {
        $table = 'rt_skill_call_summary';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";

        if($report_type == REPORT_YEARLY){
            $selected_report_type = ", YEAR(sdate) as syear ";
            $group_by = " GROUP BY syear, skill_id ";
            $order_by = " ORDER BY syear ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_QUARTERLY ){
            $selected_report_type = ", QUARTER(sdate) as quarter_no ";
            $group_by = " GROUP BY quarter_no, skill_id ";
            $order_by = " ORDER BY quarter_no ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_MONTHLY ){
            $selected_report_type = ", MONTHNAME(sdate) as smonth ";
            $group_by = " GROUP BY smonth, skill_id ";
            $order_by = " ORDER BY smonth ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
            $order_by = " ORDER BY sdate ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HALF_HOURLY ){
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }else{
            // $sql = "SELECT * FROM $table";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }

        $sql = "SELECT sdate, shour,sminute, skill_id, SUM(rgb_call_count) as rgb_call_count, SUM(calls_offered) as calls_offered, ";
        $sql .= "SUM(calls_answerd) as calls_answerd, SUM(calls_abandoned) as calls_abandoned, ";
        $sql .= "SUM(ans_lte_10_count) as ans_lte_10_count, SUM(ans_lte_20_count) as ans_lte_20_count, SUM(ans_lte_30_count) as ans_lte_30_count, ";
        $sql .= "SUM(ans_lte_60_count) as ans_lte_60_count, SUM(ans_lte_90_count) as ans_lte_90_count, SUM(ans_lte_120_count) as ans_lte_120_count, ";
        $sql .= "SUM(ans_gt_120_count) as ans_gt_120_count, SUM(abd_lte_10_count) as abd_lte_10_count, SUM(abd_lte_20_count) as abd_lte_20_count, ";
        $sql .= "SUM(abd_lte_30_count) as abd_lte_30_count, SUM(abd_lte_60_count) as abd_lte_60_count, SUM(abd_lte_90_count) as abd_lte_90_count, ";
        $sql .= "SUM(abd_lte_120_count) as abd_lte_120_count, SUM(abd_gt_120_count) as abd_gt_120_count, SUM(ring_time) as ring_time,  ";
        $sql .= "SUM(service_duration) as service_duration, SUM(wrap_up_time) as wrap_up_time, SUM(agent_hold_time) as agent_hold_time, ";
        $sql .= "SUM(fcr_call_count) as fcr_call_count, SUM(short_call_count) as short_call_count, SUM(wrap_up_call_count) as wrap_up_call_count, ";
        $sql .= "SUM(repeat_1_count) as repeat_1_count, SUM(agent_hangup_count) as agent_hangup_count ";
        if(!empty($selected_report_type))
            $sql .= $selected_report_type;
        $sql .= "FROM {$table} ";

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $sql .= (!empty($category_skill_ids) && $category_skill_ids!='*') ? " AND skill_id IN('{$category_skill_ids}') " : "";

        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;

        return $this->getDB()->query($sql);
    }

    function numCategoriesDetailsReport($query_param)
    {
        if($query_param['dateinfo']->sdate > $query_param['dateinfo']->edate){
            return 0;
        }
        $sdate = date('Y-m-d H:i:s', $query_param['dateinfo']->ststamp);
        $edate = date('Y-m-d H:i:s', $query_param['dateinfo']->etstamp);
        $table = 'log_skill_inbound';
        $group_by = '';
        $join = '';
        $disposition_cond = '';

        if(isset($query_param['disposition_id']) && !empty($query_param['disposition_id'])){
            $scdl_sdate = strtotime("-1 hour", $query_param['dateinfo']->ststamp);
            $scdl_edate = strtotime("+1 hour", $query_param['dateinfo']->etstamp);
            $join = "LEFT JOIN skill_crm_disposition_log as scdl ON scdl.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND lsi.callid=scdl.callid ";

            $disposition_cond = " AND scdl.disposition_id='{$query_param['disposition_id']}' ";
            $group_by = "group by lsi.callid";
        }

        $sql = "SELECT COUNT(lsi.call_start_time) AS total_record FROM {$table} as lsi ";
        $sql .= $join;
        $sql .= " WHERE lsi.call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

        // $sql .= (!empty($qtype )) ? " AND skill.qtype='{$qtype}' " : "";
        $sql .= (!empty($query_param['skill_ids']) && $query_param['skill_ids']!='*') ? " AND lsi.skill_id IN('{$query_param['skill_ids']}') " : "";
        $sql .= (!empty($query_param['skill_type']) && $query_param['skill_type']!='*') ? " AND lsi.call_type='{$query_param['skill_type']}' " : "";
        $sql .= (!empty($query_param['hangup_initiator']) && $query_param['hangup_initiator']!='*') ? " AND lsi.disc_party='{$query_param['hangup_initiator']}' " : ""; 
        $sql .= $disposition_cond;
        $sql .= $group_by;
        // echo $sql;
        // die();

        $record = $this->getDB()->query($sql);

        if(isset($query_param['disposition_id']) && !empty($query_param['disposition_id'])){
            return count($record);
        }
        return $record[0]->total_record;
    }

    function getCategoriesDetailsReport($query_param, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE) {
        if($query_param['dateinfo']->sdate > $query_param['dateinfo']->edate){
            return [];
        }
        $sdate = date('Y-m-d H:i:s', $query_param['dateinfo']->ststamp);
        $edate = date('Y-m-d H:i:s', $query_param['dateinfo']->etstamp);
        $table = 'log_skill_inbound';
        $group_by = '';
        $order_by = " ORDER BY lsi.call_start_time ASC, skill_id ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        $disposition_cond = "";

        $sql = "SELECT lsi.call_start_time, lsi.cli, lsi.skill_id, lsi.status, lsi.ring_time, lsi.service_time, lsi.agent_hold_time, lsi.repeated_call, lsi.ice_feedback, lsi.disc_party, lsi.hold_in_q, lsi.wrap_up_time, lsi.disposition_count, lsi.agent_id, lsi.callid, lsi.transfer_tag ";
        if(!$query_param['multiple_wrap_up']){
            // $sql .= ', scdc.title, scdc.disposition_type, scdc.disposition_id ';
            $sql .= ', lsi.disposition_id ';
        }else{
            $sql .= ', scdl.disposition_id ';
        }

        if($query_param['isSum']){
            $sql = "SELECT SUM(lsi.ring_time) as ring_time, SUM(lsi.service_time) as service_time, SUM(lsi.agent_hold_time) as agent_hold_time, SUM(lsi.repeated_call) as repeated_call, SUM(lsi.hold_in_q) as hold_in_q, SUM(lsi.wrap_up_time) as wrap_up_time, SUM(lsi.disposition_count) as disposition_count, SUM(IF(lsi.status='S', 1,0)) as call_ans, SUM(IF(lsi.transfer_tag !='', 1,0)) as transfer_tag ";
        }

        $sql .= "FROM {$table} as lsi ";
        if($query_param['multiple_wrap_up'] || (isset($query_param['disposition_id']) && !empty($query_param['disposition_id']))){
            $scdl_sdate = strtotime("-1 hour", $query_param['dateinfo']->ststamp);
            $scdl_edate = strtotime("+1 hour", $query_param['dateinfo']->etstamp);
            $sql .= "LEFT JOIN skill_crm_disposition_log as scdl ON scdl.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND lsi.callid=scdl.callid ";

            if(!empty($query_param['disposition_id']))
                $disposition_cond = " AND scdl.disposition_id='{$query_param['disposition_id']}' ";
        }
        $sql .= " WHERE lsi.call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

        $sql .= (!empty($query_param['skill_ids']) && $query_param['skill_ids']!='*') ? " AND lsi.skill_id IN('{$query_param['skill_ids']}') " : "";
        $sql .= (!empty($query_param['skill_type']) && $query_param['skill_type']!='*') ? " AND lsi.call_type='{$query_param['skill_type']}' " : "";
        $sql .= (!empty($query_param['hangup_initiator']) && $query_param['hangup_initiator']!='*') ? " AND lsi.disc_party='{$query_param['hangup_initiator']}' " : "";
        $sql .= $disposition_cond;
        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    function numSkillSetSummaryWithCategoryReport($dateinfo,$skill_ids, $report_type = REPORT_DAILY)
    {
        $table = 'rt_skill_call_summary';
        $group_by = '';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";

        if($report_type == REPORT_YEARLY){
            $sql = "SELECT YEAR(sdate) as syear, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY syear, skill_id ";
        }elseif($report_type == REPORT_QUARTERLY ){
            $sql = "SELECT QUARTER(sdate) as quarter_no, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY quarter_no, skill_id ";
        }elseif($report_type == REPORT_MONTHLY ){
            $sql = "SELECT MONTHNAME(sdate) as smonth, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY smonth, skill_id ";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
        }elseif($report_type == REPORT_HALF_HOURLY){
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
        }

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        if(empty($report_type) || $report_type == REPORT_15_MIN_INV)
            return $record[0]->total_record;
        // Gprint($record);

        return (!empty($record)) ? count($record) : 0;

    }

    function getSkillSetSummaryWithCategoryReport($dateinfo, $category_skill_ids, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE)
    {
        $table = 'rt_skill_call_summary';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";

        if($report_type == REPORT_YEARLY){
            $selected_report_type = ", YEAR(sdate) as syear ";
            $group_by = " GROUP BY syear, skill_id ";
            $order_by = " ORDER BY syear ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_QUARTERLY ){
            $selected_report_type = ", QUARTER(sdate) as quarter_no ";
            $group_by = " GROUP BY quarter_no, skill_id ";
            $order_by = " ORDER BY quarter_no ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_MONTHLY ){
            $selected_report_type = ", MONTHNAME(sdate) as smonth ";
            $group_by = " GROUP BY smonth, skill_id ";
            $order_by = " ORDER BY smonth ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
            $order_by = " ORDER BY sdate ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HALF_HOURLY ){
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }else{
            // $sql = "SELECT * FROM $table";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }

        $sql = "SELECT sdate, shour,sminute, skill_id, SUM(rgb_call_count) as rgb_call_count, SUM(calls_offered) as calls_offered, ";
        $sql .= "SUM(calls_answerd) as calls_answerd, SUM(calls_abandoned) as calls_abandoned, ";
        $sql .= "SUM(ans_lte_10_count) as ans_lte_10_count, SUM(ans_lte_20_count) as ans_lte_20_count, SUM(ans_lte_30_count) as ans_lte_30_count, ";
        $sql .= "SUM(ans_lte_60_count) as ans_lte_60_count, SUM(ans_lte_90_count) as ans_lte_90_count, SUM(ans_lte_120_count) as ans_lte_120_count, ";
        $sql .= "SUM(ans_gt_120_count) as ans_gt_120_count, SUM(abd_lte_10_count) as abd_lte_10_count, SUM(abd_lte_20_count) as abd_lte_20_count, ";
        $sql .= "SUM(abd_lte_30_count) as abd_lte_30_count, SUM(abd_lte_60_count) as abd_lte_60_count, SUM(abd_lte_90_count) as abd_lte_90_count, ";
        $sql .= "SUM(abd_lte_120_count) as abd_lte_120_count, SUM(abd_gt_120_count) as abd_gt_120_count, SUM(ring_time) as ring_time,  ";
        $sql .= "SUM(service_duration) as service_duration, SUM(wrap_up_time) as wrap_up_time, SUM(agent_hold_time) as agent_hold_time, ";
        $sql .= "SUM(fcr_call_count) as fcr_call_count, SUM(short_call_count) as short_call_count, SUM(wrap_up_call_count) as wrap_up_call_count, ";
        $sql .= "SUM(repeat_1_count) as repeat_1_count, SUM(agent_hangup_count) as agent_hangup_count ";
        if(!empty($selected_report_type))
            $sql .= $selected_report_type;
        $sql .= "FROM {$table} ";

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $sql .= (!empty($category_skill_ids) && $category_skill_ids!='*') ? " AND skill_id IN('{$category_skill_ids}') " : "";

        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;

        return $this->getDB()->query($sql);
    }
    ////////Arif////////
    function  numOutboundSummary($from_date, $to_date, $agent_id, $skill_ids){
        if ($from_date > $to_date) {
            return 0;
        }

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = ' log_agent_outbound_manual ';

        $sql = "SELECT DATE(start_time) as sdate";

        $sql .= " FROM $table ";
        $cond = " DATE(start_time) BETWEEN '$from_date' AND '$to_date' ";
        $cond .= !empty($agent_id) && $agent_id != "*" ? " AND agent_id = '$agent_id' " : " AND agent_id !='' ";
        $cond .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= " WHERE  $cond ";
        $sql .= !empty($partition_id) ? " AND ag.partition_id= '{$partition_id}' " : "";
        $sql .= " GROUP BY DATE(start_time), skill_id, agent_id ";
        $result = $this->getDB()->query($sql);
        // echo $sql;

        return (!empty($result)) ? count($result) : 0;

    }

    function getOutboundSummary($from_date, $to_date, $agent_id, $skill_ids, $offset = 0, $rowsPerPage = 20, $isGroupBy = true)
    {
        if ($from_date > $to_date) {
            return 0;
        }

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = ' log_agent_outbound_manual ';

        $sql = "SELECT DATE(start_time) as sdate,
               skill_id,
               agent_id,
               COUNT(is_reached) AS total_call,
               SUM(CASE WHEN is_reached = 'Y' THEN 1 ELSE 0 END) AS success_call,
               SUM(CASE WHEN is_reached = 'N' THEN 1 ELSE 0 END) AS failed_call,
               SUM(talk_time) AS talk_time, SUM(ring_time) AS ring_time, SUM(hold_time) AS hold_time, SUM(wrap_up_time) AS wrap_up_time";

        $sql .= " FROM $table ";
        $cond = " DATE(start_time) BETWEEN '$from_date' AND '$to_date' ";
        $cond .= !empty($agent_id) && $agent_id != "*" ? " AND agent_id = '$agent_id' " : " AND agent_id != '' ";
        $cond .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= " WHERE  $cond ";
        $sql .= !empty($partition_id) ? " AND ag.partition_id= '{$partition_id}' " : "";
        if($isGroupBy)
            $sql .= " GROUP BY DATE(start_time), skill_id, agent_id ";

        if($isGroupBy){
            $sql .= ($rowsPerPage > 0) ? "  LIMIT $rowsPerPage OFFSET $offset" : "";
        }
        // echo $sql;

        return $this->getDB()->query($sql);

    }
    ////////Arif////////

    public function getMsisdnReport($skill_ids=[], $dateInfo, $offset=0, $limit=20, $skill_type)
    {
        if($dateInfo->sdate > $dateInfo->edate){
            return 0;
        }
        $sdate = date('Y-m-d H:i:s', $dateInfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateInfo->etstamp);
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT log_skill_inbound.cli AS msisdn, log_skill_inbound.cli as msisdn_880, log_skill_inbound.call_start_time AS sdate ";
        $sql .= "FROM log_skill_inbound ";
        $sql .= "WHERE log_skill_inbound.call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
        $sql .= !empty($partition_id) ? " AND log_skill_inbound.agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND log_skill_inbound.skill_id IN('{$skill_ids}') " : "";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND log_skill_inbound.call_type='{$skill_type}' " : "";
        $sql .= " ORDER BY call_start_time ASC, skill_id ";
        $sql .= $limit > 0 ? " LIMIT $limit OFFSET $offset " : "";

        return $this->getDB()->query($sql);
    }

    public function numMsisdnReport($skill_ids=[], $dateInfo, $skill_type){
        if($dateInfo->sdate > $dateInfo->edate){
            return 0;
        }
        $sdate = date('Y-m-d H:i:s', $dateInfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateInfo->etstamp);

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $sql = "SELECT COUNT(*) AS total_record ";
        $sql .= "FROM log_skill_inbound ";
        $sql .= " WHERE log_skill_inbound.call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
        $sql .= !empty($partition_id) ? " AND log_skill_inbound.agent_id IN( Select agent_id from agents where partition_id= '{$partition_id}') " : "";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND log_skill_inbound.skill_id IN('{$skill_ids}') " : "";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND log_skill_inbound.call_type='{$skill_type}' " : "";

        $record = $this->getDB()->query($sql);

        return $record[0]->total_record;
    }

    public function getCallHangUpReport($dateinfo, $reportType, $date, $half, $skill_type)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        if ($reportType == REPORT_DAILY) {
            // $sql = "SELECT sdate, shour, sminute, SUM(agent_call_count) AS Agent, SUM(client_call_count) AS Subscriber, SUM(system_call_count) AS System, SUM(others_call_count) AS Others";
            // $sql .= " FROM rt_hangup_party_summary";
            // $sql .= " GROUP BY sdate";
            // $sql .= " HAVING sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
            $sql = "SELECT COUNT(call_start_time) as hang_up_count, disc_party, SUM(service_time) as service_time, DATE(call_start_time) as sdate ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";

            $sql .= "GROUP BY sdate, disc_party ";
            $sql .= "ORDER BY sdate ASC, disc_party ASC ";
        } elseif ($reportType == REPORT_15_MIN_INV) {
            // $sql = "SELECT sdate, shour, sminute, agent_call_count AS Agent, client_call_count AS Subscriber, system_call_count AS System, others_call_count AS Others";
            // $sql .= " FROM rt_hangup_party_summary";
            // $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        } elseif ($reportType == REPORT_HOURLY) {
            $sdate = $date.' 00:00:00';
            $edate = $date.' 23:59:59';
            // $sql = "SELECT sdate, shour, sminute, SUM(agent_call_count) AS Agent, SUM(client_call_count) AS Subscriber, SUM(system_call_count) AS System, SUM(others_call_count) AS Others";
            // $sql .= " FROM rt_hangup_party_summary";
            // $sql .= " WHERE sdate = '$date' ";
            // $sql .= " GROUP BY shour";
            $sql = "SELECT COUNT(call_start_time) as hang_up_count, disc_party, SUM(service_time) as service_time, DATE(call_start_time) as sdate, DATE_FORMAT(call_start_time, '%H') as shour ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";

            $sql .= "GROUP BY sdate, shour, disc_party ";
            $sql .= "ORDER BY sdate ASC, shour ASC, disc_party ASC ";
        } elseif ($reportType == REPORT_HALF_HOURLY) {
            // $sql = "SELECT sdate, shour, sminute, SUM(agent_call_count) AS Agent, SUM(client_call_count) AS Subscriber, SUM(system_call_count) AS System, SUM(others_call_count) AS Others";
            // $sql .= " FROM rt_hangup_party_summary";
            // $sql .= " WHERE sdate = '$date' ";
            // $sql .= ($half == 1) ? " AND sminute IN ('00','15')" : "";
            // $sql .= ($half == 2) ? " AND sminute IN ('30','45')" : "";
            // $sql .= " GROUP BY shour";
        } elseif ($reportType == REPORT_MONTHLY) {
            // $sql = "SELECT sdate, shour, sminute, SUM(agent_call_count) AS Agent, SUM(client_call_count) AS Subscriber, SUM(system_call_count) AS System, SUM(others_call_count) AS Others";
            // $sql .= " FROM rt_hangup_party_summary";
            // $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
            // $sql .= " GROUP BY MONTH(sdate), YEAR(sdate)";
            $sql = "SELECT COUNT(call_start_time) as hang_up_count, disc_party, SUM(service_time) as service_time, MONTHNAME(call_start_time) as smonth, YEAR(call_start_time) as syear ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";

            $sql .= "GROUP BY smonth, syear, disc_party ";
            $sql .= "ORDER BY smonth ASC, syear ASC, disc_party ASC ";
        } elseif ($reportType == REPORT_YEARLY) {
            // $sql = "SELECT sdate, shour, sminute, SUM(agent_call_count) AS Agent, SUM(client_call_count) AS Subscriber, SUM(system_call_count) AS System, SUM(others_call_count) AS Others";
            // $sql .= " FROM rt_hangup_party_summary";
            // $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
            // $sql .= " GROUP BY YEAR(sdate)";
            $sql = "SELECT COUNT(call_start_time) as hang_up_count, disc_party, SUM(service_time) as service_time, YEAR(call_start_time) as syear ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";

            $sql .= "GROUP BY syear, disc_party ";
            $sql .= "ORDER BY syear ASC , disc_party ASC ";
        } elseif ($reportType == REPORT_QUARTERLY){
            // $sql = "SELECT sdate, shour, sminute, SUM(agent_call_count) AS Agent, SUM(client_call_count) AS Subscriber, SUM(system_call_count) AS System, SUM(others_call_count) AS Others";
            // $sql .= " ,QUARTER(sdate) as quarter_no";
            // $sql .= " FROM rt_hangup_party_summary";
            // $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
            // $sql .= " GROUP BY QUARTER(sdate), YEAR(sdate)";
            $sql = "SELECT COUNT(call_start_time) as hang_up_count, disc_party, SUM(service_time) as service_time, QUARTER(call_start_time) as quarter_no, YEAR(call_start_time) as syear ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";

            $sql .= "GROUP BY quarter_no, syear, disc_party ";
            $sql .= "ORDER BY quarter_no ASC, syear ASC, disc_party ASC ";
        }
        $record = $this->getDB()->query($sql);
        // var_dump($sql);
        // var_dump($record);
        // die();

        return $record;
    }

    public function getTotalAnsCall($dateinfo, $reportType, $date, $half, $skill_type)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        if ($reportType == REPORT_DAILY) {
            $sql = "SELECT COUNT(call_start_time) as total_offer_call, DATE(call_start_time) as sdate, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY sdate";
        } elseif ($reportType == REPORT_MONTHLY) {
            $sql = "SELECT COUNT(call_start_time) as total_offer_call, MONTHNAME(call_start_time) as smonth, YEAR(call_start_time) as syear, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY smonth, syear ";
        } elseif ($reportType == REPORT_YEARLY) {
            $sql = "SELECT COUNT(call_start_time) as total_offer_call, YEAR(call_start_time) as syear, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY syear ";
        } elseif ($reportType == REPORT_QUARTERLY){
            $sql = "SELECT COUNT(call_start_time) as total_offer_call, QUARTER(call_start_time) as quarter_no, YEAR(call_start_time) as syear, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY quarter_no, syear ";
        }elseif ($reportType == REPORT_HOURLY) {
            $sdate = $date.' 00:00:00';
            $edate = $date.' 23:59:59';

            $sql = "SELECT COUNT(call_start_time) as total_offer_call, DATE(call_start_time) as sdate, DATE_FORMAT(call_start_time, '%H') as shour, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY sdate, shour";
        }
        $record = $this->getDB()->query($sql);
        // var_dump($sql);
        // var_dump($record);
        // die();

        return $record;
    }

    function getAverageWaitTime() {
        $to = date('Y-m-d', time());
        $from = date('Y-m-d',strtotime(date('Y-01-01')));
        $select = "sdate, skill_id,  SUM(hold_time_in_queue)/SUM(calls_offered) as avg_queue_wait_time, SUM(calls_abandoned)/SUM(calls_offered) AS abandoned_ratio";
        $select .= " ,SUM(answerd_within_service_level) / (SUM(calls_offered) - (SUM(calls_abandoned)-SUM(abandoned_after_threshold))) AS service_level";
        $table = "rt_daily_skill_summary";
        $sql = "SELECT $select ";
        $sql .= " FROM $table ";
        $sql .= " WHERE sdate BETWEEN '{$from}' AND '{$to}' ";
        $sql .= " GROUP BY sdate ORDER BY sdate";
//        echo $sql;die;
        return $this->getDB()->query($sql);
    }

    public function getCurrentDaySkillSummary()
    {
        $today = date("Y-m-d");
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";
        /*
        return $this->getDB()->query("SELECT ss.*, skill.skill_name FROM rt_skill_call_summary AS ss LEFT JOIN skill ON ss.skill_id = skill.skill_id WHERE sdate='{$today}' group by sdate ORDER BY ss.skill_id");*/

        $sql = "SELECT ss.skill_id,skill.skill_name, sum(service_duration) as service_duration, sum(calls_offered) as calls_offered, ";
        $sql .=" sum(calls_answerd) as calls_answerd, sum(calls_repeated) as calls_repeated, ";
        $sql .= " sum(answerd_within_service_level) AS answerd_within_service_level, sum(calls_abandoned) AS calls_abandoned, ";
        $sql .= " sum(abandoned_after_threshold) AS abandoned_after_threshold, sum(calls_in_service) AS calls_in_service, ";
        $sql .= " sum(calls_in_queue) AS calls_in_queue, sum(hold_time_in_queue) AS hold_time_in_queue, ";
        $sql .= " max(max_hold_time_in_queue) AS max_hold_time_in_queue, min(min_hold_time_in_queue) AS min_hold_time_in_queue ";
        $sql .= " FROM rt_skill_call_summary AS ss left join skill on ss.skill_id = skill.skill_id ";
        $sql .= " WHERE sdate='{$today}' ";
        $sql .= !empty($partition_id) ? " AND ss.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $sql .= " group by sdate,skill_id ";

        return $this->getDB()->query($sql);
    }

    public function getDayWiseDisposition($sdate, $edate, $skill_id, $interval='daily', $skill_type, $offset=0,$limit=0)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $groupBy = '';
        $select_date_format = '';

        $edate = date('Y-m-d', strtotime($edate)-1).' 23:59:59';
        // GPrint($edate);
        // die();

        $scdl_sdate = strtotime("-1 hour", strtotime($sdate));
        $scdl_edate = strtotime("+1 hour", strtotime($edate));
        // Gprint(date('Y-m-d H:i:s', $scdl_sdate));
        // Gprint(date('Y-m-d H:i:s', $scdl_edate));

        if ($interval == 'quarter-hourly'){
            $groupBy = "DATE(lsi.call_start_time),HOUR(lsi.call_start_time),LPAD(FLOOR(MINUTE(lsi.call_start_time)/15)*15,2,0)";
            $select_date_format = "CONCAT(DATE_FORMAT(lsi.call_start_time,'%Y-%m-%d-%H'),'-',LPAD(FLOOR(MINUTE(lsi.call_start_time)/15)*15,2,0))";
        }
        elseif ($interval == 'half-hourly'){
            $groupBy = "DATE(lsi.call_start_time),HOUR(lsi.call_start_time),LPAD(FLOOR(MINUTE(lsi.call_start_time)/30)*30,2,0)";
            $select_date_format = "CONCAT(DATE_FORMAT(lsi.call_start_time,'%Y-%m-%d-%H'),'-',LPAD(FLOOR(MINUTE(lsi.call_start_time)/30)*30,2,0))";
        }
        elseif ($interval == 'hourly'){
            $groupBy = "DATE_FORMAT(lsi.call_start_time,'%Y-%m-%d %H')";
            $select_date_format = "DATE_FORMAT(lsi.call_start_time,'%Y-%m-%d-%H:00')";
        }
        elseif ($interval == 'daily'){
            $groupBy = 'csdate';
            $select_date_format = "DATE(lsi.call_start_time)";
        }
        elseif ($interval == 'monthly'){
            $groupBy = "DATE_FORMAT(lsi.call_start_time, '%Y-%m')";
            $select_date_format = "DATE_FORMAT(lsi.call_start_time, '%M,%Y')";
        } elseif ($interval == 'quarterly'){
            $groupBy = "YEAR(lsi.call_start_time), QUARTER(lsi.call_start_time)";
            $select_date_format = "CONCAT(YEAR(lsi.call_start_time),'-', QUARTER(lsi.call_start_time))";
        }
        elseif ($interval == 'yearly'){
            $groupBy = "YEAR(lsi.call_start_time)";
            $select_date_format = "YEAR(lsi.call_start_time)";
        }

        $query  = "SELECT {$select_date_format} AS csdate, scdl.disposition_id, COUNT(*) AS call_count ";
        $query .= " FROM log_skill_inbound AS lsi ";
        $query .= " LEFT JOIN skill_crm_disposition_log as scdl ON scdl.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND scdl.callid=lsi.callid ";
        $query .= " WHERE lsi.call_start_time BETWEEN '{$sdate}' AND '{$edate}' AND lsi.status='S' ";
        $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $query .= !empty($skill_id) && $skill_id != '*' ? " AND lsi.skill_id='{$skill_id}'" : "";
        $query .= !empty($skill_type) && $skill_type != '*' ? " AND lsi.call_type='{$skill_type}'" : "";
        $query .= " GROUP BY {$groupBy}, scdl.disposition_id ";
        $query .= $limit > 0 ? " LIMIT $limit OFFSET $offset " : "";
        // echo $query;
        // die();

        return $this->getDB()->query($query);
    }

    public function getWorkCodeCountReport($sdate, $edate, $skill_id, $interval='daily', $skill_type, $offset=0,$limit=0)
    {
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $groupBy = '';
        $select_date_format = '';

        if ($interval == 'quarter-hourly'){
            $groupBy = "DATE(call_start_time),HOUR(call_start_time),LPAD(FLOOR(MINUTE(call_start_time)/15)*15,2,0), skill_id";
            $select_date_format = "CONCAT(DATE_FORMAT(call_start_time,'%d-%M-%Y_%H:'),LPAD(FLOOR(MINUTE(call_start_time)/15)*15,2,0))";

            $query  = "SELECT {$select_date_format} AS sdate, skill_id, COUNT(*) AS call_count ";
            $query .= " FROM log_skill_inbound WHERE status='S' AND DATE(call_start_time) = '{$sdate}' AND disposition_id != '' ";
            $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
            $query .= !empty($skill_id) && $skill_id != '*' ? " AND skill_id='{$skill_id}'" : "";
            $query .= !empty($skill_type) && $skill_type != '*' ? " AND call_type='{$skill_type}'" : "";
            $query .= " GROUP BY {$groupBy}";
            $query .= $limit > 0 ? " LIMIT $limit OFFSET $offset " : "";

            return $this->getDB()->query($query);
        }
        elseif ($interval == 'half-hourly'){
            $groupBy = "DATE(call_start_time),HOUR(call_start_time),LPAD(FLOOR(MINUTE(call_start_time)/30)*30,2,0), skill_id";
            $select_date_format = "CONCAT(DATE_FORMAT(call_start_time,'%d-%M-%Y_%H:'),LPAD(FLOOR(MINUTE(call_start_time)/30)*30,2,0))";

            $query  = "SELECT {$select_date_format} AS sdate, skill_id, COUNT(*) AS call_count ";
            $query .= " FROM log_skill_inbound WHERE status='S' AND DATE(call_start_time) = '{$sdate}' AND disposition_id != '' ";
            $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
            $query .= !empty($skill_id) && $skill_id != '*' ? " AND skill_id='{$skill_id}'" : "";
            $query .= !empty($skill_type) && $skill_type != '*' ? " AND call_type='{$skill_type}'" : "";
            $query .= " GROUP BY {$groupBy} ";
            $query .= $limit > 0 ? " LIMIT $limit OFFSET $offset " : "";

            return $this->getDB()->query($query);
        }
        elseif ($interval == 'hourly'){
            $groupBy = "DATE_FORMAT(call_start_time,'%Y-%m-%d %H'), skill_id";
            $select_date_format = "DATE_FORMAT(call_start_time,'%d-%M-%Y_%H:00')";

            $query  = "SELECT {$select_date_format} AS sdate, skill_id, COUNT(*) AS call_count ";
            $query .= " FROM log_skill_inbound WHERE status='S' AND DATE(call_start_time) = '{$sdate}' AND disposition_id != '' ";
            $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
            $query .= !empty($skill_id) && $skill_id != '*' ? " AND skill_id='{$skill_id}'" : "";
            $query .= !empty($skill_type) && $skill_type != '*' ? " AND call_type='{$skill_type}'" : "";
            $query .= " GROUP BY {$groupBy} ";
            $query .= $limit > 0 ? " LIMIT $limit OFFSET $offset " : "";

            return $this->getDB()->query($query);
        }
        elseif ($interval == 'daily'){
            $groupBy = 'sdate, skill_id';
            $select_date_format = "DATE(call_start_time)";
        }
        elseif ($interval == 'monthly'){
            $groupBy = "DATE_FORMAT(call_start_time, '%Y-%m'), skill_id";
            $select_date_format = "DATE_FORMAT(call_start_time, '%M, %Y')";
        } elseif ($interval == 'quarterly'){
            $groupBy = "YEAR(call_start_time), QUARTER(call_start_time), skill_id";
            $select_date_format = "CONCAT(YEAR(call_start_time),'_Quarter-', QUARTER(call_start_time))";
        }
        elseif ($interval == 'yearly'){
            $groupBy = "YEAR(call_start_time), skill_id";
            $select_date_format = "YEAR(call_start_time)";
        }

        $query  = "SELECT {$select_date_format} AS sdate, skill_id, COUNT(*) AS call_count ";
        $query .= " FROM log_skill_inbound WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' AND status='S' AND disposition_id != '' ";
        $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $query .= !empty($skill_id) && $skill_id != '*' ? " AND skill_id='{$skill_id}'" : "";
        // $query .= !empty($skill_type) && $skill_type != '*' ? " AND call_type='{$skill_type}'" : "";
        $query .= " GROUP BY {$groupBy} ";
        $query .= $limit > 0 ? " LIMIT $limit OFFSET $offset " : "";

        // echo $query;

        return $this->getDB()->query($query);
    }

    public function get_disposition_as_key_value()
    {
        $dispositions = [];
        $query = "SELECT disposition_id, title FROM skill_crm_disposition_code ORDER BY title";

        $result = $this->getDB()->query($query);
        if (empty($result)){
            return $dispositions;
        }

        foreach ($result as $disposition){
            $dispositions[$disposition->disposition_id] = $disposition->title;
        }

        return $dispositions;
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
            $dispositions[$disposition->disposition_id]['title'] = $disposition->title;
            $dispositions[$disposition->disposition_id]['type'] = $disposition->disposition_type;
            $dispositions[$disposition->disposition_id]['responsible_party'] = $disposition->responsible_party;
        }

        return $dispositions;
    }

    public function get_webchat_disposition_all_value()
    {
        $dispositions = [];
        $query = "SELECT disposition_id, title, disposition_type FROM skill_disposition_code ORDER BY title";

        $result = $this->getDB()->query($query);
        if (empty($result)){
            return $dispositions;
        }

        foreach ($result as $disposition){
            $dispositions[$disposition->disposition_id]['title'] = $disposition->title;
            $dispositions[$disposition->disposition_id]['type'] = $disposition->disposition_type;
        }

        return $dispositions;
    }

    function getDailySnapShotChartDataForMonth($skill_ids){
        $year = date('Y',time());
        $month = date('m', time());

        $select = " (SUM(ans_lte_30_count)/(SUM(calls_offered)-SUM(abd_lte_30_count)) * 100) AS service_level, (SUM(calls_abandoned)/SUM(calls_offered) * 100) AS abandoned_ratio, SUM(hold_time_in_queue)/SUM(calls_offered) AS awt, (SUM(ring_time)+SUM(service_duration)+SUM(wrap_up_time))/SUM(calls_answerd) as aht, (SUM(wrap_up_call_count)/SUM(calls_answerd) * 100) AS word_code, ((SUM(calls_offered)-SUM(repeat_cli_1_count))/SUM(calls_offered)) * 100 AS unique_call_per, SUM(calls_offered)/SUM(rgb_call_count) AS cpc, (SUM(repeat_1_count)/SUM(calls_offered)) * 100 AS repeat_call_per ";
        $table = " rt_skill_call_summary ";

        $sql = "SELECT $select FROM  $table ";
        $sql .= " WHERE MONTH(sdate)='$month' and YEAR(sdate)='$year' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";
        // echo $sql;die();

        return $this->getDB()->query($sql);
    }
    function getDailySnapShotChartDataForDay($skill_ids){
        $today = date('Y-m-d'); // '2018-09-30'
        $table = " rt_skill_call_summary ";

        $select = " (SUM(ans_lte_30_count)/(SUM(calls_offered)-SUM(abd_lte_30_count)) * 100) AS service_level, (SUM(calls_abandoned)/SUM(calls_offered) * 100) AS abandoned_ratio, SUM(hold_time_in_queue)/SUM(calls_offered) AS awt, (SUM(ring_time)+SUM(service_duration)+SUM(wrap_up_time))/SUM(calls_answerd) as aht, (SUM(wrap_up_call_count)/SUM(calls_answerd) * 100) AS word_code, ((SUM(calls_offered)-SUM(repeat_cli_1_count))/SUM(calls_offered)) * 100 AS unique_call_per, SUM(calls_offered)/SUM(rgb_call_count) AS cpc, (SUM(repeat_1_count)/SUM(calls_offered)) * 100 AS repeat_call_per ";
        $sql = "SELECT $select FROM  $table ";
        $sql .= " WHERE  sdate ='$today' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";
        // echo $sql;die();

        return $this->getDB()->query($sql);
    }
    function getDailySnapShotChartDataForYear($skill_ids){
        $year = date('Y',time());
        $table = " rt_skill_call_summary ";

        $select = " (SUM(ans_lte_30_count)/(SUM(calls_offered)-SUM(abd_lte_30_count)) * 100) AS service_level, (SUM(calls_abandoned)/SUM(calls_offered) * 100) AS abandoned_ratio, SUM(hold_time_in_queue)/SUM(calls_offered) AS awt, (SUM(ring_time)+SUM(service_duration)+SUM(wrap_up_time))/SUM(calls_answerd) as aht, (SUM(wrap_up_call_count)/SUM(calls_answerd) * 100) AS word_code, ((SUM(calls_offered)-SUM(repeat_cli_1_count))/SUM(calls_offered)) * 100 AS unique_call_per, SUM(calls_offered)/SUM(rgb_call_count) AS cpc, (SUM(repeat_1_count)/SUM(calls_offered)) * 100 AS repeat_call_per ";

        $sql = "SELECT $select FROM  $table ";
        $sql .= " WHERE  YEAR(sdate)='$year'";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";
//        echo $sql;die;

        return $this->getDB()->query($sql);
    }

    public function half_hour_data_calculation($half_hour_first_data, $half_hour_second_data){
        if(!empty($half_hour_first_data) && !empty($half_hour_second_data)){
            $half_hour_first_data->rgb_call_count = $half_hour_first_data->rgb_call_count + $half_hour_second_data->rgb_call_count;
            $half_hour_first_data->calls_offered = $half_hour_first_data->calls_offered + $half_hour_second_data->calls_offered;
            $half_hour_first_data->calls_answerd = $half_hour_first_data->calls_answerd + $half_hour_second_data->calls_answerd;
            $half_hour_first_data->calls_abandoned = $half_hour_first_data->calls_abandoned + $half_hour_second_data->calls_abandoned;
            $half_hour_first_data->ans_lte_10_count = $half_hour_first_data->ans_lte_10_count + $half_hour_second_data->ans_lte_10_count;
            $half_hour_first_data->ans_lte_20_count = $half_hour_first_data->ans_lte_20_count + $half_hour_second_data->ans_lte_20_count;
            $half_hour_first_data->ans_lte_30_count = $half_hour_first_data->ans_lte_30_count + $half_hour_second_data->ans_lte_30_count;
            $half_hour_first_data->ans_lte_60_count = $half_hour_first_data->ans_lte_60_count + $half_hour_second_data->ans_lte_60_count;
            $half_hour_first_data->ans_lte_90_count = $half_hour_first_data->ans_lte_90_count + $half_hour_second_data->ans_lte_90_count;
            $half_hour_first_data->ans_lte_120_count = $half_hour_first_data->ans_lte_120_count + $half_hour_second_data->ans_lte_120_count;
            $half_hour_first_data->ans_gt_120_count = $half_hour_first_data->ans_gt_120_count + $half_hour_second_data->ans_gt_120_count;
            $half_hour_first_data->abd_lte_10_count = $half_hour_first_data->abd_lte_10_count + $half_hour_second_data->abd_lte_10_count;
            $half_hour_first_data->abd_lte_20_count = $half_hour_first_data->abd_lte_20_count + $half_hour_second_data->abd_lte_20_count;
            $half_hour_first_data->abd_lte_30_count = $half_hour_first_data->abd_lte_30_count + $half_hour_second_data->abd_lte_30_count;
            $half_hour_first_data->abd_lte_60_count = $half_hour_first_data->abd_lte_60_count + $half_hour_second_data->abd_lte_60_count;
            $half_hour_first_data->abd_lte_90_count = $half_hour_first_data->abd_lte_90_count + $half_hour_second_data->abd_lte_90_count;
            $half_hour_first_data->abd_lte_120_count = $half_hour_first_data->abd_lte_120_count + $half_hour_second_data->abd_lte_120_count;
            $half_hour_first_data->abd_gt_120_count = $half_hour_first_data->abd_gt_120_count + $half_hour_second_data->abd_gt_120_count;
            $half_hour_first_data->ring_time = $half_hour_first_data->ring_time + $half_hour_second_data->ring_time;
            $half_hour_first_data->service_duration = $half_hour_first_data->service_duration + $half_hour_second_data->service_duration;
            $half_hour_first_data->wrap_up_time = $half_hour_first_data->wrap_up_time + $half_hour_second_data->wrap_up_time;
            $half_hour_first_data->agent_hold_time = $half_hour_first_data->agent_hold_time + $half_hour_second_data->agent_hold_time;
            $half_hour_first_data->fcr_call_count = $half_hour_first_data->fcr_call_count + $half_hour_second_data->fcr_call_count;
            $half_hour_first_data->short_call_count = $half_hour_first_data->short_call_count + $half_hour_second_data->short_call_count;
            $half_hour_first_data->wrap_up_call_count = $half_hour_first_data->wrap_up_call_count + $half_hour_second_data->wrap_up_call_count;
            $half_hour_first_data->repeat_1_count = $half_hour_first_data->repeat_1_count + $half_hour_second_data->repeat_1_count;
            $half_hour_first_data->agent_hangup_count = $half_hour_first_data->agent_hangup_count + $half_hour_second_data->agent_hangup_count;

            return $half_hour_first_data;
        }
    }

    function numDashboardWorkingReportWithCategory($dateinfo,$skill_ids, $report_type = REPORT_DAILY)
    {
        $table = 'rt_skill_call_summary';
        $group_by = '';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";

        if($report_type == REPORT_YEARLY){
            $sql = "SELECT YEAR(sdate) as syear, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY syear, skill_id ";
        }elseif($report_type == REPORT_QUARTERLY ){
            $sql = "SELECT QUARTER(sdate) as quarter_no, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY quarter_no, skill_id ";
        }elseif($report_type == REPORT_MONTHLY ){
            $sql = "SELECT MONTHNAME(sdate) as smonth, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY smonth, skill_id ";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
        }elseif($report_type == REPORT_HALF_HOURLY){
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
        }

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        if(empty($report_type) || $report_type == REPORT_15_MIN_INV)
            return $record[0]->total_record;
        // Gprint($record);

        return (!empty($record)) ? count($record) : 0;

    }

    function getDashboardWorkingReportWithCategory($dateinfo, $category_skill_ids, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE)
    {
        $table = 'rt_skill_call_summary';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";

        if($report_type == REPORT_YEARLY){
            $selected_report_type = ", YEAR(sdate) as syear ";
            $group_by = " GROUP BY syear, skill_id ";
            $order_by = " ORDER BY syear ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_QUARTERLY ){
            $selected_report_type = ", QUARTER(sdate) as quarter_no ";
            $group_by = " GROUP BY quarter_no, skill_id ";
            $order_by = " ORDER BY quarter_no ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_MONTHLY ){
            $selected_report_type = ", MONTHNAME(sdate) as smonth ";
            $group_by = " GROUP BY smonth, skill_id ";
            $order_by = " ORDER BY smonth ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
            $order_by = " ORDER BY sdate ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HALF_HOURLY ){
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }else{
            // $sql = "SELECT * FROM $table";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }

        $sql = "SELECT sdate, shour,sminute, skill_id, SUM(rgb_call_count) as rgb_call_count, SUM(calls_offered) as calls_offered, ";
        $sql .= "SUM(calls_answerd) as calls_answerd, SUM(calls_abandoned) as calls_abandoned, ";
        $sql .= "SUM(ans_lte_10_count) as ans_lte_10_count, SUM(ans_lte_20_count) as ans_lte_20_count, SUM(ans_lte_30_count) as ans_lte_30_count, ";
        $sql .= "SUM(ans_lte_60_count) as ans_lte_60_count, SUM(ans_lte_90_count) as ans_lte_90_count, SUM(ans_lte_120_count) as ans_lte_120_count, ";
        $sql .= "SUM(ans_gt_120_count) as ans_gt_120_count, SUM(abd_lte_10_count) as abd_lte_10_count, SUM(abd_lte_20_count) as abd_lte_20_count, ";
        $sql .= "SUM(abd_lte_30_count) as abd_lte_30_count, SUM(abd_lte_60_count) as abd_lte_60_count, SUM(abd_lte_90_count) as abd_lte_90_count, ";
        $sql .= "SUM(abd_lte_120_count) as abd_lte_120_count, SUM(abd_gt_120_count) as abd_gt_120_count, SUM(ring_time) as ring_time,  ";
        $sql .= "SUM(service_duration) as service_duration, SUM(wrap_up_time) as wrap_up_time, SUM(agent_hold_time) as agent_hold_time, ";
        $sql .= "SUM(fcr_call_count) as fcr_call_count, SUM(short_call_count) as short_call_count, SUM(wrap_up_call_count) as wrap_up_call_count, ";
        $sql .= "SUM(repeat_1_count) as repeat_1_count, SUM(agent_hangup_count) as agent_hangup_count, SUM(ice_count) as ice_count, ";
        $sql .= "SUM(query_call_count) as query_call_count, SUM(request_call_count) as request_call_count, SUM(complaint_call_count) as complaint_call_count, ";
        $sql .= "SUM(ice_negative_count) as ice_negative_count, SUM(ice_positive_count) as ice_positive_count, ";
        $sql .= "SUM(repeat_2_count) as repeat_2_count, SUM(repeat_3_count) as repeat_3_count, SUM(repeat_7_count) as repeat_7_count, ";
        $sql .= "SUM(repeat_30_count) as repeat_30_count, SUM(repeat_cli_1_count) as repeat_cli_1_count, ";
        $sql .= "SUM(repeat_cli_2_count) as repeat_cli_2_count, SUM(repeat_cli_3_count) as repeat_cli_3_count, ";
        $sql .= "SUM(repeat_cli_7_count) as repeat_cli_7_count, SUM(repeat_cli_30_count) as repeat_cli_30_count, ";
        $sql .= "SUM(hold_time_in_queue ) as hold_time_in_queue, SUM(max_hold_time_in_queue) as max_hold_time_in_queue ";
        if(!empty($selected_report_type))
            $sql .= $selected_report_type;
        $sql .= "FROM {$table} ";

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $sql .= (!empty($category_skill_ids) && $category_skill_ids!='*') ? " AND skill_id IN('{$category_skill_ids}') " : "";

        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;

        return $this->getDB()->query($sql);
    }

    function numWebChatSummaryData($dateInfo, $skill_ids)
    {
        if ($dateInfo->sdate > $dateInfo->edate) {
            return 0;
        }

        $from = date("Y-m-d H:i:s", $dateInfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateInfo->etstamp);

        $table = 'log_skill_inbound';
        $sql = "SELECT DATE(call_start_time) as sdate, COUNT(call_start_time) AS total_record FROM {$table} ";
        $sql .= " WHERE call_start_time BETWEEN '{$from}' AND '{$to}' AND call_type='C' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= " GROUP BY sdate ";
        $record = $this->getDB()->query($sql);
        // echo $sql;
        // die();

        return (!empty($record)) ? count($record) : 0;
    }

    function getWebChatSummaryData($dateinfo, $skill_ids, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy = true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $from = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateinfo->etstamp);

        $table = 'log_skill_inbound';
        $group_by = '';
        $order_by = "";

        if($report_type == REPORT_MONTHLY ){
            $selected_report_type = ", MONTHNAME(sdate) as smonth ";
            $group_by = " GROUP BY smonth, skill_id ";
            $order_by = " ORDER BY smonth ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }else{
            $group_by = " GROUP BY sdate, skill_id ";
        }

        $sql = "SELECT DATE(call_start_time) as sdate, count(call_start_time) AS calls_offered, SUM(IF(`status`='S' or agent_id!='',1,0)) as calls_answerd, SUM(IF(`status`='A',1,0)) as calls_abandoned, SUM(service_time) as service_time, SUM(agent_hold_time) as agent_hold_time, SUM(ring_time) as ring_time, SUM(wrap_up_time) as wrap_up_time, SUM(IF(hold_in_q <= '".WEB_CHAT_SL_TIME."' AND (status='S' OR agent_id!='') ,1,0)) as kpi_in, SUM(IF(hold_in_q <= '".WEB_CHAT_SL_TIME."' and status='A',1,0)) as abd_in, SUM(hold_in_q) as hold_in_q, MAX(hold_in_q) as max_hold_in_q ";

        $sql .= "FROM {$table} ";
        $sql .= " WHERE call_start_time BETWEEN '{$from}' AND '{$to}' AND call_type='C' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        if($isGroupBy)
            $sql .= " GROUP BY sdate, skill_id ";

        $order_by = " ORDER BY sdate ASC, skill_id ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    function getOnlineChatEfficiencyData($dateInfo, $agent_id, $skill_id, $offset = 0, $limit = 0)
    {
        if ($dateInfo->sdate > $dateInfo->edate) {
            return [];
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $from = date("Y-m-d H:i:s", $dateInfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateInfo->etstamp);

        /*$table = 'skill_log sl
        INNER JOIN chat_detail_log cdl ON sl.callid = cdl.callid
        INNER JOIN chat_service cs ON cdl.service_id = cs.service_id ';*/
        $table = ' log_skill_inbound lsi
        LEFT JOIN chat_detail_log cdl ON lsi.callid_cti = cdl.callid 
        LEFT JOIN chat_service cs ON cdl.service_id = cs.service_id ';

        $columns = 'lsi.call_start_time AS sdate,
            lsi.enter_time AS start_time,
            lsi.tstamp AS end_time,
            lsi.service_time AS duration,
            lsi.hold_in_q AS hold_time,
            lsi.skill_id,
            lsi.agent_id,
            cs.service_name as service_id, 
            cdl.email,
            cdl.contact_number';

        $query = "SELECT {$columns} FROM {$table} ";
        $query .= " WHERE call_start_time BETWEEN '{$from}' AND '{$to}' ";
        $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $query .= !empty($agent_id) && $agent_id != "*" ? " AND lsi.agent_id='{$agent_id}' " : "";
        $query .= !empty($skill_id) && $skill_id != "*" ? " AND lsi.skill_id='{$skill_id}' " : "";
        $query .= " AND lsi.call_type = 'C' ";
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        return $this->getDB()->query($query);
    }

    function numOnlineChatEfficiencyData($dateInfo, $agent_id, $skill_id)
    {
        if($dateInfo->sdate > $dateInfo->edate){
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $from = date("Y-m-d H:i:s", $dateInfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateInfo->etstamp);

        $table = ' log_skill_inbound lsi
        LEFT JOIN chat_detail_log cdl ON lsi.callid_cti = cdl.callid 
        LEFT JOIN chat_service cs ON cdl.service_id = cs.service_id ';

        $columns = 'lsi.call_start_time AS sdate,
            lsi.enter_time AS start_time,
            lsi.tstamp AS end_time,
            lsi.service_time AS duration,
            lsi.hold_in_q AS hold_time,
            lsi.skill_id,
            lsi.agent_id,
            cs.service_name as service_id, 
            cdl.email,
            cdl.contact_number';
        $query = "SELECT {$columns} FROM {$table} ";
        $query .= " WHERE call_start_time BETWEEN '{$from}' AND '{$to}' ";
        $query .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";
        $query .= !empty($agent_id) && $agent_id != "*" ? " AND lsi.agent_id='{$agent_id}' " : "";
        $query .= !empty($skill_id) && $skill_id != "*" ? " AND lsi.skill_id='{$skill_id}' " : "";
        $query .= " AND lsi.call_type = 'C' ";

        $response = $this->getDB()->query($query);
        return count($response);
    }


    function numSummaryReport($dateinfo, $skill_ids, $report_type = REPORT_DAILY)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";

        if($report_type == REPORT_YEARLY){
            $sql = "SELECT YEAR(sdate) as syear, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY syear, skill_id ";
        }elseif($report_type == REPORT_QUARTERLY ){
            $sql = "SELECT QUARTER(sdate) as quarter_no, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY quarter_no, skill_id ";
        }elseif($report_type == REPORT_MONTHLY ){
            $sql = "SELECT MONTHNAME(sdate) as smonth, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY smonth, skill_id ";
        }elseif($report_type == REPORT_DAILY ){
            $sql = "SELECT COUNT(sdate) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, skill_id ";
        }elseif($report_type == REPORT_HOURLY ){
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, skill_id ";
        }elseif($report_type == REPORT_HALF_HOURLY){
            $sql = "SELECT COUNT(*) as total_record, IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val FROM {$table}";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
        }else{
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
        }

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        // Gprint($record);

        // if($report_type == REPORT_15_MIN_INV)
        // return $record[0]->total_record;


        return (!empty($record)) ? count($record) : 0;
    }

    function getSummaryReport($dateinfo, $skill_ids, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy=true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";

        if($report_type == REPORT_YEARLY){
            $selected_report_type = ", YEAR(sdate) as syear ";
            $group_by = " GROUP BY syear, skill_id ";
            $order_by = " ORDER BY syear ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_QUARTERLY ){
            $selected_report_type = ", QUARTER(sdate) as quarter_no ";
            $group_by = " GROUP BY quarter_no, skill_id ";
            $order_by = " ORDER BY quarter_no ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_MONTHLY ){
            $selected_report_type = ", MONTHNAME(sdate) as smonth ";
            $group_by = " GROUP BY smonth, skill_id ";
            $order_by = " ORDER BY smonth ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
            $order_by = " ORDER BY sdate ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HALF_HOURLY ){
            $selected_report_type = ", IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val ";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, hour_minute_val ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }else{
            // $sql = "SELECT * FROM $table";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }

        $sql = "SELECT sdate, shour,sminute, skill_id, SUM(rgb_call_count) as rgb_call_count, SUM(calls_offered) as calls_offered, ";
        $sql .= "SUM(calls_answerd) as calls_answerd, SUM(calls_abandoned) as calls_abandoned, ";
        $sql .= "SUM(ans_lte_10_count) as ans_lte_10_count, SUM(ans_lte_20_count) as ans_lte_20_count, SUM(ans_lte_30_count) as ans_lte_30_count, ";
        $sql .= " SUM(ans_lte_40_count) as ans_lte_40_count, ";
		$sql .= "SUM(ans_lte_60_count) as ans_lte_60_count, SUM(ans_lte_90_count) as ans_lte_90_count, SUM(ans_lte_120_count) as ans_lte_120_count, ";
        $sql .= "SUM(ans_gt_120_count) as ans_gt_120_count, SUM(abd_lte_10_count) as abd_lte_10_count, SUM(abd_lte_20_count) as abd_lte_20_count, ";
        $sql .= "SUM(abd_lte_30_count) as abd_lte_30_count, SUM(abd_lte_60_count) as abd_lte_60_count, SUM(abd_lte_90_count) as abd_lte_90_count, ";
        $sql .= " SUM(abd_lte_40_count) as abd_lte_40_count, ";
		$sql .= "SUM(abd_lte_120_count) as abd_lte_120_count, SUM(abd_gt_120_count) as abd_gt_120_count, SUM(ring_time) as ring_time,  ";
        $sql .= "SUM(service_duration) as service_duration, SUM(wrap_up_time) as wrap_up_time, SUM(agent_hold_time) as agent_hold_time, ";
        $sql .= "SUM(fcr_call_count) as fcr_call_count, SUM(short_call_count) as short_call_count, SUM(wrap_up_call_count) as wrap_up_call_count, ";
        $sql .= "SUM(repeat_1_count) as repeat_1_count, SUM(agent_hangup_count) as agent_hangup_count, SUM(repeat_cli_1_count) as repeat_cli_1_count, ";
        $sql .= "SUM(hold_time_in_queue) as hold_time_in_queue ";
        if(!empty($selected_report_type))
            $sql .= $selected_report_type;
        $sql .= "FROM {$table} ";

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        if($isGroupBy)
            $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;



        return $this->getDB()->query($sql);
    }

    function numSkillSetSummaryReport($dateinfo, $skill_ids, $report_type = REPORT_DAILY)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";

        if($report_type == REPORT_YEARLY){
            $sql = "SELECT YEAR(sdate) as syear, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY syear, skill_id ";
        }elseif($report_type == REPORT_QUARTERLY ){
            $sql = "SELECT QUARTER(sdate) as quarter_no, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY quarter_no, skill_id ";
        }elseif($report_type == REPORT_MONTHLY ){
            $sql = "SELECT MONTHNAME(sdate) as smonth, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY smonth, skill_id ";
        }elseif($report_type == REPORT_DAILY ){
            $sql = "SELECT COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY sdate, skill_id ";
        }elseif($report_type == REPORT_HOURLY ){
            $sql = "SELECT COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, skill_id ";
        }elseif($report_type == REPORT_HALF_HOURLY){
            $sql = "SELECT COUNT(*) as total_record, IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val FROM {$table}";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
        }else{
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
        }

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        // var_dump($record);

        // if(empty($report_type) || $report_type == REPORT_15_MIN_INV)
        // return $record[0]->total_record;


        return (!empty($record)) ? count($record) : 0;
    }

    function getSkillSetSummaryReport($dateinfo, $skill_ids, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy=true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";

        if($report_type == REPORT_YEARLY){
            $selected_report_type = ", YEAR(sdate) as syear ";
            $group_by = " GROUP BY syear, skill_id ";
            $order_by = " ORDER BY syear ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_QUARTERLY ){
            $selected_report_type = ", QUARTER(sdate) as quarter_no ";
            $group_by = " GROUP BY quarter_no, skill_id ";
            $order_by = " ORDER BY quarter_no ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_MONTHLY ){
            $selected_report_type = ", MONTHNAME(sdate) as smonth ";
            $group_by = " GROUP BY smonth, skill_id ";
            $order_by = " ORDER BY smonth ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
            $order_by = " ORDER BY sdate ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HALF_HOURLY ){
            $selected_report_type = ", IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val ";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, hour_minute_val ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }else{
            // $sql = "SELECT * FROM $table";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }

        $sql = "SELECT sdate, shour,sminute, skill_id, SUM(rgb_call_count) as rgb_call_count, SUM(calls_offered) as calls_offered, ";
        $sql .= "SUM(calls_answerd) as calls_answerd, SUM(calls_abandoned) as calls_abandoned, ";
        $sql .= "SUM(ans_lte_10_count) as ans_lte_10_count, SUM(ans_lte_20_count) as ans_lte_20_count, SUM(ans_lte_30_count) as ans_lte_30_count, ";
        $sql .= "SUM(ans_lte_60_count) as ans_lte_60_count, SUM(ans_lte_90_count) as ans_lte_90_count, SUM(ans_lte_120_count) as ans_lte_120_count, ";
        $sql .= "SUM(ans_gt_120_count) as ans_gt_120_count, SUM(abd_lte_10_count) as abd_lte_10_count, SUM(abd_lte_20_count) as abd_lte_20_count, ";
        $sql .= "SUM(abd_lte_30_count) as abd_lte_30_count, SUM(abd_lte_60_count) as abd_lte_60_count, SUM(abd_lte_90_count) as abd_lte_90_count, ";
        $sql .= "SUM(abd_lte_120_count) as abd_lte_120_count, SUM(abd_gt_120_count) as abd_gt_120_count, SUM(ring_time) as ring_time,  ";
        $sql .= "SUM(service_duration) as service_duration, SUM(wrap_up_time) as wrap_up_time, SUM(agent_hold_time) as agent_hold_time, ";
        $sql .= "SUM(fcr_call_count) as fcr_call_count, SUM(short_call_count) as short_call_count, SUM(wrap_up_call_count) as wrap_up_call_count, ";
        $sql .= "SUM(repeat_1_count) as repeat_1_count, SUM(agent_hangup_count) as agent_hangup_count, SUM(repeat_cli_1_count) as repeat_cli_1_count, SUM(hold_time_in_queue) as hold_time_in_queue ";
        if(!empty($selected_report_type))
            $sql .= $selected_report_type;
        $sql .= "FROM {$table} ";

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        if($isGroupBy)
            $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;

        return $this->getDB()->query($sql);
    }

    function numDashboardWorkingReport($dateinfo, $skill_ids, $report_type = REPORT_DAILY)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";

        if($report_type == REPORT_YEARLY){
            $sql = "SELECT YEAR(sdate) as syear, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY syear, skill_id ";
        }elseif($report_type == REPORT_QUARTERLY ){
            $sql = "SELECT QUARTER(sdate) as quarter_no, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY quarter_no, skill_id ";
        }elseif($report_type == REPORT_MONTHLY ){
            $sql = "SELECT MONTHNAME(sdate) as smonth, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY smonth, skill_id ";
        }elseif($report_type == REPORT_DAILY ){
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, skill_id ";
        }elseif($report_type == REPORT_HOURLY ){
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, skill_id ";
        }elseif($report_type == REPORT_HALF_HOURLY){
            $sql = "SELECT COUNT(*) as total_record, IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val FROM {$table}";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
        }else{
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
        }

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        // Gprint($record);

        // if(empty($report_type) || $report_type == REPORT_15_MIN_INV)
        // return $record[0]->total_record;

        return (!empty($record)) ? count($record) : 0;
    }

    function getDashboardWorkingReport($dateinfo, $skill_ids, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy = true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";

        if($report_type == REPORT_YEARLY){
            $selected_report_type = ", YEAR(sdate) as syear ";
            $group_by = " GROUP BY syear, skill_id ";
            $order_by = " ORDER BY syear ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_QUARTERLY ){
            $selected_report_type = ", QUARTER(sdate) as quarter_no ";
            $group_by = " GROUP BY quarter_no, skill_id ";
            $order_by = " ORDER BY quarter_no ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_MONTHLY ){
            $selected_report_type = ", MONTHNAME(sdate) as smonth ";
            $group_by = " GROUP BY smonth, skill_id ";
            $order_by = " ORDER BY smonth ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
            $order_by = " ORDER BY sdate ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HALF_HOURLY ){
            $selected_report_type = ", IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val ";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, hour_minute_val ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }else{
            // $sql = "SELECT * FROM $table";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }

        $sql = "SELECT sdate, shour,sminute, skill_id, SUM(rgb_call_count) as rgb_call_count, SUM(calls_offered) as calls_offered, ";
        $sql .= "SUM(calls_answerd) as calls_answerd, SUM(calls_abandoned) as calls_abandoned, ";
        $sql .= "SUM(ans_lte_10_count) as ans_lte_10_count, SUM(ans_lte_20_count) as ans_lte_20_count, SUM(ans_lte_30_count) as ans_lte_30_count, ";
        $sql .= "SUM(ans_lte_60_count) as ans_lte_60_count, SUM(ans_lte_90_count) as ans_lte_90_count, SUM(ans_lte_120_count) as ans_lte_120_count, ";
        $sql .= "SUM(ans_gt_120_count) as ans_gt_120_count, SUM(abd_lte_10_count) as abd_lte_10_count, SUM(abd_lte_20_count) as abd_lte_20_count, ";
        $sql .= "SUM(abd_lte_30_count) as abd_lte_30_count, SUM(abd_lte_60_count) as abd_lte_60_count, SUM(abd_lte_90_count) as abd_lte_90_count, ";
        $sql .= "SUM(abd_lte_120_count) as abd_lte_120_count, SUM(abd_gt_120_count) as abd_gt_120_count, SUM(ring_time) as ring_time,  ";
        $sql .= "SUM(service_duration) as service_duration, SUM(wrap_up_time) as wrap_up_time, SUM(agent_hold_time) as agent_hold_time, ";
        $sql .= "SUM(fcr_call_count) as fcr_call_count, SUM(short_call_count) as short_call_count, SUM(wrap_up_call_count) as wrap_up_call_count, ";
        $sql .= "SUM(repeat_1_count) as repeat_1_count, SUM(agent_hangup_count) as agent_hangup_count, SUM(ice_count) as ice_count, ";
        $sql .= "SUM(query_call_count) as query_call_count, SUM(request_call_count) as request_call_count, SUM(complaint_call_count) as complaint_call_count, ";
        $sql .= "SUM(ice_negative_count) as ice_negative_count, SUM(ice_positive_count) as ice_positive_count, ";
        $sql .= "SUM(repeat_2_count) as repeat_2_count, SUM(repeat_3_count) as repeat_3_count, SUM(repeat_7_count) as repeat_7_count, ";
        $sql .= "SUM(repeat_30_count) as repeat_30_count, SUM(repeat_cli_1_count) as repeat_cli_1_count, ";
        $sql .= "SUM(repeat_cli_2_count) as repeat_cli_2_count, SUM(repeat_cli_3_count) as repeat_cli_3_count, ";
        $sql .= "SUM(repeat_cli_7_count) as repeat_cli_7_count, SUM(repeat_cli_30_count) as repeat_cli_30_count, ";
        $sql .= "SUM(hold_time_in_queue ) as hold_time_in_queue, SUM(max_hold_time_in_queue) as max_hold_time_in_queue ";
        if(!empty($selected_report_type))
            $sql .= $selected_report_type;
        $sql .= "FROM {$table} ";

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        if($isGroupBy)
            $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;

        return $this->getDB()->query($sql);
    }

    function getAgentSkillWiseData($dateinfo, $agent_id, $skill_ids,  $skill_type, $report_type, $offset = 0, $limit = 0, $isGroupBy=true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $from = $dateinfo->sdate . " " . $dateinfo->stime.':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime.':59:59';

        $table = " log_agent_inbound l ";

        $columns = "l.agent_id,
            DATE(l.call_start_time) as new_sdate,
            l.shour,
            l.skill_id,
            count(l.callid)AS offered,
            SUM(IF(is_answer = 'Y', 1, 0))AS answered,
            SUM(ring_time)AS rtime,
            SUM(service_time)AS srv_time,
            SUM(wrap_up_time)AS wrap_time,
            SUM(hold_time)AS hold_time,
            SUM(hold_in_q)AS wait_time,
            SUM(IF(disc_party = 'A', 1, 0))AS agent_hangup";

        $condition = " l.call_start_time BETWEEN '{$from}' AND '{$to}' ";
        $condition .= !empty($skill_ids) && $skill_ids != "*" ? " AND l.skill_id IN('{$skill_ids}') " : "";
        $condition .= !empty($agent_id) && $agent_id != "*" ? " AND l.agent_id='{$agent_id}' " : "";
        $condition .= !empty($skill_type) && $skill_type != "*" ? " AND l.call_type='{$skill_type}' " : "";
        $condition .= !empty($partition_id) ? " AND l.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";

        $query = "SELECT {$columns} FROM {$table} WHERE {$condition} ";
        if($isGroupBy){
            if($report_type==REPORT_DAILY)
                $query .= " GROUP BY new_sdate, l.agent_id, l.skill_id ";
            elseif($report_type==REPORT_HOURLY)
                $query .= " GROUP BY new_sdate, l.shour, l.agent_id, l.skill_id ";
        }

        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";
        // echo $query;
        // die();

        return $this->getDB()->query($query);
    }

    function numAgentSkillWiseData($dateinfo, $agent_id, $skill_ids, $skill_type, $report_type)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }
        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $from = $dateinfo->sdate . " " . $dateinfo->stime.':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime.':59:59';

        $table = " log_agent_inbound l ";

        $columns = "count(l.call_start_time) as total_record, DATE(l.call_start_time) as new_sdate";

        $condition = " l.call_start_time BETWEEN '{$from}' AND '{$to}' ";
        $condition .= !empty($skill_ids) && $skill_ids != "*" ? " AND l.skill_id IN('{$skill_ids}') " : "";
        $condition .= !empty($agent_id) && $agent_id != "*" ? " AND l.agent_id='{$agent_id}' " : "";
        $condition .= !empty($skill_type) && $skill_type != "*" ? " AND l.call_type='{$skill_type}' " : "";
        $condition .= !empty($partition_id) ? " AND l.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";

        $query = "SELECT {$columns} FROM {$table} WHERE {$condition} ";

        if($report_type==REPORT_DAILY)
            $query .= " GROUP BY new_sdate, l.agent_id, l.skill_id ";
        elseif($report_type==REPORT_HOURLY)
            $query .= " GROUP BY new_sdate, l.shour, l.agent_id, l.skill_id ";

        $record = $this->getDB()->query($query);
        // var_dump($query);
        // var_dump($record);die();

        return (!empty($record)) ? count($record) : 0;
    }

    function numOutboundDetailsReport($dateinfo, $skill_ids='', $agent_id='', $report_type = REPORT_DAILY)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_agent_outbound_manual';
        $group_by = '';

        $sql = "SELECT COUNT(start_time) AS total_record FROM {$table} ";

        $sql .= " WHERE {$table}.start_time BETWEEN '{$sdate}' AND '{$edate}'  ";

        // $sql .= (!empty($qtype )) ? " AND skill.qtype='{$qtype}' " : "";
        $sql .= !empty($skill_ids) && $skill_ids != "*" ? " AND {$table}.skill_id IN('{$skill_ids}') " : "";
        $sql .= (!empty($agent_id) && $agent_id!='*') ? " AND {$table}.agent_id = '{$agent_id}' " : " AND {$table}.agent_id !='' ";
        $sql .= $group_by;
        // echo $sql;

        $record = $this->getDB()->query($sql);
        // var_dump($record);

        return $record[0]->total_record;
    }

    function getOutboundDetailsReport($dateinfo, $skill_ids='', $agent_id='', $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isSum=false, $multiple_wrap_up=false)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_agent_outbound_manual';
        $group_by = '';
        $order_by = " ORDER BY al.start_time ASC ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";

        $sql = "SELECT DISTINCT(al.callid) AS callid, al.callid AS callid_sl, al.callto, al.agent_id, al.skill_id, al.is_reached, al.start_time, al.talk_time, al.callerid, al.service_time AS service_time, al.disc_cause, al.disc_party, al.ring_time, al.wrap_up_time, al.hold_time ";

        if($multiple_wrap_up){
            $sql .= ', scdl.disposition_id ';
        }

        if($isSum){
            $sql = "SELECT SUM(al.talk_time) as talk_time, SUM(al.ring_time) as ring_time, SUM(al.wrap_up_time) as wrap_up_time, SUM(al.hold_time) as hold_time ";
        }

        $sql .= "FROM {$table} as al ";

        if($multiple_wrap_up){
            $scdl_sdate = strtotime("-1 hour", $dateinfo->ststamp);
            $scdl_edate = strtotime("+1 hour", $dateinfo->etstamp);
            // var_dump(date('Y-m-d H:i:s', $scdl_sdate));
            // var_dump(date('Y-m-d H:i:s', $scdl_edate));
            // var_dump($scdl_sdate);
            // var_dump($scdl_edate);
            $sql .= "LEFT JOIN skill_crm_disposition_log as scdl ON scdl.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND scdl.callid=al.callid AND scdl.disposition_id!='' ";
        }

        $sql .= " WHERE al.start_time BETWEEN '{$sdate}' AND '{$edate}' ";

        // $sql .= (!empty($qtype )) ? " AND skill.qtype='{$qtype}' " : "";
        $sql .= !empty($skill_ids) && $skill_ids != "*" ? " AND al.skill_id IN('{$skill_ids}') " : "";
        $sql .= (!empty($agent_id) && $agent_id!='*') ? " AND al.agent_id = '{$agent_id}' " : " AND al.agent_id !='' ";
        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;

        return $this->getDB()->query($sql);
    }

    function numPdDetailsReport($dateinfo, $skill_id='', $agent_id='', $report_type = REPORT_DAILY){
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_cdr_autodial';
        $group_by = '';

        $sql = "SELECT COUNT(start_time) AS total_record FROM {$table} ";

        $sql .= " WHERE {$table}.start_time BETWEEN '{$sdate}' AND '{$edate}'  ";

        // $sql .= (!empty($qtype )) ? " AND skill.qtype='{$qtype}' " : "";
        $sql .= (!empty($skill_id) && $skill_id!='*') ? " AND {$table}.skill_id = '{$skill_id}' " : " AND {$table}.skill_id!=''";
        $sql .= (!empty($agent_id) && $agent_id!='*') ? " AND {$table}.agent_id = '{$agent_id}' " : "";
        $sql .= $group_by;
        // echo $sql;

        $record = $this->getDB()->query($sql);
        // var_dump($record);

        return $record[0]->total_record;
    }

    function getPdDetailsReport($dateinfo, $skill_id='', $agent_id='', $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isSum=false, $multiple_wrap_up=false){

        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $l_sdate = date('Y-m-d H:i:s', strtotime("-1 hour", $dateinfo->ststamp));
        $l_edate = date('Y-m-d H:i:s', strtotime("+1 hour", $dateinfo->etstamp));

        $table = 'log_cdr_autodial';
        $group_by = '';
        $order_by = " ORDER BY lca.start_time ASC ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";

        $sql = "SELECT DISTINCT(lca.callid) AS callid, lca.skill_id, lca.customer_id, lca.agent_id, lca.start_time, lca.answer_time, lca.call_from, lca.dial_number, lca.dial_count, lca.ring_time, lca.talk_time, lca.answer_time, lca.disc_cause, lca.disc_party, lca.status ";

        if(!$multiple_wrap_up){
            $sql .= ', lsi.disposition_id ';
        }else{
            $sql .= ', scdl.disposition_id ';
        }

        if($isSum){
            $sql = "SELECT SUM(lca.dial_count) as dial_count, SUM(lca.ring_time) as ring_time, SUM(lca.talk_time) as talk_time ";
        }

        $sql .= "FROM {$table} as lca ";

        if($multiple_wrap_up){
            $scdl_sdate = strtotime("-1 hour", $dateinfo->ststamp);
            $scdl_edate = strtotime("+1 hour", $dateinfo->etstamp);
            // var_dump(date('Y-m-d H:i:s', $scdl_sdate));
            // var_dump(date('Y-m-d H:i:s', $scdl_edate));
            // var_dump($scdl_sdate);
            // var_dump($scdl_edate);
            $sql .= "LEFT JOIN skill_crm_disposition_log as scdl ON scdl.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND scdl.callid=lca.callid AND scdl.disposition_id!='' ";
        }else{
            $sql .= "LEFT JOIN log_skill_inbound as lsi ON lsi.call_start_time BETWEEN '$l_sdate' AND '$l_edate' AND lsi.callid = lca.callid ";
        }

        $sql .= " WHERE lca.start_time BETWEEN '{$sdate}' AND '{$edate}' ";

        // $sql .= (!empty($qtype )) ? " AND skill.qtype='{$qtype}' " : "";
        $sql .= (!empty($skill_id) && $skill_id!='*') ? " AND lca.skill_id = '{$skill_id}' " : " AND lca.skill_id!=''";
        $sql .= (!empty($agent_id) && $agent_id!='*') ? " AND lca.agent_id = '{$agent_id}' " : "";
        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        //echo $sql;

        return $this->getDB()->query($sql);
    }

    function numAgentDetailsData($dateinfo, $agent_id, $skill_ids, $skill_type, $msisdn)
    {
        if ($dateinfo->ststamp > $dateinfo->etstamp) {
            return 0;
        }

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $from = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $to = date('Y-m-d H:i:s', $dateinfo->etstamp);

        // $table = " log_agent_inbound l LEFT JOIN cdrin_log cdl ON cdl.callid = l.callid_cti ";
        $table = " log_agent_inbound as l ";

        $columns = "count(l.call_start_time) as total_record ";

        $condition = " l.call_start_time BETWEEN '{$from}' AND '{$to}' ";
        $condition .= !empty($skill_ids) && $skill_ids != "*" ? " AND l.skill_id IN('{$skill_ids}') " : "";
        $condition .= !empty($agent_id) && $agent_id != "*" ? " AND l.agent_id='{$agent_id}' " : "";
        $condition .= !empty($skill_type) && $skill_type != "*" ? " AND l.call_type='{$skill_type}' " : "";
        $condition .= !empty($msisdn) ? " AND cdl.cli='{$msisdn}' " : "";
        $condition .= !empty($partition_id) ? " AND skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";

        $query = "SELECT {$columns} FROM {$table} WHERE {$condition} ";

        $record = $this->getDB()->query($query);
        // echo $query;

        return $record[0]->total_record;
    }

    function getAgentDetailsData($dateinfo, $agent_id, $skill_ids, $skill_type, $msisdn, $offset = 0, $limit = 0, $isSum=false)
    {
        if ($dateinfo->ststamp > $dateinfo->etstamp) {
            return [];
        }

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $from = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $to = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $scdl_sdate = strtotime("-1 hour", $dateinfo->ststamp);
        $scdl_edate = strtotime("+1 hour", $dateinfo->etstamp);

        $table = " log_agent_inbound as l ";
        $table .= " LEFT JOIN cdrin_log cdl ON cdl.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND cdl.callid = l.callid_cti  ";

        $columns = "l.agent_id,
            l.call_start_time,            
            l.skill_id,
            l.callid AS offered,
            IF(l.is_answer = 'Y', 1, 0) AS answered,
            l.ring_time AS rtime,
            l.service_time AS srv_time,
            l.wrap_up_time AS wrap_time,
            l.hold_time AS hold_time,
            l.hold_in_q AS wait_time,
            IF(l.disc_party = 'A', 1, 0) AS agent_hangup,
            cdl.cli AS msisdn,
            l.transfer_to";

        if($isSum){
            $columns = "l.agent_id,
                l.call_start_time,                
                l.skill_id,
                cdl.cli AS msisdn,
                count(l.callid) AS offered,
                SUM(IF(l.is_answer = 'Y', 1, 0)) AS answered,
                SUM(l.ring_time)AS rtime,
                SUM(l.service_time) AS srv_time,
                SUM(l.wrap_up_time) AS wrap_time,
                SUM(l.hold_time) AS hold_time,
                SUM(l.hold_in_q) AS wait_time,
                SUM(IF(l.disc_party = 'A', 1, 0)) AS agent_hangup,
                SUM(IF(l.transfer_to != '', 1, 0)) AS transfer_to";
        }

        $condition = " l.call_start_time BETWEEN '{$from}' AND '{$to}' ";
        $condition .= !empty($skill_ids) && $skill_ids != "*" ? " AND l.skill_id IN('{$skill_ids}') " : "";
        $condition .= !empty($agent_id) && $agent_id != "*" ? " AND l.agent_id='{$agent_id}' " : "";
        $condition .= !empty($skill_type) && $skill_type != "*" ? " AND l.call_type='{$skill_type}' " : "";
        $condition .= !empty($msisdn) ? " AND cdl.cli='{$msisdn}' " : "";
        $condition .= !empty($partition_id) ? " AND l.skill_id IN( Select record_id from partition_record where partition_id = '{$partition_id}' AND type='SQ' ) " : "";

        $query = "SELECT {$columns} FROM {$table} WHERE {$condition} ";

        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";
        // echo $query;

        return $this->getDB()->query($query);
    }
    function getMultipleDisposition($callIds){
        $table = "skill_crm_disposition_log";
        $sql = "SELECT * FROM {$table} ";

        $sql .= !empty($callIds) ? " WHERE callid IN('".$callIds."') " : '';
        // var_dump($sql);
        $record = $this->getDB()->query($sql);

        return $record;
    }

    function getMultiplePdDisposition($callIds){
        $table = "skill_crm_disposition_log";
        $sql = "SELECT * FROM {$table} ";

        $sql .= !empty($callIds) ? " WHERE callid IN('".$callIds."') AND disposition_id!='' " : '';
        // var_dump($sql);
        $record = $this->getDB()->query($sql);

        return $record;
    }

    function numIceDetailsReport($dateinfo, $skill_ids = [], $agent_id, $skill_type)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_skill_inbound';
        $group_by = '';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table} ";
        // $sql .= "left join skill ON skill.skill_id={$table}.skill_id ";

        $sql .= " WHERE {$table}.call_start_time BETWEEN '{$sdate}' AND '{$edate}' AND (ice_feedback!='' AND ice_feedback IS NOT NULL) ";

        // $sql .= (!empty($qtype )) ? " AND skill.qtype='{$qtype}' " : "";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND {$table}.skill_id IN('{$skill_ids}') " : "";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND {$table}.call_type='{$skill_type}' " : "";
        $sql .= (!empty($agent_id) && $agent_id!='*') ? " AND {$table}.agent_id = '{$agent_id}' " : "";
        $sql .= $group_by;
        // echo $sql;

        $record = $this->getDB()->query($sql);

        return $record[0]->total_record;
    }

    function getIceDetailsReport($dateinfo, $skill_ids = [], $agent_id, $skill_type, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE)
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

        $sql = "SELECT lsi.call_start_time, lsi.cli, lsi.skill_id, lsi.status, lsi.agent_id, lsi.callid, scdc.title, lsi.ice_feedback ";

        $sql .= "FROM {$table} as lsi ";
        $sql .= "LEFT JOIN skill_crm_disposition_code as scdc ON scdc.disposition_id=lsi.disposition_id ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '{$sdate}' AND '{$edate}' AND (ice_feedback!='' AND ice_feedback IS NOT NULL) ";

        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND lsi.skill_id IN('{$skill_ids}') " : "";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND lsi.call_type='{$skill_type}' " : "";
        $sql .= (!empty($agent_id) && $agent_id!='*') ? " AND lsi.agent_id = '{$agent_id}' " : "";
        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }


    function numIceSummaryReport($dateinfo, $skill_id, $report_type = REPORT_DAILY)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";

        if($report_type == REPORT_YEARLY){
            $sql = "SELECT YEAR(sdate) as syear, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY syear, skill_id ";
        }elseif($report_type == REPORT_QUARTERLY ){
            $sql = "SELECT QUARTER(sdate) as quarter_no, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY quarter_no, skill_id ";
        }elseif($report_type == REPORT_MONTHLY ){
            $sql = "SELECT MONTHNAME(sdate) as smonth, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY smonth, skill_id ";
        }elseif($report_type == REPORT_DAILY ){
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, skill_id ";
        }elseif($report_type == REPORT_HOURLY ){
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, skill_id ";
        }elseif($report_type == REPORT_HALF_HOURLY){
            $sql = "SELECT COUNT(*) as total_record, IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val FROM {$table}";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
        }else{
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
        }

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($skill_id) && $skill_id!='*') ? " AND skill_id IN('{$skill_id}') " : "";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        // Gprint($record);

        // if(empty($report_type) || $report_type == REPORT_15_MIN_INV)
        // return $record[0]->total_record;

        return (!empty($record)) ? count($record) : 0;
    }

    function getIceSummaryReport($dateinfo, $skill_id, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy = true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";

        if($report_type == REPORT_YEARLY){
            $selected_report_type = ", YEAR(sdate) as syear ";
            $group_by = " GROUP BY syear, skill_id ";
            $order_by = " ORDER BY syear ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_QUARTERLY ){
            $selected_report_type = ", QUARTER(sdate) as quarter_no ";
            $group_by = " GROUP BY quarter_no, skill_id ";
            $order_by = " ORDER BY quarter_no ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_MONTHLY ){
            $selected_report_type = ", MONTHNAME(sdate) as smonth ";
            $group_by = " GROUP BY smonth, skill_id ";
            $order_by = " ORDER BY smonth ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
            $order_by = " ORDER BY sdate ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HALF_HOURLY ){
            $selected_report_type = ", IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val ";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, hour_minute_val ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }else{
            // $sql = "SELECT * FROM $table";
            // $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            // $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            // $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
            $group_by = " GROUP BY sdate, skill_id ";
            $order_by = " ORDER BY sdate ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }

        $sql = "SELECT sdate, shour,sminute, skill_id, ";
        $sql .= "SUM(calls_answerd) as calls_answerd, ";
        $sql .= " SUM(num_ice_msg_sent) AS ice_sent, ";
        $sql .= "SUM(ice_count) as ice_count, ";
        $sql .= "SUM(ice_negative_count) as ice_negative_count, SUM(ice_positive_count) as ice_positive_count ";

        if(!empty($selected_report_type))
            $sql .= $selected_report_type;
        $sql .= "FROM {$table} ";

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $sql .= (!empty($skill_id) && $skill_id!='*') ? " AND skill_id IN('{$skill_id}') " : "";

        if($isGroupBy)
            $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
//         echo $sql;

        return $this->getDB()->query($sql);
    }

    function numTopReasonsDissatisfaction($dateinfo, $skill_type)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $table = 'log_skill_inbound';
        $group_by = ' GROUP BY sdate, lsi.disposition_id ';
        $having = ' HAVING dis_ice_negative > 0 ';
        $order_by = " ORDER BY sdate ASC, dis_ice_negative DESC ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";

        $sql = "SELECT DATE(lsi.call_start_time) as sdate, lsi.disposition_id, SUM(IF(lsi.ice_feedback='N', 1, 0)) as dis_ice_negative ";
        $sql .= "FROM {$table} as lsi ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '{$sdate}' AND '{$edate}' AND lsi.disposition_id != '' ";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND lsi.call_type='{$skill_type}' " : "";

        $sql .= $group_by;
        $sql .= $having;
        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        $recode = $this->getDB()->query($sql);
        // echo $sql;
        // die();

        return count($recode);
    }

    function getTopReasonsDissatisfaction($dateinfo, $skill_type, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy = true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_skill_inbound';
        $group_by = ' GROUP BY sdate, lsi.disposition_id ';
        $having = ' HAVING dis_ice_negative > 0 ';
        $order_by = " ORDER BY sdate ASC, dis_ice_negative DESC ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";

        $sql = "SELECT DATE(lsi.call_start_time) as sdate, lsi.disposition_id, SUM(IF(lsi.ice_feedback='N', 1, 0)) as dis_ice_negative ";
        $sql .= "FROM {$table} as lsi ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '{$sdate}' AND '{$edate}' AND lsi.disposition_id != '' ";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND lsi.call_type='{$skill_type}' " : "";
        if($isGroupBy)
            $sql .= $group_by;

        $sql .= $having;
        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    function numTopAgentsDissatisfaction($dateinfo, $skill_type)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $table = 'log_skill_inbound';
        $group_by = ' GROUP BY sdate, lsi.disposition_id ';
        $having = ' HAVING dis_ice_negative > 0 ';
        $order_by = " ORDER BY sdate ASC, dis_ice_negative DESC ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";

        $sql = "SELECT DATE(lsi.call_start_time) as sdate, lsi.disposition_id, SUM(IF(lsi.ice_feedback='N', 1, 0)) as dis_ice_negative ";
        $sql .= "FROM {$table} as lsi ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '{$sdate}' AND '{$edate}' AND lsi.disposition_id != '' ";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND lsi.call_type='{$skill_type}' " : "";
        $sql .= $group_by;
        $sql .= $having;
        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        $recode = $this->getDB()->query($sql);
        // echo $sql;
        // die();

        return count($recode);
    }

    function getTopAgentsDissatisfaction($dateinfo, $skill_type, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy = true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_skill_inbound';
        $group_by = ' GROUP BY sdate, lsi.agent_id ';
        $having = ' HAVING ag_ice_negative > 0 ';
        $order_by = " ORDER BY sdate ASC, ag_ice_negative DESC ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";

        $sql = "SELECT DATE(lsi.call_start_time) as sdate, lsi.agent_id, SUM(IF(lsi.ice_feedback='N', 1, 0)) as ag_ice_negative ";
        $sql .= "FROM {$table} as lsi ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
        $sql .= (!empty($skill_type) && $skill_type!='*') ? " AND lsi.call_type='{$skill_type}' " : "";
        if($isGroupBy)
            $sql .= $group_by;

        $sql .= $having;
        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    public function getTotalAnsCallWorkcode($sdate, $edate, $skill_id, $interval='daily', $skill_type)
    {
        if($sdate > $edate){
            return [];
        }
        // $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        // $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $edate = date('Y-m-d', strtotime($edate)-1).' 23:59:59';

        if ($interval == 'daily') {
            $sql = "SELECT COUNT(call_start_time) as total_offer_call, DATE(call_start_time) as cdate, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY cdate";
        } elseif ($interval == 'monthly') {
            $sql = "SELECT COUNT(call_start_time) as total_offer_call, MONTHNAME(call_start_time) as smonth, YEAR(call_start_time) as syear, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY smonth, syear ";
        } elseif ($interval == 'yearly') {
            $sql = "SELECT COUNT(call_start_time) as total_offer_call, YEAR(call_start_time) as syear, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY syear ";
        } elseif ($interval == 'quarterly'){
            $sql = "SELECT COUNT(call_start_time) as total_offer_call, QUARTER(call_start_time) as quarter_no, YEAR(call_start_time) as syear, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY quarter_no, syear ";
        }elseif ($interval == 'hourly') {
            $sql = "SELECT COUNT(call_start_time) as total_offer_call, DATE(call_start_time) as cdate, DATE_FORMAT(call_start_time, '%H') as shour, SUM(IF(status='S',1,0)) as total_ans_call ";
            $sql .= "FROM log_skill_inbound ";
            $sql .= "WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";

            if(!empty($skill_type) && $skill_type != "*" )
                $sql .= "AND call_type='{$skill_type}' ";
            $sql .= "GROUP BY cdate, shour";
        }
        $record = $this->getDB()->query($sql);
        // var_dump($sql);
        // var_dump($record);
        // die();

        return $record;
    }

    public function numIvrSummary($dateinfo, $ivr_id)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $from = $dateinfo->sdate . " " . $dateinfo->stime.':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime.':59:59';

        $sql  = "SELECT COUNT(call_start_time) AS total_record FROM ivr_log where call_start_time BETWEEN '{$from}' ";
        $sql .= " AND '{$to}' ";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND ivr_id='{$ivr_id}' " : "";
        $sql .= " AND ivr_id != 'AX' ";
        $sql .= " AND ivr_id != 'AB' ";
        $sql .= " AND ivr_id != 'AC' ";
        $sql .= " GROUP BY DATE(call_start_time), ivr_id";
        // echo $sql;
        // die();

        $response = $this->getDB()->query($sql);

        return !empty($response) && is_array($response) ? count($response) : 0;
    }

    public function getIvrSummary($dateinfo, $ivr_id,  $offset = 0, $limit = 20, $isGroupBy= true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $from = $dateinfo->sdate . " " . $dateinfo->stime.':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime.':59:59';

        $sql  = "SELECT DATE(call_start_time) as sdate, ivr_id, count(callid) as total_hit, SUM(IF(skill_id ='', 1, 0)) as ivr_only, SUM(time_in_ivr) as time_in_ivr ";
        $sql .= "FROM ivr_log WHERE call_start_time BETWEEN '{$from}' AND '{$to}' ";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND ivr_id='{$ivr_id}' " : "";
        $sql .= " AND ivr_id != 'AX' ";
        $sql .= " AND ivr_id != 'AB' ";
        $sql .= " AND ivr_id != 'AC' ";
        if($isGroupBy)
            $sql .= "GROUP BY sdate, ivr_id ";

        $sql .= "LIMIT {$limit} OFFSET {$offset} ";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    public function numIvrDetails($dateinfo, $ivr_type, $ivr_id)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $from = $dateinfo->sdate . " " . $dateinfo->stime.':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime.':59:59';

        $sql = "SELECT COUNT(*) AS total_record ";
        $sql .= "FROM ivr_log WHERE call_start_time BETWEEN '{$from}' AND '{$to}'";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND ivr_id='{$ivr_id}' " : "";
        $sql .= " AND ivr_id != 'AX' ";
        if ($ivr_type == 'I'){
            $sql .= "AND skill_id= '' ";
        }elseif ($ivr_type == 'A'){
            $sql .= "AND skill_id!= '' ";
        }
        // echo $sql;
        // die();

        $response = $this->getDB()->query($sql);

        return !empty($response) ? $response[0]->total_record : 0;
    }

    public function getIvrDetails($dateinfo, $ivr_type, $ivr_id, $offset = 0, $limit = 20, $isSum=false)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $from = $dateinfo->sdate . " " . $dateinfo->stime.':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime.':59:59';
        $scdl_sdate = strtotime("-1 hour", strtotime($from));
        $scdl_edate = strtotime("+1 hour", strtotime($to));
        // GPrint(date('Y-m-d H:i:s', $scdl_sdate));
        // GPrint(date('Y-m-d H:i:s', $scdl_edate));

        $sql = "SELECT call_start_time, FROM_UNIXTIME(enter_time) AS enter_time, FROM_UNIXTIME(il.tstamp) AS new_tstamp, cl.cli, cl.did, il.time_in_ivr ";
        if($isSum)
            $sql  = "SELECT call_start_time, FROM_UNIXTIME(enter_time) AS enter_time, FROM_UNIXTIME(il.tstamp) AS new_tstamp, cl.cli, cl.did, SUM(time_in_ivr) AS time_in_ivr ";

        $sql .= "FROM ivr_log AS il ";
        $sql .= "LEFT JOIN cdrin_log AS cl ON cl.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND il.callid_cti = cl.callid ";
        $sql .= " WHERE il.call_start_time BETWEEN '{$from}' AND '{$to}' ";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND il.ivr_id='{$ivr_id}' " : "";
        $sql .= " AND ivr_id != 'AX' ";
        if ($ivr_type == 'I'){
            $sql .= "AND il.skill_id= '' ";
        }elseif ($ivr_type == 'A'){
            $sql .= "AND il.skill_id!= '' ";
        }
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    function numAgentIceSummaryReport($dateinfo, $agent_id, $report_type = REPORT_DAILY)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateinfo->etstamp);

        $table = "log_skill_inbound";
        $columns = "";
        $group_by = "";

        if ($report_type == REPORT_MONTHLY) {
            $columns .= "MONTHNAME(call_start_time) as smonth,";
            $group_by .= "GROUP BY smonth, agent_id";
        } elseif ($report_type == REPORT_DAILY) {
            $columns .= "DATE(call_start_time) as sdate,";
            $group_by .= "GROUP BY sdate, agent_id";
        }
        $columns .= " agent_id, 
                    SUM(IF(STATUS = 'S', 1, 0))AS call_ans,
                    SUM(IF(ice_feedback = 'Y', 1, 0))AS ice_feedback_yes,
                    SUM(IF(ice_feedback = 'N', 1, 0))AS ice_feedback_no";

        $conditions = "call_start_time BETWEEN '$from' AND '$to' ";
        $conditions .= "AND agent_id != '' ";
        $conditions .= (!empty($agent_id) && $agent_id != '*') ? "AND agent_id = '{$agent_id}' " : "";

        $sql = "SELECT $columns FROM $table WHERE $conditions ";
        $sql .= $group_by;

        $record = $this->getDB()->query($sql);
        return (!empty($record)) ? count($record) : 0;
    }

    function getAgentIceSummaryReport($dateinfo, $agent_id, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy = true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $from = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateinfo->etstamp);

        $table = "log_skill_inbound";
        $columns = "";
        $group_by = "";

        if ($report_type == REPORT_MONTHLY) {
            $columns .= "MONTHNAME(call_start_time) as smonth,";
            $group_by .= "GROUP BY smonth, agent_id";
        } elseif ($report_type == REPORT_DAILY) {
            $columns .= "DATE(call_start_time) as sdate,";
            $group_by .= "GROUP BY sdate, agent_id";
        }
        $columns .= " agent_id, 
                    SUM(IF(STATUS = 'S', 1, 0))AS call_ans,
                    SUM(IF(ice_feedback = 'Y', 1, 0))AS ice_feedback_yes,
                    SUM(IF(ice_feedback = 'N', 1, 0))AS ice_feedback_no";

        $conditions = "call_start_time BETWEEN '$from' AND '$to' ";
        $conditions .= "AND agent_id != '' ";
        $conditions .= (!empty($agent_id) && $agent_id != '*') ? "AND agent_id = '{$agent_id}' " : "";

        $sql = "SELECT $columns FROM $table WHERE $conditions ";
        if ($isGroupBy) {
            $sql .= $group_by;
        }
        $sql .= " LIMIT $rowsPerPage OFFSET $offset";

        return $this->getDB()->query($sql);
    }


    function numWebChatDetailsData($dateInfo, $skill_id)
    {
        if ($dateInfo->sdate > $dateInfo->edate) {
            return 0;
        }

        $from = date("Y-m-d H:i:s", $dateInfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateInfo->etstamp);

        $columns = "count(s.call_start_time) as total_record ";

        $table = "log_skill_inbound s           
                LEFT JOIN chat_detail_log dl ON dl.callid = s.callid                
                LEFT JOIN log_agent_inbound al ON al.callid = s.callid";

        $query = "SELECT $columns FROM $table WHERE ";
        $query .= "s.call_start_time BETWEEN '$from' AND '$to' ";
        $query .= !empty($skill_id) && $skill_id != "*" ? " AND s.skill_id = '{$skill_id}' " : " ";
        $query .= " AND s.call_type = 'C' ";
//        $query .= " AND (s.status = 'S' OR s.agent_id != '') ";
        // $query .= " AND dl.agent_id != '' ";
        // echo $query;
        // die();

        $response = $this->getDB()->query($query);
        return (!empty($response)) ? $response[0]->total_record : 0;
    }

    function getWebChatDetailsData($dateInfo, $skill_id, $offset = 0, $limit = 0, $isSum=false)
    {
        if ($dateInfo->sdate > $dateInfo->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $dateInfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateInfo->etstamp);

        $columns = "s.call_start_time 'sdate', s.skill_id 'skill_id',
                    s.status, 
                    s.agent_id 'agent_id',
                    s.disc_party,
                    dl.callid 'callid',
                    dl.contact_number 'customer_number',
                    dl.verify_user 'is_verified',
                    s.cli 'customer_name',
                    al.start_time 'agent_response_time',
                    s.tstamp 'stop_time',                
                    IF(al.hold_in_q <= ".WEB_CHAT_SL_TIME.", ".WEB_CHAT_SL_PERCENTAGE.", 0)'SL',                    
					dl.disposition_id,
                    dl.customer_feedback,
                    dl.agent_first_response";

        if($isSum){
            $columns .= ", SUM(TIMESTAMPDIFF(SECOND, al.start_time, s.tstamp)) as service_time, SUM(s.ring_time), SUM(s.wrap_up_time), ";
            $columns .= "SUM(IF(s.STATUS != 'S', s.hold_in_q, 0)) as abd_hold_in_q , SUM(s.agent_hold_time) as agent_hold_time, ";
            $columns .= "SUM(TIMESTAMPDIFF(SECOND, s.call_start_time, al.start_time)) as wait_time ";
        }else{
            $columns .= ", TIMESTAMPDIFF(SECOND, al.start_time, s.tstamp) as service_time, s.ring_time, s.wrap_up_time, s.hold_in_q, ";
            $columns .= "TIMESTAMPDIFF(SECOND, s.call_start_time, al.start_time) as wait_time, ";
            $columns .= "s.agent_hold_time ";
        }

        $table = "log_skill_inbound s
                LEFT JOIN chat_detail_log dl ON dl.callid = s.callid
                LEFT JOIN log_agent_inbound al ON al.callid = s.callid";

        $query = "SELECT $columns FROM $table WHERE ";
        $query .= "s.call_start_time BETWEEN '$from' AND '$to' ";
        $query .= !empty($skill_id) && $skill_id != "*" ? " AND s.skill_id = '{$skill_id}' " : " ";
        $query .= " AND s.call_type = 'C' ";
//        $query .= " AND (s.status = 'S' OR s.agent_id != '')";
        // $query .= " AND dl.agent_id != '' ";
        // echo $query;
        // die();

        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";
        return $this->getDB()->query($query);
    }

    function getEvaluationFormFields($report_type = null)
    {
        $sql = "SELECT DISTINCT f.fid, f.flabel, f.report_label, f.forder, f.calculative, f.foption_value, ga.`order` FROM ev_form_group_fields f
                LEFT JOIN ev_form_groups g on f.group_id = g.id
                LEFT JOIN ev_form_group_assign ga ON f.group_id = ga.group_id
                LEFT JOIN ev_form_report_assign ra ON ga.form_id = ra.form_id
                WHERE g.status = 'A' ";
        $sql .= ($report_type != null) ? " AND ra.report_type ='{$report_type}' " : "";
        $sql .= " GROUP BY ga.`order`, f.forder";
        $result = $this->getDB()->query($sql);
        return $result;
    }

    function numQaReportData($date_info, $agent_score = null, $score_comparison = "=")
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "ls.call_start_time as sdate,
        ls.agent_id,
        lc.agent_id as evaluator_id,
        ls.skill_id,
        ls.cli as msisdn,
        lc.form_total,
        lc.agent_score,
        lc.score_percentage,
        lc.evaluation_time,        
        lc.callid,
        sd.title as wrap_up_code";
        $tables = "log_evp_call lc 
        LEFT JOIN log_skill_inbound ls on lc.callid = ls.callid 
        LEFT JOIN skill_crm_disposition_code sd on ls.disposition_id = sd.disposition_id";

        $conditions = "lc.skill_type = 'I' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";

        $query = "SELECT $columns FROM $tables WHERE $conditions";

        $record = $this->getDB()->query($query);
        return (!empty($record)) ? count($record) : 0;

    }

    function getQaReportData($date_info, $agent_score = null, $score_comparison = "=", $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "ls.call_start_time as sdate,
        ls.agent_id,
        lc.agent_id as evaluator_id,
        ls.skill_id,
        ls.cli as msisdn,
        lc.form_total,
        lc.agent_score,
        lc.score_percentage,
        lc.evaluation_time,        
        lc.callid,
        sd.title as wrap_up_code";
        $tables = "log_evp_call lc 
        LEFT JOIN log_skill_inbound ls on lc.callid = ls.callid
        LEFT JOIN skill_crm_disposition_code sd on ls.disposition_id = sd.disposition_id ";

        $conditions = "lc.skill_type = 'I' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";
        $query = "SELECT $columns FROM $tables WHERE $conditions";
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        $response = $this->getDB()->query($query);
        return $response;
    }
    function getEvaluationFormValue($call_id)
    {
        $query = "SELECT field_name, field_value,percentage_value FROM ev_review_info where callid = '$call_id'";
        $response = $this->getDB()->query($query);
        return $response;
    }

    function getChatRecordCount($did, $date_info, $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $sql = "SELECT cdl.disposition_id, COUNT(*) AS numrecords, scdc.title  ";
        $sql .= " FROM log_skill_inbound AS lsi ";
        $sql .= " LEFT JOIN chat_detail_log AS cdl ON cdl.callid=lsi.callid  ";
        $sql .= " LEFT JOIN skill_crm_disposition_code AS scdc ON scdc.disposition_id=cdl.disposition_id  ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '$from' AND '$to' AND lsi.call_type='C' AND (lsi.status OR lsi.agent_id !='') ";
        $sql .= !empty($did) ? " AND cdl.disposition_id='$did' " : "";
        $sql .= " GROUP BY cdl.disposition_id ";
        //$sql .= " ORDER BY cdl.disposition_id ";
        $sql .= " ORDER BY numrecords DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    function getTotalCrmRecordCount($did, $date_info)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $sql = "SELECT COUNT(*) AS numrows ";
        $sql .= " FROM log_skill_inbound AS lsi ";
        $sql .= " LEFT JOIN chat_detail_log AS cdl ON cdl.callid=lsi.callid ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '$from' AND '$to' AND lsi.call_type='C' AND (lsi.status OR lsi.agent_id !='') ";
        $sql .= !empty($did) ? " AND cdl.disposition_id='$did' " : "";
        $result = $this->getDB()->query($sql);
        // echo $sql;
        // die();

        if ($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function numChatRecordCount($did, $date_info)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $sql = "SELECT COUNT(DISTINCT cdl.disposition_id) AS numrows ";
        $sql .= " FROM log_skill_inbound AS lsi ";
        $sql .= " LEFT JOIN chat_detail_log AS cdl ON cdl.callid=lsi.callid ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '$from' AND '$to' AND lsi.call_type='C' and (lsi.status OR lsi.agent_id !='') ";
        if (!empty($did)) $sql .= " AND cdl.disposition_id='$did'";
        $result = $this->getDB()->query($sql);
        // echo $sql;
        // die();

        if ($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }
    function numIceRawDataReport($dateinfo)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_sms_inbound';
        $group_by = '';

        $sql = "SELECT COUNT({$table}.log_time) AS total_record FROM {$table} ";

        $sql .= " WHERE {$table}.log_time BETWEEN '{$sdate}' AND '{$edate}' ";
        $sql .= $group_by;
        // echo $sql;

        $record = $this->getDB()->query($sql);

        return $record[0]->total_record;
    }
    function getIceRawDataReport($dateinfo, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
        $table = 'log_sms_inbound';
        $group_by = '';
        $order_by = " ORDER BY lsi.log_time ASC ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";

        // $sql = "SELECT *, convert(sms_text using binary) as sms_text ";
        $sql = "SELECT * ";


        $sql .= "FROM {$table} as lsi ";
        $sql .= " WHERE lsi.log_time BETWEEN '{$sdate}' AND '{$edate}' ";
        $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;
        // die();
        $this->getDB()->setCharset('utf8');
        return $this->getDB()->query($sql);
    }
    public function numAgentPerformanceSummary($dateinfo)
    {
        $sql = "SELECT COUNT(start_time) AS aggregate FROM log_agent_inbound WHERE start_time BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' GROUP  BY agent_id ";
        $result = $this->getDB()->query($sql);

        return !empty($result) ? count($result): 0;
    }

    public function getAgentPerformanceSummary($dateinfo, $offset=0, $limit=20)
    {
        $sql = "SELECT lai.agent_id, a.name AS agent_name, SUM(CASE WHEN lai.is_answer='Y' THEN 1 ELSE 0 END ) AS calls_answered, ";
        $sql .= "SUM(lai.ring_time) AS ring_time, SUM(lai.service_time)  - SUM(lai.hold_time) AS talk_time, SUM(lai.wrap_up_time) AS wrap_up_time, ";
        $sql .= "SUM(lai.hold_time) AS hold_time, SUM(lai.hold_in_q) AS hold_time_in_queue,  ";
        $sql .= "SUM(CASE WHEN (lai.is_answer='Y' AND  lai.disc_party='A') THEN 1 ELSE 0 END)  AS agent_hangup, ";
        $sql .= "SUM(CASE WHEN (lai.is_answer='N' AND  lai.disc_party='A') THEN 1 ELSE 0 END) AS agent_reject_calls, ";
        $sql .= "SUM(CASE WHEN lai.disc_party='A' THEN 1 ELSE 0 END ) AS agent_disc_calls, ";
        $sql .= "SUM(CASE WHEN lai.service_time < 10 THEN 1 ELSE 0 END ) AS short_call, ";
        $sql .= "SUM(CASE WHEN lai.repeated_call = 'Y' THEN 1 ELSE 0 END ) AS repeated_call, ";
        $sql .= "SUM(CASE WHEN lai.fcr_call = 'Y' THEN 1 ELSE 0 END ) AS fcr_call, ";
        $sql .= "SUM(lai.disposition_count) AS workcode_count ";
        $sql .= "FROM log_agent_inbound AS lai INNER JOIN agents AS a ON lai.agent_id = a.agent_id ";
        $sql .= "WHERE lai.call_start_time BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' group  BY lai.agent_id ORDER BY lai.agent_id LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }

    public function getAgentSessionDetails($sdate,$agent_id)
    {
        $sdate = date('Y-m-d',strtotime($sdate));
        $sql = "SELECT * FROM rt_agent_shift_summary where agent_id='$agent_id' and sdate='{$sdate}'";
        return $this->getDB()->query($sql);
    }

    function getAgentSessionInfo($agent_id, $dateinfo)
    {
		$report_start_time = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $report_end_time = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $result = $this->getAgentSessionLog($agent_id, $report_start_time, $report_end_time);
		
		$log_data = [$agent_id, json_encode($result)];
        log_text($log_data, "temp/agent_performance_report_log/");
		
        return $this->calcAgentSessionInfo($agent_id, $result, $report_start_time, $report_end_time);
    }

    function getAgentSessionLog($agent_id, $stime='', $etime='', $order = 'tstamp')
    {
        $sql = "SELECT * FROM log_agent_session WHERE tstamp BETWEEN '{$stime}' AND '{$etime}' ";
        $sql .= " AND agent_id='$agent_id' ORDER BY $order";
		
		$log_data = [$agent_id, $sql];
        log_text($log_data, "temp/agent_performance_report_log/");
		
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
		$randAgent = rand(0,45);
		
		if($etime > date('Y-m-d H:i:s'))
			$etime = date('Y-m-d H:i:s');

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
                    //$agent->not_ready_time += $last_time < 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
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
                if($l_arr_c > 0 && !isset($logout_duration_arr[$l_arr_c-1])){
                    unset($login_duration_arr[$l_arr_c]);
                }
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

		//GPrint($login_duration_arr);
		//GPrint($logout_duration_arr);
        $login_duration = 0;
        foreach ($login_duration_arr as $key => $item) {
            //new condition for empty logout time
            if(!empty($login_duration_arr[$key]) && !empty($logout_duration_arr[$key])){
                $login_duration +=$this->diff_in_sec($login_duration_arr[$key], $logout_duration_arr[$key]);
            }
			//GPrint($login_duration);
			//GPrint(gmdate("H:i:s", $login_duration));
        }
        $agent->login_duration = $login_duration;
		//GPrint($agent);
		//GPrint(gmdate("H:i:s", $agent->login_duration));
		//die();

        $log_data = [$agent_id, json_encode($agent)];
        log_text($log_data, "temp/agent_performance_report_log/");

        return $agent;
    }

    function getAgentLastStatus($agent_id, $stime)
    {
        $days_ago = date('Y-m-d H:i:s', strtotime('-2 days', strtotime($stime)));

        //$sql = "SELECT * FROM log_agent_session WHERE tstamp < '{$stime}'";
        $sql = "SELECT * FROM log_agent_session WHERE tstamp BETWEEN '{$days_ago}' AND '{$stime}'";
        $sql .= " AND agent_id='$agent_id' ORDER BY tstamp DESC LIMIT 1";
        // echo $sql;

        return $this->getDB()->query($sql);
    }

    function getAgentNextStatus($agent_id, $etime)
    {
        $days_ahead = date('Y-m-d H:i:s', strtotime('1 days', strtotime($etime)));

        //$sql = "SELECT * FROM log_agent_session WHERE tstamp > '{$etime}'";
        $sql = "SELECT * FROM log_agent_session WHERE tstamp BETWEEN '{$etime}' AND '{$days_ahead}'";
        $sql .= " AND agent_id='$agent_id' ORDER BY tstamp ASC LIMIT 1";

        return $this->getDB()->query($sql);
    }

    function getAgentNextLogoutStatus($agent_id, $etime)
    {
        $days_ahead = date('Y-m-d H:i:s', strtotime('1 days', strtotime($etime)));

        //$sql = "SELECT * FROM log_agent_session WHERE  tstamp > '{$etime}' ";
        $sql = "SELECT * FROM log_agent_session WHERE tstamp BETWEEN '{$etime}' AND '{$days_ahead}'";
        $sql .= " AND agent_id='$agent_id' and type='O' ORDER BY tstamp ASC LIMIT 1 ";

        return $this->getDB()->query($sql);
    }
    function getAgentPreviousLoginStatus($agent_id, $stime)
    {
        $days_ago = date('Y-m-d H:i:s', strtotime('-2 days', strtotime($stime)));

        //$sql = "SELECT * FROM log_agent_session WHERE   tstamp < '{$stime}' ";
        $sql = "SELECT * FROM log_agent_session WHERE tstamp BETWEEN '{$days_ago}' AND '{$stime}'";
        $sql .= " AND agent_id='$agent_id' and type='I' ORDER BY tstamp DESC LIMIT 1 ";

        return $this->getDB()->query($sql);
    }


    function diff_in_sec($stime, $etime)
    {
        return strtotime($etime) - strtotime($stime);
    }

    function getDailyEmailActivity($dateinfo, $agent_id){
        $from = $dateinfo->sdate;
        $to = $dateinfo->edate;

        $groupBy = '';
        $query= "SELECT DATE(FROM_UNIXTIME(activity_time)) AS sdate, COUNT(*) as activity_count, ";
        $query.= "IF(activity !='S', activity, CONCAT(activity,activity_details)) as new_activity, agent_id ";
        $query .= "FROM e_ticket_activity ";
        $query .= " WHERE DATE(FROM_UNIXTIME(activity_time)) BETWEEN '{$from}' AND '{$to}' AND LENGTH(agent_id) <= 4 ";
        $query .= !empty($agent_id) && $agent_id != '*' ? " AND agent_id='{$agent_id}'" : "";

        $query .= " GROUP BY sdate, agent_id, new_activity";
        $query .= " ORDER BY sdate ASC, agent_id ASC, new_activity ASC";

        // echo $query;die();

        return $this->getDB()->queryOnUpdateDB($query);
    }

    function getDailyEmailActivityAgents($dateinfo, $agent_id){
        $from = $dateinfo->sdate;
        $to = $dateinfo->edate;

        $groupBy = '';
        $query= "SELECT agent_id ";
        $query .= "FROM e_ticket_activity ";
        $query .= " WHERE DATE(FROM_UNIXTIME(activity_time)) BETWEEN '{$from}' AND '{$to}' AND LENGTH(agent_id) <= 4 ";
        $query .= !empty($agent_id) && $agent_id != '*' ? " AND agent_id='{$agent_id}'" : "";

        $query .= " GROUP BY agent_id";
        $query .= " ORDER BY agent_id ASC";
        // echo $query;die();

        return $this->getDB()->queryOnUpdateDB($query);
    }
    function numEmailActivityReport($agentId, $dateTimeInfo, $status, $ticket_id, $supervisor_id){
        $cond = '';
        $sql = "SELECT COUNT(*) as numrows FROM e_ticket_activity ";
        $cond = $this->getActivityReportCondition($agentId, $dateTimeInfo, $status, $ticket_id);
        if (!empty($cond)) $sql .= " WHERE $cond ";
        $result = $this->getDB()->query($sql);
//        echo $sql;die;
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }

    function getEmailActivityReport($agentId, $dateTimeInfo, $status, $ticket_id, $supervisor_id, $offset=0, $limit=0){
        $sql = "SELECT * FROM e_ticket_activity ";
        $cond = $this->getActivityReportCondition($agentId, $dateTimeInfo, $status, $ticket_id);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "ORDER BY activity_time DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";

        return $this->getDB()->query($sql);
    }

    function getTicketStatusLabel( $status='O' )
    {
        $status_available = array(
            'O' => 'New',
            'P' => 'Pending',
            'C' => 'Pending - Client',
            'S' => 'Served',
            'E' => 'Closed',
            'H' => 'Hold',
            'K' => 'Park',
            'R' => 'Re-schedule',
            'Z' => 'Skip',
        );

        $staText = isset ($status_available[$status]) ? $status_available[$status] : "";
        return $staText;
    }

    function getActivityReportCondition($agentId, $dateTimeInfo, $status, $ticket_id){
        $cond = '';
        if (isset($dateTimeInfo->sdate) && !empty($dateTimeInfo->sdate)){
            $cond.= "activity_time BETWEEN '$dateTimeInfo->ststamp' AND '$dateTimeInfo->etstamp'";
        }

        if (!empty($ticket_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "ticket_id='$ticket_id' ";
        }
        if (!empty($agentId)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "agent_id IN ('$agentId') ";
        }

        if (!empty($cond)) $cond .= " AND ";
        $cond .= " activity='S' ";
        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " activity_details='$status' ";
        }
        return rtrim(trim($cond), ',');
    }

    function getDailyIvrHitData($date, $ivr_id)
    {
        $sql = "SELECT service_title, SUM(hit_count) AS hit_count, trace_id, branch
            FROM
            rt_ivr_trace LEFT JOIN ivr_service_code ON disposition_code = trace_id
            WHERE sdate='$date' ";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND branch LIKE '$ivr_id%'  " : "";
        $sql .= " GROUP BY trace_id";
        $sql .= " ORDER BY SUM(hit_count) DESC";

        $result = $this->getDB()->query($sql);

        return $result;
    }

    function getIvrNodes(){
        $sql = "SELECT disposition_code,service_title FROM ivr_service_code WHERE report_category = 'D'";
        return $this->getDB()->query($sql);
    }



    public function numAgentSessionDetailsReport($agent_id, $dateinfo)
    {
        $sql = "SELECT count(tstamp) AS aggregate FROM log_agent_session ";
        $sql .= " where tstamp BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($agent_id) ? " AND agent_id='{$agent_id}' " : "";

        $response = $this->getDB()->query($sql);

        return !empty($response[0]->aggregate) ? $response[0]->aggregate : 0;
    }

    public function getAgentSessionDetailsReport($agent_id, $dateinfo, $offset=0, $limit=20)
    {
        $sql = "SELECT * FROM log_agent_session las ";
        $sql .= " where las.tstamp BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        $sql .= !empty($agent_id) ? " AND las.agent_id='{$agent_id}' " : "";
        $sql .= " ORDER BY las.agent_id LIMIT {$offset}, {$limit} ";

        return $this->getDB()->query($sql);

    }

    public function getAgentSkillSetAsString($agent_id='')
    {
        $skill_sets = [];

        $sql = "SELECT agent_id, skill_name  FROM agent_skill ";
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

    public function getDailyIvrHitSummaryData($date_info, $ivr_id)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $query = "SELECT trace_id, sum(hit_count)AS hit_count, branch FROM	rt_ivr_trace
                  WHERE sdate BETWEEN '$date_info->sdate' and '$date_info->edate' ";
        $query .= !empty($ivr_id) && $ivr_id != '*' ? " AND branch LIKE '$ivr_id%'  " : "";
        $query .= " GROUP BY trace_id";

        return $this->getDB()->query($query);

    }

    public function getParentIvrNodes()
    {
        $query = "SELECT * FROM ivr_service_code WHERE report_category = 'S'";
        return $this->getDB()->query($query);
    }

    public function getChildIvrNodes()
    {
        $query = "SELECT * FROM ivr_service_code WHERE report_category = 'D'";
        return $this->getDB()->query($query);
    }

    public function getDailyIvrHitCountData($date_info, $ivr_id)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $query = "SELECT trace_id, sum(hit_count)AS hit_count, branch FROM	rt_ivr_trace
                  WHERE sdate BETWEEN '$date_info->sdate' and '$date_info->edate' ";
        $query .= !empty($ivr_id) && $ivr_id != '*' ? " AND branch LIKE '$ivr_id%'  " : "";
        $query .= " GROUP BY trace_id ";
        // echo $query;
        return $this->getDB()->query($query);
    }

    public function getDayWiseIvrHitCountData($date_info, $ivr_id)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $query = "SELECT sdate, trace_id, sum(hit_count)AS hit_count, branch FROM rt_ivr_trace
                  WHERE sdate BETWEEN '$date_info->sdate' and '$date_info->edate'
                  GROUP BY trace_id, sdate ";
        $query .= !empty($ivr_id) && $ivr_id != '*' ? " HAVING branch LIKE '$ivr_id%'  " : "";

        return $this->getDB()->query($query);
    }





    function numEmailAgentReport($customer_id, $waiting_duration, $agentid, $last_update_time, $in_kpi, $creatie_time, $disposition_id, $first_open_time, $close_time, $status, $subject, $isAgent=false, $skill_id=null, $from_email=null){
        $sql = "SELECT COUNT(les.ticket_id) as numrows from log_email_session AS les ";
        $sql .=" JOIN e_ticket_info AS eti ON eti.ticket_id = les.ticket_id ";
        if (!empty($agentid)){
            //$sql .= "JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }
        $cond = $this->getEmailAgentReportCondition($disposition_id, $status,$agentid, $creatie_time, '', $newMailTime='0', $last_update_time,$first_open_time,$close_time, $customer_id, $waiting_duration, $in_kpi, $subject, $isAgent, $skill_id, $from_email);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        //$sql .= "  AND !( les.close_time > 0 AND les.first_open_time = 0) "; //// updated for mark as closed.
        $result = $this->getDB()->queryOnUpdateDB($sql);
        //echo $sql;die;
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }
    function getEmailAgentReport($customer_id, $waiting_duration, $agentid, $last_update_time, $in_kpi, $creatie_time, $disposition_id, $first_open_time, $close_time, $status, $subject, $isAgent=false, $skill_id=null, $from_email=null, $offset=0, $limit=0){
        $sql = "SELECT les.*, skl.skill_name,  eti.subject,eti.created_for, eti.mail_to, eti.customer_id FROM log_email_session AS les ";
        $sql .=" JOIN e_ticket_info AS eti ON eti.ticket_id = les.ticket_id ";
        if (!empty($agentid)){
            //$sql .= " JOIN agent_skill AS ags ON ags.skill_id=les.skill_id AND ags.agent_id='$agentid' ";
        }
        $sql .= " LEFT JOIN skill AS skl ON skl.skill_id=les.skill_id ";
        $cond = $this->getEmailAgentReportCondition($disposition_id, $status,$agentid, $creatie_time, '', $newMailTime='0', $last_update_time,$first_open_time,$close_time, $customer_id, $waiting_duration, $in_kpi, $subject, $isAgent, $skill_id, $from_email);
        if (!empty($cond)) $sql .= "WHERE $cond ";
        //$sql .= "  AND !( les.close_time > 0 AND les.first_open_time = 0) "; //// updated for mark as closed.
        $sql .= "ORDER BY last_update_time DESC ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //echo $sql;die;
        return $this->getDB()->queryOnUpdateDB($sql);
    }

    function getEmailAgentReportCondition( $disposition_id, $status, $agentid, $dateinfo, $tfield, $newMailTime='0', $updateinfo=null, $first_open_date_info, $close_date_info  ,$customer_id, $waiting_duration, $in_kpi, $subject, $isAgent=false, $skill_id=null, $from_email=null)	{
        $cond = '';
        $timeField = "create_time";

        if (!empty($disposition_id)) {
            $cond .= " les.disposition_id='$disposition_id'";
        }
        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " les.status='$status'";
        }
        if (!empty($skill_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " les.skill_id='$skill_id'";
        }
        if (isset($dateinfo->sdate) && !empty($dateinfo->sdate)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " les.create_time BETWEEN '{$dateinfo->ststamp}' AND '{$dateinfo->etstamp}' ";
        }
        if (isset($updateinfo->sdate) && !empty($updateinfo->sdate)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " les.last_update_time BETWEEN '{$updateinfo->ststamp}' AND '{$updateinfo->etstamp}' ";
        }
        if (!empty($customer_id)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " eti.customer_id='$customer_id'";
        }
        if (!empty($agentid)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " ( les.agent_1='$agentid' OR les.agent_2='$agentid' ) ";
        }
        if (!empty($waiting_duration)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " les.waiting_duration='$waiting_duration' ";
        }
        if (!empty($in_kpi)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " les.in_kpi='$in_kpi' ";
        }
        if (!empty($subject)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " eti.subject_db LIKE '%$subject%' ";
        }
        if (isset($first_open_date_info->sdate) && !empty($first_open_date_info->sdate)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " les.first_open_time BETWEEN '{$first_open_date_info->ststamp}' AND '{$first_open_date_info->etstamp}' ";
        }
        if (isset($close_date_info->sdate) && !empty($close_date_info->sdate)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " les.close_time BETWEEN '{$close_date_info->ststamp}' AND '{$close_date_info->etstamp}' ";
        }
        if (!empty($from_email)){
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= " eti.created_for LIKE '%$from_email%' ";
        }
        return $cond;
    }

    function numEmailDaywiseRepoet($last_update_time){
        if (empty($last_update_time->sdate) || ($last_update_time->sdate > $last_update_time->edate) )
            return 0;
        $sql = "SELECT from_unixtime(close_time, '%Y-%m-%d') AS sdate  FROM log_email_session  ";
        $sql .= " WHERE close_time BETWEEN '{$last_update_time->ststamp}' AND '{$last_update_time->etstamp}' ";
        $sql .= " GROUP BY sdate, skill_id";
        $result = $this->getDB()->query($sql);
        //dd($sql);
        if(!empty($result)) {
            return count($result);
        }
        return 0;
    }

    function getEmailDaywiseRepoet($last_update_time, $offset=0, $limit=20){
        $sql = "SELECT  count(ticket_id) AS total_email, skill_id, from_unixtime(close_time, '%Y-%m-%d') AS sdate, ";
        $sql .= " SUM(IF (STATUS = 'S' OR STATUS = 'E', 1, 0)) AS total_closed, ";
        $sql .= " SUM(IF(in_kpi = 'Y', 1, 0)) total_in_kpi, ";
        $sql .= " SUM(IF ((STATUS = 'S' OR STATUS = 'E') AND in_kpi = 'N',1,0)) total_out_kpi, ";
        $sql .= " SUM(IF (STATUS = 'S' OR STATUS = 'E',waiting_duration,0)) AS WaitDuration, ";
        $sql .= " MAX(IF (STATUS = 'S' OR STATUS = 'E',waiting_duration,0)) MAX_Wait, ";
        $sql .= " SUM(IF (STATUS = 'S' OR STATUS = 'E',open_duration,0)) AS total_open_duration ";
        //$sql .= " SUM(open_duration) AS total_open_duration ";
        $sql .= " FROM log_email_session  ";
        $sql .= " WHERE close_time BETWEEN '{$last_update_time->ststamp}' AND '{$last_update_time->etstamp}' ";
        $sql .= " GROUP BY sdate, skill_id ";
        $sql .= " ORDER BY close_time DESC LIMIT {$offset}, {$limit} ";
        //GPrint($sql);die;
        return $this->getDB()->query($sql);
    }

    function numWebChatDsywiseData($dateinfo, $skill_id){
        if (empty($dateinfo->sdate) || ($dateinfo->sdate > $dateinfo->edate))
            return 0;

        $sdate = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $edate = date("Y-m-d H:i:s", $dateinfo->etstamp);

        $sql = "SELECT DATE(call_start_time) AS csdate, count(call_start_time) AS total_record, skill_id FROM log_skill_inbound ";
        $sql .= " WHERE call_start_time between '{$sdate}' and '{$edate}' AND call_type='C' ";
        $sql .= !empty($skill_id) ? " AND skill_id='$skill_id' " : "";
        $sql .= " GROUP BY csdate,skill_id";
        //echo $sql;

        $result = $this->getDB()->query($sql);

        if(!empty($result)) {
            return count($result);
        }
        return 0;
    }

    function getWebChatDayWiseData($dateinfo, $skill_id, $offset=0, $limit=20){
        if(empty($dateinfo->sdate) || ($dateinfo->sdate > $dateinfo->edate)){
            return [];
        }

        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $sql = "SELECT DATE(lsi.call_start_time) AS csdate, lsi.skill_id, count(*) AS offered_chat, ";
        $sql .= " SUM(IF(lsi.`status` = 'S', 1, 0)) AS total_chat, ";
//        $sql .= " SUM( IF (lai.hold_in_q <= '20' AND lsi.`status` = 'S', 1, 0) ) AS in_kpi, ";
        $sql .= " SUM(IF(TIMESTAMPDIFF(SECOND, lsi.call_start_time, lai.start_time) <= '".WEB_CHAT_SL_TIME."' AND lsi.`status` = 'S', 1, 0) ) AS in_kpi, ";
		
		if($dateinfo->sdate >= WEBCHAT_AGENT_RESPONSE){
			$sql .= " SUM(IF(TIMESTAMPDIFF(SECOND, lai.start_time, cdl.agent_first_response) <= '".WEB_CHAT_SL_TIME."' AND lsi.`status` = 'S', 1, 0) ) AS in_kpi_new, ";
			$sql .= " SUM(IF((cdl.agent_first_response = '0000-00-00 00:00:00' OR cdl.agent_first_response IS NULL) AND lsi.`status` = 'S', 1, 0) ) AS abd_new, ";
			$sql .= " SUM(IF((cdl.agent_first_response = '0000-00-00 00:00:00' OR cdl.agent_first_response IS NULL) AND lsi.`status` = 'S' AND lsi.hold_in_q <= 60, 1, 0) ) AS abd_new_before_60, ";
			$sql .= " SUM(IF((cdl.agent_first_response = '0000-00-00 00:00:00' OR cdl.agent_first_response IS NULL) AND lsi.`status` = 'S' AND lsi.hold_in_q > 60, 1, 0) ) AS abd_new_after_60, ";			
			$sql .= " SUM(IF((lsi.`status` != 'S' AND lsi.hold_in_q <= 60), 1, 0)) AS abd_before_60, ";
			$sql .= " SUM(IF((lsi.`status` != 'S' AND lsi.hold_in_q > 60), 1, 0)) AS abd_after_60, ";
			$sql .= " SUM(IF(cdl.agent_first_response != '0000-00-00 00:00:00', TIMESTAMPDIFF(SECOND, lai.start_time, lsi.tstamp), 0)) AS total_service_time, ";
			//$sql .= " SUM(IF(cdl.agent_first_response != '0000-00-00 00:00:00', TIMESTAMPDIFF(SECOND, lsi.call_start_time, lai.start_time), 0)) AS total_wait_time, ";
			$sql .= " SUM(IF(lsi.STATUS = 'S', TIMESTAMPDIFF(SECOND, lsi.call_start_time, lai.start_time),0)) AS total_wait_time, ";
		}else{
			$sql .= " SUM(TIMESTAMPDIFF(SECOND, lai.start_time, lsi.tstamp)) AS total_service_time, ";
			$sql .= " SUM(TIMESTAMPDIFF(SECOND, lsi.call_start_time, lai.start_time)) AS total_wait_time, ";
		}
        $sql .= " SUM( IF(cdl.verify_user = 'Y', 1, 0) ) AS verified, ";
        // $sql .= " SUM( IF(cdl.verify_user = 'N', 1, 0) ) AS non_verified, ";
        $sql .= " SUM(IF(cdl.agent_first_response != '0000-00-00 00:00:00' AND cdl.agent_first_response IS NOT NULL AND lsi.`status` = 'S' AND lsi.disc_party != 'S' AND cdl.disposition_id !='1457' AND cdl.customer_feedback = '5', 1, 0)) AS ice_positive_count, ";
        $sql .= " SUM(IF(cdl.agent_first_response != '0000-00-00 00:00:00' AND cdl.agent_first_response IS NOT NULL AND lsi.`status` = 'S' AND lsi.disc_party != 'S' AND cdl.disposition_id !='1457' AND cdl.customer_feedback = '0', 1, 0)) AS ice_negative_count, ";
        $sql .= " SUM(IF(lsi.STATUS != 'S', lsi.hold_in_q, 0)) AS total_abd_wait_time, ";
        $sql .= " SUM(lsi.hold_in_q) AS total_hold_in_q ";
        $sql .= " FROM log_skill_inbound lsi LEFT JOIN log_agent_inbound lai ON lai.callid = lsi.callid";
        $sql .= " LEFT JOIN chat_detail_log cdl ON cdl.callid = lsi.callid";
        $sql .= " WHERE ";
        $sql .= " lsi.call_start_time between '{$sdate}' and '{$edate}' ";
        $sql .= " AND lsi.call_type='C' ";
        $sql .= !empty($skill_id) ? " AND lsi.skill_id='$skill_id' " : "";
        $sql .= " GROUP BY csdate, lsi.skill_id ORDER BY csdate ASC LIMIT {$offset}, {$limit}";
        //echo $sql;

        return $this->getDB()->query($sql);
    }


    function numServiceLog($dateinfo, $dcode, $clid, $alid, $skills=array()) {
        //dd($dateinfo);
        $cond = $this->getServiceLogCond($dateinfo, $dcode, $clid, $alid);
        $sql = "SELECT COUNT(*) AS numrows FROM ivr_service_request_log AS isrl";
        $sql .= " INNER JOIN ivr ON isrl.ivr_id = ivr.ivr_id ";
        $sql .= "  WHERE caller_id != '' ";
        $sql .= !empty($cond) ? " AND $cond " : "";
        //dd($sql);
        $result = $this->getDB()->query($sql);
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }
    function getServiceLogCond($dateinfo, $dcode, $clid, $alid) {
        //dd($dateinfo->ststamp);
        $cond = " served_time BETWEEN '{$dateinfo->ststamp}' AND '{$dateinfo->etstamp}' ";
        if (!empty($dcode)) $cond = $this->getAndCondition($cond, "isrl.disposition_code='$dcode'");
        if (!empty($clid)) $cond = $this->getAndCondition($cond, "isrl.caller_id LIKE '%$clid%'");
        if (!empty($alid) &&  $alid != '*') $cond = $this->getAndCondition($cond, "isrl.ivr_id ='$alid'");
        return $cond;
    }
    function getServiceLog($dateinfo, $dcode, $clid, $alid, $offset=0, $limit=0, $skills=array()){
        $cond = $this->getServiceLogCond($dateinfo, $dcode, $clid, $alid);

        $sql = "SELECT isrl.*, ivr.ivr_name FROM ivr_service_request_log AS isrl ";
        $sql .= " INNER JOIN ivr ON isrl.ivr_id = ivr.ivr_id ";
        $sql .= "  WHERE caller_id != '' ";
        $sql .= !empty($cond) ? " AND $cond " : "";
        $sql .= " ORDER BY tstamp DESC ";
        $sql .= $limit > 0 ?  " LIMIT $offset, $limit" : "";
//        dd($sql);
        return $this->getDB()->query($sql);
    }

    function numQaOutboundReportData($date_info, $agent_score = null, $score_comparison = "=")
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "ls.start_time as sdate,
        ls.agent_id,
        lc.agent_id as evaluator_id,
        ls.skill_id,
        ls.callto as msisdn,
        lc.form_total,
        lc.agent_score,
        lc.score_percentage,
        lc.evaluation_time,        
        lc.callid ";
        $tables = "log_evp_call lc 
        LEFT JOIN log_agent_outbound_manual ls on lc.callid = ls.callid ";
        //LEFT JOIN skill_crm_disposition_code sd on ls.disposition_id = sd.disposition_id";

        $conditions = "lc.skill_type = 'O' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";

        $query = "SELECT $columns FROM $tables WHERE $conditions";

        $record = $this->getDB()->query($query);
        return (!empty($record)) ? count($record) : 0;
    }

    function getQaReportOutboundData($date_info, $agent_score = null, $score_comparison = "=", $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "ls.start_time as sdate,
        ls.agent_id,
        lc.agent_id as evaluator_id,
        ls.skill_id,
        ls.callto as msisdn,
        lc.form_total,
        lc.agent_score,
        lc.score_percentage,
        lc.evaluation_time,        
        lc.callid ";
        $tables = "log_evp_call lc 
        LEFT JOIN log_agent_outbound_manual ls on lc.callid = ls.callid ";

        $conditions = "lc.skill_type = 'O' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";
        $query = "SELECT $columns FROM $tables WHERE $conditions";
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        $response = $this->getDB()->query($query);
        return $response;
    }

    function getChatRatingReportData($dateinfo, $agent_id, $offset=0, $limit=0){
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $sql = "SELECT DATE(lsi.call_start_time) as sdate, lsi.agent_id, COUNT(lsi.call_start_time) AS numrecords, SUM(IF(dl.customer_feedback='5', 1, 0)) AS good, SUM(IF(dl.customer_feedback='0', 1, 0)) AS bad, ";
        $sql .= " SUM(IF(dl.customer_feedback='' OR dl.customer_feedback IS NULL, 1, 0)) AS no_rating ";
        $sql .= " FROM log_skill_inbound AS lsi ";
        $sql .= " LEFT JOIN chat_detail_log AS dl ON dl.callid=lsi.callid ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '$sdate' AND '$edate' AND lsi.call_type='C' AND (lsi.status='S' OR lsi.agent_id!='') ";

        if (!empty($agent_id)) $sql .= " AND  lsi.agent_id='$agent_id' ";
        $sql .= " GROUP BY sdate, lsi.agent_id  ORDER BY sdate ASC ";
        if ($limit > 0 && $offset >= 0) $sql .= " LIMIT {$limit} OFFSET {$offset} ";
        // echo $sql;die;
        return $this->getDB()->query($sql);
    }
    function numChatRatingReportData($dateinfo, $agent_id){
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $sql = "SELECT DATE(lsi.call_start_time) as sdate, lsi.agent_id ";
        $sql .= " FROM log_skill_inbound AS lsi ";
        $sql .= " LEFT JOIN chat_detail_log AS dl ON dl.callid=lsi.callid ";
        $sql .= " WHERE lsi.call_start_time BETWEEN '$sdate' AND '$edate' AND lsi.call_type='C' AND (lsi.status='S' OR lsi.agent_id!='') ";
        if (!empty($agent_id)) $sql .= " AND  lsi.agent_id='$agent_id' ";
        $sql .= " GROUP BY sdate, lsi.agent_id  ";
        //echo $sql;die;
        return $this->getDB()->query($sql);
    }


    public function numVivrSummary($dateinfo, $ivr_id = '*', $source = '*')
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return 0;
        }

        $from = $dateinfo->sdate . " " . $dateinfo->stime . ':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime . ':59:59';

        $sql = "SELECT COUNT(*) AS total_record FROM vivr_log  ";
        $sql .= " where start_time BETWEEN '{$from}' AND '{$to}' ";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND ivr_id='{$ivr_id}' " : "";
        $sql .= !empty($source) && $source != '*' ? " AND source='{$source}' " : "";
        $sql .= " GROUP BY DATE(start_time), ivr_id";

        $response = $this->getDB()->query($sql);

        return !empty($response) && is_array($response) ? count($response) : 0;
    }

    public function getVivrSummary($dateinfo, $ivr_id, $source = '*', $offset = 0, $limit = 20, $isGroupBy = true)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $from = $dateinfo->sdate . " " . $dateinfo->stime . ':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime . ':59:59';

        $sql = "SELECT DATE(start_time) as sdate, ivr_id, count(session_id) as total_hit, SUM(IF(skill_id ='', 1, 0)) as ivr_only, SUM(time_in_ivr) as time_in_ivr ";
        $sql .= "FROM vivr_log WHERE start_time BETWEEN '{$from}' AND '{$to}' ";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND ivr_id='{$ivr_id}' " : "";
        $sql .= !empty($source) && $source != '*' ? " AND source='{$source}' " : "";
        if ($isGroupBy)
            $sql .= "GROUP BY sdate, ivr_id ";

        $sql .= "LIMIT {$limit} OFFSET {$offset} ";

        return $this->getDB()->query($sql);
    }

    public function numVivrDetails($dateinfo, $ivr_id, $source, $did)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $from = $dateinfo->sdate . " " . $dateinfo->stime.':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime.':59:59';

        $sql = "SELECT COUNT(*) AS total_record ";
        $sql .= "FROM vivr_log WHERE start_time BETWEEN '{$from}' AND '{$to}'";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND ivr_id='{$ivr_id}' " : "";
        $sql .= !empty($source) && $source != '*' ? " AND source='{$source}' " : "";
        $sql .= !empty($did) && $did != '*' ? " AND did='{$did}' " : "";

        $response = $this->getDB()->query($sql);

        return !empty($response) ? $response[0]->total_record : 0;
    }

    public function getVivrDetails($dateinfo, $ivr_id, $source, $did, $offset = 0, $limit = 20, $isSum = false)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $from = $dateinfo->sdate . " " . $dateinfo->stime . ':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime . ':59:59';

        $sql = "SELECT start_time, stop_time, ivr_id, cli, did, time_in_ivr, source ";
        if ($isSum) {
            $sql = "SELECT start_time, stop_time, ivr_id, cli, did, SUM(time_in_ivr) AS time_in_ivr ";
        }
        $sql .= "FROM vivr_log  ";

        $sql .= " WHERE start_time BETWEEN '{$from}' AND '{$to}' ";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND ivr_id='{$ivr_id}' " : "";
        $sql .= !empty($source) && $source != '*' ? " AND source='{$source}' " : "";
        $sql .= !empty($did) && $did != '*' ? " AND did='{$did}' " : "";
        $sql .= " LIMIT {$limit} OFFSET {$offset}";

        return $this->getDB()->query($sql);
    }

    function numVivrIceSummaryReport($date_info, $ivr_id, $report_type = REPORT_DAILY, $source, $did)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = $date_info->sdate . " " . $date_info->stime . ':00:00';
        $to = $date_info->edate . " " . $date_info->etime . ':59:59';

        $table = 'vivr_log';
        $group_by = '';
        $date_condition = " start_time BETWEEN '{$from}' AND '{$to}' ";
        $ivr_condition = (!empty($ivr_id) && $ivr_id != '*') ? " AND ivr_id IN('{$ivr_id}') " : "";
        $source_condition = !empty($source) && $source != '*' ? " AND source='{$source}' " : "";
        $did_condition = !empty($did) && $did != '*' ? " AND did='{$did}' " : "";

        if ($report_type == REPORT_MONTHLY) {
            $sql = "SELECT MONTHNAME(start_time) as smonth, ivr_id as ivr, count(*) as hit_count 
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'Y' AND MONTHNAME(start_time) = smonth AND {$date_condition}  {$source_condition}  {$did_condition}) as positive_ice_count
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'N' AND MONTHNAME(start_time) = smonth AND {$date_condition}  {$source_condition}  {$did_condition} ) as negative_ice_count ";

            $group_by = " GROUP BY smonth, ivr_id ";
        } elseif ($report_type == REPORT_DAILY) {
            $sql = "SELECT DATE(start_time) as sdate, ivr_id as ivr, count(*) as hit_count 
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'Y' AND DATE(start_time) = sdate  {$source_condition}  {$did_condition}) as positive_ice_count
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'N' AND DATE(start_time) = sdate  {$source_condition}  {$did_condition}) as negative_ice_count";

            $group_by = " GROUP BY sdate, ivr_id ";
        } else {
            $sql = "SELECT DATE(start_time) as sdate, ivr_id as ivr, count(*) as hit_count 
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'Y' AND DATE(start_time) = sdate {$source_condition}  {$did_condition}) as positive_ice_count
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'N' AND DATE(start_time) = sdate {$source_condition}  {$did_condition}) as negative_ice_count";

            $group_by = " GROUP BY sdate, ivr_id ";
        }
        $sql .= " FROM {$table} ";
        $sql .= " WHERE  {$date_condition} ";
        $sql .= " {$ivr_condition} ";
        $sql .= " {$source_condition} ";
        $sql .= " {$did_condition} ";

        $sql .= $group_by;

        $record = $this->getDB()->query($sql);

        return (!empty($record)) ? count($record) : 0;
    }

    function getVivrIceSummaryReport($dateinfo, $ivr_id, $report_type = REPORT_DAILY, $source, $did, $offset = 0, $rowsPerPage = REPORT_DATA_PER_PAGE, $isGroupBy = true)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $from = $dateinfo->sdate . " " . $dateinfo->stime . ':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime . ':59:59';

        $table = 'vivr_log';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";
        $date_condition = " start_time BETWEEN '{$from}' AND '{$to}' ";
        $ivr_condition = (!empty($ivr_id) && $ivr_id != '*') ? " AND ivr_id IN('{$ivr_id}') " : "";
        $source_condition = !empty($source) && $source != '*' ? " AND source='{$source}' " : "";
        $did_condition = !empty($did) && $did != '*' ? " AND did='{$did}' " : "";

        if ($report_type == REPORT_MONTHLY) {
            $sql = "SELECT MONTHNAME(start_time) as smonth, ivr_id as ivr, count(*) as hit_count 
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'Y' AND MONTHNAME(start_time) = smonth AND {$date_condition}  {$source_condition}  {$did_condition}) as positive_ice_count
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'N' AND MONTHNAME(start_time) = smonth AND {$date_condition}  {$source_condition}  {$did_condition} ) as negative_ice_count ";
            $order_by = " ORDER BY start_time ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
            $group_by = " GROUP BY smonth, ivr_id ";
        } elseif ($report_type == REPORT_DAILY) {
            $sql = "SELECT DATE(start_time) as sdate, ivr_id as ivr, count(*) as hit_count 
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'Y' AND DATE(start_time) = sdate {$source_condition} {$did_condition}) as positive_ice_count
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'N' AND DATE(start_time) = sdate {$source_condition} {$did_condition}) as negative_ice_count";
            $order_by = " ORDER BY start_time ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
            $group_by = " GROUP BY sdate, ivr_id ";
        } else {
            $sql = "SELECT DATE(start_time) as sdate, ivr_id as ivr, count(*) as hit_count 
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'Y' AND DATE(start_time) = sdate {$source_condition}  {$did_condition}) as positive_ice_count
            ,(SELECT count(*) from vivr_log where ivr_id = ivr AND ice_feedback = 'N' AND DATE(start_time) = sdate {$source_condition}  {$did_condition}) as negative_ice_count";
            $order_by = " ORDER BY start_time ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
            $group_by = " GROUP BY sdate, ivr_id ";
        }

        $sql .= " FROM {$table} ";
        $sql .= " WHERE  {$date_condition} ";
        $sql .= " {$ivr_condition} ";
        $sql .= " {$source_condition} ";
        $sql .= " {$did_condition} ";

//        if ($isGroupBy)
//            $sql .= $group_by;

        $sql .= $group_by;
        if ($isGroupBy) {
            $sql .= ($rowsPerPage > 0) ? $order_by : "";
        }

        return $this->getDB()->query($sql);
    }

    public function saveReportAuditRequest($report_name, $request_param){
        // $log_txt = [];
        // foreach ($request_param as $key => $value) {
        //     $log_txt[] =$key.'='.$value;
        // }
        // $log_txt = implode(';', $log_txt);
        $log_txt = json_encode($request_param);
        $this->addToAuditLog($report_name, 'R', $log_txt, '');
    }


    function numApiAccessSummaryReport($date_info, $report_type = REPORT_DAILY) {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }

        $table = 'rt_api_access';
        $conn = " WHERE ldate BETWEEN '{$date_info->sdate}' AND '{$date_info->edate}' ";
        if(!empty($date_info->stime) && !empty($date_info->etime))
            $conn .= " AND lhour BETWEEN '{$date_info->stime}' AND '{$date_info->etime}' ";

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}".$conn;
        if($report_type == REPORT_DAILY ){
            $sql .= " GROUP BY ldate, conn_name";
        }else{
            $sql .= " GROUP BY ldate, lhour, conn_name ";
        }
        $record = $this->getDB()->query($sql);
        return (!empty($record)) ? count($record) : 0;
    }

    function getApiAccessSummaryReport($dateinfo, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupby=true){

        $conn = " WHERE ldate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $conn .= " AND lhour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $table = 'rt_api_access';
        if($report_type == REPORT_DAILY && $isGroupby){
            $sql = "SELECT ldate, conn_name, SUM(total_count) AS total_count, SUM(success_count) AS success_count, SUM(error_count) AS error_count, ";
            $sql .= " SUM(timeout_count) AS timeout_count, SUM(total_response_time) AS total_response_time, total_response_time/total_count AS avg_response_time ";
            $sql .= " FROM {$table} ";
            $sql .= $conn;
            $sql .= " GROUP BY ldate, conn_name ORDER BY ldate ASC ";
            $sql .= "LIMIT $rowsPerPage OFFSET $offset";
        } elseif (empty($isGroupby)){
            $sql = "SELECT ldate, conn_name, SUM(total_count) AS total_count, SUM(success_count) AS success_count, SUM(error_count) AS error_count, SUM(timeout_count) AS timeout_count,  ";
            $sql .= " SUM(total_response_time) AS total_response_time, SUM(total_response_time)/SUM(total_count) AS avg_response_time, SUM(success_count)/SUM(total_count) AS success_ration ";
            $sql .= " FROM {$table} ";
            $sql .= $conn;
        } else {
            $sql = "SELECT *, total_response_time/total_count AS avg_response_time FROM {$table}";
            $sql .= $conn;
            //$sql .= " GROUP BY ldate, lhour, conn_name ";
            $sql .= " ORDER BY ldate ASC, lhour ASC ";
            $sql .= "LIMIT $rowsPerPage OFFSET $offset";
        }
        return $this->getDB()->query($sql);
    }

    function numApiAccessDetailsReport($date_info, $callid=""){
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $table = "log_api_access";
        $sdate = date('Y-m-d H:i:s', $date_info->ststamp);
        $edate = date('Y-m-d H:i:s', $date_info->etstamp);

        $sql = "SELECT COUNT(*) AS total_record FROM {$table} ";
        $sql .= " WHERE log_time BETWEEN '{$sdate}' AND '{$edate}' ";
        $sql .= !empty($callid) ? " AND callid = '$callid' " : "";
        $record = $this->getDB()->query($sql);

        return $record[0]->total_record;
    }

    function getApiAccessDetailsReport($date_info, $callid="", $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE){
        $table = "log_api_access";
        $sdate = date('Y-m-d H:i:s', $date_info->ststamp);
        $edate = date('Y-m-d H:i:s', $date_info->etstamp);

        $sql = "SELECT * FROM {$table} ";
        $sql .= " WHERE log_time BETWEEN '$sdate' AND '$edate' ";
        $sql .= !empty($callid) ? " AND callid = '$callid' " : "";
        $sql .= "LIMIT $rowsPerPage OFFSET $offset";
        return $this->getDB()->query($sql);
    }
    function getVivrServiceReport($date_info, $ivr_id)
    {
        $from = date('Y-m-d H:i:s', $date_info->ststamp);
        $to = date('Y-m-d H:i:s', $date_info->etstamp);

        $condition = "log_time between '$from' and '$to'";
        $group_by = "DATE(log_time), service_title_id";

        if (!empty($ivr_id) && $ivr_id != '*') {
            $condition .= " AND ivr_id = '$ivr_id'";
            $group_by .= ", ivr_id";
        }

        $table = "(SELECT DATE(log_time) AS sdate,ivr_id,service_title_id,count(*) AS total FROM vivr_journey WHERE
                  $condition GROUP BY $group_by) AS j LEFT JOIN vivr_pages AS p ON j.service_title_id = p.page_id";

        $sql = "SELECT j.sdate, j.ivr_id, j.service_title_id, j.total, p.page_heading_en FROM $table";

        return $this->getDB()->query($sql);
    }

    function getVivrMenuServiceReport($date_info, $ivr_id, $page_list, $report_type)
    {
        $from = date('Y-m-d H:i:s', $date_info->ststamp);
        $to = date('Y-m-d H:i:s', $date_info->etstamp);

        $condition = "log_time between '$from' and '$to'";
        $group_by = "DATE(log_time), service_title_id";

        if (!empty($ivr_id) && $ivr_id != '*') {
            $condition .= " AND ivr_id = '$ivr_id'";
            $condition .= " AND service_title_id IN ($page_list)";
            $group_by .= ", ivr_id";
        }

        $table = "(SELECT DATE(log_time) AS sdate,ivr_id,service_title_id,count(*) AS total FROM vivr_journey WHERE
                  $condition GROUP BY $group_by) AS j LEFT JOIN vivr_pages AS p ON j.service_title_id = p.page_id";

        if($report_type == "Weekly"){
            $group_by = "WEEK(log_time), service_title_id";
            $table = "(SELECT WEEK(log_time) AS sdate,ivr_id,service_title_id,count(*) AS total FROM vivr_journey WHERE
                  $condition GROUP BY $group_by) AS j LEFT JOIN vivr_pages AS p ON j.service_title_id = p.page_id";
        }

        $sql = "SELECT j.sdate, j.ivr_id, j.service_title_id, j.total, p.page_heading_en FROM $table";


        return $this->getDB()->query($sql);
    }

    function numQaWebChatReportData($date_info, $agent_score = null, $score_comparison = "=")
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "ls.call_start_time as sdate,
        ls.agent_id,
        lc.agent_id as evaluator_id,
        ls.skill_id,
        cdl.contact_number as msisdn,
        lc.form_total,
        lc.agent_score,
        lc.score_percentage,
        lc.evaluation_time,        
        lc.callid,
        sd.title as wrap_up_code";
        $tables = "log_evp_call lc 
        LEFT JOIN log_skill_inbound ls on lc.callid = ls.callid
        LEFT JOIN chat_detail_log cdl on ls.callid = cdl.callid
        LEFT JOIN skill_disposition_code sd on cdl.disposition_id = sd.disposition_id ";

        $conditions = "lc.skill_type = 'WC' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";

        $query = "SELECT $columns FROM $tables WHERE $conditions";

        $record = $this->getDB()->query($query);
        return (!empty($record)) ? count($record) : 0;
    }

    function getQaWebChatReportData($date_info, $agent_score = null, $score_comparison = "=", $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "ls.call_start_time as sdate,
        ls.agent_id,
        lc.agent_id as evaluator_id,
        ls.skill_id,
        cdl.contact_number as msisdn,
        cdl.name as customer_name,
        lc.form_total,
        lc.agent_score,
        lc.score_percentage,
        lc.evaluation_time,        
        lc.callid,
        sd.title as wrap_up_code";
        $tables = "log_evp_call lc 
        LEFT JOIN log_skill_inbound ls on lc.callid = ls.callid
        LEFT JOIN chat_detail_log cdl on ls.callid = cdl.callid
        LEFT JOIN skill_disposition_code sd on cdl.disposition_id = sd.disposition_id ";

        $conditions = "lc.skill_type = 'WC' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";

        $query = "SELECT $columns FROM $tables WHERE $conditions";
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        $response = $this->getDB()->query($query);
        return $response;
    }

    function numQaEmailReportData($date_info, $agent_score = null, $score_comparison = "=")
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $tables = "log_evp_call lc 
        LEFT JOIN log_email_session les ON lc.callid = les.session_id
        LEFT JOIN e_ticket_info eti ON les.ticket_id = eti.ticket_id
        LEFT JOIN email_disposition_code sd ON les.disposition_id = sd.disposition_id ";

        $conditions = "lc.skill_type = 'E' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";

        $query = "SELECT COUNT(*) AS total_record FROM $tables WHERE $conditions";

        $record = $this->getDB()->query($query);

        return (!empty($record)) ? $record[0]->total_record : 0;
    }

    function getQaEmailReportData($date_info, $agent_score = null, $score_comparison = "=", $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "les.create_time,
                    les.agent_1,
                    les.agent_2,
                    les.skill_id,
                    eti.created_for AS email,
                    eti.customer_id,
                    lc.agent_id AS evaluator_id,
                    lc.form_total,
                    lc.agent_score,
                    lc.score_percentage,
                    lc.evaluation_time as sdate,
                    lc.callid,
                    sd.title AS wrap_up_code";

        $tables = "log_evp_call lc 
				LEFT JOIN log_email_session les ON lc.callid = les.session_id
                LEFT JOIN e_ticket_info eti ON les.ticket_id = eti.ticket_id
                LEFT JOIN email_disposition_code sd ON les.disposition_id = sd.disposition_id ";

        $conditions = "lc.skill_type = 'E' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";

        $query = "SELECT $columns FROM $tables WHERE $conditions";
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        $response = $this->getDB()->query($query);
        return $response;
    }

    function getVivrWeekList($date_info)
    {
        $from = date('Y-m-d H:i:s', $date_info->ststamp);
        $to = date('Y-m-d H:i:s', $date_info->etstamp);
        $condition = "log_time between '$from' and '$to'";

        $sql = "SELECT DISTINCT WEEK(log_time) AS sdate FROM vivr_journey WHERE $condition";

        return $this->getDB()->query($sql);
    }

    function numIvrGlobalGroup($date_info, $ivr_id)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }

        $from = $date_info->sdate . " " . $date_info->stime . ':00:00';
        $to = $date_info->edate . " " . $date_info->etime . ':59:59';

        $sql = "SELECT COUNT(*) as total_count, dtmf, status_flag, SUBSTRING(branch,1,2) AS ivr_id FROM log_ivr_journey";
        $sql .= " WHERE enter_time BETWEEN '{$from}' AND '{$to}'";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND branch LIKE '" . $ivr_id . "%' " : "";

        $sql2 = $sql . " AND status_flag IN ('T', 'W') GROUP BY status_flag, ivr_id ORDER BY ivr_id";
        $sql .= " AND dtmf IN ('*','#') AND status_flag NOT IN ('T', 'W')  GROUP BY dtmf, ivr_id ORDER BY ivr_id";

        $response = $this->getDB()->query($sql);
        $response2 = $this->getDB()->query($sql2);

        if (!is_array($response) && !is_array($response2)) {
            return 0;
        } elseif (is_array($response) && !is_array($response2)) {
            return count($response);
        } elseif (!is_array($response) && is_array($response2)) {
            return count($response2);
        }
        $result = array_merge($response, $response2);

        return !empty($result) ? count($result) : 0;
    }

    function getIvrGlobalGroup($date_info, $ivr_id, $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = $date_info->sdate . " " . $date_info->stime . ':00:00';
        $to = $date_info->edate . " " . $date_info->etime . ':59:59';

        $sql = "SELECT COUNT(*) as total_count, dtmf, status_flag, SUBSTRING(branch,1,2) AS ivr_id FROM log_ivr_journey";
        $sql .= " WHERE enter_time BETWEEN '{$from}' AND '{$to}'";
        $sql .= !empty($ivr_id) && $ivr_id != '*' ? " AND branch LIKE '" . $ivr_id . "%' " : "";

        $sql2 = $sql . " AND status_flag IN ('T', 'W') GROUP BY status_flag, ivr_id ORDER BY ivr_id";
        $sql .= " AND dtmf IN ('*','#') AND status_flag NOT IN ('T', 'W') GROUP BY dtmf, ivr_id ORDER BY ivr_id";

        $response = $this->getDB()->query($sql);
        $response2 = $this->getDB()->query($sql2);

        if (!is_array($response) && !is_array($response2)) {
            return null;
        } elseif (is_array($response) && !is_array($response2)) {
            return $response;
        } elseif (!is_array($response) && is_array($response2)) {
            return $response2;
        }
        $result = array_merge($response,$response2);
        return $result;
    }

    function numDiameterBillSummary($date_info)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date('Y-m-d H:i:s', $date_info->ststamp);
        $to = date('Y-m-d H:i:s', $date_info->etstamp);

        $sql = "SELECT DATE(call_start_time) as sdate, dm_result_code, SUM(time_in_ivr) as ivr_time, SUM(hold_in_q) as wait_time,
                SUM(service_time) as agent_time, SUM(bill_duration) as bill_duration, SUM(bill_amount) as bill_amount ";
        $sql .= " FROM log_diameter_bill WHERE call_start_time BETWEEN '{$from}' AND '{$to}'";
        $sql .= " AND  bill_amount > 0 ";
        $sql .= " GROUP BY sdate, dm_result_code ";

        $record = $this->getDB()->query($sql);
        return (!empty($record)) ? count($record) : 0;
    }

    function getDiameterBillSummary($date_info, $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date('Y-m-d H:i:s', $date_info->ststamp);
        $to = date('Y-m-d H:i:s', $date_info->etstamp);

        $sql = "SELECT DATE(call_start_time) as sdate, dm_result_code, SUM(time_in_ivr) as ivr_time, SUM(hold_in_q) as wait_time,
                SUM(service_time) as agent_time, SUM(bill_duration) as bill_duration, SUM(bill_amount) as bill_amount ";
        $sql .= " FROM log_diameter_bill WHERE call_start_time BETWEEN '{$from}' AND '{$to}'";
        $sql .= " AND  bill_amount > 0 ";
        $sql .= " GROUP BY sdate, dm_result_code ";

        if ($limit > 0 && $offset >= 0) $sql .= " LIMIT {$limit} OFFSET {$offset} ";

        return $this->getDB()->query($sql);
    }

    function numDiameterBillDetails($date_info)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date('Y-m-d H:i:s', $date_info->ststamp);
        $to = date('Y-m-d H:i:s', $date_info->etstamp);

        $sql = "SELECT COUNT(call_start_time) as total_record ";
        $sql .= " FROM log_diameter_bill WHERE call_start_time BETWEEN '{$from}' AND '{$to}'";

        $record = $this->getDB()->query($sql);
        return (!empty($record)) ? $record[0]->total_record : 0;
    }

    function getDiameterBillDetails($date_info, $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date('Y-m-d H:i:s', $date_info->ststamp);
        $to = date('Y-m-d H:i:s', $date_info->etstamp);

        $sql = "SELECT * FROM log_diameter_bill";
        $sql .= " WHERE call_start_time BETWEEN '{$from}' AND '{$to}'";
        if ($limit > 0 && $offset >= 0) $sql .= " LIMIT {$limit} OFFSET {$offset} ";

        return $this->getDB()->query($sql);
    }

    function numQaSmsReportData($date_info, $agent_score = null, $score_comparison = "=")
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $tables = "log_evp_call lc 
        LEFT JOIN log_sms_detail lsd on lc.callid = lsd.callid
        LEFT JOIN skill_crm_disposition_code sd on lsd.disposition_id = sd.disposition_id ";

        $conditions = "lc.skill_type = 'S' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";

        $query = "SELECT COUNT(*) AS total_record FROM $tables WHERE $conditions";

        $record = $this->getDB()->query($query);

        return (!empty($record)) ? $record[0]->total_record : 0;
    }

    function getQaSmsReportData($date_info, $agent_score = null, $score_comparison = "=", $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "lsd.start_time as sdate,
        lsd.agent_id,
        lsd.phone_number, 
        lsd.skill_id, 
        lc.agent_id as evaluator_id,       
        lc.form_total,
        lc.agent_score,
        lc.score_percentage,
        lc.evaluation_time,        
        lc.callid,
        sd.title as wrap_up_code";

        $tables = "log_evp_call lc 
        LEFT JOIN log_sms_detail lsd on lc.callid = lsd.callid
        LEFT JOIN skill_crm_disposition_code sd on lsd.disposition_id = sd.disposition_id ";

        $conditions = "lc.skill_type = 'S' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";

        $query = "SELECT $columns FROM $tables WHERE $conditions";
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        $response = $this->getDB()->query($query);
        return $response;
    }

    function  numPdSummery($from_date, $to_date, $agent_id, $skill_id, $offset = null, $rowsPerPage = null, $isList = false) {
        if ($from_date > $to_date) {
            return 0;
        }

        $partition = UserAuth::getPartition();
        $partition_id = isset($partition['partition_id']) ? $partition['partition_id'] : "";

        $table = ' log_cdr_autodial ';

        $sql = "SELECT DATE(start_time) as sdate,
               skill_id,
               agent_id,
               COUNT(dial_count) AS total_dial,
               SUM(ring_time) as total_ring,
               SUM(talk_time) as total_talk,
               SUM(IF(status = 'A', 1, 0)) AS total_answered";

        $sql .= " FROM $table ";
        $cond = " DATE(start_time) BETWEEN '$from_date' AND '$to_date' ";
        $cond .= !empty($agent_id) && $agent_id != "*" ? " AND agent_id = '$agent_id' " : "";
        $cond .= !empty($skill_id) && $skill_id != "*" ? " AND skill_id = '$skill_id' " : "";

        $sql .= " WHERE  $cond ";
        $sql .= !empty($partition_id) ? " AND ag.partition_id= '{$partition_id}' " : "";
        $sql .= " GROUP BY DATE(start_time), skill_id, agent_id ";

        $sql .= $isList && ($rowsPerPage > 0) ? "  LIMIT $rowsPerPage OFFSET $offset" : "";

        return $this->getDB()->query($sql);
    }

    function numSmsSummaryReport($dateinfo, $skill_ids) {
        /*$dateinfo->sdate = $dateinfo->sdate." 00:00:00";
        $dateinfo->edate = $dateinfo->edate." 23:59:59";*/

        $start_time = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $end_time = date("Y-m-d H:i:s", $dateinfo->etstamp);
        $l_sdate = date('Y-m-d H:i:s', strtotime("-1 hour", $dateinfo->ststamp));
        $l_edate = date('Y-m-d H:i:s', strtotime("+1 hour", $dateinfo->etstamp));

        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $sql = "SELECT DATE(lsd.start_time) AS sdate, COUNT(lsd.session_id) AS num_rows, lsd.skill_id ";
        $sql .= " FROM log_sms_detail lsd LEFT JOIN log_skill_inbound lsi ON lsi.call_start_time BETWEEN 
                '$l_sdate' AND '$l_edate' AND lsi.callid = lsd.callid ";
        $sql .= " WHERE lsd.start_time BETWEEN '{$start_time}' AND '{$end_time}' ";
        $sql .= !empty($skill_ids) ? " AND lsd.skill_id = '{$skill_ids}' " : "";
        $sql .= " GROUP BY DATE(lsd.start_time), lsd.skill_id ";
        $record = $this->getDB()->query($sql);

        return !empty($record[0]) ? count($record[0]) : 0;
    }


    function numSmsSummaryReportOLD($dateinfo, $skill_ids, $report_type = REPORT_DAILY)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return 0;
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';

        $sql = "SELECT COUNT(*) AS total_record FROM {$table}";

        if($report_type == REPORT_YEARLY){
            $sql = "SELECT YEAR(sdate) as syear, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY syear, skill_id ";
        }elseif($report_type == REPORT_QUARTERLY ){
            $sql = "SELECT QUARTER(sdate) as quarter_no, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY quarter_no, skill_id ";
        }elseif($report_type == REPORT_MONTHLY ){
            $sql = "SELECT MONTHNAME(sdate) as smonth, COUNT(*) AS total_record FROM {$table}";
            $group_by = " GROUP BY smonth, skill_id ";
        }elseif($report_type == REPORT_DAILY ){
            $sql = "SELECT COUNT(sdate) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, skill_id ";
        }elseif($report_type == REPORT_HOURLY ){
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, skill_id ";
        }elseif($report_type == REPORT_HALF_HOURLY){
            $sql = "SELECT COUNT(*) as total_record, IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val FROM {$table}";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
        }else{
            $sql = "SELECT COUNT(*) as total_record FROM {$table}";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
        }

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";
        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        $sql .= $group_by;
        $record = $this->getDB()->query($sql);
        // Gprint($sql);
        // Gprint($record);

        // if($report_type == REPORT_15_MIN_INV)
        // return $record[0]->total_record;


        return (!empty($record)) ? $record[0]->total_record : 0;
    }

    function getSmsSummaryReport($dateinfo, $skill_ids, $service_level_time=null, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE){
        /*$dateinfo->sdate = $dateinfo->sdate." 00:00:00";
        $dateinfo->edate = $dateinfo->edate." 23:59:59";*/
        $start_time = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $end_time = date("Y-m-d H:i:s", $dateinfo->etstamp);

        $l_sdate = date('Y-m-d H:i:s', strtotime("-1 hour", $dateinfo->ststamp));
        $l_edate = date('Y-m-d H:i:s', strtotime("+1 hour", $dateinfo->etstamp));

        $sql = "SELECT DATE(lsd.start_time) AS sdate, lsd.skill_id, ";
        $sql .= " COUNT(lsd.session_id) AS total_offered, ";
        $sql .= !empty($service_level_time) ? " SUM(IF (lsd.last_out_msg_time != '0000-00-00 00:00:00' AND TIMESTAMPDIFF(SECOND, lsd.answer_time, lsd.last_out_msg_time) <= $service_level_time, 1,0 )) AS total_in_kpi, " : "";
        $sql .= " SUM(IF (lsd.last_out_msg_time != '0000-00-00 00:00:00', TIMESTAMPDIFF(SECOND, lsd.answer_time, lsd.last_out_msg_time), 0)) AS total_serve_time, ";
        $sql .= " SUM(IF (lsd.last_out_msg_time != '0000-00-00 00:00:00', 1, 0)) AS total_served, ";
        $sql .= " SUM(IF(lsd.last_update_time != '0000-00-00 00:00:00' AND lsd.last_out_msg_time != '0000-00-00 00:00:00', TIMESTAMPDIFF(SECOND, lsd.last_out_msg_time, lsd.last_update_time), 0)) AS total_wrapup_time, ";
        $sql .= " SUM(IF (lsd.last_out_msg_time != '0000-00-00 00:00:00' AND lsd.last_out_msg_time != '' AND lsi.ice_feedback = 'Y', 1, 0)) AS ice_positive_count, ";
        $sql .= " SUM(IF (lsd.last_out_msg_time != '0000-00-00 00:00:00' AND lsd.last_out_msg_time != '' AND lsi.ice_feedback = 'N', 1, 0)) AS ice_negative_count, ";
		
        $sql .= " SUM(lsd.wait_time) AS total_wait_time, ";
        $sql .= " MAX(lsd.wait_time) AS max_wait_time ";
        $sql .= " FROM log_sms_detail lsd LEFT JOIN log_skill_inbound lsi ON lsi.call_start_time BETWEEN '$l_sdate' AND '$l_edate' AND lsi.callid = lsd.callid ";
        $sql .= " WHERE DATE(lsd.start_time) BETWEEN '{$start_time}' AND '{$end_time}' ";
        $sql .= !empty($skill_ids) ? " AND lsd.skill_id = '{$skill_ids}' " : "";
        $sql .= " GROUP BY DATE(lsd.start_time), lsd.skill_id ";
        $sql .= $rowsPerPage > 0 ? " LIMIT $offset, $rowsPerPage " : "";
        return $this->getDB()->query($sql);
    }

    function getSmsSummaryReportOLD($dateinfo, $skill_ids, $report_type = REPORT_DAILY, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy=true)
    {
        if($dateinfo->sdate > $dateinfo->edate){
            return [];
        }

        $table = 'rt_skill_call_summary';
        $group_by = '';
        $order_by = "";
        $selected_report_type = "";

        if($report_type == REPORT_YEARLY){
            $selected_report_type = ", YEAR(sdate) as syear ";
            $group_by = " GROUP BY syear, skill_id ";
            $order_by = " ORDER BY syear ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_QUARTERLY ){
            $selected_report_type = ", QUARTER(sdate) as quarter_no ";
            $group_by = " GROUP BY quarter_no, skill_id ";
            $order_by = " ORDER BY quarter_no ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_MONTHLY ){
            $selected_report_type = ", MONTHNAME(sdate) as smonth ";
            $group_by = " GROUP BY smonth, skill_id ";
            $order_by = " ORDER BY smonth ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_DAILY ){
            $group_by = " GROUP BY sdate, skill_id ";
            $order_by = " ORDER BY sdate ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HOURLY ){
            $group_by = " GROUP BY sdate, shour, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }elseif($report_type == REPORT_HALF_HOURLY ){
            $selected_report_type = ", IF(ROUND(sminute/30) >= 1,1,0) as hour_minute_val ";
            $group_by = " GROUP BY sdate, shour, hour_minute_val, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, hour_minute_val ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }else{
            // $sql = "SELECT * FROM $table";
            $group_by = " GROUP BY sdate, shour, sminute, skill_id ";
            $order_by = " ORDER BY sdate ASC, shour ASC, sminute ASC, skill_id ";
            $order_by .= "LIMIT $rowsPerPage OFFSET $offset";
        }

        $sql = "SELECT sdate, shour,sminute, skill_id, SUM(rgb_call_count) as rgb_call_count, SUM(calls_offered) as calls_offered, ";
        $sql .= "SUM(calls_answerd) as calls_answerd, SUM(calls_abandoned) as calls_abandoned, ";
        $sql .= "SUM(ans_lte_10_count) as ans_lte_10_count, SUM(ans_lte_20_count) as ans_lte_20_count, SUM(ans_lte_30_count) as ans_lte_30_count, ";
        $sql .= "SUM(ans_lte_60_count) as ans_lte_60_count, SUM(ans_lte_90_count) as ans_lte_90_count, SUM(ans_lte_120_count) as ans_lte_120_count, ";
        $sql .= "SUM(ans_gt_120_count) as ans_gt_120_count, SUM(abd_lte_10_count) as abd_lte_10_count, SUM(abd_lte_20_count) as abd_lte_20_count, ";
        $sql .= "SUM(abd_lte_30_count) as abd_lte_30_count, SUM(abd_lte_60_count) as abd_lte_60_count, SUM(abd_lte_90_count) as abd_lte_90_count, ";
        $sql .= "SUM(abd_lte_120_count) as abd_lte_120_count, SUM(abd_gt_120_count) as abd_gt_120_count, SUM(ring_time) as ring_time,  ";
        $sql .= "SUM(service_duration) as service_duration, SUM(wrap_up_time) as wrap_up_time, SUM(agent_hold_time) as agent_hold_time, ";
        $sql .= "SUM(fcr_call_count) as fcr_call_count, SUM(short_call_count) as short_call_count, SUM(wrap_up_call_count) as wrap_up_call_count, ";
        $sql .= "SUM(repeat_1_count) as repeat_1_count, SUM(agent_hangup_count) as agent_hangup_count, SUM(repeat_cli_1_count) as repeat_cli_1_count, ";
        $sql .= "SUM(hold_time_in_queue) as hold_time_in_queue ";
        if(!empty($selected_report_type))
            $sql .= $selected_report_type;
        $sql .= "FROM {$table} ";

        $sql .= " WHERE sdate BETWEEN '{$dateinfo->sdate}' AND '{$dateinfo->edate}' ";
        if(!empty($dateinfo->stime) && !empty($dateinfo->etime))
            $sql .= " AND shour BETWEEN '{$dateinfo->stime}' AND '{$dateinfo->etime}' ";

        $sql .= (!empty($skill_ids) && $skill_ids!='*') ? " AND skill_id IN('{$skill_ids}') " : "";

        if($isGroupBy)
            $sql .= $group_by;

        $sql .= ($rowsPerPage > 0) ? $order_by : "";
        // echo $sql;



        return $this->getDB()->query($sql);
    }

    function numSmsLog($dateinfo, $phone_number = null)
    {
        $start_time = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $end_time = date("Y-m-d H:i:s", $dateinfo->etstamp);

        //$sql = "SELECT COUNT(start_time) AS total_record FROM log_sms_detail lsd LEFT JOIN skill_crm_disposition_code dc ";
        // $sql .= " ON lsd.disposition_id = dc.disposition_id";
        $sql = "SELECT COUNT(start_time) AS total_record FROM log_sms_detail lsd ";
        $sql .= " WHERE lsd.start_time BETWEEN '$start_time' and '$end_time' ";
        if ($phone_number != null) $sql .= " AND lsd.phone_number = '$phone_number' ";
        $result = $this->getDB()->query($sql);
        //var_dump($result);

        return (!empty($result)) ? $result[0]->total_record : 0;
    }

    function getSmsLog($dateinfo,  $phone_number = null, $offset = 0, $limit = 0)
    {
        $start_time = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $end_time = date("Y-m-d H:i:s", $dateinfo->etstamp);

        $l_sdate = date('Y-m-d H:i:s', strtotime("-1 hour", $dateinfo->ststamp));
        $l_edate = date('Y-m-d H:i:s', strtotime("+1 hour", $dateinfo->etstamp));

        $sql = "SELECT *,lsd.status as status_code, lsi.ice_feedback as customer_feedback FROM log_sms_detail lsd ";
        $sql .= "LEFT JOIN log_skill_inbound as lsi ON lsi.call_start_time BETWEEN '$l_sdate' AND '$l_edate' AND lsi.callid = lsd.callid ";
        $sql .= "LEFT JOIN skill_crm_disposition_code dc ON lsd.disposition_id = dc.disposition_id ";
        $sql .= " WHERE lsd.start_time BETWEEN '$start_time' and '$end_time' ";
//        $sql = "SELECT * FROM log_sms_detail WHERE ";
//        $sql .= " start_time BETWEEN '$start_time' and '$end_time'";
        if ($phone_number != null) $sql .= " AND lsd.phone_number = '$phone_number' ";
        if ($limit > 0) $sql .= " LIMIT $offset, $limit";
        // echo $sql;

        return $this->getDB()->query($sql);
    }

    public function getAgentObSkillSetAsString($agent_id='')
    {
        $skill_sets = [];

        $sql = "SELECT agent_id, skill_name  FROM agent_skill ";
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

    public function numAgentPerformanceObmSummary($dateinfo)
    {
        $scdl_sdate = date('Y-m-d H:i:s', strtotime("-2 hour", $dateinfo->ststamp));
        $scdl_edate = date('Y-m-d H:i:s', strtotime("+2 hour", $dateinfo->etstamp));

        $sql = "SELECT COUNT(las.tstamp) AS aggregate ";
        $sql .= "FROM log_agent_session as las ";
        $sql .= "LEFT JOIN agent_skill as ask ON ask.agent_id=las.agent_id ";
        $sql .= "LEFT JOIN skill as s ON s.skill_id=ask.skill_id ";
        $sql .= "WHERE las.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND s.skill_type IN('O') ";
        $sql .= "GROUP BY las.agent_id ";
        $result = $this->getDB()->query($sql);
        // echo $sql;
        // die();

        return !empty($result) ? count($result): 0;
    }

    public function getAgentPerformanceObmSummary($dateinfo, $offset=0, $limit=20)
    {
        $scdl_sdate = date('Y-m-d H:i:s', strtotime("-2 hour", strtotime($dateinfo->sdate)));
        $scdl_edate = date('Y-m-d H:i:s', strtotime("+2 hour", strtotime($dateinfo->edate)));

        $sql = "SELECT las.agent_id ";
        $sql .= "FROM log_agent_session as las ";
        $sql .= "LEFT JOIN agent_skill as ask ON ask.agent_id=las.agent_id ";
        $sql .= "LEFT JOIN skill as s ON s.skill_id=ask.skill_id ";
        $sql .= "WHERE las.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND s.skill_type IN('O') ";
        $sql .= "GROUP BY las.agent_id ";
        $sql .= "ORDER BY las.agent_id LIMIT $offset, $limit ";

        return $this->getDB()->query($sql);
    }

    public function numAgentPerformanceSummary2($dateinfo, $type="")
    {
        $scdl_sdate = date('Y-m-d H:i:s', strtotime("-2 hour", $dateinfo->ststamp));
        $scdl_edate = date('Y-m-d H:i:s', strtotime("+2 hour", $dateinfo->etstamp));
        
        $skill_type_cond = " NOT IN('O', 'E', 'S', 'C') ";
        if (!empty($type) && $type=="E")
            $skill_type_cond = " IN('E') ";
        elseif(!empty($type) && $type=="S")
            $skill_type_cond = " IN('S') ";
        elseif(!empty($type) && $type=="C")
            $skill_type_cond = " IN('C') ";
        elseif(!empty($type) && $type=="O")
            $skill_type_cond = " IN('O') ";

        $sql = "SELECT COUNT(las.tstamp) AS aggregate ";
        $sql .= "FROM log_agent_session as las ";
        $sql .= "LEFT JOIN agent_skill as ask ON ask.agent_id=las.agent_id ";
        $sql .= "LEFT JOIN skill as s ON s.skill_id=ask.skill_id ";
        $sql .= "WHERE las.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND s.skill_type ".$skill_type_cond;
        $sql .= "GROUP BY las.agent_id ";
        $result = $this->getDB()->query($sql);
        // echo $sql;
        // die();

        return !empty($result) ? count($result): 0;
    }

    public function getAgentPerformanceSummary2($dateinfo, $offset=0, $limit=20, $type="")
    {
        $scdl_sdate = date('Y-m-d H:i:s', strtotime("-2 hour", $dateinfo->ststamp));
        $scdl_edate = date('Y-m-d H:i:s', strtotime("+2 hour", $dateinfo->etstamp));

        $skill_type_cond = " NOT IN('O', 'E', 'S', 'C') ";
        if (!empty($type) && $type=="E")
            $skill_type_cond = " IN('E') ";
        elseif(!empty($type) && $type=="S")
            $skill_type_cond = " IN('S') ";
        elseif(!empty($type) && $type=="C")
            $skill_type_cond = " IN('C') ";
        elseif(!empty($type) && $type=="O")
            $skill_type_cond = " IN('O') ";

        $sql = "SELECT las.agent_id ";
        $sql .= "FROM log_agent_session as las ";
        $sql .= "LEFT JOIN agent_skill as ask ON ask.agent_id=las.agent_id ";
        $sql .= "LEFT JOIN skill as s ON s.skill_id=ask.skill_id ";
        $sql .= "WHERE las.tstamp BETWEEN '{$scdl_sdate}' AND '{$scdl_edate}' AND s.skill_type ".$skill_type_cond;
        $sql .= "GROUP BY las.agent_id ";
        $sql .= "ORDER BY las.agent_id LIMIT $offset, $limit ";
		//echo $sql;
		//die();

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
        // Gprint($response);
        // die();

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

    public function getAgentCallInformation($dateinfo, $agent_id, $type=""){
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);
		
		$skill_type_cond = " NOT IN('O', 'E', 'S', 'C') ";
        if (!empty($type) && $type=="E")
            $skill_type_cond = " IN('E') ";
        elseif(!empty($type) && $type=="S")
            $skill_type_cond = " IN('S') ";
        elseif(!empty($type) && $type=="C")
            $skill_type_cond = " IN('C') ";

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
        $sql .= "WHERE lai.call_start_time BETWEEN '{$sdate}' AND '{$edate}' AND lai.agent_id = '{$agent_id}' ";
        $sql .= "AND call_type ".$skill_type_cond;
        //echo $sql;
        //die();
		
        $result = $this->getDB()->query($sql);

        return (!empty($result)) ? array_shift($result) : [];
    }

    public function getAgentObmCallInformation($dateinfo, $agent_id){
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $sql = "SELECT laom.agent_id, count(laom.agent_id) as calls_out_attempt, ";
        $sql .= "SUM(CASE WHEN laom.is_reached='Y' THEN 1 ELSE 0 END ) AS calls_answered, ";
        $sql .= "SUM(laom.ring_time) AS ring_time, ";
        $sql .= "SUM(laom.wrap_up_time) AS wrap_up_time, SUM(laom.service_time) AS service_time, ";
        $sql .= "SUM(laom.hold_time) AS hold_time, ";
        $sql .= "SUM(CASE WHEN (laom.is_reached='Y' AND  laom.disc_party='A') THEN 1 ELSE 0 END)  AS agent_hangup, ";
        $sql .= "SUM(CASE WHEN (laom.is_reached='N' AND  laom.disc_party='A') THEN 1 ELSE 0 END) AS agent_reject_calls, ";
        $sql .= "SUM(CASE WHEN laom.disc_party='A' THEN 1 ELSE 0 END ) AS agent_disc_calls, ";
        $sql .= "SUM(laom.disposition_count) AS workcode_count ";
        $sql .= "FROM log_agent_outbound_manual AS laom ";
        $sql .= "WHERE laom.start_time BETWEEN '{$sdate}' AND '{$edate}' AND laom.agent_id = '{$agent_id}' ";
        $sql .= "AND laom.skill_id!='' ";
        //echo $sql;
        //die();

        $result = $this->getDB()->query($sql);

        return (!empty($result)) ? array_shift($result) : [];
    }

    function numVivrIceDetailsReport($dateinfo, $ivr_id)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $from = $dateinfo->sdate . " " . $dateinfo->stime . ':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime . ':59:59';

        $table = 'vivr_log';

        $condition = " start_time BETWEEN '{$from}' AND '{$to}' ";
        $condition .= (!empty($ivr_id) && $ivr_id != '*') ? " AND ivr_id IN('{$ivr_id}') " : "";
        $order_by = " ORDER BY start_time ";

        $sql = "SELECT COUNT(*) as total_record FROM {$table} WHERE  {$condition} ";
        $sql .= " $order_by ";

        $result = $this->getDB()->query($sql);

        return (!empty($result)) ? $result[0]->total_record : 0;

    }

    function getVivrIceDetailsReport($dateinfo, $ivr_id, $offset = 0, $rowsPerPage = REPORT_DATA_PER_PAGE, $isGroupBy = true)
    {
        if ($dateinfo->sdate > $dateinfo->edate) {
            return [];
        }
        $from = $dateinfo->sdate . " " . $dateinfo->stime . ':00:00';
        $to = $dateinfo->edate . " " . $dateinfo->etime . ':59:59';

        $table = 'vivr_log';

        $condition = " start_time BETWEEN '{$from}' AND '{$to}' ";
        $condition .= (!empty($ivr_id) && $ivr_id != '*') ? " AND ivr_id IN('{$ivr_id}') " : "";
        $order_by = " ORDER BY start_time ";

        $sql = "SELECT * FROM {$table} WHERE  {$condition} ";
        $sql .= " $order_by ";
        $sql .= ($rowsPerPage > 0) ? "  LIMIT $rowsPerPage OFFSET $offset" : "";

        return $this->getDB()->query($sql);
    }

    function getEmailPerformanceActivity($dateinfo, $skills=null){
        if (empty($dateinfo->sdate) || ($dateinfo->sdate > $dateinfo->edate))
            return [];

        $sdate = $dateinfo->ststamp;
        $edate = $dateinfo->etstamp;

        // $sdate = strtotime($dateinfo->sdate);
        // $edate = strtotime($dateinfo->edate);

        $sql = "SELECT agent_id, SUM(IF(activity='S' AND activity_details='P', 1, 0)) AS pending, ";
        $sql .= "SUM(IF(activity='S' AND activity_details='C', 1, 0)) AS pen_client, SUM(IF(activity='S' AND activity_details='S', 1, 0)) AS served, ";
        $sql .= "SUM(IF(activity='S' AND activity_details='E', 1, 0)) AS closed, SUM(IF(activity='S' AND activity_details='R', 1, 0)) AS rescheduled, ";
        $sql .= "SUM(IF(activity='S' AND activity_details='K', 1, 0)) AS park, SUM(IF(activity='S' AND activity_details='O', 1, 0)) AS new, ";
        $sql .= "SUM(IF(activity='V', 1, 0)) AS view, SUM(IF(activity='P', 1, 0)) AS pull, SUM(IF(activity='M' OR activity='F', 1, 0)) AS mail_send, ";
        $sql .= "SUM(IF(activity='D', 1, 0)) AS disposition ";
        $sql .= "FROM e_ticket_activity ";
        $sql .= "WHERE activity_time BETWEEN '{$sdate}' AND '{$edate}' AND LENGTH(agent_id) <= 4 ";
        $sql .= !empty($skills) ? " AND agent_id IN ('".implode("','", $skills)."') " : "";
        $sql .= "GROUP BY agent_id ASC";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }

    function numEvaluationSummaryData($dateInfo, $service_id, $agent_id)
    {
        if($dateInfo->sdate > $dateInfo->edate){
            return [];
        }
        $from = date("Y-m-d H:i:s", $dateInfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateInfo->etstamp);

        $table = ' log_evp_call lec LEFT JOIN agents a on lec.agent_id = a.agent_id ';

        $sql = "SELECT DATE(lec.evaluation_time) as sdate, count(lec.evaluation_time) as evaluate_count, lec.agent_id, lec.skill_type, a.name ";
        $sql .= "FROM {$table} ";
        $sql .= " WHERE evaluation_time BETWEEN '{$from}' AND '{$to}'  ";
        $sql .= (!empty($agent_id) && $agent_id!='*') ? " AND lec.agent_id = '$agent_id'" : "";
        $sql .= (!empty($service_id) && $service_id!='*') ? " AND lec.skill_type = '$service_id' " : "";
        $sql .= " GROUP BY sdate, lec.skill_type, lec.agent_id";

        $result = $this->getDB()->query($sql);
        return (!empty($result)) ? count($result) : 0;
    }

    function getEvaluationSummaryData($dateInfo, $service_id, $agent_id, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE, $isGroupBy = true)
    {
        if($dateInfo->sdate > $dateInfo->edate){
            return [];
        }
        $from = date("Y-m-d H:i:s", $dateInfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateInfo->etstamp);

        $table = ' log_evp_call lec LEFT JOIN agents a on lec.agent_id = a.agent_id ';

        $sql = "SELECT DATE(lec.evaluation_time) as sdate, count(lec.evaluation_time) as evaluate_count, lec.agent_id, lec.skill_type, a.name ";
        $sql .= "FROM {$table} ";
        $sql .= " WHERE evaluation_time BETWEEN '{$from}' AND '{$to}'  ";
        $sql .= (!empty($agent_id) && $agent_id!='*') ? " AND lec.agent_id = '$agent_id'" : "";
        $sql .= (!empty($service_id) && $service_id!='*') ? " AND lec.skill_type = '$service_id' " : "";

        if($isGroupBy)
            $sql .= " GROUP BY sdate, lec.skill_type, lec.agent_id";

        $order_by = " ORDER BY sdate ASC, lec.agent_id ";
        $order_by .= "LIMIT $rowsPerPage OFFSET $offset";

        $sql .= ($rowsPerPage > 0) ? $order_by : "";

        return $this->getDB()->query($sql);
    }

    function numCustomerJourneyAgentActivitySummary($dateinfo, $searchObj){
        if (empty($dateinfo->sdate) || ($dateinfo->sdate > $dateinfo->edate))
            return 0;

        $sql = "SELECT COUNT(agent_id) FROM log_customer_journey_activity ";
        $sql .= " WHERE sdate BETWEEN '$dateinfo->sdate' AND '$dateinfo->edate' ";
        $sql .= !empty($searchObj->agent_id) ? " AND agent_id='$searchObj->agent_id' " : "";
        $sql .= !empty($searchObj->skill_id) ? " AND skill_id='$searchObj->skill_id' " : "";
        //$sql .= " GROUP BY sdate, agent_id, skill_id ORDER BY sdate";
        $sql .= " GROUP BY sdate, agent_id ORDER BY sdate";
        $result = $this->getDB()->query($sql);
        return (!empty($result)) ? count($result) : 0;
    }

    function getCustomerJourneyAgentActivitySummary($dateinfo, $searchObj, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE){
        if (empty($dateinfo->sdate) || ($dateinfo->sdate > $dateinfo->edate))
            return [];

        $sql = "SELECT *, count(agent_id) AS activity FROM log_customer_journey_activity ";
        $sql .= " WHERE sdate BETWEEN '$dateinfo->sdate' AND '$dateinfo->edate' ";
        $sql .= !empty($searchObj->agent_id) ? " AND agent_id='$searchObj->agent_id' " : "";
        $sql .= !empty($searchObj->skill_id) ? " AND skill_id='$searchObj->skill_id' " : "";
        //$sql .= " GROUP BY sdate, agent_id, skill_id ORDER BY sdate";
        $sql .= " GROUP BY sdate, agent_id ORDER BY sdate";
        $sql .= ($rowsPerPage > 0) ? "  LIMIT $rowsPerPage OFFSET $offset" : "";
        return $this->getDB()->query($sql);
    }

    function numCustomerJourneyReport($dateinfo, $searchObj) {
        if (empty($dateinfo->sdate) || ($dateinfo->sdate > $dateinfo->edate))
            return 0;

        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $sql = "SELECT COUNT(customer_id) as total_record FROM log_customer_journey ";
        $sql .= " WHERE log_time BETWEEN '$sdate' AND '$edate' ";
        $sql .= !empty($searchObj->customer_id) ? " AND customer_id='$searchObj->customer_id' " : "";
        $sql .= !empty($searchObj->module_type) ? " AND module_type='$searchObj->module_type' " : "";
        $sql .= " ORDER BY log_time";

        $result = $this->getDB()->query($sql);
        // echo $sql;
        return (!empty($result[0]->total_record)) ? $result[0]->total_record : 0;
    }

    function getCustomerJourneyReport($dateinfo, $searchObj, $offset=0, $rowsPerPage=REPORT_DATA_PER_PAGE){
        if (empty($dateinfo->sdate) || ($dateinfo->sdate > $dateinfo->edate))
            return [];

        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $sql = "SELECT * FROM log_customer_journey ";
        $sql .= " WHERE log_time BETWEEN '$sdate' AND '$edate' ";
        $sql .= !empty($searchObj->customer_id) ? " AND customer_id='$searchObj->customer_id' " : "";
        $sql .= !empty($searchObj->module_type) ? " AND module_type='$searchObj->module_type' " : "";
        $sql .= " ORDER BY log_time";
        $sql .= ($rowsPerPage > 0) ? "  LIMIT $rowsPerPage OFFSET $offset" : "";
        return $this->getDB()->query($sql);
    }

    function getMaxWaitTimeData($dateinfo, $skill_id, $report_type)
    {
        if (empty($dateinfo->sdate) || ($dateinfo->sdate > $dateinfo->edate))
            return [];
        $sdate = date('Y-m-d H:i:s', $dateinfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateinfo->etstamp);

        $sql = "SELECT SUM(wait_time) as total_in_queue, MAX(wait_time) AS max_wait_time, skill_id, DATE(start_time) AS sdate FROM log_sms_detail ";
        $sql .= " WHERE start_time BETWEEN '$sdate' AND '$edate' ";
        $sql .= (!empty($skill_id) && $skill_id != '*') ? " AND skill_id = '$skill_id'" : "";
        $sql .= " GROUP BY sdate ASC, skill_id ";

        return $this->getDB()->query($sql);
    }

    function numPdQaReportData($date_info, $agent_score = null, $score_comparison = "=")
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $tables = "log_evp_call lc 
        LEFT JOIN log_skill_inbound ls on lc.callid = ls.callid 
        LEFT JOIN skill_crm_disposition_code sd on ls.disposition_id = sd.disposition_id";

        $conditions = "lc.skill_type = 'P' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";

        $query = "SELECT COUNT(*) AS total_record FROM $tables WHERE $conditions";

        $record = $this->getDB()->query($query);
        return (!empty($record)) ? $record[0]->total_record : 0;
    }

    function getPdQaReportData($date_info, $agent_score = null, $score_comparison = "=", $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "ls.call_start_time as call_start_time,
        ls.agent_id,
        lc.agent_id as evaluator_id,
        ls.skill_id,
        ls.cli as msisdn,
        lc.form_total,
        lc.agent_score,
        lc.score_percentage,
        lc.evaluation_time as sdate,        
        lc.callid,
        sd.title as wrap_up_code";
        $tables = "log_evp_call lc 
        LEFT JOIN log_skill_inbound ls on lc.callid = ls.callid
        LEFT JOIN skill_crm_disposition_code sd on ls.disposition_id = sd.disposition_id ";

        $conditions = "lc.skill_type = 'P' AND lc.evaluation_time BETWEEN '$from' AND '$to'";
        if ($agent_score != null) $conditions .= " AND lc.agent_score $score_comparison $agent_score ";
        $query = "SELECT $columns FROM $tables WHERE $conditions";
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        return $this->getDB()->query($query);
    }

    function getWebchatAgentPerformance($dateinfo, $agent_ary, $offset=0, $limit=20, $downloadFlag){
        if (empty($dateinfo->sdate) || ($dateinfo->sdate > $dateinfo->edate))
            return 0;

        $sdate = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $edate = date("Y-m-d H:i:s", $dateinfo->etstamp);

        //updated by sanaul Bhai
        $sql = "SELECT DATE(lsi.call_start_time) AS csdate, lsi.skill_id, count(*) AS offered_chat, lsi.agent_id, ";
        $sql .= " SUM(IF(lsi.`status` = 'S', 1, 0)) AS total_chat, ";
        //$sql .= " SUM( IF (lai.hold_in_q <= '20' AND lsi.`status` = 'S', 1, 0) ) AS in_kpi, ";
        $sql .= " SUM(IF(TIMESTAMPDIFF(SECOND, lsi.call_start_time, lai.start_time) <= '".WEB_CHAT_SL_TIME."' AND lsi.`status` = 'S', 1, 0) ) AS in_kpi, ";

        if($dateinfo->sdate >= WEBCHAT_AGENT_RESPONSE){
            $sql .= " SUM(IF(TIMESTAMPDIFF(SECOND, lai.start_time, cdl.agent_first_response) <= '".WEB_CHAT_SL_TIME."' AND lsi.`status` = 'S', 1, 0) ) AS in_kpi_new, ";
            $sql .= " SUM(IF((cdl.agent_first_response = '0000-00-00 00:00:00' OR cdl.agent_first_response IS NULL) AND TIMESTAMPDIFF(SECOND, lai.start_time, cdl.agent_first_response) IS NULL AND lsi.`status` = 'S', 1, 0) ) AS abd_new, ";
            $sql .= " SUM(IF(cdl.agent_first_response != '0000-00-00 00:00:00', TIMESTAMPDIFF(SECOND, lai.start_time, lsi.tstamp), 0)) AS total_service_time, ";
            //$sql .= " SUM(IF(cdl.agent_first_response != '0000-00-00 00:00:00', TIMESTAMPDIFF(SECOND, lsi.call_start_time, lai.start_time), 0)) AS total_wait_time, ";
            $sql .= " SUM(IF(lsi.STATUS = 'S', TIMESTAMPDIFF(SECOND, lsi.call_start_time, lai.start_time),0)) AS total_wait_time, ";
        }else{
            $sql .= " SUM(TIMESTAMPDIFF(SECOND, lai.start_time, lsi.tstamp)) AS total_service_time, ";
            $sql .= " SUM(TIMESTAMPDIFF(SECOND, lsi.call_start_time, lai.start_time)) AS total_wait_time, ";
        }
        $sql .= " SUM( IF(cdl.verify_user = 'Y', 1, 0) ) AS verified, ";
        // $sql .= " SUM( IF(cdl.verify_user = 'N', 1, 0) ) AS non_verified, ";
        $sql .= " SUM(IF(lsi.STATUS != 'S', lsi.hold_in_q, 0)) AS total_abd_wait_time, ";
        $sql .= " SUM(lsi.hold_in_q) AS total_hold_in_q ";
        $sql .= " FROM log_skill_inbound lsi LEFT JOIN log_agent_inbound lai ON lai.callid = lsi.callid";
        $sql .= " LEFT JOIN chat_detail_log cdl ON cdl.callid = lsi.callid";
        $sql .= " WHERE ";
        $sql .= " lsi.call_start_time between '{$sdate}' and '{$edate}' ";
        $sql .= " AND lsi.call_type='C' ";
        $sql .= !empty($skill_id) ? " AND lsi.skill_id='$skill_id' " : "";
        $sql .= !empty($agent_ary) ? " AND lsi.agent_id IN ('".implode("','", $agent_ary)."') " : "";
        $sql .= " GROUP BY lsi.agent_id ORDER BY csdate ASC LIMIT {$offset}, {$limit}";

        $result = $this->getDB()->query($sql);
        $response = [];
		
        if (!empty($result)){
            foreach ($result as $key){				
                $obj = new stdClass();
                $obj->offered_chat = $key->offered_chat;
				$obj->total_chat = (isset($key->abd_new) && !empty($key->abd_new)) ? $key->total_chat - $key->abd_new : $key->total_chat;
                $obj->in_kpi = (!empty($key->in_kpi_new)) ? $key->in_kpi_new : $key->in_kpi;
                $obj->in_kpi_new = $key->in_kpi_new;
				//Gprint($key->total_chat);
				//Gprint($key->in_kpi);
				//Gprint($key->agent_id);
				$obj->out_kpi = !empty($obj->total_chat) ? ($obj->total_chat - $obj->in_kpi) : 0;
                $obj->total_service_time = $key->total_service_time;
                $obj->total_wait_time = $key->total_wait_time;
                $obj->verified = $key->verified;
                $obj->total_abd_wait_time = $key->total_abd_wait_time;

				$obj->sl = !empty($obj->total_chat) && !empty($obj->in_kpi) ? fractionFormat(($obj->in_kpi / $obj->total_chat) * 100, "%.2f", $downloadFlag).'%' : '0.00%';
				$obj->aht = !empty($obj->total_service_time) && !empty($obj->total_chat) ?  fractionFormat($obj->total_service_time / $obj->total_chat, "%.2f", $downloadFlag) : '';
                // var_dump($obj->aht);
				$obj->aht = gmdate("H:i:s", round($obj->aht));
				//$obj->avg_wait_time = !empty($obj->offered_chat) && !empty(round(($obj->total_wait_time+$obj->total_abd_wait_time) / $obj->offered_chat)) ? gmdate("H:i:s", round(($obj->total_wait_time+$obj->total_abd_wait_time) / $obj->offered_chat)) : "";

                $obj->abandoned_chat = $key->offered_chat - $key->total_chat;
                $obj->total_service_time = gmdate("H:i:s", $key->total_service_time);
                $obj->total_wait_time = gmdate("H:i:s", $key->total_wait_time+$key->total_abd_wait_time);
                $obj->non_verified = $key->offered_chat-$key->verified;
                $response[$key->agent_id] = $obj;
            }
        }
		
        return $response;
    }

    /*
     * SMS Agent Performance
     */
    function getSmsAgentPerformance($dateinfo, $agent_ary, $service_level_time=null){
        $sdate = date("Y-m-d H:i:s", $dateinfo->ststamp);
        $edate = date("Y-m-d H:i:s", $dateinfo->etstamp);

        $sql = "SELECT DATE(start_time) AS sdate, agent_id, ";
        $sql .= " COUNT(session_id) AS total_offered, ";
        $sql .= !empty($service_level_time) ? " SUM(IF (last_out_msg_time != '0000-00-00 00:00:00' AND TIMESTAMPDIFF(SECOND, answer_time, last_out_msg_time) <= $service_level_time, 1,0 )) AS total_in_kpi, " : "";
        $sql .= " SUM(IF (last_out_msg_time != '0000-00-00 00:00:00', TIMESTAMPDIFF(SECOND, answer_time, last_out_msg_time), 0)) AS total_serve_time, ";
        $sql .= " SUM(IF (last_out_msg_time != '0000-00-00 00:00:00', 1, 0)) AS total_served, ";
        $sql .= " SUM(IF(last_update_time != '0000-00-00 00:00:00' AND last_out_msg_time != '0000-00-00 00:00:00', TIMESTAMPDIFF(SECOND, last_out_msg_time, last_update_time), 0)) AS total_wrapup_time, ";
        $sql .= " SUM(wait_time) AS total_wait_time, ";
        $sql .= " MAX(wait_time) AS max_wait_time ";
        $sql .= " FROM log_sms_detail ";
        $sql .= " WHERE DATE(start_time) BETWEEN '{$sdate}' AND '{$edate}' ";
        $sql .= !empty($agent_ary) ? " AND agent_id IN ('".implode("','", $agent_ary)."') " : "";
        $sql .= " GROUP BY  agent_id ORDER BY sdate ASC ";
        $result = $this->getDB()->query($sql);
        $response = [];
        if (!empty($result)){
            foreach ($result as $key) {
                $obj = new stdClass();
                $obj->aht = !empty($key->total_served) ? round(($key->total_serve_time + $key->total_wrapup_time)/$key->total_served,2) : 0;
                $obj->aht = gmdate("H:i:s", round($obj->aht));
                $obj->service_level = !empty($key->total_served) ? round(($key->total_in_kpi/$key->total_served)*100, 2)."%" : "0%";
                $obj->avg_wait_time = !empty($key->total_offered) && !empty(round($key->total_wait_time/$key->total_offered)) ? gmdate("H:i:s", round($key->total_wait_time/$key->total_offered)) : "";
                $obj->total_serve_time = gmdate("H:i:s", $key->total_serve_time);
                $obj->total_wrapup_time = gmdate("H:i:s", $key->total_wrapup_time);
                $obj->total_wait_time = gmdate("H:i:s", $key->total_wait_time);
                $obj->max_wait_time = gmdate("H:i:s", $key->max_wait_time);
                $obj->total_in_kpi = $key->total_in_kpi;
                $obj->total_offered = $key->total_offered;
                $obj->total_served = $key->total_served;
                $response[$key->agent_id] = $obj;
            }
        }
        return $response;
    }

    function numAutoSms($date_info, $cli = null)
    {
        if ($date_info->sdate > $date_info->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $tables = "log_sms_blocked";

        $conditions = "log_time BETWEEN '{$from}' AND '{$to}'";
        if ($cli != null) $conditions .= " AND cli = '{$cli}'  ";

        $query = "SELECT COUNT(*) AS total_record FROM {$tables} WHERE {$conditions}";

        $record = $this->getDB()->query($query);
        return (!empty($record)) ? $record[0]->total_record : 0;
    }

    function getAutoSms($date_info, $cli = null, $offset = 0, $limit = 0)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = "log_time, cli, did, sms_text";
        $tables = "log_sms_blocked";

        $conditions = "log_time BETWEEN '{$from}' AND '{$to}'";
        if ($cli != null) $conditions .= " AND cli = '{$cli}'  ";

        $query = "SELECT {$columns} FROM {$tables} WHERE {$conditions}";
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        return $this->getDB()->query($query);
    }

    function getWebChatRawDetails($dateInfo, $skillId = null)
    {
        $sdate = date('Y-m-d H:i:s', $dateInfo->ststamp);
        $edate = date('Y-m-d H:i:s', $dateInfo->etstamp);
        if ($sdate > $edate) {
            return [];
        }
        $tables = " log_skill_inbound lsi LEFT JOIN log_agent_inbound lai ON lai.callid = lsi.callid ";
        $tables .= " LEFT JOIN chat_detail_log cdl ON cdl.callid = lsi.callid ";

        $columns = " lsi.call_start_time, lsi.tstamp, lsi.skill_id, lsi.`status`, lsi.disc_party, lsi.hold_in_q, ";
        $columns .= " lai.start_time, cdl.agent_first_response, cdl.verify_user, cdl.disposition_id, cdl.customer_feedback, ";
        $columns .= " TIMESTAMPDIFF(SECOND, lai.start_time, lsi.tstamp) as service_time ";
        $conditions = " lsi.call_start_time between '{$sdate}' and '{$edate}' ";
        $conditions .= " AND lsi.call_type='C' ";
        $conditions .= !empty($skillId) ? " AND lsi.skill_id = '$skillId' " : "";

        $sql = "SELECT {$columns} FROM {$tables}  WHERE {$conditions} ";
        return $this->getDB()->query($sql);
    }
    
	function numEvaluatorSummaryData($date_info, $agent_id = null)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = " DATE(evaluation_time) as sdate, agent_id as evaluator_id, COUNT(callid) as total_count ";
        $tables = "log_evp_call";
        $conditions = "evaluation_time BETWEEN '{$from}' AND '{$to}'";
        if ($agent_id != null) $conditions .= " AND agent_id = '{$agent_id}' ";
		$conditions .= " AND skill_type != '' ";
        $groupBy = "DATE(evaluation_time), agent_id";

        $query = "SELECT {$columns} FROM {$tables} WHERE {$conditions} GROUP BY {$groupBy}";

        $record = $this->getDB()->query($query);
        return (!empty($record)) ? count($record) : 0;
    }

    function getEvaluatorSummaryData($date_info, $agent_id = null, $limit = 0, $offset = 0, $isGroupBy = true)
    {
        if ($date_info->sdate > $date_info->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $date_info->ststamp);
        $to = date("Y-m-d H:i:s", $date_info->etstamp);

        $columns = " DATE(evaluation_time) as sdate,
                    agent_id as evaluator_id,
                    COUNT(callid) as total_count,
                    SUM(IF(skill_type = 'I', 1, 0)) as inbound,
                    SUM(IF(skill_type = 'O', 1, 0)) as outbound,
                    SUM(IF(skill_type = 'WC', 1, 0)) as webchat,
                    SUM(IF(skill_type = 'E', 1, 0)) as email,
                    SUM(IF(skill_type = 'S', 1, 0)) as sms,
                    SUM(IF(skill_type = 'P', 1, 0)) as pd ";

        $tables = "log_evp_call";
        $conditions = "evaluation_time BETWEEN '{$from}' AND '{$to}'";
        if ($agent_id != null) $conditions .= " AND agent_id = '{$agent_id}' ";
        $conditions .= " AND skill_type != '' ";
        $groupBy = "DATE(evaluation_time), agent_id";
        $query = "SELECT {$columns} FROM {$tables} WHERE {$conditions} ";
        if ($isGroupBy) {
            $query .= " GROUP BY {$groupBy} ";
        }
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        return $this->getDB()->query($query);
    }
	
	function numChatCoBrowseDetails($dateInfo, $agentId)
    {
        if ($dateInfo->sdate > $dateInfo->edate) {
            return 0;
        }
        $from = date("Y-m-d H:i:s", $dateInfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateInfo->etstamp);

        $tables = "chat_co_browse_log";

        $conditions = "log_start_time BETWEEN '{$from}' AND '{$to}'";
        if ($agentId != null) $conditions .= " AND agent_id = '{$agentId}'  ";

        $query = "SELECT COUNT(*) AS total_record FROM {$tables} WHERE {$conditions}";

        $record = $this->getDB()->query($query);
        return (!empty($record)) ? $record[0]->total_record : 0;
    }

    function getChatCoBrowseDetails($dateInfo, $agentId = null, $offset = 0, $limit = 0)
    {
        if ($dateInfo->sdate > $dateInfo->edate) {
            return [];
        }
        $from = date("Y-m-d H:i:s", $dateInfo->ststamp);
        $to = date("Y-m-d H:i:s", $dateInfo->etstamp);

        $columns = "*";
        $tables = "chat_co_browse_log";

        $conditions = "log_start_time BETWEEN '{$from}' AND '{$to}'";
        if ($agentId != null) $conditions .= " AND agent_id = '{$agentId}'  ";

        $query = "SELECT {$columns} FROM {$tables} WHERE {$conditions}";
        if ($limit > 0 && $offset >= 0) $query .= " LIMIT {$limit} OFFSET {$offset} ";

        return $this->getDB()->query($query);
    }

    function test()
    {
        $this->tables = " vivr_pages ";
        $this->columns = " page_id ";
        $this->conditions = " page_id != '' ";
        $pageIdList = $this->getResultData();

        $sql = "insert into vivr_page_title values ";
        foreach ($pageIdList as $data) {
            $sql .= " ($data->page_id, ''), ";
        }
        $sql = rtrim(trim($sql), ",");
        return $this->getDB()->query($sql);
    }
	
}
