<?php

class MCrmTicket extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getETicketInfoStatusOptions()
    {
        return array(
            'O' => 'New',
            'P' => 'Pending',
            'C' => 'Pending - Client',
            'S' => 'Served',
            'E' => 'Closed',
        );
    }



    function numETicket($agentid, $assignedId, $ticketid, $skill_id, $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo=null)
    {
        $sql = "SELECT COUNT(eti.ticket_id) AS numrows FROM e_ticket_info AS eti ";
        if (!empty($agentid)){
            $sql .= " JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }
        $cond = $this->getEmailCondition($assignedId, $ticketid, $skill_id, $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo);
        if (!empty($cond)) $sql .= " WHERE $cond ";
        $result = $this->getDB()->query($sql);

        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }
        return 0;
    }


    function getETicket($agentid, $assignedId, $ticketid, $email, $disposition_id, $status, $dateinfo, $offset=0, $limit=0, $tfield, $newMailTime='0', $updateinfo=null)
    {
        $sql = "SELECT eti.*, skl.skill_name FROM e_ticket_info AS eti ";
        if (!empty($agentid)){
            $sql .= " JOIN agent_skill AS ags ON ags.skill_id=eti.skill_id AND ags.agent_id='$agentid' ";
        }
        $sql .= " LEFT JOIN skill AS skl ON skl.skill_id=eti.skill_id ";
        $cond = $this->getEmailCondition($assignedId, $ticketid, $email, $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo);
        if (!empty($cond)) $sql .= " WHERE $cond ";
        $sql .= " ORDER BY last_update_time DESC ";
        if ($limit > 0) $sql .= " LIMIT $offset, $limit";
        return $this->getDB()->query($sql);
    }


    function getEmailCondition($assignedId, $ticketid, $skill_id, $disposition_id, $status, $dateinfo, $tfield, $newMailTime='0', $updateinfo=null)
    {
        $cond = '';
        $timeField = "create_time";
        if (!empty($ticketid)) $cond .= "ticket_id='$ticketid'";

        if (!empty($skill_id) && $skill_id != "*") {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "skill_id='$skill_id'";
        }

        if (!empty($disposition_id) && $disposition_id != "*") {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "disposition_id='$disposition_id'";
        }

        if (!empty($assignedId)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "assigned_to='$assignedId'";
        }


        if (!empty($status)) {
            if (!empty($cond)) $cond .= ' AND ';
            $cond .= "status='$status'";
        }

        if (!empty($tfield)) {
            if ($tfield == "update"){
                $timeField = "last_update_time";
            }
        }

        $last_date_attr = new stdClass();
        $last_date_attr->condition = "";
        $date_attributes = DateHelper::get_date_attributes($timeField, $dateinfo->sdate, $dateinfo->edate, $dateinfo->stime, $dateinfo->etime);
        if (isset($updateinfo->sdate) && !empty($updateinfo->sdate)){
            $last_date_attr = DateHelper::get_date_attributes("last_update_time", $updateinfo->sdate, $updateinfo->edate, $updateinfo->stime, $updateinfo->etime);
        }

        if (!empty($date_attributes->condition)) {
            //$newMailTime = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
            if ($tfield == "update" && $newMailTime == "24"){
                $starttime = strtotime("-1 day");
                $endtime = time();
                $date_cond = "last_update_time BETWEEN $starttime AND $endtime";
                if (!empty($cond)) $cond .= ' AND ';
                $cond .= $date_cond;
            }else {
                if (!empty($cond)) $cond .= ' AND ';
                $cond .= $date_attributes->condition;
            }
        }

        if (!empty($last_date_attr->condition)) {
            if ($tfield == "update" && $newMailTime == "24"){
                ;
            }else {
                if (!empty($cond)) $cond .= ' AND ';
                $cond .= $last_date_attr->condition;
            }
        }

        $cond .= !empty($cond) ? " AND source='C' " : " source='C' ";

        return $cond;
    }



    public function getETicketInfo($skill_id, $disposition_id, $status, $dateTimeInfo, $tfield, $lastDateInfo, $offset=0, $limit=0)
    {
        $sql = "SELECT eti.*, skl.skill_name FROM e_ticket_info AS eti ";
        $sql .= " LEFT JOIN skill AS skl ON skl.skill_id=eti.skill_id ";

        $cond = $this->getEmailCondition($skill_id, $disposition_id, $status, $dateTimeInfo, $tfield, $lastDateInfo, $tabName="eti.");
        $sql .= !empty($cond) ? " WHERE {$cond} AND source='C' " : " AND source='C' ";

        $sql .= "ORDER BY last_update_time DESC ";
        if ($limit > 0) {
            $sql .= "LIMIT $offset, $limit";
        }
        return $this->getDB()->query($sql);
    }


    function numReportByDisposition($skillid, $did, $dateinfo)
    {
        $cond = $this->getConditionForReportByDisposition($skillid, $did, $dateinfo);
        $sql = "SELECT COUNT(DISTINCT disposition_id, skill_id) AS numrows FROM e_ticket_info AS ti ";

        if (!empty($cond)) $sql .= "WHERE $cond ";
        //echo $sql;die;
        $result = $this->getDB()->query($sql);

        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }

        return 0;
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
        //GPrint($did);die;
        return $cond;
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

    function numReportByStatus($skillid, $sid, $dateinfo)
    {
        $cond = $this->getConditionForReportByStatus($skillid, $sid, $dateinfo);
        $sql = "SELECT COUNT(DISTINCT skill_id, status) AS numrows FROM e_ticket_info AS ti ";
        if (!empty($cond)) $sql .= "WHERE $cond ";
        $result = $this->getDB()->query($sql);
        //echo ;
        if($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }

        return 0;
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

    function getReportByStatus($skillid, $sid, $dateinfo, $offset=0, $limit=0)
    {
        $cond = $this->getConditionForReportByStatus($skillid, $sid, $dateinfo);
        $sql = "SELECT ti.skill_id, ti.status, COUNT(ticket_id) AS num_tickets FROM e_ticket_info AS ti ";

        if (!empty($cond)) $sql .= "WHERE $cond ";
        $sql .= "GROUP BY skill_id, status ORDER BY skill_id, status ";
        if ($limit > 0) $sql .= "LIMIT $offset, $limit";
        //echo $sql;
        return $this->getDB()->query($sql);
    }

    public function getMyJobSummary($agentid)
    {
        $sql = "SELECT SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, SUM(IF(status='C', 1, 0)) AS num_client_pendings FROM e_ticket_info ";
        $sql .= "WHERE assigned_to IN('', '$agentid') AND source = 'C' ";

        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    function getRecentJobSummary($agentid, $skipAgent)
    {
        $starttime = strtotime("-1 day");
        $endtime = time();
        $condition = "last_update_time BETWEEN {$starttime} AND {$endtime} AND source = 'C' ";
        $select_fields = "SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, ".
            "SUM(IF(status='C', 1, 0)) AS num_client_pendings, SUM(IF(status='S', 1, 0)) AS num_serves";

        $sql = "SELECT $select_fields FROM e_ticket_info AS e ";
        $sql .= $skipAgent ? " " : " JOIN agent_skill AS a ON a.skill_id=e.skill_id AND a.agent_id='{$agentid}' ";
        $sql .= " WHERE $condition ";


        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    function getJobSummary($agentid, $skipAgent)
    {
        $select_fields = "SUM(IF(status='O', 1, 0)) AS num_news, SUM(IF(status='P', 1, 0)) AS num_pendings, ".
            "SUM(IF(status='C', 1, 0)) AS num_client_pendings, SUM(IF(status='S', 1, 0)) AS num_serves";

        $sql = "SELECT $select_fields FROM e_ticket_info";
        $sql .= $skipAgent ? " " : " AS e JOIN agent_skill AS a ON a.skill_id=e.skill_id AND a.agent_id='{$agentid}' ";
        $sql .= " WHERE source='C' ";

        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }
}
