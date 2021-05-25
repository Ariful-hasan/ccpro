<?php

class MUnattended extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function getThresholdTime()
    {
       return strtotime(UNATTENDED_CDR_TIME_RANGE) - time();
    }

    private function setUnattendedCdrData($msisdn, $did, $skillId, $type, $limit = 0, $offset = 0)
    {
        $thresholdTime = $this->getThresholdTime();
        $this->tables = "log_unattended_cdr";
        $this->columns = " call_start_time as sdate, status, skill_id, did, cli, callid ";
        $this->conditions = " call_start_time BETWEEN '{$this->dateFrom}' AND '{$this->dateTo}' ";
        if (!empty($skillId)) $this->conditions .= " AND skill_id = '{$skillId}' ";
        if (!empty($did)) $this->conditions .= " AND did = '{$did}' ";
        if (!empty($msisdn)) $this->conditions .= " AND cli = '{$msisdn}' ";

        $this->conditions .= !empty($type) && $type!= "*" ? " AND type = '$type' " : "";   // voice call

        $this->conditions .= " AND call_type = 'V' ";   // voice call
        $this->conditions .= " AND status IN ('A', 'I') ";      // status abandoned
        $this->conditions .= " AND NOT (inbound_time_difference BETWEEN 1  AND {$thresholdTime} 
                               OR outbound_time_difference BETWEEN 1  AND {$thresholdTime} 
                               OR removal_status IN ('M','A')) ";
        $this->orderByColumns = " call_start_time ";
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function numUnattendedCdr($dateInfo, $msisdn, $did, $skillId, $type)
    {
        if (!$this->setDate($dateInfo)) return 0;
        $this->setUnattendedCdrData($msisdn, $did, $skillId, $type);
        return $this->getResultCount("callid");
    }

    public function getUnattendedCdr($dateInfo, $msisdn, $did, $skillId, $type, $limit = 0, $offset = 0)
    {
        if (!$this->setDate($dateInfo)) return [];
        $this->setUnattendedCdrData($msisdn, $did, $skillId, $type, $limit, $offset);
        return $this->getResultData();
    }

    private function setUnattendedCdrInfoData($callId)
    {
        $this->tables = "log_unattended_cdr";
        $this->columns = "  status, skill_id, cli, callid, type ";
        $this->conditions = " callid = '{$callId}' ";
    }

    public function getUnattendedCdrInfo($callId)
    {
        $this->setUnattendedCdrInfoData($callId);
        return $this->getResultData();
    }

    private function setCallStatusData($callId, $status, $type)
    {
        $this->tables = "log_unattended_cdr";
        $this->columns = "  status = '{$status}', type= '{$type}' ";
        $this->conditions = " callid = '{$callId}' ";
        $this->limit = 1;
    }

    public function updateCallStatus($callId, $status, $type)
    {
        $this->setCallStatusData($callId, $status, $type);
        return $this->getUpdateResult();
    }

    private function setRemovingDispositionsData($templateId)
    {
        $this->tables = "skill_crm_disposition_code";
        $this->columns = "  disposition_id, title ";
        $this->conditions = "  template_id = '{$templateId}' ";
    }

    public function getRemovingDispositions($templateId)
    {
        $this->setRemovingDispositionsData($templateId);
        return $this->getResultData();
    }

    private function setManualDispositionData($callId, $dispositionId, $comment)
    {
        $agentId = UserAuth::getCurrentUser();
        $manualRemovalStatus = 'M';
        $currentTime= date("Y-m-d H:i:s");
        $this->tables = "log_unattended_cdr";
        $this->columns = " disposition_id = '{$dispositionId}', `comment` = '{$comment}', removed_by = '{$agentId}',
                        removal_status = '{$manualRemovalStatus}', manual_update_time = '{$currentTime}'";
        $this->conditions = " callid = '{$callId}' ";
        $this->limit = 1;
    }

    public function updateManualDisposition($callId, $dispositionId, $comment)
    {
        $this->setManualDispositionData($callId, $dispositionId, $comment);
        return $this->getUpdateResult();
    }

    private function setUnattendedCdrSummaryData($skillId, $limit = 0, $offset = 0)
    {
        $thresholdTime = $this->getThresholdTime();
        $this->tables = "log_unattended_cdr";
        $this->columns = "DATE(call_start_time) as sdate, skill_id, COUNT(cli) as total_call, SUM(hold_in_q) AS hold_in_q,
                      SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) AS cb_request,
                      SUM(CASE WHEN outbound_time_difference <= {$thresholdTime} THEN 1 ELSE 0 END) AS cb_within_threshold,
                      SUM(CASE WHEN outbound_time_difference > {$thresholdTime} THEN 1 ELSE 0 END) AS cb_after_threshold";
        $this->conditions = " call_start_time BETWEEN '{$this->dateFrom}' AND '{$this->dateTo}' ";
        if (!empty($skillId)) $this->conditions .= " AND skill_id = '{$skillId}' ";
        $this->conditions .= " AND call_type = 'V' ";   // voice call
        $this->conditions .= " AND inbound_time_difference NOT BETWEEN 1  AND {$thresholdTime} ";
        $this->orderByColumns = " call_start_time ";
        $this->groupByColumns = " DATE(call_start_time), skill_id ";
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function numUnattendedCdrSummary($dateInfo, $skillId)
    {
        if (!$this->setDate($dateInfo)) return 0;
        $this->setUnattendedCdrSummaryData($skillId);
        return $this->getResultCount("callid");
    }

    public function getUnattendedCdrSummary($dateInfo, $skillId, $limit = 0, $offset = 0)
    {
        if (!$this->setDate($dateInfo)) return [];
        $this->setUnattendedCdrSummaryData($skillId, $limit, $offset);
        return $this->getResultData();
    }

    private function setUnattendedCdrDetailsData($params, $limit = 0, $offset = 0)
    {
        $this->tables = "log_unattended_cdr luc LEFT JOIN log_agent_outbound_manual laom 
                        ON luc.outbound_callid = laom.callid";

        $this->columns = "DATE(luc.call_start_time) AS sdate, luc.tstamp AS stop_time, luc.skill_id, luc.cli, luc.did,
                        luc.hold_in_q, luc.outbound_time_difference, luc.threshold_time, luc.threshold_status, luc.status,
                        luc.removal_status, luc.removed_by, luc.manual_update_time, luc.disposition_id as removal_disposition_id,
                        laom.agent_id, laom.talk_time, laom.disc_cause, laom.disc_party, laom.disposition_id ";

        $this->conditions = " luc.call_start_time BETWEEN '{$this->dateFrom}' AND '{$this->dateTo}' ";
        if (!empty($params->skillId)) $this->conditions .= " AND luc.skill_id = '{$params->skillId}' ";
        if (!empty($params->agentId)) $this->conditions .= " AND laom.agent_id = '{$params->agentId}' ";
        if (!empty($params->did)) $this->conditions .= " AND luc.did = '{$params->did}' ";
        if (!empty($params->cli)) $this->conditions .= " AND luc.cli = '{$params->cli}' ";
        if (!empty($params->callId)) $this->conditions .= " AND luc.callid = '{$params->callId}' ";
        if (!empty($params->callbackWithin)) $this->conditions .= " AND luc.outbound_time_difference = '{$params->callbackWithin}' ";
        if (!empty($params->status)) $this->conditions .= " AND luc.status = '{$params->status}' ";
        if (!empty($params->removedBy)) $this->conditions .= " AND luc.removed_by = '{$params->removedBy}' ";
        if (!empty($params->removeDateInfo)) {
            $removeTimestampFrom = date("Y-m-d H:i:s", $params->removeDateInfo->ststamp);
            $removeTimestampTo = date("Y-m-d H:i:s", $params->removeDateInfo->etstamp);
            $this->conditions .= " AND luc.manual_update_time BETWEEN '{$removeTimestampFrom}' AND '{$removeTimestampTo}' ";
        }
        if (!empty($params->dispositionId)) $this->conditions .= " AND laom.disposition_id = '{$params->dispositionId}' ";
        $this->conditions .= " AND luc.call_type = 'V' ";   // voice call
        $this->orderByColumns = " luc.call_start_time ";
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function numUnattendedCdrDetails($dateInfo, $params)
    {
        if (!$this->setDate($dateInfo)) return 0;
        $this->setUnattendedCdrDetailsData($params);
        return $this->getResultCount("luc.callid");
    }

    public function getUnattendedCdrDetails($dateInfo, $params, $limit = 0, $offset = 0)
    {
        if (!$this->setDate($dateInfo)) return [];
        $this->setUnattendedCdrDetailsData($params, $limit, $offset);
        return $this->getResultData();
    }

}