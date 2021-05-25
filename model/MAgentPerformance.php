<?php

class MAgentPerformance extends Model
{

    /**
     * MAgentPerformance constructor.
     */
    function __construct() 
    {
        parent::__construct();
    }

    public function numAgents()
    {
        $response =  $this->getDB()->query("SELECT COUNT(*) AS aggregate FROM  agents");

        return !empty($response[0]->aggregate) ? $response[0]->aggregate : 0;
    }

    public function getAgents($select="*", $agent_id, $offset = 0, $limit = 20)
    {
        $sql = "SELECT {$select} FROM agents ";
        $sql .= !empty($agent_id) ? " WHERE agent_id='{$agent_id}' " : "";
        $sql .= " ORDER BY agent_id LIMIT {$offset}, {$limit}";

        return $this->getDB()->query($sql);
    }

    public function getAgentsSessionEvents($agent_ids = [], $sdate, $edate)
    {
       // $sdate = empty($sdate) ?  date('Y-m-d 00:00:00') : $sdate;
        //$edate = empty($edate) ?  date('Y-m-d 23:59:59') : $edate;
        $sql = "SELECT * FROM log_agent_session WHERE tstamp BETWEEN '{$sdate}' AND '{$edate}' AND agent_id IN ('". implode("', '",$agent_ids) ."') ORDER BY agent_id, tstamp";

        return $this->getDB()->query($sql);
    }


    function getAgentLastStatus($agent_id, $stime)
    {
        $sql = "SELECT * FROM log_agent_session WHERE tstamp < '{$stime}'";
        $sql .= " AND agent_id='$agent_id' ORDER BY tstamp DESC LIMIT 1";

        return $this->getDB()->query($sql);
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

    function getAgentPreviousLoginStatus($agent_id, $stime)
    {
        $sql = "SELECT * FROM log_agent_session WHERE   tstamp < '{$stime}' ";
        $sql .= " AND agent_id='$agent_id' and type='I' ORDER BY tstamp DESC LIMIT 1 ";

        return $this->getDB()->query($sql);
    }

    public function getAgentPerformanceSummary($agent_id, $sdate, $edate)
    {
        $sql = "SELECT SUM(CASE WHEN is_answer='Y' THEN 1 ELSE 0 END ) AS calls_answered, ";
        $sql .= "SUM(ring_time) AS ring_time, SUM(service_time)  - SUM(hold_time) AS talk_time, SUM(wrap_up_time) AS wrap_up_time, ";
        $sql .= "SUM(hold_time) AS hold_time, SUM(hold_in_q) AS hold_time_in_queue,  ";
        $sql .= "SUM(CASE WHEN (is_answer='Y' AND  disc_party='A') THEN 1 ELSE 0 END)  AS agent_hangup, ";
        $sql .= "SUM(CASE WHEN (is_answer='N' AND  disc_party='A') THEN 1 ELSE 0 END) AS agent_reject_calls, ";
        $sql .= "SUM(CASE WHEN disc_party='A' THEN 1 ELSE 0 END ) AS agent_disc_calls, ";
        $sql .= "SUM(CASE WHEN service_time < 10 THEN 1 ELSE 0 END ) AS short_call, ";
        $sql .= "SUM(CASE WHEN repeated_call = 'Y' THEN 1 ELSE 0 END ) AS repeated_call, ";
        $sql .= "SUM(CASE WHEN fcr_call = 'Y' THEN 1 ELSE 0 END ) AS fcr_call, ";
        $sql .= "SUM(disposition_count) AS workcode_count FROM log_agent_inbound ";
        $sql .= "WHERE start_time BETWEEN '{$sdate}' AND '{$edate}' AND agent_id = '{$agent_id}' LIMIT 1";

        return $this->getDB()->query($sql);
    }


    public function getAgentSkillSetAsString($agentIds = [])
    {
        $skill_sets = [];

        $sql = "SELECT agent_id, skill_name  FROM agent_skill ";
        $sql .= " INNER JOIN  skill ON agent_skill.skill_id = skill.skill_id";
        $sql .= " WHERE agent_id  IN ('". implode("', '", $agentIds)."') ";
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




}

