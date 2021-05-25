<?php

class MEmailReport extends Model
{
	
	function __construct() {
		parent::__construct();
	}

	function numReportByDisposition($skillid, $did, $dateinfo)
	{
		$cond = $this->getConditionForReportByDisposition($skillid, $did, $dateinfo);
		$sql = "SELECT COUNT(DISTINCT disposition_id, skill_id) AS numrows FROM e_ticket_info AS ti ";
		/*
		if (!empty($skillid)) {
			$sql .= "LEFT JOIN email_disposition_code AS dc ON dc.disposition_id=ti.disposition_id ";
		}
		*/

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getReportByDisposition($skillid, $did, $dateinfo, $offset=0, $limit=0)
	{
		$cond = $this->getConditionForReportByDisposition($skillid, $did, $dateinfo);
		$sql = "SELECT ti.skill_id, ti.disposition_id, COUNT(ticket_id) AS num_tickets FROM e_ticket_info AS ti ";

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "GROUP BY skill_id, disposition_id ORDER BY skill_id, disposition_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getConditionForReportByDisposition($skillid, $did, $dateinfo)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('create_time', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($did)) $cond = $this->getAndCondition($cond, "ti.disposition_id='$did'");
		if (!empty($skillid)) {
			$cond = $this->getAndCondition($cond, "ti.skill_id='$skillid'");
		}
		
		return $cond;
	}
	
	function numReportByStatus($skillid, $sid, $dateinfo)
	{
		$cond = $this->getConditionForReportByStatus($skillid, $sid, $dateinfo);
		$sql = "SELECT COUNT(DISTINCT skill_id, status) AS numrows FROM e_ticket_info AS ti ";

		if (!empty($cond)) $sql .= "WHERE $cond ";

		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getReportByStatus($skillid, $sid, $dateinfo, $offset=0, $limit=0)
	{
		$cond = $this->getConditionForReportByStatus($skillid, $sid, $dateinfo);
		$sql = "SELECT ti.skill_id, ti.status, COUNT(ticket_id) AS num_tickets FROM e_ticket_info AS ti ";

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "GROUP BY skill_id, status ORDER BY skill_id, status ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function getConditionForReportByStatus($skillid, $sid, $dateinfo)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('create_time', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($sid)) $cond = $this->getAndCondition($cond, "ti.status='$sid'");
		if (!empty($skillid)) {
			$cond = $this->getAndCondition($cond, "ti.skill_id='$skillid'");
		}
		
		return $cond;
	}
	
	
	function numReportByAgentActivity($agentid, $dateinfo)
	{
		$cond = $this->getConditionForReportByAgentActivity($agentid, $dateinfo);
		$sql = "SELECT COUNT(DISTINCT agent_id) AS numrows FROM e_ticket_activity ";

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getReportByAgentActivity($agentid, $dateinfo, $offset=0, $limit=0)
	{
		$cond = $this->getConditionForReportByAgentActivity($agentid,$dateinfo);
		$sql = "SELECT agent_id, COUNT(ticket_id) AS num_records, SUM(IF(activity='V', 1, 0)) AS num_views, ".
			"SUM(IF(activity='A', 1, 0)) AS num_assigns, SUM(IF(activity='M', 1, 0)) AS num_responses, ".
			"SUM(IF(activity='D', 1, 0)) AS num_dispositions, SUM(IF(activity='S', 1, 0)) AS num_statuses FROM e_ticket_activity ";

		if (!empty($cond)) $sql .= "WHERE $cond ";
		$sql .= "GROUP BY agent_id ORDER BY agent_id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getConditionForReportByAgentActivity($agentid, $dateinfo)
	{
		$cond = '';
		$date_attributes = DateHelper::get_date_attributes('activity_time', $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
		if (!empty($date_attributes->condition)) $cond = $this->getAndCondition($cond, $date_attributes->condition);
		if (!empty($agentid)) $cond = $this->getAndCondition($cond, "agent_id='$agentid'");
		
		return $cond;
	}
	
	function getMyJobSummary($agentid, $source=null, $from_date=null, $to_date=null) {
	    $start_time = strtotime('-7 days');
	    $end_time = time();
		$sql = "SELECT SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, SUM(IF(status='C', 1, 0)) AS num_client_pendings FROM e_ticket_info ";
		$sql .= "WHERE assigned_to='$agentid' AND last_update_time BETWEEN '{$start_time}' AND '{$end_time}'";
//		echo $sql;die;
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getRecentJobSummary($agentid, $skipAgent, $source=null)
	{
		$starttime = strtotime("-1 day");
		$endtime = time();
		$date_cond = "last_update_time BETWEEN '{$starttime}' AND '{$endtime}' ";
		$select_fields = "SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, ".
			"SUM(IF(status='C', 1, 0)) AS num_client_pendings, SUM(IF(status='S', 1, 0)) AS num_serves";
		if ($skipAgent) {
			$sql = "SELECT $select_fields FROM e_ticket_info AS e WHERE $date_cond";
		} else {
			$sql = "SELECT $select_fields FROM e_ticket_info AS e JOIN agent_skill AS a ON a.skill_id=e.skill_id AND a.agent_id='$agentid' ";
			$sql .= "WHERE $date_cond";
		}
//		echo $sql;die;
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getJobSummary($agentid, $skipAgent, $source=null)
	{
		$starttime = strtotime("-7 day");
		$endtime = time();
		$date_cond = " last_update_time BETWEEN '{$starttime}' AND '{$endtime}' ";
		$select_fields = "SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, ".
			"SUM(IF(status='C', 1, 0)) AS num_client_pendings, SUM(IF(status='S', 1, 0)) AS num_serves";
		if ($skipAgent) {
			$sql = "SELECT $select_fields FROM e_ticket_info WHERE $date_cond";
		} else {
			$sql = "SELECT $select_fields FROM e_ticket_info AS e JOIN agent_skill AS a ON a.skill_id=e.skill_id AND a.agent_id='$agentid' ";
            $sql .= " WHERE $date_cond";
		}
//        echo $sql;die;
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function get_today_SL_AWT_AHT(){
        $starttime = strtotime("today");
        $endtime = strtotime(date("Y-m-d 23:59:59"));
	    $sql = "SELECT SUM(IF (STATUS = 'S' OR STATUS = 'E', 1, 0)) AS total_closed, SUM(IF(in_kpi = 'Y', 1, 0)) total_in_kpi,";
	    $sql .= " SUM(IF (STATUS = 'S' OR STATUS = 'E',waiting_duration,0)) AS WaitDuration, ";
	    $sql .= " SUM(IF (STATUS = 'S' OR STATUS = 'E',open_duration,0)) AS total_open_duration ";
	    $sql .= " FROM log_email_session ";
	    $sql .= " WHERE last_update_time BETWEEN '{$starttime}' AND '{$endtime}' ";
	    //GPrint($sql);die;
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }
    function getTodayInboundEmailsCount(){
	    $response = [];
        $starttime = strtotime("today");
        $endtime = strtotime(date("Y-m-d 23:59:59"));
	    $sql = "SELECT COUNT(*) AS total, eti.fetch_box_name FROM email_messages AS em ";
	    $sql .= " LEFT JOIN e_ticket_info AS eti ON eti.ticket_id=em.ticket_id ";
	    $sql .= " WHERE em.tstamp BETWEEN '{$starttime}' AND '{$endtime}' AND em.`status`='N' ";
	    $sql .= " GROUP BY eti.fetch_box_email ";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            foreach ($result as $key){
                $response[$key->fetch_box_name] = $key->total;
            }
        }
        return $response;
    }

    function getTodayInboxOutboundEmailsCount(){
        $response = [];
        $starttime = strtotime("today");
        $endtime = strtotime(date("Y-m-d 23:59:59"));
        $sql = "SELECT COUNT(*) AS total, eti.fetch_box_name FROM email_messages AS em ";
        $sql .= " LEFT JOIN e_ticket_info AS eti ON eti.ticket_id=em.ticket_id ";
        $sql .= " WHERE em.tstamp BETWEEN '{$starttime}' AND '{$endtime}' AND em.`status`='O' ";
        $sql .= " GROUP BY eti.fetch_box_email ";
        $result = $this->getDB()->query($sql);
        if (!empty($result)){
            foreach ($result as $key){
                $response[$key->fetch_box_name] = $key->total;
            }
        }
        return $response;
    }


    /*
     *  Email Activity Dashboard.
     */
     function getEmailActivityDashboardData($object){
        $sql = "SELECT  count(ticket_id) AS total_email, skill_id, ";
        $sql .= " SUM(IF (STATUS = 'S' OR STATUS = 'E', 1, 0)) AS total_closed, ";
        $sql .= " SUM(IF(in_kpi = 'Y', 1, 0)) total_in_kpi, ";
        $sql .= " SUM(IF ((STATUS = 'S' OR STATUS = 'E') AND in_kpi = 'N',1,0)) total_out_kpi, ";
        $sql .= " SUM(IF (STATUS = 'S' OR STATUS = 'E',waiting_duration,0)) AS WaitDuration, ";
        $sql .= " MAX(IF (STATUS = 'S' OR STATUS = 'E',waiting_duration,0)) MAX_Wait, ";
        $sql .= " SUM(IF (STATUS = 'S' OR STATUS = 'E',open_duration,0)) AS total_open_duration ";
        $sql .= " FROM log_email_session  ";
        $sql .= " WHERE close_time BETWEEN '{$object->ststamp}' AND '{$object->etstamp}' ";
        $sql .= empty($object->isAdmin) ? " AND skill_id IN ('".implode("','", $object->skill)."')" : "";
        $sql .= " GROUP BY  skill_id ";
        //GPrint($sql);die;
        return $this->getDB()->query($sql);
    }
}

?>